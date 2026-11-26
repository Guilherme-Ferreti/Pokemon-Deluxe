# Spec 2 — Catching and exploration

Status: agreed.

Players pick an area, press Explore to meet one random wild Pokémon of that area's types, and catch it by battling and throwing balls.

## Areas

- Players explore **areas**, not a walkable map. A map can be added later on top of areas.
- Each area is themed by **type** and holds **every species** that has one of its types, so areas are large.
- No locked areas and no area-specific rare spawns. Levels and how often evolved forms appear follow the Encounters rules below.
- No limit or cooldown on exploring for now.

There are 9 areas, two types each, covering all 18 types. A dual-type species appears in every area that matches one of its types.

| Area | Types |
| --- | --- |
| Meadow | Normal, Fairy |
| Forest | Grass, Bug |
| Coast | Water, Flying |
| Volcano | Fire, Ground |
| Power Plant | Electric, Steel |
| Cave | Rock, Fighting |
| Snowy Peak | Ice, Dragon |
| Haunted Tower | Ghost, Dark |
| Ancient Ruins | Psychic, Poison |

## Encounters

- One wild Pokémon at a time, saved on the server. **Keep exploring** throws it away and rolls a new one; **Battle** starts a wild battle.
- Shiny chance 1 in 100, the same as eggs.
- Megas, G-Max and baby Pokémon never appear in the wild. Babies come only from eggs.
- Unevolved Pokémon are common; evolved forms appear rarely. Each encounter is 85% unevolved, 12% second stage, 3% third stage, then a species is picked evenly from that group in the area.
- Pokémon that do not evolve (Tauros, Lapras) and Pokémon whose only pre-evolution is a baby (Pikachu, from Pichu) count as unevolved.
- Legendaries and mythicals appear in 1 in 500 encounters.
- About 1 in 20 explores meets an NPC trainer instead (see Spec 6).
- A caught Pokémon keeps its wild level.
- Wild battles are always one Pokémon against the player's party. **Run** always works.

Each species stores its own wild level range (`wild_min_level` and `wild_max_level` in the species data files), and a wild Pokémon's level is picked evenly within it. Both are null for species that never appear in the wild. The ranges are tuned per species; these guidelines set the first values and are followed for new species:

| Wild Pokémon | Level | Example |
| --- | --- | --- |
| Unevolved | 2–12 | Charmander: 2–12 |
| Evolved by level | its evolution level to 4 above | Charizard (evolves at 36): 36–40 |
| Evolved another way (item, gender, other) | 18–22, never below the previous stage's minimum | Raichu: 18–22 |
| Legendary or mythical | 50–70 | — |

No wild Pokémon is above level 100.

## Catching

- **Throw ball** is available on any turn of a wild battle. A throw uses the player's turn, so the wild Pokémon still attacks.
- A failed ball is used up. Wild Pokémon never flee.
- **Catching counts as winning the wild battle:** the player's Pokémon get XP as if they had knocked it out (Spec 1), and the catch counts for "win battles" missions (Spec 5).
- Each species has its own catch rate, from 3 (most legendaries and mythicals) to 255 (common species such as Pidgey), as in the main games. The Pokédex shows it as a percentage.
- Catch chance rises as the wild Pokémon's HP falls and when it has a status effect.

The formula, capped at 100%:

```math
\text{chance} = \text{base} \times \text{ball} \times \left(1 + 1.5 \times (1 - \text{HP\%})\right) \times \text{status}
```

- Base: the species' catch rate ÷ 255, as a percentage (255 → 100%, 45 → 17.6%, 3 → 1.2%).
- Status: sleep or freeze x2; paralysis, burn or poison x1.5; none or confusion x1.

| Situation | Catch rate 190 (Pikachu) | Catch rate 45 (Charmander) | Catch rate 3 (Mewtwo) |
| --- | --- | --- | --- |
| Poké Ball, full HP | 75% | 18% | 1% |
| Poké Ball, 10% HP | 100% | 41% | 3% |
| Poké Ball, 10% HP, asleep | 100% | 83% | 6% |
| Ultra Ball, 10% HP, asleep | 100% | 100% | 11% |
| Master Ball | 100% | 100% | 100% |

