<?php

declare(strict_types=1);

use App\Pokemon\Actions\CreatePokemonAction;
use App\Pokemon\Exceptions\BoxFullException;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\SpeciesLearnableMove;
use Shared\Models\User;
use Shared\ValueObjects\NewPokemon;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\travelTo;

it('creates a pokemon', function () {
    travelTo('2026-01-01 12:00:00');

    $user     = User::factory()->create();
    $species  = Species::factory()->create();
    $pokeball = Pokeball::factory()->create();
    $gender   = $species->getRandomGender();

    new CreatePokemonAction()->handle(
        owner: $user,
        newPokemon: new NewPokemon(
            species: $species,
            level: 50,
            is_shiny: false,
            gender: $gender,
        ),
        pokeball: $pokeball,
    );

    assertDatabaseHas(Pokemon::class, [
        'user_id'          => $user->id,
        'original_user_id' => $user->id,
        'species_id'       => $species->id,
        'level'            => 50,
        'experience'       => 50_000,
        'is_shiny'         => false,
        'gender'           => $gender,
        'pokeball_id'      => $pokeball->id,
        'caught_at'        => '2026-01-01 12:00:00',
    ]);
});

it('adds four base moves to the created pokemon', function () {
    $user     = User::factory()->create();
    $species  = Species::factory()->create();
    $pokeball = Pokeball::factory()->create();

    $baseMoves   = Move::factory()->count(4)->create();
    $nonBaseMove = Move::factory()->create();

    $baseMoves->each(fn (Move $move) => SpeciesLearnableMove::factory()->for($species)->for($move)->create(['is_base_move' => true]));
    SpeciesLearnableMove::factory()->for($species)->for($nonBaseMove)->create(['is_base_move' => false]);

    $pokemon = new CreatePokemonAction()->handle(
        owner: $user,
        newPokemon: new NewPokemon(
            species: $species,
            level: 1,
            is_shiny: false,
            gender: $species->getRandomGender(),
        ),
        pokeball: $pokeball,
    );

    assertDatabaseCount('pokemon_move', 4);

    $baseMoves->each(fn (Move $move) => assertDatabaseHas('pokemon_move', [
        'pokemon_id' => $pokemon->id,
        'move_id'    => $move->id,
    ]));

    assertDatabaseMissing('pokemon_move', [
        'pokemon_id' => $pokemon->id,
        'move_id'    => $nonBaseMove->id,
    ]);
});

it('does not create a pokemon if the owner\'s box is full', function () {
    config()->set('game.user.max_box_size', 1);

    $user     = User::factory()->create();
    $species  = Species::factory()->create();
    $pokeball = Pokeball::factory()->create();

    Pokemon::factory()->for($user)->create();

    expect(fn () => new CreatePokemonAction()->handle(
        owner: $user,
        newPokemon: new NewPokemon(
            species: $species,
            level: 1,
            is_shiny: false,
            gender: $species->getRandomGender(),
        ),
        pokeball: $pokeball,
    ))->toThrow(BoxFullException::class);

    assertDatabaseCount(Pokemon::class, 1);
});
