# Tasks

Planned work, in order. Each task is small enough to build, test and merge on its own. Tick a task when its work is merged.

Every task names the classes, methods, columns and files it adds, using [glossary.md](glossary.md). If a task needs a name it does not give, build it from the glossary's recipes and add it to the glossary.

Each task follows the project rules in `.ai/rules/`. "Tests" lists the behaviour to cover, not every test case.

Tasks that change the screen have **UI suggestions**: common elements and components that could build the page. They are ideas, not requirements; use something else when it fits better.

Tasks with a **Mock-up** link have an interactive mock-up in `docs/mockups/` (open it in a browser; the State menu at the top shows each state). Match its layout, content and states, but build the page from the project's components, not from the mock-up's HTML or CSS. Components named in them are the project's shadcn-vue components (`resources/js/shared/components/ui`) and shared components (`resources/js/shared/components`).

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

### 1.9 Decide status effect numbers ✅

- [x] Agreed and added to Spec 1 under "Status effects". Task 1.11 adds the config keys under `game.battle.ailments`.

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

**Build** (numbers from Spec 1, "Status effects"; add them to `config/game.php` under `game.battle.ailments`):
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
- `App\Pokemon\Services\ExperienceCalculator` → move to `App\Pokemon\Mechanics\ExperienceCalculator` (update callers). Add `experienceForDefeating(int $opponentLevel, int $pokemonLevel, BattleType $battleType, bool $isKnockout): int` = 250 × clamp(opponentLevel ÷ pokemonLevel, 0.1, 3) × battle bonus × 1.5 when it knocked the opponent out, rounded down (Spec 1, "XP").
- Config `game.experience`: `base` (250), `min_ratio` (0.1), `max_ratio` (3), `battle_bonuses` (wild 1, trainer 1.5, gym 2, elite four 2, battle tower 1), `knockout_multiplier` (1.5), `xp_share_ratio` (0.5, used by task 5.10).
- `App\Pokemon\Actions\AwardExperienceAction::handle(Pokemon $pokemon, int $experience): void`: adds XP, updates `level` with `levelForExperience()`, stops at level 100.
- When a battle ends with `WON`, every player combatant with `has_entered_field` gets the sum, over each defeated opponent Pokémon, of `experienceForDefeating()` (with the knockout bonus for the opponents it knocked out). Track who knocked out each opponent in the state. Battle Tower battles don't pay here: task 6.12 saves their XP in the run.

**Tests:** `ExperienceCalculatorTest` with every row of Spec 1's XP table, the 0.1 and 3 caps and each battle bonus; XP added; level goes up; level 100 gains nothing; only Pokémon that entered the field gain XP; the knockout bonus; several defeated opponents add up.

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

**Mock-up:** `docs/mockups/battle.html` (state "Battle, choosing a move")

**Build:**
- Route `GET /battle` named `battle.web.show` → `App\Battle\Http\Web\Controllers\BattleController@show`; shows the user's `ONGOING` battle or redirects to the Dashboard.
- `App\Battle\Http\Web\PageProps\BattleShowPageProps`: both active combatants (sprite, name, level, boost, shiny, ailment), player HP as numbers, opponent HP as a percentage only, team dots, active moves (name, type, power), log.
- Page `resources/js/app/Battle/pages/Battle/index.vue` with the Spec 1 layout; stacks on phones.
- The sidebar already has a placeholder "Battle" item pointing to `#`. Turn it into a collapsible group, like "Your Pokémon", with one sub-item for now: "Current battle" → `battle.web.show`. Spec 6 adds Trainers, Gyms and Battle Tower to the same group (tasks 6.6, 6.9, 6.13). While a battle is ongoing, "Current battle" shows a dot.

**Mock-up:** `docs/mockups/layout.html` (the "Battle" group)

**UI suggestions** (ideas, not requirements):
- Layout: a CSS grid with two `Card`s, opponent at the top right and player at the bottom left; one column on phones (opponent, player, moves, log).
- Each Pokémon card: `SpeciesSprite`, `PokemonDisplayName`, `PokemonGenderIcon`, level and boost as `Badge`s, ailment as a small coloured `Badge` ("PAR", "BRN").
- HP: a `Progress` bar coloured by HP left (green above 50%, yellow 20–50%, red below 20%). Player card shows "410 / 520"; opponent card shows only "62%".
- Team: a row of small dots (filled = can fight, outlined = fainted), with a `Tooltip` naming each Pokémon.
- Moves: a 2×2 grid of large outline `Button`s, each with `MoveNameLabel`, a type badge and `MovePowerLabel`.
- Log: a `Card` with a fixed height and its own scroll, newest line at the bottom, scrolled down automatically.
- Header: `AppPageHeader` ("Wild battle") with the "Give up" button on the right (task 1.21).

**Tests:** feature test for the props, including that opponent HP is a percentage only; redirect when there is no battle.

**Done when:** page shows a battle started with `battle:start-wild`.

### 1.20 Battle page: take turns

**Goal:** the player can fight.

**Mock-up:** `docs/mockups/battle.html` (state "Battle, choosing a move"; click the moves)

**Build:**
- Route `POST /battle/turn` named `battle.web.turn` → `TakeTurnController` with `TakeTurnRequest` (`move_slot` 1–4).
- Move buttons submit the turn; super-effective moves show a hint (from `typeMultiplier`).
- The HP bar slides down (the only animation).

**UI suggestions** (ideas, not requirements):
- While a turn is being sent, disable all move buttons and show a spinner on the clicked one, so a double click can't send two turns.
- Effectiveness hint on move buttons: a small arrow-up icon for super effective, a muted arrow-down for not very effective, a crossed-out icon for no effect, each with a `Tooltip` explaining it.
- HP bar: animate its width with a Tailwind transition (`transition-[width] duration-500`).
- Highlight new log lines for a moment (a light background that fades).
- Errors (for example the battle already ended) as a toast through the existing toaster.

**Tests:** feature test: a valid turn updates the battle; invalid slot is rejected; browser test: clicking a move updates HP and the log.

**Done when:** a full battle can be played to the end from the page, except sending out and giving up.

### 1.21 Battle page: send out, give up and end screen

**Goal:** finish the battle flow on the page.

**Mock-up:** `docs/mockups/battle.html` (states "Pokémon fainted: send out", "Give up confirmation", "Battle won", "Gave up / lost")

**Build:**
- Route `POST /battle/send-out` (`battle.web.send-out`) → `SendOutPokemonController`; a dialog lists the remaining team (portrait, level, HP) when a Pokémon faints.
- Route `POST /battle/forfeit` (`battle.web.forfeit`) → `ForfeitBattleController`; "Give up" button with a confirmation.
- End screen: won or lost, XP gained per Pokémon, level-ups, and a button back to the Dashboard.

**UI suggestions** (ideas, not requirements):
- Send-out: a `Dialog` that can't be closed (no close button, no click outside) listing the team as selectable rows: `SpeciesPortrait`, name, level, HP `Progress`. Fainted Pokémon are greyed out and disabled. One primary button: "Send out".
- Give up: a destructive `Button` that opens a confirmation dialog (shadcn-vue `alert-dialog`, added with the shadcn-vue CLI) with "Keep fighting" (secondary) and "Give up" (destructive).
- End screen: the moves area is replaced by a result `Card`: a large "You won!" or "You lost" title; one row per Pokémon that fought with portrait, "+300 XP", an XP `Progress` bar and a "Level up! 36 → 37" `Badge`; one primary button, "Back to Dashboard".

**Tests:** feature tests for both routes; browser test for the faint dialog and giving up.

**Done when:** a battle can be played from start to end screen on the page.

---

## 2. Spec 2 — Catching and exploration

Read [02-catching-and-exploration.md](02-catching-and-exploration.md) first. Needs Spec 1 tasks done (battles, `RandomHelper`, `PokemonCaught` event). Exploring lives in a new domain, `src/App/Exploration`; catching lives in `src/App/Battle`. NPC trainers met while exploring come with Spec 6.

### 2.1 Mark Ultra Beasts

**Goal:** the Beast Ball can tell Ultra Beasts apart.

**Build:**
- Migration `017_add_is_ultra_beast_to_species_table.php`: `boolean('is_ultra_beast')->default(false)` after `is_gmax`.
- Seed files: `"is_ultra_beast": true` on Nihilego, Buzzwole, Pheromosa, Xurkitree, Celesteela, Kartana, Guzzlord, Poipole, Naganadel, Stakataka and Blacephalon; `false` on every other species. `SpeciesSeeder` reads it.
- `Species` model: cast to boolean. `SpeciesTrait::ULTRA_BEAST` with label "Ultra Beast", wherever the other traits are listed (Pokédex filters).

**Tests:** seeder test: exactly 11 Ultra Beasts.

**Done when:** column, data and trait exist; tests pass.

### 2.2 Wild data on species

**Goal:** every species knows whether it can appear in the wild, at which evolution stage, and at which levels. Calculated once while seeding.

**Build:**
- Migration `018_add_wild_data_to_species_table.php`: `unsignedTinyInteger('evolution_stage')->default(1)`, nullable `unsignedTinyInteger('wild_min_level')` and `wild_max_level` (null = never in the wild).
- `App\Exploration\Mechanics\WildSpawnCalculator` (reads loaded `previousEvolutions` relations, no queries):
  - `evolutionStage(Species $species): int`: 1 + the stage of the previous evolution; baby previous evolutions do not count (Pikachu is stage 1).
  - `levelRange(Species $species): ?array` returning `[min, max]`:
    - Mega, G-Max or baby → `null`.
    - Legendary or mythical → 50–70.
    - Stage 1 → 2–12.
    - Evolved by level → `min_level` to `min_level` + 4.
    - Evolved another way → min = the larger of 18 and the previous stage's `wild_min_level`; max = min + 4.
    - With several ways to evolve into a species, use the lowest result.
