# Tasks

Planned work, in order. Each task is small enough to build, test and merge on its own. Tick a task when its work is merged.

Every task names the classes, methods, columns and files it adds, using [glossary.md](glossary.md). If a task needs a name it does not give, build it from the glossary's recipes and add it to the glossary.

Each task follows the project rules in `.ai/rules/`. "Tests" lists the behaviour to cover, not every test case.

---

## 0. Foundations

### 0.1 Point AI sessions at the specs ✅

- [x] `AGENTS.md` and `.ai/rules/index.md` say that feature behaviour lives in `docs/specs/`, that the relevant spec is read before working on a feature, and that specs are updated when a decision changes.

### 0.2 A home for game mechanics

**Goal:** pure game rules (formulas, odds, rolls) live in one strictly tested kind of folder.

**Build:**
- Folder `src/App/{Domain}/Mechanics/`, usable by any domain.
- Rule file `.ai/rules/mechanics.md` and a row in `.ai/rules/index.md` for `src/App/**/Mechanics/**`. Rules:
  - Classes are `final`, named `{Noun}Calculator`, `{Noun}Roller` or `{Noun}Checker` (see the glossary).
  - They may read models and their loaded relations, and `config('game.*')`. They never query or save to the database, use HTTP, sessions or auth.
  - Randomness only through `RandomHelper` (task 0.3), and only in `Roller` classes.
- `arch()` rule in `tests/Architectural/ArchitecturalTest.php`: `App\*\Mechanics` are final classes with suffix `Calculator`, `Roller` or `Checker`, extend nothing, and do not use `Illuminate\Support\Facades\DB`, `Illuminate\Http`, `Illuminate\Support\Facades\Auth`.
- Move `App\Battle\Services\DamageCalculator` to `App\Battle\Mechanics\DamageCalculator`. Update `PokedexShowPageProps` and the test. Remove the empty `Services` folder.

**Done when:** rule file, index row and `arch()` rule exist; `DamageCalculator` is in `Mechanics`; the suite passes.

### 0.3 Testable randomness

**Goal:** one source of randomness that tests can control.

**Build:**
- `Shared\Helpers\RandomHelper`, a final class with static methods:
  - `chance(float $probability): bool`: true with the given probability (0.0–1.0).
  - `integer(int $min, int $max): int`: inclusive range.
  - `float(float $min, float $max): float`
  - `pick(array $items): mixed`: one random item.
  - For tests: `fake(array $values): void` returns the given values in order, whatever method is called; `reset(): void` goes back to real randomness. Calling a method after the faked values run out throws an exception so tests fail loudly.
- Use PHP's `Random\Randomizer` inside.
- Replace the three `Lottery::odds()` calls (`Species`, `PlaceEggAction`, `ListLayoutSpritesAction`) with `RandomHelper::chance()`, and update their tests.
- Reset the fake after each test in `tests/Pest.php`.
- Rule in `.ai/rules/helpers.md`: game code gets randomness only from `RandomHelper`.
- `arch()` rule: `rand`, `mt_rand`, `random_int`, `array_rand`, `shuffle`, `Illuminate\Support\Lottery` and `Illuminate\Support\Arr::random` are not used in `App` or `Shared`, except inside `RandomHelper`.

**Tests:** `tests/Shared/Unit/Helpers/RandomHelperTest.php`: each method returns values in range; `fake()` returns values in order; running out throws; `reset()` restores randomness.

**Done when:** `RandomHelper` exists with tests, `Lottery` is gone from `src`, rules and `arch()` rule exist, the suite passes.

### 0.4 Events and listeners between domains

**Goal:** domains announce what happened instead of calling each other. Needed before Spec 5 at the latest.

