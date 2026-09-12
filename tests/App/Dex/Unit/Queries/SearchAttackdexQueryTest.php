<?php

declare(strict_types=1);

use App\Dex\Enums\SearchAttackdexSortBy;
use App\Dex\Filters\SearchAttackdexFilters;
use App\Dex\Queries\SearchAttackdexQuery;
use Shared\Enums\Ailment;
use Shared\Enums\ElementalType;
use Shared\Enums\MoveCategory;
use Shared\Enums\MoveTrait;
use Shared\Enums\SortDirection;
use Shared\Models\Move;

/**
 * @param  array<int, ElementalType>  $types
 * @param  array<int, Ailment>  $ailments
 * @param  array<int, MoveTrait>  $traits
 */
function makeSearchAttackdexFilters(
    ?string $search = null,
    array $types = [],
    array $ailments = [],
    array $traits = [],
    SearchAttackdexSortBy $sort = SearchAttackdexSortBy::NAME,
    SortDirection $dir = SortDirection::ASC,
): SearchAttackdexFilters {
    return new SearchAttackdexFilters(
        search: $search,
        types: collect($types),
        ailments: collect($ailments),
        traits: collect($traits),
        sort: $sort,
        dir: $dir,
    );
}

it('returns all moves when no filters are applied', function () {
    Move::factory()->count(3)->create();

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters())->get();

    expect($result)->toHaveCount(3);
});

it('filters moves by matching name', function () {
    $tackle = Move::factory()->create(['name' => 'Tackle']);
    $ember  = Move::factory()->create(['name' => 'Ember']);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(search: 'Tackle'))->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($tackle->id);
});

it('filters moves by partial name match', function () {
    $tackle    = Move::factory()->create(['name' => 'Tackle']);
    $ember     = Move::factory()->create(['name' => 'Ember']);
    $emberFang = Move::factory()->create(['name' => 'Ember Fang']);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(search: 'Ember'))->get();

    expect($result)->toHaveCount(2)
        ->and($result->pluck('id'))->toContain($ember->id, $emberFang->id)->not->toContain($tackle->id);
});

it('returns no results when the search matches nothing', function () {
    Move::factory()->create(['name' => 'Tackle']);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(search: 'Missing'))->get();

    expect($result)->toBeEmpty();
});

it('filters moves by a single type', function () {
    $fireOnly  = Move::factory()->create(['type' => ElementalType::FIRE]);
    $waterOnly = Move::factory()->create(['type' => ElementalType::WATER]);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(types: [ElementalType::FIRE]))->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($fireOnly->id);
});

it('filters moves by multiple types', function () {
    $fire  = Move::factory()->create(['type' => ElementalType::FIRE]);
    $water = Move::factory()->create(['type' => ElementalType::WATER]);
    $grass = Move::factory()->create(['type' => ElementalType::GRASS]);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(types: [ElementalType::FIRE, ElementalType::WATER]))->get();

    expect($result)->toHaveCount(2)
        ->and($result->pluck('id'))->toContain($fire->id, $water->id)->not->toContain($grass->id);
});

it('filters moves by a single ailment', function () {
    $burnMove = Move::factory()->create(['ailment' => Ailment::BURN]);
    Move::factory()->create(['ailment' => Ailment::FREEZE]);
    Move::factory()->create(['ailment' => null]);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(ailments: [Ailment::BURN]))->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($burnMove->id);
});

it('filters moves by multiple ailments', function () {
    $burn     = Move::factory()->create(['ailment' => Ailment::BURN]);
    $freeze   = Move::factory()->create(['ailment' => Ailment::FREEZE]);
    $paralyze = Move::factory()->create(['ailment' => Ailment::PARALYSIS]);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(ailments: [Ailment::BURN, Ailment::FREEZE]))->get();

    expect($result)->toHaveCount(2)
        ->and($result->pluck('id'))->toContain($burn->id, $freeze->id)->not->toContain($paralyze->id);
});

it('filters moves by a single trait', function () {
    $zmove = Move::factory()->create(['is_zmove' => true]);
    Move::factory()->create(['is_zmove' => false, 'is_dynamax' => true]);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(traits: [MoveTrait::Z_MOVE]))->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($zmove->id);
});

it('filters moves by multiple traits', function () {
    $zmove   = Move::factory()->create(['is_zmove' => true]);
    $dynamax = Move::factory()->create(['is_dynamax' => true]);
    $other   = Move::factory()->create(['is_zmove' => false, 'is_dynamax' => false]);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(traits: [MoveTrait::Z_MOVE, MoveTrait::DYNAMAX]))->get();

    expect($result)->toHaveCount(2)
        ->and($result->pluck('id'))->toContain($zmove->id, $dynamax->id)->not->toContain($other->id);
});

it('sorts moves by name ascending', function () {
    $ember    = Move::factory()->create(['name' => 'Ember']);
    $tackle   = Move::factory()->create(['name' => 'Tackle']);
    $waterGun = Move::factory()->create(['name' => 'Water Gun']);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(sort: SearchAttackdexSortBy::NAME, dir: SortDirection::ASC))->get();

    expect($result->pluck('id')->all())->toBe([$ember->id, $tackle->id, $waterGun->id]);
});

