<?php

declare(strict_types=1);

use App\Pokemon\Actions\Incubator\CatchPokemonAction;
use App\Pokemon\Exceptions\IncubatorEggNotReadyToCatchException;
use App\Pokemon\Exceptions\IncubatorNotActiveException;
use Shared\Enums\PokemonGender;
use Shared\Models\Incubation;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\SpeciesLearnableMove;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\travelTo;

it('catches a ready-to-catch egg and adds the pokemon to the user', function () {
    travelTo('2026-01-01 12:00:00');

    $user     = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $species  = Species::factory()->create();
    $pokeball = Pokeball::factory()->create();

    InventoryItem::factory()->for($user)->pokeball($pokeball)->create();

    $incubation = Incubation::factory()->for($user)->for($species)->readyToCatch()->create([
        'incubator_index' => new IncubatorIndex(1),
        'gender'          => PokemonGender::FEMALE,
        'is_shiny'        => true,
    ]);

    $pokemon = new CatchPokemonAction()->handle($user, new IncubatorIndex(1), $pokeball);

    expect($pokemon->user_id)->toBe($user->id)
        ->and($pokemon->original_user_id)->toBe($user->id)
        ->and($pokemon->species_id)->toBe($species->id)
        ->and($pokemon->level)->toBe(1)
        ->and($pokemon->experience)->toBe(0)
        ->and($pokemon->in_party)->toBeFalse()
        ->and($pokemon->is_shiny)->toBeTrue()
        ->and($pokemon->gender)->toBe(PokemonGender::FEMALE)
        ->and($pokemon->pokeball_id)->toBe($pokeball->id)
        ->and($pokemon->caught_at->toDateTimeString())->toBe('2026-01-01 12:00:00');

    assertDatabaseCount(Pokemon::class, 1);
    assertDatabaseCount(Incubation::class, 1);

    assertDatabaseHas(Incubation::class, [
        'id'        => $incubation->id,
        'caught_at' => '2026-01-01 12:00:00',
    ]);

    assertDatabaseHas(Pokemon::class, [
        'id'               => $pokemon->id,
        'user_id'          => $user->id,
        'original_user_id' => $user->id,
        'species_id'       => $species->id,
        'level'            => 1,
        'experience'       => 0,
        'in_party'         => false,
        'is_shiny'         => true,
        'gender'           => PokemonGender::FEMALE->value,
        'pokeball_id'      => $pokeball->id,
        'caught_at'        => '2026-01-01 12:00:00',
    ]);
});

it('teaches the caught pokemon up to four base moves', function () {
    $user     = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $species  = Species::factory()->create();
    $pokeball = Pokeball::factory()->create();

    InventoryItem::factory()->for($user)->pokeball($pokeball)->create();

    $baseMoves = Move::factory()->count(4)->create();

    foreach ($baseMoves as $baseMove) {
        SpeciesLearnableMove::factory()->for($species)->for($baseMove, 'move')->create([
            'is_base_move' => true,
        ]);
    }

    $nonBaseMove = Move::factory()->create();

    SpeciesLearnableMove::factory()->for($species)->for($nonBaseMove, 'move')->create([
        'is_base_move' => false,
    ]);

    Incubation::factory()->for($user)->for($species)->readyToCatch()->create([
        'incubator_index' => new IncubatorIndex(1),
    ]);

    $pokemon = new CatchPokemonAction()->handle($user, new IncubatorIndex(1), $pokeball);

    $attachedMoveIds = $pokemon->moves()->pluck('moves.id')->all();

    expect($attachedMoveIds)
        ->toHaveCount(4)
        ->not->toContain($nonBaseMove->id)
        ->and(array_diff($attachedMoveIds, $baseMoves->pluck('id')->all()))->toBeEmpty();
});

it('does not catch an egg when the incubator has no active incubation', function () {
    $user     = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $pokeball = Pokeball::factory()->create();

    expect(fn () => new CatchPokemonAction()->handle($user, new IncubatorIndex(1), $pokeball))
        ->toThrow(IncubatorNotActiveException::class);

    assertDatabaseCount(Pokemon::class, 0);
    assertDatabaseCount(Incubation::class, 0);
});

it('does not catch an egg that is not ready to catch', function (string $state) {
    $user     = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $pokeball = Pokeball::factory()->create();

    $incubation = Incubation::factory()->for($user)->{$state}()->create([
        'incubator_index' => new IncubatorIndex(1),
    ]);

    expect(fn () => new CatchPokemonAction()->handle($user, new IncubatorIndex(1), $pokeball))
        ->toThrow(IncubatorEggNotReadyToCatchException::class);

    assertDatabaseCount(Pokemon::class, 0);
    assertDatabaseCount(Incubation::class, 1);

    assertDatabaseHas(Incubation::class, [
        'id'        => $incubation->id,
        'caught_at' => null,
    ]);
})->with([
    'incubating egg'     => ['incubating'],
    'egg ready to hatch' => ['readyToHatch'],
]);
