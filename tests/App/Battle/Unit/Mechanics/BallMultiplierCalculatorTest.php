<?php

declare(strict_types=1);

use App\Battle\Mechanics\BallMultiplierCalculator;
use App\Battle\ValueObjects\CatchContext;
use Database\Seeders\PokeballSeeder;
use Shared\Enums\Area;
use Shared\Enums\PokemonGender;
use Shared\Models\Pokeball;
use Shared\Models\Species;

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
