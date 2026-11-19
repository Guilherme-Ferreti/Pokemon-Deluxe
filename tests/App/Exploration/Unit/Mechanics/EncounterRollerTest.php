<?php

declare(strict_types=1);

use App\Exploration\Mechanics\EncounterRoller;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonGenderRate;
use Shared\Helpers\RandomHelper;
use Shared\Models\Species;

it('rolls each evolution stage by its weight', function (int $roll, int $expectedStage) {
    config(['game.exploration.stage_weights' => [1 => 85, 2 => 12, 3 => 3]]);

    RandomHelper::fake([$roll]);

    expect(new EncounterRoller()->rollEvolutionStage())->toBe($expectedStage);
})->with([
    'lowest unevolved roll'     => [1, 1],
    'highest unevolved roll'    => [85, 1],
    'lowest second stage roll'  => [86, 2],
    'highest second stage roll' => [97, 2],
    'lowest third stage roll'   => [98, 3],
    'highest third stage roll'  => [100, 3],
]);

it('picks a species of the rolled stage', function (int $stage) {
    config([
        'game.exploration.legendary_chance' => 0,
        'game.exploration.stage_weights'    => [1 => 0, 2 => 0, 3 => 0, $stage => 1],
    ]);

    $areaSpecies = collect([1, 2, 3])->map(fn (int $evolutionStage): Species => Species::factory()->make([
        'evolution_stage' => $evolutionStage,
        'is_legendary'    => false,
        'is_mythical'     => false,
        'gender_rate'     => PokemonGenderRate::GENDERLESS,
    ]));

    $newPokemon = new EncounterRoller()->roll($areaSpecies);

    expect($newPokemon->species->evolution_stage)->toBe($stage);
})->with([1, 2, 3]);

it('falls back to the next lower stage when the area has no species at the rolled stage', function (int $rolledStage, array $areaStages, int $expectedStage) {
    config([
        'game.exploration.legendary_chance' => 0,
        'game.exploration.stage_weights'    => [1 => 0, 2 => 0, 3 => 0, $rolledStage => 1],
    ]);

    $areaSpecies = collect($areaStages)->map(fn (int $evolutionStage): Species => Species::factory()->make([
        'evolution_stage' => $evolutionStage,
        'is_legendary'    => false,
        'is_mythical'     => false,
        'gender_rate'     => PokemonGenderRate::GENDERLESS,
    ]));

    $newPokemon = new EncounterRoller()->roll($areaSpecies);

    expect($newPokemon->species->evolution_stage)->toBe($expectedStage);
})->with([
    'third stage to second stage' => [3, [1, 2], 2],
    'third stage to unevolved'    => [3, [1], 1],
    'second stage to unevolved'   => [2, [1, 3], 1],
]);

it('rolls a legendary or mythical with the legendary chance', function (string $flag) {
    config(['game.exploration.legendary_chance' => 100]);

    $charmander = Species::factory()->make([
        'evolution_stage' => 1,
        'is_legendary'    => false,
        'is_mythical'     => false,
        'gender_rate'     => PokemonGenderRate::GENDERLESS,
    ]);
    $legendary = Species::factory()->make([
        'evolution_stage' => 1,
        'is_legendary'    => false,
        'is_mythical'     => false,
        $flag             => true,
        'wild_min_level'  => 50,
        'wild_max_level'  => 70,
        'gender_rate'     => PokemonGenderRate::GENDERLESS,
    ]);

    $newPokemon = new EncounterRoller()->roll(collect([$charmander, $legendary]));

    expect($newPokemon->species)->toBe($legendary);
})->with(['is_legendary', 'is_mythical']);

it('leaves legendaries and mythicals out of the stage roll', function () {
    config([
        'game.exploration.legendary_chance' => 0,
        'game.exploration.stage_weights'    => [1 => 1],
    ]);

    $charmander = Species::factory()->make([
        'evolution_stage' => 1,
        'is_legendary'    => false,
        'is_mythical'     => false,
        'gender_rate'     => PokemonGenderRate::GENDERLESS,
    ]);
    $mewtwo = Species::factory()->make([
        'evolution_stage' => 1,
        'is_legendary'    => true,
        'is_mythical'     => false,
        'gender_rate'     => PokemonGenderRate::GENDERLESS,
    ]);

    $newPokemon = new EncounterRoller()->roll(collect([$charmander, $mewtwo]));

    expect($newPokemon->species)->toBe($charmander);
});

it('rolls the level within the species wild level range', function () {
    $charizard = Species::factory()->make([
        'evolution_stage' => 3,
        'is_legendary'    => false,
        'is_mythical'     => false,
        'wild_min_level'  => 37,
        'wild_max_level'  => 37,
        'gender_rate'     => PokemonGenderRate::GENDERLESS,
    ]);

    $newPokemon = new EncounterRoller()->roll(collect([$charizard]));

    expect($newPokemon->level)->toBe(37);
});

it('rolls the level, shininess and gender of the picked species', function (bool $isShiny, bool $isMale, PokemonGender $expectedGender) {
    config(['game.exploration.stage_weights' => [1 => 85, 2 => 12, 3 => 3]]);

    $charmander = Species::factory()->make([
        'evolution_stage' => 1,
        'is_legendary'    => false,
        'is_mythical'     => false,
        'wild_min_level'  => 2,
        'wild_max_level'  => 12,
        'gender_rate'     => PokemonGenderRate::EQUAL,
    ]);

    RandomHelper::fake([50, $charmander, 7, $isShiny, $isMale]);

    $newPokemon = new EncounterRoller()->roll(collect([$charmander]));

    expect($newPokemon->species)->toBe($charmander)
        ->and($newPokemon->level)->toBe(7)
        ->and($newPokemon->is_shiny)->toBe($isShiny)
        ->and($newPokemon->gender)->toBe($expectedGender);
})->with([
    'shiny male'     => [true, true, PokemonGender::MALE],
    'regular female' => [false, false, PokemonGender::FEMALE],
]);

it('refuses to roll an encounter without species', function () {
    new EncounterRoller()->roll(collect());
})->throws(InvalidArgumentException::class);