it('sorts moves by name descending', function () {
    $ember    = Move::factory()->create(['name' => 'Ember']);
    $tackle   = Move::factory()->create(['name' => 'Tackle']);
    $waterGun = Move::factory()->create(['name' => 'Water Gun']);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(sort: SearchAttackdexSortBy::NAME, dir: SortDirection::DESC))->get();

    expect($result->pluck('id')->all())->toBe([$waterGun->id, $tackle->id, $ember->id]);
});

it('sorts moves by power ascending with ohko moves last', function () {
    $low  = Move::factory()->create(['category' => MoveCategory::DAMAGE, 'power' => 20]);
    $high = Move::factory()->create(['category' => MoveCategory::DAMAGE, 'power' => 100]);
    $ohko = Move::factory()->create(['category' => MoveCategory::OHKO, 'power' => null]);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(sort: SearchAttackdexSortBy::POWER, dir: SortDirection::ASC))->get();

    expect($result->pluck('id')->all())->toBe([$low->id, $high->id, $ohko->id]);
});

it('sorts moves by power descending with ohko moves first', function () {
    $low  = Move::factory()->create(['category' => MoveCategory::DAMAGE, 'power' => 20]);
    $high = Move::factory()->create(['category' => MoveCategory::DAMAGE, 'power' => 100]);
    $ohko = Move::factory()->create(['category' => MoveCategory::OHKO, 'power' => null]);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(sort: SearchAttackdexSortBy::POWER, dir: SortDirection::DESC))->get();

    expect($result->pluck('id')->all())->toBe([$ohko->id, $high->id, $low->id]);
});

it('sorts moves by accuracy ascending', function () {
    $low  = Move::factory()->create(['accuracy' => 50]);
    $mid  = Move::factory()->create(['accuracy' => 80]);
    $high = Move::factory()->create(['accuracy' => 100]);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(sort: SearchAttackdexSortBy::ACCURACY, dir: SortDirection::ASC))->get();

    expect($result->pluck('id')->all())->toBe([$low->id, $mid->id, $high->id]);
});

it('sorts moves by accuracy descending', function () {
    $low  = Move::factory()->create(['accuracy' => 50]);
    $mid  = Move::factory()->create(['accuracy' => 80]);
    $high = Move::factory()->create(['accuracy' => 100]);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(sort: SearchAttackdexSortBy::ACCURACY, dir: SortDirection::DESC))->get();

    expect($result->pluck('id')->all())->toBe([$high->id, $mid->id, $low->id]);
});

it('sorts moves by ailment chance ascending', function () {
    $low  = Move::factory()->create(['ailment_chance' => 10]);
    $mid  = Move::factory()->create(['ailment_chance' => 50]);
    $high = Move::factory()->create(['ailment_chance' => 100]);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(sort: SearchAttackdexSortBy::AILMENT_CHANCE, dir: SortDirection::ASC))->get();

    expect($result->pluck('id')->all())->toBe([$low->id, $mid->id, $high->id]);
});

it('sorts moves by ailment chance descending', function () {
    $low  = Move::factory()->create(['ailment_chance' => 10]);
    $mid  = Move::factory()->create(['ailment_chance' => 50]);
    $high = Move::factory()->create(['ailment_chance' => 100]);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(sort: SearchAttackdexSortBy::AILMENT_CHANCE, dir: SortDirection::DESC))->get();

    expect($result->pluck('id')->all())->toBe([$high->id, $mid->id, $low->id]);
});

it('sorts moves by flinch chance ascending', function () {
    $low  = Move::factory()->create(['flinch_chance' => 0]);
    $mid  = Move::factory()->create(['flinch_chance' => 30]);
    $high = Move::factory()->create(['flinch_chance' => 100]);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(sort: SearchAttackdexSortBy::FLINCH_CHANCE, dir: SortDirection::ASC))->get();

    expect($result->pluck('id')->all())->toBe([$low->id, $mid->id, $high->id]);
});

it('sorts moves by flinch chance descending', function () {
    $low  = Move::factory()->create(['flinch_chance' => 0]);
    $mid  = Move::factory()->create(['flinch_chance' => 30]);
    $high = Move::factory()->create(['flinch_chance' => 100]);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(sort: SearchAttackdexSortBy::FLINCH_CHANCE, dir: SortDirection::DESC))->get();

    expect($result->pluck('id')->all())->toBe([$high->id, $mid->id, $low->id]);
});

it('combines multiple filters together', function () {
    $match = Move::factory()->create([
        'name'    => 'Flamethrower',
        'type'    => ElementalType::FIRE,
        'ailment' => Ailment::BURN,
    ]);

    Move::factory()->create(['name' => 'Tackle', 'type' => ElementalType::NORMAL, 'ailment' => null]);
    Move::factory()->create(['name' => 'Flamethrower', 'type' => ElementalType::FIRE, 'ailment' => null]);
    Move::factory()->create(['name' => 'Flamethrower', 'type' => ElementalType::WATER, 'ailment' => Ailment::BURN]);

    $result = new SearchAttackdexQuery()->handle(makeSearchAttackdexFilters(
        search: 'Flamethrower',
        types: [ElementalType::FIRE],
        ailments: [Ailment::BURN],
    ))->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($match->id);
});
