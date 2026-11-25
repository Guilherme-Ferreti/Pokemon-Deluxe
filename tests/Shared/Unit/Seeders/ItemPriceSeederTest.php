<?php

declare(strict_types=1);

use Database\Seeders\EvolutionItemSeeder;
use Database\Seeders\PokeballSeeder;
use Shared\Models\EvolutionItem;
use Shared\Models\Pokeball;

use function Pest\Laravel\seed;

it('does not price the master ball', function () {
    seed(PokeballSeeder::class);

    expect(Pokeball::query()->where('slug', 'master-ball')->value('price'))->toBeNull()
        ->and(Pokeball::query()->where('slug', 'poke-ball')->value('price'))->toBe(200);
});

it('does not price mega stones', function () {
    seed(EvolutionItemSeeder::class);

    expect(EvolutionItem::query()->whereIn('slug', ['gengarite', 'charizardite-x', 'glimmoranite'])->pluck('price')->all())
        ->toBe([null, null, null]);
});

it('prices every other evolution item at 3,000', function () {
    seed(EvolutionItemSeeder::class);

    $otherItems = EvolutionItem::query()->where('description', 'not like', '%Mega Evolve%')->get();

    expect($otherItems)->not->toBeEmpty()
        ->and($otherItems->pluck('price')->unique()->all())->toBe([3000]);
});