## Ball conditions

Balls have conditions, as in the main games. The game has no weight, speed, fishing or friendship, so those conditions are adapted.

| Ball | Multiplier |
| --- | --- |
| Poké Ball | 1x |
| Great Ball | 1.5x |
| Ultra Ball | 2x |
| Master Ball | always catches; only from difficult missions |
| Quick Ball | 5x on the first turn, 1x after |
| Timer Ball | 1x, +0.3x per turn, up to 4x |
| Repeat Ball | 3.5x if the player already owns that species |
| Nest Ball | 3x against level 2 Pokémon, falling evenly to 1x at level 20 and above: 1 + (20 − level) ÷ 9, kept between 1x and 3x (level 11: 2x) |
| Level Ball | compares the player's active Pokémon with the wild one: 2x when its level is higher, 4x when it is at least double, 8x when it is at least four times; 1x otherwise |
| Dusk Ball | 3x at night (server time 18:00–06:00) or in the Cave |
| Dream Ball | 4x if the wild Pokémon is asleep |
| Love Ball | 8x if same species and opposite gender to the active Pokémon |
| Moon Ball | 4x on species that evolve with a Moon Stone |
| Net Ball | 3.5x on Bug and Grass types |
| Lure Ball | 3.5x on Water types |
| Dive Ball | 3.5x in the Coast |
| Heavy Ball | 3.5x on Rock, Steel and Ground types |
| Fast Ball | 3.5x on Electric and Flying types |
| Beast Ball | 5x on Ultra Beasts, 0.1x on everything else (needs a new Ultra Beast mark on species) |
| Luxury, Premier, Heal, Friend, Cherish | 1x; collectible looks |
| Safari, Sport, Park | 1.5x; event-only collectibles |

## After a catch

- The Pokémon gets its species' base moves and a random gender.
- It joins the party if there is room, otherwise the Box. If the Box is full (9,999), catching is blocked.
- The ball used is recorded on the Pokémon, as today.

## New players

- At registration the player picks a starter at level 5: any first-stage starter from any region.
- The player also gets 20 Poké Balls and 5 Great Balls.

## Explore page

```
┌──────────────────────────────────────────────────────────┐
│  Explore                                                  │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐       │
│  │ (area image) │ │ (area image) │ │ (area image) │       │
│  │ Forest       │ │ Coast        │ │ Volcano      │       │
│  │ [GRASS][BUG] │ │ [WATER][FLY] │ │ [FIRE][GRND] │       │
│  │ [ Explore ]  │ │ [ Explore ]  │ │ [ Explore ]  │       │
│  └──────────────┘ └──────────────┘ └──────────────┘       │
│  ... one card per area; 3 per row on desktop, 1 on phones │
└──────────────────────────────────────────────────────────┘
```

- One card per area: image, name, type badges (the same badges the Pokédex uses) and an Explore button.

## Area page

```
┌──────────────────────────────────────────────────────────┐
│  ← Explore    Forest  [GRASS][BUG]    [Pokémon found here] │
│  ┌─────────────────────────────────────────────────────┐  │
│  │      A wild Shiny Caterpie ♀ (Lv 7) appeared!      │  │
│  │                     (sprite)                        │  │
│  │                    [BUG]                            │  │
│  │         [ Battle ]        [ Keep exploring ]        │  │
│  └─────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────┘
```

- The encounter card is the centre of the page. Shiny and legendary encounters get a gold border and badge so they stand out before the fight.
- **Pokémon found here** opens a modal: a grid of species portraits with names, a search box, and a type filter ("All types" or one type). No caught counts.
- On a phone the card fills the width and the two buttons stack.

## Catching in the battle page

- In wild battles a **Throw ball** button sits in the **More options** card, next to **Run** (Spec 1). It opens a ball picker (a bottom sheet on phones): each owned ball with its sprite, quantity and multiplier right now; balls whose condition applies get a "Boosted" tag. It is disabled while the player has to send out a Pokémon after a faint.
- A catch ends the battle with the result card (Spec 1): "Gotcha! Pidgey was caught", where it went (Party or Box), the XP each of the player's Pokémon got (with level-ups), and the buttons **View Pidgey** and **Keep exploring**.
