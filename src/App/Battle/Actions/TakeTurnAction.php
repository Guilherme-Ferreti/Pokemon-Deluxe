<?php

declare(strict_types=1);

namespace App\Battle\Actions;

use App\Battle\Enums\ActionBlocker;
use App\Battle\Enums\TurnActionOutcome;
use App\Battle\Exceptions\BattleNotReadyForTurnException;
use App\Battle\Exceptions\DynamaxMoveNotAllowedException;
use App\Battle\Exceptions\MoveAlreadyUsedException;
use App\Battle\Mechanics\AilmentInflictionChecker;
use App\Battle\Mechanics\DamageCalculator;
use App\Battle\Mechanics\MoveOutcomeRoller;
use App\Battle\Mechanics\StatusEffectCalculator;
use App\Battle\Mechanics\StatusEffectRoller;
use App\Battle\Mechanics\TurnOrderCalculator;
use App\Battle\Support\LockKeys;
use App\Battle\ValueObjects\MoveHit;
use App\Pokemon\Mechanics\StatsCalculator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use LogicException;
use Shared\Enums\Ailment;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Helpers\RandomHelper;
use Shared\Models\Battle;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;

final class TakeTurnAction
{
    private Battle $battle;

    private BattleState $state;

    /** @var EloquentCollection<string, Move> */
    private EloquentCollection $moves;

    /** @var EloquentCollection<string, Species> */
    private EloquentCollection $species;

    /** @var list<string> */
    private array $logLines = [];

    public function handle(Battle $battle, int $moveSlot): Battle
    {
        $this->battle = $battle;

        return Cache::withoutOverlapping(
            key: LockKeys::battleTurn($battle->id),
            callback: fn () => $this->takeTurn($moveSlot),
        );
    }

    private function takeTurn(int $moveSlot): Battle
    {
        $this->battle->refresh();
        $this->state = $this->battle->state;

        $this->ensureBattleIsReadyForTurn();

        $this->loadMovesAndSpecies();

        $moveIdsBySide = [
            'player'   => $this->findPlayerMoveId($moveSlot),
            'opponent' => $this->pickOpponentMoveId(),
        ];

        $flinchedSide = null;
        $turnOrder    = $this->rollTurnOrder();

        foreach ($turnOrder as $position => $side) {
            if ($side === $flinchedSide) {
                $this->logFlinch($side);

                continue;
            }

            $actionOutcome = $this->act($side, $moveIdsBySide[$side]);

            if ($position === 0 && $actionOutcome === TurnActionOutcome::CAUSED_FLINCH) {
                $flinchedSide = $this->otherSide($side);
            }
        }

        $this->applyEndOfTurnDamage($turnOrder);

        $this->battle->update(['state' => $this->state->withNextTurn($this->logLines)]);

        return $this->battle;
    }

    private function ensureBattleIsReadyForTurn(): void
    {
        $isReady = $this->battle->status === BattleStatus::ONGOING && ! $this->state->is_awaiting_send_out;

        throw_unless($isReady, BattleNotReadyForTurnException::class);
    }

    private function loadMovesAndSpecies(): void
    {
        $playerCombatant   = $this->state->player_side->activeCombatant();
        $opponentCombatant = $this->state->opponent_side->activeCombatant();

        $this->moves = Move::query()
            ->whereKey([...$playerCombatant->move_ids, ...$opponentCombatant->move_ids])
            ->get()
            ->keyBy('id');

        $this->species = Species::query()
            ->whereKey([$playerCombatant->species_id, $opponentCombatant->species_id])
            ->get(['id', 'name', 'is_gmax'])
            ->keyBy('id');
    }

    private function findPlayerMoveId(int $moveSlot): string
    {
        $moveIds = $this->state->player_side->activeCombatant()->move_ids;

        throw_unless(
            isset($moveIds[$moveSlot - 1]),
            InvalidArgumentException::class,
            sprintf('The move slot must be between 1 and %d, [%d] given.', count($moveIds), $moveSlot),
        );

        $combatant = $this->state->player_side->activeCombatant();
        $move      = $this->moves->findOrFail($moveIds[$moveSlot - 1]);

        throw_if($this->isOncePerBattleMoveUsed($combatant, $move), new MoveAlreadyUsedException($move));

        throw_if($this->isDynamaxMoveNotAllowed($combatant, $move), new DynamaxMoveNotAllowedException($move));

        return $move->id;
    }

