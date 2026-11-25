<?php

declare(strict_types=1);

return [
    'user' => [
        'max_incubators' => 10,   // Number of incubator slots in the Nursery, bought one at a time.
        'max_box_size'   => 9999, // Most Pokémon a player can own; catching or hatching more is refused.
        'max_party_size' => 6,    // Most Pokémon in a player's party; a caught Pokémon joins it while there is room.
    ],

    'registration' => [
        'starters' => [ // First-stage starters a new player can choose from, three per region.
            'bulbasaur', 'charmander', 'squirtle',
            'chikorita', 'cyndaquil', 'totodile',
            'treecko', 'torchic', 'mudkip',
            'turtwig', 'chimchar', 'piplup',
            'snivy', 'tepig', 'oshawott',
            'chespin', 'fennekin', 'froakie',
            'rowlet', 'litten', 'popplio',
            'grookey', 'scorbunny', 'sobble',
            'sprigatito', 'fuecoco', 'quaxly',
        ],
        'starter_level'  => 5, // Level of the starter a new player chooses.
        'starting_items' => [
            'pokeballs' => [ // Balls a new player starts with, by slug and quantity.
                'poke-ball'  => 20,
                'great-ball' => 5,
            ],
        ],
    ],

    'economy' => [
        'starting_money' => 2000, // Pokédollars a new player starts with, enough for 10 Poké Balls.
    ],

    'shop' => [
        'daily_tm_count'        => 10,  // TMs for sale each day, the same for every player.
        'sell_back_ratio'       => 0.5, // Share of an item's price paid when the player sells it back.
        'confirm_purchase_over' => 10000, // Purchases costing more than this ask the player to confirm first.

        'tm_prices' => [
            'status_move'  => 3000,  // Cost for a TM of a move with no power.
            'by_min_power' => [      // Cost for a TM by the lowest power it needs, checked from the top.
                90 => 10000,
                60 => 5000,
                0  => 2000,
            ],
        ],
    ],

    'inventory' => [
        'max_stack' => 9999, // Most units of one item (ball, evolution item) a player can hold.
    ],

    'incubation' => [
        'hatch_time_in_hours' => 24,   // Hours an egg incubates before it is ready to hatch.
        'price_step'          => 5000, // Money each next incubator costs more than the last; the first is free.
    ],

    'pokemon' => [
        'max_level'            => 100,      // Highest level a Pokémon can reach.
        'max_boost'            => 100,      // Highest boost a Pokémon can reach with boost stones.
        'experience_per_level' => 1000,     // XP needed to go up one level.
        'max_active_moves'     => 4,        // Number of active move slots a Pokémon uses in battle.
        'shiny_chance'         => 1,        // 1% chance that a hatched egg or a wild Pokémon is shiny.

        'stats' => [
            'max_level_hp'                  => 400,  // Max HP of a level 100 Pokémon with no boost and no shine.
            'level_bonus'                   => 10,   // How close low and high levels are in strength (higher = smaller gap).
            'damage_scale'                  => 77,   // How much damage a Pokémon deals for its level (higher = less damage).
            'shiny_hp_multiplier'           => 1.25, // Extra max HP a shiny Pokémon has (1.25 = 25% more).
            'shiny_damage_done_multiplier'  => 1.25, // Extra damage a shiny Pokémon deals (1.25 = 25% more).
            'shiny_damage_taken_multiplier' => 0.75, // Damage a shiny Pokémon takes (0.75 = 25% less).
        ],
    ],

    'boost' => [
        'safe_until'   => 20, // Boost below which a boost stone always works (from +20 → +21 it can fail).
        'failure_step' => 1,  // Failure chance in % each boost level adds from safe_until (1 = +20 → +21 fails 1%, +99 → +100 fails 80%).
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

    'exploration' => [
        'legendary_chance' => 0.2,      // 0.2% chance (1 in 500) that an encounter is a legendary or mythical, when the area has any.
        'stage_weights'    => [1 => 85, 2 => 12, 3 => 3], // Weights of each evolution stage in an encounter (85% unevolved, 12% second, 3% third).
    ],

    'catching' => [
        'low_hp_bonus'          => 1.5, // Extra catch chance as HP falls (1.5 = up to 150% more at 0 HP).
        'night_starts_at'       => 18,  // Server hour night starts, when the Dusk Ball is boosted.
        'night_ends_at'         => 6,   // Server hour night ends.
        'nest_ball_max'         => 3,   // Highest Nest Ball multiplier, against the lowest-level Pokémon.
        'nest_ball_until_level' => 20,  // Wild level from which the Nest Ball is back to 1x.
        'ailment_multipliers'   => [    // Catch chance multiplier for each status effect; any other is 1.
            'sleep'     => 2,
            'freeze'    => 2,
            'paralysis' => 1.5,
            'burn'      => 1.5,
            'poison'    => 1.5,
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
