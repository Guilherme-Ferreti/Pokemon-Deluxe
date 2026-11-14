<?php

declare(strict_types=1);

use App\Pokemon\Actions\Incubator\PlaceEggAction;
use App\Pokemon\Exceptions\IncubatorNotIdleException;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonGenderRate;
use Shared\Helpers\RandomHelper;
use Shared\Models\Incubation;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\travelTo;

it('creates an incubation on an idle incubator', function () {
    travelTo('2026-01-01 12:00:00');

    $user    = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
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
    $user       = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $incubation = Incubation::factory()->for($user)->incubating()->create();

    expect(fn () => new PlaceEggAction()->handle($user, $incubation->incubator_index))
        ->toThrow(IncubatorNotIdleException::class);

    assertDatabaseCount(Incubation::class, 1);
});

it('does not place an egg when the incubator has an egg ready to hatch', function () {
    $user       = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $incubation = Incubation::factory()->for($user)->readyToHatch()->create();

    expect(fn () => new PlaceEggAction()->handle($user, $incubation->incubator_index))
        ->toThrow(IncubatorNotIdleException::class);

    assertDatabaseCount(Incubation::class, 1);
});

it('does not place an egg when the incubator has an egg ready to catch', function () {
    $user       = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $incubation = Incubation::factory()->for($user)->readyToCatch()->create();

    expect(fn () => new PlaceEggAction()->handle($user, $incubation->incubator_index))
        ->toThrow(IncubatorNotIdleException::class);

    assertDatabaseCount(Incubation::class, 1);
});

it('does not place an egg in an incubator the user has not purchased', function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);

    expect(fn () => new PlaceEggAction()->handle($user, new IncubatorIndex(2)))
        ->toThrow(IncubatorNotIdleException::class);

    assertDatabaseCount(Incubation::class, 0);
});

it('places an egg in an incubator if the incubation process is complete', function () {
    $user       = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $incubation = Incubation::factory()->for($user)->completed()->create();

    new PlaceEggAction()->handle($user, $incubation->incubator_index);

    assertDatabaseCount(Incubation::class, 2);
});

it('assigns the egg a gender the species can have', function (PokemonGenderRate $genderRate, array $possibleGenders) {
    $user    = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $species = Species::factory()->create(['egg_obtainable' => true, 'gender_rate' => $genderRate]);

    new PlaceEggAction()->handle($user, new IncubatorIndex(1));

    $incubation = $user->incubations()->sole();

    expect($incubation->species_id)->toBe($species->id)
        ->and($incubation->gender)->toBeIn($possibleGenders);
})->with([
    'genderless species'     => [PokemonGenderRate::GENDERLESS, [PokemonGender::GENDERLESS]],
    'male-only species'      => [PokemonGenderRate::ONLY_MALE, [PokemonGender::MALE]],
    'female-only species'    => [PokemonGenderRate::ONLY_FEMALE, [PokemonGender::FEMALE]],
    'male-or-female species' => [PokemonGenderRate::EQUAL, [PokemonGender::MALE, PokemonGender::FEMALE]],
]);

it('marks the egg as shiny when the shiny roll succeeds', function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    Species::factory()->create(['egg_obtainable' => true, 'gender_rate' => PokemonGenderRate::GENDERLESS]);

    RandomHelper::fake([true]);

    new PlaceEggAction()->handle($user, new IncubatorIndex(1));

    expect($user->incubations()->sole()->is_shiny)->toBeTrue();
});

it('does not mark the egg as shiny when the shiny roll fails', function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    Species::factory()->create(['egg_obtainable' => true, 'gender_rate' => PokemonGenderRate::GENDERLESS]);

    RandomHelper::fake([false]);

    new PlaceEggAction()->handle($user, new IncubatorIndex(1));

    expect($user->incubations()->sole()->is_shiny)->toBeFalse();
});
