<?php

declare(strict_types=1);

use Shared\ValueObjects\ExperienceGain;

it('rejects invalid values', function (string $field, int $invalidValue) {
    $values = [
        'combatant_index'   => 0,
        'earned_experience' => 1500,
        'level_before'      => 50,
        'level_after'       => 51,
    ];

    expect(fn () => new ExperienceGain(...[...$values, $field => $invalidValue]))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'negative combatant index'   => ['combatant_index', -1],
    'negative earned experience' => ['earned_experience', -1],
    'level before 0'             => ['level_before', 0],
    'level after below before'   => ['level_after', 49],
    'level after above max'      => ['level_after', 101],
]);
