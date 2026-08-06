<?php

declare(strict_types=1);

use App\Pokemon\Enums\SearchBoxSortBy;
use App\Pokemon\Filters\SearchBoxFilters;
use App\Pokemon\Queries\SearchBoxQuery;
use Shared\Enums\ElementalType;
use Shared\Enums\Generation;
use Shared\Enums\PokemonTrait;
use Shared\Enums\SortDirection;
use Shared\Models\Pokedex;
use Shared\Models\Pokemon;
use Shared\Models\User;

/**
 * @param  array<int, ElementalType>  $types
 * @param  array<int, PokemonTrait>  $traits
 * @param  array<int, Generation>  $generations
 */
function makeSearchBoxFilters(
    ?string $search = null,
    array $types = [],
    array $traits = [],
    array $generations = [],
    SearchBoxSortBy $sort = SearchBoxSortBy::CAPTURE_DATE,
    SortDirection $dir = SortDirection::DESC,
): SearchBoxFilters {
    return new SearchBoxFilters(
        search: $search,
        types: collect($types),
        traits: collect($traits),
        generations: collect($generations),
        sort: $sort,
        dir: $dir,
    );
}

it('returns all box pokemon for the user when no filters are applied', function () {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->count(3)->inBox()->create();

    $result = new SearchBoxQuery()->handle($user, makeSearchBoxFilters())->get();

    expect($result)->toHaveCount(3);
});

it('excludes party pokemon', function () {
    $user         = User::factory()->create();
    $boxPokemon   = Pokemon::factory()->for($user)->inBox()->create();
    $partyPokemon = Pokemon::factory()->for($user)->inParty()->create();

    $result = new SearchBoxQuery()->handle($user, makeSearchBoxFilters())->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($boxPokemon->id);
});

it("excludes other users's pokemon", function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $pokemon = Pokemon::factory()->for($userA)->inBox()->create();
    Pokemon::factory()->for($userB)->inBox()->create();

    $result = new SearchBoxQuery()->handle($userA, makeSearchBoxFilters())->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($pokemon->id);
});

it('filters box pokemon by matching name', function () {
    $user = User::factory()->create();

    $pikachu    = Pokemon::factory()->for($user)->for(Pokedex::factory()->create(['name' => 'Pikachu']))->inBox()->create();
    $charmander = Pokemon::factory()->for($user)->for(Pokedex::factory()->create(['name' => 'Charmander']))->inBox()->create();

    $result = new SearchBoxQuery()->handle($user, makeSearchBoxFilters(search: 'Pikachu'))->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($pikachu->id);
});

it('filters box pokemon by partial name match', function () {
    $user = User::factory()->create();

    $pikachu    = Pokemon::factory()->for($user)->for(Pokedex::factory()->create(['name' => 'Pikachu']))->inBox()->create();
    $raichu     = Pokemon::factory()->for($user)->for(Pokedex::factory()->create(['name' => 'Raichu']))->inBox()->create();
    $charmander = Pokemon::factory()->for($user)->for(Pokedex::factory()->create(['name' => 'Charmander']))->inBox()->create();

    $result = new SearchBoxQuery()->handle($user, makeSearchBoxFilters(search: 'chu'))->get();

    expect($result)->toHaveCount(2)
        ->and($result->pluck('id'))->toContain($pikachu->id, $raichu->id)->not->toContain($charmander->id);
});

it('returns no results when the search matches nothing', function () {
    $pokemon = Pokemon::factory()->inBox()->create();

    $result = new SearchBoxQuery()->handle($pokemon->user, makeSearchBoxFilters(search: 'Missing'))->get();

    expect($result)->toBeEmpty();
});

it('filters box pokemon by a single type', function () {
    $user = User::factory()->create();

    $fireOnly  = Pokemon::factory()->for($user)->for(Pokedex::factory()->create(['types' => [ElementalType::FIRE]]))->inBox()->create();
    $waterOnly = Pokemon::factory()->for($user)->for(Pokedex::factory()->create(['types' => [ElementalType::WATER]]))->inBox()->create();
    $fireWater = Pokemon::factory()->for($user)->for(Pokedex::factory()->create(['types' => [ElementalType::FIRE, ElementalType::WATER]]))->inBox()->create();

    $result = new SearchBoxQuery()->handle($user, makeSearchBoxFilters(types: [ElementalType::FIRE]))->get();

    expect($result)->toHaveCount(2)
        ->and($result->pluck('id'))->toContain($fireOnly->id, $fireWater->id)->not->toContain($waterOnly->id);
});

it('requires both types when two types are selected', function () {
    $user = User::factory()->create();

    $fireOnly  = Pokemon::factory()->for($user)->for(Pokedex::factory()->create(['types' => [ElementalType::FIRE]]))->inBox()->create();
    $fireWater = Pokemon::factory()->for($user)->for(Pokedex::factory()->create(['types' => [ElementalType::FIRE, ElementalType::WATER]]))->inBox()->create();

    $result = new SearchBoxQuery()->handle($user, makeSearchBoxFilters(types: [ElementalType::FIRE, ElementalType::WATER]))->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($fireWater->id);
});

