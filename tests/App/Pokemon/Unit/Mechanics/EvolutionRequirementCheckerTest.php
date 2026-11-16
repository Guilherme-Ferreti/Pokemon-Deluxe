<?php

declare(strict_types=1);

use App\Pokemon\Enums\EvolutionRequirement;
use App\Pokemon\Mechanics\EvolutionRequirementChecker;
use Shared\Enums\PokemonGender;
use Shared\Models\EvolutionItem;
use Shared\Models\Pokemon;
use Shared\Models\SpeciesEvolution;

it('allows an evolution with no requirements', function () {
    $pokemon   = Pokemon::factory()->create(['level' => 1]);
    $evolution = SpeciesEvolution::factory()->create(['evolution_item_id' => null, 'min_level' => null, 'gender' => null])->load('evolutionItem');

    expect(new EvolutionRequirementChecker()->check($pokemon, $evolution, []))->toBeNull();
});

it('needs the minimum level', function (int $level, ?EvolutionRequirement $expectedUnmetRequirement) {
    $pokemon   = Pokemon::factory()->create(['level' => $level]);
    $evolution = SpeciesEvolution::factory()->create(['evolution_item_id' => null, 'min_level' => 36, 'gender' => null])->load('evolutionItem');

    expect(new EvolutionRequirementChecker()->check($pokemon, $evolution, []))->toBe($expectedUnmetRequirement);
})->with([
    'below'   => [35, EvolutionRequirement::LEVEL],
    'reached' => [36, null],
    'above'   => [80, null],
]);

it('needs the evolution item when it is not owned', function () {
    $pokemon   = Pokemon::factory()->create(['level' => 50]);
    $item      = EvolutionItem::factory()->create();
    $evolution = SpeciesEvolution::factory()->create(['evolution_item_id' => $item->id, 'min_level' => null, 'gender' => null])->load('evolutionItem');

    expect(new EvolutionRequirementChecker()->check($pokemon, $evolution, ['other-item']))->toBe(EvolutionRequirement::ITEM);
});

it('allows an evolution item that is owned', function () {
    $pokemon   = Pokemon::factory()->create(['level' => 50]);
    $item      = EvolutionItem::factory()->create(['name' => 'Water Stone']);
    $evolution = SpeciesEvolution::factory()->create(['evolution_item_id' => $item->id, 'min_level' => null, 'gender' => null])->load('evolutionItem');

    expect(new EvolutionRequirementChecker()->check($pokemon, $evolution, [$item->id]))->toBeNull();
});

it('needs the right gender', function (PokemonGender $gender, ?EvolutionRequirement $expectedUnmetRequirement) {
    $pokemon   = Pokemon::factory()->create(['level' => 50, 'gender' => $gender]);
    $evolution = SpeciesEvolution::factory()->create(['evolution_item_id' => null, 'min_level' => null, 'gender' => PokemonGender::FEMALE])->load('evolutionItem');

    expect(new EvolutionRequirementChecker()->check($pokemon, $evolution, []))->toBe($expectedUnmetRequirement);
})->with([
    'female'     => [PokemonGender::FEMALE, null],
    'male'       => [PokemonGender::MALE, EvolutionRequirement::GENDER],
    'genderless' => [PokemonGender::GENDERLESS, EvolutionRequirement::GENDER],
]);

it('checks the gender first, then the level, then the item', function (int $level, PokemonGender $gender, EvolutionRequirement $expectedUnmetRequirement) {
    $pokemon   = Pokemon::factory()->create(['level' => $level, 'gender' => $gender]);
    $item      = EvolutionItem::factory()->create();
    $evolution = SpeciesEvolution::factory()->create(['evolution_item_id' => $item->id, 'min_level' => 36, 'gender' => PokemonGender::FEMALE])->load('evolutionItem');

    expect(new EvolutionRequirementChecker()->check($pokemon, $evolution, []))->toBe($expectedUnmetRequirement);
})->with([
    'nothing met'      => [10, PokemonGender::MALE, EvolutionRequirement::GENDER],
    'gender met'       => [10, PokemonGender::FEMALE, EvolutionRequirement::LEVEL],
    'gender and level' => [36, PokemonGender::FEMALE, EvolutionRequirement::ITEM],
]);
