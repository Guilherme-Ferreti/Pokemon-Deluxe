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
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Exceptions\GameDataException;
use Shared\Models\Battle;
use Shared\Models\Move;
use Shared\Models\Pokemon;
use Shared\Models\User;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;

final class StartWildBattleAction
{
    private User $user;

    private NewPokemon $wildPokemon;

    /** @var EloquentCollection<int, Pokemon> */
    private EloquentCollection $partyPokemon;

    public function handle(User $user, NewPokemon $wildPokemon): Battle
    {
        $this->user        = $user;
        $this->wildPokemon = $wildPokemon;

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
                player_side: new BattleSide(combatants: $this->buildPlayerCombatants(), active_index: 0),
                opponent_side: new BattleSide(combatants: [$this->buildWildCombatant()], active_index: 0),
                is_awaiting_send_out: false,
                log: [
                    "A wild {$this->wildPokemon->species->getDisplayName($this->wildPokemon->is_shiny)} appeared!",
                    "Go! {$this->partyPokemon->firstOrFail()->getDisplayName()}!",
                ],
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
            pokemon_id: $pokemon->id,
            species_id: $pokemon->species_id,
            level: $pokemon->level,
            boost: $pokemon->boost,
            is_shiny: $pokemon->is_shiny,
            gender: $pokemon->gender,
            types: array_values($pokemon->species->types->all()),
            move_ids: array_values($pokemon->activeMoves->map(fn (Move $move): string => $move->id)->all()),
            max_hp: $maxHp,
            current_hp: $maxHp,
            ailment: null,
            ailment_turns_left: 0,
            confusion_turns_left: 0,
            has_entered_field: $isFirst,
            used_once_per_battle_move_ids: [],
        );
    }

    private function buildWildCombatant(): Combatant
    {
        $species = $this->wildPokemon->species;
        $maxHp   = new StatsCalculator()->calculate($this->wildPokemon->level, boost: 0, isShiny: $this->wildPokemon->is_shiny)->hp;

        return new Combatant(
            pokemon_id: null,
            species_id: $species->id,
            level: $this->wildPokemon->level,
            boost: 0,
            is_shiny: $this->wildPokemon->is_shiny,
            gender: $this->wildPokemon->gender,
            types: array_values($species->types->all()),
            move_ids: $this->wildMoveIds(),
            max_hp: $maxHp,
            current_hp: $maxHp,
            ailment: null,
            ailment_turns_left: 0,
            confusion_turns_left: 0,
            has_entered_field: true,
            used_once_per_battle_move_ids: [],
        );
    }

    /**
     * @return list<string>
     */
    private function wildMoveIds(): array
    {
        $species = $this->wildPokemon->species;
        $moveIds = $species->baseMoves()
            ->take((int) config('game.pokemon.max_active_moves'))
            ->get(['moves.id'])
            ->map(fn (Move $move): string => $move->id);

        if ($moveIds->isNotEmpty()) {
            return array_values($moveIds->all());
        }

        report(GameDataException::speciesWithoutBaseMoves($species));

        return [Move::query()->where('slug', config('game.pokemon.default_base_move'))->firstOrFail()->id];
    }
}