it('filters box pokemon by traits', function (PokemonTrait $trait, ?string $column) {
    $user = User::factory()->create();

    if ($trait === PokemonTrait::SHINY) {
        $pokemon = Pokemon::factory()->for($user)->inBox()->shiny()->create();

        Pokemon::factory()->for($user)->inBox()->notShiny()->create();
    } else {
        $pokemon = Pokemon::factory()->for($user)->for(Pokedex::factory()->create([$column => true]))->inBox()->create();

        Pokemon::factory(5)->for($user)->for(Pokedex::factory()->create([$column => false]))->inBox()->create();
    }

    $result = new SearchBoxQuery()->handle($user, makeSearchBoxFilters(traits: [$trait]))->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($pokemon->id);
})->with([
    'shiny'     => [PokemonTrait::SHINY, null],
    'baby'      => [PokemonTrait::BABY, 'is_baby'],
    'legendary' => [PokemonTrait::LEGENDARY, 'is_legendary'],
    'mythical'  => [PokemonTrait::MYTHICAL, 'is_mythical'],
    'mega'      => [PokemonTrait::MEGA, 'is_mega'],
    'gmax'      => [PokemonTrait::GMAX, 'is_gmax'],
]);

it('requires all selected traits to match', function () {
    $user = User::factory()->create();

    $legendaryBaby = Pokemon::factory()->for($user)->for(Pokedex::factory()->create(['is_legendary' => true, 'is_baby' => true]))->inBox()->create();
    $legendaryOnly = Pokemon::factory()->for($user)->for(Pokedex::factory()->create(['is_legendary' => true, 'is_baby' => false]))->inBox()->create();

    $result = new SearchBoxQuery()->handle($user, makeSearchBoxFilters(traits: [PokemonTrait::LEGENDARY, PokemonTrait::BABY]))->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($legendaryBaby->id);
});

it('filters box pokemon by a single generation', function () {
    $user = User::factory()->create();

    $kanto = Pokemon::factory()->for($user)->for(Pokedex::factory()->create(['generation' => Generation::KANTO]))->inBox()->create();
    $johto = Pokemon::factory()->for($user)->for(Pokedex::factory()->create(['generation' => Generation::JOHTO]))->inBox()->create();

    $result = new SearchBoxQuery()->handle($user, makeSearchBoxFilters(generations: [Generation::KANTO]))->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($kanto->id);
});

it('filters box pokemon by multiple generations', function () {
    $user = User::factory()->create();

    $kanto = Pokemon::factory()->for($user)->for(Pokedex::factory()->create(['generation' => Generation::KANTO]))->inBox()->create();
    $johto = Pokemon::factory()->for($user)->for(Pokedex::factory()->create(['generation' => Generation::JOHTO]))->inBox()->create();
    $hoenn = Pokemon::factory()->for($user)->for(Pokedex::factory()->create(['generation' => Generation::HOENN]))->inBox()->create();

    $result = new SearchBoxQuery()->handle($user, makeSearchBoxFilters(generations: [Generation::KANTO, Generation::JOHTO]))->get();

    expect($result)->toHaveCount(2)
        ->and($result->pluck('id'))->toContain($kanto->id, $johto->id)->not->toContain($hoenn->id);
});

it('sorts box pokemon by the given column and direction', function (SearchBoxSortBy $sort, Closure $createFixtures) {
    $user = User::factory()->create();

    [$lowId, $midId, $highId] = $createFixtures($user);

    $ascResult = new SearchBoxQuery()->handle($user, makeSearchBoxFilters(sort: $sort, dir: SortDirection::ASC))->get();

    expect($ascResult->pluck('id')->all())->toBe([$lowId, $midId, $highId]);

    $descResult = new SearchBoxQuery()->handle($user, makeSearchBoxFilters(sort: $sort, dir: SortDirection::DESC))->get();

    expect($descResult->pluck('id')->all())->toBe([$highId, $midId, $lowId]);
})->with([
    'capture date' => [
        SearchBoxSortBy::CAPTURE_DATE,
        function (User $user): array {
            $low  = Pokemon::factory()->for($user)->inBox()->create(['captured_at' => now()->subDays(3)]);
            $mid  = Pokemon::factory()->for($user)->inBox()->create(['captured_at' => now()->subDays(2)]);
            $high = Pokemon::factory()->for($user)->inBox()->create(['captured_at' => now()->subDays(1)]);

            return [$low->id, $mid->id, $high->id];
        },
    ],
    'experience' => [
        SearchBoxSortBy::EXPERIENCE,
        function (User $user): array {
            $low  = Pokemon::factory()->for($user)->inBox()->create(['experience' => 10]);
            $mid  = Pokemon::factory()->for($user)->inBox()->create(['experience' => 50]);
            $high = Pokemon::factory()->for($user)->inBox()->create(['experience' => 90]);

            return [$low->id, $mid->id, $high->id];
        },
    ],
    'level' => [
        SearchBoxSortBy::LEVEL,
        function (User $user): array {
            $low  = Pokemon::factory()->for($user)->inBox()->create(['level' => 5]);
            $mid  = Pokemon::factory()->for($user)->inBox()->create(['level' => 25]);
            $high = Pokemon::factory()->for($user)->inBox()->create(['level' => 60]);

            return [$low->id, $mid->id, $high->id];
        },
    ],
    'pokedex number' => [
        SearchBoxSortBy::POKEDEX_NUMBER,
        function (User $user): array {
            $lowPokedex  = Pokedex::factory()->create(['national_dex_entry_number' => 1]);
            $midPokedex  = Pokedex::factory()->create(['national_dex_entry_number' => 100]);
            $highPokedex = Pokedex::factory()->create(['national_dex_entry_number' => 700]);

            $low  = Pokemon::factory()->for($user)->for($lowPokedex)->inBox()->create();
            $mid  = Pokemon::factory()->for($user)->for($midPokedex)->inBox()->create();
            $high = Pokemon::factory()->for($user)->for($highPokedex)->inBox()->create();

            return [$low->id, $mid->id, $high->id];
        },
    ],
]);
