# Spec 5 — Missions

Status: agreed.

Players get 3 daily missions and a list of one-time achievements; both are active automatically and pay out earned-only items when the player presses Claim.

## Kinds

- **Daily:** 3 missions a day (easy, medium, hard), generated from templates such as "Catch {5–10} {type} Pokémon". They reset at midnight server time. One free reroll per day.
- **Achievements:** one-time goals written by hand, such as "Catch 100 Pokémon", "Reach +50 boost on any Pokémon" or "Beat all gyms". Gym, Elite Four and Champion rewards are given as achievements (Spec 6).
- Weekly missions and story chains may come later.
- Idea for later: TM collection achievements, one per move type plus one for every TM, counting TMs the player has ever owned (see Future ideas in the README).

## Goals a mission can ask for

- Catch Pokémon: any, of a type, in an area, shiny, legendary.
- Win battles: wild, trainers, gyms, Battle Tower floors.
- Hatch eggs.
- Evolve or Mega evolve Pokémon.
- Reach a level or boost on a Pokémon.
- Teach TMs, spend money, make trades.

## Rewards

| Mission | Typical reward |
| --- | --- |
| Easy daily | ₽ 1,000–2,000, a few balls |
| Medium daily | ₽ 3,000, a normal TM or 1 boost stone |
| Hard daily | 2–3 boost stones, or a special ball pack |
| Achievements | Mega Stones, Z-Move and Dynamax TMs, many boost stones; the hardest give the Master Ball |

- Missions alone give roughly 4 boost stones a day, about 6–7 weeks to take one Pokémon to +100. Other reward sources (see Future ideas in the README) will speed this up.
- Rewards are given when the player presses **Claim**. If an item stack is full, the claim warns before going ahead.
- **Streak:** finishing all 3 dailies on consecutive days builds a streak. Every 7-day streak gives a bonus boost stone; other streak prizes may be added.

## XP Share

- A key item earned from an early achievement (such as "Win 10 battles").
- Turned on or off from the inventory.
- While on, party Pokémon that did not fight get 50% of the XP.

## Missions page

```
MISSIONS                                    Resets in 07:42:10
[Daily] [Achievements]

┌───────────────────────────────────────────────────────────┐
│ EASY   Catch 5 Water Pokémon                               │
│        ███████░░░  3 / 5            ₽ 1,500 · 5 Poké Balls │
│                                                  [Reroll]  │
├───────────────────────────────────────────────────────────┤
│ MEDIUM Win 3 battles against trainers                      │
│        ██████████  3 / 3  ✓         1 Boost Stone [ Claim ]│
├───────────────────────────────────────────────────────────┤
│ HARD   Catch a Pokémon at level 60 or higher               │
│        ░░░░░░░░░░  0 / 1            3 Boost Stones         │
└───────────────────────────────────────────────────────────┘
Streak: 4 days  ● ● ● ● ○ ○ ○   (7 days → bonus Boost Stone)
```

- Each card shows difficulty, goal, a progress bar and its reward.
- The **Claim** button is coloured and shown only when a mission is complete.
- A dot on "Missions" in the navigation bar when something is ready to claim.
- The Achievements tab groups the same cards by category (Catching, Battling, Collection…); finished ones are dimmed at the bottom.

## Starting content (proposal)

A first set to build and play-test with; easy to change because it lives in config and seed files.

### Daily templates

| Difficulty | Template | Reward |
| --- | --- | --- |
| Easy | Catch {5–10} Pokémon | ₽ 1,000 + 5 Poké Balls |
| Easy | Catch {3–5} {type} Pokémon | ₽ 1,500 + 3 Great Balls |
| Easy | Win {3–5} wild battles | ₽ 1,500 |
| Easy | Hatch {1–2} eggs | ₽ 2,000 |
| Medium | Win {3–5} battles against trainers | ₽ 3,000 + 1 boost stone |
| Medium | Catch {3–5} Pokémon in the {area} | ₽ 3,000 + a random normal TM |
| Medium | Evolve {1–2} Pokémon | ₽ 3,000 + 1 boost stone |
| Hard | Catch a Pokémon at level {50–60} or higher | 3 boost stones |
| Hard | Win {8–10} battles against trainers | 2 boost stones + 3 Ultra Balls |
| Hard | Climb {10–15} Battle Tower floors in one run | 3 boost stones |

Templates whose feature is not built yet (trainers, the Battle Tower) are skipped until it exists.

### Achievements

| Category | Achievement | Reward |
| --- | --- | --- |
| Catching | Catch 10 Pokémon | ₽ 5,000 |
| Catching | Catch 100 Pokémon | 10 boost stones |
| Catching | Catch 1,000 Pokémon | Master Ball |
| Catching | Catch a shiny Pokémon | 5 boost stones + a Cherish Ball |
| Catching | Catch a legendary or mythical Pokémon | 10 boost stones |
| Battling | Win 10 battles | XP Share |
| Battling | Win 100 battles | 10 boost stones |
| Battling | Beat a gym | ₽ 10,000 |
| Battling | Earn all 8 badges of a region | a random Mega Stone |
| Battling | Become Champion of a region | a random Z-Move TM |
| Battling | Reach floor 100 of the Battle Tower | a random Dynamax TM |
| Collection | Get a Pokémon to level 100 | 10 boost stones |
| Collection | Reach +50 boost on any Pokémon | a random Z-Move TM |
| Collection | Reach +100 boost on any Pokémon | Master Ball |
| Collection | Mega evolve a Pokémon | 10 boost stones |

### Counting rules

- "Catch" goals count wild catches only. Hatched Pokémon count for "Hatch eggs".
- "Reach a level / boost" goals look at the player's best Pokémon, so a Pokémon that already qualifies completes them at once.