    private function pickOpponentMoveId(): string
    {
        $combatant = $this->state->opponent_side->activeCombatant();

        $usableMoveIds = array_values(array_filter(
            $combatant->move_ids,
            fn (string $moveId) => ! $this->isOncePerBattleMoveUsed($combatant, $this->moves->findOrFail($moveId))
                && ! $this->isDynamaxMoveNotAllowed($combatant, $this->moves->findOrFail($moveId)),
        ));

        if ($usableMoveIds === []) {
            throw new LogicException('The opponent has no move it can use.');
        }

        return RandomHelper::pick($usableMoveIds);
    }

    private function isOncePerBattleMoveUsed(Combatant $combatant, Move $move): bool
    {
        return ($move->is_z_move || $move->is_dynamax_move)
            && in_array($move->id, $combatant->used_once_per_battle_move_ids, true);
    }

    private function isDynamaxMoveNotAllowed(Combatant $combatant, Move $move): bool
    {
        return $move->is_dynamax_move && ! $this->species->findOrFail($combatant->species_id)->is_gmax;
    }

    /**
     * @return list<'player'|'opponent'>
     */
    private function rollTurnOrder(): array
    {
        $playerChance = new TurnOrderCalculator()->firstMoverChance(
            $this->state->player_side->activeCombatant()->level,
            $this->state->opponent_side->activeCombatant()->level,
        );

        return RandomHelper::chance($playerChance) ? ['player', 'opponent'] : ['opponent', 'player'];
    }

    /**
     * @param  'player'|'opponent'  $side
     */
    private function act(string $side, string $moveId): TurnActionOutcome
    {
        $attacker = $this->activeCombatant($side);

        if ($attacker->isFainted()) {
            return TurnActionOutcome::SKIPPED;
        }

        $actionBlocker = new StatusEffectRoller()->rollActionBlocker($attacker);

        $this->wakeUpOrThawOut($side, $actionBlocker);

        $isConfused = $attacker->confusion_turns_left > 0 && ! in_array($actionBlocker, [ActionBlocker::FROZEN, ActionBlocker::ASLEEP], true);

        if ($isConfused) {
            $this->logLines[] = "{$this->displayName($side, $attacker, atStart: true)} is confused!";

            $this->replaceActiveCombatant($side, $this->activeCombatant($side)->withConfusionTurnsLeft($attacker->confusion_turns_left - 1));
        }

        $actionOutcome = $actionBlocker instanceof ActionBlocker
            ? $this->applyActionBlocker($side, $actionBlocker)
            : $this->useMove($side, $this->moves->findOrFail($moveId));

        $attacker = $this->activeCombatant($side);

        if ($isConfused && $attacker->confusion_turns_left === 0 && ! $attacker->isFainted()) {
            $this->logLines[] = "{$this->displayName($side, $attacker, atStart: true)} snapped out of its confusion!";
        }

        return $actionOutcome;
    }

    /**
     * @param  'player'|'opponent'  $side
     */
    private function wakeUpOrThawOut(string $side, ?ActionBlocker $actionBlocker): void
    {
        $combatant = $this->activeCombatant($side);

        $logLine = match (true) {
            $combatant->ailment === Ailment::FREEZE && $actionBlocker !== ActionBlocker::FROZEN => 'thawed out!',
            $combatant->ailment === Ailment::SLEEP && $actionBlocker !== ActionBlocker::ASLEEP  => 'woke up!',
            default                                                                             => null,
        };

        if ($logLine === null) {
            return;
        }

        $this->replaceActiveCombatant($side, $combatant->withAilment(null, 0));

        $this->logLines[] = "{$this->displayName($side, $combatant, atStart: true)} {$logLine}";
    }

