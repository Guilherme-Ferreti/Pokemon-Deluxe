# Tasks

Planned work, in order. Each task should be small enough to build, test and merge on its own. Tick a task when its work is merged.

## 0. Foundations

Conventions to settle before the game features grow. Each one follows the project's existing pattern: a convention, a rule file in `.ai/rules/`, a row in `.ai/rules/index.md`, and an `arch()` rule in `tests/Architectural/ArchitecturalTest.php`.

### 0.1 Point AI sessions at the specs

- [x] `AGENTS.md` and `.ai/rules/index.md` say that feature behaviour lives in `docs/specs/`, that the relevant spec is read before working on a feature, and that specs are updated when a decision changes.

### 0.2 A home for game mechanics

Game rules that are pure calculation (damage, turn order, status effects, catch chance, boost odds, encounter rolls, XP) get a dedicated, strictly tested place.

- [ ] Choose the folder name and scope, for example `src/App/{Domain}/Mechanics/`, available to any domain.
- [ ] Rule file: mechanics classes are `final`, take plain values or value objects, return results, and do not touch Eloquent models, facades, the database, HTTP or config directly (values such as odds are passed in).
- [ ] `arch()` rule enforcing the above.
- [ ] Move `App\Battle\Services\DamageCalculator` into the new folder and update its test and callers.

Done when: the rule file, index row and `arch()` rule exist, the test suite passes, and `DamageCalculator` lives in the new folder.

### 0.3 Testable randomness

Battles, catching and encounters need random ranges (damage 85–100%, levels 2–12, species picks), not only yes/no rolls. Tests must be able to control them.

- [ ] Choose the approach: a shared random source that tests can fix or seed, usable from Actions (which are created with `new`, not injected) and from mechanics classes. Keep `Lottery` for yes/no odds or replace it; decide and document.
- [ ] Implement it in `src/Shared/` with a unit test.
- [ ] Rule file: game code gets randomness only from the shared source.
- [ ] `arch()` rule forbidding direct `rand`, `mt_rand`, `random_int`, `array_rand`, `shuffle` and `Arr::random` in `src/App`.
- [ ] Move existing random calls to the new source if the chosen approach replaces `Lottery`.

Done when: the shared source exists with tests, the rule and `arch()` rule exist, and the suite passes.

### 0.4 Events and listeners between domains

Missions, achievements, notifications and the latest-captures feed react to things that happen in other domains. Domains announce what happened instead of calling each other.

- [ ] Rule file: an Action that finishes something other domains may care about dispatches a domain event (past tense, for example `PokemonCaught`, `BattleWon`, `EggHatched`). Listeners live in the domain that reacts. Events carry IDs and plain values.
- [ ] Folders `src/App/{Domain}/Events/` and `src/App/{Domain}/Listeners/`, with `arch()` rules (naming, `final`, listeners not used by controllers).
- [ ] Decide whether listeners run synchronously or queued, and document it.
- [ ] One example: `CatchPokemonAction` (Nursery) dispatches `PokemonCaught`, covered by a test.

Done when: the rule file, index rows and `arch()` rules exist, the example event is dispatched and tested, and the suite passes. Needed before Spec 5 (Missions) at the latest.

## 1. Spec 1 — Power model and battle system

To be split next.
