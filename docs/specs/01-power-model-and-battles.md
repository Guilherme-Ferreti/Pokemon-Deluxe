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
7. The player can leave at any time: **Run** in wild battles, **Forfeit** in every other battle. Either counts as a loss. Losing has no penalty: trainers pay nothing, but XP already earned is kept (see XP).
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
- A move's ailment does not apply if the target already has one (except confusion, which doesn't apply while the target is already confused).
- Some types can't get an ailment: Fire is never burned, Ice never frozen, Electric never paralyzed, Poison and Steel never poisoned.
- Status moves follow type effectiveness like damaging moves: Thunder Wave doesn't affect a Ground type. A status move whose ailment can't apply fails ("But it failed!").
- Before acting, effects are checked in this order: freeze (thaw roll), sleep, confusion (self-hit roll), paralysis. The first one that stops the Pokémon ends the check.
- A Pokémon that wakes up or thaws out acts on that same turn. Sleep for N turns means it misses N turns and acts on the next one.
- Confusion for N turns means N turns where the Pokémon reaches the confusion check (turns stopped by freeze or sleep don't count). It snaps out right after its last confused turn.
- The confusion self-hit uses the Pokémon's own attack and defense, with no STAB, type effectiveness, critical hit or random factor.
- Burn and poison damage comes at the end of the turn, after both Pokémon have acted (or been stopped), in the order they moved that turn. If the first one's damage knocks out its side's last Pokémon, the battle ends there and the second takes no damage.
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
- The Pokémon that knocked the opponent out, or that was on the field when it was caught, gets ×1.5. An opponent that faints from its own burn, poison or confusion counts as knocked out by the player's Pokémon on the field.
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
- Mega: level 100 plus the matching Mega Stone, which is used up. Mega Rayquaza has no Mega Stone and needs only level 100. G-Max: level 100.
- Every evolution item is used up when the Pokémon evolves: stones, Mega Stones and items like Metal Coat or King's Rock alike.
- Every evolution is permanent. Level, boost, shine and known moves are kept. Megas and G-Max cannot be caught or hatched.

## Battle page

```
Wild battle                                        (Elite Four steps)
A wild Pidgey appeared in the Forest · Turn 3
┌─────────────┬──────────────────────────────────┬──────────────────┐
│ YOUR TEAM ⓘ │  ┌ Pidgey ♀ PAR       Lv 34 ┐     │ HIKER BOB'S TEAM │
│ (●) Shiny   │  │ HP: ████░░  115 / 185    │ (o) │ (trainers only)  │
│ Magikarp ♀ ◓│  └──────────────────────────┘     ├──────────────────┤
│ ( ) Lapras  │ (p) ┌ Shiny Magikarp ♀  Lv 54 ┐   │ BATTLE LOG ⓘ     │
│  HP: ██ 91  │     │ HP: █████░  472 / 550   │   │ ⌄ TURN 2         │
│ ( ) Gible   │     └─────────────────────────┘   │ ▌Shiny Magikarp  │
│  Fainted    ├──────────────────────────────────┤ ▌ used **Surf**! │
│    [Switch] │ CHOOSE A MOVE                    │ ▌The wild Pidgey │
│             │ (●) Surf  WATER  ( ) Thunder Wave│ ▌ lost 70 HP.    │
│             │ ( ) Ice Beam ICE ( ) Tackle      │ › TURN 1         │
│             │                         [Attack] │ › START          │
│             ├──────────────────────────────────┤                  │
│             │ MORE OPTIONS [Throw ball] [Run]  │                  │
└─────────────┴──────────────────────────────────┴──────────────────┘
```

- Three columns on wide screens: **Your team** on the left; the battlefield, **Choose a move** and **More options** in the middle; the opponent's team (battles against trainers) and the **Battle log** on the right. On a phone the parts stack: result, battlefield, moves, Your team, log, More options.
- The header names the battle and the turn: "Wild battle" with "A wild Pidgey appeared in the Forest · Turn 3"; against trainers, the trainer's sprite and name with "Trainer battle · Turn 3", "Pewter City Gym · Rock · Turn 3", "Kanto Elite Four · battle 2 of 5 · Turn 3" (with the five Elite Four steps) or "Tower Trainer Lena · Turn 3".
- **Battlefield:** the opponent at the top right, the player at the bottom left, as in the main games, over a background that fits the place (wild, trainer arena, gym, Indigo Plateau, Battle Tower). Each card shows the name with its gender icon, the ailment, the level on the right ("Lv 34") and "HP:" with a bar and **both sides' HP as numbers** ("115 / 185"). On a phone, the opponent's card of a trainer battle also has team dots.
- **Choose a move:** the four moves as radio choice cards (name and type, no power and no effectiveness hint); the first is selected; **Attack** uses the selected move.
- **Your team:** the battle's team as a radio group. The Pokémon in battle has a Poké Ball icon and no HP bar; the checked item (at first the one in battle) has a muted background. The others show level, boost, ailment and "HP:" with bar and numbers; fainted ones are faded, greyscale and can't be picked. Picking a benched Pokémon moves the checked look to it (it keeps its HP bar) and enables **Switch**, which switches with no confirmation (see Switching). Long names wrap; the gender icon stays right after the name. Below the three-column layout the items keep the same rows, in as many columns as fit: one on a phone, two on a small tablet, three on a large one.
- **Fainting:** a card replaces the moves ("Shiny Magikarp fainted!"); nothing is checked in Your team until the player picks who to send out and presses **Send out** (free). On a phone this is a bottom sheet that can't be closed, with the same rows in as many columns as fit.
- **More options:** **Throw ball** (wild battles, Spec 2; disabled while waiting for a send-out) and **Run** in wild battles; **Forfeit** in every other battle. Both ask for confirmation: running from a wild battle gives nothing; forfeiting counts as a loss (no money, XP kept), and also ends an Elite Four or Tower run.
- **Opponent's team** (battles against trainers): the trainer's team, read-only, with the Pokémon in battle marked like the player's. Gym leaders and the Elite Four reveal their Pokémon only as they are sent out ("Not sent out yet"); other trainers show the whole team from the start.
- **Battle log:** one collapsible section per turn ("Start", "Turn 1", …), newest first; only the newest is open. Each message is a plain line, with no coloured bar beside it. Move names are bold; damage ("Gengar lost 96 HP.") and details (critical hits, effectiveness, multi-hit) are smaller and muted. No coloured or bold success or failure messages.
- **Result:** when the battle ends, the opponent's HP drains, the battlefield fades out and a result card slides in for every ending (won, lost, ran, caught): the title (with a badge on the right such as "New best floor!" or "Kanto Champion ✓"), **Your rewards** (money, a gym badge), and **XP gained**: one row per Pokémon that got XP with its gender icon, "+340 XP" and a "Level up! 54 → 55" pill on the right. With the XP Share on, each party Pokémon that got XP through it has its own row marked "via XP Share". No XP bars. A loss with no opponent knocked out says "No XP this time". Tower battles show the XP saved in the run instead. Buttons fit their text and sit on the right, the main one last.
- The battle log also gets one line per Pokémon that earned XP ("Shiny Magikarp gained 340 XP!") and one per level-up ("Shiny Magikarp grew to Lv 55!").
- **Motion:** nothing changes abruptly: HP bars slide, new log lines and dialogs fade in, bottom sheets slide up, and the end of a battle plays in order (HP drains, battlefield fades, result slides up). Reduced motion turns these off.
