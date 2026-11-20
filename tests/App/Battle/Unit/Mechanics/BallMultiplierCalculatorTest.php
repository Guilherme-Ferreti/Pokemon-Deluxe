<?php

declare(strict_types=1);

use App\Battle\Mechanics\BallMultiplierCalculator;
use App\Battle\ValueObjects\CatchContext;
use Database\Seeders\PokeballSeeder;
use Shared\Enums\Ailment;
use Shared\Enums\Area;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Models\EvolutionItem;
use Shared\Models\Pokeball;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;

use function Pest\Laravel\seed;

it('returns the multiplier of each ball without a condition', function (string $slug, float $expectedMultiplier) {
    seed(PokeballSeeder::class);

    $context = new CatchContext(
        turn: 1,
        area: Area::FOREST,
        isNight: false,
        wildSpecies: Species::factory()->make(),
        wildLevel: 10,
        wildGender: PokemonGender::MALE,
        wildAilment: null,
        activeSpecies: Species::factory()->make(),
        activeLevel: 20,
        activeGender: PokemonGender::FEMALE,
        playerOwnsSpecies: false,
    );

    $pokeball = Pokeball::query()->where('slug', $slug)->sole();

    expect(new BallMultiplierCalculator()->multiplier($pokeball, $context))->toBe($expectedMultiplier);
})->with([
    'poke ball'    => ['poke-ball', 1.0],
    'great ball'   => ['great-ball', 1.5],
    'ultra ball'   => ['ultra-ball', 2.0],
    'safari ball'  => ['safari-ball', 1.5],
    'sport ball'   => ['sport-ball', 1.5],
    'park ball'    => ['park-ball', 1.5],
    'luxury ball'  => ['luxury-ball', 1.0],
    'premier ball' => ['premier-ball', 1.0],
    'heal ball'    => ['heal-ball', 1.0],
    'friend ball'  => ['friend-ball', 1.0],
    'cherish ball' => ['cherish-ball', 1.0],
]);

it('guarantees a catch only with the master ball', function (string $slug, bool $expectedIsGuaranteed) {
    $pokeball = Pokeball::factory()->make(['slug' => $slug]);

    expect(new BallMultiplierCalculator()->isGuaranteed($pokeball))->toBe($expectedIsGuaranteed);
})->with([
    'master ball' => ['master-ball', true],
    'ultra ball'  => ['ultra-ball', false],
]);

