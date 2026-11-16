---
paths:
  - 'src/App/**/Events/**'
  - 'src/App/**/Listeners/**'
  - 'src/App/**/Actions/**'
---

# Events

## Announce what happened instead of calling other domains
An Action that finishes something other domains may care about dispatches an event from `src/App/{Domain}/Events/`, named in the past tense (`PokemonCaught`, `BattleEnded`). Other domains react through listeners; the Action never calls their Actions directly. The one exception is another domain's Action whose result is part of this Action's own work and must commit with it: call it inside this Action's `DB::transaction` (e.g. `EndBattleAction` pays XP through `App\Pokemon\Actions\AwardExperienceAction` and saves the levels it returns).

## Events are final readonly classes that reach listeners after the commit
Events are `final readonly` classes carrying only IDs and plain values (`pokemonId`, `userId`), never models. They use the `Dispatchable` trait and implement `ShouldDispatchAfterCommit`. The Action dispatches them with `{Event}::dispatch(...)` after its `DB::transaction`, never inside it; when the Action itself runs inside a caller's transaction, `ShouldDispatchAfterCommit` still holds the event until that transaction commits. Listeners therefore only hear about saved data, and a failing listener never undoes the change that raised the event.

## Listeners live in the domain that reacts
Listeners live in `src/App/{Domain}/Listeners/` of the domain that reacts, are named `{Verb}{Noun}On{Event}` (`AdvanceMissionsOnPokemonCaught`) and have a `handle({Event} $event)` method. They are discovered automatically from that type-hint (`bootstrap/app.php`); never register them by hand.

## Each feature decides whether its listener is queued
Whether a listener runs synchronously or implements `ShouldQueue` is decided by the feature that builds it. A queued listener sets `$tries` and `$backoff` and is idempotent (handling the same event twice changes nothing the second time), so a failure retries safely and a listener that keeps failing stays in `failed_jobs` for `php artisan queue:retry`.
