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

**Mock-up:** `docs/mockups/battle.html` (state "Battle, choosing a move")

**Build:**
- Route `GET /battle` named `battle.web.show` → `App\Battle\Http\Web\Controllers\BattleController@show`; shows the user's `ONGOING` battle or redirects to the Dashboard.
- `App\Battle\Http\Web\PageProps\BattleShowPageProps`: both active combatants (sprite, name, level, boost, shiny, ailment), player HP as numbers, opponent HP as a percentage only, team dots, active moves (name, type, power), log.
- Page `resources/js/app/Battle/pages/Battle/index.vue` with the Spec 1 layout; stacks on phones.

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
- "Explore" link in the main navigation.

**UI suggestions** (ideas, not requirements):
- `AppPageHeader`: "Explore", with the description "Pick an area to look for wild Pokémon."
- A responsive grid: 1 column on phones, 2 on tablets, 3 on desktop.
- One `Card` per area, the whole card clickable: a coloured band at the top (the two type colours as a gradient) with the area name on it, `PokemonTypeBadges` in the content, and a full-width outline "Explore" `Button` in the footer (outline because it repeats on every card; the design guide keeps primary for one action per screen).
- Sidebar: an "Explore" `AppNavLink` with a compass or map icon, near the top since it is the main activity.

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