- Config `game.exploration`: `unevolved_levels` ([2, 12]), `legendary_levels` ([50, 70]), `other_evolution_min_level` (18), `evolved_level_spread` (4).
- Seeder `SpeciesWildDataSeeder`, run after species and evolutions, saves the three columns using the calculator.

**Tests:** `tests/App/Exploration/Unit/Mechanics/WildSpawnCalculatorTest.php`: Charmander 2–12 stage 1; Charizard 36–40 stage 3; Raichu 18–22; Vileplume never below Gloom's minimum; Pikachu stage 1; Mewtwo 50–70; Pichu, Mega Charizard X and G-Max Pikachu → `null`.

**Done when:** columns are filled after `migrate:fresh --seed`; tests pass.

### 2.3 Areas

**Goal:** the 9 areas exist in code.

**Build:**
- Enum `Shared\Enums\Area`: `MEADOW`, `FOREST`, `COAST`, `VOLCANO`, `POWER_PLANT`, `CAVE`, `SNOWY_PEAK`, `HAUNTED_TOWER`, `ANCIENT_RUINS` (values in kebab-case, for example `power-plant`).
- `label(): string` and `types(): array` (the two `PokemonType`s from the Spec 2 table).

**Tests:** every `PokemonType` belongs to exactly one area.

**Done when:** enum exists; tests pass.

### 2.4 Which species live in an area

**Goal:** list the species that can appear in an area.

**Build:**
- `App\Exploration\Queries\AreaSpeciesQuery::get(Area $area): Collection`: species with at least one of the area's types and a non-null `wild_min_level`. Ordered by `pokedex_order`.

**Tests:** includes a matching dual-type species; excludes other types, Megas, G-Max and babies.

**Done when:** query exists; tests pass.

### 2.5 Roll an encounter

**Goal:** pick the wild Pokémon for one explore.

**Build:**
- Move `game.incubation.shiny_odds` to `game.pokemon.shiny_odds` (eggs and the wild share it; update `PlaceEggAction`).
- Config `game.exploration`: `legendary_odds` (500), `stage_weights` ([1 => 85, 2 => 12, 3 => 3]).
- `App\Exploration\Mechanics\EncounterRoller::roll(Collection $areaSpecies): NewPokemon`, using `RandomHelper`:
  1. 1 in 500: a legendary or mythical, if the area has any.
  2. Otherwise a stage from the weights; if the area has no species at that stage, fall back to the next lower stage.
  3. A species picked evenly from that group (legendaries and mythicals excluded in this step).
  4. Level between the species' `wild_min_level` and `wild_max_level`; shiny with 1 / `shiny_odds`; gender from `getRandomGender()`.

**Tests:** with `RandomHelper::fake()`: legendary roll, each stage, fallback to a lower stage, level within range, shiny.

**Done when:** roller exists; tests pass.

### 2.6 Save the encounter and explore

**Goal:** pressing Explore (or Keep exploring) stores one wild Pokémon for the player.

**Build:**
- Migration `019_create_encounters_table.php`: `uuid id`, `foreignIdFor(User)` unique, `area` enum, `foreignIdFor(Species)`, `level`, `is_shiny`, `gender` enum, timestamps.
- Model `Shared\Models\Encounter` (with factory), `User::encounter(): HasOne`, `Encounter::toNewPokemon(): NewPokemon`.
- `App\Exploration\Actions\ExploreAction::handle(User $user, Area $area): Encounter`: refuses with `BattleAlreadyInProgressException` when a battle is ongoing; replaces any existing encounter with a new roll from `EncounterRoller`.

**Tests:** creates one encounter; a second explore replaces it (still one row); refused during a battle.

**Done when:** table, model and action exist; tests pass.

### 2.7 Battle the encounter

**Goal:** turn the stored encounter into a wild battle.

**Build:**
- `BattleState`: add `?Area $area` (null outside wild battles), saved by `AsBattleState`.
- `StartWildBattleAction::handle()`: add an `?Area $area = null` parameter and store it.
- `App\Exploration\Actions\BattleEncounterAction::handle(User $user): Battle`: throws `App\Exploration\Exceptions\NoEncounterException` without an encounter; starts the battle with the encounter's Pokémon and area; deletes the encounter.

**Tests:** battle created with the encounter's species, level, shiny and area; encounter deleted; refused without an encounter.

**Done when:** tests pass.

### 2.8 Catch chance

**Goal:** the Spec 2 catch formula.

**Build:**
- Config `game.catching`: `base_chance` (0.10), `legendary_base_chance` (0.01), `low_hp_bonus` (1.5), `ailment_multipliers` (sleep and freeze 2, paralysis, burn and poison 1.5).
- `App\Battle\Mechanics\CatchChanceCalculator::calculate(Species $species, int $currentHp, int $maxHp, ?Ailment $ailment, float $ballMultiplier): float`: base × ball × (1 + 1.5 × (1 − HP ratio)) × ailment, capped at 1.0.

**Tests:** every row of the Spec 2 example table.

**Done when:** calculator exists; tests pass.

### 2.9 Ball multipliers: basic balls

**Goal:** balls without conditions.

**Build:**
- `pokeballs.json`: `catch_rate` holds the multiplier when no condition applies: Poké 1, Great 1.5, Ultra 2, Safari, Sport and Park 1.5, collectible balls 1, conditional balls 1, Beast Ball 0.1.
- `App\Battle\Mechanics\BallMultiplierCalculator::multiplier(Pokeball $pokeball, CatchContext $context): float`: returns `catch_rate`; `isGuaranteed(Pokeball $pokeball): bool` is true for the Master Ball.
- Value object `Shared\ValueObjects\CatchContext`: `int $turn`, `?Area $area`, `bool $isNight`, `Species $wildSpecies`, `int $wildLevel`, `PokemonGender $wildGender`, `Species $activeSpecies`, `int $activeLevel`, `PokemonGender $activeGender`, `bool $playerOwnsSpecies`.

**Tests:** basic balls return their multiplier; Master Ball is guaranteed.

**Done when:** data, calculator and value object exist; tests pass.

### 2.10 Ball multipliers: conditional balls

**Goal:** the conditions from the Spec 2 ball table.

**Build:** in `BallMultiplierCalculator::multiplier()`, a `match` on the ball's slug for Quick, Timer, Repeat, Nest, Level, Dusk, Dream (needs the wild ailment: add `?Ailment $wildAilment` to `CatchContext`), Love, Moon (species evolves with `moon-stone`), Net, Lure, Dive, Heavy, Fast and Beast. Config `game.catching.night_starts_at` (18) and `night_ends_at` (6).

**Tests:** for each ball, one case where the condition applies and one where it does not.

**Done when:** tests pass.

### 2.11 Add items to the inventory

**Goal:** a reusable way to give a player items (starter balls now, shop and missions later).

**Build:**
- `App\Inventory\Actions\AddInventoryItemAction::handle(User $user, EvolutionItem|Pokeball $item, int $quantity = 1): void`: creates or increases the stack; throws `App\Inventory\Exceptions\InventoryStackFullException` above `game.inventory.max_stack`.

**Tests:** new stack; existing stack grows (still one row); over the limit is refused; quantity below 1 is refused.

**Done when:** action and exception exist; tests pass.

### 2.12 Throw a ball

**Goal:** catching in a wild battle.

**Build:**
- Extract the opponent's half of a turn from `TakeTurnAction` (its move, status effects, end-of-turn damage, fainting) into `App\Battle\Actions\OpponentActsAction::handle(Battle $battle): Battle`, used by both actions.
- `CreatePokemonAction`: add `bool $joinPartyIfRoom = false`; when true, the Pokémon joins the party if it has fewer than `game.user.max_party_size` (new config, 6).
- `App\Battle\Actions\ThrowBallAction::handle(Battle $battle, Pokeball $pokeball): Battle`:
  - Refuses outside `ONGOING` wild battles or while awaiting a send-out (`App\Battle\Exceptions\NotAWildBattleException`, `BattleNotReadyForTurnException`).
  - Refuses with `BoxFullException` before using the ball when the Box is full.
  - Uses one ball (`RemoveInventoryItemAction`).
  - Caught (guaranteed or `RandomHelper::chance()` with the catch chance): creates the Pokémon (`joinPartyIfRoom: true`, the thrown ball), status `CAUGHT`, logs "Gotcha! … was caught!", dispatches `PokemonCaught`.
  - Not caught: logs "Oh no! The Pokémon broke free!", then `OpponentActsAction`.

**Tests:** catch; break free then the opponent attacks; ball used either way; Master Ball always catches; joins party or Box; refused when not wild, Box full or no ball.

**Done when:** tests pass.

### 2.13 Starter and free balls at registration

**Goal:** new players start with a Pokémon and balls.

**Build:**
- Config `game.registration`: `starters` (the 27 first-stage starters: Bulbasaur, Charmander, Squirtle … Sprigatito, Fuecoco, Quaxly, as slugs), `starter_level` (5), `starting_items` (`poke-ball` 20, `great-ball` 5).
- `RegisterUserAction::handle()`: add `Species $starter`; in one transaction creates the user, the starter (in the party, Poké Ball, random gender, not shiny) and the items (`AddInventoryItemAction`).
- `RegistrationRequest`: required `starter` slug, must be in the starters list.

**Tests:** registration creates the user, one Pokémon in the party at level 5 and the two ball stacks; a starter outside the list is rejected.

**Done when:** tests pass.

