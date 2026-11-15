# Pokémon Deluxe — Feature Specs

Pokémon Deluxe is a browser Pokémon game inspired by Pokémon Deluge (delugerpg.com) and by Pokétibias such as OTPokémon, with its own features on top. Its core rule: **every Pokémon can be strong.** Power comes from what the player invests (level, boost, moves, shiny), never from species, so a shiny level 100 Magikarp with a high boost and good TMs can match an Arceus. Shinies, Megas and G-Max stay valuable for rarity, looks and trade value.

The game is built as if it were public, but has no release plan yet.

## Roadmap

Each feature depends on the ones above it. All numbers in the specs are first proposals, to be tuned during play-testing.

| # | Spec | Status |
| --- | --- | --- |
| 1 | [Power model and battle system](01-power-model-and-battles.md) | agreed |
| 2 | [Catching and exploration](02-catching-and-exploration.md) | agreed |
| 3 | [Money, inventory and shop](03-money-inventory-and-shop.md) | agreed |
| 4 | [Pokémon page](04-pokemon-page.md) | agreed |
| 5 | [Missions](05-missions.md) | agreed |
| 6 | [NPC trainers, Gyms and the Battle Tower](06-trainers-gyms-and-battle-tower.md) | agreed |
| 7 | [Trading](07-trading.md) | agreed |
| 8 | [Social](08-social.md) | agreed |

Planned work is listed in [tasks.md](tasks.md). Names used in code follow [glossary.md](glossary.md).

## Open questions

- None right now. Numbers marked as first proposals are tuned during play-testing.

## Future ideas

Not planned yet; noted so they are not lost.

- **Expeditions:** send a few Pokémon away for a set time and collect rewards when they return (inspired by the spirit explorations in Super Smash Bros. Ultimate).
- **Battle Tower shop:** spend Battle Points earned in the Battle Tower on rewards.
- **Dungeons:** like the Battle Tower, a run of battles ending in a boss; beating it gives a pack of rewards instead of points.
- **More streak prizes** beyond the 7-day boost stone.
- **Weekly missions and story chains.**
- **TM collection achievements:** one achievement per move type ("Collect every Fire TM") and one for collecting every TM in the game. A TM counts once the player has owned it at least once; it does not have to stay in the inventory, so teaching, selling or trading it later keeps the progress. Needs a record of every TM a player has ever obtained.
- **Double battles.**
- **Pokémon history** on the Pokémon page (times traded, battles won, date it reached level 100).
- **Battle history:** a separate table recording each finished battle (opponent, result, date), since `battles` rows are deleted 7 days after they end.
- **Direct trades** and **selling Pokémon for money.**
- **Friends, chat and private messages.**
