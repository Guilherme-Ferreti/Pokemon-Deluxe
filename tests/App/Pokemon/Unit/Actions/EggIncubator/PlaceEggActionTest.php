<?php

declare(strict_types=1);

use App\Pokemon\Actions\Incubator\PlaceEggAction;
use App\Pokemon\Exceptions\IncubatorNotIdleException;
use Illuminate\Support\Lottery;
use Shared\Enums\PokemonGender;
use Shared\Models\Incubation;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\travelTo;

beforeEach(function () {
    User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
});

it('creates an incubation on an idle incubator', function () {
    travelTo('2026-01-01 12:00:00');

    $user    = User::first();
    $species = Species::factory()->create(['egg_obtainable' => true]);

    new PlaceEggAction()->handle($user, new IncubatorIndex(1));

    assertDatabaseCount(Incubation::class, 1);

    assertDatabaseHas(Incubation::class, [
        'user_id'           => $user->id,
        'incubator_index'   => 1,
        'species_id'        => $species->id,
        'ready_to_hatch_at' => '2026-01-02 12:00:00',
        'hatched_at'        => null,
        'caught_at'         => null,
    ]);
});

it('does not place an egg when the incubator is already incubating', function () {
    $user = User::first();

    Incubation::factory()->for($user)->incubating()->create(['incubator_index' => new IncubatorIndex(1)]);

    expect(fn () => new PlaceEggAction()->handle($user, new IncubatorIndex(1)))
        ->toThrow(IncubatorNotIdleException::class);

    assertDatabaseCount(Incubation::class, 1);
});

it('does not place an egg when the incubator has an egg ready to hatch', function () {
    $user = User::first();

    Incubation::factory()->for($user)->readyToHatch()->create(['incubator_index' => new IncubatorIndex(1)]);

    expect(fn () => new PlaceEggAction()->handle($user, new IncubatorIndex(1)))
        ->toThrow(IncubatorNotIdleException::class);

    assertDatabaseCount(Incubation::class, 1);
});

it('does not place an egg when the incubator has an egg ready to catch', function () {
    $user = User::first();

    Incubation::factory()->for($user)->readyToCatch()->create(['incubator_index' => new IncubatorIndex(1)]);

    expect(fn () => new PlaceEggAction()->handle($user, new IncubatorIndex(1)))
        ->toThrow(IncubatorNotIdleException::class);

    assertDatabaseCount(Incubation::class, 1);
});

it('does not place an egg in an incubator the user has not purchased', function () {
    $user = User::first();

    expect(fn () => new PlaceEggAction()->handle($user, new IncubatorIndex(2)))
        ->toThrow(IncubatorNotIdleException::class);

    assertDatabaseCount(Incubation::class, 0);
});

it('places an egg in an incubator if the incubation process is complete', function () {
    $user    = User::first();
    $species = Species::factory()->create(['egg_obtainable' => true]);

    Incubation::factory()->for($user)->completed()->create(['incubator_index' => new IncubatorIndex(1)]);

    new PlaceEggAction()->handle($user, new IncubatorIndex(1));

    assertDatabaseCount(Incubation::class, 2);
});

it('assigns the egg a gender the species can have', function (int $genderRate, array $possibleGenders) {
    $user    = User::first();
    $species = Species::factory()->create(['egg_obtainable' => true, 'gender_rate' => $genderRate]);

    new PlaceEggAction()->handle($user, new IncubatorIndex(1));

    $incubation = $user->Incubations()->sole();

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

    $user = User::first();
    Species::factory()->create(['egg_obtainable' => true, 'gender_rate' => -1]);

    new PlaceEggAction()->handle($user, new IncubatorIndex(1));

    expect($user->Incubations()->sole()->is_shiny)->toBeTrue();
});

it('does not mark the egg as shiny when the shiny lottery loses', function () {
    Lottery::alwaysLose();

    $user = User::first();
    Species::factory()->create(['egg_obtainable' => true, 'gender_rate' => -1]);

    new PlaceEggAction()->handle($user, new IncubatorIndex(1));

    expect($user->Incubations()->sole()->is_shiny)->toBeFalse();
});
