<?php

declare(strict_types=1);

use App\Pokemon\Mechanics\ExperienceCalculator;
use Shared\Enums\BattleType;

it('gives XP for defeating a pokemon as in the Spec 1 table', function (int $opponentLevel, int $pokemonLevel, BattleType $battleType, bool $isKnockout, int $expectedExperience) {
    $experience = new ExperienceCalculator()->experienceForDefeating($opponentLevel, $pokemonLevel, $battleType, $isKnockout);

    expect($experience)->toBe($expectedExperience);
})->with([
    'Lv 5 knocks out a wild Lv 7'              => [7, 5, BattleType::WILD, true, 525],
    'Lv 30 beats a wild Lv 10'                 => [10, 30, BattleType::WILD, false, 83],
    'Lv 50 beats a Lv 50 trainer pokemon'      => [50, 50, BattleType::TRAINER, false, 375],
    'Lv 50 knocks out a Lv 50 trainer pokemon' => [50, 50, BattleType::TRAINER, true, 562],
    'Lv 50 beats a Lv 55 gym pokemon'          => [55, 50, BattleType::GYM, false, 550],
    'Lv 50 knocks out a Lv 55 gym pokemon'     => [55, 50, BattleType::GYM, true, 825],
    'Lv 90 beats a Lv 90 Tower pokemon'        => [90, 90, BattleType::BATTLE_TOWER, false, 250],
    'Lv 90 knocks out a Lv 90 Tower pokemon'   => [90, 90, BattleType::BATTLE_TOWER, true, 375],
]);

it('caps the level ratio between 0.1 and 3', function (int $opponentLevel, int $pokemonLevel, int $expectedExperience) {
    $experience = new ExperienceCalculator()->experienceForDefeating($opponentLevel, $pokemonLevel, BattleType::WILD, isKnockout: false);

    expect($experience)->toBe($expectedExperience);
})->with([
    'Lv 100 beats a Lv 1'  => [1, 100, 25],
    'Lv 10 beats a Lv 100' => [100, 10, 750],
]);

it('multiplies the XP by the battle bonus', function (BattleType $battleType, int $expectedExperience) {
    $experience = new ExperienceCalculator()->experienceForDefeating(50, 50, $battleType, isKnockout: false);

    expect($experience)->toBe($expectedExperience);
})->with([
    'wild'         => [BattleType::WILD, 250],
    'trainer'      => [BattleType::TRAINER, 375],
    'gym'          => [BattleType::GYM, 500],
    'elite four'   => [BattleType::ELITE_FOUR, 500],
    'battle tower' => [BattleType::BATTLE_TOWER, 250],
]);

it('rejects a level below 1', function (int $opponentLevel, int $pokemonLevel) {
    expect(fn () => new ExperienceCalculator()->experienceForDefeating($opponentLevel, $pokemonLevel, BattleType::WILD, isKnockout: false))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'opponent level 0' => [0, 50],
    'own level 0'      => [50, 0],
]);