### 2.14 Registration page: choose a starter

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

**Goal:** the Pokédex shows this game's catch rate, not the main games'.

**Build:**
- Pokédex overview shows "10%" (or "1%" for legendaries and mythicals) from `game.catching`, or "Not found in the wild" when `wild_min_level` is null.
- Migration `020_drop_catch_rate_from_species_table.php`; remove `catch_rate` from the seeder, model, `isCapturable()` and `getCatchRatePercentage()`.

**UI suggestions** (ideas, not requirements):
- Keep the existing `AppInfoCardRow` in the overview card: "Catch rate: 10%".
- For species never in the wild, show muted text "Not found in the wild", with a `Tooltip` explaining why (Megas and G-Max evolve, babies hatch).

**Tests:** update `PokedexShowTest` for the three cases.

**Done when:** tests pass.

### 2.16 Explore page

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

**Goal:** explore an area and see the wild Pokémon.

**Mock-up:** `docs/mockups/area.html` (states "Wild Pokémon found", "Shiny encounter", "Legendary or mythical encounter", "No encounter yet", "A battle is in progress")

**Build:**
- Route `GET /explore/{area}` named `exploration.web.show` → `ExploreController@show` with `ExploreShowPageProps`: the area, and the player's encounter if it is in this area (sprite, name, gender, level, types, shiny, legendary).
- Route `POST /explore/{area}` named `exploration.web.explore` → `ExploreAreaController`: calls `ExploreAction`, redirects to the area page. Used by both Explore and Keep exploring.
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

**Goal:** catching from the battle page.

**Mock-up:** `docs/mockups/battle.html` (states "Ball picker open" and "Pokémon caught")

**Build:**
- Route `POST /battle/throw-ball` named `battle.web.throw-ball` → `ThrowBallController` with `ThrowBallRequest` (`pokeball` slug, owned by the player).
- `BattleShowPageProps`: in wild battles, the player's balls (sprite, name, quantity, current multiplier from `BallMultiplierCalculator`, `isBoosted` when above its `catch_rate`).
- "Throw ball" button under the moves (wild battles only) opens the ball picker; boosted balls show a "Boosted" tag.
- Catch dialog: the Pokémon, where it went (Party or Box), buttons "View Pokémon" (to the Box until the Pokémon page exists) and "Keep exploring" (back to the area page).

**UI suggestions** (ideas, not requirements):
- An outline "Throw ball" `Button` with a ball icon, full width under the move grid (wild battles only).
- Ball picker: a `Popover` on desktop (a `Dialog` on phones) listing one row per ball: `ItemSprite`, name, "×42" owned, and the current multiplier ("×3.5"). Boosted balls get a green "Boosted" `Badge` and are sorted first.
- Clicking a row throws right away, with a processing state on the row.
- Catch dialog: the Pokémon's sprite, "Gotcha! Shiny Caterpie was caught!", a `Badge` saying "Sent to Party" or "Sent to Box", then a primary "Keep exploring" and an outline "View Pokémon".
- A ball that fails only adds a log line; no extra popup.

**Tests:** feature tests for the route and props; browser test: throw a ball and see the catch dialog.

**Done when:** a player can explore, battle, throw balls and catch a Pokémon entirely from the pages.

---

## 3. Spec 3 — Money, inventory and shop

Read [03-money-inventory-and-shop.md](03-money-inventory-and-shop.md) first. Money lives in a new domain, `src/App/Economy`; the shop in `src/App/Shop`; the inventory page in the existing `src/App/Inventory`. Using items on a Pokémon (TMs, boost stones, evolution items) comes with Spec 4; the XP Share switch comes with Spec 5.

### 3.1 Money

**Goal:** players have Pokédollars.

**Build:**
- Migration `021_add_money_to_users_table.php`: `unsignedInteger('money')->default(0)`.
- `User` model cast; `UserFactory` state `withMoney(int $money)`.
- `App\Economy\Actions\AddMoneyAction::handle(User $user, int $amount): void` and `SpendMoneyAction::handle(User $user, int $amount): void`. Spending more than the player has throws `App\Economy\Exceptions\NotEnoughMoneyException`. Amounts below 1 throw `InvalidArgumentException`. Both lock with a `LockKeys` method and refresh the user first.
- Config `game.economy.starting_money` (2000); `RegisterUserAction` (task 2.13) gives it.

**Tests:** add; spend; spending too much is refused and changes nothing; new players start with ₽ 2,000.

**Done when:** tests pass.

### 3.2 Show money in the layout

**Goal:** the player always sees their money.

**Mock-up:** `docs/mockups/layout.html` (all states)

**Build:** `HandleInertiaRequests` shares `auth.user.money`. The sidebar shows it.

**UI suggestions** (ideas, not requirements):
- A small row in the sidebar header or user area: a coin icon and "₽ 12,450" in `font-medium`, formatted with thousands separators (`Intl.NumberFormat`).
- On phones, where the sidebar collapses, show it in the top bar next to the menu button.
- A shared component `MoneyAmount` (`shared/components/game/`) used everywhere money appears, so the format stays the same.

**Tests:** feature test: the shared prop holds the user's money.

**Done when:** money shows on every page.

### 3.3 Prices

**Goal:** every item the shop sells has a price; earned-only items have none.

