<?php

declare(strict_types=1);

use Shared\ValueObjects\ExperienceGain;

it('rejects invalid values', function (string $field, mixed $invalidValue) {
    $values = [
        'combatantIndex'        => 0,
        'earnedExperience'      => 1500,
        'levelBefore'           => 50,
        'levelAfter'            => 51,
        'isFromExperienceShare' => false,
        'isAtMaxLevel'          => false,
    ];

    expect(fn () => new ExperienceGain(...[...$values, $field => $invalidValue]))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'negative combatant index'   => ['combatantIndex', -1],
    'negative earned experience' => ['earnedExperience', -1],
    'level before 0'             => ['levelBefore', 0],
    'level after below before'   => ['levelAfter', 49],
    'level after above max'      => ['levelAfter', 101],
    'max level below level 100'  => ['isAtMaxLevel', true],
]);
