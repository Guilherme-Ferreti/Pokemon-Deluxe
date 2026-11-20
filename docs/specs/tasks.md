# Tasks

Planned work, in order. Each task is small enough to build, test and merge on its own.

Each task starts with a `- [ ] Done` box. Tick it (`- [x] Done`) in the same commit or pull request that finishes the task, so this file always shows what is left.

Every task names the classes, methods, columns and files it adds, using [glossary.md](glossary.md). These names are suggestions to start from, not final: the final name is decided when the task is built. When a name changes, or a task needs one it does not give, update the glossary (and later tasks that use the name) in the same change.

Each task follows the project rules in `.ai/rules/`. "Tests" lists the behaviour to cover, not every test case.

Tasks that change the screen have **UI suggestions**: common elements and components that could build the page. They are ideas, not requirements; use something else when it fits better.

Tasks with a **Mock-up** link have an interactive mock-up in `docs/mockups/` (open it in a browser; the State menu at the top shows each state). Match its layout, content and states, but build the page from the project's components, not from the mock-up's HTML or CSS. Components named in them are the project's shadcn-vue components (`resources/js/shared/components/ui`) and shared components (`resources/js/shared/components`).

---

## 0. Foundations

### 0.1 Point AI sessions at the specs

- [x] Done

`AGENTS.md` and `.ai/rules/index.md` say that feature behaviour lives in `docs/specs/`, that the relevant spec is read before working on a feature, and that specs are updated when a decision changes.

### 0.2 A home for game mechanics

- [x] Done

**Goal:** pure game rules (formulas, odds, rolls) live in one strictly tested kind of folder.

**Build:**
- Folder `src/App/{Domain}/Mechanics/`, usable by any domain.
- Rule file `.ai/rules/mechanics.md` and a row in `.ai/rules/index.md` for `src/App/**/Mechanics/**`. Rules:
  - Classes are `final`, named `{Noun}Calculator`, `{Noun}Roller` or `{Noun}Checker` (see the glossary).
  - They may read models and their loaded relations, and `config('game.*')`. They never query or save to the database, use HTTP, sessions or auth.
  - Randomness only through `RandomHelper` (task 0.3), and only in `Roller` classes.
- `arch()` rule in `tests/Architectural/ArchitecturalTest.php`: `App\*\Mechanics` are final classes that extend nothing and do not use `Illuminate\Support\Facades\DB`, `Illuminate\Http`, `Illuminate\Support\Facades\Auth`.
- Move `App\Battle\Services\DamageCalculator` to `App\Battle\Mechanics\DamageCalculator`. Update `PokedexShowPageProps` and add `tests/App/Battle/Unit/Mechanics/DamageCalculatorTest.php` for the existing type multiplier. Remove the empty `Services` folder.

**Done when:** rule file, index row and `arch()` rule exist; `DamageCalculator` is in `Mechanics`; the suite passes.

### 0.3 Testable randomness

- [x] Done

**Goal:** one source of randomness that tests can control.

**Build:**
- `Shared\Helpers\RandomHelper`, a final class with static methods:
  - `chance(float $percentage): bool`: true with the given percentage chance (0–100). Throws `InvalidArgumentException` below 0 or above 100, even while faked.
  - `integer(int $min, int $max): int`: inclusive range.
  - `float(float $min, float $max): float`
  - `pick(array $items): mixed`: one random item.
  - For tests: `fake(array $values): void` returns the given values in order, whatever method is called; `reset(): void` goes back to real randomness. Calling a method after the faked values run out throws an exception so tests fail loudly.
- Use PHP's `Random\Randomizer` inside.
- Replace the three `Lottery::odds()` calls (`Species`, `PlaceEggAction`, `ListLayoutSpritesAction`) with `RandomHelper::chance()`, and update their tests.
- Reset the fake after each test in `tests/Pest.php`.
- Rule in `.ai/rules/helpers.md`: game code gets randomness only from `RandomHelper`. Picking random database rows with `inRandomOrder()` in a query stays allowed.
- `arch()` rule: `rand`, `mt_rand`, `random_int`, `array_rand`, `shuffle` and `Illuminate\Support\Lottery` are not used in `App` or `Shared`, except inside `RandomHelper`. Pest cannot ban a single method, so `Arr::random()` is named in the rule file instead.

**Tests:** `tests/Shared/Unit/Helpers/RandomHelperTest.php`: each method returns values in range; `fake()` returns values in order; running out throws; `reset()` restores randomness.

**Done when:** `RandomHelper` exists with tests, `Lottery` is gone from `src`, rules and `arch()` rule exist, the suite passes.

### 0.4 Events and listeners between domains

- [x] Done

**Goal:** domains announce what happened instead of calling each other. Needed before Spec 5 at the latest.

**Build:**
- Folders `src/App/{Domain}/Events/` and `src/App/{Domain}/Listeners/`.
- Rule file `.ai/rules/events.md` and index rows:
  - An Action that finishes something other domains may care about dispatches an event, named in the past tense (`PokemonCaught`).
  - Events are `final readonly` classes carrying IDs and plain values, using the `Dispatchable` trait and implementing `ShouldDispatchAfterCommit`. Actions dispatch them after their transaction, never inside it.
  - Listeners live in the domain that reacts, are named `{Verb}{Noun}On{Event}` (for example `AdvanceMissionsOnPokemonCaught`) and have a `handle({Event} $event)` method. They are discovered automatically from `src/App/*/Listeners` (`bootstrap/app.php`, with a class-name resolver in `AppServiceProvider`).
  - Whether a listener runs synchronously or queued is decided by each feature when it is built.
- `arch()` rules for both folders.
- Example: `App\Pokemon\Actions\Incubator\CatchPokemonAction` dispatches `App\Pokemon\Events\PokemonCaught` with `pokemonId` and `userId`.

**Tests:** `CatchPokemonActionTest` asserts `PokemonCaught` is dispatched with the right IDs (`Event::fake`).

**Done when:** rule file, index rows and `arch()` rules exist, the example event is dispatched and tested, the suite passes.

---

## 1. Spec 1 — Power model and battle system

Read [01-power-model-and-battles.md](01-power-model-and-battles.md) first. Tasks 1.1–1.18 are backend only; the battle page comes next (1.19–1.21); task 1.22 is seed data; tasks 1.23–1.24 add switching Pokémon; tasks 1.25–1.35 rebuild the battle page with the new layout, one component per task (task 1.25 changes how the backend logs messages).

There is no production data yet, so a new column (or index, or a dropped column) goes into the table's existing `create_…` migration, and only a new table gets a new migration file. Local databases are rebuilt with `php artisan migrate:fresh --seed`. No data migrations.

### 1.1 Add boost to Pokémon

- [x] Done

**Goal:** every Pokémon has a boost level from 0 to 100.

**Build:**
- In `010_create_pokemon_table.php`: `unsignedTinyInteger('boost')->default(0)` after `experience`.
- `Pokemon` model: cast `'boost' => 'integer'`. `CreatePokemonAction` sets `boost` to 0.
- `PokemonFactory`: `boost` defaults to 0; new state `boosted(int $boost)`.
- `config/game.php`: `'pokemon' => ['max_boost' => 100, ...]`.

**Tests:** factory state sets the boost; a new Pokémon from `CreatePokemonAction` has boost 0.

**Done when:** column, cast, factory state and config key exist; tests pass.

### 1.2 Calculate a Pokémon's stats

- [x] Done

**Goal:** turn level, boost and shiny into HP, Attack and Defense.

**Build:**
- Value object `Shared\ValueObjects\PokemonStats` (`final readonly`, implements `ValueObject`): `int $hp`, `float $attack`, `float $defense`.
- `App\Pokemon\Mechanics\StatsCalculator::calculate(int $level, int $boost, bool $isShiny): PokemonStats`:
  - `hp` = round(400 × (level + 10) / (100 + 10) × (1 + boost / 100) × (1.25 if shiny))
  - `attack` = (level + 10) / 77 × (1 + boost / 100) × (1.25 if shiny)
  - `defense` = 0.75 if shiny, else 1.0 (multiplier on damage taken)
- New config keys under `game.pokemon.stats`: `max_level_hp` (400), `level_bonus` (10), `damage_scale` (77), `shiny_hp_multiplier` (1.25), `shiny_damage_done_multiplier` (1.25), `shiny_damage_taken_multiplier` (0.75).
- `Pokemon::getStats(): PokemonStats` calls the calculator.

**Tests:** `tests/App/Pokemon/Unit/Mechanics/StatsCalculatorTest.php`: level 1 → HP 40; level 100 → HP 400; +100 boost doubles HP and attack; shiny gives ×1.25 HP and attack and 0.75 defense.

**Done when:** calculator, value object, config keys and model method exist; tests pass.

### 1.3 Split moves into known moves and active slots

- [x] Done

**Goal:** a Pokémon has a pool of known moves; up to 4 of them sit in active slots.

**Build:**
- In `011_create_pokemon_move_table.php`: nullable `unsignedTinyInteger('slot')`; unique `['pokemon_id', 'move_id']`; unique `['pokemon_id', 'slot']`.
- `Pokemon` model: rename `moves()` to `knownMoves()` (with `withPivot('slot')`); add `activeMoves()` = known moves with a slot, ordered by slot.
- `CreatePokemonAction::addBaseMoves()`: attach the species' unique base moves (up to 4) with slots 1, 2, 3…; remove `repeatUntil(4)`. Every species gets exactly 4 different base moves in task 1.22; until then, a species with fewer gives fewer active moves.
- Update every use of `moves` (`BoxIndexPageProps`, `SearchBoxQuery`, `UserSeeder`, Box tests) to `activeMoves`. `PokemonFactory` gets a `withActiveMoves(int $count = 4)` state. The `repeatUntil` collection macro is removed (no longer used).

**Tests:** `CreatePokemonActionTest`: base moves become active in slots 1–4; no duplicates; a species with 2 base moves gives 2 active moves; the default-move fallback still works.

**Done when:** column, relations and action changes are in; Box page still shows the moves; the suite passes.

### 1.4 Put a known move in an active slot

- [x] Done

**Goal:** the backend for "swap" on the Pokémon page (the page itself is Spec 4).

**Build:**
- `App\Pokemon\Actions\SetActiveMoveAction::handle(Pokemon $pokemon, Move $move, int $slot): void`
  - `$move` must be a known move, otherwise throw `App\Pokemon\Exceptions\MoveNotKnownException`.
  - `$slot` must be 1–`game.pokemon.max_active_moves` (4, also used by `CreatePokemonAction`), otherwise throw `InvalidArgumentException`.
  - If `$move` is already active in another slot, the two moves swap slots (an active move sent to an empty slot leaves its old slot empty). Otherwise the move that was in `$slot` (if any) goes back to the pool (slot `null`).
  - Locked per Pokémon with `LockKeys::pokemonActiveMoves()`.

**Tests:** `tests/App/Pokemon/Unit/Actions/SetActiveMoveActionTest.php`: replace, swap, empty slot, unknown move, invalid slot.

**Done when:** action and exception exist; tests pass.

### 1.5 Remove one-hit-KO moves

- [x] Done

**Goal:** OHKO moves (Fissure, Guillotine, Horn Drill, Sheer Cold) never exist in the game.

**Build:**
- Delete the OHKO moves from `database/seeders/files/moves.json`, and their references from `species-base-moves.json`; the seeders stay unchanged.
- Remove `MoveCategory::OHKO` (PHP and TypeScript) and the code that only handled OHKO moves: the Attackdex power sort, the Pokédex base-moves power sort and the "One-hit KO" power label (`MovePowerLabel`).

**Tests:** seeder test (in `tests/Shared/Unit/Seeders/MoveSeederTest.php`): no move with category `ohko` after seeding.

**Done when:** no OHKO moves after `migrate:fresh --seed`; tests pass.

### 1.6 Damage formula

- [x] Done

**Goal:** calculate the damage of one hit.

**Build:**
- `App\Battle\Mechanics\DamageCalculator`:
  - Rename the existing `calculateDamageMultiplier()` to `typeMultiplier(PokemonType $attackType, PokemonType ...$defenderTypes)` (update `PokedexShowPageProps`).
  - Add `calculate(PokemonStats $attackerStats, array $attackerTypes, PokemonStats $defenderStats, array $defenderTypes, Move $move, bool $isCriticalHit, float $randomFactor): int`:
    - damage = move power × attacker attack × STAB (1.5 if the move's type is one of the attacker's types) × type multiplier × critical (1.5) × randomFactor × defender defense
    - Rounded down. At least 1 when the type multiplier is above 0. 0 when the move has no power.
- Config keys under `game.battle`: `stab_multiplier` (1.5), `critical_hit_multiplier` (1.5).
- The caller rolls `isCriticalHit` and `randomFactor` (task 1.8); the calculator has no randomness.

**Tests:** `tests/App/Battle/Unit/Mechanics/DamageCalculatorTest.php`: the Spec 1 matchup table (level 100 vs 100 takes about 4 hits), STAB, immune type gives 0, minimum 1, critical, shiny defender.

**Done when:** rename and new method exist; tests pass.

### 1.7 Turn order

- [x] Done

**Goal:** decide who acts first each turn.

**Build:**
- `App\Battle\Mechanics\TurnOrderCalculator::firstMoverChance(int $level, int $opponentLevel): float` = 100 × level ÷ (level + opponentLevel), a percentage. Throws `InvalidArgumentException` when either level is below 1.

**Tests:** 100 vs 100 → 50; 1 vs 100 → about 0.99.

**Done when:** calculator exists; tests pass.

### 1.8 Roll a move's outcome

- [x] Done

**Goal:** all the dice for one move use, in one place.

**Build:**
- Value object `App\Battle\ValueObjects\MoveHit`: `bool $isCriticalHit`, `float $randomFactor` (above 0, at most 1). One per hit.
- Value object `App\Battle\ValueObjects\MoveOutcome`: `bool $hasHit`, `list<MoveHit> $hits`, `bool $causesFlinch`, `?Ailment $inflictedAilment`; `hitCount()`; `MoveOutcome::miss()`.
- `App\Battle\Mechanics\MoveOutcomeRoller::roll(Move $move): MoveOutcome`, using `RandomHelper`:
  - hit if a roll is within the move's accuracy;
  - hit count between `min_hits` and `max_hits` (1 when empty; no roll when both are equal);
  - each hit rolls its own critical hit with `game.battle.critical_hit_chance` (6.25, 1 in 16) and its own random factor between `game.battle.damage_random_min` (0.85) and 1.0;
  - a move without power (status move) has no hits;
  - flinch with `flinch_chance`; ailment with `ailment_chance` (100 when empty). The `unknown` ailment (Tri Attack) picks burn, paralysis or freeze at random.
  - A miss rolls nothing else and returns `MoveOutcome::miss()`.

**Tests:** with `RandomHelper::fake()`: miss, hit, multi-hit (each hit rolled separately), critical, flinch, ailment, status move, unknown ailment.

**Done when:** roller and value object exist; tests pass.

### 1.9 Decide status effect numbers

- [x] Done

Agreed and added to Spec 1 under "Status effects". Task 1.11 adds the config keys under `game.battle.ailments`.

### 1.10 Battle data: table, model and state

- [x] Done

**Goal:** a battle can be saved and loaded. No turn logic yet.

**Build:**
- Enums in `Shared\Enums`: `BattleType` (`WILD`, `TRAINER`, `GYM`, `ELITE_FOUR`, `BATTLE_TOWER`), `BattleStatus` (`ONGOING`, `WON`, `LOST`, `FORFEITED`, `CAUGHT`).
- Migration `014_create_battles_table.php`: `uuid id`, `foreignIdFor(User)`, `type` enum, `status` enum, `json state`, timestamps; index `['user_id', 'status']`.
- Model `Shared\Models\Battle` (with factory), relation `User::battles()`.
- Pruning: `Battle` uses `MassPrunable`; battles that are not `ONGOING` and were last updated more than `game.battle.keep_ended_days` (7) days ago are deleted by `model:prune`, scheduled daily with `withSchedule()` in `bootstrap/app.php`.
- Value objects in `Shared\ValueObjects`:
  - `Combatant`: `?string $pokemon_id` (null for wild or NPC), `string $species_id`, `int $level`, `int $boost`, `bool $is_shiny`, `PokemonGender $gender`, `array $types`, `array $move_ids`, `int $max_hp`, `int $current_hp`, `?Ailment $ailment`, `int $ailment_turns_left`, `int $confusion_turns_left`, `bool $has_entered_field`, `array $used_once_per_battle_move_ids`. Method `isFainted(): bool`.
  - `BattleSide`: `array $combatants`, `int $active_index`. Methods `activeCombatant(): Combatant`, `hasCombatantsLeft(): bool`.
  - `BattleState`: `int $turn`, `BattleSide $player_side`, `BattleSide $opponent_side`, `bool $is_awaiting_send_out`, `array $log`.
  - Each has `fromArray()` / `toArray()` for the JSON column, and its constructor rejects impossible values (level, boost, HP, 1–2 types, 1–4 moves, `ailment` never `CONFUSION` (tracked by `confusion_turns_left`) or `UNKNOWN`, active index in range, turn at least 1).
- Cast `Shared\Casts\AsBattleState`.

**Tests:** `BattleState` survives a save and reload unchanged; `Combatant::isFainted()`; `BattleSide::hasCombatantsLeft()`; only ended battles older than 7 days are pruned; pruning is scheduled daily.

**Done when:** enums, migration, model, value objects and cast exist; tests pass.

### 1.11 Status effect rolls

- [x] Done

**Goal:** the dice for status effects during a turn.

**Build** (numbers from Spec 1, "Status effects"; add them to `config/game.php` under `game.battle.ailments`, chances as percentages such as `paralysis_chance` (25)):
- `App\Battle\Mechanics\StatusEffectRoller` with:
  - `rollActionBlocker(Combatant $combatant): ?ActionBlocker`: `null` when the Pokémon can act, otherwise why it can't. Checked in this order: freeze (`FROZEN` unless the thaw roll succeeds), sleep (`ASLEEP` while `ailment_turns_left` > 0, no roll), confusion (`HURT_ITSELF_IN_CONFUSION` while `confusion_turns_left` > 0 and the self-hit roll succeeds), paralysis (`PARALYZED`). Enum `App\Battle\Enums\ActionBlocker`.
  - `durationFor(Ailment $ailment): int`: sleep (1–3) and confusion (1–4) turn counts; 0 for ailments that last until the battle ends.
- `App\Battle\Mechanics\StatusEffectCalculator::endOfTurnDamage(Combatant $combatant): int`: burn (6.25% of max HP) and poison (12.5%), at least 1; 0 otherwise or when fainted.
- Config `game.battle.ailments` also holds `burn_damage_done_multiplier` (0.5) and `confusion_self_hit_power` (40), used by task 1.15.

Uses `Combatant` from task 1.10.

**Tests:** one per effect, with `RandomHelper::fake()`.

**Done when:** roller and calculator exist; tests pass.

### 1.12 Start a wild battle

- [x] Done

**Goal:** create a battle against one wild Pokémon.

