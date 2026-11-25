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
| HP | `hp`, `maxHp`, `currentHp` | `maxHp` is the full value; `currentHp` is during battle (`Combatant`, stored as `max_hp` / `current_hp`). |
| Attack | `attack` | A damage multiplier, not a number shown as "Attack 120". |
| Defense | `defense` | A damage-taken multiplier: 1 ÷ (1 + boost ÷ 100), × 0.75 when shiny. Shown to players as "Damage taken" (`damageTakenChangePercentage()`). |
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
| Turn action outcome | `TurnActionOutcome` enum | What happened when one side acted in a turn: `SKIPPED` (already fainted), `BLOCKED` (by an action blocker or a flinch), `MISSED`, `NO_EFFECT` (immune defender), `USED`, `CAUSED_FLINCH`, `KNOCKED_OUT`. |
| Ailment infliction | `AilmentInflictionChecker::canInflict()` | Whether a move's ailment can land on the target: not fainted, no major ailment yet (or not confused yet, for confusion), and not a type immune to it. |
| Move usability | `MoveUsabilityChecker` | Whether a combatant can still use a move: `isOncePerBattleMoveAlreadyUsed()` (Z-Moves and Dynamax moves, once per battle), `isDynamaxMoveAllowed()` (only G-Max species), `isUsable()` for both. |
| Turn order roll | `TurnOrderRoller::rollIsPlayerFirst()` | Rolls `TurnOrderCalculator::firstMoverChance()`. |
| Opponent move roll | `OpponentMoveRoller::rollMoveId()` | Picks one of the opponent's usable moves. |
| Turn data | `TurnData` (`App\Battle\Data`) | What the turn pipeline carries: the battle, the move slot (`playerMove()` reads the move in it), the state, the opponent's chosen move (`opponentMove`), the turn order, each side's action (`playerAction`, `opponentAction`, null until that side acts) and the log entries (`logEntries`, written by `log()` and `logDamage()`). |
| Turn action data | `TurnActionData` (`App\Battle\Data`) | One side's action in a turn, sent through the turn action pipeline: its `side`, `move`, `blocker`, `isConfusionChecked`, `moveOutcome`, `typeMultiplier` and `outcome`. |
| Battle side | `Side` (`App\Battle\Enums`): `PLAYER`, `OPPONENT`; `other()` | Which side of a battle acts, defends or flinches. Not to be confused with the `BattleSide` value object, which holds one side's team. |
| Turn pipeline | `App\Battle\Pipes\Turn\*` | The steps of `TakeTurnAction`: ready check, loading, the player's move check (`EnsurePlayerMoveIsUsable`), the opponent's move choice, turn order, actions, end-of-turn damage, saving. |
| Turn action pipeline | `App\Battle\Pipes\TurnAction\*` | One side's action (`TurnActionData`), run by `TakeActions` for each side: skips, status checks, the move, hits and ailments. |
| Battle | `Battle` model | One fight, saved on the server. |
| Battle state | `BattleState`, `state` column | Everything that changes during a battle (turn, both sides, log), saved as JSON with `AsBattleState`. |
| Battle side | `BattleSide` | The player's or the opponent's team in a battle. |
| Combatant | `Combatant` | One Pokémon inside a battle (a snapshot, not the `Pokemon` model). |
| Active combatant | `activeIndex` on `BattleSide` | The one currently on the field. |
| Turn | `turn` | Both sides acting once. |
| Battle log | `log` (`list<BattleLogEntry>`), `BattleLogCard` | What happened, turn by turn; the page shows the newest turn first. |
| Battle log entry | `BattleLogEntry` (`turn`, `kind`, `text`, `moveName`), `BattleState::withLogEntries()` | One message. `turn` 0 is before the first turn; lines logged between turns use `BattleState::lastPlayedTurn()`. Only `MOVE` entries have a `moveName`. |
| Battle log kind | `BattleLogKind` enum (`Shared\Enums`): `MOVE`, `DAMAGE`, `DETAIL`, `INFO`, `EVENT`, `RESULT` | How the page styles an entry: move names in bold, damage and details small and muted, the battle's result ("You won!", "You lost...", "You got away safely!") in bold. |
| Ongoing battle | `Battle::ongoing()` scope | The user's battle with status `ONGOING`. |
| Recently ended battle | `Battle::recentlyEnded()` scope, `game.battle.end_screen_minutes` | An ended battle last updated within the end-screen window; `GET /battle` shows its end screen. |
| Sending out from the page | `SendOutPokemonController`, `SendOutPokemonRequest` (`combatantIndex`), `battle.isAwaitingSendOut` prop, `PokemonFaintedCard`, `SendOutSheet` | `POST /battle/send-out` from the team rail's "Send out" button (`SendOutSheet`, a bottom sheet, on phones); the index is the 0-based position in the player side's `combatants`. |
| Team rail | `player.team` prop (`BattleTeamMember`), `BattleTeamRail`, `BattleTeamRadioGroup` | The "Your team" card in two groups: "In battle" (the Pokémon on the field, a bordered row that can't be picked) and "Bench" (a radio group, nothing checked at first; the checked item is the one picked to switch to or send out); fainted ones are disabled. Below the three-column layout, a grid of 1 to 3 columns of the same rows. `BattleTeamRadioGroup` draws the bench items, also for the send-out choice; `TeamRailMemberBody` draws a row's name, ailment, level and HP, and `TeamRailGroup` a group's heading and 1 to 3 column list, in both rails. |
| Opponent's team rail | `opponent.team` prop (`isRevealed`), `OpponentTeamRail` | The trainer's team, read-only; gym leaders and the Elite Four show Pokémon not sent out yet as "Not sent out yet". Two groups, "In battle" and "Rest of the team"; shown on phones too. |
| Pokémon details | `PokemonDetailsButton` (the portrait button), `PokemonDetailsHoverCard` (wide screens), `PokemonDetailsSheet` (below the three-column layout), `PokemonDetails` (their shared content); team items' `types`, `moves`, `isShiny`, `boost`, `statBonuses` (`hp`, `attack`, `damageTaken`, from `StatsCalculator`) | Opened from a rail row's portrait: types, what the boost (and shine) does, and the four moves with type and power. The page shares whether it is below the three-column layout through `useBattleLayout`. |
| Switching from the page | The team rail's "Switch" button | Enabled when the checked Pokémon isn't the one in battle; no confirmation. |
| Battlefield | `BattlefieldCard`, `BattlefieldCombatantCard` | Both Pokémon in battle with their sprite, level and HP as numbers ("115 / 185"); fades out when the battle ends. |
| Battle header | `BattleHeader` | The battle page's title and description with the turn; a `sprite` slot for the trainer and a default slot for the Elite Four steps. |
| Running from the page | `ForfeitBattleController` | `POST /battle/forfeit`. |
| End screen | `result` prop (`experienceGains` with `isFromExperienceShare` and `isAtMaxLevel`), `BattleResultCard` | The result card that replaces the battlefield once the battle has ended: title, "Your rewards", "XP gained" rows, buttons. |
| More options | `BattleOptionsCard` | The card under the moves with Throw ball and Run (wild) or Forfeit (every other battle). |
| Battle scene | `BattleScene` enum (`fromBattleType()`), `battle.scene` prop, `BattleSceneBackground` | The SVG background of the battlefield card, picked from the battle type for now. |
| Taking a turn from the page | `TakeTurnController`, `TakeTurnRequest` (`moveSlot`) | `POST /battle/turn`; the slot is the move's position (1–4) in the active combatant's `moveIds`. |
| Battle sprites | `Species::getDisplayFrontSprite()` (opponent), `getDisplayBackSprite()` (player) | The back sprite falls back to the front one when the species has none. |
| Faint | `isFainted()` | `currentHp` is 0. |
| Send out | `SendOutPokemonAction` | Picking the next Pokémon after a faint. Free. |
| Switch | `SwitchPokemonAction`, `SwitchPokemonController` (`POST /battle/switch`, `combatantIndex`), `InvalidSwitchException` | Changing the Pokémon in battle on the player's turn. Uses the turn. Checked by the `EnsureSwitchIsAllowed` pipe, done by `SwitchPlayerPokemon`. |
| Opponent's half of a turn | The turn pipes with only the opponent acting; `TurnData::isUsingMove()` | The opponent's move, end-of-turn damage and fainting; shared by moves, switches and ball throws. A `TurnData` without a `moveSlot` lets only the opponent act. |
| Run / Forfeit | `ForfeitBattleAction`, `BattleStatus::FORFEITED` | The UI says "Run" in wild battles and "Forfeit" in every other battle. Counts as a loss. |
| Battle ended | `EndBattleAction`, `BattleEnded` event | The one place a battle ends; `isWin` for won and caught battles. |
| Earned XP | `ExperienceGain` (`combatantIndex`, `earnedExperience`, `levelBefore`, `levelAfter`, `isFromExperienceShare`, `isAtMaxLevel`) in `BattleState::$experienceGains` | Each player combatant's result, saved when the battle ends and shown on the end screen. |
| Earned XP per combatant | `EarnedExperienceCalculator::earnedByCombatant()` | The XP each player combatant that entered the field earned in a battle. |
| XP for one defeated Pokémon | `ExperienceCalculator::experienceForDefeating()` (`App\Pokemon\Mechanics`) | Spec 1's formula. |
| Paying XP | `AwardExperienceAction` | Adds XP to a Pokémon and levels it up, up to level 100. |
| Levelling up | `PokemonLevelledUp` event | Dispatched by `AwardExperienceAction` when the level goes up. |
| Evolution requirement | `EvolutionRequirement` enum (`GENDER`, `LEVEL`, `ITEM`), `EvolutionRequirementChecker::check()` | The first unmet requirement of a `SpeciesEvolution`, or null. `reason($evolution)` words it ("Needs a Water Stone") for the Evolve dialog and `EvolutionNotAllowedException`. |
| Evolving | `EvolvePokemonAction`, `PokemonEvolved` event (`isMega`) | Changes the species, uses up the evolution item and adds the new base moves. |
| Knocked out by | `Combatant::$knockedOutBy` | The index of the player combatant credited with an opponent's knockout (`CreditOpponentKnockout`). |
| First mover | `firstMoverChance` | Percentage chance (0–100) to act first this turn. |
| Area | `Area` enum, `Area::fromType()` | The 9 places to explore (`Area::FOREST`). `fromType()` gives the one area a type belongs to. |
| Area species | `AreaSpeciesQuery` (`App\Exploration\Queries`) | The species that can appear in an area: one of its types and a wild level range. |
| Explore / Keep exploring | `ExploreAction` | Both roll a new encounter. |
| Encounter | `Encounter` model | The one wild Pokémon waiting for the player. |
| Battle the encounter | `BattleEncounterAction`, `NoEncounterException` | Starts a wild battle against the stored encounter and deletes it. The battle's area is `BattleState::$area`. |
| Exploring from the page | `ExploreController` (`index`, `show`), `ExploreAreaController`, `BattleEncounterController`, `ExploreIndexPageProps`, `ExploreShowPageProps`, `AreaCard`, `EncounterCard`, `AreaSpeciesDialog` (`areaSpecies` optional prop) | `GET /explore`, `GET /explore/{area}`, `POST /explore/{area}` (Explore and Keep exploring), `POST /explore/battle`. `isBattleInProgress` shows the "You're in a battle" state on both pages. |
| Encounter roll | `EncounterRoller::roll()`, `rollEvolutionStage()` | Picks the wild Pokémon for one explore from an area's species. |
| Wild level range | `wild_min_level`, `wild_max_level` | Null when a species never appears in the wild. Stored per species in the species data files and saved by `SpeciesSeeder`. |
| Evolution stage | `evolution_stage`, `EvolutionStageCalculator::evolutionStage()` | 1 = unevolved, 2, 3. Babies don't count. Saved by `SpeciesEvolutionStageSeeder`. |
| Catch chance | `CatchChanceCalculator` | Percentage, 0–100. |
| Catch rate | `catch_rate` (Species column), `getCatchRatePercentage()`, `isFoundInTheWild()` | 3–255, as in the main games; ÷ 255 gives the base catch chance. Null for species never in the wild. |
| Ball multiplier | `BallMultiplierCalculator`, `catch_rate` (Poké Ball column), `CatchContext`, `CatchContextBuilder` | The column holds the multiplier when no condition applies; `CatchContext` holds what the conditions read (turn, area, night, both Pokémon, the wild ailment). `CatchContextBuilder::build()` loads it from a battle for `ThrowBallAction` and the battle page's ball picker. A ball is boosted when its multiplier right now is above its `catch_rate`. |
| Throw ball | `ThrowBallAction`, `ThrowBallController` (`battle.web.throw-ball`), `BattleState::$caughtPokemonId` (stored as `caught_pokemon_id`) | A catch saves the new Pokémon's id in the battle state, so the end screen can say which ball caught it and whether it went to the party or the Box. |
| Starter | `starter` | The Pokémon chosen at registration. |
| Ultra Beast | `is_ultra_beast`, `SpeciesTrait::ULTRA_BEAST` / `PokemonTrait::ULTRA_BEAST` (`ultra-beast`) | |
| Money / Pokédollars (₽) | `money` (users column), `MoneyAmount` (Vue) | Never "coins", "cash" or "balance". |
| Add and spend money | `AddMoneyAction`, `SpendMoneyAction`, `NotEnoughMoneyException`, `game.economy.starting_money` | Both are one atomic update on the user's row. Spending more than the player has is refused. New players start with ₽ 2,000. |
| Add items to the inventory | `AddInventoryItemAction`, `InventoryStackFullException`, `game.inventory.max_stack` | Creates or grows the player's stack of an item, up to 9,999. `RemoveInventoryItemAction` takes items away. |
| Price | `price` | Null means earned only: never sold. |
| TM price | `TmPriceCalculator`, `game.shop.tm_prices` | By the move's power; null for Z-Move and Dynamax TMs. |
| Buy | `BuyItemAction`, `ItemNotForSaleException`, `BuyItemController`, `BuyItemRequest`, `ShopItemBuyForm` | Spends the money and adds the items in one transaction. |
| Confirm a big purchase | `game.shop.confirm_purchase_over`, `confirmPurchaseOver` | Purchases over ₽ 10,000 ask first. |
| Item name for a quantity | `ItemHelper::name($item, $quantity)`, `pluralName` | "10 Ultra Balls"; a TM keeps its name ("TM 395 Ice Beam"). |
| Sell back | `SellItemAction`, `ItemNotSellableException`, `sell_back_ratio` | Half the price. |
| Shop price | `ItemPriceCalculator` | A TM's price from `TmPriceCalculator`, any other item's `price`. |
| Incubator price | `IncubatorPriceCalculator`, `game.incubation.price_step`, `nextIncubatorPrice` | ₽ 5,000 × incubators already bought; the first is free. |
| Today's TMs | `TmRotation`, `GetTodaysTmRotationAction`, `game.shop.daily_tm_count` | The 10 TMs for sale today. |
| Shop page | `ShopController`, `ShopIndexPageProps`, `pokeballs`, `evolutionItems`, `tms`, `ownedQuantity`, `ShopTabs`, `ShopItemRow` | Lists what the Shop sells, with how many of each the player owns. |
| Player money (page header) | `PlayerMoney` | The player's money on the right of a page header; hidden on phones, where the top bar shows it. |
| TM | `InventoryItemType::TM`, `tm_code`, `Move::tmName()`, `Move::tmSprite()` | An inventory item whose item is a `Move`. Shown as "TM 395 Ice Beam" with a disc in the move's type colour. |
| Special item | `SpecialItem`, `SpecialItemCategory` | Boost stones and key items. |
| Boost stone | `boost-stone` (special item slug) | |
| Key item | `SpecialItemCategory::KEY_ITEM` | Items that are switched on, like the XP Share (`xp-share`). |
| Earned only | price is `null` | Cannot be bought or sold to the shop; can be traded. |
| Lock | `is_locked`, `SetPokemonLockAction` | Locked Pokémon can't be released or traded. |
| Release | `ReleasePokemonAction` | Permanent; gives nothing back. |
| Teach a TM | `TeachTmAction` | Adds a move to the known moves; the TM is used up. |
| Boost failure chance | `BoostStoneCalculator::failureChance()` | Percentage (0–100) for the next stone. |
| Boost result | `BoostResult` | Stones used, successes, failures, boost before and after. |
| Evolution option | a `SpeciesEvolution` plus its unmet `EvolutionRequirement` | Shown in the Evolve dialog with the reason. |
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
| Data passed through a pipeline | `{Noun}Data` in `{Domain}/Data/` | `TurnData`, `TurnActionData` |
| Pipeline step | What it does, in `{Domain}/Pipes/{Pipeline}/` | `RollTurnOrder`, `DealHits` |
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
