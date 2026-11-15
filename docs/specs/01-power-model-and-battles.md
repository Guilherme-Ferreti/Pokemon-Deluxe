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

Level factor = level + 10, so a level 100 Pokémon is 10× a level 1, not 100×. A level 100 Pokémon with no boost and no shine has 400 HP; a level 1 has 40.

```math
\text{HP} = 400 \times \frac{\text{level} + 10}{110} \times \left(1 + \frac{\text{boost}}{100}\right) \times \text{shiny}_{1.25}
```

```math
\text{Damage} = \text{power} \times \frac{\text{level} + 10}{77} \times \left(1 + \frac{\text{boost}}{100}\right) \times \text{shiny}_{1.25} \times \text{STAB}_{1.5} \times \text{type} \times \text{crit}_{1.5} \times \text{random}_{0.85\text{–}1} \times \text{defender shiny}_{0.75}
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

- Every species has exactly 4 different base moves, and every Pokémon starts with them in its 4 active slots. It keeps a **pool of known moves** and **4 active slots**, swapped freely outside battle.
- The pool grows only by TM or evolution. A TM is used up when taught, and any Pokémon can learn any move.
- On evolution, the new species' base moves join the pool. Example: Charmander knows A B C D; Charmeleon's base moves are A E F G; after evolving it knows A B C D E F G.
- **Z-Move TMs:** only level 100 Pokémon can learn them; usable once per battle.
- **Dynamax-move TMs:** only level 100 Pokémon can learn them; only G-Max Pokémon can use them; once per battle.
- No PP: other moves are unlimited. One-hit-KO moves are removed from the game.

## Battle flow

1. The battle is saved on the server: refreshing or closing the page resumes the same turn. One active battle per player. A battle that uses the party starts with its first Pokémon, in the order set on the Party page (Spec 4).
2. Each turn the player picks one of the 4 active moves, switches to another Pokémon (see Switching), or throws a ball in a wild battle (Spec 2).
3. Turn order is rolled every turn: chance to move first = my level ÷ (my level + their level).
4. Moves can miss (accuracy), land a critical hit (1 in 16, ×1.5), flinch, or hit several times. Each hit of a multi-hit move rolls its own critical hit and random factor. Type effectiveness and STAB (×1.5) apply. Tri Attack's ailment is burn, paralysis or freeze, picked at random.
5. Status effects (burn, poison, paralysis, sleep, freeze, confusion) are in from the first version and last only for that battle. Status-only moves such as Thunder Wave are kept.
6. When a Pokémon faints, **the player picks which Pokémon comes out next**, like the main games. Free send-outs happen only after a faint; any other switch uses a turn. No items in battle.
7. The player can run at any time with the **Run** button. Running counts as a loss. Losing has no penalty: trainers pay nothing, but XP already earned is kept (see XP).
8. After every battle all Pokémon are fully healed, except in the Battle Tower, where damage carries over between floors until a checkpoint (Spec 6).
9. An ended battle is kept for 7 days (for the end screen and the reactions to it), then deleted. An ongoing battle is never deleted. There is no battle history yet.

## Switching

- On their turn, instead of using a move, the player can **switch** the Pokémon in battle for any other Pokémon of the battle's team that hasn't fainted, like the main games.
- The battle's team is the snapshot taken when the battle started: the party for most battles, the Pokémon picked for a gym, the run's team in the Battle Tower.
- Switching uses the player's turn: the new Pokémon always comes out before the opponent acts, then the opponent attacks it. End-of-turn effects (burn, poison) still apply.
- A Pokémon that switches in has come onto the field, so it gets XP (see XP).
- Burn, poison, paralysis, sleep and freeze stay with a Pokémon when it switches out (sleep turns keep counting only while it is in battle); confusion ends.
- NPC trainers never switch; they send out their next Pokémon only after a faint.

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

- A level costs 1,000 XP (`game.pokemon.experience_per_level`). Level 100 gains nothing.
- XP is counted during the battle and paid **when the battle ends**, whatever the result: won, lost, ran or caught. Opponents knocked out before a loss still count.
- Catching a wild Pokémon counts as defeating it (Spec 2).
- For each opponent Pokémon knocked out or caught, every Pokémon of the player's that came onto the field gets the full amount (it isn't split):

```math
\text{XP} = 250 \times \text{clamp}\left(\frac{\text{opponent level}}{\text{your Pokémon's level}}, 0.1, 3\right) \times \text{battle bonus}
```

- Battle bonus: wild ×1, trainer ×1.5, gym, Elite Four and Champion ×2, Battle Tower ×1.
- The Pokémon that knocked the opponent out, or that was on the field when it was caught, gets ×1.5.
- Pokémon that never came out get none, unless an XP Share is active: then the rest of the party gets 50% of the formula (with their own level, without the knockout bonus; see Spec 5). The XP Share does nothing in the Battle Tower.
- Battle Tower battles save their XP in the run and pay it when the run ends (Spec 6).
- Every number lives in `config/game.php` under `game.experience`.

| Situation | XP | Wins per level |
| --- | --- | --- |
| Lv 5 starter knocks out a wild Lv 7 | 525 | about 2 |
| Lv 30 beats a wild Lv 10 | 83 | about 12 |
| Lv 50 beats a Lv 50 trainer's Pokémon | 375–562 | about 2–3 |
| Lv 50 beats a Lv 55 gym leader's 5 Pokémon | about 2,750–4,100 in total | 3–4 levels |
| Lv 90 clears a Tower floor of 3 Lv 90 Pokémon | 750–1,125 | about 1 per floor |

Taking one Pokémon from level 1 to 100 takes about 300 fair fights, fewer when fighting stronger opponents.

## One battle at a time

- A player has at most one battle in progress, of any kind (wild, trainer, gym, Elite Four, Battle Tower). Starting another is refused with a link to the ongoing one.
- A Battle Tower run or an Elite Four run is not a battle in itself: between its battles the player can do anything else (Spec 6).

## Evolution

- When a Pokémon qualifies, an **Evolve** button appears; evolution is never automatic, so a level 100 Charmander is possible.
- Mega: level 100 plus the matching Mega Stone, which is used up. G-Max: level 100.
- Every evolution is permanent. Level, boost, shine and known moves are kept. Megas and G-Max cannot be caught or hatched.

## Battle page

```
┌──────────────────────────────────────────────────────────┐
│  Wild Pokémon battle                              [Run]  │
├──────────────────────────────────────────────────────────┤
│                     OPPONENT  Pidgey ♂  Lv 34  +5         │
│                     HP ████████░░░░  62%   [PAR]          │
│                            (sprite)                       │
│   (your sprite)                                           │
│  YOU  Shiny Magikarp ♀  Lv 100  +72                       │
│  HP ██████████░░  410 / 520                               │
│  Team: ● ● ● ○ ○ ○                                         │
├──────────────────────────────────────────────────────────┤
│  [ Surf      WATER  90 ]   [ Thunder Wave  ELEC  — ]      │
│  [ Ice Beam  ICE    90 ]   [ Tackle        NORM  40 ]     │
│  [ Throw ball ] (wild battles only)  [ Switch Pokémon ]   │
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
- **Switch Pokémon** (every battle) opens a similar dialog that can be cancelled: the Pokémon in battle and fainted ones are disabled, and it says that switching uses the turn.
- The only animation is the HP bar sliding down.
- **Run** asks for confirmation. Against trainers it says that running counts as a loss; in an Elite Four run, that it also ends the run.
- When the battle ends, the moves area becomes a result card for every ending (won, lost, ran, caught): the result, the money earned against trainers, and one row per Pokémon that came out with the XP it got, an XP bar and any level-up. With the XP Share on, one more line sums up what the rest of the party got. A loss with no opponent knocked out says "No XP this time". Tower battles show the XP saved in the run instead.
