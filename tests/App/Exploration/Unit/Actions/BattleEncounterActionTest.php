<?php

declare(strict_types=1);

use App\Battle\Exceptions\EmptyPartyException;
use App\Exploration\Actions\BattleEncounterAction;
use App\Exploration\Exceptions\NoEncounterException;
use Shared\Enums\Area;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonGenderRate;
use Shared\Models\Battle;
use Shared\Models\Encounter;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;

use function Pest\Laravel\assertDatabaseCount;

it('starts a wild battle against the encounter and deletes it', function () {
    $user    = User::factory()->create();
    $species = Species::factory()->withBaseMoves()->create(['gender_rate' => PokemonGenderRate::EQUAL]);
    Pokemon::factory()->for($user)->inParty()->withActiveMoves()->create();
    Encounter::factory()->for($user)->for($species)->create([
        'area'     => Area::FOREST,
        'level'    => 9,
        'is_shiny' => true,
        'gender'   => PokemonGender::FEMALE,
    ]);

    $battle = new BattleEncounterAction()->handle($user);

    $state         = $battle->fresh()?->state;
    $wildCombatant = $state?->opponent_side->activeCombatant();

    expect($battle->type)->toBe(BattleType::WILD)
        ->and($battle->status)->toBe(BattleStatus::ONGOING)
        ->and($state?->area)->toBe(Area::FOREST)
        ->and($wildCombatant?->species_id)->toBe($species->id)
        ->and($wildCombatant?->level)->toBe(9)
        ->and($wildCombatant?->is_shiny)->toBeTrue()
        ->and($wildCombatant?->gender)->toBe(PokemonGender::FEMALE);
    assertDatabaseCount(Battle::class, 1);
    assertDatabaseCount(Encounter::class, 0);
});

it('refuses to battle without an encounter', function () {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->inParty()->withActiveMoves()->create();

    expect(fn () => new BattleEncounterAction()->handle($user))->toThrow(NoEncounterException::class);

    assertDatabaseCount(Battle::class, 0);
});

it('keeps the encounter when the battle cannot start', function () {
    $user = User::factory()->create();
    Encounter::factory()->for($user)->create();

    expect(fn () => new BattleEncounterAction()->handle($user))->toThrow(EmptyPartyException::class);

    assertDatabaseCount(Battle::class, 0);
    assertDatabaseCount(Encounter::class, 1);
});
