<?php

declare(strict_types=1);

namespace App\Battle\Actions;

use App\Battle\Exceptions\BattleAlreadyInProgressException;
use App\Battle\Exceptions\EmptyPartyException;
use App\Battle\Support\LockKeys;
use App\Pokemon\Mechanics\StatsCalculator;
use App\Pokemon\ValueObjects\NewPokemon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Shared\Enums\Area;
use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Models\Battle;
use Shared\Models\Move;
use Shared\Models\Pokemon;
use Shared\Models\User;
use Shared\ValueObjects\BattleLogEntry;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;

final class StartWildBattleAction
{
    private User $user;

    private NewPokemon $wildPokemon;

    private Area $area;

    /** @var EloquentCollection<int, Pokemon> */
    private EloquentCollection $partyPokemon;

    public function handle(User $user, NewPokemon $wildPokemon, Area $area): Battle
    {
        $this->user        = $user;
        $this->wildPokemon = $wildPokemon;
        $this->area        = $area;

        return Cache::withoutOverlapping(
            key: LockKeys::startBattle($user->id),
            callback: fn () => $this->startBattle(),
        );
    }

    private function startBattle(): Battle
    {
        $this->ensureNoBattleIsOngoing();

        $this->loadPartyPokemon();

        $this->ensurePartyIsNotEmpty();

        return Battle::create([
            'user_id' => $this->user->id,
            'type'    => BattleType::WILD,
            'status'  => BattleStatus::ONGOING,
            'state'   => new BattleState(
                turn: 1,
                playerSide: new BattleSide(combatants: $this->buildPlayerCombatants(), activeIndex: 0),
                opponentSide: new BattleSide(combatants: [$this->buildWildCombatant()], activeIndex: 0),
                isAwaitingSendOut: false,
                log: [
                    new BattleLogEntry(0, BattleLogKind::INFO, "A wild {$this->wildPokemon->species->getDisplayName($this->wildPokemon->isShiny)} appeared!"),
                    new BattleLogEntry(0, BattleLogKind::EVENT, "Go! {$this->partyPokemon->firstOrFail()->getDisplayName()}!"),
                ],
                area: $this->area,
            ),
        ]);
    }

    private function ensureNoBattleIsOngoing(): void
    {
        $hasOngoingBattle = $this->user->battles()->ongoing()->exists();

        throw_if($hasOngoingBattle, BattleAlreadyInProgressException::class);
    }

    private function ensurePartyIsNotEmpty(): void
    {
        throw_if($this->partyPokemon->isEmpty(), EmptyPartyException::class);
    }

    private function loadPartyPokemon(): void
    {
        $this->partyPokemon = $this->user->pokemon()
            ->inParty()
            ->with('species:id,name,types', 'activeMoves:id')
            ->orderBy('caught_at')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return list<Combatant>
     */
    private function buildPlayerCombatants(): array
    {
        return array_values($this->partyPokemon
            ->map(fn (Pokemon $pokemon, int $index) => $this->buildPlayerCombatant($pokemon, isFirst: $index === 0))
            ->all());
    }

    private function buildPlayerCombatant(Pokemon $pokemon, bool $isFirst): Combatant
    {
        $maxHp = $pokemon->getStats()->hp;

        return new Combatant(
            pokemonId: $pokemon->id,
            speciesId: $pokemon->species_id,
            level: $pokemon->level,
            boost: $pokemon->boost,
            isShiny: $pokemon->is_shiny,
            gender: $pokemon->gender,
            types: array_values($pokemon->species->types->all()),
            moveIds: array_values($pokemon->activeMoves->map(fn (Move $move) => $move->id)->all()),
            maxHp: $maxHp,
            currentHp: $maxHp,
            ailment: null,
            ailmentTurnsLeft: 0,
            confusionTurnsLeft: 0,
            hasEnteredField: $isFirst,
            usedOncePerBattleMoveIds: [],
        );
    }

    private function buildWildCombatant(): Combatant
    {
        $species = $this->wildPokemon->species;
        $maxHp   = new StatsCalculator()->calculate($this->wildPokemon->level, boost: 0, isShiny: $this->wildPokemon->isShiny)->hp;

        return new Combatant(
            pokemonId: null,
            speciesId: $species->id,
            level: $this->wildPokemon->level,
            boost: 0,
            isShiny: $this->wildPokemon->isShiny,
            gender: $this->wildPokemon->gender,
            types: array_values($species->types->all()),
            moveIds: $this->wildMoveIds(),
            maxHp: $maxHp,
            currentHp: $maxHp,
            ailment: null,
            ailmentTurnsLeft: 0,
            confusionTurnsLeft: 0,
            hasEnteredField: true,
            usedOncePerBattleMoveIds: [],
        );
    }

    /**
     * @return list<string>
     */
    private function wildMoveIds(): array
    {
        $moveIds = $this->wildPokemon->species->baseMoves()
            ->take((int) config('game.pokemon.max_active_moves'))
            ->get(['moves.id'])
            ->map(fn (Move $move) => $move->id);

        return array_values($moveIds->all());
    }
}
