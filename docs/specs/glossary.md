# Glossary and naming guide

One name per idea, used everywhere: specs, code, database, tests and UI. When you need a name, look here first. If the idea is missing, build the name from the recipes below and add it to this file in the same change.

**These are ideas, not final names.** The names here and in [tasks.md](tasks.md) are suggestions to start from, so nobody has to invent names from scratch. The final name is decided when a task is built, and it may differ (a Checker that returns a bool, a method named after what it answers, an existing method such as `getStats()` that keeps its `get` prefix). When it does, update this file and the later tasks that use the name in the same change.

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
| Random factor | `randomFactor` | 0.85–1.0 damage spread, rolled by the caller and passed to `DamageCalculator`. |
| Move hit | `MoveHit` | One hit of a move use, with its own `isCriticalHit` and `randomFactor`. A multi-hit move has several. |
| Move outcome | `MoveOutcome` | Everything the dice decided for one move use: `hasHit`, `hits`, `causesFlinch`, `inflictedAilment`. |
| Type effectiveness | `typeMultiplier` | ×0, ×0.25, ×0.5, ×1, ×2, ×4. |
| Ailment / status effect | `Ailment` enum (existing) | Burn, poison, paralysis, sleep, freeze, confusion. |
| Action blocker | `ActionBlocker` enum | Why a Pokémon can't act this turn: `FROZEN`, `ASLEEP`, `HURT_ITSELF_IN_CONFUSION`, `PARALYZED`. |
| Turn action outcome | `TurnActionOutcome` enum | What happened when one side acted in a turn: `SKIPPED` (already fainted), `BLOCKED` (by an action blocker), `MISSED`, `NO_EFFECT` (immune defender), `USED`, `CAUSED_FLINCH`, `KNOCKED_OUT`. |
| Ailment infliction | `AilmentInflictionChecker::canInflict()` | Whether a move's ailment can land on the target: not fainted, no major ailment yet (or not confused yet, for confusion), and not a type immune to it. |
| Battle | `Battle` model | One fight, saved on the server. |
| Battle state | `BattleState`, `state` column | Everything that changes during a battle (turn, both sides, log), saved as JSON with `AsBattleState`. |
| Battle side | `BattleSide` | The player's or the opponent's team in a battle. |
| Combatant | `Combatant` | One Pokémon inside a battle (a snapshot, not the `Pokemon` model). |
| Active combatant | `active_index` on `BattleSide` | The one currently on the field. |
| Turn | `turn` | Both sides acting once. |
| Battle log | `log` | List of text lines shown under the battle. |
| Faint | `isFainted()` | `current_hp` is 0. |
| Send out | `SendOutPokemonAction` | Picking the next Pokémon after a faint. Free. |
| Switch | `SwitchPokemonAction` | Changing the Pokémon in battle on the player's turn. Uses the turn. |
| Opponent's half of a turn | `OpponentActsAction` | The opponent's move, end-of-turn damage and fainting; shared by moves, switches and ball throws. |
| Run (forfeit) | `ForfeitBattleAction`, `BattleStatus::FORFEITED` | The UI says "Run". Counts as a loss. |
| Battle ended | `EndBattleAction`, `BattleEnded` event | The one place a battle ends; `isWin` for won and caught battles. |
| Earned XP | `earned_experience`, `level_before`, `level_after` | Each player combatant's result, shown on the end screen. |
| First mover | `firstMoverChance` | Percentage chance (0–100) to act first this turn. |
| Area | `Area` enum | The 9 places to explore (`Area::FOREST`). |
| Explore / Keep exploring | `ExploreAction` | Both roll a new encounter. |
| Encounter | `Encounter` model | The one wild Pokémon waiting for the player. |
| Wild level range | `wild_min_level`, `wild_max_level` | Null when a species never appears in the wild. |
| Evolution stage | `evolution_stage` | 1 = unevolved, 2, 3. Babies don't count. |
| Catch chance | `CatchChanceCalculator` | Percentage, 0–100. |
| Ball multiplier | `BallMultiplierCalculator`, `catch_rate` (Poké Ball column) | The column holds the multiplier when no condition applies. |
| Throw ball | `ThrowBallAction` | |
| Starter | `starter` | The Pokémon chosen at registration. |
| Ultra Beast | `is_ultra_beast` | |
| Money / Pokédollars (₽) | `money` (users column), `MoneyAmount` (Vue) | Never "coins", "cash" or "balance". |
| Price | `price` | Null means earned only: never sold. |
| Sell back | `SellItemAction`, `sell_back_ratio` | Half the price. |
| Today's TMs | `TmRotation` | The 10 TMs for sale today. |
| TM | `InventoryItemType::TM`, `tm_code` | An inventory item whose item is a `Move`. Shown as "TM 395 Ice Beam". |
| Special item | `SpecialItem`, `SpecialItemCategory` | Boost stones and key items. |
| Boost stone | `boost-stone` (special item slug) | |
| Key item | `SpecialItemCategory::KEY_ITEM` | Items that are switched on, like the XP Share (`xp-share`). |
| Earned only | price is `null` | Cannot be bought or sold to the shop; can be traded. |
| Lock | `is_locked`, `SetPokemonLockAction` | Locked Pokémon can't be released or traded. |
| Release | `ReleasePokemonAction` | Permanent; gives nothing back. |
| Teach a TM | `TeachTmAction` | Adds a move to the known moves; the TM is used up. |
| Boost failure chance | `BoostStoneCalculator::failureChance()` | Percentage (0–100) for the next stone. |
| Boost result | `BoostResult` | Stones used, successes, failures, boost before and after. |
| Evolution option | a `SpeciesEvolution` plus its requirement reason | Shown in the Evolve dialog. |
| Party size limit | `game.user.max_party_size` | 6. |
| Daily mission / achievement | `UserMission` with `MissionKind::DAILY` / `ACHIEVEMENT` | One row per player per mission. |
| Mission goal | `MissionGoal` | What a mission counts (`CATCH_POKEMON_OF_TYPE`). |
| Progress / target | `progress`, `target` | "3 / 5". |
| Claim | `ClaimMissionAction`, `claimed_at` | Pressing Claim gives the reward. |
| Reroll | `RerollDailyMissionAction`, `last_reroll_on` | One per day. |
| Streak | `mission_streak`, `streak_last_completed_on` | Days in a row with all 3 dailies done. |
| Reward | `MissionReward`, `GrantRewardAction` | Money plus items. |
| XP Share | `xp-share` (key item), `is_xp_share_enabled` | |
| Trainer (NPC) | `Trainer`, `TrainerRole` | Hand-made trainers, gym leaders, Elite Four, Champions. Not the player ("original trainer" is a player). |
| Trainer class | `trainer_class` | "Hiker", "Ace Trainer", "Gym Leader". |
| Region | `Region` enum | Kanto … Paldea. |
| Badge | a `TrainerVictory` against a gym leader, `badge_name` | |
| Payout | `payout`, `TrainerPayoutCalculator` | Full money for beating a trainer; reduced when the player outlevels it. |
| Elite Four run | `EliteFourRun`, `stage` 1–5 | Stage 5 is the Champion. |
| Battle Tower run | `TowerRun`, `floor` | `floor` is the next floor to fight. |
| Checkpoint | `TowerFloorCalculator::isCheckpoint()` | Full heal; floors 10, 25, 45, 70, 100… |
| Battle Points | `battle_points` | Earned in the Tower. |
| Best floor | `best_tower_floor` | Highest floor won. |
| Listing | `TradeListing`, `ListingStatus` | A Pokémon put on the trade market. |
| Seller / offerer | `user_id` on the listing / on the offer | |
| Offer | `TradeOffer`, `OfferStatus` | Pokémon and items offered for a listing. |
| Looking for | `looking_for` | The seller's free-text wish, up to 200 characters. |
| Listing fee | `fee`, `game.trading.listing_fee` | ₽ 500, refunded on expiry. |
| On trade hold | `is_on_trade_hold` | The Pokémon is in a listing or offer and can't be used. |
| Accept / decline / withdraw / cancel / expire | `AcceptOfferAction`, `DeclineOfferAction`, `WithdrawOfferAction`, `CancelListingAction`, `ExpireListingAction` | |
| Notification | Laravel database notifications | Shown under the bell. |
| Player / profile | `User`, `PlayerProfileQuery`, `/players/{user}` | Another person playing. Not an NPC trainer. |
| Latest captures | `CaptureFeedEntry`, `LatestCapturesQuery` | Notable catches and hatches, recorded at catch time. |
| Notable capture | `NotableCaptureChecker` | Shiny, legendary, mythical or level 60+. |
| Waiting for you | Dashboard section | Things the player can act on now. |
| Original trainer | `original_user_id` / `originalUser()` | Already in the code. |
| Party / Box | `in_party` / `inParty()`, `inBox()` | Already in the code. |
| Party order | `party_position` (1–6), `ReorderPartyAction` | The sending-out order; the first one leads. |
| Tower team order | `ReorderTowerTeamAction` | The run's own sending-out order. |
| Quit the Elite Four | `QuitEliteFourRunAction`, status `QUIT` on `EliteFourRun` | Between battles only. |
| Champion prize | `game.trainers.champion_prize` | ₽ 30,000 for beating a region's Champion the first time. |

