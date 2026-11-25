# Spec 6 — NPC trainers, Gyms and the Battle Tower

Status: agreed.

All three reuse the Spec 1 battle engine: trainers are the main source of money, gyms lead to each region's Elite Four and Champion, and the Battle Tower is a 1,000-floor run with damage carrying over between checkpoints.

## NPC trainers

- Players find trainers on a **Trainers page** and sometimes **meet one while exploring** an area (about 1 in 20 explores).
- Trainers on the Trainers page are hand-made characters with fixed teams. Only the hardest ones have shinies.
- Trainers met while exploring are randomly generated, at levels similar to the area's wild Pokémon.
- Rematches are unlimited. Money shrinks when the player's team outlevels the trainer.
- Money = full payout x (trainer's average level ÷ average level of the player's Pokémon that came onto the field), never more than the full payout and never less than 10%. Only level counts, not boost. The same formula applies to trainers met while exploring.

| Player's field average vs trainer's average (Lv 40) | Money |
| --- | --- |
| Lv 40 or lower | 100% |
| Lv 50 | 80% |
| Lv 80 | 50% |
| Lv 100 | 40% |

## Gyms, Elite Four and Champion

- 8 gyms per region, as in the main games, plus each region's Elite Four and Champion.
- All regions are at about the same level. Leaders use their main-game teams, at levels suited to this game.
- Gyms can be beaten in any order. All 8 badges of a region are needed to challenge its Elite Four and Champion.
- Gym leaders bring 2 to 6 Pokémon, depending on the leader. Gyms are plain battles with one rule: the player picks **from the party** up to as many Pokémon as the leader has, never more (a leader with 3 Pokémon means up to 3 of yours). Bringing fewer is allowed after a confirmation, including when the party is smaller than the leader's team.
- Elite Four and Champion: the player uses their party in party order (Spec 4, Party page), and the team is fully healed after each battle.
- A gym win gives the badge and an achievement; achievement rewards come from Spec 5. Rematches give no special prize.

## Battle Tower

- The player picks 6 Pokémon from the party or the Box, in sending-out order. No level cap: real level and boost apply.
- The team's order can be changed before the run starts and between floors; the first Pokémon with HP left goes out first.
- Each floor is one battle against a **randomly generated** NPC team: 3 Pokémon on normal floors, 6 on checkpoint floors. Teams use species that can appear in the wild; legendaries and mythicals only after floor 50 (configurable).
- No healing between floors. Checkpoints give a full heal, and the gap between them grows: checkpoints at floors 10, 25, 45, 70, 100, and so on (each gap 5 floors longer).
- Floors get harder up to the top floor, 1,000. Opponent level equals the floor number up to 100; from floor 101, opponent boost rises by about 1 every 9 floors, reaching +100 at floor 1,000. To be fine-tuned if reaching floor 1,000 turns out too easy.
- Losing ends the run. A run is saved on the server and can be resumed.
- Rewards are Battle Points per floor, with a bonus at each checkpoint. No items. Points are spent later (see Future ideas in the README).
- The best floor shows on the player's profile (Spec 8).

## Trainers page

```
TRAINERS                                   [Search by name]
┌──────────────────────────────────────────────────────────┐
│ (sprite) Hiker Bob      3 Pokémon · Lv 20–25   ₽ 1,800   │
│          [ROCK][GROUND]                 [ Battle ]       │
│ (sprite) Ace Trainer Liz  6 Pokémon · Lv 80–90 ₽ 12,000  │
│          [DRAGON][STEEL]                [ Battle ]       │
└──────────────────────────────────────────────────────────┘
```

- Tabs by trainer category (Townsfolk, Outdoors, Water, Fighters, Mystics, Experts), not by difficulty; each sorted by level. Each row: sprite, name, team size, level range, types used, full payout and Battle.

## Gyms page

