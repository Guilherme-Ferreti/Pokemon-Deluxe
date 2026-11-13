# Glossary and naming guide

One name per idea, used everywhere: specs, code, database, tests and UI. When you need a name, look here first. If the idea is missing, build the name from the recipes below and add it to this file in the same change.

## Game terms

| Term in the specs | Name in code | Notes |
| --- | --- | --- |
| Boost / boost level | `boost` (column, property, variable) | 0–100. Never "power", "rank" or "plus". |
| Boost stone | `BoostStone` / `boost_stone` | |
| Shiny | `is_shiny` (column), `isShiny` (variable) | |
| Stats | `PokemonStats` | Value object holding `hp`, `attack`, `defense`. |
| HP | `hp`, `max_hp`, `current_hp` | `max_hp` is the full value; `current_hp` is during battle. |
| Attack | `attack` | A damage multiplier, not a number shown as "Attack 120". |
| Defense | `defense` | A damage-taken multiplier (1.0, or 0.75 when shiny). |
| Known moves (the pool) | `knownMoves()` relation | Every move the Pokémon has learned. |
| Active moves (the 4 slots) | `activeMoves()` relation, `slot` (1–4) | Known moves with a slot. |
| Base moves | `baseMoves()` (species) | Already in the code. |
| STAB | `stab` | Same-type attack bonus. |
| Critical hit | `isCriticalHit` | |
| Type effectiveness | `typeMultiplier` | ×0, ×0.25, ×0.5, ×1, ×2, ×4. |
| Ailment / status effect | `Ailment` enum (existing) | Burn, poison, paralysis, sleep, freeze, confusion. |
| Battle | `Battle` model | One fight, saved on the server. |
| Battle side | `BattleSide` | The player's or the opponent's team in a battle. |
| Combatant | `Combatant` | One Pokémon inside a battle (a snapshot, not the `Pokemon` model). |
| Active combatant | `active_index` on `BattleSide` | The one currently on the field. |
| Turn | `turn` | Both sides acting once. |
| Battle log | `log` | List of text lines shown under the battle. |
| Faint | `isFainted()` | `current_hp` is 0. |
| Send out | `SendOutPokemonAction` | Picking the next Pokémon after a faint. |
| Forfeit / give up | `ForfeitBattleAction` | The UI says "Give up". |
| First mover | `firstMoverChance` | Chance to act first this turn. |
| Area | `Area` enum | The 9 places to explore (`Area::FOREST`). |
| Explore / Keep exploring | `ExploreAction` | Both roll a new encounter. |
| Encounter | `Encounter` model | The one wild Pokémon waiting for the player. |
| Wild level range | `wild_min_level`, `wild_max_level` | Null when a species never appears in the wild. |
| Evolution stage | `evolution_stage` | 1 = unevolved, 2, 3. Babies don't count. |
| Catch chance | `CatchChanceCalculator` | 0.0–1.0. |
| Ball multiplier | `BallMultiplierCalculator`, `catch_rate` (Poké Ball column) | The column holds the multiplier when no condition applies. |
| Throw ball | `ThrowBallAction` | |
| Starter | `starter` | The Pokémon chosen at registration. |
| Ultra Beast | `is_ultra_beast` | |
| Original trainer | `original_user_id` / `originalUser()` | Already in the code. |
| Party / Box | `in_party` / `inParty()`, `inBox()` | Already in the code. |

## Naming recipes

| Kind of thing | Recipe | Examples |
| --- | --- | --- |
| Action (does one thing, may write to the database) | `{Verb}{Noun}Action`, method `handle()` | `StartWildBattleAction`, `TakeTurnAction`, `EvolvePokemonAction` |
| Mechanics: pure math, no randomness | `{Noun}Calculator` in `{Domain}/Mechanics/` | `StatsCalculator`, `DamageCalculator`, `TurnOrderCalculator` |
| Mechanics: rolls dice through `RandomHelper` | `{Noun}Roller` in `{Domain}/Mechanics/` | `MoveOutcomeRoller`, `StatusEffectRoller` |
| Mechanics: answers "is this allowed?" | `{Noun}Checker`, returns `null` or a reason string | `EvolutionRequirementChecker` |
| Result of a mechanic | Value object `{Noun}` or `{Noun}Result` in `Shared/ValueObjects` | `PokemonStats`, `MoveOutcome` |
| Exception | `{What went wrong}Exception` | `BattleAlreadyInProgressException`, `MoveNotKnownException` |
| Event (task 0.4) | Past tense: `{Noun}{Verb in past tense}` | `PokemonCaught`, `BattleWon` |
| Enum | Singular noun, cases `UPPER_SNAKE_CASE` | `BattleStatus::ONGOING` |
| Boolean | `is…`, `has…`, `can…` | `isFainted`, `hasEnteredField`, `canEvolve` |
| Method returning a number | The noun, no `get` prefix | `maxHp()`, `firstMoverChance()` |
| Method computing something from inputs | `calculate()` on a Calculator, `roll()` on a Roller, `check()` on a Checker | |
| Config key | `game.{area}.{snake_case}` in `config/game.php` | `game.battle.stab_multiplier` |
| Migration | `{next number}_{verb}_{noun}_table.php` | `014_add_boost_to_pokemon_table.php` |
| Test file | `{ClassName}Test.php`, mirrored folders | `tests/App/Battle/Unit/Mechanics/DamageCalculatorTest.php` |

## Rules of thumb

- Use the name from the spec, not a synonym. If the spec says "boost", the code says `boost`.
- Name by what something **is** in the game, not how it is stored: `activeMoves()`, not `movesWithSlot()`.
- When stuck, ask the AI for three options using this file, pick one and add it to the table.