**Build:**
- Folders `src/App/{Domain}/Events/` and `src/App/{Domain}/Listeners/`.
- Rule file `.ai/rules/events.md` and index rows:
  - An Action that finishes something other domains may care about dispatches an event, named in the past tense (`PokemonCaught`).
  - Events are `final readonly` classes carrying IDs and plain values, using the `Dispatchable` trait.
  - Listeners live in the domain that reacts, are named `{Verb}{Noun}On{Event}` (for example `AdvanceMissionsOnPokemonCaught`) and have a `handle({Event} $event)` method.
  - Listeners run synchronously for now.
- `arch()` rules for both folders.
- Example: `App\Pokemon\Actions\Incubator\CatchPokemonAction` dispatches `App\Pokemon\Events\PokemonCaught` with `pokemonId` and `userId`.

**Tests:** `CatchPokemonActionTest` asserts `PokemonCaught` is dispatched with the right IDs (`Event::fake`).

**Done when:** rule file, index rows and `arch()` rules exist, the example event is dispatched and tested, the suite passes.

---

## 1. Spec 1 — Power model and battle system

Read [01-power-model-and-battles.md](01-power-model-and-battles.md) first. Tasks 1.1–1.18 are backend only; the battle page comes last (1.19–1.21).

There is no production data yet, so database changes are new migrations and local databases are rebuilt with `php artisan migrate:fresh --seed`. No data migrations.

### 1.1 Add boost to Pokémon

**Goal:** every Pokémon has a boost level from 0 to 100.

**Build:**
- Migration `014_add_boost_to_pokemon_table.php`: `unsignedTinyInteger('boost')->default(0)` after `experience`.
- `Pokemon` model: cast `'boost' => 'integer'`.
- `PokemonFactory`: `boost` defaults to 0; new state `boosted(int $boost)`.
- `config/game.php`: `'pokemon' => ['max_boost' => 100, ...]`.

**Tests:** factory state sets the boost; a new Pokémon from `CreatePokemonAction` has boost 0.

**Done when:** column, cast, factory state and config key exist; tests pass.

### 1.2 Calculate a Pokémon's stats

**Goal:** turn level, boost and shiny into HP, Attack and Defense.

**Build:**
- Value object `Shared\ValueObjects\PokemonStats` (`final readonly`, implements `ValueObject`): `int $hp`, `float $attack`, `float $defense`.
- `App\Pokemon\Mechanics\StatsCalculator::calculate(int $level, int $boost, bool $isShiny): PokemonStats`:
  - `hp` = round(4 × (level + 10) × (1 + boost / 100) × (1.25 if shiny))
  - `attack` = (level + 10) / 70 × (1 + boost / 100) × (1.25 if shiny)
  - `defense` = 0.75 if shiny, else 1.0 (multiplier on damage taken)
- New config keys under `game.stats`: `hp_factor` (4), `level_offset` (10), `attack_divisor` (70), `shiny_hp_multiplier` (1.25), `shiny_attack_multiplier` (1.25), `shiny_defense_multiplier` (0.75).
- `Pokemon::getStats(): PokemonStats` calls the calculator.

**Tests:** `tests/App/Pokemon/Unit/Mechanics/StatsCalculatorTest.php`: level 1 → HP 44; level 100 → HP 440; +100 boost doubles HP and attack; shiny gives ×1.25 HP and attack and 0.75 defense.

**Done when:** calculator, value object, config keys and model method exist; tests pass.

### 1.3 Split moves into known moves and active slots

**Goal:** a Pokémon has a pool of known moves; up to 4 of them sit in active slots.

**Build:**
- Migration `015_add_slot_to_pokemon_move_table.php`: nullable `unsignedTinyInteger('slot')`; unique `['pokemon_id', 'move_id']`; unique `['pokemon_id', 'slot']`.
- `Pokemon` model: rename `moves()` to `knownMoves()` (with `withPivot('slot')`); add `activeMoves()` = known moves with a slot, ordered by slot.
- `CreatePokemonAction::addBaseMoves()`: attach the species' unique base moves (up to 4) with slots 1, 2, 3…; remove `repeatUntil(4)`. A species with fewer than 4 base moves gives fewer active moves.
- Update every use of `moves` (`BoxIndexPageProps`, Box tests) to `activeMoves`.

