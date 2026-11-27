<?php

declare(strict_types=1);

return [
    // Daily mission templates. Each day one enabled template per difficulty is rolled into a mission.
    // `target` and a `level` parameter are [min, max] ranges; `type` and `area` pick any Pokémon type or area.
    // `description` fills in {target}, {type}, {area} and {level}; "singular|plural" picks by the target.
    // `reward` uses the stored MissionReward format; `random` items are picked when the reward is claimed.
    'daily_templates' => [
        [
            'difficulty'  => 'easy',
            'goal'        => 'catch-pokemon',
            'description' => 'Catch {target} Pokémon',
            'target'      => [5, 10],
            'parameters'  => [],
            'reward'      => ['money' => 1000, 'items' => [['item_type' => 'pokeball', 'slug' => 'poke-ball', 'quantity' => 5]]],
            'enabled'     => true,
        ],
        [
            'difficulty'  => 'easy',
            'goal'        => 'catch-pokemon-of-type',
            'description' => 'Catch {target} {type} Pokémon',
            'target'      => [3, 5],
            'parameters'  => ['type' => 'any'],
            'reward'      => ['money' => 1500, 'items' => [['item_type' => 'pokeball', 'slug' => 'great-ball', 'quantity' => 3]]],
            'enabled'     => true,
        ],
        [
            'difficulty'  => 'easy',
            'goal'        => 'win-wild-battles',
            'description' => 'Win {target} wild battles',
            'target'      => [3, 5],
            'parameters'  => [],
            'reward'      => ['money' => 1500, 'items' => []],
            'enabled'     => true,
        ],
        [
            'difficulty'  => 'easy',
            'goal'        => 'hatch-eggs',
            'description' => 'Hatch {target} egg|Hatch {target} eggs',
            'target'      => [1, 2],
            'parameters'  => [],
            'reward'      => ['money' => 2000, 'items' => []],
            'enabled'     => true,
        ],
        [
            'difficulty'  => 'medium',
            'goal'        => 'win-trainer-battles',
            'description' => 'Win {target} battles against trainers',
            'target'      => [3, 5],
            'parameters'  => [],
            'reward'      => ['money' => 3000, 'items' => [['item_type' => 'special-item', 'slug' => 'boost-stone', 'quantity' => 1]]],
            'enabled'     => false, // Trainers are not built yet (Spec 6).
        ],
        [
            'difficulty'  => 'medium',
            'goal'        => 'catch-pokemon-in-area',
            'description' => 'Catch {target} Pokémon in the {area}',
            'target'      => [3, 5],
            'parameters'  => ['area' => 'any'],
            'reward'      => ['money' => 3000, 'items' => [['random' => 'normal-tm', 'quantity' => 1]]],
            'enabled'     => true,
        ],
        [
            'difficulty'  => 'medium',
            'goal'        => 'evolve-pokemon',
            'description' => 'Evolve {target} Pokémon',
            'target'      => [1, 2],
            'parameters'  => [],
            'reward'      => ['money' => 3000, 'items' => [['item_type' => 'special-item', 'slug' => 'boost-stone', 'quantity' => 1]]],
            'enabled'     => true,
        ],
        [
            'difficulty'  => 'hard',
            'goal'        => 'catch-pokemon-at-level',
            'description' => 'Catch a Pokémon at level {level} or higher',
            'target'      => [1, 1],
            'parameters'  => ['level' => [50, 60]],
            'reward'      => ['money' => 0, 'items' => [['item_type' => 'special-item', 'slug' => 'boost-stone', 'quantity' => 3]]],
            'enabled'     => true,
        ],
        [
            'difficulty'  => 'hard',
            'goal'        => 'win-trainer-battles',
            'description' => 'Win {target} battles against trainers',
            'target'      => [8, 10],
            'parameters'  => [],
            'reward'      => ['money' => 0, 'items' => [
                ['item_type' => 'special-item', 'slug' => 'boost-stone', 'quantity' => 2],
                ['item_type' => 'pokeball', 'slug' => 'ultra-ball', 'quantity' => 3],
            ]],
            'enabled' => false, // Trainers are not built yet (Spec 6).
        ],
        [
            'difficulty'  => 'hard',
            'goal'        => 'climb-tower-floors',
            'description' => 'Climb {target} Battle Tower floors in one run',
            'target'      => [10, 15],
            'parameters'  => [],
            'reward'      => ['money' => 0, 'items' => [['item_type' => 'special-item', 'slug' => 'boost-stone', 'quantity' => 3]]],
            'enabled'     => false, // The Battle Tower is not built yet (Spec 6).
        ],
    ],
];