## Naming recipes

| Kind of thing | Recipe | Examples |
| --- | --- | --- |
| Action (does one thing, may write to the database) | `{Verb}{Noun}Action`, method `handle()` | `StartWildBattleAction`, `TakeTurnAction`, `EvolvePokemonAction` |
| Mechanics: pure math, no randomness | `{Noun}Calculator` in `{Domain}/Mechanics/` | `StatsCalculator`, `DamageCalculator`, `TurnOrderCalculator` |
| Mechanics: rolls dice through `RandomHelper` | `{Noun}Roller` in `{Domain}/Mechanics/` | `MoveOutcomeRoller`, `StatusEffectRoller` |
| Mechanics: answers "is this allowed?" | `{Noun}Checker`, usually returns `null` or a reason string (a bool is fine for a plain yes or no) | `EvolutionRequirementChecker` |
| Result of a mechanic | Value object `{Noun}` or `{Noun}Result` in `{Domain}/ValueObjects`, or `Shared/ValueObjects` when it is stored in a database column or used by more than one domain | `MoveOutcome`, `MoveHit` |
| Exception | `{What went wrong}Exception` | `BattleAlreadyInProgressException`, `MoveNotKnownException` |
| Event (task 0.4) | Past tense: `{Noun}{Verb in past tense}` | `PokemonCaught`, `BattleEnded` |
| Enum | Singular noun, cases `UPPER_SNAKE_CASE`, in `Shared/Enums` when stored in a database column or used by more than one domain, otherwise in `{Domain}/Enums` | `BattleStatus::ONGOING`, `ActionBlocker::ASLEEP` |
| Boolean | `is…`, `has…`, `can…` | `isFainted`, `hasEnteredField`, `canEvolve` |
| Method returning a number | The noun, no `get` prefix | `maxHp()`, `firstMoverChance()` |
| Method computing something from inputs | `calculate()` on a Calculator, `roll()` on a Roller, `check()` on a Checker, or a name saying what it returns when a class has several | `failureChance()`, `isCheckpoint()` |
| Config key | `game.{area}.{snake_case}` in `config/game.php`; every key has a short comment saying what it controls in the game, not a formula | `game.battle.stab_multiplier` |
| Chance | `{noun}_chance` (config, column) or `{noun}Chance()` (method), always a percentage from 0 to 100, never a 0.0–1.0 fraction or "1 in N" odds; `RandomHelper::chance()` takes it as is | `game.battle.critical_hit_chance` (6.25), `flinch_chance`, `failureChance()` |
| Migration | `{next number}_create_{table}_table.php`, for new tables only; until there is live data, new columns go into the table's existing `create_…` migration | `014_create_battles_table.php` |
| Test file | `{ClassName}Test.php`, mirrored folders | `tests/App/Battle/Unit/Mechanics/DamageCalculatorTest.php` |

## Rules of thumb

- Use the name from the spec, not a synonym. If the spec says "boost", the code says `boost`.
- Name by what something **is** in the game, not how it is stored: `activeMoves()`, not `movesWithSlot()`.
- When stuck, ask the AI for three options using this file, pick one and add it to the table.
