---
paths:
  - 'src/App/**/Actions/**'
---

# Actions

## Use Action classes with handle() for business logic
Create `final` Action classes suffixed with `Action` (e.g. `CatchPokemonAction`) with a single `handle()` method. Name their unit tests `{ActionName}Test.php` under `tests/App/{Domain}/Unit/Actions/`. Controllers instantiate and call them directly (`new Action()->handle(...)`). Actions are not injected via the container.

## Run guards at the top of the method that does the work
Guard clauses live in private `ensure*()` methods that take no arguments: `handle()` stores the action's inputs in private properties, and values derived from them come from private getters (e.g. `chosenCombatant()`). An exception that needs a computed value is built in a closure, so the value is only computed when the guard fails: `throw_if($combatant->isFainted(), fn () => new PokemonHasFaintedException($this->chosenDisplayName()))`. Call the guards as the first lines of the method that does the work, right after it loads or refreshes what they check, so they run inside the lock when the action takes one. A guard that reads saved data outside the lock lets two requests both pass it before either one acts.

## Lock the owning row when the action can run inside a caller's transaction
A `Cache::withoutOverlapping` lock is released when its callback returns, which is before an outer `DB::transaction` commits. An action that guards on saved data and may run inside another action's transaction (e.g. `CreatePokemonAction` inside `CatchPokemonAction`) instead locks the row that owns that data with `lockForUpdate()` inside its own `DB::transaction`, before its guards (`User::query()->whereKey($this->owner->id)->lockForUpdate()->firstOrFail()`). The database holds that lock until the outermost transaction commits.
