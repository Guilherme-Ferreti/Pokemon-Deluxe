<?php

declare(strict_types=1);

return [
    'user' => [
        'max_incubators' => 10,
        'max_box_size'   => 9999,
    ],

    'inventory' => [
        'max_stack' => 9999,
    ],

    'incubation' => [
        'hatch_time_in_hours' => 24,
        'shiny_chance'        => 1, // 1% chance that an egg is shiny.
    ],

    'pokemon' => [
        'max_level'            => 100,
        'experience_per_level' => 1000,
        'default_base_move'    => 'tackle',
    ],
];