**Tests:** `CreatePokemonActionTest`: base moves become active in slots 1–4; no duplicates; a species with 2 base moves gives 2 active moves; the default-move fallback still works.

**Done when:** migration, relations and action changes are in; Box page still shows the moves; the suite passes.

### 1.4 Put a known move in an active slot

**Goal:** the backend for "swap" on the Pokémon page (the page itself is Spec 4).

**Build:**
- `App\Pokemon\Actions\SetActiveMoveAction::handle(Pokemon $pokemon, Move $move, int $slot): void`
  - `$move` must be a known move, otherwise throw `App\Pokemon\Exceptions\MoveNotKnownException`.
  - `$slot` must be 1–4, otherwise throw `InvalidArgumentException`.
  - If `$move` is already active in another slot, the two moves swap slots. Otherwise the move that was in `$slot` (if any) goes back to the pool (slot `null`).

**Tests:** `tests/App/Pokemon/Unit/Actions/SetActiveMoveActionTest.php`: replace, swap, empty slot, unknown move, invalid slot.

**Done when:** action and exception exist; tests pass.

### 1.5 Remove one-hit-KO moves

**Goal:** OHKO moves (Fissure, Guillotine, Horn Drill, Sheer Cold) never exist in the game.

**Build:**
- `MoveSeeder` skips moves whose category is `ohko`. `SpeciesBaseMovesSeeder` skips references to skipped moves.
- Remove `MoveCategory::OHKO` if nothing else uses it.

**Tests:** seeder test (in `tests/Shared/Unit/Seeders`): no move with category `ohko` after seeding.

**Done when:** no OHKO moves after `migrate:fresh --seed`; tests pass.

### 1.6 Damage formula

**Goal:** calculate the damage of one hit.

**Build:**
- `App\Battle\Mechanics\DamageCalculator`:
  - Rename the existing `calculateDamageMultiplier()` to `typeMultiplier()` (update `PokedexShowPageProps`).
  - Add `calculate(PokemonStats $attackerStats, array $attackerTypes, PokemonStats $defenderStats, array $defenderTypes, Move $move, bool $isCriticalHit, float $randomFactor): int`:
    - damage = move power × attacker attack × STAB (1.5 if the move's type is one of the attacker's types) × type multiplier × critical (1.5) × randomFactor × defender defense
    - Rounded down. At least 1 when the type multiplier is above 0. 0 when the move has no power.
- Config keys under `game.battle`: `stab_multiplier` (1.5), `critical_hit_multiplier` (1.5).
- The caller rolls `isCriticalHit` and `randomFactor` (task 1.8); the calculator has no randomness.

**Tests:** `tests/App/Battle/Unit/Mechanics/DamageCalculatorTest.php`: the Spec 1 matchup table (level 100 vs 100 takes about 4 hits), STAB, immune type gives 0, minimum 1, critical, shiny defender.

**Done when:** rename and new method exist; tests pass.

### 1.7 Turn order

**Goal:** decide who acts first each turn.

**Build:**
- `App\Battle\Mechanics\TurnOrderCalculator::firstMoverChance(int $level, int $opponentLevel): float` = level ÷ (level + opponentLevel).

**Tests:** 100 vs 100 → 0.5; 1 vs 100 → about 0.0099.

**Done when:** calculator exists; tests pass.

### 1.8 Roll a move's outcome

**Goal:** all the dice for one move use, in one place.

