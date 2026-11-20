<?php

declare(strict_types=1);

use App\Battle\Actions\StartWildBattleAction;
use App\Battle\Exceptions\BattleAlreadyInProgressException;
use App\Battle\Exceptions\EmptyPartyException;
use App\Pokemon\ValueObjects\NewPokemon;
use Shared\Enums\Area;
use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonGenderRate;
use Shared\Enums\PokemonType;
use Shared\Models\Battle;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\BattleLogEntry;

use function Pest\Laravel\assertDatabaseCount;

it('starts a wild battle with the party against the wild pokemon', function () {
    $user           = User::factory()->create();
    $pikachuSpecies = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $pidgeySpecies  = Species::factory()->withBaseMoves(5)->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING], 'gender_rate' => PokemonGenderRate::EQUAL]);

    $secondPartyPokemon = Pokemon::factory()->for($user)->for($pikachuSpecies)->inParty()->withActiveMoves(2)->create(['caught_at' => '2026-02-01', 'level' => 30, 'boost' => 0, 'is_shiny' => false]);
    $firstPartyPokemon  = Pokemon::factory()->for($user)->for($pikachuSpecies)->inParty()->withActiveMoves(4)->create(['caught_at' => '2026-01-01', 'level' => 50, 'boost' => 20, 'is_shiny' => true]);
    Pokemon::factory()->for($user)->inBox()->withActiveMoves()->create();

    $battle = new StartWildBattleAction()->handle($user, new NewPokemon(
        species: $pidgeySpecies,
        level: 12,
        is_shiny: false,
        gender: PokemonGender::FEMALE,
    ), Area::COAST);

    $state          = $battle->fresh()?->state;
    $playerSide     = $state?->player_side;
    $wildCombatant  = $state?->opponent_side->activeCombatant();
    $firstCombatant = $playerSide?->activeCombatant();

    expect($battle->user_id)->toBe($user->id)
        ->and($battle->type)->toBe(BattleType::WILD)
        ->and($battle->status)->toBe(BattleStatus::ONGOING)
        ->and($state?->turn)->toBe(1)
        ->and($state?->is_awaiting_send_out)->toBeFalse()
        ->and($state?->area)->toBe(Area::COAST)
        ->and($state?->log)->toEqual([
            new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pidgey appeared!'),
            new BattleLogEntry(0, BattleLogKind::EVENT, 'Go! Shiny Pikachu!'),
        ])
        ->and(array_map(fn ($combatant) => $combatant->pokemon_id, $playerSide->combatants ?? []))->toBe([$firstPartyPokemon->id, $secondPartyPokemon->id])
        ->and($playerSide?->active_index)->toBe(0)
        ->and($firstCombatant?->level)->toBe(50)
        ->and($firstCombatant?->boost)->toBe(20)
        ->and($firstCombatant?->is_shiny)->toBeTrue()
        ->and($firstCombatant?->types)->toBe([PokemonType::ELECTRIC])
        ->and($firstCombatant?->move_ids)->toBe($firstPartyPokemon->activeMoves()->pluck('moves.id')->all())
        ->and($firstCombatant?->max_hp)->toBe($firstPartyPokemon->getStats()->hp)
        ->and($firstCombatant?->current_hp)->toBe($firstPartyPokemon->getStats()->hp)
        ->and($firstCombatant?->has_entered_field)->toBeTrue()
        ->and($playerSide?->combatants[1]->has_entered_field)->toBeFalse()
        ->and($wildCombatant?->pokemon_id)->toBeNull()
        ->and($wildCombatant?->species_id)->toBe($pidgeySpecies->id)
        ->and($wildCombatant?->level)->toBe(12)
        ->and($wildCombatant?->boost)->toBe(0)
        ->and($wildCombatant?->gender)->toBe(PokemonGender::FEMALE)
        ->and($wildCombatant?->types)->toBe([PokemonType::NORMAL, PokemonType::FLYING])
        ->and($wildCombatant?->move_ids)->toHaveCount(4)
        ->and($pidgeySpecies->baseMoves()->pluck('moves.id')->all())->toContain(...$wildCombatant->move_ids ?? [])
        ->and($wildCombatant?->current_hp)->toBe($wildCombatant?->max_hp);
});

it('refuses to start a battle while another one is ongoing', function () {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->inParty()->withActiveMoves()->create();
    Battle::factory()->for($user)->create(['status' => BattleStatus::ONGOING]);

    expect(fn () => new StartWildBattleAction()->handle($user, new NewPokemon(
        species: Species::factory()->withBaseMoves()->create(['gender_rate' => PokemonGenderRate::EQUAL]),
        level: 5,
        is_shiny: false,
        gender: PokemonGender::MALE,
    ), Area::MEADOW))->toThrow(BattleAlreadyInProgressException::class);

    assertDatabaseCount(Battle::class, 1);
});

it('starts a battle when the previous one has ended', function () {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->inParty()->withActiveMoves()->create();
    Battle::factory()->for($user)->create(['status' => BattleStatus::WON]);

    new StartWildBattleAction()->handle($user, new NewPokemon(
        species: Species::factory()->withBaseMoves()->create(['gender_rate' => PokemonGenderRate::EQUAL]),
        level: 5,
        is_shiny: false,
        gender: PokemonGender::MALE,
    ), Area::MEADOW);

    assertDatabaseCount(Battle::class, 2);
});

it('refuses to start a battle with an empty party', function () {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->inBox()->withActiveMoves()->create();

    expect(fn () => new StartWildBattleAction()->handle($user, new NewPokemon(
        species: Species::factory()->withBaseMoves()->create(['gender_rate' => PokemonGenderRate::EQUAL]),
        level: 5,
        is_shiny: false,
        gender: PokemonGender::MALE,
    ), Area::MEADOW))->toThrow(EmptyPartyException::class);

    assertDatabaseCount(Battle::class, 0);
});
