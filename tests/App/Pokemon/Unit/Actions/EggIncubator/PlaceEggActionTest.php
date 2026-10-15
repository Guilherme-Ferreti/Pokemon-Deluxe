<?php

declare(strict_types=1);

use App\Pokemon\Actions\EggIncubator\PlaceEggAction;
use App\Pokemon\Exceptions\EggIncubatorNotIdleException;
use Illuminate\Support\Lottery;
use Shared\Enums\PokemonGender;
use Shared\Models\EggIncubation;
use Shared\Models\Species;
use Shared\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\travelTo;

afterEach(function () {
    Lottery::determineResultsNormally();
});

it('creates an incubation on an idle incubator', function () {
    travelTo('2026-01-01 12:00:00');

    $user    = User::factory()->create(['purchased_egg_incubator_indexes' => [1]]);
    $species = Species::factory()->create(['egg_obtainable' => true]);

    new PlaceEggAction()->handle($user, 1);

    assertDatabaseCount(EggIncubation::class, 1);

    assertDatabaseHas(EggIncubation::class, [
        'user_id'             => $user->id,
        'egg_incubator_index' => 1,
        'species_id'          => $species->id,
        'ready_to_hatch_at'   => '2026-01-02 12:00:00',
        'hatched_at'          => null,
        'captured_at'         => null,
    ]);
});

it('does not place an egg when the incubator is already incubating', function () {
    $user = User::factory()->create(['purchased_egg_incubator_indexes' => [1]]);

    EggIncubation::factory()->for($user)->incubating()->create(['egg_incubator_index' => 1]);

    expect(fn () => new PlaceEggAction()->handle($user, 1))
        ->toThrow(EggIncubatorNotIdleException::class);

    assertDatabaseCount(EggIncubation::class, 1);
});

it('does not place an egg when the incubator has an egg ready to hatch', function () {
    $user = User::factory()->create(['purchased_egg_incubator_indexes' => [1]]);

    EggIncubation::factory()->for($user)->readyToHatch()->create(['egg_incubator_index' => 1]);

    expect(fn () => new PlaceEggAction()->handle($user, 1))
        ->toThrow(EggIncubatorNotIdleException::class);

    assertDatabaseCount(EggIncubation::class, 1);
});

it('does not place an egg when the incubator has an egg ready to capture', function () {
    $user = User::factory()->create(['purchased_egg_incubator_indexes' => [1]]);

    EggIncubation::factory()->for($user)->readyToCapture()->create(['egg_incubator_index' => 1]);

    expect(fn () => new PlaceEggAction()->handle($user, 1))
        ->toThrow(EggIncubatorNotIdleException::class);

    assertDatabaseCount(EggIncubation::class, 1);
});

it('does not place an egg in an incubator the user has not purchased', function () {
    $user = User::factory()->create(['purchased_egg_incubator_indexes' => [1]]);

    expect(fn () => new PlaceEggAction()->handle($user, 2))
        ->toThrow(EggIncubatorNotIdleException::class);

    assertDatabaseCount(EggIncubation::class, 0);
});

it('places an egg in an incubator if the incubation process is complete', function () {
    $user    = User::factory()->create(['purchased_egg_incubator_indexes' => [1]]);
    $species = Species::factory()->create(['egg_obtainable' => true]);

    EggIncubation::factory()->for($user)->completed()->create(['egg_incubator_index' => 1]);

    new PlaceEggAction()->handle($user, 1);

    assertDatabaseCount(EggIncubation::class, 2);
});

it('assigns the egg a gender the species can have', function (int $genderRate, array $possibleGenders) {
    $user    = User::factory()->create(['purchased_egg_incubator_indexes' => [1]]);
    $species = Species::factory()->create(['egg_obtainable' => true, 'gender_rate' => $genderRate]);

    new PlaceEggAction()->handle($user, 1);

    $incubation = $user->eggIncubations()->sole();

    expect($incubation->species_id)->toBe($species->id)
        ->and($incubation->gender)->toBeIn($possibleGenders);
})->with([
    'genderless species'     => [-1, [PokemonGender::GENDERLESS]],
    'all-male species'       => [0, [PokemonGender::MALE]],
    'all-female species'     => [8, [PokemonGender::FEMALE]],
    'male-or-female species' => [4, [PokemonGender::MALE, PokemonGender::FEMALE]],
]);

it('marks the egg as shiny when the shiny lottery wins', function () {
    Lottery::alwaysWin();

    $user = User::factory()->create(['purchased_egg_incubator_indexes' => [1]]);
    Species::factory()->create(['egg_obtainable' => true, 'gender_rate' => -1]);

    new PlaceEggAction()->handle($user, 1);

    expect($user->eggIncubations()->sole()->is_shiny)->toBeTrue();
});

it('does not mark the egg as shiny when the shiny lottery loses', function () {
    Lottery::alwaysLose();

    $user = User::factory()->create(['purchased_egg_incubator_indexes' => [1]]);
    Species::factory()->create(['egg_obtainable' => true, 'gender_rate' => -1]);

    new PlaceEggAction()->handle($user, 1);

    expect($user->eggIncubations()->sole()->is_shiny)->toBeFalse();
});
