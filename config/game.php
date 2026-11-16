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
        'max_active_moves'     => 4,        // Number of active move slots a Pokémon uses in battle.

        'stats' => [
            'max_level_hp'                  => 400,  // Max HP of a level 100 Pokémon with no boost and no shine.
            'level_bonus'                   => 10,   // How close low and high levels are in strength (higher = smaller gap).
            'damage_scale'                  => 77,   // How much damage a Pokémon deals for its level (higher = less damage).
            'shiny_hp_multiplier'           => 1.25, // Extra max HP a shiny Pokémon has (1.25 = 25% more).
            'shiny_damage_done_multiplier'  => 1.25, // Extra damage a shiny Pokémon deals (1.25 = 25% more).
            'shiny_damage_taken_multiplier' => 0.75, // Damage a shiny Pokémon takes (0.75 = 25% less).
        ],
    ],

    'experience' => [
        'base'                => 250, // XP for defeating a Pokémon of the same level in a wild battle.
        'min_ratio'           => 0.1, // Lowest opponent level ÷ own level counted (0.1 = a much stronger Pokémon still gets 10%).
        'max_ratio'           => 3,   // Highest opponent level ÷ own level counted (3 = at most triple XP).
        'knockout_multiplier' => 1.5, // Extra XP for the Pokémon that knocked the opponent out (1.5 = 50% more).
        'xp_share_ratio'      => 0.5, // Share of the formula the XP Share gives Pokémon that never came out (0.5 = half).

        'battle_bonuses' => [
            'wild'         => 1,   // XP multiplier in wild battles.
            'trainer'      => 1.5, // XP multiplier in trainer battles.
            'gym'          => 2,   // XP multiplier in gym battles.
            'elite-four'   => 2,   // XP multiplier in Elite Four and Champion battles.
            'battle-tower' => 1,   // XP multiplier in Battle Tower battles.
        ],
    ],

    'battle' => [
        'stab_multiplier'         => 1.5,  // Extra damage when the move's type is one of the attacker's types (1.5 = 50% more).
        'critical_hit_multiplier' => 1.5,  // Extra damage a critical hit deals (1.5 = 50% more).
        'critical_hit_chance'     => 6.25, // 6.25% chance (1 in 16) that a hit is critical.
        'damage_random_min'       => 0.85, // Lowest random damage roll per hit (0.85 = a hit can deal down to 85%).
        'keep_ended_days'         => 7,    // Days an ended battle is kept before it is deleted.
        'end_screen_minutes'      => 10,   // Minutes after a battle ends that the battle page still shows its end screen.

        'ailments' => [
            'burn_damage_percentage'      => 6.25, // Share of max HP a burned Pokémon loses at the end of each turn (6.25 = 1/16).
            'burn_damage_done_multiplier' => 0.5,  // Damage a burned Pokémon deals (0.5 = half).
            'poison_damage_percentage'    => 12.5, // Share of max HP a poisoned Pokémon loses at the end of each turn (12.5 = 1/8).
            'paralysis_chance'            => 25,   // 25% chance that a paralyzed Pokémon can't act on its turn.
            'sleep_min_turns'             => 1,    // Fewest turns a Pokémon stays asleep.
            'sleep_max_turns'             => 3,    // Most turns a Pokémon stays asleep.
            'freeze_thaw_chance'          => 20,   // 20% chance that a frozen Pokémon thaws out at the start of its turn.
            'confusion_min_turns'         => 1,    // Fewest turns a Pokémon stays confused.
            'confusion_max_turns'         => 4,    // Most turns a Pokémon stays confused.
            'confusion_self_hit_chance'   => 33,   // 33% chance that a confused Pokémon hits itself instead of acting.
            'confusion_self_hit_power'    => 40,   // Power of the typeless hit a confused Pokémon deals to itself.
        ],
    ],
];
