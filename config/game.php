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
        'shiny_odds'          => 100, // An egg is shiny 1 time in this many.
    ],

    'pokemon' => [
        'max_level'            => 100,
        'experience_per_level' => 1000,
        'default_base_move'    => 'tackle',
    ],
];
