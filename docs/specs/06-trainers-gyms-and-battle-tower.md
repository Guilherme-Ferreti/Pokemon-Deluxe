# Spec 6 — NPC trainers, Gyms and the Battle Tower

Status: agreed.

All three reuse the Spec 1 battle engine: trainers are the main source of money, gyms lead to each region's Elite Four and Champion, and the Battle Tower is a 1,000-floor run with damage carrying over between checkpoints.

## NPC trainers

- Players find trainers on a **Trainers page** and sometimes **meet one while exploring** an area (about 1 in 20 explores).
- Trainers on the Trainers page are hand-made characters with fixed teams. Only the hardest ones have shinies.
- Trainers met while exploring are randomly generated, at levels similar to the area's wild Pokémon.
- Rematches are unlimited. Money shrinks when the player's team outlevels the trainer.
- Money = full payout × (trainer's average level ÷ average level of the player's Pokémon that came onto the field), never more than the full payout and never less than 10%. Only level counts, not boost. The same formula applies to trainers met while exploring.

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
- Gyms are plain battles with one rule: the player picks as many Pokémon **from the party** as the leader has (a leader with 3 Pokémon means 3 of yours).
- Elite Four and Champion: the player uses their party, and the team is fully healed after each battle.
- A gym win gives the badge and an achievement; achievement rewards come from Spec 5. Rematches give no special prize.

## Battle Tower

- The player picks 6 Pokémon from the party or the Box. No level cap: real level and boost apply.
- Each floor is one battle against a **randomly generated** NPC team: 3 Pokémon on normal floors, 6 on checkpoint floors.
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

- One list, no difficulty groups. Each row: sprite, name, team size, level range, types used, full payout and Battle.

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
│ Your team (pick 6):  [slot][slot][slot][slot][slot][slot]│
│                      [ Choose from Party / Box ]         │
│                                    [ Start run ]         │
└──────────────────────────────────────────────────────────┘
During a run: floor number, team HP that carries over,
next checkpoint, [Next battle] [Quit run]
```