    /**
     * @param  'player'|'opponent'  $side
     */
    private function applyActionBlocker(string $side, ActionBlocker $actionBlocker): TurnActionOutcome
    {
        $combatant = $this->activeCombatant($side);
        $name      = $this->displayName($side, $combatant, atStart: true);

        match ($actionBlocker) {
            ActionBlocker::FROZEN                   => $this->logLines[] = "{$name} is frozen solid!",
            ActionBlocker::ASLEEP                   => $this->logLines[] = "{$name} is fast asleep.",
            ActionBlocker::HURT_ITSELF_IN_CONFUSION => $this->logLines[] = 'It hurt itself in its confusion!',
            ActionBlocker::PARALYZED                => $this->logLines[] = "{$name} is paralyzed! It can't move!",
        };

        if ($actionBlocker === ActionBlocker::ASLEEP) {
            $this->replaceActiveCombatant($side, $combatant->withAilment(Ailment::SLEEP, $combatant->ailment_turns_left - 1));
        }

        if ($actionBlocker === ActionBlocker::HURT_ITSELF_IN_CONFUSION) {
            $damage = new StatusEffectCalculator()->confusionSelfHitDamage(
                new StatsCalculator()->calculate($combatant->level, $combatant->boost, $combatant->is_shiny),
            );

            $this->applyDamage($side, $damage);

            $this->logFaintIfFainted($side);
        }

        return TurnActionOutcome::BLOCKED;
    }

    /**
     * @param  'player'|'opponent'  $side
     */
    private function useMove(string $side, Move $move): TurnActionOutcome
    {
        $attacker     = $this->activeCombatant($side);
        $defenderSide = $this->otherSide($side);

        $this->logLines[] = "{$this->displayName($side, $attacker, atStart: true)} used {$move->name}!";

        if ($move->is_z_move || $move->is_dynamax_move) {
            $this->replaceActiveCombatant($side, $attacker->withUsedOncePerBattleMove($move->id));
        }

        $outcome = new MoveOutcomeRoller()->roll($move);

        if (! $outcome->hasHit) {
            $this->logLines[] = "{$this->displayName($side, $attacker, atStart: true)}'s attack missed!";

            return TurnActionOutcome::MISSED;
        }

        $typeMultiplier = new DamageCalculator()->typeMultiplier($move->type, ...$this->activeCombatant($defenderSide)->types);

        if ($typeMultiplier === 0.0) {
            $this->logLines[] = "It doesn't affect {$this->displayName($defenderSide, $this->activeCombatant($defenderSide), atStart: false)}...";

            return TurnActionOutcome::NO_EFFECT;
        }

        if ($move->power === null) {
            if (! $outcome->inflictedAilment instanceof Ailment) {
                return TurnActionOutcome::USED;
            }

            if ($this->inflictAilment($defenderSide, $outcome->inflictedAilment)) {
                return TurnActionOutcome::USED;
            }

            $this->logLines[] = 'But it failed!';

            return TurnActionOutcome::NO_EFFECT;
        }

        $landedHits = 0;

        foreach ($outcome->hits as $hit) {
            if ($this->activeCombatant($defenderSide)->isFainted()) {
                break;
            }

            $this->dealHit($this->activeCombatant($side), $defenderSide, $move, $hit);

            $landedHits++;
        }

        $this->logEffectiveness($typeMultiplier);

        if ($outcome->hitCount() > 1) {
            $this->logLines[] = $landedHits === 1 ? 'It hit once!' : "It hit {$landedHits} times!";
        }

        if ($this->logFaintIfFainted($defenderSide)) {
            return TurnActionOutcome::KNOCKED_OUT;
        }

        if ($outcome->inflictedAilment instanceof Ailment) {
            $this->inflictAilment($defenderSide, $outcome->inflictedAilment);
        }

        return $outcome->causesFlinch ? TurnActionOutcome::CAUSED_FLINCH : TurnActionOutcome::USED;
    }

    /**
     * @param  'player'|'opponent'  $defenderSide
     */
    private function dealHit(Combatant $attacker, string $defenderSide, Move $move, MoveHit $hit): void
    {
        $defender = $this->activeCombatant($defenderSide);

        $damage = new DamageCalculator()->calculate(
            attackerStats: new StatsCalculator()->calculate($attacker->level, $attacker->boost, $attacker->is_shiny),
            attackerTypes: $attacker->types,
            defenderStats: new StatsCalculator()->calculate($defender->level, $defender->boost, $defender->is_shiny),
            defenderTypes: $defender->types,
            move: $move,
            isCriticalHit: $hit->isCriticalHit,
            randomFactor: $hit->randomFactor,
            isAttackerBurned: $attacker->ailment === Ailment::BURN,
        );

        if ($hit->isCriticalHit) {
            $this->logLines[] = 'A critical hit!';
        }

        $this->applyDamage($defenderSide, $damage);
    }

    /**
     * @param  'player'|'opponent'  $side
     */
    private function applyDamage(string $side, int $damage): void
    {
        $combatant = $this->activeCombatant($side);

        $this->replaceActiveCombatant($side, $combatant->withCurrentHp(max(0, $combatant->current_hp - $damage)));
    }

