<?php

declare(strict_types=1);

use App\Pokemon\Actions\CreatePokemonAction;
use App\Pokemon\Exceptions\BoxFullException;
use Illuminate\Support\Facades\Exceptions;
use Shared\Exceptions\GameDataException;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\NewPokemon;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\travelTo;

it('creates a pokemon', function () {
    travelTo('2026-01-01 12:00:00');

    $user     = User::factory()->create();
    $species  = Species::factory()->withBaseMoves()->create();
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
        'boost'            => 0,
        'is_shiny'         => false,
        'gender'           => $gender,
        'pokeball_id'      => $pokeball->id,
        'caught_at'        => '2026-01-01 12:00:00',
    ]);
});

it('puts the species base moves in active slots 1 to 4', function () {
    Exceptions::fake();

    $species     = Species::factory()->create();
    $baseMoves   = Move::factory()->count(4)->create();
    $nonBaseMove = Move::factory()->create();

    $species->baseMoves()->attach($baseMoves);

    $pokemon = new CreatePokemonAction()->handle(
        owner: User::factory()->create(),
        newPokemon: new NewPokemon(
            species: $species,
            level: 1,
            is_shiny: false,
            gender: $species->getRandomGender(),
        ),
        pokeball: Pokeball::factory()->create(),
    );

    assertDatabaseCount('pokemon_move', 4);

    expect($pokemon->activeMoves->pluck('pivot.slot')->all())->toBe([1, 2, 3, 4])
        ->and($pokemon->activeMoves->pluck('id')->all())->toEqualCanonicalizing($baseMoves->pluck('id')->all())
        ->and($pokemon->knownMoves->pluck('id')->all())->not->toContain($nonBaseMove->id);

    Exceptions::assertNothingReported();
});

it('gives only four active moves when the species has more base moves', function () {
    $species = Species::factory()->create();

    $species->baseMoves()->attach(Move::factory()->count(5)->create());

    $pokemon = new CreatePokemonAction()->handle(
        owner: User::factory()->create(),
        newPokemon: new NewPokemon(
            species: $species,
            level: 1,
            is_shiny: false,
            gender: $species->getRandomGender(),
        ),
        pokeball: Pokeball::factory()->create(),
    );

    assertDatabaseCount('pokemon_move', 4);

    expect($pokemon->activeMoves->pluck('pivot.slot')->all())->toBe([1, 2, 3, 4]);
});

it('gives as many active moves as the species has base moves, without repeating them', function () {
    $species   = Species::factory()->create();
    $baseMoves = Move::factory()->count(2)->create();

    $species->baseMoves()->attach($baseMoves);

    $pokemon = new CreatePokemonAction()->handle(
        owner: User::factory()->create(),
        newPokemon: new NewPokemon(
            species: $species,
            level: 1,
            is_shiny: false,
            gender: $species->getRandomGender(),
        ),
        pokeball: Pokeball::factory()->create(),
    );

    assertDatabaseCount('pokemon_move', 2);

    expect($pokemon->activeMoves->pluck('pivot.slot')->all())->toBe([1, 2])
        ->and($pokemon->activeMoves->pluck('id')->all())->toEqualCanonicalizing($baseMoves->pluck('id')->all());
});

it('gives the default base move in slot 1 and reports a game data error when the species has no base moves', function () {
    Exceptions::fake();

    $species     = Species::factory()->create();
    $defaultMove = Move::factory()->create(['slug' => config('game.pokemon.default_base_move')]);

    $pokemon = new CreatePokemonAction()->handle(
        owner: User::factory()->create(),
        newPokemon: new NewPokemon(
            species: $species,
            level: 1,
            is_shiny: false,
            gender: $species->getRandomGender(),
        ),
        pokeball: Pokeball::factory()->create(),
    );

    assertDatabaseCount(Pokemon::class, 1);
    assertDatabaseCount('pokemon_move', 1);

    assertDatabaseHas('pokemon_move', [
        'pokemon_id' => $pokemon->id,
        'move_id'    => $defaultMove->id,
        'slot'       => 1,
    ]);

    Exceptions::assertReported(fn (GameDataException $exception) => $exception->getMessage() === "Species [{$species->slug}] has no base moves.");
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