**Build:**
- Value object `Shared\ValueObjects\MoveOutcome`: `bool $hasHit`, `int $hitCount`, `bool $isCriticalHit`, `float $randomFactor`, `bool $causesFlinch`, `?Ailment $inflictedAilment`.
- `App\Battle\Mechanics\MoveOutcomeRoller::roll(Move $move): MoveOutcome`, using `RandomHelper`:
  - hit if a roll is within the move's accuracy;
  - hit count between `min_hits` and `max_hits` (1 when empty);
  - critical with chance 1 / `game.battle.critical_hit_odds` (16);
  - random factor between `game.battle.damage_random_min` (0.85) and 1.0;
  - flinch with `flinch_chance`; ailment with `ailment_chance`.
  - A miss sets everything else to its "nothing happened" value.

**Tests:** with `RandomHelper::fake()`: miss, hit, multi-hit, critical, flinch, ailment.

**Done when:** roller and value object exist; tests pass.

### 1.9 Decide status effect numbers (decision, no code)

**Goal:** Spec 1 lists the status effects but not their numbers. Agree them and write them into the spec.

**Proposal** (based on the main games):

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

**Done when:** the table is agreed and added to Spec 1, with config keys under `game.battle.ailments`.

### 1.10 Battle data: table, model and state

**Goal:** a battle can be saved and loaded. No turn logic yet.

**Build:**
- Enums in `Shared\Enums`: `BattleType` (`WILD`, `TRAINER`, `GYM`, `ELITE_FOUR`, `BATTLE_TOWER`), `BattleStatus` (`ONGOING`, `WON`, `LOST`, `FORFEITED`, `CAUGHT`).
- Migration `016_create_battles_table.php`: `uuid id`, `foreignIdFor(User)`, `type` enum, `status` enum, `json state`, timestamps; index `['user_id', 'status']`.
- Model `Shared\Models\Battle` (with factory), relation `User::battles()`.
- Value objects in `Shared\ValueObjects`:
  - `Combatant`: `?string $pokemon_id` (null for wild or NPC), `string $species_id`, `int $level`, `int $boost`, `bool $is_shiny`, `PokemonGender $gender`, `array $types`, `array $move_ids`, `int $max_hp`, `int $current_hp`, `?Ailment $ailment`, `int $ailment_turns_left`, `int $confusion_turns_left`, `bool $has_entered_field`, `array $used_once_per_battle_move_ids`. Method `isFainted(): bool`.
  - `BattleSide`: `array $combatants`, `int $active_index`. Methods `activeCombatant(): Combatant`, `hasCombatantsLeft(): bool`.
  - `BattleState`: `int $turn`, `BattleSide $player_side`, `BattleSide $opponent_side`, `bool $is_awaiting_send_out`, `array $log`.
- Cast `Shared\Casts\AsBattleState`.

**Tests:** `BattleState` survives a save and reload unchanged; `Combatant::isFainted()`; `BattleSide::hasCombatantsLeft()`.

**Done when:** enums, migration, model, value objects and cast exist; tests pass.

### 1.11 Status effect rolls

**Goal:** the dice for status effects during a turn.

**Build** (numbers from task 1.9):
- `App\Battle\Mechanics\StatusEffectRoller` with:
  - `canAct(Combatant $combatant): bool`: handles paralysis, sleep, freeze, and the confusion self-hit roll (returns `false` when it hits itself).
  - `durationFor(Ailment $ailment): int`: sleep and confusion turn counts.
- `App\Battle\Mechanics\StatusEffectCalculator::endOfTurnDamage(Combatant $combatant): int`: burn and poison.

Uses `Combatant` from task 1.10.

**Tests:** one per effect, with `RandomHelper::fake()`.

**Done when:** roller and calculator exist; tests pass.

### 1.12 Start a wild battle

**Goal:** create a battle against one wild Pokémon.