it('boosts a conditional ball only when its condition applies', function (string $slug, array $contextOverrides, float $expectedMultiplier) {
    $pokeball = Pokeball::factory()->make(['slug' => $slug, 'catch_rate' => 1]);

    $context = new CatchContext(...[
        'turn'              => 5,
        'area'              => Area::MEADOW,
        'isNight'           => false,
        'wildSpecies'       => Species::factory()->create(['types' => [PokemonType::NORMAL]]),
        'wildLevel'         => 30,
        'wildGender'        => PokemonGender::MALE,
        'wildAilment'       => null,
        'activeSpecies'     => Species::factory()->create(),
        'activeLevel'       => 30,
        'activeGender'      => PokemonGender::MALE,
        'playerOwnsSpecies' => false,
        ...$contextOverrides,
    ]);

    expect(new BallMultiplierCalculator()->multiplier($pokeball, $context))->toEqualWithDelta($expectedMultiplier, 0.0001);
})->with([
    'quick ball on the first turn'                      => ['quick-ball', ['turn' => 1], 5.0],
    'quick ball after the first turn'                   => ['quick-ball', ['turn' => 2], 1.0],
    'timer ball on the first turn'                      => ['timer-ball', ['turn' => 1], 1.0],
    'timer ball on the second turn'                     => ['timer-ball', ['turn' => 2], 1.3],
    'timer ball on the eleventh turn'                   => ['timer-ball', ['turn' => 11], 4.0],
    'timer ball after its cap'                          => ['timer-ball', ['turn' => 20], 4.0],
    'repeat ball on an owned species'                   => ['repeat-ball', ['playerOwnsSpecies' => true], 3.5],
    'repeat ball on a new species'                      => ['repeat-ball', ['playerOwnsSpecies' => false], 1.0],
    'nest ball at level 2'                              => ['nest-ball', ['wildLevel' => 2], 3.0],
    'nest ball at level 11'                             => ['nest-ball', ['wildLevel' => 11], 2.0],
    'nest ball at level 20'                             => ['nest-ball', ['wildLevel' => 20], 1.0],
    'nest ball at level 40'                             => ['nest-ball', ['wildLevel' => 40], 1.0],
    'level ball at four times the level'                => ['level-ball', ['activeLevel' => 40, 'wildLevel' => 10], 8.0],
    'level ball at just under four times'               => ['level-ball', ['activeLevel' => 39, 'wildLevel' => 10], 4.0],
    'level ball at double the level'                    => ['level-ball', ['activeLevel' => 20, 'wildLevel' => 10], 4.0],
    'level ball at just under double'                   => ['level-ball', ['activeLevel' => 19, 'wildLevel' => 10], 2.0],
    'level ball at a higher level'                      => ['level-ball', ['activeLevel' => 11, 'wildLevel' => 10], 2.0],
    'level ball at the same level'                      => ['level-ball', ['activeLevel' => 10, 'wildLevel' => 10], 1.0],
    'dusk ball at night'                                => ['dusk-ball', ['isNight' => true], 3.0],
    'dusk ball in the cave'                             => ['dusk-ball', ['area' => Area::CAVE], 3.0],
    'dusk ball by day outside the cave'                 => ['dusk-ball', ['isNight' => false, 'area' => Area::MEADOW], 1.0],
    'dream ball on a sleeping pokemon'                  => ['dream-ball', ['wildAilment' => Ailment::SLEEP], 4.0],
    'dream ball on a paralyzed pokemon'                 => ['dream-ball', ['wildAilment' => Ailment::PARALYSIS], 1.0],
    'love ball on the same species and opposite gender' => [
        'love-ball',
        function (): array {
            $species = Species::factory()->create();

            return ['wildSpecies' => $species, 'wildGender' => PokemonGender::FEMALE, 'activeSpecies' => $species, 'activeGender' => PokemonGender::MALE];
        },
        8.0,
    ],
    'love ball on the same species and gender' => [
        'love-ball',
        function (): array {
            $species = Species::factory()->create();

            return ['wildSpecies' => $species, 'wildGender' => PokemonGender::MALE, 'activeSpecies' => $species, 'activeGender' => PokemonGender::MALE];
        },
        1.0,
    ],
    'love ball on genderless pokemon of the same species' => [
        'love-ball',
        function (): array {
            $species = Species::factory()->create();

            return ['wildSpecies' => $species, 'wildGender' => PokemonGender::GENDERLESS, 'activeSpecies' => $species, 'activeGender' => PokemonGender::GENDERLESS];
        },
        1.0,
    ],
    'love ball on another species of the opposite gender' => [
        'love-ball',
        fn (): array => ['wildGender' => PokemonGender::FEMALE, 'activeGender' => PokemonGender::MALE],
        1.0,
    ],
    'moon ball on a species that evolves with a moon stone' => [
        'moon-ball',
        function (): array {
            $species = Species::factory()->create();

            SpeciesEvolution::factory()->for($species, 'from')->create([
                'evolution_item_id' => EvolutionItem::factory()->create(['slug' => 'moon-stone']),
            ]);

            return ['wildSpecies' => $species->load('evolutions.evolutionItem')];
        },
        4.0,
    ],
    'moon ball on a species that evolves with another item' => [
        'moon-ball',
        function (): array {
            $species = Species::factory()->create();

            SpeciesEvolution::factory()->for($species, 'from')->create([
                'evolution_item_id' => EvolutionItem::factory()->create(['slug' => 'fire-stone']),
            ]);

            return ['wildSpecies' => $species->load('evolutions.evolutionItem')];
        },
        1.0,
    ],
    'net ball on a bug type'        => ['net-ball', fn (): array => ['wildSpecies' => Species::factory()->create(['types' => [PokemonType::BUG]])], 3.5],
    'net ball on a grass type'      => ['net-ball', fn (): array => ['wildSpecies' => Species::factory()->create(['types' => [PokemonType::POISON, PokemonType::GRASS]])], 3.5],
    'net ball on a normal type'     => ['net-ball', [], 1.0],
    'lure ball on a water type'     => ['lure-ball', fn (): array => ['wildSpecies' => Species::factory()->create(['types' => [PokemonType::WATER]])], 3.5],
    'lure ball on a normal type'    => ['lure-ball', [], 1.0],
    'dive ball in the coast'        => ['dive-ball', ['area' => Area::COAST], 3.5],
    'dive ball in the forest'       => ['dive-ball', ['area' => Area::FOREST], 1.0],
    'heavy ball on a rock type'     => ['heavy-ball', fn (): array => ['wildSpecies' => Species::factory()->create(['types' => [PokemonType::ROCK]])], 3.5],
    'heavy ball on a steel type'    => ['heavy-ball', fn (): array => ['wildSpecies' => Species::factory()->create(['types' => [PokemonType::STEEL]])], 3.5],
    'heavy ball on a ground type'   => ['heavy-ball', fn (): array => ['wildSpecies' => Species::factory()->create(['types' => [PokemonType::GROUND]])], 3.5],
    'heavy ball on a normal type'   => ['heavy-ball', [], 1.0],
    'fast ball on an electric type' => ['fast-ball', fn (): array => ['wildSpecies' => Species::factory()->create(['types' => [PokemonType::ELECTRIC]])], 3.5],
    'fast ball on a flying type'    => ['fast-ball', fn (): array => ['wildSpecies' => Species::factory()->create(['types' => [PokemonType::NORMAL, PokemonType::FLYING]])], 3.5],
    'fast ball on a normal type'    => ['fast-ball', [], 1.0],
]);

it('boosts the beast ball only on ultra beasts', function (bool $isUltraBeast, float $expectedMultiplier) {
    $pokeball = Pokeball::factory()->make(['slug' => 'beast-ball', 'catch_rate' => 0.1]);

    $context = new CatchContext(
        turn: 1,
        area: Area::MEADOW,
        isNight: false,
        wildSpecies: Species::factory()->create(['is_ultra_beast' => $isUltraBeast]),
        wildLevel: 50,
        wildGender: PokemonGender::GENDERLESS,
        wildAilment: null,
        activeSpecies: Species::factory()->create(),
        activeLevel: 50,
        activeGender: PokemonGender::MALE,
        playerOwnsSpecies: false,
    );

    expect(new BallMultiplierCalculator()->multiplier($pokeball, $context))->toBe($expectedMultiplier);
})->with([
    'ultra beast'   => [true, 5.0],
    'other pokemon' => [false, 0.1],
]);
