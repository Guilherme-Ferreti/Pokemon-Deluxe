<?php

declare(strict_types=1);

namespace App\Battle\Actions;

use App\Battle\Enums\TurnActionOutcome;
use App\Battle\Exceptions\BattleNotReadyForTurnException;
use App\Battle\Exceptions\DynamaxMoveNotAllowedException;
use App\Battle\Exceptions\MoveAlreadyUsedException;
use App\Battle\Mechanics\DamageCalculator;
use App\Battle\Mechanics\MoveOutcomeRoller;
use App\Battle\Mechanics\TurnOrderCalculator;
use App\Battle\Support\LockKeys;
use App\Battle\ValueObjects\MoveHit;
use App\Pokemon\Mechanics\StatsCalculator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use LogicException;
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

        foreach ($this->rollTurnOrder() as $position => $side) {
            if ($side === $flinchedSide) {
                $this->logFlinch($side);

                continue;
            }

            $actionOutcome = $this->act($side, $moveIdsBySide[$side]);

            if ($position === 0 && $actionOutcome === TurnActionOutcome::CAUSED_FLINCH) {
                $flinchedSide = $this->otherSide($side);
            }
        }

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
        $attacker     = $this->activeCombatant($side);
        $defenderSide = $this->otherSide($side);

        if ($attacker->isFainted()) {
            return TurnActionOutcome::SKIPPED;
        }

        $move = $this->moves->findOrFail($moveId);

        $this->logLines[] = "{$this->displayName($side, $attacker, atStart: true)} used {$move->name}!";

        if ($move->is_z_move || $move->is_dynamax_move) {
            $this->replaceActiveCombatant($side, $attacker->withUsedOncePerBattleMove($move->id));
        }

        $outcome = new MoveOutcomeRoller()->roll($move);

        if (! $outcome->hasHit) {
            $this->logLines[] = "{$this->displayName($side, $attacker, atStart: true)}'s attack missed!";

            return TurnActionOutcome::MISSED;
        }

        if ($move->power === null) {
            return TurnActionOutcome::USED;
        }

        $typeMultiplier = new DamageCalculator()->typeMultiplier($move->type, ...$this->activeCombatant($defenderSide)->types);

        if ($typeMultiplier === 0.0) {
            $this->logLines[] = "It doesn't affect {$this->displayName($defenderSide, $this->activeCombatant($defenderSide), atStart: false)}...";

            return TurnActionOutcome::NO_EFFECT;
        }

        $landedHits = 0;

        foreach ($outcome->hits as $hit) {
            if ($this->activeCombatant($defenderSide)->isFainted()) {
                break;
            }

            $this->dealHit($attacker, $defenderSide, $move, $hit);

            $landedHits++;
        }

        $this->logEffectiveness($typeMultiplier);

        if ($outcome->hitCount() > 1) {
            $this->logLines[] = $landedHits === 1 ? 'It hit once!' : "It hit {$landedHits} times!";
        }

        $defender = $this->activeCombatant($defenderSide);

        if ($defender->isFainted()) {
            $this->logLines[] = "{$this->displayName($defenderSide, $defender, atStart: true)} fainted!";

            return TurnActionOutcome::KNOCKED_OUT;
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
        );

        $this->replaceActiveCombatant($defenderSide, $defender->withCurrentHp(max(0, $defender->current_hp - $damage)));

        if ($hit->isCriticalHit) {
            $this->logLines[] = 'A critical hit!';
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