    /**
     * @param  'player'|'opponent'  $side
     */
    private function logFaintIfFainted(string $side): bool
    {
        $combatant = $this->activeCombatant($side);

        if (! $combatant->isFainted()) {
            return false;
        }

        $this->logLines[] = "{$this->displayName($side, $combatant, atStart: true)} fainted!";

        return true;
    }

    /**
     * @param  'player'|'opponent'  $side
     */
    private function inflictAilment(string $side, Ailment $ailment): bool
    {
        $combatant = $this->activeCombatant($side);

        if (! new AilmentInflictionChecker()->canInflict($ailment, $combatant)) {
            return false;
        }

        $turnsLeft = new StatusEffectRoller()->durationFor($ailment);

        $this->replaceActiveCombatant($side, $ailment === Ailment::CONFUSION
            ? $combatant->withConfusionTurnsLeft($turnsLeft)
            : $combatant->withAilment($ailment, $turnsLeft));

        $logLine = match ($ailment) {
            Ailment::BURN      => 'was burned!',
            Ailment::POISON    => 'was poisoned!',
            Ailment::PARALYSIS => 'is paralyzed! It may be unable to move!',
            Ailment::SLEEP     => 'fell asleep!',
            Ailment::FREEZE    => 'was frozen solid!',
            Ailment::CONFUSION => 'became confused!',
            Ailment::UNKNOWN   => throw new LogicException('The unknown ailment is never inflicted.'),
        };

        $this->logLines[] = "{$this->displayName($side, $combatant, atStart: true)} {$logLine}";

        return true;
    }

    /**
     * @param  list<'player'|'opponent'>  $turnOrder
     */
    private function applyEndOfTurnDamage(array $turnOrder): void
    {
        foreach ($turnOrder as $side) {
            if (! $this->state->player_side->hasCombatantsLeft() || ! $this->state->opponent_side->hasCombatantsLeft()) {
                return;
            }

            $combatant = $this->activeCombatant($side);
            $damage    = new StatusEffectCalculator()->endOfTurnDamage($combatant);

            if ($damage === 0) {
                continue;
            }

            $this->applyDamage($side, $damage);

            $cause = $combatant->ailment === Ailment::BURN ? 'its burn' : 'poison';

            $this->logLines[] = "{$this->displayName($side, $combatant, atStart: true)} is hurt by {$cause}!";

            $this->logFaintIfFainted($side);
        }
    }

    /**
     * @param  'player'|'opponent'  $side
     */
    private function logFlinch(string $side): void
    {
        $combatant = $this->activeCombatant($side);

        if (! $combatant->isFainted()) {
            $this->logLines[] = "{$this->displayName($side, $combatant, atStart: true)} flinched and couldn't move!";
        }
    }

    private function logEffectiveness(float $typeMultiplier): void
    {
        if ($typeMultiplier > 1.0) {
            $this->logLines[] = "It's super effective!";
        } elseif ($typeMultiplier < 1.0) {
            $this->logLines[] = "It's not very effective...";
        }
    }

    /**
     * @param  'player'|'opponent'  $side
     */
    private function activeCombatant(string $side): Combatant
    {
        return $side === 'player'
            ? $this->state->player_side->activeCombatant()
            : $this->state->opponent_side->activeCombatant();
    }

    /**
     * @param  'player'|'opponent'  $side
     */
    private function replaceActiveCombatant(string $side, Combatant $combatant): void
    {
        $this->state = $side === 'player'
            ? $this->state->withPlayerSide($this->state->player_side->withActiveCombatant($combatant))
            : $this->state->withOpponentSide($this->state->opponent_side->withActiveCombatant($combatant));
    }

    /**
     * @param  'player'|'opponent'  $side
     * @return 'player'|'opponent'
     */
    private function otherSide(string $side): string
    {
        return $side === 'player' ? 'opponent' : 'player';
    }

    /**
     * @param  'player'|'opponent'  $side
     */
    private function displayName(string $side, Combatant $combatant, bool $atStart): string
    {
        $name = $this->species->findOrFail($combatant->species_id)->name;
        $name = $combatant->is_shiny ? "Shiny {$name}" : $name;

        if ($side === 'opponent' && $this->battle->type === BattleType::WILD) {
            return ($atStart ? 'The wild ' : 'the wild ') . $name;
        }

        return $name;
    }
}