**Build:**
- `App\Battle\Actions\StartWildBattleAction::handle(User $user, NewPokemon $wildPokemon): Battle`
  - Throws `App\Battle\Exceptions\BattleAlreadyInProgressException` if the user has an `ONGOING` battle.
  - Throws `App\Battle\Exceptions\EmptyPartyException` if the party is empty.
  - Player side: party Pokémon in Party-page order, as `Combatant`s built with `StatsCalculator` and their active moves. The first one is active and `has_entered_field = true`.
  - Opponent side: one `Combatant` from `$wildPokemon` (boost 0, active moves = the species' base moves, up to 4).
- Artisan command `battle:start-wild {email} {species-slug} {level}` for local testing until Spec 2 adds exploring. Available only when `app()->isLocal()`.

**Tests:** creates one battle with the right sides; refuses a second ongoing battle; refuses an empty party.

**Done when:** action, exceptions and command exist; tests pass.

### 1.13 Take a turn: damage only

**Goal:** the core turn loop, without accuracy, crits, flinch or status effects.

**Build:**
- `App\Battle\Actions\TakeTurnAction::handle(Battle $battle, int $moveSlot): Battle`
  - Refuses when the battle is not `ONGOING` or is awaiting a send-out (`App\Battle\Exceptions\BattleNotReadyForTurnException`).
  - The opponent picks a random active move (`RandomHelper::pick`).
  - Turn order from `TurnOrderCalculator` and `RandomHelper::chance`.
  - Each side's move deals damage from `DamageCalculator` (no critical, random factor 1.0 for now). A fainted Pokémon does not act.
  - Adds log lines ("Magikarp used Surf!", "It's super effective!") and increments `turn`.
  - Wraps the update in `Cache::withoutOverlapping` with a `LockKeys` method, per the Support rule.

**Tests:** both sides lose HP; order follows the faked roll; a fainted opponent does not act; log lines are added; refused when not ongoing.

**Done when:** action works for one turn; tests pass.

### 1.14 Take a turn: accuracy, critical hits, multi-hit and flinch

**Goal:** plug `MoveOutcomeRoller` into the turn.

**Build:** `TakeTurnAction` uses `MoveOutcomeRoller::roll()` for each move: misses log "…'s attack missed!"; each hit of a multi-hit move deals damage; criticals log "A critical hit!"; flinch only works when the attacker acted first. Z-Moves and Dynamax moves (`is_z_move`, `is_dynamax_move`) can be used once per battle, tracked in `used_once_per_battle_move_ids`; Dynamax moves only by G-Max species.

**Tests:** one per behaviour, with `RandomHelper::fake()`.

**Done when:** tests pass.

### 1.15 Take a turn: status effects

**Goal:** plug status effects into the turn.

**Build:** before acting, `StatusEffectRoller::canAct()`; after a hit, apply the outcome's ailment (one major ailment at a time); at the end of the turn, `StatusEffectCalculator::endOfTurnDamage()`; count down durations. Log each effect ("Pidgey is paralyzed! It can't move!").

**Tests:** one per effect.

**Done when:** tests pass.

### 1.16 Fainting, sending out, winning, losing and giving up

**Goal:** the battle can end.

**Build:**
- In `TakeTurnAction`: when the player's active Pokémon faints and others are left, set `is_awaiting_send_out`. When none are left, status `LOST`. When the opponent has none left, status `WON`.
- `App\Battle\Actions\SendOutPokemonAction::handle(Battle $battle, int $combatantIndex): Battle`: only while awaiting a send-out; the chosen combatant must not be fainted; sets `has_entered_field = true`.
- `App\Battle\Actions\ForfeitBattleAction::handle(Battle $battle): Battle`: status `FORFEITED`.
- Nothing is written back to `Pokemon` rows: every battle ends fully healed (Spec 1).

**Tests:** win, loss, awaiting send-out, sending out a fainted Pokémon is refused, forfeit.

**Done when:** tests pass.

### 1.17 XP after a won battle

**Goal:** Pokémon that fought gain XP and level up.

**Build:**
- `App\Pokemon\Services\ExperienceCalculator` → move to `App\Pokemon\Mechanics\ExperienceCalculator` (update callers). Add `experienceForDefeating(int $opponentLevel, bool $isKnockout): int`. Placeholder until the open question is settled: opponent level × 10, ×1.5 for the Pokémon that knocked it out (config `game.experience.per_opponent_level`, `game.experience.knockout_multiplier`).
- `App\Pokemon\Actions\AwardExperienceAction::handle(Pokemon $pokemon, int $experience): void`: adds XP, updates `level` with `levelForExperience()`, stops at level 100.
- When `TakeTurnAction` ends with `WON`, award XP to every player combatant with `has_entered_field`. Track who knocked out each opponent in the state.

**Tests:** XP added; level goes up; level 100 gains nothing; only Pokémon that entered the field gain XP; the knockout bonus.

**Done when:** tests pass.

### 1.18 Evolution

**Goal:** the backend for the Evolve button (the button itself is on the Pokémon page, Spec 4).

**Build:**
- First check the seed data: Mega evolutions need `min_level` 100 and their Mega Stone as `item`; G-Max evolutions need `min_level` 100. Fix the data if any are missing.
- `App\Pokemon\Mechanics\EvolutionRequirementChecker::check(Pokemon $pokemon, SpeciesEvolution $evolution, User $owner): ?string`: `null` when allowed, otherwise the reason ("Needs level 36", "Needs a Water Stone").
- `App\Pokemon\Actions\EvolvePokemonAction::handle(Pokemon $pokemon, SpeciesEvolution $evolution): Pokemon`: re-checks the requirement (throws `App\Pokemon\Exceptions\EvolutionNotAllowedException`), uses up the item (Mega Stones and evolution stones), changes `species_id`, adds the new species' base moves to the known moves (no duplicates, active slots unchanged). Level, boost, shiny stay.

**Tests:** checker reasons (level, item, gender); evolution keeps level, boost and shiny; known moves grow without duplicates; item used up; refused when requirements are not met.

**Done when:** checker and action exist; tests pass.

### 1.19 Battle page: show the battle

**Goal:** a read-only battle page.

**Build:**
- Route `GET /battle` named `battle.web.show` → `App\Battle\Http\Web\Controllers\BattleController@show`; shows the user's `ONGOING` battle or redirects to the Dashboard.
- `App\Battle\Http\Web\PageProps\BattleShowPageProps`: both active combatants (sprite, name, level, boost, shiny, ailment), player HP as numbers, opponent HP as a percentage only, team dots, active moves (name, type, power), log.
- Page `resources/js/app/Battle/pages/Battle/index.vue` with the Spec 1 layout; stacks on phones.

**Tests:** feature test for the props, including that opponent HP is a percentage only; redirect when there is no battle.

**Done when:** page shows a battle started with `battle:start-wild`.

### 1.20 Battle page: take turns

**Goal:** the player can fight.

**Build:**
- Route `POST /battle/turn` named `battle.web.turn` → `TakeTurnController` with `TakeTurnRequest` (`move_slot` 1–4).
- Move buttons submit the turn; super-effective moves show a hint (from `typeMultiplier`).
- The HP bar slides down (the only animation).

**Tests:** feature test: a valid turn updates the battle; invalid slot is rejected; browser test: clicking a move updates HP and the log.

**Done when:** a full battle can be played to the end from the page, except sending out and giving up.

### 1.21 Battle page: send out, give up and end screen

**Goal:** finish the battle flow on the page.

**Build:**
- Route `POST /battle/send-out` (`battle.web.send-out`) → `SendOutPokemonController`; a dialog lists the remaining team (portrait, level, HP) when a Pokémon faints.
- Route `POST /battle/forfeit` (`battle.web.forfeit`) → `ForfeitBattleController`; "Give up" button with a confirmation.
- End screen: won or lost, XP gained per Pokémon, level-ups, and a button back to the Dashboard.

**Tests:** feature tests for both routes; browser test for the faint dialog and giving up.

**Done when:** a battle can be played from start to end screen on the page.