**Build:**
- Migration `022_add_price_to_pokeballs_and_evolution_items_tables.php`: nullable `unsignedInteger('price')` on both tables (null = never sold).
- Seed data: Poké Ball 200, Great Ball 600, Ultra Ball 1,200; conditional balls (Net, Dive, Nest, Repeat, Timer, Dusk, Quick, Dream, Lure, Level, Moon, Heavy, Fast, Love, Beast) 1,000; Master, Safari, Sport, Park, Luxury, Premier, Heal, Friend and Cherish null. Every evolution item (stones and items such as Metal Coat or King's Rock) 3,000, except Mega Stones, which are null.
- `App\Shop\Mechanics\TmPriceCalculator::price(Move $move): ?int`: null for Z-Moves and Dynamax moves; 3,000 for status moves (no power); 2,000 under 60 power; 5,000 for 60–89; 10,000 for 90+. Config `game.shop.tm_prices`.

**Tests:** calculator boundaries (59, 60, 89, 90, status, Z-Move); seeder test: Master Ball and Mega Stones have no price; every other evolution item costs 3,000.

**Done when:** tests pass.

### 3.4 TMs and special items in the inventory

**Goal:** the inventory can hold TMs, boost stones and key items.

**Build:**
- Migration `023_create_special_items_table.php`: `uuid id`, `slug` unique, `name`, `description`, nullable `sprite`, `category` enum.
- Enum `Shared\Enums\SpecialItemCategory`: `BOOST_STONE`, `KEY_ITEM`. Model `Shared\Models\SpecialItem` (with factory); `SpecialItemSeeder` seeds `boost-stone` and `xp-share`.
- `InventoryItemType`: add `TM` (item is a `Move`) and `SPECIAL_ITEM`; extend `fromModel()` and the morph map.
- `AddInventoryItemAction` and `RemoveInventoryItemAction` accept `EvolutionItem|Pokeball|Move|SpecialItem`.

**Tests:** add and remove a TM and a boost stone; `fromModel()` for each model.

**Done when:** tests pass.

### 3.5 Today's TMs

**Goal:** 10 TMs for sale each day, the same for every player.

**Build:**
- Migration `024_create_tm_rotations_table.php`: `uuid id`, `date` unique, `json move_ids`. Model `Shared\Models\TmRotation`.
- `App\Shop\Actions\GetTodaysTmRotationAction::handle(): TmRotation`: returns today's rotation (server date), creating it on the first request of the day with 10 random TMs that have a price (`RandomHelper`), under a lock so two requests can't create two.
- Config `game.shop.daily_tm_count` (10).

**Tests:** creates one rotation per day (second call returns the same one); 10 distinct moves; no Z-Moves or Dynamax moves; a new day gives a new rotation.

**Done when:** tests pass.

### 3.6 Buy items

**Goal:** spend money on shop items.

**Build:**
- `App\Shop\Actions\BuyItemAction::handle(User $user, Pokeball|EvolutionItem|Move $item, int $quantity): void`
  - The item must be for sale: a price, and for TMs, in today's rotation. Otherwise `App\Shop\Exceptions\ItemNotForSaleException`.
  - The stack must have room (`InventoryStackFullException`) and the player enough money (`NotEnoughMoneyException`), both checked before anything changes.
  - In one transaction: `SpendMoneyAction`, then `AddInventoryItemAction`.

**Tests:** buy one and many; not for sale; TM not in today's rotation; not enough money; stack full; nothing changes when refused.

**Done when:** tests pass.

### 3.7 Sell items

**Goal:** sell shop items back for half their price.

**Build:**
- `App\Shop\Actions\SellItemAction::handle(User $user, Pokeball|EvolutionItem|Move $item, int $quantity): int` returns the money earned.
  - Earned-only items (no price) throw `App\Shop\Exceptions\ItemNotSellableException`.
  - Earns floor(price × `game.shop.sell_back_ratio` (0.5) × quantity). TMs use `TmPriceCalculator`, whether or not they are in today's rotation.
  - In one transaction: `RemoveInventoryItemAction`, then `AddMoneyAction`.

**Tests:** sell one and many; earned-only refused; selling more than owned refused (`InsufficientInventoryException`); money and inventory both change.

**Done when:** tests pass.

### 3.8 Incubators cost money

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

**Goal:** see what the shop sells.

**Mock-up:** `docs/mockups/shop.html` (states "Balls tab", "Evolution items tab", "TMs tab")

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

**Goal:** a locked Pokémon can't be released (or traded, Spec 7) by accident.

**Build:**
- Migration `025_add_is_locked_to_pokemon_table.php`: `boolean('is_locked')->default(false)`.
- `Pokemon` cast; `PokemonFactory` state `locked()`.
- `App\Pokemon\Actions\SetPokemonLockAction::handle(Pokemon $pokemon, bool $isLocked): void`.

**Tests:** lock and unlock.

**Done when:** tests pass.

### 4.2 Add to and remove from the party

**Goal:** party changes from the Pokémon page.

**Build:**
- `App\Pokemon\Actions\AddToPartyAction::handle(Pokemon $pokemon): void`: throws `App\Pokemon\Exceptions\PartyFullException` at `game.user.max_party_size` (6).
- `App\Pokemon\Actions\RemoveFromPartyAction::handle(Pokemon $pokemon): void`: throws `App\Pokemon\Exceptions\PartyCannotBeEmptyException` for the last party Pokémon, since battles need a party.
- `App\Pokemon\Actions\SwapPartyPokemonAction::handle(Pokemon $joining, Pokemon $leaving): void`: `$leaving` must be in the party and `$joining` must not; in one transaction `$leaving` goes to the Box and `$joining` takes its place.
- Both refuse during an ongoing battle (`BattleAlreadyInProgressException`): the battle uses a snapshot of the party.

**Tests:** add; party full; remove; last one refused; swap (party size unchanged); swap with a Pokémon outside the party refused; refused during a battle.

**Done when:** tests pass.

### 4.3 Release a Pokémon

**Goal:** release is permanent and gives nothing back.

**Build:**
- `App\Pokemon\Actions\ReleasePokemonAction::handle(Pokemon $pokemon): void`: deletes the Pokémon (its known moves go with it).
  - Refuses locked Pokémon (`App\Pokemon\Exceptions\PokemonLockedException`), the last party Pokémon (`PartyCannotBeEmptyException`) and any release during an ongoing battle.

**Tests:** released (row gone, moves gone); locked refused; last party Pokémon refused; refused during a battle.

**Done when:** tests pass.

### 4.4 Teach a TM

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

**Goal:** the chance a boost stone fails, and the roll.

**Build:**
- `App\Pokemon\Mechanics\BoostStoneCalculator::failureChance(int $currentBoost): float`: 0 below +20; from +20, (currentBoost − 19) / 100, so +20 → +21 fails 1% and +99 → +100 fails 80%. Config `game.boost.safe_until` (20), `game.boost.failure_step` (0.01).
- `App\Pokemon\Mechanics\BoostStoneRoller::roll(int $currentBoost): bool` (true = success), with `RandomHelper`.

**Tests:** chances at +0, +19, +20, +60, +99; roller with `RandomHelper::fake()`.

**Done when:** tests pass.

### 4.6 Use boost stones

**Goal:** use one or several boost stones in one go.

**Build:**
- Value object `Shared\ValueObjects\BoostResult`: `int $stonesUsed`, `int $successes`, `int $failures`, `int $boostBefore`, `int $boostAfter`.
- `App\Pokemon\Actions\UseBoostStonesAction::handle(Pokemon $pokemon, int $maxStones, ?int $targetBoost = null): BoostResult`
  - Uses stones one at a time until `$maxStones` are used, `$targetBoost` is reached, the Pokémon is at +100, or the player runs out.
  - Refuses at +100 (`App\Pokemon\Exceptions\MaxBoostReachedException`) and with no stones (`InsufficientInventoryException`).
  - One transaction and a lock; each stone is removed from the inventory whether it succeeds or fails.

**Tests:** one stone; stops at target; stops at max stones; stops at +100; runs out of stones; failures still use stones (faked rolls).

**Done when:** tests pass.

### 4.7 Pokémon page: route and props

**Goal:** the data for the page.

**Build:**
- Route `GET /your-pokemon/{pokemon}` named `pokemon.web.show` → `App\Pokemon\Http\Web\Controllers\PokemonController@show`. Only the owner may open it (404 for anyone else).
- `App\Pokemon\Http\Web\PageProps\PokemonShowPageProps`:
  - Header: species, types, gender, shiny, level, boost, XP progress to the next level, ball, catch date, original trainer, `is_locked`, `in_party`, party size.
  - Stats: HP, Attack and Defense from `getStats()`, each with its parts (level, boost, shiny) for the hover breakdown.
  - Moves: active moves by slot and the known-moves pool (name, type, power, category).
  - Evolution options: each `SpeciesEvolution` of the species with the target species and `EvolutionRequirementChecker::check()`'s reason (null = allowed).
  - Items: owned TMs (with "already known" flags) for Teach TM, boost stones (count, current failure chance) for Boost, and for each evolution option whether the player owns the item it needs.
- Box card "View" and the Party list link to the page.

**Tests:** feature test for the props; another user's Pokémon gives 404; guest redirected to login.

**Done when:** the page loads with all props.

### 4.8 Pokémon page: header and stats

**Goal:** the read-only part of the page.

**Mock-up:** `docs/mockups/pokemon.html` (state "Overview")

**Build:** page `resources/js/app/Pokemon/pages/Pokemon/Show/index.vue` with the header (portrait, name, badges, XP bar, catch details), the action buttons (wired in later tasks) and the stats card.

**UI suggestions** (ideas, not requirements):
- `AppBreadcrumbs` (Box › Shiny Charmeleon), then a header `Card`: large `SpeciesSprite` on the left, details on the right; stacked on phones.
- Badges for level, boost and shiny (gold); `PokemonTypeBadges`; `PokemonGenderIcon`.
- Catch details: "Caught in a [sprite] Ultra Ball · 3 Nov 2026", with the ball's `ItemSprite` right before the ball's name, the same way the Pokédex's Evolution Chain card shows an evolution item.
- XP: a thin `Progress` with "7,200 / 10,000 XP to Lv 37"; at level 100, "Max level" instead.
- Stats: an `AppInfoCard` with one `AppInfoCardRow` per stat; a `Tooltip` on each value lists its parts ("Level 36: ×0.66 · Boost +14: ×1.14 · Shiny: ×1.25").
- Action row: "Evolve" (primary, only when an evolution is allowed), "Boost" (outline, opens the boost stones dialog), party toggle (outline), lock toggle (ghost with a lock icon), and "Release" (destructive outline, at the end).

**Tests:** browser test: the page shows the Pokémon's name, level, boost and stats.

**Done when:** the page matches the mock-up's overview.

### 4.9 Pokémon page: moves

**Goal:** swap active moves and teach TMs from the page.

**Mock-up:** `docs/mockups/pokemon.html` (states "Overview", "Swap a move", "Teach a TM")

**Build:**
- Route `POST /your-pokemon/{pokemon}/active-moves` named `pokemon.web.set-active-move` → `SetActiveMoveController` (`move_id`, `slot` 1–4).
- Route `POST /your-pokemon/{pokemon}/teach-tm` named `pokemon.web.teach-tm` → `TeachTmController` (`move_id`).
- `App\Pokemon\Actions\ReorderActiveMovesAction::handle(Pokemon $pokemon, array $moveIds): void`: `$moveIds` must be exactly the current active moves in their new order (otherwise `InvalidArgumentException`); saves slots 1, 2, 3… in that order. Route `POST /your-pokemon/{pokemon}/active-moves/order` named `pokemon.web.reorder-active-moves` → `ReorderActiveMovesController` (`move_ids` array).
- The slot order is the order of the move buttons on the battle page (task 1.19).
- Moves card: four slots with Swap buttons; the known-moves pool below; a "Teach TM" button.

**UI suggestions** (ideas, not requirements):
- Active slots: a numbered list (1–4, the battle order). Each row: a drag handle, the number, `MoveNameLabel`, type badge, `MovePowerLabel`, a "STAB" badge for damaging moves of the Pokémon's own type, up and down arrow buttons, and a ghost "Swap" button. An empty slot shows a dashed outline and "Empty slot".
- Drag and drop (desktop): drag a slot by its handle to reorder; drag a known move onto a slot to replace it. Every drag action also has a non-drag way: the arrows reorder, Swap replaces. Keyboard and touch users rely on those.
- Drag and drop needs a library for touch support and smooth sorting (for example `vue-draggable-plus`, built on SortableJS). Adding it needs approval (AGENTS.md: no new dependencies without approval); without it, use the native HTML drag events on desktop and the arrow buttons everywhere.
- Swap: a `Dialog` listing the known moves (the active ones marked "In slot 2"), with a search input when the pool is long. Picking one swaps it in.
- Known moves: a grid of small cards (name, type badge, power, STAB), sorted by name; moves already active are dimmed with "In slot 2". When the pool grows past about 12 moves, add the same search and type chips as the Teach TM dialog.
- Teach TM: a `Dialog` with a search input (name or number; players will own hundreds of TMs) and type filter chips (only types the player owns TMs of, plus "All types"), over a scrolling list of owned TMs sorted by code. A wide dialog (about 720px) with the TMs in two columns on desktop, one on phones. Each option shows "TM 395 Ice Beam", its type badge, power and how many are owned; TMs the Pokémon already knows are disabled with "Already known"; Z-Move and Dynamax TMs show "Needs level 100" when not allowed. Teaching asks for a confirmation step inside the dialog, since the TM is used up.

**Tests:** `ReorderActiveMovesActionTest` (new order saved; a list that is not exactly the active moves is refused); feature tests for the three routes and their errors; browser test: swap a move and reorder with the arrows.

**Done when:** a player can swap moves and teach TMs from the page.

### 4.10 Pokémon page: boost stones

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

**Goal:** add a Pokémon to the party straight from the Box.

**Mock-up:** `docs/mockups/pokemon.html` (state "Party full: choose who to swap"; the same dialog)

**Build:**
- The Box card's menu (today "View" and "Pokédex") gets "Add to party" for Pokémon not in the party, and "Remove from party" for those in it, using the routes from task 4.12.
- "Add to party" with a full party opens `PartySwapDialog`; "Remove from party" opens the same confirmation as the Pokémon page.
- The card shows an "In party" badge.

**Tests:** browser test: add from the Box, and swap when the party is full.

**Done when:** party changes work from the Box.

---

## 5. Spec 5 — Missions

Read [05-missions.md](05-missions.md) first, including its "Starting content" and "Counting rules". Missions live in a new domain, `src/App/Missions`, and learn about the game only through events (task 0.4): they never call other domains, and other domains never call them. Goals whose feature is not built yet (trainers, gyms, the Battle Tower, trades) get their events when that feature is built.

### 5.1 Mission data

**Goal:** store achievements and each player's missions.

**Build:**
- Enums in `Shared\Enums`: `MissionKind` (`DAILY`, `ACHIEVEMENT`, `STREAK_BONUS`), `MissionDifficulty` (`EASY`, `MEDIUM`, `HARD`), `MissionCategory` (`CATCHING`, `BATTLING`, `COLLECTION`), `MissionGoal` (`CATCH_POKEMON`, `CATCH_POKEMON_OF_TYPE`, `CATCH_POKEMON_IN_AREA`, `CATCH_SHINY`, `CATCH_LEGENDARY`, `CATCH_POKEMON_AT_LEVEL`, `WIN_BATTLES`, `WIN_WILD_BATTLES`, `WIN_TRAINER_BATTLES`, `BEAT_GYM`, `EARN_REGION_BADGES`, `BECOME_CHAMPION`, `CLIMB_TOWER_FLOORS`, `HATCH_EGGS`, `EVOLVE_POKEMON`, `MEGA_EVOLVE`, `REACH_LEVEL`, `REACH_BOOST`, `TEACH_TMS`, `SPEND_MONEY`, `MAKE_TRADES`), each with `label()`.
- Value object `Shared\ValueObjects\MissionReward`: `int $money`, `array $items` (each: item type, slug, quantity, or `random` with a pool such as `normal-tm`, `mega-stone`, `z-move-tm`, `dynamax-tm`). Cast `AsMissionReward`.
- Migration `026_create_achievements_table.php`: `uuid id`, `slug` unique, `name`, `category` enum, `goal` enum, `json goal_parameters`, `target`, `json reward`, `sort_order`. Model `Shared\Models\Achievement`; `AchievementSeeder` from `database/seeders/files/achievements.json` (the Spec 5 list).
- Migration `027_create_user_missions_table.php`: `uuid id`, `foreignIdFor(User)`, `kind` enum, nullable `foreignIdFor(Achievement)`, nullable `date` (dailies), nullable `difficulty`, `goal`, `json goal_parameters`, `target`, `progress` default 0, `json reward`, nullable `completed_at`, nullable `claimed_at`, timestamps; unique `['user_id', 'achievement_id']`; index `['user_id', 'date']`. Model `Shared\Models\UserMission` (with factory): `isCompleted()`, `isClaimable()`.
- Migration `028_add_mission_columns_to_users_table.php`: `mission_streak` (default 0), nullable `streak_last_completed_on` date, nullable `last_reroll_on` date, `is_xp_share_enabled` (default false).

**Tests:** achievements seed from the file; casts round-trip; `isClaimable()` only when completed and not claimed.

**Done when:** tables, models, enums and seed data exist; tests pass.

### 5.2 Give rewards

**Goal:** one way to hand out money and items, used by missions and later by other features.

**Build:**
- `App\Missions\Mechanics\RewardRoller::roll(MissionReward $reward): MissionReward`: replaces each `random` item with a concrete one from its pool (`RandomHelper::pick`).
- `App\Missions\Actions\GrantRewardAction::handle(User $user, MissionReward $reward, bool $acceptOverflow = false): void`
  - Gives money (`AddMoneyAction`) and items (`AddInventoryItemAction`) in one transaction.
  - When an item would go over the stack limit, throws `App\Missions\Exceptions\RewardOverflowException` listing what wouldn't fit, unless `$acceptOverflow` is true; then it fills the stack and drops the rest.

**Tests:** money and items given; random pools resolved (faked); overflow refused with the list; overflow accepted caps at the limit.

**Done when:** tests pass.

### 5.3 Daily mission templates

**Goal:** turn templates into three concrete daily missions.

**Build:**
- `config/missions.php`: the Spec 5 daily templates, each with difficulty, goal, target range, parameter choices (`type`: any `PokemonType`; `area`: any `Area`; `level`: range) and reward; plus `enabled` so templates for unbuilt features stay off.
- `App\Missions\Mechanics\DailyMissionRoller::roll(): array`: one enabled template per difficulty, with target and parameters rolled (`RandomHelper`), the description filled in ("Catch 4 Water Pokémon").

**Tests:** one mission per difficulty; values within their ranges; disabled templates never picked.

**Done when:** tests pass.

### 5.4 Today's missions

**Goal:** every player has 3 dailies for today, created when first needed.

**Build:**
- `App\Missions\Actions\EnsureDailyMissionsAction::handle(User $user): Collection`: returns today's 3 dailies (server date), creating them with `DailyMissionRoller` on the first call of the day, under a lock.
- Also creates the player's achievement rows the first time (one `UserMission` per `Achievement`), so progress can be stored.
- `REACH_LEVEL` and `REACH_BOOST` rows start with the player's current best, so they complete at once if already reached.

**Tests:** creates 3 dailies once per day; a new day creates 3 new ones; achievements created once; reach goals start completed when already reached.

**Done when:** tests pass.

### 5.5 Advance progress

**Goal:** events move missions forward.

**Build:**
- `App\Missions\Mechanics\MissionGoalChecker::matches(UserMission $mission, MissionGoal $goal, array $context): bool`: the goal is the same and the parameters fit (type in the Pokémon's types, the same area, level at least the target, shiny, legendary or mythical).
- `App\Missions\Actions\AdvanceMissionsAction::handle(User $user, MissionGoal $goal, array $context, int $amount = 1): void`: calls `EnsureDailyMissionsAction`, then adds `$amount` to every open matching mission (today's dailies and achievements), capping at `target` and setting `completed_at` when reached. For `REACH_LEVEL` and `REACH_BOOST`, progress is the highest value seen, not a sum.
- `PokemonCaught` (task 0.4) gains `source` (`WILD` or `EGG`), `speciesId`, `types`, `area`, `level`, `isShiny`, `isLegendary`.
- Listener `App\Missions\Listeners\AdvanceMissionsOnPokemonCaught`: wild catches advance the `CATCH_*` goals; egg catches advance `HATCH_EGGS`.

**Tests:** matching and non-matching parameters; caps at target; completed once; reach goals use the highest value; egg catches don't count as catches.

**Done when:** catching advances the right missions.

### 5.6 Events for the other goals

**Goal:** the rest of today's built features advance missions.

**Build:** events and listeners for: `BattleWon` (battle type, from task 1.16) → `WIN_BATTLES`, `WIN_WILD_BATTLES`; `PokemonEvolved` (with `isMega`, from task 1.18) → `EVOLVE_POKEMON`, `MEGA_EVOLVE`; `PokemonLevelledUp` (task 1.17) → `REACH_LEVEL`; `PokemonBoosted` (task 4.6) → `REACH_BOOST`; `TmTaught` (task 4.4) → `TEACH_TMS`; `MoneySpent` (task 3.1) → `SPEND_MONEY` with the amount. One listener per event in `App\Missions\Listeners`. Trainer, gym, tower and trade goals are wired in their own specs.

**Tests:** each event advances its goal (one test per listener).

**Done when:** tests pass.

### 5.7 Claim a reward

**Goal:** the Claim button.

**Build:**
- `App\Missions\Actions\ClaimMissionAction::handle(UserMission $mission, bool $acceptOverflow = false): MissionReward`: refuses when not claimable (`App\Missions\Exceptions\MissionNotClaimableException`); rolls the reward, grants it (`GrantRewardAction`), sets `claimed_at`, returns what was given. Locked so a double click can't claim twice.

**Tests:** claim gives the reward once; not completed refused; already claimed refused; overflow refused unless accepted.

**Done when:** tests pass.

### 5.8 Reroll a daily

**Goal:** one free reroll per day.

**Build:**
- `App\Missions\Actions\RerollDailyMissionAction::handle(UserMission $mission): UserMission`: only today's unclaimed, uncompleted dailies; only if `last_reroll_on` is not today (`App\Missions\Exceptions\RerollAlreadyUsedException`). Replaces it with a new mission of the same difficulty (a different template when possible) and sets `last_reroll_on`.

**Tests:** reroll replaces the mission; second reroll the same day refused; completed missions refused; works again the next day.

**Done when:** tests pass.

### 5.9 Streak and bonus

**Goal:** finishing all 3 dailies on consecutive days builds a streak; every 7 days gives a bonus boost stone.

**Build:**
- When the third daily of a day completes (inside `AdvanceMissionsAction`): if `streak_last_completed_on` was yesterday, `mission_streak` + 1, otherwise 1; set `streak_last_completed_on` to today.
- Every multiple of 7 (`game.missions.streak_bonus_every`) creates a `STREAK_BONUS` mission, already completed, with the bonus reward (`game.missions.streak_bonus_reward`: 1 boost stone), claimed like any mission.
- The streak shown to the player is 0 when `streak_last_completed_on` is older than yesterday.

**Tests:** streak grows on consecutive days; resets after a missed day; bonus created at 7 and 14; shown streak drops to 0 after a gap.

**Done when:** tests pass.

### 5.10 XP Share

**Goal:** the XP Share key item works.

**Build:**
- `App\Inventory\Actions\SetXpShareAction::handle(User $user, bool $isEnabled): void`: needs the `xp-share` item (`InsufficientInventoryException`).
- Task 1.17's XP award: when `is_xp_share_enabled`, party Pokémon that did not enter the field get `game.experience.xp_share_ratio` (0.5) of the base XP.
- Route `POST /inventory/xp-share` named `inventory.web.xp-share` → `SetXpShareController` (`is_enabled`).

**Mock-up:** `docs/mockups/inventory.html` (states "Key items tab", "XP Share settings")

**UI suggestions** (ideas, not requirements):
- The XP Share tile in the Key items tab shows an "On" / "Off" badge; its dialog has a `Switch` (shadcn-vue `switch`) labelled "Share XP with the whole party", with one line explaining it.

**Tests:** switch on and off; refused without the item; XP split with and without it.

**Done when:** tests pass.

### 5.11 Missions page: daily missions

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

**Goal:** the Achievements tab.

**Mock-up:** `docs/mockups/missions.html` (state "Achievements")

**Build:** the achievements tab lists every achievement grouped by category, with progress and reward; finished-and-claimed ones go to the bottom of each group, dimmed. Claiming uses task 5.12's route.

**UI suggestions** (ideas, not requirements):
- One section per category (`AppSectionHeading`: Catching, Battling, Collection), with a count ("4 / 5").
- Compact rows instead of big cards: name, `Progress` with numbers ("37 / 100"), reward, and Claim when ready.

**Tests:** feature test for the grouped props.

**Done when:** the tab lists achievements and they can be claimed.

### 5.14 Something to claim: navigation dot

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

**Goal:** store hand-made trainers, gym leaders, Elite Four members and Champions.

**Build:**
- Enums in `Shared\Enums`: `TrainerRole` (`TRAINER`, `GYM_LEADER`, `ELITE_FOUR`, `CHAMPION`), `TrainerCategory` (`TOWNSFOLK`, `OUTDOORS`, `WATER`, `FIGHTERS`, `MYSTICS`, `EXPERTS`, with `label()`; only for `TRAINER` role) and `Region` (`KANTO` … `PALDEA`, with `label()` and `generation()`).
- Migration `029_create_trainers_table.php`: `uuid id`, `slug` unique, `name`, `trainer_class` ("Hiker", "Gym Leader"), nullable `sprite`, `role` enum, nullable `category` enum, nullable `region` enum, nullable `badge_name`, nullable `gym_type` (`PokemonType`), `position` (gym number or Elite Four order), `payout`.
- Migration `030_create_trainer_pokemon_table.php`: `uuid id`, `foreignIdFor(Trainer)`, `foreignIdFor(Species)`, `level`, `boost`, `is_shiny`, `gender`, `json move_slugs`, `position`.
- Models `Shared\Models\Trainer` (`team()` ordered by position) and `TrainerPokemon`, with factories.
- `TrainerSeeder` from `database/seeders/files/trainers.json`.

**Tests:** seeder loads a trainer with its team in order.

**Done when:** tables, models and seeder exist; tests pass.

### 6.2 Trainer content (data, no code)

**Goal:** fill `trainers.json`.

**Build:** about 30 hand-made trainers (Lv 5–100, payouts ₽ 500–20,000; shinies only on the hardest), then for each of the 9 regions its 8 gym leaders (2 to 6 Pokémon each, depending on the leader), Elite Four and Champion with their main-game teams, at the levels and boosts in Spec 6's "Details" section. Moves: each Pokémon's base moves unless the main-game team suggests better ones that exist as moves here.

**Done when:** `migrate:fresh --seed` loads every trainer; a seeder test checks each region has 8 gym leaders, 4 Elite Four members and 1 Champion.

### 6.3 Payout

**Goal:** the money formula.

**Build:** `App\Trainers\Mechanics\TrainerPayoutCalculator::payout(int $fullPayout, float $trainerAverageLevel, float $fieldAverageLevel): int` = full payout × (trainer average ÷ field average), capped between 10% and 100%, rounded down. Config `game.trainers.min_payout_ratio` (0.1).

**Tests:** every row of the Spec 6 table, plus the 10% floor.

**Done when:** tests pass.

### 6.4 Battles against trainers

**Goal:** the battle engine handles NPC teams.

**Build:**
- `BattleState`: add nullable `trainer_id`, nullable `trainer_name` and `bool $heals_after` (default true; the Tower sets false).
- `App\Battle\Actions\StartTrainerBattleAction::handle(User $user, BattleType $type, array $opponentTeam, ?Trainer $trainer, ?array $playerPokemonIds = null): Battle`: player side from `$playerPokemonIds` (in that order) or the whole party; opponent side from `$opponentTeam` (a list of `NewPokemon` plus boost and moves, or `TrainerPokemon` rows).
- Opponent AI: a random active move each turn (as wild battles); after a faint it sends out its next Pokémon automatically and the log says "Hiker Bob sent out Geodude!".
- `ThrowBallAction` already refuses non-wild battles (task 2.12).
- When a trainer battle is won, dispatch `BattleWon` with type and trainer, then the domain that started it pays and records (tasks 6.5, 6.8, 6.12).

**Tests:** opponent sends out the next Pokémon; win only when the whole opponent team faints; trainer name in the log.

**Done when:** tests pass.

### 6.5 Fight a trainer from the list

**Goal:** battle hand-made trainers and get paid.

**Build:**
- `App\Trainers\Actions\ChallengeTrainerAction::handle(User $user, Trainer $trainer): Battle`: only `TRAINER` role; starts a `TRAINER` battle with the party.
- Migration `031_create_trainer_victories_table.php`: `uuid id`, `foreignIdFor(User)`, `foreignIdFor(Trainer)`, `wins` (default 0), `first_won_at`; unique `['user_id', 'trainer_id']`. Model `TrainerVictory`.
- Listener `App\Trainers\Listeners\PayOutOnBattleWon`: for trainer and gym battles, pays `TrainerPayoutCalculator` money (using the player's Pokémon that entered the field) with `AddMoneyAction`, records the victory, and stores the amount in the battle log for the end screen ("You got ₽ 1,440 for winning!").
- Mission listeners from Spec 5 now get `WIN_TRAINER_BATTLES` from `BattleWon`.

**Tests:** win pays the formula; a higher-level team gets less; victory counted; loss pays nothing.

**Done when:** tests pass.

### 6.6 Trainers page

**Goal:** the list of hand-made trainers.

**Mock-up:** `docs/mockups/trainers.html` (all states, including "Trainer's team")

**Build:**
- Route `GET /trainers` named `trainers.web.index` → `App\Trainers\Http\Web\Controllers\TrainersController@index` with `TrainersIndexPageProps`: every `TRAINER` (name, class, sprite, team size, level range, types used, full payout, and the team: species, level, types, shiny) sorted by level, with each trainer's category; plus the player's party average level for the payout hint.
- Route `POST /trainers/{trainer}/challenge` named `trainers.web.challenge` → `ChallengeTrainerController`, redirects to the battle page.
- "Trainers" sub-item in the sidebar's "Battle" group (task 1.19), after "Current battle".

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

**Goal:** about 1 in 20 explores meets a generated trainer.

**Mock-up:** `docs/mockups/area.html` (state "Trainer encounter")

**Build:**
- `App\Trainers\Mechanics\TrainerTeamRoller::roll(Collection $species, int $size, int $minLevel, int $maxLevel, int $boost = 0): array` (shared with the Tower): random species, levels, genders, shiny at the normal odds, base moves.
- A small list of names and classes for generated trainers in `config/trainers.php`.
- Migration `032_add_trainer_columns_to_encounters_table.php`: `kind` enum (`POKEMON`, `TRAINER`), nullable `trainer_name`, `trainer_class`, `json trainer_team`, `payout`.
- `ExploreAction` (task 2.6): 1 in `game.exploration.trainer_odds` (20) rolls a trainer of 1–3 Pokémon from the area at its wild levels, payout from `game.trainers.explore_payout_per_level`.
- `BattleEncounterAction` starts a `TRAINER` battle for trainer encounters (paid like any trainer).

**UI suggestions** (ideas, not requirements):
- The encounter card shows the trainer sprite, "Hiker Bob wants to battle!", the team as small portraits (or "3 Pokémon"), the level range and the payout, with "Battle" (primary) and "Keep exploring" (outline).

**Tests:** trainer rolled with the faked odds; team from the area within its levels; battle starts as a trainer battle and pays.

**Done when:** tests pass.

### 6.8 Gyms and badges

**Goal:** challenge gym leaders and earn badges.

**Build:**
- `App\Trainers\Actions\ChallengeGymAction::handle(User $user, Trainer $leader, array $pokemonIds): Battle`: only `GYM_LEADER`; `$pokemonIds` must be 1 to as many party Pokémon as the leader has, never more (`App\Trainers\Exceptions\GymTeamSizeException`), in the chosen order. Fewer is allowed (the page confirms it first); starts a `GYM` battle.
- A badge is a `TrainerVictory` against a gym leader. `App\Trainers\Queries\BadgesQuery::forRegion(User $user, Region $region)`.
- `BattleWon` for a gym: pays (task 6.5) and, on the first win, dispatches `BadgeEarned` (region, leader) for missions (`BEAT_GYM`, `EARN_REGION_BADGES`).

**Tests:** more Pokémon than the leader refused; fewer allowed; none refused; non-party Pokémon refused; first win gives the badge and the event once; rematch pays but no new badge.

**Done when:** tests pass.

### 6.9 Gyms page

**Goal:** the badge case per region.

**Mock-up:** `docs/mockups/gyms.html` (states "Region badges", "Gym details", "Choose your team", "Leader brings 6: choose the order", "Party smaller than the leader's team", "Confirm battling with fewer", "Elite Four locked", "Elite Four unlocked")

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

**Goal:** the four members then the Champion, in order.

**Build:**
- Migration `033_create_elite_four_runs_table.php`: `uuid id`, `foreignIdFor(User)`, `region` enum, `stage` (1–5), `status` enum (`ACTIVE`, `WON`, `LOST`, `QUIT`), timestamps.
- `App\Trainers\Actions\StartEliteFourRunAction::handle(User $user, Region $region): EliteFourRun`: needs all 8 badges of the region (`App\Trainers\Exceptions\BadgesMissingException`); one active run at a time.
- `ContinueEliteFourRunAction::handle(EliteFourRun $run): Battle`: starts an `ELITE_FOUR` battle against the stage's opponent with the party (healed: normal battles always heal).
- Listener on `BattleWon` / battle lost for `ELITE_FOUR` battles: win → next stage (after the Champion: `WON`, record the victory, dispatch `ChampionDefeated`); loss → `LOST`.
- The battle end screen shows "Next: Lance" and a button to continue the run.

**Tests:** refused without 8 badges; stages advance; loss ends the run; the next run starts at stage 1; beating the Champion records it and fires the event.

**Done when:** tests pass, and the run can be played from the Gyms page and the battle page.

### 6.11 Battle Tower: rules

**Goal:** checkpoints, opponent strength and Battle Points.

**Build:**
- `App\BattleTower\Mechanics\TowerFloorCalculator`:
  - `isCheckpoint(int $floor): bool` and `nextCheckpoint(int $floor): int`: checkpoints at 10, 25, 45, 70, 100… (each gap 5 floors longer).
  - `opponentLevel(int $floor): int` = min(floor, 100); `opponentBoost(int $floor): int` = 0 up to floor 100, then round((floor − 100) ÷ 9), max 100.
  - `teamSize(int $floor): int`: 3, or 6 on checkpoints.
  - `battlePoints(int $floor): int`: 1, plus 5 × the checkpoint's number on checkpoints.
- Config `game.battle_tower`: `top_floor` (1000), `first_checkpoint` (10), `checkpoint_gap_step` (5), `team_size` (3), `checkpoint_team_size` (6), `points_per_floor` (1), `points_per_checkpoint_number` (5).

**Tests:** the first five checkpoints; level and boost at floors 1, 100, 101, 550, 1000; team sizes; points on normal and checkpoint floors.

**Done when:** tests pass.

### 6.12 Battle Tower: runs

**Goal:** start, climb, resume, quit.

**Build:**
- Migration `034_create_tower_runs_table.php`: `uuid id`, `foreignIdFor(User)`, `status` enum (`ACTIVE`, `ENDED`), `floor` (the next floor to fight), `json team` (for each of the 6: Pokémon ID, the frozen copy taken at the start — species, level, boost, shiny, gender, types, active moves, max HP and stats — its current HP, and the XP saved up so far), `battle_points_earned`, timestamps. Migration `035_add_tower_columns_to_users_table.php`: `best_tower_floor` (default 0), `battle_points` (default 0).
- `App\BattleTower\Actions\StartTowerRunAction::handle(User $user, array $pokemonIds): TowerRun`: exactly 6 of the player's Pokémon (party or Box), none on trade hold (Spec 7); one active run; copies each Pokémon into `team`.
- `ClimbTowerFloorAction::handle(TowerRun $run): Battle`: a `BATTLE_TOWER` battle against `TrainerTeamRoller` with the floor's level, boost and size (species from every wild-eligible species). The player's side is built from the frozen copies, not the live Pokémon, starting at the HP stored in the run; `heals_after` false. Refused while another battle is ongoing (one battle at a time).
- On a win: store each team member's HP back in the run, add each member's XP for the floor (Spec 1's formula with the Tower bonus) to its saved-up XP, add the floor's points, `floor` + 1; on a checkpoint floor, heal the whole team. On a loss: status `ENDED`.
- When the run ends (loss, quit or floor 1,000): pay each member's saved-up XP with `AwardExperienceAction` (to the live Pokémon), update `best_tower_floor` (highest floor won) and add points to `users.battle_points`.
- Tower hold: `App\BattleTower\Queries\ActiveTowerTeamQuery::contains(Pokemon $pokemon): bool`. `ReleasePokemonAction` and `EvolvePokemonAction` refuse a Pokémon in an active run (`App\BattleTower\Exceptions\PokemonInTowerRunException`); so do `CreateListingAction` and `MakeOfferAction` (Spec 7, when built). The Pokémon page shows an "In a Battle Tower run" badge, with a tooltip that boosts and TMs count from its next run.
- `QuitTowerRunAction::handle(TowerRun $run): void`: ends the run, paying XP and keeping points and best floor.

**Tests:** start needs 6; the run uses the frozen copy even after a team member levels up elsewhere; damage carries over between floors; checkpoint heals; XP is saved up per floor and paid only when the run ends (loss, quit); loss ends the run and keeps points; best floor updated; Tower hold refuses release and evolve; climbing is refused during another battle; resume after reload (the run is just loaded again).

**Done when:** tests pass.

### 6.13 Battle Tower page

**Goal:** the lobby and the run screen.

**Mock-up:** `docs/mockups/tower.html` (all states)

**Build:**
- Route `GET /battle-tower` named `battle-tower.web.index` → `BattleTowerController@index` with `BattleTowerIndexPageProps`: best floor, Battle Points, the active run (floor, next checkpoint, team with HP) or the player's Pokémon for picking.
- Routes `POST /battle-tower/runs` (`battle-tower.web.runs.store`, `pokemon_ids`), `POST /battle-tower/runs/climb` (`battle-tower.web.runs.climb`, redirects to the battle), `DELETE /battle-tower/runs` (`battle-tower.web.runs.destroy`).
- After a Tower battle, the battle page's end screen links back to the Tower ("Back to the Tower").
- "Battle Tower" sub-item in the sidebar's "Battle" group, after "Gyms".

**UI suggestions** (ideas, not requirements):
- Header with two stats: "Best floor 37" and "Battle Points 1,240".
- Lobby: six empty team slots and "Choose your team", which opens a picker dialog with search and Party / Box tabs; picked Pokémon fill the slots in order; primary "Start run" enabled at 6.
- Run in progress: a big "Floor 38", a progress line to the next checkpoint ("Checkpoint at floor 45: full heal"), the team with HP bars (fainted greyed out), two per row when the screen is wide enough and one per row on phones, primary "Next battle" and a destructive-outline "Quit run" with a confirmation.
- Checkpoint reached: a green notice "Checkpoint! Your team was fully healed."
- Run over: floors climbed, Battle Points earned, the XP each team member received (with level-ups), "New best floor!" when it is, and "Start a new run".
- Lobby and run screens say the team is a frozen copy: "Your team as it was when the run started".

**Tests:** feature tests for the props and routes; browser test: start a run and climb one floor.

**Done when:** a player can play the Tower from the page.

### 6.14 Battle page: trainer battles

**Goal:** the battle page shows trainer battles properly.

**Mock-up:** `docs/mockups/battle.html` (states "Trainer battle", "Trainer battle won")

**Build:** the header names the trainer ("Hiker Bob" / "Gym Leader Brock"); the opponent's team dots show all its Pokémon; no Throw ball button; the end screen shows the money earned (with the reduced-payout note when it applies), "Next: …" for Elite Four runs, and "Back to the Tower" with the HP left for Tower battles.

**UI suggestions** (ideas, not requirements):
- Header: the trainer's sprite next to "Gym Leader Brock" and "Pewter City Gym · Rock".
- End screen money line: "+ ₽ 1,440" with a muted note "Reduced: your team outlevels Hiker Bob".

**Tests:** feature test: trainer battle props (no balls, trainer name, team size); browser test: the win screen shows the money.

**Done when:** trainer, gym, Elite Four and Tower battles all look right on the page.

---

## 7. Spec 7 — Trading

Read [07-trading.md](07-trading.md) first, including its "Details" section. Trading lives in a new domain, `src/App/Trading`. Notifications use Laravel's database notifications and are shared by later features.

### 7.1 Trade data

**Goal:** store listings and offers.

**Build:**
- Enums in `Shared\Enums`: `ListingStatus` (`OPEN`, `TRADED`, `CANCELLED`, `EXPIRED`), `OfferStatus` (`PENDING`, `ACCEPTED`, `DECLINED`, `WITHDRAWN`).
- Migration `036_create_trade_listings_table.php`: `uuid id`, `foreignIdFor(User)` (seller), `foreignIdFor(Pokemon)`, nullable `looking_for` (200), `status` enum, `fee`, `expires_at`, nullable `traded_at`, timestamps; index `['status', 'expires_at']`.
- Migration `037_create_trade_offers_table.php`: `uuid id`, `foreignIdFor(TradeListing)`, `foreignIdFor(User)` (offerer), `status` enum, timestamps; unique pending offer per user per listing (checked in code).
- Migration `038_create_trade_offer_pokemon_table.php` (`trade_offer_id`, `pokemon_id`) and `039_create_trade_offer_items_table.php` (`trade_offer_id`, `item_type`, `item_id`, `quantity`).
- Migration `040_add_is_on_trade_hold_to_pokemon_table.php`: `boolean('is_on_trade_hold')->default(false)`.
- Models `Shared\Models\TradeListing`, `TradeOffer`, `TradeOfferItem` (with factories); `Pokemon::scopeTradeable()` (owned, not locked, not on hold).
- Config `game.trading`: `listing_fee` (500), `listing_days` (7), `max_offer_pokemon` (6), `max_offer_items` (10), `looking_for_max_length` (200).

**Tests:** relations and the tradeable scope.

**Done when:** tables and models exist; tests pass.

### 7.2 Trade holds everywhere

**Goal:** a Pokémon on hold can't be used.

**Build:**
- Exception `App\Pokemon\Exceptions\PokemonOnTradeHoldException`.
- Refuse Pokémon on hold in: `AddToPartyAction`, `SwapPartyPokemonAction`, `ReleasePokemonAction`, `EvolvePokemonAction`, `TeachTmAction`, `UseBoostStonesAction`, `SetActiveMoveAction`, `ReorderActiveMovesAction`, `StartTowerRunAction`, gym and battle team selection.
- Pokémon page and Box show an "On trade" badge and disable those actions with the reason.

**Tests:** one test per action that a held Pokémon is refused.

**Done when:** tests pass.

### 7.3 Notifications

**Goal:** a reusable notification bell.

**Build:**
- `php artisan make:notifications-table` → migration `041_create_notifications_table.php`.
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

**Goal:** put a Pokémon on the market.

**Build:**
- `App\Trading\Actions\CreateListingAction::handle(User $user, Pokemon $pokemon, ?string $lookingFor): TradeListing`
  - The Pokémon must be tradeable (`App\Trading\Exceptions\PokemonNotTradeableException` with the reason: locked, on hold, or taking part in an ongoing battle, meaning its ID is on the player's side of the player's `ONGOING` battle) and not the last party Pokémon (`PartyCannotBeEmptyException`).
  - Spends the fee (`SpendMoneyAction`), removes it from the party, sets `is_on_trade_hold`, sets `expires_at`. One transaction.

**Tests:** listing created and fee spent; leaves the party and goes on hold; locked, held, in-battle and last-party Pokémon refused; not enough money refused; nothing changes when refused.

**Done when:** tests pass.

### 7.5 Make, withdraw and decline offers

**Goal:** the offer side.

**Build:**
- `App\Trading\Actions\MakeOfferAction::handle(User $user, TradeListing $listing, array $pokemonIds, array $items): TradeOffer`
  - Refuses: own listing, listing not open, an existing pending offer by the player, empty offer, more than the limits, Pokémon not tradeable (locked, on hold, or in an ongoing battle) or last party Pokémon, items not owned.
  - Puts the Pokémon on hold (leaving the party) and removes the items from the inventory (`RemoveInventoryItemAction`). Notifies the seller (`OfferReceived`).
- `WithdrawOfferAction::handle(TradeOffer $offer): void` (offerer only, pending only) and `DeclineOfferAction::handle(TradeOffer $offer): void` (seller only, pending only, notifies `OfferDeclined`): both release the holds and return the items (`AddInventoryItemAction`).

**Tests:** offer made, holds and items set aside; each refusal; withdraw and decline give everything back; notifications sent.

**Done when:** tests pass.

### 7.6 Accept an offer

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

**Goal:** listings end without a trade.

**Build:**
- `App\Trading\Actions\CancelListingAction::handle(TradeListing $listing): void` (seller only, open only): `CANCELLED`, fee kept, hold released, pending offers declined.
- `ExpireListingAction::handle(TradeListing $listing): void`: `EXPIRED`, fee refunded (`AddMoneyAction`), hold released, pending offers declined, seller notified (`ListingExpired`).
- Command `trading:expire-listings`, scheduled every 5 minutes in `routes/console.php`, expires open listings past `expires_at`.

**Tests:** cancel keeps the fee; expire refunds it; offers declined in both; the command expires only past-due listings.

**Done when:** tests pass.

### 7.8 Pokémon details dialog

**Goal:** see everything about a Pokémon in a trade.

**Mock-up:** `docs/mockups/trade-listing.html` (state "Pokémon details")

**Build:** a shared component `resources/js/shared/components/game/PokemonDetailsDialog.vue` (Spec 8's profile can reuse it), fed by a `PokemonDetails` shape from a shared PHP presenter: sprite, display name, gender, types, level, boost, shiny, stats, active moves, the full known-moves pool, ball, original trainer.

**UI suggestions** (ideas, not requirements):
- A read-only version of the Pokémon page's header, stats and moves, in a wide `Dialog`; known moves as compact type-coloured chips with power.
- Catch details as on the Pokémon page: "Caught in a [sprite] Ultra Ball", the ball's `ItemSprite` right before its name, then the original trainer.

**Tests:** feature test: listing props include the details for the listed and offered Pokémon.

**Done when:** the dialog opens from listings and offers.

### 7.9 Trade market page

**Goal:** browse open listings.

**Mock-up:** `docs/mockups/trade-market.html` (states "Market", "No listings match", "Recent trades")

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

**Goal:** list a Pokémon from the page.

**Mock-up:** `docs/mockups/trade-market.html` (states "New listing: choose a Pokémon", "New listing: details")

**Build:**
- Route `POST /trading/listings` (`trading.web.listings.store`, `pokemon_id`, `looking_for`).
- "New listing" opens a two-step dialog: pick a tradeable Pokémon (search; locked and held ones disabled with the reason), then "Looking for" and the fee confirmation.
- The Pokémon page (Spec 4) gets a "Trade" action that opens step two directly.

**UI suggestions** (ideas, not requirements):
- Step 2 shows the chosen Pokémon's summary, a `Textarea` for "Looking for" with a 200-character counter, the fee ("Listing costs ₽ 500, refunded if no one trades within 7 days"), and a primary "List for ₽ 500".

**Tests:** feature test for the route and its errors; browser test: list a Pokémon.

**Done when:** a player can list from the market and the Pokémon page.

### 7.11 Listing page and offers

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

**Goal:** every name is unique and usable on a profile.

**Build:**
- Migration `042_add_unique_name_index_to_users_table.php`: a unique index on `lower(name)` (or a `name_normalized` column if the database can't index an expression).
- `StoreRegistrationRequest`: `name` 3–20 characters, letters, numbers, spaces, `-` and `_`, unique ignoring case, with a clear message ("That name is taken.").
- `UserFactory` gives unique names.

**Tests:** registration refuses a taken name in any case; refuses bad characters and lengths.

**Done when:** tests pass.

### 8.2 Latest captures feed

**Goal:** record notable catches and hatches as they happen.

**Build:**
- Migration `043_create_capture_feed_entries_table.php`: `uuid id`, `foreignIdFor(User)`, nullable `foreignIdFor(Pokemon)` (null on delete), `foreignIdFor(Species)`, `level`, `is_shiny`, `source` enum (`WILD`, `EGG`), `created_at`. Model `Shared\Models\CaptureFeedEntry`.
- `App\Social\Mechanics\NotableCaptureChecker::isNotable(Species $species, int $level, bool $isShiny): bool`: shiny, legendary or mythical, or level ≥ `game.social.notable_level` (60).
- Listener `App\Social\Listeners\RecordCaptureOnPokemonCaught` adds an entry for notable catches and hatches.
- `App\Social\Queries\LatestCapturesQuery::get(int $limit = 20)`.

**Tests:** shiny, legendary, mythical and level 60 are recorded; ordinary ones are not; the entry keeps the catch level after a level-up; the newest come first.

**Done when:** tests pass.

### 8.3 Profile data

**Goal:** what a profile shows.

**Build:**
- `App\Social\Queries\PlayerProfileQuery::get(User $user)`: name, join date, Pokédex count (distinct species owned now) and total species, shiny count (owned now), best Battle Tower floor, badges per region with Elite Four and Champion status (Spec 6's `TrainerVictory`), and open trade listings (Spec 7).
- Badges, Tower and listings show as empty until those specs are built.

**Tests:** each number for a seeded player; a traded-away Pokémon no longer counts.

**Done when:** tests pass.

### 8.4 Profile page

**Goal:** a player's public profile.

**Mock-up:** `docs/mockups/profile.html` (all states)

**Build:**
- Route `GET /players/{user}` named `social.web.players.show` → `PlayerController@show` with `PlayerShowPageProps` from `PlayerProfileQuery`. Visible to every logged-in player.
- Every player name in the app (captures feed, recent trades, listings, offers, notifications) links here.
- The sidebar user menu gets "My profile".

**UI suggestions** (ideas, not requirements):
- A header `Card`: an avatar with the initials (the same `Avatar` as the sidebar), the name, "Joined Nov 2026", and "This is you" for your own profile.
- Three stat tiles: Pokédex "702 / 1,025" with a thin `Progress` and the %, Shinies, Best Tower floor.
- Badges: one row per region with 8 small badge marks (gold when earned, outline when not), then "Elite Four" and "Champion" marks; regions with no badges collapse into "No badges yet" text.
- Active trade listings as the same listing cards as the trade market (Spec 7); empty: "No active listings".

**Tests:** feature test for the props; guests redirected to login.

**Done when:** profiles open from every player name.

### 8.5 Player search

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

**Goal:** list a Pokémon from its own page (task 7.10).

**Mock-up:** `docs/mockups/pokemon.html` (states "Overview" and "Locked")

**Build:** a "Trade" outline button in the Pokémon page's action row opens task 7.10's listing dialog at step two. Disabled with the reason when the Pokémon is locked, on trade hold, or the last party Pokémon; "View listing" instead when it is already listed.

**Tests:** browser test: list a Pokémon from its page.

**Done when:** a Pokémon can be listed from its page.
