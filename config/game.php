<?php

declare(strict_types=1);

return [
    'user' => [
        'max_incubators' => 10,   // Number of incubator slots in the Nursery, bought one at a time.
        'max_box_size'   => 9999, // Most Pokémon a player can own; catching or hatching more is refused.
    ],

    'inventory' => [
        'max_stack' => 9999, // Most units of one item (ball, evolution item) a player can hold.
    ],

    'incubation' => [
        'hatch_time_in_hours' => 24, // Hours an egg incubates before it is ready to hatch.
        'shiny_chance'        => 1,  // 1% chance that an egg is shiny.
    ],

    'pokemon' => [
        'max_level'            => 100,      // Highest level a Pokémon can reach.
        'max_boost'            => 100,      // Highest boost a Pokémon can reach with boost stones.
        'experience_per_level' => 1000,     // XP needed to go up one level.
        'default_base_move'    => 'tackle', // Move given to a new Pokémon whose species has no base moves.

        'stats' => [
            'max_level_hp'                  => 400,  // Max HP of a level 100 Pokémon with no boost and no shine.
            'level_bonus'                   => 10,   // How close low and high levels are in strength (higher = smaller gap).
            'damage_scale'                  => 77,   // How much damage a Pokémon deals for its level (higher = less damage).
            'shiny_hp_multiplier'           => 1.25, // Extra max HP a shiny Pokémon has (1.25 = 25% more).
            'shiny_damage_done_multiplier'  => 1.25, // Extra damage a shiny Pokémon deals (1.25 = 25% more).
            'shiny_damage_taken_multiplier' => 0.75, // Damage a shiny Pokémon takes (0.75 = 25% less).
        ],
    ],
];