```
GYMS   [Kanto] [Johto] [Hoenn] [Sinnoh] ...   Kanto badges 5 / 8
┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐
│ badge│ │ badge│ │ badge│ │ badge│ │ badge│ │ (?)  │
│Boulde│ │Cascad│ │Thundr│ │Rainbw│ │ Soul │ │Marsh │
└──────┘ └──────┘ └──────┘ └──────┘ └──────┘ └──────┘
Click a gym → leader, team preview, rules, rewards, [Challenge]
```

- A tab per region, each a badge case with the Elite Four and Champion below it, locked until all 8 badges; earned badges in colour, missing ones as outlines. Clicking a gym opens its leader, team preview, rule and Challenge.

## Battle Tower lobby

```
BATTLE TOWER        Best floor: 37      Battle Points: 1,240
┌──────────────────────────────────────────────────────────┐
│ Your team (pick 6, in order): [1][2][3][4][5][6]  ↑↓     │
│                      [ Choose from Party / Box ]         │
│                                    [ Start run ]         │
└──────────────────────────────────────────────────────────┘
During a run: floor number, the team in order with the HP
that carries over (reorder with ↑↓ or drag), next checkpoint,
[Next battle] [Quit run]
```

## Details (agreed)

Gaps found while splitting this spec into tasks, agreed on 13 Nov 2026.

- **Gym money:** gym battles pay money with the trainer formula on every win, like any trainer. The badge and achievement come only from the first win.
- **Gym levels:** the same in every region, rising with the gym's number: gym 1 Lv 12–15, gym 2 Lv 18–21, gym 3 Lv 24–27, gym 4 Lv 30–33, gym 5 Lv 36–39, gym 6 Lv 42–45, gym 7 Lv 48–51, gym 8 Lv 54–57. Elite Four Lv 62–68 with +10 boost; Champion Lv 70–75 with +20 boost. Badge order stays free.
- **Elite Four run:** the four members and then the Champion are fought in order, healed after each battle. Losing or forfeiting ends the run; the next attempt starts again from the first member. Between battles the player can quit the run (with a confirmation) from the Gyms page or the battle's end screen. Beating the Champion records it on the profile (Spec 8).
- **Elite Four money:** the four members pay nothing. Beating a region's Champion for the first time pays ₽ 30,000 (`game.trainers.champion_prize`), with no level reduction. Later wins against that Champion pay nothing.
- **Trainers met while exploring:** 1 to 3 Pokémon, each picked like a wild encounter in that area (85% unevolved, 12% second stage, 3% third stage, a legendary or mythical 1 in 500) at its wild levels. Full payout ₽ 100 per level of the team's average level (`game.trainers.explore_payout_per_level`), reduced like any trainer.
- **Hand-made trainers:** about 30 to start, spread from Lv 5 to Lv 100, payouts from ₽ 500 to ₽ 20,000.
- **Opponent behaviour:** NPCs pick a random active move each turn and send out their next Pokémon in team order.
- **Battle Points:** 1 per floor won, plus 5 x the checkpoint's number at each checkpoint (10 at the 2nd checkpoint, 25 at the 5th).
- **Tower XP:** Battle Tower battles give XP with Spec 1's formula, saved up in the run and paid to each team member when the run ends (lost, quit or floor 1,000). XP from the floor that was lost counts too. The XP Share does nothing in the Tower. The run summary shows each Pokémon's XP.
- **Legendaries in the Tower:** opponent teams include legendaries and mythicals only after floor 50 (`game.battle_tower.legendaries_after_floor`, 50), to be fine-tuned during play-testing.
- **Tower team order:** set when picking the team and changeable in the lobby and between floors (not during a Tower battle). A fainted Pokémon can sit anywhere in the order; it is skipped until a checkpoint heals it.
- **Frozen Tower team:** the run copies its 6 Pokémon when it starts (level, boost, moves, stats) and uses that copy for the whole run. Levelling, boosting or teaching a team member elsewhere doesn't change the run; the changes count from its next run.
- **Tower hold:** while a run is active, its 6 Pokémon can't be traded, released or evolved. They can still battle elsewhere between floors, join the party and use boost stones or TMs. The Pokémon page shows "In a Battle Tower run".
- **Other battles during a run:** allowed between floors; only one battle can be in progress at a time (Spec 1).
