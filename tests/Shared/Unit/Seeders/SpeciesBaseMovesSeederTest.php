<?php

declare(strict_types=1);

use App\Pokemon\Actions\CreatePokemonAction;
use App\Pokemon\ValueObjects\NewPokemon;
use Database\Seeders\EvolutionItemSeeder;
use Database\Seeders\MoveSeeder;
use Database\Seeders\SpeciesBaseMovesSeeder;
use Database\Seeders\SpeciesSeeder;
use Illuminate\Support\Facades\DB;
use Shared\Enums\PokemonGender;
use Shared\Models\Pokeball;
use Shared\Models\Species;
use Shared\Models\User;

use function Pest\Laravel\seed;

it('gives every species exactly four different base moves', function () {
    seed([EvolutionItemSeeder::class, MoveSeeder::class, SpeciesSeeder::class, SpeciesBaseMovesSeeder::class]);

    $species = Species::query()->withCount('baseMoves')->get();

    expect($species)->not->toBeEmpty()
        ->and($species->where('base_moves_count', '!=', 4)->pluck('slug')->all())->toBeEmpty()
        ->and(DB::table('species_base_move')->groupBy('species_id', 'move_id')->havingRaw('count(*) > 1')->exists())->toBeFalse();
});

it('gives a new Ditto four active moves', function () {
    seed([EvolutionItemSeeder::class, MoveSeeder::class, SpeciesSeeder::class, SpeciesBaseMovesSeeder::class]);

    $ditto = Species::query()->where('slug', 'ditto')->firstOrFail();

    $pokemon = new CreatePokemonAction()->handle(
        User::factory()->create(),
        new NewPokemon(species: $ditto, level: 5, isShiny: false, gender: PokemonGender::GENDERLESS),
        Pokeball::factory()->create(),
    );

    expect($pokemon->knownMoves()->wherePivotNotNull('slot')->count())->toBe(4);
});

it('fails the seed when a species has no base moves', function () {
    seed([EvolutionItemSeeder::class, MoveSeeder::class, SpeciesSeeder::class]);

    Species::factory()->create(['slug' => 'missingno', 'pokedex_number' => 9_999, 'pokedex_order' => 99_990]);

    seed(SpeciesBaseMovesSeeder::class);
})->throws(RuntimeException::class, 'Species [missingno] must have exactly 4 different base moves, [0] given: [].');