**Build:**
- `App\Battle\Actions\StartWildBattleAction::handle(User $user, NewPokemon $wildPokemon): Battle`
  - Throws `App\Battle\Exceptions\BattleAlreadyInProgressException` if the user has an `ONGOING` battle.
  - Throws `App\Battle\Exceptions\EmptyPartyException` if the party is empty.
  - Player side: party Pokémon in party order (`party_position`, task 4.15; until that task, oldest first), as `Combatant`s built with `StatsCalculator` and their active moves. The first one is active and `has_entered_field = true`.
  - Opponent side: one `Combatant` from `$wildPokemon` (boost 0, active moves = the species' 4 base moves; like `CreatePokemonAction`, the default move and a reported `GameDataException` when the species has none).
  - Log starts with "A wild Pidgey appeared!" and "Go! Pikachu!" (display names, so shiny ones read "Shiny Pidgey").
  - Locked per user with `App\Battle\Support\LockKeys::startBattle()`.
- Artisan command `battle:start-wild {email} {species-slug} {level} {--shiny}` (`Shared\Console\Commands\StartWildBattleCommand`) for local testing until Spec 2 adds exploring. Available only when `app()->isLocal()`.

**Tests:** creates one battle with the right sides; refuses a second ongoing battle (an ended one doesn't block); refuses an empty party; default move for a species without base moves; the command runs only locally.

**Done when:** action, exceptions and command exist; tests pass.

### 1.13 Take a turn: damage only

- [x] Done

**Goal:** the core turn loop, without accuracy, crits, flinch or status effects.

**Build:**
- `App\Battle\Actions\TakeTurnAction::handle(Battle $battle, int $moveSlot): Battle`
  - Refuses when the battle is not `ONGOING` or is awaiting a send-out (`App\Battle\Exceptions\BattleNotReadyForTurnException`).
  - The opponent picks a random active move (`RandomHelper::pick`).
  - Turn order from `TurnOrderCalculator` and `RandomHelper::chance`.
  - Each side's move deals damage from `DamageCalculator` (no critical, random factor 1.0 for now). A fainted Pokémon does not act.
  - `$moveSlot` (1–4) picks from the active combatant's `move_ids`; a slot it doesn't have throws `InvalidArgumentException`.
  - Adds log lines and increments `turn`: "Magikarp used Surf!" (a wild opponent reads "The wild Pidgey used Gust!"), then "It's super effective!", "It's not very effective..." or "It doesn't affect the wild Pidgey..." for damaging moves, and "Pikachu fainted!" when HP reaches 0. HP never goes below 0.
  - Value objects get `Combatant::withCurrentHp()`, `BattleSide::withActiveCombatant()` and `BattleState::withPlayerSide()`, `withOpponentSide()` and `withNextTurn()`; each builds a new object through the constructor, so its checks still run.
  - Wraps the update in `Cache::withoutOverlapping` with a `LockKeys` method, per the Support rule.

**Tests:** both sides lose HP; order follows the faked roll; a fainted opponent (or player) does not act; log lines are added; immune moves deal nothing; refused when not ongoing or awaiting a send-out; invalid move slot.

**Done when:** action works for one turn; tests pass.

### 1.14 Take a turn: accuracy, critical hits, multi-hit and flinch

- [x] Done

**Goal:** plug `MoveOutcomeRoller` into the turn.

**Build:** `TakeTurnAction` uses `MoveOutcomeRoller::roll()` for each move (status moves too, so they can miss):
- Misses log "It missed..." as a `DETAIL` entry.
- Each `MoveHit` deals damage with its own critical hit and random factor; criticals log "A critical hit!" after that hit. A multi-hit move stops once the defender faints and logs "It hit 3 times!" (or "It hit once!"). Against an immune defender only "It doesn't affect …" is logged.
- Flinch only works when the attacker acted first and the defender is still standing: the defender logs "… flinched and couldn't move!" instead of acting.
- Z-Moves and Dynamax moves (`is_z_move`, `is_dynamax_move`) can be used once per battle, tracked in `used_once_per_battle_move_ids` (`Combatant::withUsedOncePerBattleMove()`); Dynamax moves only by G-Max species. The player gets `App\Battle\Exceptions\OncePerBattleMoveAlreadyUsedException` or `DynamaxMoveNotAllowedException`; the opponent only picks among moves it can still use.

**Tests:** one per behaviour, with `RandomHelper::fake()`.

**Done when:** tests pass.

### 1.15 Take a turn: status effects

- [x] Done

**Goal:** plug status effects into the turn.

**Build:**
- Before acting, `StatusEffectRoller::rollActionBlocker()`. On `HURT_ITSELF_IN_CONFUSION`, the Pokémon takes a typeless hit of `confusion_self_hit_power` (40) from its own attack instead of acting. A frozen Pokémon that is not blocked by `FROZEN` has thawed: clear its freeze. A sleeping Pokémon with `ailment_turns_left` at 0 wakes up: clear its sleep. After an `ASLEEP` block, count `ailment_turns_left` down.
- Confusion counts down on each turn the Pokémon reaches the confusion check; when it reaches 0 the log says "… snapped out of its confusion!" right after that turn's action. The self-hit damage is `StatusEffectCalculator::confusionSelfHitDamage(PokemonStats $stats): int`.
- A blocked Pokémon's action returns `TurnActionOutcome::BLOCKED`.
- After a hit (or a status move that lands), apply the outcome's ailment when `App\Battle\Mechanics\AilmentInflictionChecker::canInflict(Ailment $ailment, Combatant $target): bool` allows it: `CONFUSION` sets `confusion_turns_left` from `durationFor()` (never the `ailment` field) unless already confused; any other ailment applies only when the target has none (one major ailment at a time) and its types aren't immune (Fire to burn, Ice to freeze, Electric to paralysis, Poison and Steel to poison), with `ailment_turns_left` from `durationFor()`. A status move whose ailment can't apply logs "But it failed!". Status moves also check type effectiveness ("It doesn't affect …").
- A burned attacker deals `burn_damage_done_multiplier` (half) damage (`DamageCalculator::calculate()` takes `bool $isAttackerBurned`).
- At the end of the turn, after both Pokémon have acted or been stopped, `StatusEffectCalculator::endOfTurnDamage()` for each, in the order they moved that turn. Once either side has no Pokémon left (from a move or from the first one's end-of-turn damage), no more end-of-turn damage is dealt. Ending the battle comes with task 1.16.
- Log each effect: "Pikachu is frozen solid!", "Pikachu thawed out!", "Pikachu is fast asleep.", "Pikachu woke up!", "Pikachu is confused!", "It hurt itself in its confusion!", "Pikachu is paralyzed! It can't move!"; when inflicted: "… was burned!", "… was poisoned!", "… is paralyzed! It may be unable to move!", "… fell asleep!", "… was frozen solid!", "… became confused!"; at the end of the turn: "… is hurt by its burn!", "… is hurt by poison!".

**Tests:** one per effect; end-of-turn damage follows that turn's move order, and when both sides' last Pokémon would faint from it, the one that moved first faints and the other takes no damage.

**Done when:** tests pass.

### 1.16 Fainting, sending out, winning, losing and running

- [x] Done

**Goal:** the battle can end, always through one place.

**Build:**
- `App\Battle\Actions\EndBattleAction::handle(Battle $battle, BattleStatus $status): Battle`: the only place a battle ends. Sets the status, pays XP (task 1.17) and dispatches `App\Battle\Events\BattleEnded` (`battleId`, `userId`, `type`, `status`, `isWin` for `WON` and `CAUGHT`; `trainerId` comes with task 6.4). Throws a `LogicException` for a battle that has already ended. It takes no lock: its callers already hold `App\Battle\Support\LockKeys::battle()` (renamed from `battleTurn()`, as every action that changes a battle uses it) and call it inside their `DB::transaction`, so the event reaches listeners after the commit. Other domains (missions, trainers, the Elite Four, the Battle Tower) react to this event; they never end battles or check statuses themselves.
- In `TakeTurnAction`: when the player's active Pokémon faints and others are left, set `is_awaiting_send_out`. When none are left, end as `LOST`. When the opponent has none left, end as `WON`. This covers every knockout: from a move, from a confusion self-hit and from end-of-turn damage (task 1.15 already stops end-of-turn damage once a side has no Pokémon left).
- `App\Battle\Actions\SendOutPokemonAction::handle(Battle $battle, int $combatantIndex): Battle`: only while awaiting a send-out (`App\Battle\Exceptions\BattleNotAwaitingSendOutException`); the chosen combatant must not be fainted (`PokemonHasFaintedException`); sets `has_entered_field = true`, stops awaiting and logs "Go! Eevee!". The turn number doesn't change.
- `App\Battle\Actions\ForfeitBattleAction::handle(Battle $battle): Battle`: the **Run** button, allowed at any time while ongoing, also while awaiting a send-out (`BattleAlreadyEndedException` otherwise); logs "You ran away!" and ends as `FORFEITED`, which counts as a loss everywhere.
- Helpers: `BattleState::withAwaitingSendOut()` and `withLogLines()`, `BattleSide::withActiveIndex()`, `Combatant::withEnteredField()`.
- Nothing is written back to `Pokemon` rows: every battle ends fully healed (Spec 1).

**Tests:** win, loss, awaiting send-out, sending out a fainted Pokémon is refused, forfeit; `BattleEnded` is dispatched once per ending with the right status and `isWin`.

**Done when:** tests pass.

### 1.17 XP when a battle ends

- [x] Done

**Goal:** Pokémon that fought gain XP and level up, whatever the result.

**Build:**
- `App\Pokemon\Services\ExperienceCalculator` → move to `App\Pokemon\Mechanics\ExperienceCalculator` (update callers; the `Services` folder and its architecture rule are gone, since nothing else lived there). Add `experienceForDefeating(int $opponentLevel, int $pokemonLevel, BattleType $battleType, bool $isKnockout): int` = 250 × clamp(opponentLevel ÷ pokemonLevel, 0.1, 3) × battle bonus × 1.5 when it knocked the opponent out, rounded down (Spec 1, "XP").
- Config `game.experience`: `base` (250), `min_ratio` (0.1), `max_ratio` (3), `battle_bonuses` (wild 1, trainer 1.5, gym 2, elite four 2, battle tower 1), `knockout_multiplier` (1.5), `xp_share_ratio` (0.5, used by task 5.10).
- `App\Pokemon\Actions\AwardExperienceAction::handle(Pokemon $pokemon, int $experience): Pokemon`: locks the Pokémon's row, refuses negative XP, adds the XP up to the level 100 total (`experienceForLevel(100)`) and updates `level` with `levelForExperience()`. When the level goes up, it dispatches `App\Pokemon\Events\PokemonLevelledUp` (`pokemonId`, `userId`, `levelBefore`, `levelAfter`) for the missions (task 5.x; added with task 1.18).
- Each opponent `Combatant` has `knocked_out_by` (the player combatant's index, null while standing). The turn pipe `CreditOpponentKnockout` (after `ApplyEndOfTurnDamage`) sets it to the player's active combatant whenever the opponent's active Pokémon faints, whatever knocked it out: a move, its own burn or poison, or hurting itself in confusion.
- `App\Battle\Mechanics\EarnedExperienceCalculator::earnedByCombatant(BattleState, BattleType, BattleStatus): array<int, int>`: for every player combatant with `has_entered_field`, the sum over each fainted opponent of `experienceForDefeating()` (with the knockout bonus when `knocked_out_by` is its index). On `CAUGHT`, the opponent's active Pokémon counts as knocked out by the player's active combatant. Opponents still standing give nothing, so running from a wild battle gives no XP.
- `EndBattleAction`, for every ending (`WON`, `LOST`, `FORFEITED`, `CAUGHT`), pays each amount through `AwardExperienceAction` inside its own `DB::transaction` (the exception in `.ai/rules/events.md`), and saves each result in `BattleState::experience_gains`: a list of `Shared\ValueObjects\ExperienceGain` (`combatant_index`, `earned_experience`, `level_before`, `level_after`) for the end screen. `earned_experience` is what was really added (0 at level 100); `is_at_max_level` is true when a level 100 Pokémon would have earned XP, so the end screen and the log can list it. A combatant whose Pokémon no longer exists gets 0.
- Battle Tower battles are counted the same way but not paid here: their `ExperienceGain` holds the counted XP with the level unchanged, and task 6.12 saves it in the run.

**Tests:** `ExperienceCalculatorTest` with every row of Spec 1's XP table, the 0.1 and 3 caps and each battle bonus; `AwardExperienceActionTest`: XP added, level goes up, level 100 gains nothing, negative XP refused; `EndBattleActionTest`: only Pokémon that entered the field gain XP, the knockout bonus, several defeated opponents add up, every ending pays for the opponents knocked out before it, running with none knocked out pays nothing, a catch pays like a knockout by the active Pokémon, Battle Tower XP is counted but not paid, the state holds each result; `TakeTurnActionTest`: the knockout is credited to the player's active Pokémon for a move and for a burn.

**Done when:** tests pass.

### 1.18 Evolution

- [x] Done

**Goal:** the backend for the Evolve button (the button itself is on the Pokémon page, Spec 4).

**Build:**
- Seed data checked: every Mega evolution has `min_level` 100 and its Mega Stone as `item`, except Mega Rayquaza (level 100 only, Spec 1); every G-Max evolution has `min_level` 100 and no item.
- `App\Pokemon\Mechanics\EvolutionRequirementChecker::check(Pokemon $pokemon, SpeciesEvolution $evolution, list<string> $ownedEvolutionItemIds): ?EvolutionRequirement`: `null` when allowed, otherwise the first unmet `App\Pokemon\Enums\EvolutionRequirement`, in this order: `GENDER`, `LEVEL`, `ITEM`. It takes the IDs of the evolution items the owner holds instead of the `User`, because Mechanics never query (the caller loads them once, also for the Evolve dialog), and needs `evolutionItem` loaded on the evolution.
- `App\Pokemon\Actions\EvolvePokemonAction::handle(Pokemon $pokemon, SpeciesEvolution $evolution): Pokemon`, under `LockKeys::pokemonEvolution()`: refuses an evolution that doesn't start from the Pokémon's species (`InvalidArgumentException`), re-checks the requirement (throws `App\Pokemon\Exceptions\EvolutionNotAllowedException` with the unmet requirement and the evolution; its message is "This Pokémon can't evolve yet." plus `EvolutionRequirement::reason($evolution)`: "Needs a female Pokémon", "Needs level 36", "Needs a Water Stone", "Needs an Ice Stone"), uses up the evolution item, if any, through `RemoveInventoryItemAction` (every evolution item, Spec 1), changes `species_id`, adds the new species' base moves to the known moves (no duplicates, active slots unchanged). Level, boost, shiny stay.
- Dispatches `App\Pokemon\Events\PokemonEvolved` (`pokemonId`, `userId`, `fromSpeciesId`, `intoSpeciesId`, `isMega`) for the missions (task 5.x).

**Tests:** checker's unmet requirement (level, item, gender, in that order) and allowed cases; `EvolutionRequirement::reason()` (gender, level, item with "a" and "an"); the exception's message; evolution keeps level, boost, shiny and active slots; known moves grow without duplicates; item used up; an evolution by level alone; refused when requirements are not met (level, item) with nothing changed; refused for another species' evolution; `PokemonEvolved` with `isMega`.

**Done when:** checker and action exist; tests pass.

### 1.19 Battle page: show the battle

- [x] Done

**Goal:** a read-only battle page.

**Mock-up:** `docs/mockups/battle.html` (state "Battle, choosing a move")

**Build:**
- Route `GET /battle` named `battle.web.show` → `App\Battle\Http\Web\Controllers\BattleController@show`; shows the user's `ONGOING` battle (`Battle::ongoing()` scope) or redirects to the Dashboard.
- `App\Battle\Http\Web\PageProps\BattleShowPageProps`: `battle` (type, turn, log), `player` and `opponent` (the active combatant's display name, gender, level, boost, shiny, ailment, sprite and `team` of names with `isFainted`), the player's HP as `currentHp`/`maxHp`, the opponent's HP only as `hpPercentage` (rounded up, so a sliver shows 1%), and `moves` (the player's active moves in slot order: name, type, power, Z/Dynamax). The player's sprite is the back sprite (`Species::getDisplayBackSprite()`, falling back to the front one).
- Page `resources/js/app/Battle/pages/Battle/index.vue` with the Spec 1 layout; stacks on phones. Sprites are shown at their natural 96px (80px on phones). The log reads oldest first and scrolls to the newest line. Title from the battle type ("Wild battle"), description "A wild Pidgey appeared.". The move buttons are shown disabled until task 1.20 wires them; status moves say "Status" and "Power —".

**UI suggestions** (ideas, not requirements):
- Layout: a CSS grid with two `Card`s, opponent at the top right and player at the bottom left; one column on phones (opponent, player, moves, log).
- Each Pokémon card: `SpeciesSprite`, `PokemonDisplayName`, `PokemonGenderIcon`, level and boost as `Badge`s, ailment as a small coloured `Badge` ("PAR", "BRN").
- HP: a `Progress` bar coloured by HP left (green above 50%, yellow 20–50%, red below 20%). Player card shows "410 / 520"; opponent card shows only "62%".
- Team: a row of small dots (filled = can fight, outlined = fainted), with a `Tooltip` naming each Pokémon.
- Moves: a 2×2 grid of large outline `Button`s, each with `MoveNameLabel`, a type badge and the power ("Power 90", "Power —" for status moves).
- Log: a `Card` with a fixed height and its own scroll, newest line at the bottom, scrolled down automatically.
- Header: `AppPageHeader` ("Wild battle") with the "Run" button on the right (task 1.21).

**Tests:** feature test for the props, including that opponent HP is a percentage only (rounded up); redirect when there is no battle in progress; the back sprite and its fallback.

**Done when:** page shows a battle started with `battle:start-wild`.

### 1.20 Battle page: take turns

- [x] Done

**Goal:** the player can fight.

**Mock-up:** `docs/mockups/battle.html` (state "Battle, choosing a move"; click the moves)

**Build:**
- Route `POST /battle/turn` named `battle.web.turn` → `App\Battle\Http\Web\Controllers\TakeTurnController` with `TakeTurnRequest` (`moveSlot`, 1 to `game.pokemon.max_active_moves`). It plays the user's ongoing battle (`BattleAlreadyEndedException` as a toast when there is none) and redirects back.
- Move buttons submit the turn and are all disabled while it is sent. Damaging moves that are super effective against the opponent's active Pokémon show a green "Super effective" hint (`isSuperEffective` on each move in `BattleShowPageProps`, from `DamageCalculator::typeMultiplier()` above 1).
- The HP bar slides down over 500ms (the only animation; none with reduced motion).

**UI suggestions** (ideas, not requirements):
- While a turn is being sent, disable all move buttons and show a spinner on the clicked one, so a double click can't send two turns.
- Effectiveness hint on move buttons: a small arrow-up icon for super effective, a muted arrow-down for not very effective, a crossed-out icon for no effect, each with a `Tooltip` explaining it.
- HP bar: animate its width with a Tailwind transition (`transition-[width] duration-500`).
- Highlight new log lines for a moment (a light background that fades).
- Errors (for example the battle already ended) as a toast through the existing toaster.

**Tests:** feature test: a valid turn updates the battle; invalid slot is rejected; browser test: clicking a move updates HP and the log.

**Done when:** a full battle can be played to the end from the page, except sending out and running.

### 1.21 Battle page: send out, run and end screen

- [x] Done

**Goal:** finish the battle flow on the page.

**Mock-up:** `docs/mockups/battle.html` (states "Pokémon fainted: send out", "Run confirmation", "Battle won", "Lost: XP kept", "Ran away")

**Build:**
- Route `POST /battle/send-out` (`battle.web.send-out`) → `SendOutPokemonController` with `SendOutPokemonRequest` (`combatantIndex`, 0-based); `SendOutDialog` lists the team (portrait, level, HP) when a Pokémon faints, from the `sendOut` prop (null unless awaiting a send-out). Task 1.24 replaced that prop with `battle.isAwaitingSendOut` and the rows of `player.team`.
- Route `POST /battle/forfeit` (`battle.web.forfeit`) → `ForfeitBattleController`; `RunBattleDialog` puts a "Run" button in the page header (a new default slot on `AppPageHeader`) with a confirmation `Dialog`. Both routes play the user's ongoing battle and warn with `BattleAlreadyEndedException` when there is none.
- With no ongoing battle, `GET /battle` shows the end screen of the player's latest battle if it ended less than `game.battle.end_screen_minutes` (10) ago (`Battle::recentlyEnded()` scope, by `updated_at`), so it survives a reload; otherwise it still redirects to the Dashboard (task 1.19).
- End screen (`BattleResultCard`, from the `result` prop, null while ongoing) for every ending (won, lost, ran; caught comes with task 2.19): the result, and one row per Pokémon that earned XP with the XP it got, its XP bar (`experienceProgress`, the share of the way to its next level now; none when the Pokémon no longer exists) and any level-up (from the state saved by task 1.17). With no XP earned, a short "No XP this time" line instead. A button back to the Dashboard.
- `EndBattleAction` adds the result line ("You won!", "You lost...") and one log line per Pokémon that earned XP ("Shiny Magikarp gained 340 XP!") and one per level-up ("Shiny Magikarp grew to Lv 55!"); none in the Battle Tower, where XP isn't paid. They are logged in the "End" section: turn `BattleState::endTurn()` (the current, unplayed turn number), which `BattleShowPageProps` flags with `isEnd` so the log card labels it "End". `ForfeitBattleAction` logs its line there too.
- `HpProgress` draws every HP bar on the page (combatant panels and the send-out dialog).

**UI suggestions** (ideas, not requirements):
- Send-out: a `Dialog` that can't be closed (no close button, no click outside) listing the team as selectable rows: `SpeciesPortrait`, name, level, HP `Progress`. Fainted Pokémon are greyed out and disabled. One primary button: "Send out".
- Run: a destructive `Button` that opens a confirmation `Dialog` (the shadcn-vue CLI could not add `alert-dialog` without upgrading dependencies) with "Keep fighting" (outline) and "Run" (destructive). In a wild battle: "Run from the wild Pidgey? You won't get anything for this battle." (Trainer and Elite Four wording comes with task 6.14.)
- End screen: the moves area is replaced by a result `Card`: a large title ("You won!", "You lost", "You got away safely"); one row per Pokémon that fought with portrait, "+300 XP", an XP `Progress` bar and a "Level up! 36 → 37" `Badge`; one primary button, "Back to Dashboard" (later tasks point it back where the battle came from).
- After a loss, a muted line under the XP rows: "XP is kept for the opponents you knocked out."
- The battle log also gets one line per Pokémon at the end ("Shiny Magikarp gained 340 XP!", "Shiny Magikarp grew to Lv 55!").

**Tests:** feature tests for both routes; the end screen's XP props after a win, a loss and a run; browser test for the faint dialog and running.

**Done when:** a battle can be played from start to end screen on the page.

### 1.22 Four base moves for every species (data)

- [x] Done

**Goal:** every species has exactly 4 different base moves (Spec 1), so every Pokémon starts with 4 active moves. Data only; can be done any time after task 1.5.

**Build:**
- Edit `database/seeders/files/species-base-moves.json` so every species lists 4 different moves that exist in `moves.json` after task 1.5 (no one-hit-KO moves). Today 49 species have fewer: Metapod, Weedle, Kakuna, Ditto, the 28 Unown forms, Wobbuffet, Smeargle, Wynaut, Spewpa, Pyukumuku, Cosmog, Cosmoem and Blipbug, plus the 9 that lost a one-hit-KO move in task 1.5 (Dewgong, Suicune, Glalie, Mega Glalie, Rhyperior, Vanilluxe, Bisharp, Vikavolt and Alolan Ninetales).
- Pick moves the species learns early in the main games when it has them; otherwise moves of its own type or Normal moves that fit it. Prefer at least one damaging move, so no Pokémon is stuck with only status moves.
- `SpeciesBaseMovesSeeder` throws a clear `RuntimeException` when a species doesn't end up with exactly `game.pokemon.max_active_moves` (4) different moves, including a species missing from the file, and when the file names a species that doesn't exist, so bad data fails the seed instead of creating Pokémon with fewer moves.
- Nothing needs the default-move fallback any more, so it is removed from `CreatePokemonAction` and `StartWildBattleAction`, along with `game.pokemon.default_base_move`, `Shared\Exceptions\GameDataException` (its only use) and the `game-data` log channel.

**Tests:** seeder test (in `tests/Shared/Unit/Seeders`): every species has exactly 4 different base moves; a new Pokémon of a species that had fewer (Ditto) gets 4 active moves.

**Done when:** `migrate:fresh --seed` passes with the check; tests pass.

### 1.23 Switch Pokémon during a battle

- [x] Done

**Goal:** on their turn, the player can switch the Pokémon in battle for another one of the battle's team; it uses the turn (Spec 1, "Switching").

**Build:**
- The opponent's half of a turn (its move, status effects, end-of-turn damage on both sides, fainting, ending the battle through `EndBattleAction`) reuses the turn pipes instead of a separate action: the switch sends `TurnData` through `EnsureBattleIsReadyForTurn`, `LoadMovesAndSpecies`, `EnsureSwitchIsAllowed`, `SwitchPlayerPokemon`, `ChooseOpponentMove`, `TakeActions`, `ApplyEndOfTurnDamage` (both sides), `CreditOpponentKnockout` (an opponent that faints from its own poison still credits the player's new active Pokémon) and `SaveTurn`. The player's `TurnData::$playerAction` stays null. `ThrowBallAction` (task 2.12) reuses the same pipes.
- How `TakeActions` learns that only the opponent acts: `TurnData`'s inputs are a nullable `moveSlot` (a move turn) and `switchIndex` (a switch). `TurnData::isUsingMove(Side)` is true for the opponent and for the player only when a move slot was given, and `TakeActions` skips a side that isn't using a move. `SwitchPlayerPokemon` sets `turnOrder` to player then opponent, which also orders the end-of-turn damage.
- `LoadMovesAndSpecies` loads the species of the player's whole team (not just the active one), so the switch can name the Pokémon coming in.
- `App\Battle\Actions\SwitchPokemonAction::handle(Battle $battle, int $combatantIndex): Battle`, under the same lock as `TakeTurnAction`:
  - Refuses when the battle is not `ONGOING` or is awaiting a send-out (`BattleNotReadyForTurnException`: a faint uses the free `SendOutPokemonAction` instead).
  - Refuses an index outside the player's side, the active combatant or a fainted one (`App\Battle\Exceptions\InvalidSwitchException::notInTeam()`, `alreadyInBattle()`, `fainted()`; checked by the `EnsureSwitchIsAllowed` pipe).
  - The leaving combatant loses its confusion; its other ailment stays. The chosen one becomes active with `has_entered_field = true`.
  - Logs "Come back, Shiny Magikarp!" and "Go! Dragonite!", then the opponent acts (always after the switch, with no turn-order roll), and `SaveTurn` increments `turn`.
- The player's side is the battle's own snapshot (party, chosen gym team or Tower team), so switching never reads the party again.
- Route `POST /battle/switch` named `battle.web.switch` → `SwitchPokemonController` with `SwitchPokemonRequest` (`combatantIndex`, camelCase like the other battle requests).

**Tests:** the switch makes the chosen Pokémon active and the opponent then attacks it; `has_entered_field` set (it gets XP at the end); confusion cleared, other ailments kept; refused for the active, a fainted or an unknown combatant, while awaiting a send-out and after the battle ended; end-of-turn damage still applies; a switch that ends with the new Pokémon fainting asks for a send-out; feature test for the route.

**Done when:** tests pass.

### 1.24 Battle page: switch Pokémon

- [x] Done

**Goal:** switch from the battle page.

**Mock-up:** `docs/mockups/battle.html` (states "Switch Pokémon", "Switched: the opponent attacks")

**Build:**
- `BattleShowPageProps`: `player.team` lists the player's whole team for the dialogs (portrait, display name, level, current and max HP, ailment, `isFainted`, `isActive`); the opponent's team keeps only name and fainted for the team dots. The `sendOut` prop is replaced by `battle.isAwaitingSendOut` (only while ongoing), since both dialogs now read `player.team`.
- `BattleTeamRadioGroup` draws the team rows for both dialogs (`SpeciesPortrait`, name, level, `AilmentBadge`, "In battle" badge, `HpProgress`); the active and fainted Pokémon are disabled. The consuming dialog passes its block for the test IDs (`send-out-dialog__pokemon-2-radio`, `switch-pokemon-dialog__pokemon-2-radio`). `AilmentBadge` is shared with `CombatantPanel` (`BattlefieldCombatantCard` since task 1.27).
- `SwitchPokemonDialog`: a full-width outline "Switch Pokémon" button (swap icon) in the moves card's footer, in every battle, disabled while a move is being sent or when no other Pokémon can fight. Its dialog can be cancelled, says that switching uses the turn, and posts the choice to `battle.web.switch`; the choice is cleared each time the dialog opens or closes.
- After the switch, the player card, HP and move buttons show the new Pokémon.

**UI suggestions** (ideas, not requirements):
- An outline `Button` with a swap icon, full width under the move grid (or side by side with "Throw ball" in wild battles).
- The dialog reuses the send-out dialog's rows (`SpeciesPortrait`, name, level, HP `Progress`, ailment badge), with an "In battle" badge on the active one. Unlike the send-out dialog, it can be closed ("Cancel" keeps the turn).
- One line under the title: "Switching uses your turn: your new Pokémon comes out first, then the opponent attacks it."
- Disable "Switch Pokémon" while a turn is being sent, like the move buttons, and when no other Pokémon can fight.

**Tests:** feature test for the team props and `isAwaitingSendOut`; browser test: switch to another Pokémon and see it in battle (HP and moves) with the new log lines.

**Done when:** a player can switch Pokémon from the battle page in any battle.

### 1.25 Battle log entries (backend)

- [x] Done

**Goal:** the log knows which turn and kind each message belongs to, so the page can group turns and style lines (Spec 1, "Battle log"). The log is a flat list of strings today; this task changes how messages are logged in the backend.

**Mock-up:** `docs/mockups/battle.html` (state "Battle log: every kind of message": every message and its kind)

**Build:**
- `Shared\Enums\BattleLogKind` (in Shared because it is stored in the `battles.state` column; TypeScript copy in `resources/js/shared/types/enums/`): `MOVE` (a "used Move!" line; the move name is shown in bold), `DAMAGE` ("Gengar lost 96 HP."), `DETAIL` (critical hit, effectiveness, no effect, multi-hit, "But it failed!", status inflicted, waking up, thawing out, "is hurt by its burn/poison!"), `INFO` (battle start, ran away, forfeit) and `EVENT` (everything else: status that stops a move, send-outs, switches, faints, rewards, XP).
- Value object `Shared\ValueObjects\BattleLogEntry` (`turn`, `kind`, `text`, nullable `move_name`), with `fromArray()` / `toArray()` like the other battle value objects. Only `MOVE` entries have a `move_name`, and the `text` keeps the whole line ("Pikachu used Tackle!"). `turn` is 0 for the lines before the first turn.
- `BattleState::$log` becomes `list<BattleLogEntry>`; `withLogLines()` is renamed `withLogEntries()`, and it and `withNextTurn()` take entries. `TurnData::log(kind, text, moveName)` stamps the turn being played; `TurnData::$logLines` becomes `$logEntries`.
- Lines logged between turns (a send-out after a faint, running, forfeiting, XP and level-ups) belong to the last turn played, `BattleState::lastPlayedTurn()` (`turn - 1`), so they show under the turn that caused them (or under "Start" before the first turn).
- New `DAMAGE` lines, one after each damaging action, with the HP actually lost (capped at the HP left), worded "Gengar lost 96 HP." / "The wild Pidgey lost 46 HP.": after a move's hits (after the effectiveness and "It hit 4 times!" lines, before a faint), after confusion self-damage and after burn and poison damage. `TurnData::damageActiveCombatant()` returns the HP lost and `TurnData::logDamage()` writes the line.
- Endings: running from a wild battle logs "You got away safely!" (it used to log "You ran away!"); forfeiting any other battle logs "You forfeited the battle." (both `INFO`).
- No production data exists: the `battles` table keeps its JSON column; old local battles are dropped with `migrate:fresh --seed`.
- `BattleLogCard` reads the new prop but still shows one flat list, oldest first, until task 1.33 rewrites it.
- `BattleShowPageProps`: `battle.log` becomes a list of turns, newest first: `{ turn: int, entries: list<{ kind: BattleLogKind, text: string, moveName: ?string }> }`.

**Tests:** every pipe that logs writes the right kind (one case per message in the mock-up's reference state; the turn tests compare whole entries); damage lines carry the HP actually lost (multi-hit totals, a faint that caps the loss, end-of-turn damage); entries survive the JSON round trip and reject a move name on a non-move entry; the page prop groups by turn, newest first; running and forfeiting log their messages.

**Done when:** tests pass and a played battle stores structured entries.

### 1.26 Battle page: layout and header

- [x] Done

**Goal:** the battle page uses the new three-column layout (Spec 1, "Battle page").

**Mock-up:** `docs/mockups/battle.html` (any state; narrow the window for the phone order)

**Build:**
- `pages/Battle/index.vue`: a grid with three columns on wide screens (Your team | battlefield, moves, More options or the result | the opponent's team and the Battle log) and one column on phones in this order: result, battlefield, moves (or the fainted card), Your team, log, More options. Use container queries on `@container/main-content` so the sidebar's width is respected.
- New `BattleHeader.vue` (replaces the `AppPageHeader` use on this page, and sets the browser tab title with `Head`): the title and a one-line description with the turn: wild "Wild battle" / "A wild Pidgey appeared in the Forest · Turn 3" (the area name comes with Spec 2; until then "A wild Pidgey appeared · Turn 3"); other battle types show only "Turn 3" until task 6.14. Once the battle has ended the description shows the last turn played. A `sprite` slot on the left for the trainer's sprite and the default slot on the right for the Elite Four steps (both filled by task 6.14). The Run button (`RunBattleDialog`) sits in the right slot until task 1.32 moves it into the More options card.
- Each later task fills one area; until then the existing cards sit in their new places: the battlefield (`BattleArenaCard`, `BattlefieldCard` since task 1.27), the moves or the result in the middle column, the log in the right one. The left column stays empty until task 1.29 adds the team rail.
- Each card has an `order-*` for the phone order and `@5xl/main-content:order-none` on wide screens; the columns are `display: contents` on phones so their cards join one grid, and flex columns (`col-start-2`, `col-start-3`) on wide screens.

**UI suggestions** (ideas, not requirements):
- Grid: `grid-cols-1 @5xl/main-content:grid-cols-[18rem_minmax(0,1fr)_18rem]`; on phones give each card an `order-*`, on wide screens reset the order.

**Tests:** browser tests: the header's title and description; the columns on a 1440px screen and the phone order on a 390px screen (compared with `getBoundingClientRect()` in `assertScript`); the result first on a phone once the battle has ended.

**Done when:** the page uses the new layout in both sizes.

### 1.27 Battle page: battlefield card

- [x] Done

**Goal:** the battlefield shows both Pokémon with their HP as numbers.

**Mock-up:** `docs/mockups/battle.html` (states "Wild: choosing a move", "Trainer: choosing a move")

**Build:**
- New `BattlefieldCard.vue` replaces `BattleArenaCard` and `CombatantPanel`: the opponent's card and sprite at the top right, the player's sprite and card at the bottom left, over the scene background (task 1.28). A fainted Pokémon's sprite fades to 35%.
- Each combatant card (`BattlefieldCombatantCard.vue`, renamed from `CombatantPanel`; test IDs `battlefield-combatant-card--{side}`, `…__name`, `…__hp-indicator`): `PokemonDisplayName` (which already draws `PokemonGenderIcon` right after the name), the ailment badge, the level as plain muted text aligned right ("Lv 34", no pill and no boost), and an "HP:" label before the HP `Progress` bar and "115 / 185". The shiny gold border is gone, as in the mock-up: "Shiny" in the name says it.
- `BattleShowPageProps`: the opponent's `currentHp` and `maxHp` replace `hpPercentage` (Spec 1 now shows the opponent's HP as numbers).
- Phones: smaller sprites and cards side by side with their sprite, through a `@container/battlefield` query on the card (the desktop look from 24rem wide); in every battle but wild ones the opponent's card adds team dots (`CombatantTeamDots`, now opponent-only), hidden at the page's three-column width, since the opponent's team rail (task 1.34) is not shown on phones. The player's card has no dots.
- When the battle ends: the moves card goes and the opponent's HP drains to 0 if it was knocked out. The battlefield stays on screen, with the opponent's sprite faded to 35% like a fainted Pokémon's (the player's only if it was knocked out), and the result card shows at once (task 1.35 adds its entrance). A battle that had already ended when the page loads shows the same faded battlefield and the result.

**UI suggestions** (ideas, not requirements):
- Cards on the scene: `bg-card/95 border rounded-lg`, 15rem wide on desktop.
- HP colours from the existing `HpProgress`.

**Tests:** feature test: the opponent's HP props are numbers (the old "percentage, rounded up" test now checks the numbers; its "a sliver" case went with the rounding); browser tests: the gender icon, level and "HP:" label show; the opponent's HP reads "125 / 218" after a turn; running keeps the battlefield on screen and the result shows; an already-ended battle shows the battlefield with the result.

**Done when:** both combatants show as in the mock-up on desktop and phones.

### 1.28 Battle page: scene backgrounds

- [x] Done

**Goal:** the battlefield has a background that fits where the battle happens.

**Mock-up:** `docs/mockups/battle.html` (the battlefield in each battle type)

**Build:**
- New `BattleSceneBackground.vue` (prop `scene`): inline SVG scenes drawn with theme-friendly colours (sky, ground, a few shapes), filling the battlefield card behind the cards and sprites, with `aria-hidden`. One `viewBox="0 0 600 300"` with `preserveAspectRatio="xMidYMid slice"`, so it covers the card at any size; the horizon sits at 58% like the mock-up.
- Scenes: grass field (wild: clouds, hills, grass tufts), trainer arena (trainers: a back wall and court lines), gym (pillars and floor tiles), Indigo Plateau (Elite Four: mountains and a floor emblem) and Battle Tower (towers with windows and floor tiles). The scene is picked from the battle type through a `scene` prop on `BattleShowPageProps` (`battle.scene`), so Spec 2 can later pick one per area.
- `App\Battle\Enums\BattleScene` (`GRASS_FIELD`, `TRAINER_ARENA`, `GYM`, `INDIGO_PLATEAU`, `BATTLE_TOWER`, kebab-case values) with `fromBattleType()`; TypeScript copy in `resources/js/app/Battle/types/enums/`. It moves to Shared when Spec 2 picks a scene per area from another domain.
- Dark mode: every colour has a dark variant. Each scene sets CSS variables (`--scene-sky`, `--scene-ground`, `--scene-far`, `--scene-light`, `--scene-detail`, `--scene-line`) with the mock-up's sky and ground colours, and redefines them under `@variant dark`.

**Tests:** feature test: the scene prop per battle type; browser test: the background fills the card (`battle-scene-background--{scene}`) and the combatant card is on top of it.

**Done when:** each battle type has its scene in light and dark mode.

### 1.29 Battle page: Your team rail and switching

- [x] Done

**Goal:** the player's team is always visible and switching happens from it (Spec 1, "Switching").

**Mock-up:** `docs/mockups/battle.html` (states "Wild: choosing a move", "Wild: switch Pokémon (Dragonite picked)")

**Build:**
- New `BattleTeamRail.vue` (replaces the switch dialog, `SwitchPokemonDialog`, and the "Switch Pokémon" button under the moves): a card titled "Your team" with an info tooltip, and the team as a radio group (shadcn-vue `RadioGroup`).
- One item per Pokémon in battle order: `SpeciesPortrait`, `PokemonDisplayName` with `PokemonGenderIcon` right after the name (on the same line as the last word when the name wraps), "Lv 54 · +72", the ailment badge, and "HP:" with the HP bar and numbers. Long names wrap; they are never cut off.
- The Pokémon in battle has a Poké Ball icon on the right and no HP bar (the battlefield shows its HP). The checked item has a muted background and border; portraits keep the same size. Only one item is checked at a time: it starts on the Pokémon in battle, and picking a benched Pokémon moves the checked look to it while it keeps its HP bar.
- Fainted Pokémon are disabled items with a "Fainted" tag, faded, with a greyscale portrait. Every item is disabled once the battle has ended.
- A "Switch" outline button under the rail, sized to its text and aligned right: enabled only when the checked Pokémon isn't the one in battle; it posts task 1.23's route. No confirmation dialog.
- Phones: the same radio group as a 2-column grid of tiles (portrait, name with gender icon, and the same "HP:" row as on desktop with the bar and numbers; the checked tile shows the Poké Ball instead).
- Changed after it was built: tiles left a lot of empty space on small screens and hid the level, boost and ailment. Below the three-column layout the items now keep the desktop rows (with level, boost, ailment and HP), in 1 column, 2 from `@md` and 3 from `@2xl` (measured on the nearest container: the main content for the rail, the sheet itself for the send-out sheet, which is drawn outside the main content). The mock-up shows the same; its demo team has "Calyrex (Shadow Rider)" to show a long name wrapping. Long names are never shortened: `wrap-anywhere` only breaks a single word that is wider than its column.
- Tooltip: "The highlighted Pokémon is the one in battle. To switch, pick another one and press Switch. Switching uses your turn. Fainted Pokémon can't be picked." After the battle: "HP as the battle ended."
- Changed after it was built: the info tooltip was removed from Your team. The radio group, the Poké Ball and the fainted look already explain the rail, and the fainted card and the sheet explain sending out.
- `BattleShowPageProps`: `player.team` items gain `gender`, `boost`, `currentHp` and `maxHp` if missing (only `gender` and `boost` were missing).
- As built: `BattleTeamRadioGroup` was rewritten into the rail's items (props `testIdBlock`, `disabled`, `isGridBelowWideLayout`, `marksPokemonInBattle`) and was shared with `SendOutDialog` (task 1.30 turned it into `SendOutSheet`); `SwitchPokemonDialog` is deleted. Each item is a `FieldLabel` wrapping a visually hidden `RadioGroupItem` (test ID `battle-team-rail__pokemon-2-radio`), so the whole row is the choice and keeps the keyboard focus ring. The Poké Ball is Tabler's `IconPokeball` with an "In battle" screen-reader label. The rail and Switch are disabled while any Inertia request runs (`router.on('start' | 'finish')`) and while waiting for a send-out; the Switch button is hidden once the battle has ended, and no item is checked then. After a switch the checked item follows the new Pokémon in battle.
- `PokemonDisplayName` (shared) now keeps the gender icon on the same line as the last word (the last word and the icon sit in a `whitespace-nowrap` span), and `PokemonGenderIcon` sits on the text baseline (`align-[-0.15em]`), so it works inline in wrapping text everywhere.

**UI suggestions** (ideas, not requirements):
- Reuse `BattleTeamRadioGroup`'s item markup where it fits, then delete what is no longer used.
- Disable the rail and the Switch button while a turn is being sent.

**Tests:** feature test: the team props with `gender` and `boost`; browser tests: Switch is disabled until a benched Pokémon is picked, and switching shows it on the battlefield (the old switch-dialog test now uses the rail); the Pokémon in battle and a fainted one can't enable Switch; the number of columns at each width (1 on a phone, 2 on a small tablet, 3 on a large one, a single list in the wide layout).

**Done when:** the player can see their team and switch from the rail on desktop and phones.

### 1.30 Battle page: send out from the team rail

- [x] Done

**Goal:** after a faint, the player sends out the next Pokémon from the team rail instead of a dialog.

**Mock-up:** `docs/mockups/battle.html` (state "Wild: Pokémon fainted, send out"; on a phone it opens as a bottom sheet)

**Build:**
- New `PokemonFaintedCard.vue` in place of the moves while `battle.isAwaitingSendOut`: "Shiny Magikarp fainted!" and "Pick who to send out in Your team, then press Send out. This doesn't use a turn."
- `BattleTeamRail` in send-out mode: no item is checked at first, the button reads "Send out" and is the primary button, enabled once a Pokémon that can fight is picked; it posts task 1.21's route. Tooltip: "Pick who to send out, then press Send out. This does not use a turn." (Removed later with the rail's tooltip, see task 1.29; the fainted card says the same.)
- Phones: a bottom `Sheet` that can't be closed (no close button, no click outside), titled "Shiny Magikarp fainted!", with the same radio items (with HP bars) and a "Send out" button.
- Remove `SendOutDialog`.
- As built: `SendOutDialog` became `SendOutSheet` (test IDs `send-out-sheet`, `send-out-sheet__pokemon-2-radio`, `send-out-sheet__send-out-button`). The page opens it only while the battle is waiting for a send-out and the page is in its phone layout. It checks the phone layout by measuring the grid: narrower than 64rem, the same width as the `@5xl/main-content` breakpoint. On wide screens the rail's "Send out" button (`battle-team-rail__send-out-button`) does the job. The rail stays enabled while waiting for a send-out. `PokemonFaintedCard` (test IDs `pokemon-fainted-card`, `__title`) takes the moves' place and points left at Your team with a chevron, as in the mock-up. The shared `SheetContent` gained a `showCloseButton` prop, like `DialogContent`. The sheet's rows use the same 1–3 column grid as the rail (`isGridBelowWideLayout`, see task 1.29), with the sheet as their container.

**Tests:** browser tests: on a wide screen, after a faint the fainted card replaces the moves, Send out enables after picking, and sending out shows the new Pokémon; on a phone, the sheet stays open after Escape and a press outside it, and sending out from it shows the new Pokémon (these replace the old send-out-dialog test).

**Done when:** a faint is handled from the rail on desktop and from the sheet on phones.

### 1.31 Battle page: Choose a move card

- [x] Done

**Goal:** the player picks a move, then attacks.

**Mock-up:** `docs/mockups/battle.html` (state "Wild: choosing a move"; click the moves)

**Build:**
- `MovesCard.vue` rewritten as a card titled "Choose a move" (no "What will X do?" line) with the four moves as shadcn-vue choice cards (`RadioGroup` items inside `FieldLabel`s, like a questionnaire choice): the move name and its type badge on the left, the radio circle on the right. The first move is selected when the page loads and after every turn.
- An "Attack" primary button under the grid, sized to its text and aligned right: it posts task 1.20's route with the selected slot.
- No power and no effectiveness hint on the moves: drop `isSuperEffective` (and `power` if nothing else uses it) from `BattleShowPageProps`.

**UI suggestions** (ideas, not requirements):
- Checked card: `border-primary bg-muted`; the dot fades and scales in (200ms).
- While a turn is being sent, disable the cards and the Attack button and show a spinner on the button.

**Tests:** feature test: the move props no longer have the hint; browser test: picking a move and pressing Attack takes the turn with that move; the first move is preselected.

**Done when:** the player attacks with the selected move.

### 1.32 Battle page: More options card

- [x] Done

**Goal:** running or forfeiting (and later throwing a ball) sit in their own card.

**Mock-up:** `docs/mockups/battle.html` (states "Wild: run confirmation", "Trainer: forfeit confirmation", "Wild: Pokémon fainted, send out")

**Build:**
- New `BattleOptionsCard.vue` under the moves, titled "More options", with buttons sized to their text:
  - Wild battles: "Run" (destructive outline); task 2.19 adds "Throw ball" before it.
  - Every other battle: "Forfeit" (destructive outline).
- Both open a confirmation (`alert-dialog`, replacing `RunBattleDialog`, which task 1.26 left in `BattleHeader`'s right slot) with "Keep fighting" (outline) and the destructive "Run" or "Forfeit":
  - Wild: "Run from the wild Pidgey?" / "You won't get anything for this battle. Your Pokémon are healed either way."
  - Trainer: "Forfeit against Hiker Bob?" / "Forfeiting counts as a loss: no money, but your Pokémon keep the XP for opponents they knocked out." (gym, Elite Four and Tower wording comes with task 6.14).
- The card also shows while waiting for a send-out; there "Throw ball" (task 2.19) is disabled.

**Tests:** browser tests: Run in a wild battle and Forfeit in a trainer battle open their dialogs; confirming ends the battle; the card shows during a send-out.

**Done when:** the player can run or forfeit from the card.

### 1.33 Battle page: Battle log card

- [x] Done

**Goal:** the log reads turn by turn, newest first.

**Mock-up:** `docs/mockups/battle.html` (any state; open and close the turns; the reference state "Battle log: every kind of message")

**Build:**
- `BattleLogCard.vue` rewritten on task 1.25's entries: a card titled "Battle log" with an info tooltip ("The newest turn is at the top and open; older turns are collapsed. Click a turn to open or close it.").
- One collapsible section per turn ("Start", "Turn 1", …), newest first; only the newest is open. The open/closed choice is kept until the page reloads; a new turn opens itself.
- Each entry is a plain line with no coloured bar. `MOVE` entries show the move name in bold; `DAMAGE` and `DETAIL` entries are smaller and muted; `INFO` entries are muted. Success and failure messages have no colour and no bold.
- New lines fade in one after another (about 140ms apart) when a turn comes in.
- The list scrolls inside the card (about 470px tall on desktop, 300px on phones).

**UI suggestions** (ideas, not requirements):
- shadcn-vue `Collapsible` for the turns, with a chevron that rotates.

**Tests:** browser tests: the newest turn is open and on top; older turns toggle; a new turn's lines appear on top after taking a turn; the move name is bold.

**Done when:** the log matches the mock-up on desktop and phones.

### 1.34 Battle page: opponent's team rail

- [x] Done

**Goal:** in battles against a trainer, the player sees the trainer's team.

**Mock-up:** `docs/mockups/battle.html` (states "Trainer: choosing a move", "Gym: choosing a move", "Elite Four: choosing a move")

**Build:**
- New `OpponentTeamRail.vue` above the Battle log on wide screens, in every battle but wild ones: a card titled "Hiker Bob's team" with an info tooltip ("Opponent's team" until task 6.4 adds the trainer's name to the battle), built like the player's rail (task 1.29) but read-only (not a radio group).
- The Pokémon in battle has the "in battle" look with a Poké Ball icon and no HP bar; the others show "Lv 35", "HP:" with the bar and "180 / 180", or "Fainted" with a greyscale portrait.
- Gym leaders and the Elite Four keep their team secret: Pokémon that haven't come out yet show as dashed "Not sent out yet" rows with no portrait, level or HP bar; tooltip "Pokémon are revealed as they're sent out." Other trainers show the whole team; tooltip "The whole team is shown from the start."
- Phones don't show the rail; the opponent's battlefield card has team dots instead (task 1.27).
- `BattleShowPageProps`: `opponent.team` items gain `portrait`, `gender`, `level`, `boost` (only for the one in battle), `currentHp`, `maxHp`, `isActive` and `isRevealed` (a combatant that has entered the field, or every combatant when the battle type isn't `GYM` or `ELITE_FOUR`). Unrevealed items carry no species data.

**Tests:** feature tests: the team props for a trainer battle and for a gym battle (unrevealed combatants send no name); browser test: the rail in a trainer battle and its absence in a wild one.

**Done when:** the rail shows in non-wild battles, ready for Spec 6.

### 1.35 Battle page: result card

- [x] Done

**Goal:** the end of a battle reads clearly and appears smoothly.

**Mock-up:** `docs/mockups/battle.html` (states "Wild: won", "Wild: ran away", "Wild: lost", "Trainer: won (money and XP)", "Trainer: lost (XP kept)")

**Build:**
- `BattleResultCard.vue` rewritten in place of the battlefield once the battle ends: the title ("You won!", "You got away safely", "You lost to the wild Pidgey") with an optional badge on the same line, aligned right; an optional muted subtitle. No ending has a badge yet, so the card leaves it out until a task fills one (Spec 6).
- "Your rewards": a section for money and badges, empty in wild battles, so the card leaves it out until Spec 3 and task 6.14 fill it.
- "XP gained": one row per Pokémon that got XP (`ExperienceGain` gains `is_from_experience_share`, false until task 5.10): `SpeciesPortrait`, `PokemonDisplayName` with `PokemonGenderIcon`, "+340 XP", and a "Level up! 54 → 55" pill aligned right (white background, border). All text in the row is the same size as the team rail's names. No XP bars: drop `experienceProgress` from the `result` prop.
- Rows for Pokémon that got XP only through the XP Share say "via XP Share" after the XP (`isFromExperienceShare` on each gain; always false until task 5.10). With the XP Share there is no summary line.
- "No XP this time: the wild Pidgey wasn't knocked out or caught." when nobody got XP.
- Buttons sized to their text, aligned right, the primary one last ("Keep exploring" / "Back to Dashboard" in wild battles). Exploring doesn't exist yet, so the card only has "Back to Dashboard" (primary) until task 2.17 adds "Keep exploring".
- The ending plays in order: the opponent's HP drains while the battlefield stays, with the opponent's sprite faded (task 1.27), and the card slides up and fades in (about 450ms).

**Tests:** feature test: the `result` prop without `experienceProgress` and with `isFromExperienceShare`; browser tests: the won, ran and lost cards; the battlefield stays visible once the card shows.

**Done when:** every ending of a wild battle shows the new card.

### 1.36 Battle page: level beside the name in the team rail

- [x] Done

**Goal:** the team rail's rows are shorter and match the battlefield cards.

**Mock-up:** `docs/mockups/battle.html` (states "Wild: choosing a move", "Wild: switch Pokémon (Dragonite picked)", "Wild: Pokémon fainted, send out")

**Build:**
- `BattleTeamRadioGroup`: the level moves onto the name's line, on the right, as plain muted text ("Lv 54"), before the "Fainted" tag; the boost ("· +72") is no longer shown. The ailment badge sits right after the name (and its gender icon). The separate meta line is gone, so each item is the name line plus the "HP:" row (only the name line for the Pokémon in battle).
- Long names still wrap; the level stays on the first line, aligned right.
- Applies to the rail and to the send-out sheet, which share the items.
- `BattleShowPageProps`: keep `boost` on `player.team`; the Pokémon details (task 1.38) show it.
- As built: the row's name line, ailment, level and HP row live in the new `TeamRailMemberBody`, shared by both rails. The ailment badge sits inline after the name, so it wraps with it.

**Tests:** browser test: an item shows "Lv 54" in its name row and no boost.

**Done when:** the rail and the send-out sheet show the level beside the name on desktop and phones.

### 1.37 Battle page: In battle and Bench groups in the team rails

- [x] Done

**Goal:** the rails say plainly which Pokémon is on the field, and the opponent's team is visible on phones too.

**Mock-up:** `docs/mockups/battle.html` (states "Wild: choosing a move", "Wild: switch Pokémon (Dragonite picked)", "Wild: Pokémon fainted, send out", "Gym: choosing a move"; narrow the window for phones)

**Build:**
- `BattleTeamRail`: two groups, each with a small uppercase muted heading. **In battle**: the Pokémon on the field as a plain row: no background, no border, no HP bar (the battlefield shows its HP), not selectable and not part of the radio group. **Bench**: every other Pokémon in team order, as the `RadioGroup`; nothing is checked at first (also outside a send-out), and picking one gives it the checked look and enables Switch. After a switch, the Pokémon switched out goes back to its place on the bench. The Poké Ball icon goes.
- While waiting for a send-out, the fainted Pokémon stays under In battle (faded, "Fainted" tag); `SendOutSheet` shows only the bench rows.
- `OpponentTeamRail`: the same two groups, **In battle** and **Rest of the team** (the latter keeps the "Not sent out yet" rows in team order). Still read-only.
- Phones: the opponent's rail shows below the three-column layout too, right after Your team (before the Battle log), with its rows in the same 1–3 column grid as Your team. The group headings span the whole row; the In battle row takes one cell.
- Remove `CombatantTeamDots` from the opponent's battlefield card (and delete the component): the rail now shows the same on phones.
- Changed after it was built: the In battle row has no level (the battlefield shows it). The mock-up had dropped its border and background by accident: it keeps the same bordered box as the other rows, only more compact. Bench rows that can be picked get a soft muted background on hover, so it is clearer they can be picked.
- As built: `BattleTeamRadioGroup` draws only the bench (every Pokémon but `isActive`), keeping each one's team index as its radio value and test ID (`battle-team-rail__pokemon-2-radio`). The groups have test IDs `battle-team-rail__in-battle-list`, `battle-team-rail__bench-list`, `opponent-team-rail__in-battle-list` and `opponent-team-rail__rest-of-the-team-list`. The checked Pokémon resets after every switch, faint (when the send-out starts) and the end of the battle. A fainted opponent shows the "Fainted" tag and no HP row, like a fainted Pokémon in battle. `TeamRailGroup` draws a group's heading and list for both rails; on phones the opponent's rail has its own `order` step between Your team and the Battle log.

**Tests:** browser tests: the in-battle Pokémon is under In battle and is not a radio; nothing is checked at first and Switch is disabled; after switching, the new Pokémon moves to In battle and the old one back to its bench place; the opponent's rail shows on a 390px screen after Your team; the battlefield card has no team dots.

**Done when:** both rails show the two groups on desktop and phones.

### 1.38 Battle page: Pokémon details from the portrait

- [x] Done

**Goal:** the player can check any Pokémon's types, boost and moves without leaving the battle and without cluttering the rails.

**Mock-up:** `docs/mockups/battle.html` (states "Wild: Dragonite's details (hover card; sheet on phones)", "Gym: Onix's details (Brock's team)"; hover the portraits on a wide screen, tap them on a narrow one)

**Build:**
- In both rails each row's portrait becomes its own button (the radio area is the rest of the row, so tapping the name or HP still picks the Pokémon). The portrait stays 36px; the button around it is 40px with no visible padding, so it meets the touch-target size. No icon on it; hover and focus show a ring. "Not sent out yet" rows have no portrait button.
- New `PokemonDetailsHoverCard.vue` (wide screens, from `@5xl/main-content`): shadcn-vue `HoverCard` (add it with the CLI) opened by hovering or focusing the portrait, beside the rail (right of Your team, left of the opponent's). Content: name with gender icon and "Lv 61" on the right; the type badges (`PokemonTypeBadges`); "Boost +20 · +20% HP and damage" when boosted, and for shiny Pokémon "Shiny · +25% HP and damage, takes 25% less damage"; the four moves in two columns, each the move name with "Dragon · 80" (or "Status") in small muted text below.
- New `PokemonDetailsSheet.vue` (below the three-column layout): tapping the portrait opens a bottom `Sheet` (closable: swipe down, tap outside, close button) with a 56px portrait, the name with gender icon, "Lv 61 · HP 568 / 568", then the same content as the hover card in slightly larger text.
- Works the same for every Pokémon in your team (fainted or not; it shows the battle's max HP, unchanged by fainting) and for the opponent's revealed Pokémon.
- `BattleShowPageProps`: `player.team` items and revealed `opponent.team` items gain `isShiny`, `types` and `moves` (`name`, `type`, `power`, null for status moves); `opponent.team` items get `boost` for every revealed Pokémon, not only the one in battle. Unrevealed items still carry no species data.

**UI suggestions** (ideas, not requirements):
- One `PokemonDetails` content component shared by the hover card and the sheet.

**As built:**
- Changed after it was built: the hover card opens above the portrait instead of beside the rail (below it when there is no room above).
- `PokemonDetailsButton` is the portrait button (test ID `battle-team-rail__pokemon-2-details-button`, `opponent-team-rail__pokemon-1-details-button`); it holds the `HoverCard` and its own `PokemonDetailsSheet` (test IDs `pokemon-details-hover-card`, `pokemon-details-sheet`, `pokemon-details-sheet__close-button`). The page shares whether it is below the three-column layout through `useBattleLayout` (provide/inject), so the hover card only opens on wide screens and a tap only opens the sheet below them. Swiping down uses VueUse's `useSwipe` on the sheet's head.
- The send-out sheet keeps plain portraits: it already is a sheet that must be answered.
- On wide screens a tap (or Enter) on the portrait also opens the hover card, so a tablet in landscape can open the details; `aria-haspopup="dialog"` is set only below the three-column layout, where the portrait opens the sheet, and `aria-expanded` follows whichever is open.
- The page only loads the moves of the player's team and of revealed opponents; a move that no longer exists is left out of the details instead of failing the page.
- `hover-card` was added with the shadcn-vue CLI; the CLI's dependency bumps, its stray `cn` package and its base CSS were reverted, and its `cn` import points at `@/shared/lib/utils`.
- Unrevealed opponents send `isShiny`, `types` and `moves` as null.

**Tests:** feature tests: the new team props for the player and for a gym battle (unrevealed opponents send no types or moves); browser tests: hovering a portrait on a 1440px screen shows its types and moves; tapping one on a 390px screen opens the sheet and its close button closes it; tapping a bench row's name still picks it for Switch.

**Done when:** every revealed Pokémon's details open from its portrait on desktop and phones.

---

## 2. Spec 2 — Catching and exploration

Read [02-catching-and-exploration.md](02-catching-and-exploration.md) first. Needs Spec 1 tasks done (battles, `RandomHelper`, `PokemonCaught` event). Exploring lives in a new domain, `src/App/Exploration`; catching lives in `src/App/Battle`. NPC trainers met while exploring come with Spec 6.

### 2.1 Mark Ultra Beasts

- [x] Done

**Goal:** the Beast Ball can tell Ultra Beasts apart.

**Build:**
- In `006_create_species_table.php`: `boolean('is_ultra_beast')->default(false)` after `is_gmax`.
- Seed files: `"is_ultra_beast": true` on Nihilego, Buzzwole, Pheromosa, Xurkitree, Celesteela, Kartana, Guzzlord, Poipole, Naganadel, Stakataka and Blacephalon; `false` on every other species. `SpeciesSeeder` reads it.
- `Species` model: cast to boolean. `SpeciesTrait::ULTRA_BEAST` (`ultra-beast`) with label "Ultra Beast", wherever the other traits are listed: the Pokédex filters, the Pokédex page's Traits row (`isUltraBeast`) and the Box filters (`PokemonTrait::ULTRA_BEAST`). In the filters it can't be combined with Baby, Legendary, Mythical, Mega or G-Max, since no Ultra Beast is any of those.

**Tests:** seeder test: exactly 11 Ultra Beasts.

**Done when:** column, data and trait exist; tests pass.

### 2.2 Wild data on species

- [x] Done

**Goal:** every species knows whether it can appear in the wild, at which evolution stage, and at which levels. Calculated once while seeding.

**Build:**
- In `006_create_species_table.php`: `unsignedTinyInteger('evolution_stage')->default(1)`, nullable `unsignedTinyInteger('wild_min_level')` and `wild_max_level` (null = never in the wild).
- `App\Exploration\Mechanics\WildSpawnCalculator` (reads loaded `previousEvolutions` relations, no queries):
  - `evolutionStage(Species $species): int`: 1 + the stage of the previous evolution; baby previous evolutions do not count (Pikachu is stage 1).
  - `levelRange(Species $species): ?array` returning `[min, max]`:
    - Mega, G-Max or baby → `null`.
    - Legendary or mythical → 50–70.
    - Stage 1 → 2–12.
    - Evolved by level → `min_level` to `min_level` + 4.
    - Evolved another way → min = the larger of 18 and the previous stage's `wild_min_level`; max = min + 4.
    - With several ways to evolve into a species, use the lowest result.
    - The max never goes above `game.pokemon.max_level` (Eternal Floette evolves at 100: 100–100).
- Config `game.exploration`: `unevolved_levels` ([2, 12]), `legendary_levels` ([50, 70]), `other_evolution_min_level` (18), `evolved_level_spread` (4).
- Seeder `SpeciesWildDataSeeder`, run after species and evolutions, saves the three columns using the calculator.

**Tests:** `tests/App/Exploration/Unit/Mechanics/WildSpawnCalculatorTest.php`: Charmander 2–12 stage 1; Charizard 36–40 stage 3; Raichu 18–22; Vileplume never below Gloom's minimum; Pikachu stage 1; Mewtwo 50–70; Pichu, Mega Charizard X and G-Max Pikachu → `null`; never above the max level. Seeder test: the saved columns for a few real species.

**Done when:** columns are filled after `migrate:fresh --seed`; tests pass.

### 2.3 Areas

- [x] Done

**Goal:** the 9 areas exist in code.

**Build:**
- Enum `Shared\Enums\Area`: `MEADOW`, `FOREST`, `COAST`, `VOLCANO`, `POWER_PLANT`, `CAVE`, `SNOWY_PEAK`, `HAUNTED_TOWER`, `ANCIENT_RUINS` (values in kebab-case, for example `power-plant`).
- `label(): string` and `types(): array` (the two `PokemonType`s from the Spec 2 table).

**Tests:** every `PokemonType` belongs to exactly one area.

**Done when:** enum exists; tests pass.

### 2.4 Which species live in an area

- [x] Done

**Goal:** list the species that can appear in an area.

**Build:**
- `App\Exploration\Queries\AreaSpeciesQuery::handle(Area $area): Builder` (Query classes return a `Builder`, see `.ai/rules/queries.md`): species with at least one of the area's types and a non-null `wild_min_level`. Ordered by `pokedex_order`.

**Tests:** includes a matching dual-type species; excludes other types, Megas, G-Max and babies.

**Done when:** query exists; tests pass.

### 2.5 Roll an encounter

- [x] Done

**Goal:** pick the wild Pokémon for one explore.

**Build:**
- Move `game.incubation.shiny_chance` to `game.pokemon.shiny_chance` (eggs and the wild share it; update `PlaceEggAction`).
- Config `game.exploration`: `legendary_chance` (0.2, 1 in 500), `stage_weights` ([1 => 85, 2 => 12, 3 => 3]).
- `App\Exploration\Mechanics\EncounterRoller::roll(Collection $areaSpecies): NewPokemon`, using `RandomHelper`:
  1. With `legendary_chance`: a legendary or mythical, if the area has any.
  2. Otherwise a stage from the weights; if the area has no species at that stage, fall back to the next lower stage.
  3. A species picked evenly from that group (legendaries and mythicals excluded in this step).
  4. Level between the species' `wild_min_level` and `wild_max_level`; shiny with `shiny_chance`; gender from `getRandomGender()`.
- As built: `rollEvolutionStage(): int` is public, so the stage weights are tested on their own. The legendary chance is only rolled when the area has a legendary or mythical; an area with nothing else always gives one. An empty species list throws `InvalidArgumentException`.

**Tests:** with `RandomHelper::fake()`: legendary roll, each stage, fallback to a lower stage, level within range, shiny.

**Done when:** roller exists; tests pass.

### 2.6 Save the encounter and explore

- [x] Done

**Goal:** pressing Explore (or Keep exploring) stores one wild Pokémon for the player.

**Build:**
- Migration `015_create_encounters_table.php`: `uuid id`, `foreignIdFor(User)` unique, `area` enum, `foreignIdFor(Species)`, `level`, `is_shiny`, `gender` enum, timestamps.
- Model `Shared\Models\Encounter` (with factory), `User::encounter(): HasOne`, `Encounter::toNewPokemon(): NewPokemon`.
- `App\Exploration\Actions\ExploreAction::handle(User $user, Area $area): Encounter`: refuses with `BattleAlreadyInProgressException` when a battle is ongoing; replaces any existing encounter with a new roll from `EncounterRoller`.
- As built: the action runs under a per-user lock (`App\Exploration\Support\LockKeys::encounter()`, shared with `BattleEncounterAction`) and rolls from `AreaSpeciesQuery`. Replacing updates the player's existing row (`updateOrCreate` on `user_id`), so the encounter keeps its ID.

**Tests:** creates one encounter; a second explore replaces it (still one row); refused during a battle.

**Done when:** table, model and action exist; tests pass.

### 2.7 Battle the encounter

- [x] Done

**Goal:** turn the stored encounter into a wild battle.

**Build:**
- `BattleState`: add `?Area $area` (null outside wild battles), saved by `AsBattleState`.
- `StartWildBattleAction::handle()`: add a required `Area $area` parameter and store it. The local `battle:start-wild` command uses `Area::fromType()` with the species' first type.
- `App\Exploration\Actions\BattleEncounterAction::handle(User $user): Battle`: throws `App\Exploration\Exceptions\NoEncounterException` without an encounter; starts the battle with the encounter's Pokémon and area; deletes the encounter.
- As built: `BattleState::$area` is the last constructor argument, defaulting to null, and states saved without it load with a null area. `BattleEncounterAction` takes the same per-user lock as `ExploreAction` (`LockKeys::encounter()`), and starts the battle and deletes the encounter in one transaction, so the encounter is kept when the battle can't start (for example with an empty party).

**Tests:** battle created with the encounter's species, level, shiny and area; encounter deleted; refused without an encounter.

**Done when:** tests pass.

### 2.8 Catch chance

- [x] Done

**Goal:** the Spec 2 catch formula.

**Build:**
- Config `game.catching`: `base_chance` (10), `legendary_base_chance` (1), `low_hp_bonus` (1.5), `ailment_multipliers` (sleep and freeze 2, paralysis, burn and poison 1.5).
- `App\Battle\Mechanics\CatchChanceCalculator::calculate(Species $species, int $currentHp, int $maxHp, ?Ailment $ailment, float $ballMultiplier): float`: base × ball × (1 + 1.5 × (1 − HP ratio)) × ailment, as a percentage capped at 100.
- As built: `ailment_multipliers` is keyed by the `Ailment` value; confusion, unknown and no ailment count as ×1. HP below 0, above the max HP or a max HP below 1 throws `InvalidArgumentException`. The result is not rounded: the Spec 2 table shows it rounded to whole percentages (23.5 → 24%).

**Tests:** every row of the Spec 2 example table.

**Done when:** calculator exists; tests pass.

### 2.9 Ball multipliers: basic balls

- [x] Done

**Goal:** balls without conditions.

**Build:**
- `pokeballs.json`: `catch_rate` holds the multiplier when no condition applies: Poké 1, Great 1.5, Ultra 2, Safari, Sport and Park 1.5, collectible balls 1, conditional balls 1, Beast Ball 0.1.
- `App\Battle\Mechanics\BallMultiplierCalculator::multiplier(Pokeball $pokeball, CatchContext $context): float`: returns `catch_rate`; `isGuaranteed(Pokeball $pokeball): bool` is true for the Master Ball.
- Value object `App\Battle\ValueObjects\CatchContext`: `int $turn`, `?Area $area`, `bool $isNight`, `Species $wildSpecies`, `int $wildLevel`, `PokemonGender $wildGender`, `Species $activeSpecies`, `int $activeLevel`, `PokemonGender $activeGender`, `bool $playerOwnsSpecies`.
- As built: only the Park Ball's data changed (255 → 1.5); every other ball already held the listed multiplier. The Master Ball keeps 255 in `catch_rate`, but `isGuaranteed()` is what makes it always catch. `CatchContext` refuses a turn or a level below 1.

**Tests:** basic balls return their multiplier; Master Ball is guaranteed.

**Done when:** data, calculator and value object exist; tests pass.

### 2.10 Ball multipliers: conditional balls

- [x] Done

**Goal:** the conditions from the Spec 2 ball table.

**Build:** in `BallMultiplierCalculator::multiplier()`, a `match` on the ball's slug for Quick, Timer, Repeat, Nest, Level, Dusk, Dream (needs the wild ailment: add `?Ailment $wildAilment` to `CatchContext`), Love, Moon (species evolves with `moon-stone`), Net, Lure, Dive, Heavy, Fast and Beast. Config `game.catching.night_starts_at` (18) and `night_ends_at` (6).
- Nest Ball: 1 + (`nest_ball_until_level` − wild level) ÷ 9, kept between 1 and `nest_ball_max`. Config `game.catching.nest_ball_max` (3) and `nest_ball_until_level` (20): level 2 → ×3, level 11 → ×2, level 20 and above → ×1.
- Level Ball: ×8 when the active Pokémon's level is at least 4 times the wild level, ×4 when at least double, ×2 when higher, ×1 otherwise.
- As built: a ball whose condition does not apply falls back to its `catch_rate` (1, or 0.1 for the Beast Ball). Timer Ball: 1 + 0.3 × (turn − 1), so turn 1 is ×1 and turn 11 reaches ×4. Love Ball never applies to genderless Pokémon. Moon Ball reads the wild species' `evolutions.evolutionItem`, so the caller eager-loads them. `CatchContext` takes `?Ailment $wildAilment` after `wildGender`. The night config is read by whoever builds the `CatchContext` (task 2.12), which sets `isNight`.

**Tests:** for each ball, one case where the condition applies and one where it does not; Nest Ball at levels 2, 11, 20 and 40; Level Ball at each threshold.

**Done when:** tests pass.

### 2.11 Add items to the inventory

- [ ] Done

**Goal:** a reusable way to give a player items (starter balls now, shop and missions later).

**Build:**
- `App\Inventory\Actions\AddInventoryItemAction::handle(User $user, EvolutionItem|Pokeball $item, int $quantity = 1): void`: creates or increases the stack; throws `App\Inventory\Exceptions\InventoryStackFullException` above `game.inventory.max_stack`.

**Tests:** new stack; existing stack grows (still one row); over the limit is refused; quantity below 1 is refused.

**Done when:** action and exception exist; tests pass.

### 2.12 Throw a ball

- [ ] Done

**Goal:** catching in a wild battle.

**Build:**
- A failed throw uses the turn like a switch: reuse the opponent's half of the turn from task 1.23 (the turn pipes, with a `TurnData` that has no `moveSlot`, so `TakeActions` skips the player).
- `CreatePokemonAction`: add `bool $joinPartyIfRoom = false`; when true, the Pokémon joins the party if it has fewer than `game.user.max_party_size` (new config, 6).
- `App\Battle\Actions\ThrowBallAction::handle(Battle $battle, Pokeball $pokeball): Battle`:
  - Refuses outside `ONGOING` wild battles or while awaiting a send-out (`App\Battle\Exceptions\NotAWildBattleException`, `BattleNotReadyForTurnException`).
  - Refuses with `BoxFullException` before using the ball when the Box is full.
  - Uses one ball (`RemoveInventoryItemAction`).
  - Caught (guaranteed or `RandomHelper::chance()` with the catch chance): creates the Pokémon (`joinPartyIfRoom: true`, the thrown ball), logs "Gotcha! … was caught!", dispatches `PokemonCaught`, and ends the battle as `CAUGHT` with `EndBattleAction` (XP as for a win, `BattleEnded` with `isWin`).
  - Not caught: logs "Oh no! The Pokémon broke free!", then the opponent acts (the turn pipes, as in task 1.23).

**Tests:** catch; break free then the opponent attacks; ball used either way; Master Ball always catches; joins party or Box; a catch pays XP like a win; refused when not wild, Box full or no ball.

**Done when:** tests pass.

### 2.13 Starter and free balls at registration

- [ ] Done

**Goal:** new players start with a Pokémon and balls.

**Build:**
- Config `game.registration`: `starters` (the 27 first-stage starters: Bulbasaur, Charmander, Squirtle … Sprigatito, Fuecoco, Quaxly, as slugs), `starter_level` (5), `starting_items` (`poke-ball` 20, `great-ball` 5).
- `RegisterUserAction::handle()`: add `Species $starter`; in one transaction creates the user, the starter (in the party, Poké Ball, random gender, not shiny) and the items (`AddInventoryItemAction`).
- `StoreRegistrationRequest`: required `starter` slug, must be in the starters list.

**Tests:** registration creates the user, one Pokémon in the party at level 5 and the two ball stacks; a starter outside the list is rejected.

**Done when:** tests pass.

### 2.14 Registration page: choose a starter

- [ ] Done

**Goal:** the starter picker on the registration form.

**Mock-up:** `docs/mockups/registration.html` (all states)

**Build:** the registration page shows the 27 starters' portraits grouped by region (one row of 3 per region), the selected one highlighted, sent as `starter`. Props come from `ShareAuthLayoutProps` or the registration page props, whichever the page already uses.

**UI suggestions** (ideas, not requirements):
- A `Field` labelled "Choose your starter" below the password fields.
- A `RadioGroup` styled as cards (accessible and keyboard-friendly): one small heading per region ("Kanto") and a row of 3 cards, each with `SpeciesPortrait`, name and type badge.
- The selected card gets a primary-coloured border and a check icon.
- On phones, 27 cards are a long scroll: consider a region `Select` that shows one region's 3 cards at a time.
- Validation error under the group with `FieldError`. "Create account" stays the only primary button.

**Tests:** feature test for the props; browser test: choose a starter and register.

**Done when:** a new player can choose a starter and lands on the Dashboard with it in their party.

### 2.15 Pokédex: game catch rate

- [ ] Done

**Goal:** the Pokédex shows this game's catch rate, not the main games'.

**Build:**
- Pokédex overview shows "10%" (or "1%" for legendaries and mythicals) from `game.catching`, or "Not found in the wild" when `wild_min_level` is null.
- Remove `catch_rate` from `006_create_species_table.php`, the seeder, model, `isCapturable()` and `getCatchRatePercentage()`.

**UI suggestions** (ideas, not requirements):
- Keep the existing `AppInfoCardRow` in the overview card: "Catch rate: 10%".
- For species never in the wild, show muted text "Not found in the wild", with a `Tooltip` explaining why (Megas and G-Max evolve, babies hatch).

**Tests:** update `PokedexShowTest` for the three cases.

**Done when:** tests pass.

### 2.16 Explore page

- [ ] Done

**Goal:** the list of areas.

**Mock-up:** `docs/mockups/explore.html` (all states)

**Build:**
- Route `GET /explore` named `exploration.web.index` → `App\Exploration\Http\Web\Controllers\ExploreController@index` with `ExploreIndexPageProps` (each area's slug, label, types).
- Page `resources/js/app/Exploration/pages/Explore/Index/index.vue`: one card per area (name, type badges, Explore button); 3 per row on desktop, 1 on phones. Until area art exists, the card background is a gradient of its two type colours.
- The sidebar already has a placeholder "Wild Areas" item pointing to `#`: rename it "Explore" and point it to `exploration.web.index`.

**UI suggestions** (ideas, not requirements):
- `AppPageHeader`: "Explore", with the description "Pick an area to look for wild Pokémon."
- A responsive grid: 1 column on phones, 2 on tablets, 3 on desktop.
- One `Card` per area, the whole card clickable: a coloured band at the top (the two type colours as a gradient) with the area name on it, `PokemonTypeBadges` in the content, and a full-width outline "Explore" `Button` in the footer (outline because it repeats on every card; the design guide keeps primary for one action per screen).
- Sidebar: the renamed "Explore" item keeps its current place near the top, since exploring is the main activity.

**Tests:** feature test for the props.

**Done when:** the page lists the 9 areas.

### 2.17 Area page: encounters

- [ ] Done

**Goal:** explore an area and see the wild Pokémon.

**Mock-up:** `docs/mockups/area.html` (states "Wild Pokémon found", "Shiny encounter", "Legendary or mythical encounter", "No encounter yet", "A battle is in progress")

**Build:**
- Route `GET /explore/{area}` named `exploration.web.show` → `ExploreController@show` with `ExploreShowPageProps`: the area, and the player's encounter if it is in this area (sprite, name, gender, level, types, shiny, legendary).
- Route `POST /explore/{area}` named `exploration.web.explore` → `ExploreAreaController`: calls `ExploreAction`, redirects to the area page. Used by both Explore and Keep exploring.
- The wild battle's result card (task 1.35) gets "Keep exploring" (primary, exploring the battle's area again); "Back to Dashboard" becomes the outline button before it.
- Route `POST /explore/battle` named `exploration.web.battle` → `BattleEncounterController`: calls `BattleEncounterAction`, redirects to `battle.web.show`.
- Encounter card as in the Spec 2 sketch; shiny and legendary encounters get a gold border and badge. While a battle is ongoing, a banner links to it instead.

**UI suggestions** (ideas, not requirements):
- `AppBreadcrumbs` (Explore › Forest), then the area name with its type badges and an outline "Pokémon found here" button on the right.
- A centred encounter `Card` (about 450px wide): large `SpeciesSprite`, name with `PokemonGenderIcon`, a level `Badge` and type badges.
- Shiny: a gold border and a "Shiny" `Badge` with a star icon. Legendary or mythical: a "Legendary" `Badge`.
- Buttons: primary "Battle" and outline "Keep exploring", side by side; stacked full width on phones. Both show a processing state while the request runs.
- No encounter yet: `AppEmptyState` ("Nothing here yet") with a primary "Explore" button.
- Ongoing battle: replace the card with a notice ("You're in a battle") and one primary "Return to battle" button (shadcn-vue `alert` or `AppInfoCard`).

**Tests:** feature tests for the three routes; browser test: explore, keep exploring, battle.

**Done when:** a player can explore an area and start a battle from the page.

### 2.18 Area page: Pokémon found here

- [ ] Done

**Goal:** the modal listing an area's species.

**Mock-up:** `docs/mockups/area.html` (states "Pokémon found here" open, loading and no matches)

**Build:** `ExploreShowPageProps` gets `areaSpecies` as an `Inertia::optional()` prop (portrait, name, types), loaded when the modal opens. The modal has a search box and type filters (client-side), using species portraits.

**UI suggestions** (ideas, not requirements):
- A `Dialog` with scrolling content, titled "Pokémon found in Forest" with the species count.
- At the top, a search `InputGroup` with a search icon, and type filters as toggle chips (`Badge`-styled buttons) for the types present in the list.
- A grid of `SpeciesPortrait` tiles with the name underneath: 5–6 per row on desktop, 3 on phones.
- `Skeleton` tiles while the optional prop loads; `AppEmptyState` ("No Pokémon match your search") when filters hide everything.
- On phones, consider making the dialog full screen.

**Tests:** feature test: the prop is absent by default and returned when requested.

**Done when:** the modal lists the area's species with search and filters.

### 2.19 Battle page: throw a ball

- [ ] Done

**Goal:** catching from the battle page.

**Mock-up:** `docs/mockups/battle.html` (states "Wild: ball picker open", "Wild: caught" and "Wild: Pokémon fainted, send out")

**Build:**
- Route `POST /battle/throw-ball` named `battle.web.throw-ball` → `ThrowBallController` with `ThrowBallRequest` (`pokeball` slug, owned by the player).
- `BattleShowPageProps`: in wild battles, the player's balls (sprite, name, quantity, current multiplier from `BallMultiplierCalculator`, `isBoosted` when above its `catch_rate`).
- "Throw ball" outline button in the More options card (task 1.32), before "Run", wild battles only; it opens the ball picker; boosted balls show a "Boosted" tag. Disabled while waiting for a send-out.
- A catch ends the battle with task 1.35's result card: "Gotcha! Pidgey was caught", where it went (Party or Box), the XP rows, a muted "A catch counts as winning: the same XP as knocking Pidgey out.", and the buttons "View Pidgey" (outline; to the Box until the Pokémon page exists) and "Keep exploring" (primary, back to the area page).

**UI suggestions** (ideas, not requirements):
- An outline "Throw ball" `Button` with a ball icon, sized to its text.
- Ball picker: a list that opens under the buttons on desktop (a bottom `Sheet` on phones) listing one row per ball: `ItemSprite`, name, "×42" owned, and the current multiplier ("×3.5"). Boosted balls get a green "Boosted" `Badge` and are sorted first.
- Clicking a row throws right away, with a processing state on the row.
- A ball that fails only adds a log line; no extra popup.

**Tests:** feature tests for the route and props; browser test: throw a ball and see the caught result card; Throw ball is disabled during a send-out.

**Done when:** a player can explore, battle, throw balls and catch a Pokémon entirely from the pages.

---

## 3. Spec 3 — Money, inventory and shop

Read [03-money-inventory-and-shop.md](03-money-inventory-and-shop.md) first. Money lives in a new domain, `src/App/Economy`; the shop in `src/App/Shop`; the inventory page in the existing `src/App/Inventory`. Using items on a Pokémon (TMs, boost stones, evolution items) comes with Spec 4; the XP Share switch comes with Spec 5.

### 3.1 Money

- [ ] Done

**Goal:** players have Pokédollars.

**Build:**
- In `009_create_users_table.php`: `unsignedInteger('money')->default(0)`.
- `User` model cast; `UserFactory` state `withMoney(int $money)`.
- `App\Economy\Actions\AddMoneyAction::handle(User $user, int $amount): void` and `SpendMoneyAction::handle(User $user, int $amount): void`. Spending more than the player has throws `App\Economy\Exceptions\NotEnoughMoneyException`. Amounts below 1 throw `InvalidArgumentException`. Both lock with a `LockKeys` method and refresh the user first.
- Config `game.economy.starting_money` (2000); `RegisterUserAction` (task 2.13) gives it.

**Tests:** add; spend; spending too much is refused and changes nothing; new players start with ₽ 2,000.

**Done when:** tests pass.

### 3.2 Show money in the layout

- [ ] Done

**Goal:** the player always sees their money.

**Mock-up:** `docs/mockups/layout.html` (all states)

**Build:** `HandleInertiaRequests` shares `auth.user.money`. The sidebar shows it.

**UI suggestions** (ideas, not requirements):
- A small row in the sidebar footer, above the user menu, linking to the Shop: a coin icon and "₽ 12,450" in `font-medium`, formatted with thousands separators (`Intl.NumberFormat`). With the sidebar collapsed, only the coin, with the amount in a tooltip.
- On phones, where the sidebar collapses, show it in the top bar next to the menu button.
- A shared component `MoneyAmount` (`shared/components/game/`) used everywhere money appears, so the format stays the same.

**Tests:** feature test: the shared prop holds the user's money.

**Done when:** money shows on every page.

### 3.3 Prices

- [ ] Done

**Goal:** every item the shop sells has a price; earned-only items have none.

**Build:**
- In `003_create_pokeballs_table.php` and `004_create_evolution_items_table.php`: nullable `unsignedInteger('price')` on both tables (null = never sold).
- Seed data: Poké Ball 200, Great Ball 600, Ultra Ball 1,200; conditional balls (Net, Dive, Nest, Repeat, Timer, Dusk, Quick, Dream, Lure, Level, Moon, Heavy, Fast, Love, Beast) 1,000; Master, Safari, Sport, Park, Luxury, Premier, Heal, Friend and Cherish null. Every evolution item (stones and items such as Metal Coat or King's Rock) 3,000, except Mega Stones, which are null.
- `App\Shop\Mechanics\TmPriceCalculator::price(Move $move): ?int`: null for Z-Moves and Dynamax moves; 3,000 for status moves (no power); 2,000 under 60 power; 5,000 for 60–89; 10,000 for 90+. Config `game.shop.tm_prices`.

**Tests:** calculator boundaries (59, 60, 89, 90, status, Z-Move); seeder test: Master Ball and Mega Stones have no price; every other evolution item costs 3,000.

**Done when:** tests pass.

### 3.4 TMs and special items in the inventory

- [ ] Done

**Goal:** the inventory can hold TMs, boost stones and key items.

**Build:**
- Migration `016_create_special_items_table.php`: `uuid id`, `slug` unique, `name`, `description`, nullable `sprite`, `category` enum.
- Enum `Shared\Enums\SpecialItemCategory`: `BOOST_STONE`, `KEY_ITEM`. Model `Shared\Models\SpecialItem` (with factory); `SpecialItemSeeder` seeds `boost-stone` and `xp-share`.
- `InventoryItemType`: add `TM` (item is a `Move`) and `SPECIAL_ITEM`; extend `fromModel()` and the morph map.
- `AddInventoryItemAction` and `RemoveInventoryItemAction` accept `EvolutionItem|Pokeball|Move|SpecialItem`.

**Tests:** add and remove a TM and a boost stone; `fromModel()` for each model.

**Done when:** tests pass.

### 3.5 Today's TMs

- [ ] Done

**Goal:** 10 TMs for sale each day, the same for every player.

**Build:**
- Migration `017_create_tm_rotations_table.php`: `uuid id`, `date` unique, `json move_ids`. Model `Shared\Models\TmRotation`.
- `App\Shop\Actions\GetTodaysTmRotationAction::handle(): TmRotation`: returns today's rotation (server date), creating it on the first request of the day with 10 random TMs that have a price (`RandomHelper`), under a lock so two requests can't create two.
- Config `game.shop.daily_tm_count` (10).

**Tests:** creates one rotation per day (second call returns the same one); 10 distinct moves; no Z-Moves or Dynamax moves; a new day gives a new rotation.

**Done when:** tests pass.

### 3.6 Buy items

- [ ] Done

**Goal:** spend money on shop items.

**Build:**
- `App\Shop\Actions\BuyItemAction::handle(User $user, Pokeball|EvolutionItem|Move $item, int $quantity): void`
  - The item must be for sale: a price, and for TMs, in today's rotation. Otherwise `App\Shop\Exceptions\ItemNotForSaleException`.
  - The stack must have room (`InventoryStackFullException`) and the player enough money (`NotEnoughMoneyException`), both checked before anything changes.
  - In one transaction: `SpendMoneyAction`, then `AddInventoryItemAction`.

**Tests:** buy one and many; not for sale; TM not in today's rotation; not enough money; stack full; nothing changes when refused.

**Done when:** tests pass.

### 3.7 Sell items

- [ ] Done

**Goal:** sell shop items back for half their price.

**Build:**
- `App\Shop\Actions\SellItemAction::handle(User $user, Pokeball|EvolutionItem|Move $item, int $quantity): int` returns the money earned.
  - Earned-only items (no price) throw `App\Shop\Exceptions\ItemNotSellableException`.
  - Earns floor(price × `game.shop.sell_back_ratio` (0.5) × quantity). TMs use `TmPriceCalculator`, whether or not they are in today's rotation.
  - In one transaction: `RemoveInventoryItemAction`, then `AddMoneyAction`.

**Tests:** sell one and many; earned-only refused; selling more than owned refused (`InsufficientInventoryException`); money and inventory both change.

**Done when:** tests pass.

### 3.8 Incubators cost money

- [ ] Done

**Goal:** the first incubator is free, each next one costs ₽ 5,000 more.

**Mock-up:** `docs/mockups/nursery.html` (all states)

**Build:**
- `App\Pokemon\Mechanics\IncubatorPriceCalculator::price(int $purchasedCount): int` = purchasedCount × `game.incubation.price_step` (5,000).
- `PurchaseAction` (incubator) spends the price with `SpendMoneyAction` inside its lock, before marking it purchased.
- `NurseryPageProps` includes the price of the next incubator.

**UI suggestions** (ideas, not requirements):
- On `IncubatorNotPurchasedCard`, show the price under the button ("₽ 15,000") with `MoneyAmount`; the first one says "Free".
- When the player can't afford it, disable the button and say why ("You need ₽ 2,550 more").

**Tests:** first is free; second costs 5,000; third costs 10,000; not enough money refused; Nursery props include the price.

**Done when:** tests pass.

### 3.9 Shop page: browse

- [ ] Done

**Goal:** see what the shop sells.

**Mock-up:** `docs/mockups/shop.html` (states "Balls tab", "Evolution items tab", "TMs tab (today's 10)")

**Build:**
- Route `GET /shop` named `shop.web.index` → `App\Shop\Http\Web\Controllers\ShopController@index` with `ShopIndexPageProps`: three groups (balls, evolution items, today's TMs), each item with name, sprite, short effect, price and how many the player owns; for TMs also type, power and when the rotation ends.
- Page `resources/js/app/Shop/pages/Shop/Index/index.vue`; "Shop" link in the navigation.

**UI suggestions** (ideas, not requirements):
- `AppPageHeader` ("Shop") with the player's money on the right.
- Tabs for Balls, Evolution items and TMs (shadcn-vue `tabs`, added with the CLI); the chosen tab in the URL (`?tab=tms`) so it survives a reload.
- A list, not a grid: one row per item with `ItemSprite`, name, a one-line effect in muted text, price with `MoneyAmount`, and "You own: 42".
- TMs tab: a line on top with `AppCountdown` ("New TMs in 07:42:10"); each row shows the move's type badge and power.
- On phones, each row stacks: name and effect on top, price and buy controls below.

**Tests:** feature test for the props, including that only today's TMs appear and earned-only items do not.

**Done when:** the page lists the shop's items.

### 3.10 Shop page: buy

- [ ] Done

**Goal:** buy from the shop page.

**Mock-up:** `docs/mockups/shop.html` (states "Confirm a purchase over ₽ 10,000", "Can't afford", "Purchase complete")

**Build:**
- Route `POST /shop/buy` named `shop.web.buy` → `BuyItemController` with `BuyItemRequest` (`item_type`, `item_id`, `quantity` 1–9,999).
- Each row gets a quantity picker and a Buy button; purchases over ₽ 10,000 ask for confirmation first; success shows a toast.

**UI suggestions** (ideas, not requirements):
- Quantity: an `InputGroup` with − and + buttons around a number input, default 1.
- "Buy" is an outline `Button` on each row (many rows, so not primary); its label shows the total ("Buy · ₽ 2,400").
- When the player can't afford the total or the stack would overflow, disable Buy and show the reason in a `Tooltip` or small text under it.
- Confirmation over ₽ 10,000: a `Dialog` "Buy 10 Ultra Balls for ₽ 12,000?" with "Buy" (primary) and "Not now" (outline).
- Success toast: "Purchase complete" / "You bought 10 Ultra Balls."

**Tests:** feature tests: buy succeeds; validation errors; not enough money shows an error; browser test: buy with confirmation.

**Done when:** a player can buy items from the page.

### 3.11 Inventory page: browse

- [ ] Done

**Goal:** see everything the player owns.

**Mock-up:** `docs/mockups/inventory.html` (the five tab states, "Empty tab", "Item details: earned only")

**Build:**
- Route `GET /inventory` named `inventory.web.index` → `App\Inventory\Http\Web\Controllers\InventoryController@index` with `InventoryIndexPageProps`: owned items grouped by Balls, Evolution items, TMs, Boost stones and Key items, each with name, sprite, quantity, description, sell price (null when earned-only).
- Page `resources/js/app/Inventory/pages/Inventory/Index/index.vue`; "Inventory" link in the navigation.

**UI suggestions** (ideas, not requirements):
- `AppPageHeader` ("Inventory") with money on the right; tabs for the five groups, the tab in the URL.
- A grid of item tiles (auto-fill, about 8rem wide): `ItemSprite`, name, and a quantity `Badge` ("×42") in the corner. TMs without a sprite show a disc icon coloured by the move's type.
- Clicking a tile opens a `Dialog` (a `Sheet` on phones) with the sprite, name, description, quantity and the actions (Sell now; Use comes with Spec 4).
- Empty tab: `AppEmptyState` saying where to get that kind of item, with a link to the Shop where it applies ("No TMs yet. Buy them in the Shop or earn them from missions.").

**Tests:** feature test for the props per group; empty groups.

**Done when:** the page lists the player's items.

### 3.12 Inventory page: sell

- [ ] Done

**Goal:** sell items from the inventory.

**Mock-up:** `docs/mockups/inventory.html` (states "Item details: can be sold", "Confirm selling", "Sold")

**Build:**
- Route `POST /inventory/sell` named `inventory.web.sell` → `SellItemController` with `SellItemRequest` (`item_type`, `item_id`, `quantity`).
- The item dialog gets a quantity picker and a Sell button; earned-only items show why they can't be sold instead.

**UI suggestions** (ideas, not requirements):
- In the item dialog: the same − / + quantity picker as the shop, the money it earns ("Sell 5 for ₽ 2,500"), and an outline "Sell" button.
- Selling always confirms in a small step inside the dialog (selling loses the item), with "Sell" as a destructive button.
- Earned-only items: muted text "Can't be sold to the shop. Trade it instead." and no Sell button.
- Success toast: "Sold" / "You sold 5 Ultra Balls for ₽ 3,000."

**Tests:** feature tests: sell succeeds; earned-only refused; quantity above owned refused; browser test: sell from the dialog.

**Done when:** a player can sell items from the page.

---

## 4. Spec 4 — Pokémon page

Read [04-pokemon-page.md](04-pokemon-page.md) first. Builds on Spec 1 (`SetActiveMoveAction`, `EvolutionRequirementChecker`, `EvolvePokemonAction`) and Spec 3 (TMs, boost stones and evolution items in the inventory). Everything lives in `src/App/Pokemon`.

### 4.1 Lock a Pokémon

- [ ] Done

**Goal:** a locked Pokémon can't be released (or traded, Spec 7) by accident.

**Build:**
- In `010_create_pokemon_table.php`: `boolean('is_locked')->default(false)`.
- `Pokemon` cast; `PokemonFactory` state `locked()`.
- `App\Pokemon\Actions\SetPokemonLockAction::handle(Pokemon $pokemon, bool $isLocked): void`.

**Tests:** lock and unlock.

**Done when:** tests pass.

### 4.2 Add to and remove from the party

- [ ] Done

**Goal:** party changes from the Pokémon page.

**Build:**
- `App\Pokemon\Actions\AddToPartyAction::handle(Pokemon $pokemon): void`: throws `App\Pokemon\Exceptions\PartyFullException` at `game.user.max_party_size` (6).
- `App\Pokemon\Actions\RemoveFromPartyAction::handle(Pokemon $pokemon): void`: throws `App\Pokemon\Exceptions\PartyCannotBeEmptyException` for the last party Pokémon, since battles need a party.
- `App\Pokemon\Actions\SwapPartyPokemonAction::handle(Pokemon $joining, Pokemon $leaving): void`: `$leaving` must be in the party and `$joining` must not; in one transaction `$leaving` goes to the Box and `$joining` takes its place.
- Both refuse during an ongoing battle (`BattleAlreadyInProgressException`): the battle uses a snapshot of the party.

**Tests:** add; party full; remove; last one refused; swap (party size unchanged); swap with a Pokémon outside the party refused; refused during a battle.

**Done when:** tests pass.

### 4.3 Release a Pokémon

- [ ] Done

**Goal:** release is permanent and gives nothing back.

**Build:**
- `App\Pokemon\Actions\ReleasePokemonAction::handle(Pokemon $pokemon): void`: deletes the Pokémon (its known moves go with it).
  - Refuses locked Pokémon (`App\Pokemon\Exceptions\PokemonLockedException`), the last party Pokémon (`PartyCannotBeEmptyException`) and any release during an ongoing battle.

**Tests:** released (row gone, moves gone); locked refused; last party Pokémon refused; refused during a battle.

**Done when:** tests pass.

### 4.4 Teach a TM

- [ ] Done

**Goal:** a TM adds a move to the known-moves pool and is used up.

**Build:**
- `App\Pokemon\Actions\TeachTmAction::handle(Pokemon $pokemon, Move $move): void`
  - The owner must have the TM (`RemoveInventoryItemAction` throws `InsufficientInventoryException`).
  - Already known: `App\Pokemon\Exceptions\MoveAlreadyKnownException`, TM kept.
  - Z-Move and Dynamax TMs need level 100: `App\Pokemon\Exceptions\TmRequirementNotMetException`, TM kept.
  - The move joins the pool. If an active slot is empty, it fills the first empty slot; otherwise it waits in the pool.

**Tests:** taught and TM used up; fills an empty slot; goes to the pool when slots are full; already known; Z-Move below level 100; TM not owned. Nothing changes when refused.

**Done when:** tests pass.

### 4.5 Boost stone odds

- [ ] Done

**Goal:** the chance a boost stone fails, and the roll.

**Build:**
- `App\Pokemon\Mechanics\BoostStoneCalculator::failureChance(int $currentBoost): float`: a percentage; 0 below +20; from +20, (currentBoost − 19) × `failure_step`, so +20 → +21 fails 1% and +99 → +100 fails 80%. Config `game.boost.safe_until` (20), `game.boost.failure_step` (1).
- `App\Pokemon\Mechanics\BoostStoneRoller::roll(int $currentBoost): bool` (true = success), with `RandomHelper`.

**Tests:** chances at +0, +19, +20, +60, +99; roller with `RandomHelper::fake()`.

**Done when:** tests pass.

### 4.6 Use boost stones

- [ ] Done

**Goal:** use one or several boost stones in one go.

**Build:**
- Value object `App\Pokemon\ValueObjects\BoostResult`: `int $stonesUsed`, `int $successes`, `int $failures`, `int $boostBefore`, `int $boostAfter`.
- `App\Pokemon\Actions\UseBoostStonesAction::handle(Pokemon $pokemon, int $maxStones, ?int $targetBoost = null): BoostResult`
  - Uses stones one at a time until `$maxStones` are used, `$targetBoost` is reached, the Pokémon is at +100, or the player runs out.
  - Refuses at +100 (`App\Pokemon\Exceptions\MaxBoostReachedException`) and with no stones (`InsufficientInventoryException`).
  - One transaction and a lock; each stone is removed from the inventory whether it succeeds or fails.

**Tests:** one stone; stops at target; stops at max stones; stops at +100; runs out of stones; failures still use stones (faked rolls).

**Done when:** tests pass.

### 4.7 Pokémon page: route and props

- [ ] Done

**Goal:** the data for the page.

**Build:**
- Route `GET /your-pokemon/{pokemon}` named `pokemon.web.show` → `App\Pokemon\Http\Web\Controllers\PokemonController@show`. Only the owner may open it (404 for anyone else).
- `App\Pokemon\Http\Web\PageProps\PokemonShowPageProps`:
  - Header: species, types, gender, shiny, level, boost, XP progress to the next level, ball, catch date, original trainer, `is_locked`, `in_party`, party size.
  - Stats: HP, Attack and Defense from `getStats()`, each with its parts (level, boost, shiny) for the hover breakdown.
  - Moves: active moves by slot and the known-moves pool (name, type, power, category).
  - Evolution options: each `SpeciesEvolution` of the species with the target species and `EvolutionRequirementChecker::check()`'s unmet `EvolutionRequirement` (null = allowed). The disabled rows show `EvolutionRequirement::reason($evolution)` ("Needs a Water Stone"), the same wording `EvolutionNotAllowedException` uses.
  - Items: owned TMs (with "already known" flags) for Teach TM, boost stones (count, current failure chance) for Boost, and for each evolution option whether the player owns the item it needs.
- Box card "View" and the Party list link to the page.

**Tests:** feature test for the props; another user's Pokémon gives 404; guest redirected to login.

**Done when:** the page loads with all props.

### 4.8 Pokémon page: header and stats

- [ ] Done

**Goal:** the read-only part of the page.

**Mock-up:** `docs/mockups/pokemon.html` (state "Overview")

**Build:** page `resources/js/app/Pokemon/pages/Pokemon/Show/index.vue` with the header (portrait, name, badges, XP bar, catch details), the action buttons (wired in later tasks) and the stats card.

**UI suggestions** (ideas, not requirements):
- `AppBreadcrumbs` (Box › Shiny Charmeleon), then a header `Card`: large `SpeciesSprite` on the left, details on the right; stacked on phones.
- Badges for level, boost and shiny (gold); `PokemonTypeBadges`; `PokemonGenderIcon`.
- Catch details: "Caught in a [sprite] Ultra Ball · 3 Nov 2026", with the ball's `ItemSprite` right before the ball's name, the same way the Pokédex's Evolution Chain card shows an evolution item.
- XP: a thin `Progress` with "720 / 1,000 XP to Lv 37"; at level 100, "Max level" instead.
- Stats: an `AppInfoCard` with one `AppInfoCardRow` per stat; a `Tooltip` on each value lists its parts ("Level 36: ×0.66 · Boost +14: ×1.14 · Shiny: ×1.25").
- Action row: "Evolve" (primary, only when an evolution is allowed), "Boost" (outline, opens the boost stones dialog), party toggle (outline), lock toggle (ghost with a lock icon), and "Release" (destructive outline, at the end).

**Tests:** browser test: the page shows the Pokémon's name, level, boost and stats.

**Done when:** the page matches the mock-up's overview.

### 4.9 Pokémon page: moves

- [ ] Done

**Goal:** swap active moves and teach TMs from the page.

**Mock-up:** `docs/mockups/pokemon.html` (states "Overview", "Swap a move", "Teach a TM")

**Build:**
- Route `POST /your-pokemon/{pokemon}/active-moves` named `pokemon.web.set-active-move` → `SetActiveMoveController` (`move_id`, `slot` 1–4).
- Route `POST /your-pokemon/{pokemon}/teach-tm` named `pokemon.web.teach-tm` → `TeachTmController` (`move_id`).
- `App\Pokemon\Actions\ReorderActiveMovesAction::handle(Pokemon $pokemon, array $moveIds): void`: `$moveIds` must be exactly the current active moves in their new order (otherwise `InvalidArgumentException`); saves slots 1, 2, 3… in that order. Route `POST /your-pokemon/{pokemon}/active-moves/order` named `pokemon.web.reorder-active-moves` → `ReorderActiveMovesController` (`move_ids` array).
- The slot order is the order of the move buttons on the battle page (task 1.19).
- Moves card: four slots with Swap buttons; the known-moves pool below; a "Teach TM" button.

**UI suggestions** (ideas, not requirements):
- Active slots: a numbered list (1–4, the battle order). Each row: a drag handle, the number, `MoveNameLabel`, type badge, the power ("—" for status moves), a "STAB" badge for damaging moves of the Pokémon's own type, up and down arrow buttons, and a ghost "Swap" button. An empty slot shows a dashed outline and "Empty slot".
- Drag and drop (desktop): drag a slot by its handle to reorder; drag a known move onto a slot to replace it. Every drag action also has a non-drag way: the arrows reorder, Swap replaces. Keyboard and touch users rely on those.
- Drag and drop needs a library for touch support and smooth sorting (for example `vue-draggable-plus`, built on SortableJS). Adding it needs approval (AGENTS.md: no new dependencies without approval); without it, use the native HTML drag events on desktop and the arrow buttons everywhere.
- Swap: a `Dialog` listing the known moves (the active ones marked "In slot 2"), with a search input when the pool is long. Picking one swaps it in.
- Known moves: a grid of small cards (name, type badge, power, STAB), sorted by name; moves already active are dimmed with "In slot 2". When the pool grows past about 12 moves, add the same search and type chips as the Teach TM dialog.
- Teach TM: a `Dialog` with a search input (name or number; players will own hundreds of TMs) and type filter chips (only types the player owns TMs of, plus "All types"), over a scrolling list of owned TMs sorted by code. A wide dialog (about 720px) with the TMs in two columns on desktop, one on phones. Each option shows "TM 395 Ice Beam", its type badge, power and how many are owned; TMs the Pokémon already knows are disabled with "Already known"; Z-Move and Dynamax TMs show "Needs level 100" when not allowed. Teaching asks for a confirmation step inside the dialog, since the TM is used up.

**Tests:** `ReorderActiveMovesActionTest` (new order saved; a list that is not exactly the active moves is refused); feature tests for the three routes and their errors; browser test: swap a move and reorder with the arrows.

**Done when:** a player can swap moves and teach TMs from the page.

### 4.10 Pokémon page: boost stones

- [ ] Done

**Goal:** use boost stones from the page.

**Mock-up:** `docs/mockups/pokemon.html` (states "Use boost stones", "Boost stones result"; the "Boost" button in "Overview")

**Build:**
- Route `POST /your-pokemon/{pokemon}/boost` named `pokemon.web.boost` → `UseBoostStonesController` (`max_stones` ≥ 1, optional `target_boost` up to 100).
- Dialog opened by the "Boost" button in the action row. Evolution items and Mega Stones are used through Evolve, TMs through Teach TM, so the page needs no general "Use item" button.

**UI suggestions** (ideas, not requirements):
- The dialog shows the current boost, stones owned and the chance for the next stone ("+60 → +61: 40% chance to fail"), with the chance coloured green (0%), yellow (1–39%) or red (40%+).
- Two modes as a `RadioGroup`: "Use up to [ 20 ] stones" and "Use until +[ 70 ]".
- Primary "Use stones" button.
- Result: a summary in the same dialog: "Used 20 stones: 13 worked, 7 failed. Boost +60 → +73." with the new boost in a large number.

**Tests:** feature test for the route and its errors; browser test: use stones and see the result.

**Done when:** a player can boost a Pokémon from the page.

### 4.11 Pokémon page: evolve

- [ ] Done

**Goal:** evolve from the page.

**Mock-up:** `docs/mockups/pokemon.html` (states "Evolution options", "Confirm evolution")

**Build:**
- Route `POST /your-pokemon/{pokemon}/evolve` named `pokemon.web.evolve` → `EvolvePokemonController` (`species_evolution_id`).
- The Evolve button shows only when at least one option is allowed; it opens the options dialog.
- After evolving, the page reloads with the new species and a toast.

**UI suggestions** (ideas, not requirements):
- Options: one row per evolution with the target's `SpeciesPortrait`, name, types and requirement. Allowed rows are selectable; others are disabled with the reason ("Needs a Water Stone", "Needs level 100").
- Confirmation inside the dialog: "Evolve Shiny Charmeleon into Shiny Charizard? This can't be undone." (display names: "Charmeleon into Charizard" for a regular one) Mention the item used up when there is one ("Uses 1 Charizardite X").
- Toast: "Evolved!" / "Your Shiny Charmeleon evolved into Shiny Charizard."

**Tests:** feature test for the route; browser test: evolve and see the new species.

**Done when:** a player can evolve from the page.

### 4.12 Pokémon page: party, lock and release

- [ ] Done

**Goal:** the remaining actions.

**Mock-up:** `docs/mockups/pokemon.html` (states "Overview", "Remove from party", "Not in party", "Added to party", "Not in party, party full", "Party full: choose who to swap", "Confirm locking", "Locked", "Release", "Release a shiny or boosted Pokémon")

**Build:**
- Routes: `POST /your-pokemon/{pokemon}/party` (`pokemon.web.party.add`), `DELETE /your-pokemon/{pokemon}/party` (`pokemon.web.party.remove`), `POST /your-pokemon/{pokemon}/party/swap` (`pokemon.web.party.swap`, `leaving_pokemon_id`), `POST /your-pokemon/{pokemon}/lock` (`pokemon.web.lock`, `is_locked` boolean), `DELETE /your-pokemon/{pokemon}` (`pokemon.web.release`, redirects to the Box with a toast).
- Locking asks for confirmation and explains that a locked Pokémon can't be released or traded; unlocking does not ask.
- Release asks the player to type the Pokémon's display name to confirm ("Shiny Charmeleon" for a shiny one).

**UI suggestions** (ideas, not requirements):
- In the party: an outline "Remove from party" button that opens a confirmation ("Remove Shiny Charmeleon from your party? It goes to your Box. You can add it back any time." with "Keep in party" and "Remove"). The last party Pokémon can't be removed: disabled with a tooltip.
- Not in the party: an outline "Add to party" button that adds it right away with a toast. When the party is full it opens "Your party is full": the six party Pokémon to pick from, and a primary "Swap with Dragonite" button.
- The swap dialog is a component in `resources/js/app/Pokemon/components/` (`PartySwapDialog`), because the Box uses it too (task 4.14).
- Lock: a ghost button with a lock icon and "Locked" / "Unlocked". Locking asks for confirmation in a dialog: "Lock Shiny Charmeleon?" / "A locked Pokémon can't be released or traded until you unlock it. You can still battle with it, train it and use items on it." with "Not now" (outline) and "Lock" (primary). Unlocking needs no confirmation. When locked, Release is disabled with a tooltip "Unlock it first".
- Release dialog: destructive styling, the input "Type Shiny Charmeleon to confirm" (the display name), the destructive "Release" button enabled only when the text matches. Shiny or boosted Pokémon add a warning block: "This Pokémon is shiny and has +14 boost. Boost stones used on it are lost."

**Tests:** feature tests for each route and its errors; browser test: release with the typed confirmation.

**Done when:** every Spec 4 action works from the page.

### 4.13 Use items from the inventory

- [ ] Done

**Goal:** the "Use" button on the inventory's item dialog (Spec 3) leads to the Pokémon page's own dialogs.

**Mock-up:** `docs/mockups/inventory.html` (state "Use: choose a Pokémon")

**Build:**
- Inventory item dialog: a "Use" button for TMs, boost stones, evolution items and Mega Stones. It opens a "Choose a Pokémon" dialog; after picking, that Pokémon's page opens with the matching dialog: Teach TM, Boost, or the evolution options.
- In "Choose a Pokémon", Pokémon the item does nothing for are disabled with the reason ("Already knows Ice Beam", "No evolution uses this item", "Already at +100").

**UI suggestions** (ideas, not requirements):
- "Choose a Pokémon": a searchable list of the player's Pokémon (party first) with `SpeciesPortrait`, name, level and boost; for TMs, Pokémon that already know the move are disabled.

**Tests:** feature test: the inventory props say which items can be used; browser test: teach a TM starting from the inventory.

**Done when:** every usable item can be used from either page.

### 4.14 Box: add to party from a card

- [ ] Done

**Goal:** add a Pokémon to the party straight from the Box.

**Mock-up:** `docs/mockups/pokemon.html` (state "Party full: choose who to swap"; the same dialog)

**Build:**
- The Box card's menu (today "View" and "Pokédex") gets "Add to party" for Pokémon not in the party, and "Remove from party" for those in it, using the routes from task 4.12.
- "Add to party" with a full party opens `PartySwapDialog`; "Remove from party" opens the same confirmation as the Pokémon page.
- The card shows an "In party" badge.

**Tests:** browser test: add from the Box, and swap when the party is full.

**Done when:** party changes work from the Box.

### 4.15 Party order

- [ ] Done

**Goal:** the player chooses the party's order; battles that use the party send out the first one.

**Build:**
- In `010_create_pokemon_table.php`: nullable `unsignedTinyInteger('party_position')` (1–6; null in the Box).
- Everything that puts a Pokémon in the party gives it the next position: `AddToPartyAction` (task 4.2), `CreatePokemonAction`'s `joinPartyIfRoom` (task 2.12) and the starter (task 2.13). `SwapPartyPokemonAction` gives the joining Pokémon the leaving one's position. `RemoveFromPartyAction`, `ReleasePokemonAction` and listing for trade (task 7.4) close the gap, so positions stay 1, 2, 3… with no holes.
- The `inParty()` scope orders by `party_position`; `PartyPageProps`, `StartWildBattleAction` (task 1.12) and every later battle that uses the party follow it.
- `App\Pokemon\Actions\ReorderPartyAction::handle(User $user, array $pokemonIds): void`: `$pokemonIds` must be exactly the party's Pokémon (otherwise `InvalidArgumentException`); saves positions 1, 2, 3… in that order. Refused during an ongoing battle (`BattleAlreadyInProgressException`): the battle uses a snapshot of the party.

**Tests:** a new party member joins at the end; removing one closes the gap; a swap keeps the position; reorder saves the new order; a list that is not exactly the party is refused; refused during a battle; a wild battle starts with the first party Pokémon.

**Done when:** tests pass.

### 4.16 Party page: reorder

- [ ] Done

**Goal:** reorder the party from the existing Party page.

**Mock-up:** `docs/mockups/party.html` (all states)

**Build:**
- Route `POST /your-pokemon/party/order` named `pokemon.web.party.reorder` → `ReorderPartyController` (`pokemon_ids`).
- `PartyPageProps`: each party Pokémon also gets its portrait, display name, level, boost, types and a link to its page (task 4.7); plus whether a battle is ongoing.
- The Party page (`resources/js/app/Pokemon/pages/Party/`) keeps its card of 6 slots, now numbered in sending-out order, with the first marked "Leads". Each change is saved right away.
- While a battle is ongoing, reordering is disabled and a notice links to the battle.

**UI suggestions** (ideas, not requirements):
- Keep today's square slots and `AppPageHeader` ("Your Party"), with the description "The first Pokémon leads every battle. Drag to change the order."
- Each slot: a number badge in the corner (1–6), `SpeciesPortrait`, `PokemonDisplayName`, "Lv 54 · +72", type badges, and two small ghost icon buttons, "Move earlier" and "Move later" (arrow left and right; up and down when the slots stack on phones). The whole slot links to the Pokémon page; the buttons don't.
- Drag and drop on desktop: drag a slot onto another place, the others shift. Use the same approach as the move reorder in task 4.9 (native drag events, or `vue-draggable-plus` if it was approved there). The arrow buttons are the keyboard and touch way.
- A short "Saved" toast is too noisy for every move: show a small "Saving…" / "Saved" text under the header instead, and a toast only on errors.
- Empty slots stay dashed with a plus icon and link to the Box ("Add from your Box").

**Tests:** feature tests for the route (saved; a list that is not exactly the party is refused; refused during a battle) and the props; browser test: move a Pokémon earlier with the arrows and see the new order after a reload.

**Done when:** a player can reorder the party from the Party page, and the next battle starts with the new first Pokémon.

---

## 5. Spec 5 — Missions

Read [05-missions.md](05-missions.md) first, including its "Starting content" and "Counting rules". Missions live in a new domain, `src/App/Missions`, and learn about the game only through events (task 0.4): they never call other domains, and other domains never call them. Goals whose feature is not built yet (trainers, gyms, the Battle Tower, trades) get their events when that feature is built.

### 5.1 Mission data

- [ ] Done

**Goal:** store achievements and each player's missions.

**Build:**
- Enums in `Shared\Enums`: `MissionKind` (`DAILY`, `ACHIEVEMENT`, `STREAK_BONUS`), `MissionDifficulty` (`EASY`, `MEDIUM`, `HARD`), `MissionCategory` (`CATCHING`, `BATTLING`, `COLLECTION`), `MissionGoal` (`CATCH_POKEMON`, `CATCH_POKEMON_OF_TYPE`, `CATCH_POKEMON_IN_AREA`, `CATCH_SHINY`, `CATCH_LEGENDARY`, `CATCH_POKEMON_AT_LEVEL`, `WIN_BATTLES`, `WIN_WILD_BATTLES`, `WIN_TRAINER_BATTLES`, `BEAT_GYM`, `EARN_REGION_BADGES`, `BECOME_CHAMPION`, `CLIMB_TOWER_FLOORS`, `HATCH_EGGS`, `EVOLVE_POKEMON`, `MEGA_EVOLVE`, `REACH_LEVEL`, `REACH_BOOST`, `TEACH_TMS`, `SPEND_MONEY`, `MAKE_TRADES`), each with `label()`.
- Value object `Shared\ValueObjects\MissionReward`: `int $money`, `array $items` (each: item type, slug, quantity, or `random` with a pool such as `normal-tm`, `mega-stone`, `z-move-tm`, `dynamax-tm`). Cast `AsMissionReward`.
- Migration `018_create_achievements_table.php`: `uuid id`, `slug` unique, `name`, `category` enum, `goal` enum, `json goal_parameters`, `target`, `json reward`, `sort_order`. Model `Shared\Models\Achievement`; `AchievementSeeder` from `database/seeders/files/achievements.json` (the Spec 5 list; "Beat all gyms" is `BEAT_GYM` with target 72, since only first wins count).
- Migration `019_create_user_missions_table.php`: `uuid id`, `foreignIdFor(User)`, `kind` enum, nullable `foreignIdFor(Achievement)`, nullable `date` (dailies), nullable `difficulty`, `goal`, `json goal_parameters`, `target`, `progress` default 0, `json reward`, nullable `completed_at`, nullable `claimed_at`, timestamps; unique `['user_id', 'achievement_id']`; index `['user_id', 'date']`. Model `Shared\Models\UserMission` (with factory): `isCompleted()`, `isClaimable()`.
- In `009_create_users_table.php`: `mission_streak` (default 0), nullable `streak_last_completed_on` date, nullable `last_reroll_on` date, `is_xp_share_enabled` (default false).

**Tests:** achievements seed from the file; casts round-trip; `isClaimable()` only when completed and not claimed.

**Done when:** tables, models, enums and seed data exist; tests pass.

### 5.2 Give rewards

- [ ] Done

**Goal:** one way to hand out money and items, used by missions and later by other features.

**Build:**
- `App\Missions\Mechanics\RewardRoller::roll(MissionReward $reward): MissionReward`: replaces each `random` item with a concrete one from its pool (`RandomHelper::pick`).
- `App\Missions\Actions\GrantRewardAction::handle(User $user, MissionReward $reward, bool $acceptOverflow = false): void`
  - Gives money (`AddMoneyAction`) and items (`AddInventoryItemAction`) in one transaction.
  - When an item would go over the stack limit, throws `App\Missions\Exceptions\RewardOverflowException` listing what wouldn't fit, unless `$acceptOverflow` is true; then it fills the stack and drops the rest.

**Tests:** money and items given; random pools resolved (faked); overflow refused with the list; overflow accepted caps at the limit.

**Done when:** tests pass.

### 5.3 Daily mission templates

- [ ] Done

**Goal:** turn templates into three concrete daily missions.

**Build:**
- `config/missions.php`: the Spec 5 daily templates, each with difficulty, goal, target range, parameter choices (`type`: any `PokemonType`; `area`: any `Area`; `level`: range) and reward; plus `enabled` so templates for unbuilt features stay off.
- `App\Missions\Mechanics\DailyMissionRoller::roll(): array`: one enabled template per difficulty, with target and parameters rolled (`RandomHelper`), the description filled in ("Catch 4 Water Pokémon").

**Tests:** one mission per difficulty; values within their ranges; disabled templates never picked.

**Done when:** tests pass.

### 5.4 Today's missions

- [ ] Done

**Goal:** every player has 3 dailies for today, created when first needed.

**Build:**
- `App\Missions\Actions\EnsureDailyMissionsAction::handle(User $user): Collection`: returns today's 3 dailies (server date), creating them with `DailyMissionRoller` on the first call of the day, under a lock.
- Also creates the player's achievement rows the first time (one `UserMission` per `Achievement`), so progress can be stored.
- `REACH_LEVEL` and `REACH_BOOST` rows start with the player's current best, so they complete at once if already reached.

**Tests:** creates 3 dailies once per day; a new day creates 3 new ones; achievements created once; reach goals start completed when already reached.

**Done when:** tests pass.

### 5.5 Advance progress

- [ ] Done

**Goal:** events move missions forward.

**Build:**
- `App\Missions\Mechanics\MissionGoalChecker::matches(UserMission $mission, MissionGoal $goal, array $context): bool`: the goal is the same and the parameters fit (type in the Pokémon's types, the same area, level at least the target, shiny, legendary or mythical).
- `App\Missions\Actions\AdvanceMissionsAction::handle(User $user, MissionGoal $goal, array $context, int $amount = 1): void`: calls `EnsureDailyMissionsAction`, then adds `$amount` to every open matching mission (today's dailies and achievements), capping at `target` and setting `completed_at` when reached. For `REACH_LEVEL` and `REACH_BOOST`, progress is the highest value seen, not a sum.
- `PokemonCaught` (task 0.4) gains `source` (`WILD` or `EGG`), `speciesId`, `types`, `area`, `level`, `isShiny`, `isLegendary`.
- Listener `App\Missions\Listeners\AdvanceMissionsOnPokemonCaught`: wild catches advance the `CATCH_*` goals; egg catches advance `HATCH_EGGS`.

**Tests:** matching and non-matching parameters; caps at target; completed once; reach goals use the highest value; egg catches don't count as catches.

**Done when:** catching advances the right missions.

### 5.6 Events for the other goals

- [ ] Done

**Goal:** the rest of today's built features advance missions.

**Build:** events and listeners for: `BattleEnded` (from task 1.16; only when `isWin`, so `WON` and `CAUGHT`) → `WIN_BATTLES`, and `WIN_WILD_BATTLES` for wild battles, catches included; `PokemonEvolved` (with `isMega`, from task 1.18) → `EVOLVE_POKEMON`, `MEGA_EVOLVE`; `PokemonLevelledUp` (task 1.17) → `REACH_LEVEL`; `PokemonBoosted` (task 4.6) → `REACH_BOOST`; `TmTaught` (task 4.4) → `TEACH_TMS`; `MoneySpent` (task 3.1) → `SPEND_MONEY` with the amount. One listener per event in `App\Missions\Listeners`. Trainer, gym, tower and trade goals are wired in their own specs.

**Tests:** each event advances its goal (one test per listener); a catch counts as a wild win; losses and runs count for nothing.

**Done when:** tests pass.

### 5.7 Claim a reward

- [ ] Done

**Goal:** the Claim button.

**Build:**
- `App\Missions\Actions\ClaimMissionAction::handle(UserMission $mission, bool $acceptOverflow = false): MissionReward`: refuses when not claimable (`App\Missions\Exceptions\MissionNotClaimableException`); rolls the reward, grants it (`GrantRewardAction`), sets `claimed_at`, returns what was given. Locked so a double click can't claim twice.

**Tests:** claim gives the reward once; not completed refused; already claimed refused; overflow refused unless accepted.

**Done when:** tests pass.

### 5.8 Reroll a daily

- [ ] Done

**Goal:** one free reroll per day.

**Build:**
- `App\Missions\Actions\RerollDailyMissionAction::handle(UserMission $mission): UserMission`: only today's unclaimed, uncompleted dailies; only if `last_reroll_on` is not today (`App\Missions\Exceptions\RerollAlreadyUsedException`). Replaces it with a new mission of the same difficulty (a different template when possible) and sets `last_reroll_on`.

**Tests:** reroll replaces the mission; second reroll the same day refused; completed missions refused; works again the next day.

**Done when:** tests pass.

### 5.9 Streak and bonus

- [ ] Done

**Goal:** finishing all 3 dailies on consecutive days builds a streak; every 7 days gives a bonus boost stone.

**Build:**
- When the third daily of a day completes (inside `AdvanceMissionsAction`): if `streak_last_completed_on` was yesterday, `mission_streak` + 1, otherwise 1; set `streak_last_completed_on` to today.
- Every multiple of 7 (`game.missions.streak_bonus_every`) creates a `STREAK_BONUS` mission, already completed, with the bonus reward (`game.missions.streak_bonus_reward`: 1 boost stone), claimed like any mission.
- The streak shown to the player is 0 when `streak_last_completed_on` is older than yesterday.

**Tests:** streak grows on consecutive days; resets after a missed day; bonus created at 7 and 14; shown streak drops to 0 after a gap.

**Done when:** tests pass.

### 5.10 XP Share

- [ ] Done

**Goal:** the XP Share key item works.

**Build:**
- `App\Inventory\Actions\SetXpShareAction::handle(User $user, bool $isEnabled): void`: needs the `xp-share` item (`InsufficientInventoryException`).
- Task 1.17's XP award: when `is_xp_share_enabled`, party Pokémon that did not enter the field get `game.experience.xp_share_ratio` (0.5) × the XP they would get for the opponents knocked out (the formula with their own level, without the knockout bonus). Never in Battle Tower battles. The state keeps what each got (`ExperienceGain::is_from_experience_share`), so the end screen lists each of them with "via XP Share" (task 1.35).
- Route `POST /inventory/xp-share` named `inventory.web.xp-share` → `SetXpShareController` (`is_enabled`).

**Mock-up:** `docs/mockups/inventory.html` (states "Key items tab", "XP Share settings")

**UI suggestions** (ideas, not requirements):
- The XP Share tile in the Key items tab shows an "On" / "Off" badge; its dialog has a `Switch` (shadcn-vue `switch`) labelled "Share XP with the whole party", with one line explaining it.

**Tests:** switch on and off; refused without the item; XP with and without it; nothing extra in a Battle Tower battle.

**Done when:** tests pass.

### 5.11 Missions page: daily missions

- [ ] Done

**Goal:** see today's missions.

**Mock-up:** `docs/mockups/missions.html` (states "Daily missions", "All done for today")

**Build:**
- Route `GET /missions` named `missions.web.index` → `App\Missions\Http\Web\Controllers\MissionsController@index` (`?tab=daily|achievements`), with `MissionsIndexPageProps`: today's dailies (and any streak bonus), time until reset, streak, whether the reroll is used.
- Page `resources/js/app/Missions/pages/Missions/Index/index.vue`; "Missions" link in the navigation.

**UI suggestions** (ideas, not requirements):
- `AppPageHeader` ("Missions") with the reset countdown (`AppCountdown`) on the right.
- Tabs: Daily and Achievements, the tab in the URL.
- One `Card` per mission, stacked: a difficulty `Badge` (Easy, Medium, Hard), the goal, a `Progress` bar with "3 / 5", and the reward on the right (item sprites and `MoneyAmount`).
- A streak row under the list: seven dots for the current week toward the 7-day bonus, and "Streak: 4 days".
- When all three are claimed: `AppEmptyState` "All done for today" with the countdown to new missions.

**Tests:** feature test for the props.

**Done when:** the page shows today's missions.

### 5.12 Missions page: claim and reroll

- [ ] Done

**Goal:** the actions on the daily cards.

**Mock-up:** `docs/mockups/missions.html` (states "Ready to claim", "Claimed", "Reward won't fit", "Reroll used", "Streak bonus")

**Build:**
- Routes `POST /missions/{userMission}/claim` (`missions.web.claim`, optional `accept_overflow`) and `POST /missions/{userMission}/reroll` (`missions.web.reroll`), each only for the owner's missions.
- Claim shows a toast with what was given ("You got 3 boost stones."). A `RewardOverflowException` opens a warning dialog that lists what won't fit, with "Claim anyway" (sends `accept_overflow`) and "Not now".

**UI suggestions** (ideas, not requirements):
- "Claim" is the primary button, shown only on completed missions; the card gets a subtle green border.
- "Reroll" is a ghost button with a refresh icon on uncompleted dailies; after using it, all Reroll buttons are disabled with a tooltip "One reroll per day".
- Claimed missions are dimmed with "Claimed ✓".
- Streak bonus: a card at the top with a gold border, "7-day streak bonus", and Claim.

**Tests:** feature tests for both routes and their errors; browser test: claim and reroll.

**Done when:** a player can claim and reroll from the page.

### 5.13 Missions page: achievements

- [ ] Done

**Goal:** the Achievements tab.

**Mock-up:** `docs/mockups/missions.html` (state "Achievements")

**Build:** the achievements tab lists every achievement grouped by category, with progress and reward; finished-and-claimed ones go to the bottom of each group, dimmed. Claiming uses task 5.12's route.

**UI suggestions** (ideas, not requirements):
- One section per category (`AppSectionHeading`: Catching, Battling, Collection), with a count ("4 / 5").
- Compact rows instead of big cards: name, `Progress` with numbers ("37 / 100"), reward, and Claim when ready.

**Tests:** feature test for the grouped props.

**Done when:** the tab lists achievements and they can be claimed.

### 5.14 Something to claim: navigation dot

- [ ] Done

**Goal:** players notice rewards waiting.

**Mock-up:** `docs/mockups/layout.html` (states "Sidebar expanded", "Sidebar collapsed to icons")

**Build:** `HandleInertiaRequests` shares `missions.claimableCount` (today's dailies, streak bonuses and achievements ready to claim); the sidebar's "Missions" item shows a dot (count in a tooltip) when it is above 0.

**UI suggestions** (ideas, not requirements):
- A small primary-coloured dot after the "Missions" label; in the collapsed sidebar, on the icon's corner.

**Tests:** feature test: the shared count.

**Done when:** the dot shows when something is claimable.

---

## 6. Spec 6 — NPC trainers, Gyms and the Battle Tower

Read [06-trainers-gyms-and-battle-tower.md](06-trainers-gyms-and-battle-tower.md) first, including its "Details" section. Trainers, gyms and the Elite Four live in a new domain, `src/App/Trainers`; the Battle Tower in `src/App/BattleTower`. All of them start battles through the Spec 1 battle engine (`src/App/Battle`), which gets a few general additions here.

### 6.1 Trainers and their teams

- [ ] Done

**Goal:** store hand-made trainers, gym leaders, Elite Four members and Champions.

**Build:**
- Enums in `Shared\Enums`: `TrainerRole` (`TRAINER`, `GYM_LEADER`, `ELITE_FOUR`, `CHAMPION`), `TrainerCategory` (`TOWNSFOLK`, `OUTDOORS`, `WATER`, `FIGHTERS`, `MYSTICS`, `EXPERTS`, with `label()`; only for `TRAINER` role) and `Region` (`KANTO` … `PALDEA`, with `label()` and `generation()`).
- Migration `020_create_trainers_table.php`: `uuid id`, `slug` unique, `name`, `trainer_class` ("Hiker", "Gym Leader"), nullable `sprite`, `role` enum, nullable `category` enum, nullable `region` enum, nullable `badge_name`, nullable `gym_type` (`PokemonType`), `position` (gym number or Elite Four order), `payout` (0 for Elite Four members and Champions: the Champion's prize comes from config, task 6.10).
- Migration `021_create_trainer_pokemon_table.php`: `uuid id`, `foreignIdFor(Trainer)`, `foreignIdFor(Species)`, `level`, `boost`, `is_shiny`, `gender`, `json move_slugs`, `position`.
- Models `Shared\Models\Trainer` (`team()` ordered by position) and `TrainerPokemon`, with factories.
- `TrainerSeeder` from `database/seeders/files/trainers.json`.

**Tests:** seeder loads a trainer with its team in order.

**Done when:** tables, models and seeder exist; tests pass.

### 6.2 Trainer content (data, no code)

- [ ] Done

**Goal:** fill `trainers.json`.

**Build:** about 30 hand-made trainers (Lv 5–100, payouts ₽ 500–20,000; shinies only on the hardest), then for each of the 9 regions its 8 gym leaders (2 to 6 Pokémon each, depending on the leader), Elite Four and Champion with their main-game teams, at the levels and boosts in Spec 6's "Details" section. Moves: each Pokémon's base moves unless the main-game team suggests better ones that exist as moves here.

**Done when:** `migrate:fresh --seed` loads every trainer; a seeder test checks each region has 8 gym leaders, 4 Elite Four members and 1 Champion.

### 6.3 Payout

- [ ] Done

**Goal:** the money formula.

**Build:** `App\Trainers\Mechanics\TrainerPayoutCalculator::payout(int $fullPayout, float $trainerAverageLevel, float $fieldAverageLevel): int` = full payout × (trainer average ÷ field average), capped between 10% and 100%, rounded down. Config `game.trainers.min_payout_ratio` (0.1).

**Tests:** every row of the Spec 6 table, plus the 10% floor.

**Done when:** tests pass.

### 6.4 Battles against trainers

- [ ] Done

**Goal:** the battle engine handles NPC teams.

**Build:**
- `BattleState`: add nullable `trainer_id`, nullable `trainer_name` and `bool $heals_after` (default true; the Tower sets false).
- `BattleEnded`: add `?string $trainerId`, filled by `EndBattleAction` from the state's `trainer_id`.
- `App\Battle\Actions\StartTrainerBattleAction::handle(User $user, BattleType $type, array $opponentTeam, ?Trainer $trainer, ?array $playerPokemonIds = null): Battle`: player side from `$playerPokemonIds` (in that order) or the whole party in party order (task 4.15); opponent side from `$opponentTeam` (a list of `NewPokemon` plus boost and moves, or `TrainerPokemon` rows).
- Opponent AI: a random active move each turn (as wild battles); after a faint it sends out its next Pokémon automatically and the log says "Hiker Bob sent out Geodude!".
- `ThrowBallAction` already refuses non-wild battles (task 2.12).
- The battle ends through `EndBattleAction` like any other (task 1.16): `BattleEnded` carries the type and trainer, and the domain that started the battle reacts to it (tasks 6.5, 6.8, 6.10, 6.12).

**Tests:** opponent sends out the next Pokémon; win only when the whole opponent team faints; trainer name in the log.

**Done when:** tests pass.

### 6.5 Fight a trainer from the list

- [ ] Done

**Goal:** battle hand-made trainers and get paid.

**Build:**
- `App\Trainers\Actions\ChallengeTrainerAction::handle(User $user, Trainer $trainer): Battle`: only `TRAINER` role; starts a `TRAINER` battle with the party.
- Migration `022_create_trainer_victories_table.php`: `uuid id`, `foreignIdFor(User)`, `foreignIdFor(Trainer)`, `wins` (default 0), `first_won_at`; unique `['user_id', 'trainer_id']`. Model `TrainerVictory`.
- Listener `App\Trainers\Listeners\PayOutOnBattleEnded`: for won `TRAINER` and `GYM` battles only (Elite Four members pay nothing), pays `TrainerPayoutCalculator` money (using the player's Pokémon that entered the field) with `AddMoneyAction`, records the victory, and stores the amount in the battle log for the end screen ("You got ₽ 1,440 for winning!").
- Mission listeners from Spec 5 now advance `WIN_TRAINER_BATTLES` from `BattleEnded` (won `TRAINER` battles).

**Tests:** win pays the formula; a higher-level team gets less; victory counted; a loss or a run pays nothing.

**Done when:** tests pass.

### 6.6 Trainers page

- [ ] Done

**Goal:** the list of hand-made trainers.

**Mock-up:** `docs/mockups/trainers.html` (all states, including "Trainer's team")

**Build:**
- Route `GET /trainers` named `trainers.web.index` → `App\Trainers\Http\Web\Controllers\TrainersController@index` with `TrainersIndexPageProps`: every `TRAINER` (name, class, sprite, team size, level range, types used, full payout, and the team: species, level, types, shiny) sorted by level, with each trainer's category; plus the player's party average level for the payout hint.
- Route `POST /trainers/{trainer}/challenge` named `trainers.web.challenge` → `ChallengeTrainerController`, redirects to the battle page.
- The sidebar's placeholder "Battle" item becomes a collapsible group, like "Your Pokémon", with "Trainers" as its first sub-item; tasks 6.9 and 6.13 add "Gyms" and "Battle Tower".

**UI suggestions** (ideas, not requirements):
- `AppPageHeader` with a search `InputGroup` on the right, placeholder "Search by name". It matches the trainer's `name` only ("Bob"), not the trainer class; the category tabs cover classes.
- Tabs by category: All, Townsfolk (Youngster, Lass, Gentleman…), Outdoors (Hiker, Camper, Bug Catcher…), Water (Swimmer, Fisherman, Sailor), Fighters (Black Belt, Battle Girl), Mystics (Psychic, Medium, Hex Maniac), Experts (Ace Trainer, Veteran, Dragon Tamer). The tab goes in the URL (`?category=water`); search works inside the chosen tab.
- One list `Card` with a row per trainer: trainer sprite, name and class, "3 Pokémon · Lv 20–25", type badges, payout (`MoneyAmount`) and an outline "Battle" button. Sorted by level inside each tab; no difficulty groups.
- When the party's average level is above the trainer's, a muted hint under the payout: "You'd get about ₽ 900 (your team is stronger)".
- A ghost "Team" button with an eye icon on each row opens a `Dialog` with the trainer's team in sending-out order: portrait, name, level and types for each Pokémon (shinies with the gold border), plus "Close" and a primary "Battle". Moves stay hidden.
- While a battle is ongoing, the Battle buttons are disabled and a notice links to the battle.

**Tests:** feature tests for the props and the challenge route (refused during a battle).

**Done when:** a player can find and battle trainers.

### 6.7 Trainers met while exploring

- [ ] Done

**Goal:** about 1 in 20 explores meets a generated trainer.

**Mock-up:** `docs/mockups/area.html` (state "Trainer encounter")

**Build:**
- `App\Trainers\Mechanics\TrainerTeamRoller::roll(Collection $species, int $size, int $minLevel, int $maxLevel, int $boost = 0): array` (used by the Battle Tower, task 6.12): random species, levels, genders, shiny at the normal odds, base moves. Trainers met while exploring use `EncounterRoller` instead (below).
- A small list of names and classes for generated trainers in `config/trainers.php`.
- In `015_create_encounters_table.php`: `kind` enum (`POKEMON`, `TRAINER`), nullable `trainer_name`, `trainer_class`, `json trainer_team`, `payout`.
- `ExploreAction` (task 2.6): `game.exploration.trainer_chance` (5, 1 in 20) rolls a trainer of 1–3 Pokémon, each picked with `EncounterRoller` (task 2.5) from the area's species: the same stage weights, 1 in 500 legendary or mythical, its own wild levels. Full payout = `game.trainers.explore_payout_per_level` (100) × the team's average level, reduced like any trainer.
- `BattleEncounterAction` starts a `TRAINER` battle for trainer encounters (paid like any trainer).

**UI suggestions** (ideas, not requirements):
- The encounter card shows the trainer sprite, "Hiker Bob wants to battle!", the team as small portraits (or "3 Pokémon"), the level range and the payout, with "Battle" (primary) and "Keep exploring" (outline).

**Tests:** trainer rolled with the faked odds; each team member rolled like a wild encounter (stage and legendary rolls faked) within its levels; payout from the average level; battle starts as a trainer battle and pays.

**Done when:** tests pass.

### 6.8 Gyms and badges

- [ ] Done

**Goal:** challenge gym leaders and earn badges.

**Build:**
- `App\Trainers\Actions\ChallengeGymAction::handle(User $user, Trainer $leader, array $pokemonIds): Battle`: only `GYM_LEADER`; `$pokemonIds` must be 1 to as many party Pokémon as the leader has, never more (`App\Trainers\Exceptions\GymTeamSizeException`), in the chosen order. Fewer is allowed (the page confirms it first); starts a `GYM` battle.
- A badge is a `TrainerVictory` against a gym leader. `App\Trainers\Queries\BadgesQuery::forRegion(User $user, Region $region)`.
- A won gym battle (`BattleEnded`): pays (task 6.5) and, on the first win, dispatches `BadgeEarned` (region, leader) for missions (`BEAT_GYM`, `EARN_REGION_BADGES`).

**Tests:** more Pokémon than the leader refused; fewer allowed; none refused; non-party Pokémon refused; first win gives the badge and the event once; rematch pays but no new badge.

**Done when:** tests pass.

### 6.9 Gyms page

- [ ] Done

**Goal:** the badge case per region.

**Mock-up:** `docs/mockups/gyms.html` (states "Region badges (Elite Four locked)", "Gym details", "Choose your team", "Leader brings 6: choose the order", "Party smaller than the leader's team", "Confirm battling with fewer", "All badges: Elite Four unlocked")

**Build:**
- Route `GET /gyms` named `trainers.web.gyms.index` → `GymsController@index` (`?region=kanto`), with `GymsIndexPageProps`: the region's 8 leaders (badge name, type, leader, team preview, won or not), Elite Four and Champion status, and the player's party.
- Route `POST /gyms/{trainer}/challenge` named `trainers.web.gyms.challenge` → `ChallengeGymController` (`pokemon_ids`).
- "Gyms" sub-item in the sidebar's "Battle" group, after "Trainers".

**UI suggestions** (ideas, not requirements):
- Region tabs (the region in the URL), the count "Badges 5 / 8" on the right.
- The badge case: a grid of 8 tiles, each with the badge (coloured when earned, outline when not), the gym type badge and the leader's name.
- Clicking a gym opens a `Dialog`: the leader's sprite and name, the gym type, the team as portraits with levels, the rule ("Bring 3 Pokémon"), and a primary "Challenge".
- The player picks 1 up to as many Pokémon as the leader brings, never more: once the limit is reached, the other rows are disabled. A counter shows "2 / 6 chosen". The picking order is the sending-out order. A party smaller than the leader's team can still challenge.
- "Choose up to 6 Pokémon" (the leader's team size, 2 to 6): the party as selectable rows, numbered in picking order; the primary "Challenge" is enabled from 1 Pokémon. With fewer than the leader, Challenge opens a confirmation: "Challenge with 4 of 6 Pokémon? Giovanni brings 6 Pokémon. You can battle with fewer, but you'll be at a disadvantage." with "Choose more" (outline) and "Challenge anyway" (primary).
- Under the badge case, an Elite Four card: locked with "Earn all 8 badges" and a lock icon, or unlocked with the four members and the Champion in order and a primary "Start the Elite Four".

**Tests:** feature tests for the props and the challenge route errors; browser test: challenge a gym with a chosen team.

**Done when:** a player can browse gyms and challenge one.

### 6.10 Elite Four run

- [ ] Done

**Goal:** the four members then the Champion, in order.

**Build:**
- Migration `023_create_elite_four_runs_table.php`: `uuid id`, `foreignIdFor(User)`, `region` enum, `stage` (1–5), `status` enum (`ACTIVE`, `WON`, `LOST`, `QUIT`), timestamps.
- `App\Trainers\Actions\StartEliteFourRunAction::handle(User $user, Region $region): EliteFourRun`: needs all 8 badges of the region (`App\Trainers\Exceptions\BadgesMissingException`); one active run at a time.
- `ContinueEliteFourRunAction::handle(EliteFourRun $run): Battle`: starts an `ELITE_FOUR` battle against the stage's opponent with the party in party order (healed: normal battles always heal).
- Listener `App\Trainers\Listeners\AdvanceEliteFourRunOnBattleEnded` for `ELITE_FOUR` battles: a win → next stage; after the Champion: `WON`, record the victory, dispatch `ChampionDefeated`, and on the player's first win against that Champion pay `game.trainers.champion_prize` (30,000) with `AddMoneyAction` (no level reduction). A loss or a run → `LOST`.
- `QuitEliteFourRunAction::handle(EliteFourRun $run): void`: only an active run, and only between battles (refused while its battle is ongoing: the player runs from the battle instead); status `QUIT`.
- Routes: `POST /gyms/{region}/elite-four` (`trainers.web.elite-four.start`), `POST /gyms/elite-four/continue` (`trainers.web.elite-four.continue`, redirects to the battle) and `DELETE /gyms/elite-four` (`trainers.web.elite-four.quit`, back to the Gyms page with a toast).
- Gyms page (task 6.9) during a run: the five steps with the beaten ones ticked, a primary "Continue: battle Agatha" and a destructive-outline "Quit the Elite Four" that asks for confirmation. The battle end screen after a member is beaten shows "Next: Agatha" and the same quit button (task 6.14).

**Mock-up:** `docs/mockups/gyms.html` (states "Elite Four run in progress", "Quit the Elite Four", "Champion beaten") and `docs/mockups/battle.html` (states "Elite Four: member beaten", "Elite Four: quit confirmation", "Elite Four: Champion beaten")

**UI suggestions** (ideas, not requirements):
- Quit confirmation (`alert-dialog`): "Quit the Kanto Elite Four?" / "Your run ends here. Next time you start again from Lorelei." with "Keep going" (outline) and "Quit" (destructive).
- Champion beaten: a gold "Kanto Champion ✓" badge next to the result title and, the first time only, "+ ₽ 30,000" under "Your rewards" with "Champion prize, paid for your first win only."

**Tests:** refused without 8 badges; stages advance; a loss or a run ends the run; quit sets `QUIT` and is refused during the run's battle; the next run starts at stage 1; members pay nothing; beating the Champion records it and fires the event, and pays the prize only the first time; feature tests for the three routes.

**Done when:** tests pass, and the run can be played and quit from the Gyms page and the battle page.

### 6.11 Battle Tower: rules

- [ ] Done

**Goal:** checkpoints, opponent strength and Battle Points.

**Build:**
- `App\BattleTower\Mechanics\TowerFloorCalculator`:
  - `isCheckpoint(int $floor): bool` and `nextCheckpoint(int $floor): int`: checkpoints at 10, 25, 45, 70, 100… (each gap 5 floors longer).
  - `opponentLevel(int $floor): int` = min(floor, 100); `opponentBoost(int $floor): int` = 0 up to floor 100, then round((floor − 100) ÷ 9), max 100.
  - `teamSize(int $floor): int`: 3, or 6 on checkpoints.
  - `battlePoints(int $floor): int`: 1, plus 5 × the checkpoint's number on checkpoints.
  - `allowsLegendaries(int $floor): bool`: true after `legendaries_after_floor`.
- Config `game.battle_tower`: `top_floor` (1000), `first_checkpoint` (10), `checkpoint_gap_step` (5), `team_size` (3), `checkpoint_team_size` (6), `points_per_floor` (1), `points_per_checkpoint_number` (5), `legendaries_after_floor` (50).

**Tests:** the first five checkpoints; level and boost at floors 1, 100, 101, 550, 1000; team sizes; points on normal and checkpoint floors; legendaries not allowed at floor 50, allowed at 51.

**Done when:** tests pass.

### 6.12 Battle Tower: runs

- [ ] Done

**Goal:** start, climb, reorder, resume, quit.

**Build:**
- Migration `024_create_tower_runs_table.php`: `uuid id`, `foreignIdFor(User)`, `status` enum (`ACTIVE`, `ENDED`), `floor` (the next floor to fight), `json team` (for each of the 6: Pokémon ID, the frozen copy taken at the start — species, level, boost, shiny, gender, types, active moves, max HP and stats — its current HP, and the XP saved up so far), `battle_points_earned`, timestamps. In `009_create_users_table.php`: `best_tower_floor` (default 0), `battle_points` (default 0).
- `App\BattleTower\Actions\StartTowerRunAction::handle(User $user, array $pokemonIds): TowerRun`: exactly 6 of the player's Pokémon (party or Box), none on trade hold (Spec 7); one active run; copies each Pokémon into `team`, in the given order (the sending-out order).
- `ClimbTowerFloorAction::handle(TowerRun $run): Battle`: a `BATTLE_TOWER` battle against `TrainerTeamRoller` with the floor's level, boost and size. Species come from every species with wild levels; legendaries and mythicals only when `TowerFloorCalculator::allowsLegendaries()` (after floor 50). The player's side is built from the frozen copies in the run's team order, not the live Pokémon, starting at the HP stored in the run; the first with HP left starts. `heals_after` false. Refused while another battle is ongoing (one battle at a time).
- `ReorderTowerTeamAction::handle(TowerRun $run, array $pokemonIds): void`: exactly the run's 6 Pokémon in a new order (otherwise `InvalidArgumentException`); only between floors (refused while the run's battle is ongoing).
- Listener `App\BattleTower\Listeners\AdvanceTowerRunOnBattleEnded` for `BATTLE_TOWER` battles. Every ending: add each member's XP from the battle (task 1.17's counting with the Tower bonus; no XP Share) to its saved-up XP. On a win: store each member's HP back in the run, add the floor's points, `floor` + 1; on a checkpoint floor, heal the whole team. On a loss or a run: status `ENDED`.
- When the run ends (loss, quit or floor 1,000): pay each member's saved-up XP with `AwardExperienceAction` (to the live Pokémon), update `best_tower_floor` (highest floor won) and add points to `users.battle_points`.
- Tower hold: `App\BattleTower\Queries\ActiveTowerTeamQuery::contains(Pokemon $pokemon): bool`. `ReleasePokemonAction` and `EvolvePokemonAction` refuse a Pokémon in an active run (`App\BattleTower\Exceptions\PokemonInTowerRunException`). Listing and offering refuse it too (tasks 7.4 and 7.5, as a reason of `PokemonNotTradeableException`). The Pokémon page shows an "In a Battle Tower run" badge, with a tooltip that boosts and TMs count from its next run.
- `QuitTowerRunAction::handle(TowerRun $run): void`: ends the run, paying XP and keeping points and best floor.

**Tests:** start needs 6 and keeps their order; the run uses the frozen copy even after a team member levels up elsewhere; damage carries over between floors; checkpoint heals; XP is saved up per floor (the lost floor's too) and paid only when the run ends (loss, run, quit); loss ends the run and keeps points; best floor updated; reorder changes who starts the next floor, refuses a wrong list and is refused during the run's battle; a fainted first member is skipped; no legendaries on floor 50; Tower hold refuses release and evolve; climbing is refused during another battle; resume after reload (the run is just loaded again).

**Done when:** tests pass.

### 6.13 Battle Tower page

- [ ] Done

**Goal:** the lobby and the run screen.

**Mock-up:** `docs/mockups/tower.html` (all states)

**Build:**
- Route `GET /battle-tower` named `battle-tower.web.index` → `BattleTowerController@index` with `BattleTowerIndexPageProps`: best floor, Battle Points, the active run (floor, next checkpoint, team with HP) or the player's Pokémon for picking.
- Routes `POST /battle-tower/runs` (`battle-tower.web.runs.store`, `pokemon_ids` in sending-out order), `POST /battle-tower/runs/climb` (`battle-tower.web.runs.climb`, redirects to the battle), `POST /battle-tower/runs/order` (`battle-tower.web.runs.reorder`, `pokemon_ids`), `DELETE /battle-tower/runs` (`battle-tower.web.runs.destroy`).
- The team can be reordered in the lobby before starting (client-side, sent with `pokemon_ids`) and between floors (saved right away with the reorder route).
- After a Tower battle, the battle page's end screen links back to the Tower ("Back to the Tower").
- "Battle Tower" sub-item in the sidebar's "Battle" group, after "Gyms".

**UI suggestions** (ideas, not requirements):
- Header with two stats: "Best floor 37" and "Battle Points 1,240".
- Lobby: six empty team slots and "Choose your team", which opens a picker dialog with search and Party / Box tabs; picked Pokémon fill the slots in order; primary "Start run" enabled at 6.
- Reordering, in the lobby and during a run: each team member shows its number (1 = leads), a drag handle on desktop and "Move earlier" / "Move later" arrow buttons, the same pattern as the Party page (task 4.16). During a run, a line says "The first Pokémon with HP left leads the next floor."
- Run in progress: a big "Floor 38", a progress line to the next checkpoint ("Checkpoint at floor 45: full heal"), the team with HP bars (fainted greyed out), two per row when the screen is wide enough and one per row on phones, primary "Next battle" and a destructive-outline "Quit run" with a confirmation.
- Checkpoint reached: a green notice "Checkpoint! Your team was fully healed."
- Run over: floors climbed, Battle Points earned, the XP each team member received (with level-ups), "New best floor!" when it is, and "Start a new run".
- Lobby and run screens say the team is a frozen copy: "Your team as it was when the run started".

**Tests:** feature tests for the props and routes; browser tests: reorder the team in the lobby and start a run; reorder between floors and climb one floor.

**Done when:** a player can play the Tower from the page.

### 6.14 Battle page: trainer battles

- [ ] Done

**Goal:** the battle page shows trainer battles properly.

**Mock-up:** `docs/mockups/battle.html` (every Trainer, Gym, Elite Four and Tower state)

**Build:** fill task 1.26–1.35's components for trainer battles:
- Header (`BattleHeader`): the trainer's sprite, the trainer's name as the title and a description with the turn: "Trainer battle · Turn 3", "Pewter City Gym · Rock · Turn 3", "Kanto Elite Four · battle 2 of 5 · Turn 3" (with the five steps on the right: Lorelei ✓ › Bruno › Agatha › Lance › Blue), "Tower Trainer Lena · Turn 3" (title "Battle Tower · Floor 38").
- Opponent's team rail (task 1.34) with the trainer's name; gym leaders and the Elite Four keep unrevealed Pokémon hidden.
- More options (task 1.32): "Forfeit" only. The confirmation says what forfeiting costs: gym "Forfeit against Brock?" / "Forfeiting counts as a loss: no money and no badge, but your Pokémon keep the XP they earned."; Elite Four "Forfeit against Bruno?" / "Forfeiting counts as a loss and ends your Elite Four run. Next time you start again from Lorelei."; Tower "Forfeit floor 38?" / "Forfeiting counts as a loss and ends your Tower run. Your saved XP and Battle Points are paid out."
- Result card (task 1.35): money under "Your rewards" ("+ ₽ 1,440" with "Reduced from ₽ 1,800: your team outlevels Hiker Bob." when it applies); a gym's badge as its own reward row ("Boulder Badge", "Kanto badges 1 / 8. The badge only comes the first time.") above the money; "Next: Agatha" (primary) and "Quit the Elite Four" (destructive outline) after an Elite Four member; the Champion's prize and a "Kanto Champion ✓" badge by the title; Tower floors: "+ 1 Battle Point", "XP saved for the end of the run" rows, "Next floor" and "Back to the Tower"; a lost Tower run: "Run over at floor 38", a "New best floor!" badge by the title when it is one, and "XP from this run, paid now" with a row per team member.
- Lost: "You lost to Hiker Bob" / "No money this time." with the XP rows.

**UI suggestions** (ideas, not requirements):
- Elite Four steps: small pills joined by chevrons; done ones muted with a ✓, the current one with a dark border.

**Tests:** feature test: trainer battle props (no balls, trainer name, team); browser tests: the win screen shows the money and XP; the forfeit wording per battle type; quitting the Elite Four from the end screen.

**Done when:** trainer, gym, Elite Four and Tower battles all look right on the page.

---

## 7. Spec 7 — Trading

Read [07-trading.md](07-trading.md) first, including its "Details" section. Trading lives in a new domain, `src/App/Trading`. Notifications use Laravel's database notifications and are shared by later features.

### 7.1 Trade data

- [ ] Done

**Goal:** store listings and offers.

**Build:**
- Enums in `Shared\Enums`: `ListingStatus` (`OPEN`, `TRADED`, `CANCELLED`, `EXPIRED`), `OfferStatus` (`PENDING`, `ACCEPTED`, `DECLINED`, `WITHDRAWN`).
- Migration `025_create_trade_listings_table.php`: `uuid id`, `foreignIdFor(User)` (seller), `foreignIdFor(Pokemon)`, nullable `looking_for` (200), `status` enum, `fee`, `expires_at`, nullable `traded_at`, timestamps; index `['status', 'expires_at']`.
- Migration `026_create_trade_offers_table.php`: `uuid id`, `foreignIdFor(TradeListing)`, `foreignIdFor(User)` (offerer), `status` enum, timestamps; unique pending offer per user per listing (checked in code).
- Migration `027_create_trade_offer_pokemon_table.php` (`trade_offer_id`, `pokemon_id`) and `028_create_trade_offer_items_table.php` (`trade_offer_id`, `item_type`, `item_id`, `quantity`).
- In `010_create_pokemon_table.php`: `boolean('is_on_trade_hold')->default(false)`.
- Models `Shared\Models\TradeListing`, `TradeOffer`, `TradeOfferItem` (with factories); `Pokemon::scopeTradeable()` (owned, not locked, not on hold).
- Config `game.trading`: `listing_fee` (500), `listing_days` (7), `max_offer_pokemon` (6), `max_offer_items` (10), `looking_for_max_length` (200).

**Tests:** relations and the tradeable scope.

**Done when:** tables and models exist; tests pass.

### 7.2 Trade holds everywhere

- [ ] Done

**Goal:** a Pokémon on hold can't be used.

**Build:**
- Exception `App\Pokemon\Exceptions\PokemonOnTradeHoldException`.
- Refuse Pokémon on hold in: `AddToPartyAction`, `SwapPartyPokemonAction`, `ReleasePokemonAction`, `EvolvePokemonAction`, `TeachTmAction`, `UseBoostStonesAction`, `SetActiveMoveAction`, `ReorderActiveMovesAction`, `StartTowerRunAction`, gym and battle team selection.
- Pokémon page and Box show an "On trade" badge and disable those actions with the reason.

**Tests:** one test per action that a held Pokémon is refused.

**Done when:** tests pass.

### 7.3 Notifications

- [ ] Done

**Goal:** a reusable notification bell.

**Build:**
- `php artisan make:notifications-table` → migration `029_create_notifications_table.php` (rename the generated file to the next number).
- Notifications in `App\Trading\Notifications`: `OfferReceived`, `OfferAccepted`, `OfferDeclined`, `ListingTraded`, `ListingExpired` (database channel; each stores a short message and a link).
- `HandleInertiaRequests` shares `notifications.unreadCount`.
- Route `GET /notifications` (`notifications.web.index`, the latest 20) and `POST /notifications/read` (`notifications.web.read`, marks all read).

**Mock-up:** `docs/mockups/layout.html` (state "Notifications open")

**UI suggestions** (ideas, not requirements):
- A bell icon button in the top bar with a small count badge (hidden at 0).
- Clicking opens a `Popover` (a `Sheet` on phones) listing notifications: icon, one line ("Misty made an offer on your Shiny Gible"), time ago, unread ones with a dot; each links to its page. A "Mark all as read" ghost button at the top.
- Empty: "No notifications yet".

**Tests:** feature tests for the shared count and both routes.

**Done when:** the bell works with a test notification.

### 7.4 List a Pokémon

- [ ] Done

**Goal:** put a Pokémon on the market.

**Build:**
- `App\Trading\Actions\CreateListingAction::handle(User $user, Pokemon $pokemon, ?string $lookingFor): TradeListing`
  - The Pokémon must be tradeable (`App\Trading\Exceptions\PokemonNotTradeableException` with the reason: locked, on hold, taking part in an ongoing battle (its ID is on the player's side of the player's `ONGOING` battle), or in an active Battle Tower run (`ActiveTowerTeamQuery`, task 6.12)) and not the last party Pokémon (`PartyCannotBeEmptyException`).
  - Spends the fee (`SpendMoneyAction`), removes it from the party (closing the gap in the party order, task 4.15), sets `is_on_trade_hold`, sets `expires_at`. One transaction.

**Tests:** listing created and fee spent; leaves the party and goes on hold; locked, held, in-battle, in-Tower-run and last-party Pokémon refused; not enough money refused; nothing changes when refused.

**Done when:** tests pass.

### 7.5 Make, withdraw and decline offers

- [ ] Done

**Goal:** the offer side.

**Build:**
- `App\Trading\Actions\MakeOfferAction::handle(User $user, TradeListing $listing, array $pokemonIds, array $items): TradeOffer`
  - Refuses: own listing, listing not open, an existing pending offer by the player, empty offer, more than the limits, Pokémon not tradeable (locked, on hold, in an ongoing battle, or in an active Battle Tower run) or last party Pokémon, items not owned.
  - Puts the Pokémon on hold (leaving the party) and removes the items from the inventory (`RemoveInventoryItemAction`). Notifies the seller (`OfferReceived`).
- `WithdrawOfferAction::handle(TradeOffer $offer): void` (offerer only, pending only) and `DeclineOfferAction::handle(TradeOffer $offer): void` (seller only, pending only, notifies `OfferDeclined`): both release the holds and return the items (`AddInventoryItemAction`).

**Tests:** offer made, holds and items set aside; each refusal, including a Pokémon in an active Battle Tower run; withdraw and decline give everything back; notifications sent.

**Done when:** tests pass.

### 7.6 Accept an offer

- [ ] Done

**Goal:** the trade happens.

**Build:**
- `App\Trading\Actions\AcceptOfferAction::handle(TradeOffer $offer): void`, seller only, under a lock for the listing:
  - Refuses when the listing isn't open or the offer isn't pending, and when either player's Box would overflow (`App\Trading\Exceptions\TradeBoxFullException` saying whose).
  - Swaps owners: the listed Pokémon to the offerer, the offer's Pokémon to the seller, the offer's items to the seller's inventory. Traded Pokémon keep everything (moves, level, boost, shiny, original trainer), go to the Box and lose the hold.
  - Listing `TRADED` (`traded_at`), offer `ACCEPTED`, every other pending offer declined (`DeclineOfferAction`).
  - Notifies the offerer (`OfferAccepted`) and dispatches `TradeCompleted` (missions: `MAKE_TRADES` for both players).

**Tests:** owners swapped and everything kept; items moved; other offers declined and returned; Box full refused for each side; second accept refused; event and notifications.

**Done when:** tests pass.

### 7.7 Cancel and expire listings

- [ ] Done

**Goal:** listings end without a trade.

**Build:**
- `App\Trading\Actions\CancelListingAction::handle(TradeListing $listing): void` (seller only, open only): `CANCELLED`, fee kept, hold released, pending offers declined.
- `ExpireListingAction::handle(TradeListing $listing): void`: `EXPIRED`, fee refunded (`AddMoneyAction`), hold released, pending offers declined, seller notified (`ListingExpired`).
- Command `trading:expire-listings`, scheduled every 5 minutes in `withSchedule()` in `bootstrap/app.php`, expires open listings past `expires_at`.

**Tests:** cancel keeps the fee; expire refunds it; offers declined in both; the command expires only past-due listings.

**Done when:** tests pass.

### 7.8 Pokémon details dialog

- [ ] Done

**Goal:** see everything about a Pokémon in a trade.

**Mock-up:** `docs/mockups/trade-listing.html` (state "Pokémon details")

**Build:** a shared component `resources/js/shared/components/game/PokemonDetailsDialog.vue` (Spec 8's profile can reuse it), fed by a `PokemonDetails` shape from a shared PHP presenter: sprite, display name, gender, types, level, boost, shiny, stats, active moves, the full known-moves pool, ball, original trainer.

**UI suggestions** (ideas, not requirements):
- A read-only version of the Pokémon page's header, stats and moves, in a wide `Dialog`; known moves as compact type-coloured chips with power.
- Catch details as on the Pokémon page: "Caught in a [sprite] Ultra Ball", the ball's `ItemSprite` right before its name, then the original trainer.

**Tests:** feature test: listing props include the details for the listed and offered Pokémon.

**Done when:** the dialog opens from listings and offers.

### 7.9 Trade market page

- [ ] Done

**Goal:** browse open listings.

**Mock-up:** `docs/mockups/trade-market.html` (states "Market", "Filters applied", "No listings match"; the "Recent trades" section is under the listings)

**Build:**
- Route `GET /trading` named `trading.web.index` → `TradeMarketController@index`, with `TradeMarketIndexPageProps`: open listings (paginated, newest first) filtered by species, type, shiny, minimum boost and minimum level, each with the Pokémon summary, offer count and time left; plus the latest public trades.
- Sidebar "Trading" link, after "Inventory".

**Mock-up (sidebar):** `docs/mockups/layout.html`

**UI suggestions** (ideas, not requirements):
- `AppPageHeader` ("Trade market") with "My trades" (outline) and "New listing" (primary) on the right.
- Filters like the Box: a search `InputGroup`, a filters button opening `AppSearchFiltersDialog` (type, shiny, min boost, min level), active filters as removable chips.
- A grid of listing cards (auto-fill, about 13rem): portrait, display name (gold for shiny), level and boost badges, types, "3 offers", "6 days left", the seller, and two buttons: "Details" (ghost, opens `PokemonDetailsDialog`) and "View" (outline, the listing page). The card itself is not a link, so the buttons don't sit inside one.
- Below, "Recent trades": rows "Misty traded Shiny Gible Lv 48 +30 to Brock for Onix +50", time ago, each name linking to the profile (Spec 8).

**Tests:** feature tests for filters and props.

**Done when:** a player can browse and filter listings.

### 7.10 New listing

- [ ] Done

**Goal:** list a Pokémon from the page.

**Mock-up:** `docs/mockups/trade-market.html` (states "New listing: choose a Pokémon", "New listing: details")

**Build:**
- Route `POST /trading/listings` (`trading.web.listings.store`, `pokemon_id`, `looking_for`).
- "New listing" opens a two-step dialog: pick a tradeable Pokémon (search; locked, held and Tower-run ones disabled with the reason), then "Looking for" and the fee confirmation. Build it so step two can open on its own: the Pokémon page's "Trade" button (task 8.7) uses it.

**UI suggestions** (ideas, not requirements):
- Step 2 shows the chosen Pokémon's summary, a `Textarea` for "Looking for" with a 200-character counter, the fee ("Listing costs ₽ 500, refunded if no one trades within 7 days"), and a primary "List for ₽ 500".

**Tests:** feature test for the route and its errors; browser test: list a Pokémon.

**Done when:** a player can list from the market.

### 7.11 Listing page and offers

- [ ] Done

**Goal:** see a listing, make offers, and (as seller) accept or decline them.

**Mock-up:** `docs/mockups/trade-listing.html` (all states)

**Build:**
- Route `GET /trading/listings/{listing}` (`trading.web.listings.show`) with `TradeListingShowPageProps`: the Pokémon details, seller, "Looking for", time left, status, offers (offerer, Pokémon summaries, items) and whether the viewer is the seller.
- Routes: `POST /trading/listings/{listing}/offers` (`trading.web.offers.store`), `DELETE /trading/offers/{offer}` (withdraw), `POST /trading/offers/{offer}/accept`, `POST /trading/offers/{offer}/decline`, `DELETE /trading/listings/{listing}` (cancel).

**UI suggestions** (ideas, not requirements):
- Top: the listed Pokémon (portrait, name, badges, moves) with a "Details" button, the seller, "Looking for" in a quote block, "Expires in 6 days".
- Offers: one `Card` per offer with the offerer's name, their Pokémon as small cards (each with "Details") and items with quantities. The seller sees "Accept" (primary) and "Decline" (ghost); the offerer sees "Withdraw" on their own.
- Visitors without an offer see a primary "Make an offer"; it opens a dialog with two tabs, Pokémon (tradeable ones) and Items (quantity pickers), each with its own search input (Pokémon by name; items by name, including TM codes such as "395"), a summary of what's offered, and "Send offer".
- Accept asks for confirmation: "Trade your Shiny Gible for Shiny Dratini and 5 boost stones? The other 2 offers will be declined."
- Seller's own listing: "Cancel listing" (destructive outline) with a confirmation that the fee isn't refunded.

**Tests:** feature tests for each route and error; browser tests: make an offer; accept an offer.

**Done when:** a full trade can be done from the pages.

### 7.12 My trades

- [ ] Done

**Goal:** a player's listings, offers and history.

**Mock-up:** `docs/mockups/trade-mine.html` (all states)

**Build:**
- Route `GET /trading/mine` (`trading.web.mine`, `?tab=listings|offers|history`) with `MyTradesPageProps`: open listings with offer counts, pending offers with their listing, and finished trades (traded, cancelled, expired, declined, withdrawn), newest first.

**UI suggestions** (ideas, not requirements):
- Tabs: My listings, My offers, History.
- Listings and offers as compact rows linking to the listing page, with status badges (Open, Pending, Accepted, Declined, Expired, Cancelled) coloured by meaning.
- History rows say what happened in a sentence: "You traded Shiny Gible to Brock for Onix" with the date.

**Tests:** feature test for each tab's props.

**Done when:** a player can follow their trades.

---

## 8. Spec 8 — Social

Read [08-social.md](08-social.md) first, including its "Details" section. Profiles, player search and the captures feed live in a new domain, `src/App/Social`; the Dashboard stays in `src/App/Dashboard`.

### 8.1 Unique player names

- [ ] Done

**Goal:** every name is unique and usable on a profile.

**Build:**
- In `009_create_users_table.php`: a unique index on `lower(name)` (or a `name_normalized` column if the database can't index an expression).
- `StoreRegistrationRequest`: `name` 3–20 characters, letters, numbers, spaces, `-` and `_`, unique ignoring case, with a clear message ("That name is taken.").
- `UserFactory` gives unique names.

**Tests:** registration refuses a taken name in any case; refuses bad characters and lengths.

**Done when:** tests pass.

### 8.2 Latest captures feed

- [ ] Done

**Goal:** record notable catches and hatches as they happen.

**Build:**
- Migration `030_create_capture_feed_entries_table.php`: `uuid id`, `foreignIdFor(User)`, nullable `foreignIdFor(Pokemon)` (null on delete), `foreignIdFor(Species)`, `level`, `is_shiny`, `source` enum (`WILD`, `EGG`), `created_at`. Model `Shared\Models\CaptureFeedEntry`.
- `App\Social\Mechanics\NotableCaptureChecker::isNotable(Species $species, int $level, bool $isShiny): bool`: shiny, legendary or mythical, or level ≥ `game.social.notable_level` (60).
- Listener `App\Social\Listeners\RecordCaptureOnPokemonCaught` adds an entry for notable catches and hatches.
- `App\Social\Queries\LatestCapturesQuery::get(int $limit = 20)`.

**Tests:** shiny, legendary, mythical and level 60 are recorded; ordinary ones are not; the entry keeps the catch level after a level-up; the newest come first.

**Done when:** tests pass.

### 8.3 Profile data

- [ ] Done

**Goal:** what a profile shows.

**Build:**
- `App\Social\Queries\PlayerProfileQuery::get(User $user)`: name, join date, Pokédex count (distinct species owned now) and total species (every species row, Megas, G-Max and forms included: 1,330 today), shiny count (owned now), best Battle Tower floor, badges per region with Elite Four and Champion status (Spec 6's `TrainerVictory`), and open trade listings (Spec 7).
- Badges, Tower and listings show as empty until those specs are built.

**Tests:** each number for a seeded player; a traded-away Pokémon no longer counts.

**Done when:** tests pass.

### 8.4 Profile page

- [ ] Done

**Goal:** a player's public profile.

**Mock-up:** `docs/mockups/profile.html` (all states)

**Build:**
- Route `GET /players/{user}` named `social.web.players.show` → `PlayerController@show` with `PlayerShowPageProps` from `PlayerProfileQuery`. Visible to every logged-in player.
- Every player name in the app (captures feed, recent trades, listings, offers, notifications) links here.
- The sidebar user menu gets "My profile".

**UI suggestions** (ideas, not requirements):
- A header `Card`: an avatar with the initials (the same `Avatar` as the sidebar), the name, "Joined Nov 2026", and "This is you" for your own profile.
- Three stat tiles: Pokédex "702 / 1,330" with a thin `Progress` and the %, Shinies, Best Tower floor.
- Badges: one row per region with 8 small badge marks (gold when earned, outline when not), then "Elite Four" and "Champion" marks; regions with no badges collapse into "No badges yet" text.
- Active trade listings as the same listing cards as the trade market (Spec 7); empty: "No active listings".

**Tests:** feature test for the props; guests redirected to login.

**Done when:** profiles open from every player name.

### 8.5 Player search

- [ ] Done

**Goal:** find a player by name.

**Mock-up:** `docs/mockups/players.html` (all states)

**Build:**
- Route `GET /players` named `social.web.players.index` (`?search=`) with `PlayersIndexPageProps`: matching players (name, join date, Pokédex %), 20 per page, sorted by name.
- Sidebar "Players" link, after "Trading".

**Mock-up (sidebar):** `docs/mockups/layout.html`

**UI suggestions** (ideas, not requirements):
- `AppPageHeader` ("Players") with a search `InputGroup` that searches as you type (debounced).
- One list `Card` with rows: avatar initials, name, "Joined Nov 2026", "Pokédex 68%", the whole row linking to the profile. `AppPagination` below.
- Empty search shows the newest players; no match: `AppEmptyState` "No players named "gary"".

**Tests:** feature tests: search by part of a name, ignoring case; pagination.

**Done when:** a player can find others by name.

### 8.6 Dashboard

- [ ] Done

**Goal:** the new home page.

**Mock-up:** `docs/mockups/dashboard.html` (all states)

**Build:**
- `DashboardPageProps` (replacing the empty Dashboard): "waiting for you" items (eggs ready to hatch or catch, rewards to claim with their count, new offers on the player's listings, an ongoing battle, an active Tower run), today's daily missions in short form with the reset time and streak, the latest captures (`LatestCapturesQuery`, 10), recent trades (5), and the shortcuts.
- Load captures and trades as deferred props so the page shows fast; skeletons meanwhile.

**UI suggestions** (ideas, not requirements):
- `AppPageHeader` "Welcome back, Ash!".
- Top row: "Waiting for you" `Card` (each item a row with an icon, a short sentence and a "Go" link; empty: "All caught up" with a check icon) next to a compact "Daily missions" `Card` (goal, "3 / 5", the reset countdown, the streak).
- Then "Latest captures" and "Recent trades" side by side on desktop, stacked on phones; each row: portrait, "Misty caught Shiny Gyarados Lv 34", time ago; names link to profiles.
- Shortcuts: a row of outline buttons (Explore, Party, Box, Shop, Missions, Trading).
- Phone order: waiting, missions, captures, trades, shortcuts.

**Tests:** feature test for each prop group, including "All caught up" when nothing waits.

**Done when:** the Dashboard matches the mock-up.

### 8.7 Pokémon page: Trade action

- [ ] Done

**Goal:** list a Pokémon from its own page (task 7.10).

**Mock-up:** `docs/mockups/pokemon.html` (states "Overview" and "Locked")

**Build:** a "Trade" outline button in the Pokémon page's action row opens task 7.10's listing dialog at step two. Disabled with the reason when the Pokémon is locked, on trade hold, in a Battle Tower run, or the last party Pokémon; "View listing" instead when it is already listed.

**Tests:** browser test: list a Pokémon from its page.

**Done when:** a Pokémon can be listed from its page.
