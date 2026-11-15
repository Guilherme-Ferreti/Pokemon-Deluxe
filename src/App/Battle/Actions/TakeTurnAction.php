<?php

declare(strict_types=1);

namespace App\Battle\Actions;

use App\Battle\Exceptions\BattleNotReadyForTurnException;
use App\Battle\Mechanics\DamageCalculator;
use App\Battle\Mechanics\TurnOrderCalculator;
use App\Battle\Support\LockKeys;
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

    /** @var array<string, string> */
    private array $speciesNames;

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

        $playerMoveId   = $this->findPlayerMoveId($moveSlot);
        $opponentMoveId = $this->pickOpponentMoveId();

        $this->loadMovesAndSpeciesNames([$playerMoveId, $opponentMoveId]);

        $moveIdsBySide = ['player' => $playerMoveId, 'opponent' => $opponentMoveId];

        foreach ($this->rollTurnOrder() as $side) {
            $this->act($side, $moveIdsBySide[$side]);
        }

        $this->battle->update(['state' => $this->state->withNextTurn($this->logLines)]);

        return $this->battle;
    }

    private function ensureBattleIsReadyForTurn(): void
    {
        $isReady = $this->battle->status === BattleStatus::ONGOING && ! $this->state->is_awaiting_send_out;

        throw_unless($isReady, BattleNotReadyForTurnException::class);
    }

    private function findPlayerMoveId(int $moveSlot): string
    {
        $moveIds = $this->state->player_side->activeCombatant()->move_ids;

        throw_unless(
            isset($moveIds[$moveSlot - 1]),
            InvalidArgumentException::class,
            sprintf('The move slot must be between 1 and %d, [%d] given.', count($moveIds), $moveSlot),
        );

        return $moveIds[$moveSlot - 1];
    }

    private function pickOpponentMoveId(): string
    {
        $moveIds = $this->state->opponent_side->activeCombatant()->move_ids;

        if ($moveIds === []) {
            throw new LogicException('The opponent has no moves to use.');
        }

        return RandomHelper::pick($moveIds);
    }

    /**
     * @param  list<string>  $moveIds
     */
    private function loadMovesAndSpeciesNames(array $moveIds): void
    {
        $this->moves = Move::query()->whereKey($moveIds)->get()->keyBy('id');

        $speciesIds = [
            $this->state->player_side->activeCombatant()->species_id,
            ...array_map(fn (Combatant $combatant) => $combatant->species_id, $this->state->opponent_side->combatants),
        ];

        $this->speciesNames = Species::query()->whereKey($speciesIds)->pluck('name', 'id')->all();
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
    private function act(string $side, string $moveId): void
    {
        $attacker = $this->activeCombatant($side);
        $defender = $this->activeCombatant($this->otherSide($side));

        if ($attacker->isFainted()) {
            return;
        }

        $move = $this->moves->findOrFail($moveId);

        $this->logLines[] = "{$this->displayName($side, $attacker, atStart: true)} used {$move->name}!";

        if ($move->power === null) {
            return;
        }

        $damage = new DamageCalculator()->calculate(
            attackerStats: new StatsCalculator()->calculate($attacker->level, $attacker->boost, $attacker->is_shiny),
            attackerTypes: $attacker->types,
            defenderStats: new StatsCalculator()->calculate($defender->level, $defender->boost, $defender->is_shiny),
            defenderTypes: $defender->types,
            move: $move,
            isCriticalHit: false,
            randomFactor: 1.0,
        );

        $this->logEffectiveness(new DamageCalculator()->typeMultiplier($move->type, ...$defender->types), $this->otherSide($side), $defender);

        $damagedDefender = $defender->withCurrentHp(max(0, $defender->current_hp - $damage));

        $this->replaceActiveCombatant($this->otherSide($side), $damagedDefender);

        if ($damagedDefender->isFainted()) {
            $this->logLines[] = "{$this->displayName($this->otherSide($side), $damagedDefender, atStart: true)} fainted!";
        }
    }

    /**
     * @param  'player'|'opponent'  $defenderSide
     */
    private function logEffectiveness(float $typeMultiplier, string $defenderSide, Combatant $defender): void
    {
        if ($typeMultiplier === 0.0) {
            $this->logLines[] = "It doesn't affect {$this->displayName($defenderSide, $defender, atStart: false)}...";
        } elseif ($typeMultiplier > 1.0) {
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
        $name = $this->speciesNames[$combatant->species_id];
        $name = $combatant->is_shiny ? "Shiny {$name}" : $name;

        if ($side === 'opponent' && $this->battle->type === BattleType::WILD) {
            return ($atStart ? 'The wild ' : 'the wild ') . $name;
        }

        return $name;
    }
}
