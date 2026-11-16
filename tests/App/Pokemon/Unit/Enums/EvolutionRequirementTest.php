<?php

declare(strict_types=1);

use App\Pokemon\Enums\EvolutionRequirement;
use Shared\Enums\PokemonGender;
use Shared\Models\EvolutionItem;
use Shared\Models\SpeciesEvolution;

it('words the reason from the evolution', function (EvolutionRequirement $requirement, string $itemName, string $expectedReason) {
    $item      = EvolutionItem::factory()->create(['name' => $itemName]);
    $evolution = SpeciesEvolution::factory()->create(['evolution_item_id' => $item->id, 'min_level' => 36, 'gender' => PokemonGender::FEMALE])->load('evolutionItem');

    expect($requirement->reason($evolution))->toBe($expectedReason);
})->with([
    'gender'                         => [EvolutionRequirement::GENDER, 'Water Stone', 'Needs a female Pokémon'],
    'level'                          => [EvolutionRequirement::LEVEL, 'Water Stone', 'Needs level 36'],
    'item starting with a consonant' => [EvolutionRequirement::ITEM, 'Water Stone', 'Needs a Water Stone'],
    'item starting with a vowel'     => [EvolutionRequirement::ITEM, 'Ice Stone', 'Needs an Ice Stone'],
]);

it('refuses a requirement the evolution does not have', function (EvolutionRequirement $requirement) {
    $evolution = SpeciesEvolution::factory()->create(['evolution_item_id' => null, 'min_level' => null, 'gender' => null])->load('evolutionItem');

    expect(fn () => $requirement->reason($evolution))->toThrow(LogicException::class);
})->with([
    'gender' => [EvolutionRequirement::GENDER],
    'level'  => [EvolutionRequirement::LEVEL],
    'item'   => [EvolutionRequirement::ITEM],
]);
