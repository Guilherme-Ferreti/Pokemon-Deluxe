# Spec 1 — Power model and battle system

Status: agreed.

Every Pokémon has only HP, Attack and Defense, identical for every species at level 1 with no boost and no shine; level, boost and shine change them. Battles are turn-based, like Pokémon Deluge.

## Stats

Species decides only base moves and types. Legendaries get no bonus.

| Source | HP | Damage dealt | Damage taken |
| --- | --- | --- | --- |
| Level 1–100 | grows with level | grows with level | — |
| Boost +0 to +100 | +1% per boost level | +1% per boost level | — |
| Shiny | +25% | +25% | −25% |
| Mega / G-Max | — | — | — |

## Formulas

Level factor = level + 10, so a level 100 Pokémon is 10× a level 1, not 100×.

```math
\text{HP} = 4 \times (\text{level} + 10) \times \left(1 + \frac{\text{boost}}{100}\right) \times \text{shiny}_{1.25}
```

```math
\text{Damage} = \text{power} \times \frac{\text{level} + 10}{70} \times \left(1 + \frac{\text{boost}}{100}\right) \times \text{shiny}_{1.25} \times \text{STAB}_{1.5} \times \text{type} \times \text{crit}_{1.5} \times \text{random}_{0.85\text{–}1} \times \text{defender shiny}_{0.75}
```

| Matchup (neutral 80-power move, no boost) | Expected result |
| --- | --- |
| Lv 100 vs Lv 100 | about 4 hits to KO each other; each moves first 50% of turns |
| Lv 100 vs Lv 50 | Lv 100 wins in about 2 hits; Lv 50 needs about 6 |
| Lv 50 (STAB, super effective, +100 boost) vs Lv 100 (no boost) | Lv 50 needs about 2 hits and survives about 4: a real chance |
| Lv 100 vs Lv 1 | one hit; Lv 1 moves first about 1% of turns |

## Boost stones

- Each stone adds +1 boost level, up to +100. The boost stays with the Pokémon after a trade.
- There is one kind of stone. A failed stone is used up; the boost never goes down.
- From +0 to +20 a stone always works. Above +20 each level adds 1% failure chance, so +99 to +100 fails 80% of the time. Reaching +100 takes about 180 stones on average.

## Moves and TMs

- Every Pokémon starts with its species' 4 base moves. It keeps a **pool of known moves** and **4 active slots**, swapped freely outside battle.
- The pool grows only by TM or evolution. A TM is used up when taught, and any Pokémon can learn any move.
- On evolution, the new species' base moves join the pool. Example: Charmander knows A B C D; Charmeleon's base moves are A E F G; after evolving it knows A B C D E F G.
- **Z-Move TMs:** only level 100 Pokémon can learn them; usable once per battle.
- **Dynamax-move TMs:** only level 100 Pokémon can learn them; only G-Max Pokémon can use them; once per battle.
- No PP: other moves are unlimited. One-hit-KO moves are removed from the game.

## Battle flow

1. The battle is saved on the server: refreshing or closing the page resumes the same turn. One active battle per player.
2. Each turn the player picks one of the 4 active moves (or throws a ball in a wild battle, see Spec 2).
3. Turn order is rolled every turn: chance to move first = my level ÷ (my level + their level).
4. Moves can miss (accuracy), land a critical hit (1 in 16, ×1.5), flinch, or hit several times. Type effectiveness and STAB (×1.5) apply.
5. Status effects (burn, poison, paralysis, sleep, freeze, confusion) are in from the first version and last only for that battle. Status-only moves such as Thunder Wave are kept.
6. When a Pokémon faints, **the player picks which Pokémon comes out next**, like the main games. No other switching, no items in battle.
7. The player can give up at any time. Losing has no penalty other than no reward.
8. After every battle all Pokémon are fully healed.

## Status effects

| Effect | Rule |
| --- | --- |
| Burn | loses 1/16 of max HP at the end of each turn; deals half damage |
| Poison | loses 1/8 of max HP at the end of each turn |
| Paralysis | 25% chance to be unable to act each turn |
| Sleep | cannot act for 1–3 turns |
| Freeze | cannot act; 20% chance to thaw at the start of each turn |
| Confusion | lasts 1–4 turns; 33% chance to hit itself (40-power typeless hit) instead of acting |

- A Pokémon has at most one of burn, poison, paralysis, sleep or freeze. Confusion can stack with one of them.
- A move's ailment does not apply if the target already has one (except confusion).
- Numbers live in `config/game.php` under `game.battle.ailments`.

## XP

- Every Pokémon that came onto the field gets XP; the one that knocked out the opponent gets more.
- Pokémon that never came out get none, unless an XP Share is active (see Spec 5).
- Higher-level opponents give more XP. Level 100 gains nothing. Amounts are tuned later; today a level costs 1,000 XP.

## Evolution

- When a Pokémon qualifies, an **Evolve** button appears; evolution is never automatic, so a level 100 Charmander is possible.
- Mega: level 100 plus the matching Mega Stone, which is used up. G-Max: level 100.
- Every evolution is permanent. Level, boost, shine and known moves are kept. Megas and G-Max cannot be caught or hatched.

## Battle page

```
┌──────────────────────────────────────────────────────────┐
│  Wild Pokémon battle                          [Give up]  │
├──────────────────────────────────────────────────────────┤
│                     OPPONENT  Pidgey ♂  Lv 34  +5         │
│                     HP ████████░░░░  62%   [PAR]          │
│                            (sprite)                       │
│   (your sprite)                                           │
│  YOU  Shiny Magikarp ♀  Lv 100  +72  ★                    │
│  HP ██████████░░  410 / 520                               │
│  Team: ● ● ● ○ ○ ○                                         │
├──────────────────────────────────────────────────────────┤
│  [ Surf      WATER  90 ]   [ Thunder Wave  ELEC  — ]      │
│  [ Ice Beam  ICE    90 ]   [ Tackle        NORM  40 ]     │
│  [ Throw ball ]   (wild battles only)                     │
├──────────────────────────────────────────────────────────┤
│  Battle log                                               │
│  > Magikarp used Surf! It's super effective!              │
│  > Pidgey is paralyzed! It can't move!                    │
└──────────────────────────────────────────────────────────┘
```

- Opponent at the top right, the player at the bottom left, as in the main games. On a phone the parts stack: opponent, player, moves, log.
- The player's HP shows exact numbers; the opponent's shows only a percentage.
- Move buttons show type and power, with a hint on super-effective moves.
- Team dots show who is left on each side. A battle log lists what happened.
- When a Pokémon faints, a dialog lists the remaining team (portrait, level, HP) to pick the next one.
- The only animation is the HP bar sliding down.
