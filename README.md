# Pokémon Deluxe

A browser Pokémon game built with Laravel, Inertia and Vue. Players catch, raise and battle Pokémon; every Pokémon can be strong, because power comes from what the player invests (level, boost, moves, shiny), not from the species.

This README describes **how the application is built**: the purpose of every folder, the patterns each kind of class follows, and the reasons behind them. It is written as a reference for working on this project and for starting new projects with the same structure.

- **Game rules and page designs** live in [`docs/specs/`](docs/specs/README.md). This README covers how code is organised, not what the game does.
- **The short, enforceable version of each rule** lives in [`.ai/rules/`](.ai/rules/index.md), indexed by file path. This README explains the same rules at length, with the reasons.
- Most structural rules are **enforced by tests** in [`tests/Architectural/ArchitecturalTest.php`](tests/Architectural/ArchitecturalTest.php), by Pint and by PHPStan, so a broken rule fails CI instead of relying on review.

## Contents

1. [Stack and commands](#1-stack-and-commands)
2. [Principles](#2-principles)
3. [Backend](#3-backend)
   - [3.1 `src/App` and `src/Shared`](#31-srcapp-and-srcshared)
   - [3.2 Domains](#32-domains)
   - [3.3 Folder reference](#33-folder-reference)
   - [3.4 How a request flows](#34-how-a-request-flows)
   - [3.5 Actions](#35-actions)
   - [3.6 Exceptions: which one to throw](#36-exceptions-which-one-to-throw)
   - [3.7 Value objects](#37-value-objects)
   - [3.8 Mechanics and randomness](#38-mechanics-and-randomness)
   - [3.9 Pipelines: Pipes and Data](#39-pipelines-pipes-and-data)
   - [3.10 Events and listeners](#310-events-and-listeners)
   - [3.11 The HTTP layer](#311-the-http-layer)
   - [3.12 Models, migrations, enums and casts](#312-models-migrations-enums-and-casts)
   - [3.13 Support, helpers and global configuration](#313-support-helpers-and-global-configuration)
   - [3.14 Naming reference](#314-naming-reference)
4. [Frontend](#4-frontend)
5. [Tests](#5-tests)
6. [Specs, rules and the AI workflow](#6-specs-rules-and-the-ai-workflow)
7. [Checklist: building a feature end to end](#7-checklist-building-a-feature-end-to-end)

---

## 1. Stack and commands

| Layer | Tools |
| --- | --- |
| Backend | PHP 8.5, Laravel 13, Inertia (Laravel adapter) 3 |
| Frontend | Vue 3, TypeScript, Inertia (Vue adapter) 3, Tailwind CSS 4, shadcn-vue (reka-ui), Tabler icons |
| Quality | Pest 5 (unit, feature, browser and architectural tests), PHPStan (Larastan) level 7, Pint, Rector, ESLint, Prettier, vue-tsc |
| Types | `spatie/laravel-typescript-transformer` generates the typed `route()` helper for the frontend |

| Command | What it does |
| --- | --- |
| `composer setup` | Installs dependencies, creates `.env`, runs migrations and builds the frontend. |
| `composer run dev` | Runs the local development processes (`php artisan dev`). |
| `php artisan migrate:fresh --seed` | Rebuilds the local database (needed after changing a migration, see [3.12](#312-models-migrations-enums-and-casts)). |
| `php artisan test --compact` | Runs the test suite. Pass a path or `--filter` to narrow it. |
| `npm run build:testing` | Builds the frontend for browser tests. Run it before browser tests and after each frontend change. |
| `composer typescript` | Regenerates `resources/js/shared/types/helpers/route.ts` after adding or renaming a route. |
| `composer format` | Regenerates types, then runs Prettier, ESLint, Pint, Rector and both type checkers. |
| `composer test` | `format`, a testing build, then the whole suite. |
| `composer ci:check` | What CI runs: lint, format and type checks without fixing, then the tests. |

---

## 2. Principles

These ideas explain most of the rules below.

1. **Organise by domain first, then by kind.** Code for battles lives in `src/App/Battle/`, and inside it in `Actions/`, `Mechanics/`, `Http/`… The same domain name appears in `resources/js/app/Battle/` and `tests/App/Battle/`, so a feature is always found in the same three places.
2. **One job per class, named after that job.** An Action changes something, a Query reads, a PageProps shapes data for one page, a Calculator does math. A class that does two of these is split.
3. **Names replace comments.** Functions and methods carry no descriptive comments. If a method needs one, it gets a better name or is split into well-named methods (`rollIsShiny()`, not `roll()` with a comment). PHPDoc blocks only hold types for PHPStan (`@param`, `@return`, `@var`, `@template`, `@throws`, array shapes).
4. **Invalid states should be impossible to build.** Value objects validate in their constructor, actions guard before writing, and getters refuse to return what was never checked.
5. **Rules are enforced by tools.** Every class in `src/` is `final` (Pint's `final_class` and the architectural tests), uses `declare(strict_types=1)` and strict equality, and overridden methods carry `#[Override]`. Each kind of folder has an `arch()` rule that checks suffixes, parents, interfaces and allowed public methods.
6. **Start narrow, move up when needed.** An enum, value object or component starts in the one domain or page that uses it, and moves to `Shared` only when a second domain or page needs it.
7. **Every game number lives in config.** Limits, odds and timings are in [`config/game.php`](config/game.php), each with a comment saying what it controls. Code reads them with `config('game.…')`; tests and specs refer to the same keys.

---

## 3. Backend

### 3.1 `src/App` and `src/Shared`

The default `app/` folder is replaced by `src/`, with two roots:

| Root | Namespace | Holds |
| --- | --- | --- |
| `src/App/{Domain}/` | `App\{Domain}\…` | Everything that belongs to one area of the game: its actions, rules, HTTP layer, exceptions, events… |
| `src/Shared/` | `Shared\…` | What every domain builds on: models, casts, shared enums and value objects, base classes, interfaces, helpers, providers, global middleware and console commands. |

`bootstrap/app.php` points Laravel's app path at `src/Shared` (`->useAppPath(...)`), registers each domain's route files, discovers listeners in `src/App/*/Listeners` and loads commands from `src/Shared/Console/Commands`. Composer maps `App\` to `src/App/` and `Shared\` to `src/Shared/`.

**Domains may use `Shared`. `Shared` never depends on a domain**, with two practical exceptions: models may call a domain's Mechanics for derived values (`Pokemon::getStats()` uses `App\Pokemon\Mechanics\StatsCalculator`), and console commands call domain actions.

### 3.2 Domains

#### What a domain is

A domain is **an area of the game a player would name**: Battle, Exploration, Pokémon, Inventory, the Dex. It owns the rules, pages and code for that area. A domain is not a database table and not a technical layer; it usually matches one or more sections of the specs.

| Domain | What it covers |
| --- | --- |
| `Auth` | Registration, login, logout, the auth layout. |
| `Battle` | Starting, playing and ending battles: turns, switching, throwing balls, damage, ailments, experience earned. |
| `Dashboard` | The page a player lands on after logging in. |
| `Dex` | The Pokédex and Attackdex: browsing species and moves, catch statistics. |
| `Exploration` | Exploring areas, rolling encounters, starting a battle with an encounter. |
| `Inventory` | Adding and removing items. Backend only: other domains' actions call it. |
| `Pokemon` | The player's Pokémon: creating, levelling, evolving, moves, party, box and the Nursery (incubators). |
| `Public` | Pages for guests (the home page). |

A domain does not need every folder. `Inventory` has only `Actions/` and `Exceptions/`; `Dashboard` and `Public` have only an HTTP layer.

#### Deciding which domain code goes into

Ask **which area of the game the change is about, from the player's point of view**, not which tables it touches.

- Catching an egg in the Nursery writes to `pokemon`, `inventory_items` and `incubations`, but the player is "catching a Pokémon in the Nursery", so `CatchPokemonAction` lives in `Pokemon/Actions/Incubator/`.
- Throwing a ball in a battle also creates a Pokémon, but the player is "in a battle", so `ThrowBallAction` lives in `Battle/` and calls `Pokemon`'s `CreatePokemonAction` for the part that belongs to Pokémon.
- When a piece of work belongs to another domain's concept (creating a Pokémon, removing an item, awarding experience), that domain owns an Action for it and the others call it. The rule for when to call it directly and when to dispatch an event is in [3.10](#310-events-and-listeners).

#### Deciding between a domain and `Shared`

| It is… | It goes in… |
| --- | --- |
| An Eloquent model | Always `Shared/Models`. Models are the database schema; every domain reads them and their relations cross domains (`User` has Pokémon, battles, an encounter, inventory items), so no single domain owns one. |
| A cast | Always `Shared/Casts`, because it serves a model. |
| An enum or value object stored in a database column | `Shared/Enums` / `Shared/ValueObjects` (it is part of a model). |
| An enum or value object used by two or more domains | `Shared/Enums` / `Shared/ValueObjects`. |
| An enum or value object used by one domain | That domain's `Enums/` / `ValueObjects/`, even when other domains pass it in as an action's input (`App\Pokemon\ValueObjects\NewPokemon`). Move it to `Shared` when a second domain starts using it. |
| A generic helper not about any domain | `Shared/Helpers` (`*Helper` class) or `Shared/Helpers/functions.php`. |
| Anything else | The domain. |

#### When to create a new domain

Create a domain when a feature brings **its own vocabulary, rules and pages** that do not fit an existing domain. In practice this is a new section of the specs: the roadmap plans `Shop`, `Missions`, `Trainers`, `Trading` and `Social`.

Do **not** create a domain:

- for one more page of an existing area (the Nursery is part of `Pokemon`);
- for a cohesive slice of an existing domain: use a **feature subfolder** instead (below);
- for technical code shared by everyone: that is `Shared`.

A useful test: if most of the new actions would take another domain's main concept and add one rule to it, the code belongs in that domain.

Creating a domain means:

1. `src/App/{Domain}/` with the folders it needs.
2. `src/App/{Domain}/Http/Web/routes.php`, registered in `bootstrap/app.php` inside the `web` group with `Route::name('{domain}.web.')`.
3. `resources/js/app/{Domain}/pages/` for its pages.
4. `tests/App/{Domain}/` with `Unit/`, `Feature/` and `Browser/` as needed.
5. An `arch()` rule only if the domain introduces a new **kind** of folder (existing kinds are matched with `App\*\…` patterns).

#### Feature subfolders

A cohesive slice of a domain can be grouped in a subfolder that is **mirrored across layers and tests**: `Pokemon/Actions/Incubator/`, `Pokemon/Http/Web/Controllers/Incubator/`, `Pokemon/Http/Web/Requests/Incubator/`, `tests/App/Pokemon/Unit/Actions/Incubator/` and `tests/App/Pokemon/Feature/Incubator/`. Inside a subfolder, names can drop the slice's name (`Incubator\PurchaseAction`, not `PurchaseIncubatorAction`).

### 3.3 Folder reference

Every folder a domain may have. Folders marked *always* exist in every domain that has the matching layer; the rest are added when needed.

| Folder | Purpose | Naming | Shape |
| --- | --- | --- | --- |
| `Actions/` | Business logic: everything that changes data, and reads that apply game rules. | `{Verb}{Noun}Action` | `final`, one public `handle()`, extends and implements nothing. See [3.5](#35-actions). |
| `Mechanics/` | Pure game rules: formulas, odds, rolls. No database, HTTP, session or auth. | `{Noun}Calculator`, `{Noun}Roller`, `{Noun}Checker` | `final`, extends nothing. See [3.8](#38-mechanics-and-randomness). |
| `Queries/` | Reusable or complex reads that return a query builder. | `{Purpose}Query` (`SearchBoxQuery`) | `final`, one public `handle()` returning `Builder`. |
| `Filters/` | Typed search/sort/filter input read from the request. | `{Purpose}Filters` | `final`, implements `Shared\Interfaces\Filter` (`fromRequest()`, `filterOptions()`). |
| `ValueObjects/` | Small immutable values with rules (used by one domain). | A noun (`MoveOutcome`, `NewPokemon`) | `final readonly`, implements `Shared\Interfaces\ValueObject`. See [3.7](#37-value-objects). |
| `Data/` | Mutable state carried through a pipeline. | `{Noun}Data` | `final`, public properties, never stored. See [3.9](#39-pipelines-pipes-and-data). |
| `Pipes/{Pipeline}/` | The steps of a long action. | Imperative step name (`RollTurnOrder`, `DealHits`) | `final`, one public `handle($data, $next)`. |
| `Enums/` | Enums used only by this domain. | A noun (`IncubatorStatus`, `SearchBoxSortBy`) | Backed enum, `UPPER_SNAKE_CASE` cases. |
| `Exceptions/` | Game-rule violations shown to the player. | `{Noun}{Problem}Exception` | `final`, extends `Shared\Base\DomainException`. See [3.6](#36-exceptions-which-one-to-throw). |
| `Events/` | Announcements that something finished. | Past tense (`PokemonCaught`) | `final readonly`, `Dispatchable`, `ShouldDispatchAfterCommit`. |
| `Listeners/` | Reactions to another domain's events. | `{Verb}{Noun}On{Event}` | `final`, `handle({Event} $event)`, discovered automatically. |
| `Support/` | Domain helpers that are none of the above: lock and cache key factories, small read models. | `LockKeys`, `CacheKeys`, a noun (`Incubator`) | `final`, extends and implements nothing. |
| `Http/Web/routes.php` | The domain's web routes. | – | Controller references only, never closures. |
| `Http/Web/Controllers/` | Thin entry points for pages and form posts. | `{Noun}Controller` or `{Verb}{Noun}Controller` | `final`, extends `Shared\Base\Controller`. |
| `Http/Web/Requests/` | Validation of form input. | `{Verb}{Noun}Request` | `final`, extends `FormRequest`, `rules()`. |
| `Http/Web/PageProps/` | The props of one Inertia page. | `{Page}{Index\|Show}PageProps` | `final`, implements `ProvidesInertiaProperties`. |
| `Http/Web/Middlewares/` | Middleware used only by this domain's routes. | A verb phrase (`ShareAuthLayoutProps`) | `final`, `handle()`. |
| `Http/Api/` | JSON endpoints (`routes.php`, `Controllers/`). | Same as web | Same as web; named `{domain}.api.*`. |

`src/Shared/` holds:

| Folder | Purpose |
| --- | --- |
| `Base/` | Abstract parents: `Controller`, `DomainException`. |
| `Casts/` | `As{ValueObject}` casts that store value objects in columns. |
| `Console/Commands/` | Artisan commands (they call actions, like controllers do). |
| `Enums/` | Enums stored in columns or used by several domains. |
| `Helpers/` | `AppHelper`, `RandomHelper`, `ToastHelper` and global functions (`functions.php`). |
| `Interfaces/` | `Filter`, `ValueObject`. |
| `Middlewares/` | Global middleware (`HandleInertiaRequests`). |
| `Models/` | Every Eloquent model. |
| `Providers/` | Service providers (global configuration). |
| `ValueObjects/` | Value objects stored in columns or used by several domains. |

### 3.4 How a request flows

**A page (GET):** route → controller → PageProps → Inertia page.

```php
// src/App/Pokemon/Http/Web/Controllers/BoxController.php
public function index(): Response
{
    return inertia('Pokemon::Box/Index')->with(new BoxIndexPageProps);
}
```

The PageProps reads filters from the request, runs a Query, and maps each row to exactly the fields the page needs.

**A form post (POST):** route (with route-model or value-object binding) → FormRequest validates → controller resolves models → Action does the work → controller flashes a toast → `back()`.

```php
// src/App/Pokemon/Http/Web/Controllers/Incubator/CatchPokemonController.php
public function __invoke(IncubatorIndex $incubatorIndex, CatchPokemonRequest $request): RedirectResponse
{
    $pokeball = Pokeball::findOrFail($request->validated('pokeballId'));

    $pokemon = new CatchPokemonAction()->handle(
        user: currentUser(),
        incubatorIndex: $incubatorIndex,
        pokeball: $pokeball,
    );

    ToastHelper::success('Congratulations!', "You caught {$pokemon->getDisplayName()}!");

    return back();
}
```

**When a rule is broken:** the action throws a `DomainException`; the Inertia exception handler in `AppServiceProvider` turns it into a warning toast with the exception's message and redirects back. Controllers never catch these.

### 3.5 Actions

Actions hold the business logic. Everything that changes data goes through one, whoever calls it: a controller, a console command, a listener or another action.

#### Shape

- `final class {Verb}{Noun}Action` with **one public method, `handle()`**, and nothing else public or protected (enforced).
- Called as `new SomeAction()->handle(...)`. Actions are **not injected through the container**: an action keeps its inputs and what it loaded in private properties for the length of one call, so each call gets a fresh object, and there is nothing to inject (it uses facades, models and other actions created the same way).
- Parameters are **models and value objects, not IDs**: `handle(User $user, IncubatorIndex $incubatorIndex, Pokeball $pokeball)`.

#### Why models, not IDs

- **Type safety.** `handle(User $user, Pokeball $pokeball)` cannot be called with the arguments swapped. Two `string $userId, string $pokeballId` UUIDs can, and nothing would notice until the data is wrong.
- **Reuse and tests.** Callers usually already hold the model (a controller from route binding or `currentUser()`, another action, a command, a listener), so they pass it along without another query. Tests pass factory models directly.
- **Staleness is handled inside.** The model passed in may be older than the database (another request may have changed it since it was loaded). Actions that depend on its columns call `->refresh()` or lock its row inside their lock before guarding (see [When to call `refresh()`](#when-to-call-refresh)), so taking a model costs nothing in correctness.

Value objects are passed for the same reason: an `IncubatorIndex` is known to be in range, an `int` is not.

#### Method order

An action reads top to bottom, in the order it runs:

1. **Properties**: the inputs first, in `handle()`'s parameter order, then the state the action loads or produces (nullable ones default to `null`).
2. **`handle()`**: stores the inputs in properties, takes the lock (if any) around the work method, dispatches events after the work, returns the result.
3. **The work method**, named after the action (`catchPokemon()`, `hatchEgg()`, `takeTurn()`): `load*()` calls, then `ensure*()` guards, then the writes (inside `DB::transaction` when there are several).
4. **The private methods, in the order the work method first calls them**: loaders, guards, checked getters, then the steps that write.

```php
final class CatchPokemonAction
{
    private User $user;                     // 1. inputs, in handle() order
    private IncubatorIndex $incubatorIndex;
    private Pokeball $pokeball;
    private ?Incubator $incubator = null;   //    loaded state
    private Pokemon $pokemon;               //    produced state

    public function handle(User $user, IncubatorIndex $incubatorIndex, Pokeball $pokeball): Pokemon
    {
        $this->user           = $user;       // 2. store inputs
        $this->incubatorIndex = $incubatorIndex;
        $this->pokeball       = $pokeball;

        Cache::withoutOverlapping(           //    lock around the work
            key: LockKeys::incubatorCatchPokemon($this->user->id, $this->incubatorIndex),
            callback: fn () => $this->catchPokemon()
        );

        return $this->pokemon;
    }

    private function catchPokemon(): void    // 3. the work method
    {
        $this->loadIncubator();              //    load

        $this->ensureIncubatorIsActive();    //    guard
        $this->ensureEggIsReadyToCatch();

        DB::transaction(function (): void {  //    write, all or nothing
            new RemoveInventoryItemAction()->handle($this->user, $this->pokeball);

            $this->pokemon = new CreatePokemonAction()->handle(
                owner: $this->user,
                newPokemon: NewPokemon::fromIncubation($this->activeIncubation()),
                pokeball: $this->pokeball
            );

            $this->markEggAsCaught();
        });

        PokemonCaught::dispatch($this->pokemon->id, $this->user->id);   // event after the commit
    }

    private function loadIncubator(): void { /* … */ }             // 4. helpers in call order
    private function ensureIncubatorIsActive(): void { /* … */ }
    private function ensureEggIsReadyToCatch(): void { /* … */ }
    private function activeIncubation(): Incubation { /* … */ }
    private function markEggAsCaught(): void { /* … */ }
}
```

(Comments here only explain the example; real actions have none.)

#### `load*()`, `ensure*()` and checked getters

Loading and deciding are kept apart, so each method has one job and its name says it:

- **`load{Noun}()`** only loads, into a property. A missing record becomes `null`; the loader never decides whether that is allowed. (`loadIncubator()`, `loadEncounter()`, `loadPlayerPokemon()`.)
- **`ensure{Condition}()`** is a guard. It takes no arguments (it reads the properties), checks one rule and throws when the rule is broken. Its name states the rule that must hold: `ensureIncubatorIsActive()`, `ensureOwnersBoxIsNotFull()`, `ensureNoBattleIsOngoing()`. Guards use `throw_if` / `throw_unless`; when the exception needs a computed value, it is built in a closure so the value is only computed when the guard fails:

  ```php
  throw_if($combatant->isFainted(), fn () => new PokemonHasFaintedException($this->chosenDisplayName()));
  ```

- **Checked getters** return a value that a guard has already checked, with its non-null type: `activeIncubation(): Incubation`, `existingEncounter(): Encounter`. If one is called before its guard, it throws a `LogicException`:

  ```php
  private function activeIncubation(): Incubation
  {
      return $this->incubator->incubation ?? throw new LogicException('The incubator must be checked as active first.');
  }
  ```

  This gives PHPStan a non-null type everywhere after the guard, without `!` assertions or repeated null checks, and turns a mistake in the action's own order into a loud failure (see [3.6](#36-exceptions-which-one-to-throw)).

#### Concurrency: when to lock

Players double-click, open two tabs and retry. An action whose guard reads saved data and then writes based on it has a race: two requests can both pass the guard before either writes ("the incubator is idle" → two eggs placed). Locks close that gap.

**Use `Cache::withoutOverlapping` when the action guards on saved data and then writes.** The lock wraps the work method, so the load, the guards and the writes all run while no other call with the same key can run. Keys come from the domain's `Support/LockKeys` class, never inline strings:

```php
Cache::withoutOverlapping(
    key: LockKeys::incubatorPlaceEgg($user->id, $incubatorIndex),
    callback: fn () => $this->placeEgg()
);
```

Choosing the key: **lock on what the conflicting requests share.**

- Per user and incubator for placing, hatching and catching (`incubatorPlaceEgg($userId, $index)`): different incubators don't conflict.
- Per user for purchasing (`incubatorPurchase($userId)`): every purchase rewrites the same `purchased_incubator_indexes` column, whichever incubator it buys.
- Per battle for anything that changes a battle (`battle($battleId)`): taking a turn, throwing a ball, switching.
- Actions that conflict with each other share a key: `ExploreAction` and `BattleEncounterAction` both use `LockKeys::encounter($userId)`.

**Guards run inside the lock**, as the first lines of the work method, right after loading or refreshing what they check. A guard that reads data outside the lock lets two requests pass it before either acts.

**Use `lockForUpdate()` instead when the action can run inside a caller's transaction.** A cache lock is released when its callback returns, which is *before* an outer `DB::transaction` commits, so it cannot protect nested work. An action that guards on saved data and may run inside another action's transaction locks the **row that owns the data**, inside its own transaction, before its guards. The database holds that lock until the outermost transaction commits.

How it works in the database:

- `lockForUpdate()` adds `FOR UPDATE` to the `SELECT` (`select * from users where id = ? for update`). The database reads the row and puts an **exclusive row lock** on it, owned by the current transaction.
- Any other transaction that tries to lock the same row (another `FOR UPDATE`), or to update or delete it, **waits** at that statement until the lock is released. It doesn't fail or skip; it blocks.
- The lock is released only when the transaction that took it **commits or rolls back**, never when the statement or the PHP method ends. Laravel runs a nested `DB::transaction` as a *savepoint* inside the outer one, so there is a single real transaction and the lock lasts until the **outermost** commit. That is why the lock still protects `CreatePokemonAction` after it returns to `CatchPokemonAction`, while a cache lock would not.
- Outside a transaction, every statement commits on its own, so the lock would be released immediately and protect nothing. `lockForUpdate()` is only useful inside `DB::transaction`.
- **Plain `SELECT`s are not blocked.** MySQL (InnoDB) and PostgreSQL let ordinary reads see the last committed version of the row without waiting. The lock only orders transactions that also ask for it, so **every action that guards on the same data must lock the same row**. One that reads without locking can still pass its guard on old data.
- **Lock the owning row, not the rows being checked.** `CreatePokemonAction` guards on how many Pokémon the user has, but the row that would break the rule (the new Pokémon) doesn't exist yet, so there is nothing to lock in `pokemon`. It locks the user's row instead, as a "one at a time per user" token: a second catch waits on `lockOwner()` until the first one commits, then counts the box with the new Pokémon in it.
- After the lock is taken, **re-read what the guards check**. A model loaded before the lock may be stale, which is why `AwardExperienceAction` calls `refresh()` right after `lockPokemon()`.
- Keep locking transactions short, and lock rows in the same order everywhere (owner first, then what it owns). Two transactions that lock the same rows in opposite orders deadlock, and the database aborts one of them.

**On SQLite, `lockForUpdate()` does nothing.** SQLite has no row locks, and Laravel's SQLite grammar compiles the call to a plain `SELECT`. SQLite instead allows only one writing transaction for the whole database at a time, so two conflicting writes don't both succeed, but there is no per-row waiting as described above. This project currently runs on SQLite locally and in tests. The row-lock guarantees above hold on MySQL, MariaDB and PostgreSQL, and tests can't prove the race is closed: they show the code works, not that it's locked.

```php
// CreatePokemonAction runs inside CatchPokemonAction's and ThrowBallAction's transactions.
return DB::transaction(function () {
    $this->lockOwner();                 // User::query()->whereKey($this->owner->id)->lockForUpdate()->firstOrFail();

    $this->ensureOwnersBoxIsNotFull();  // two catches in different incubators can't both pass

    // …
});
```

**No lock is needed for an atomic conditional write**, where the database checks and writes in one statement. `RemoveInventoryItemAction` decrements only where there is enough, then throws if no row changed:

```php
$affectedRows = InventoryItem::query()
    ->whereBelongsTo($this->user)
    ->forItem($this->item)
    ->where('quantity', '>=', $this->quantity)
    ->decrement('quantity', $this->quantity);

throw_unless($affectedRows > 0, new InsufficientInventoryException($this->item));
```

**No lock is needed for actions that only read** (`ListIncubatorsAction`, `CalculateSpeciesCatchStatisticsAction`) or that are only ever called from inside another action's lock and guard on nothing saved (`EndBattleAction`, called by `SaveTurn` and `ThrowBallAction` under the battle lock).

#### When to call `refresh()`

Call `$model->refresh()` **as the first line inside the lock** when the action guards on, or writes back, **columns of a model that was passed in**. The model may have been loaded before another request changed the row; without the refresh, the guard checks old data and the write overwrites the newer change.

- `PurchaseAction` refreshes the user, because it reads `purchased_incubator_indexes`, appends one, and saves the whole array. With a stale user, a second purchase would erase the first. Its test proves it: *"does not overwrite a purchase made after the user instance was loaded"*.
- `TakeTurnAction` and `ThrowBallAction` refresh the battle, because they rewrite the whole `state` JSON.
- `EvolvePokemonAction` refreshes the Pokémon, because it guards on and changes `species_id`.
- `AwardExperienceAction` locks the row with `lockForUpdate()` **then** refreshes, because it adds to the current `experience`.

Refresh **inside** the lock, never before it: a refresh before the lock reads data another request can still change.

No refresh is needed when the guard reads through a **new query** (`$this->user->pokemon()->count()`, `$this->user->battles()->ongoing()->exists()`, `new ListIncubatorsAction()->handle($user)`), because relation queries always go to the database. It is the model's own attributes that can be stale.

#### Transactions and events

- Wrap writes in `DB::transaction` when there is more than one, so they all happen or none do. Guards and loading stay outside the transaction (but inside the lock).
- Another domain's action whose result is part of this action's work (creating the Pokémon, removing the ball) is called **inside** the transaction, so it commits or rolls back with the rest.
- Events are dispatched **after** the transaction, never inside it. See [3.10](#310-events-and-listeners).

### 3.6 Exceptions: which one to throw

Three kinds of exception mean three different things. Picking the right one decides what the player sees and whether a developer gets an error report.

| Exception | Means | Example | What happens |
| --- | --- | --- | --- |
| A `Shared\Base\DomainException` subclass, in `{Domain}/Exceptions/` | **The player** asked for something the game rules don't allow right now. Expected; part of normal play. | `BoxFullException`, `IncubatorNotIdleException`, `PokemonHasFaintedException` | The Inertia exception handler shows a warning toast titled "Unable to complete this action" with the exception's message, and redirects back. Console commands catch it and print the message. |
| `InvalidArgumentException` | **The calling code** passed a value that can never be valid. | A quantity below 1, a move slot of 7, a value object constructed with a level of 0 | Not handled: a 500 and an error report. The UI and the FormRequest should make it unreachable, so reaching it is a bug. |
| `LogicException` | **The code itself** ran in the wrong order, or reached a state its own earlier checks should rule out. | A checked getter called before its guard; `EndBattleAction` asked to end a battle that already ended; `RandomHelper` running out of faked values in a test | Not handled: a 500 and an error report. It is always a bug. |

Why not use a `DomainException` for everything? It would show the player a message about a bug as if they had done something wrong, and the bug would never reach the error logs. Why throw instead of carrying on? A bug that fails where it happens is easy to find; one that writes wrong data shows up far away, later.

Why `LogicException` in checked getters specifically: besides catching a wrong call order, `?? throw new LogicException(...)` narrows a nullable property to a non-null type for PHPStan, so the rest of the action needs no null checks.

**Writing a `DomainException`:**

```php
final class IncubatorNotActiveException extends DomainException
{
    public function __construct(IncubatorIndex $incubatorIndex)
    {
        parent::__construct("The incubator #{$incubatorIndex->value} has not been purchased or is not going through any incubation process.");
    }

    public function getKey(): string
    {
        return 'incubator';
    }
}
```

- The message is the text the player reads: a full sentence, starting with an uppercase letter and ending with punctuation.
- `getKey()` names what the error is about (`'incubator'`, `'pokemon'`, `'encounter'`). It is reserved as the key under which the error will be reported to forms (for example `errors.incubator`); the current handler shows only the message.
- Never catch a `DomainException` in a controller to turn it into something else.

### 3.7 Value objects

A value object is a small immutable value defined by what it contains, not by an identity: `IncubatorIndex`, `PokemonStats`, `Combatant`, `MoveOutcome`, `NewPokemon`.

#### Shape

- `final readonly class`, implementing `Shared\Interfaces\ValueObject` with `equals(ValueObject $other): bool`.
- Public promoted properties, or a validated public `$value` for single values (`IncubatorIndex::$value`).
- Properties and constructor parameters use **camelCase**, like any PHP property (`currentHp`, `isShiny`, `hasHit`), even when they hold the same value as a snake_case column (`Combatant::$speciesId`, `Pokemon::$species_id`).
- The **stored format** keeps snake_case keys, like database columns: the keys `toArray()` writes and `fromArray()` reads (`'current_hp'`), the JSON a cast saves, and the PHPStan array shapes that describe them (`CombatantArray`). Renaming a property never changes what is saved, so saved rows and seed files keep working.

#### Why camelCase in PHP and snake_case in the database

The rule is: **PHP objects use camelCase; the database (and Eloquent models, which are magic access to columns) uses snake_case. A value object's cast, `fromArray()` and `toArray()` are where one becomes the other**, the same way Eloquent attributes sit between columns and code.

Why value object properties are camelCase:

- **It matches every other PHP class.** Action properties (`$incubatorIndex`), Data classes (`TurnData::$moveSlot`), events (`PokemonCaught::$pokemonId`), Support classes (`Incubator::$isPurchased`), local variables and parameters are all camelCase. Models are the only exception, because their attributes are columns, not real properties.
- **Promoted properties are also constructor parameters and named arguments.** snake_case properties would force calls like `new Combatant(max_hp: …)` next to `new TurnData($battle, moveSlot: …)`. One casing makes every named argument easy to read and guess.
- **A value object is a domain concept, not a database row.** Many are never stored (`MoveOutcome`, `CatchContext`, `NewPokemon`). Tying casing to "is it stored?" would mean renaming a value object when it starts being stored, and knowing how it's stored before naming it. How it's stored is the cast's job.
- **One name per value across layers.** The variable is `$currentHp`, the property `currentHp`, the page prop `currentHp` and the TypeScript field `currentHp`. The glossary's recipe (`is_shiny` column, `isShiny` variable) applies to properties too.

Why the stored keys stay snake_case:

- **One style per database.** Columns are snake_case, so JSON keys inside a column (`battles.state`, `species.sprites`) match them. A row read in a SQL client or Telescope follows one convention, and JSON queries read like column queries (`whereNotNull('sprites->front_default')` next to `where('is_shiny', true)`).
- **External data fits as-is.** The PokeAPI seed files use snake_case sprite keys, so the seeder writes them straight through.
- **Saved data never depends on PHP names.** Renaming a property is a code-only change; existing rows need no migration.

The trade-offs, and how to handle them:

- **Each stored field has two spellings** (`currentHp` / `current_hp`), mapped by hand in `fromArray()`, `toArray()`, the cast and the PHPStan shape. Keep that mapping in those places only, and give every stored value object a round-trip test (build → `toArray()` → `fromArray()` → `equals()`) so the two lists can't drift apart.
- **Stored keys appear in some strings:** JSON paths in queries (`'sprites->front_default'`) use the stored key, while `array_column($combatants, 'pokemonId')` and spread arguments (`new Combatant(...[...$values, 'currentHp' => 0])`) use the PHP name. A find-and-replace rename must tell the two apart; PHPStan won't flag a JSON path.
- **Code that copies a model into a value object mixes casings** (`isShiny: $incubation->is_shiny`). That's expected: it is the boundary between database data and PHP objects.
- Changes go through `with{Change}()` methods that return a new instance (`$combatant->withCurrentHp(12)`), never through mutation.
- Conversions are named `from{Source}()` / `to{Target}()`: `Combatant::fromArray()`, `toArray()`, `NewPokemon::fromIncubation()`, `FilterOption::fromEnum()`.
- Stored in a column through a dedicated `As{ValueObject}` cast in `Shared/Casts` (`AsBattleState`, `AsIncubatorIndex`).

#### Why value objects validate their data

The constructor checks every rule and throws `InvalidArgumentException` when one is broken:

```php
public function __construct(int $value)
{
    $max = (int) config('game.user.max_incubators');

    throw_if($value < 1 || $value > $max, InvalidArgumentException::class, "The incubator index must be between 1 and {$max}.");

    $this->value = $value;
}
```

- **If the object exists, it is valid.** Because it is `readonly`, it stays valid forever. Code that receives an `IncubatorIndex` or a `Combatant` never re-checks ranges; the type is the proof.
- **Every way in is checked.** Building one in code, reading one from a JSON column through its cast (`Combatant::fromArray()` calls the constructor), and every `with*()` copy (which calls `new self(...)`) all go through the same checks. Corrupt saved data fails when it is read, not three steps later.
- **Bugs fail where they happen.** A wrong current HP is rejected when the combatant is built, not when the battle page shows −14 HP.
- **The same rule serves the HTTP layer.** The `incubatorIndex` route binding in `AppServiceProvider` builds an `IncubatorIndex` and turns its `InvalidArgumentException` into a 404, so the range rule lives in one place.

The exceptions are `InvalidArgumentException` because an invalid value object is always a programming mistake (the player's input is validated earlier, by a FormRequest or a binding), see [3.6](#36-exceptions-which-one-to-throw).

#### Value objects vs other plain classes

| Kind | Mutable? | Validated? | Stored or shared? | Example |
| --- | --- | --- | --- | --- |
| Value object (`ValueObjects/`) | No | Yes | Yes: columns, action inputs, across domains | `IncubatorIndex`, `Combatant` |
| Data class (`Data/`) | Yes | No (the value objects it holds are) | No: lives only inside one pipeline run | `TurnData` |
| Support read model (`Support/`) | No | No | Built by an action for its callers | `Incubator` (an index, whether it's purchased, its active incubation) |
| Filters (`Filters/`) | – | Parsed leniently from the request | Passed to a Query and back to the page | `SearchBoxFilters` |

### 3.8 Mechanics and randomness

Mechanics hold the game's rules as pure code: formulas, odds and rolls. They are what the specs describe in numbers.

- `{Noun}Calculator`: math with **no randomness** (`DamageCalculator`, `ExperienceCalculator`, `TurnOrderCalculator::firstMoverChance()`). Random values it needs (a damage spread) are passed in as arguments.
- `{Noun}Roller`: **rolls dice**, usually by asking a Calculator for the odds and rolling them (`TurnOrderRoller::rollIsPlayerFirst()`, `EncounterRoller`, `MoveOutcomeRoller`).
- `{Noun}Checker`: answers "is this allowed?", returning `null` or a reason (an enum or string) when not, or a plain `bool` for a plain yes or no (`EvolutionRequirementChecker::check()` returns the unmet `EvolutionRequirement` or `null`).

Mechanics **never touch the database, HTTP, sessions or auth** (enforced by an arch rule). They may read the models and relations they are given, and `config('game.*')`. The caller (an Action, Query or PageProps) loads the data and saves the result. This keeps every formula testable with plain values and reusable from a page (showing a catch chance) and an action (rolling it) alike.

**All randomness goes through `Shared\Helpers\RandomHelper`** (`chance()`, `integer()`, `float()`, `pick()`), never `rand`, `random_int`, `shuffle`, `Arr::random()`, `Collection::random()`, `Str::random()` or `Lottery`. Chances are **percentages from 0 to 100** everywhere (config, columns, calculators), and `chance()` throws outside that range. Tests decide every roll with `RandomHelper::fake([true, 42, …])`, which returns the given values in order and throws when they run out, so a test that rolls more often than expected fails loudly. `tests/Pest.php` resets it after each test.

Only Rollers (and pipes or actions with a `roll*()` method, such as `PlaceEggAction::rollIsShiny()`) roll. The one exception is picking random rows in a query with `inRandomOrder()`, which stays in the query instead of loading every row to pick one.

### 3.9 Pipelines: Pipes and Data

When an action is a long sequence of steps (a battle turn has more than twenty), it becomes a Laravel pipeline.

- The **action** keeps its lock and its `refresh()`, builds a **Data class** and sends it through the pipes:

  ```php
  $turn = new TurnData($this->battle, moveSlot: $this->moveSlot);

  Pipeline::send($turn)
      ->through([
          EnsureBattleIsReadyForTurn::class,
          LoadMovesAndSpecies::class,
          EnsurePlayerMoveIsUsable::class,
          ChooseOpponentMove::class,
          RollTurnOrder::class,
          TakeActions::class,
          ApplyEndOfTurnDamage::class,
          CreditOpponentKnockout::class,
          SaveTurn::class,
      ])
      ->thenReturn();
  ```

- Each **pipe** lives in `Pipes/{Pipeline}/`, is named after its step (`RollTurnOrder`, `DealHits`, `EnsureBattleIsReadyForTurn`), and has one public method, `handle({Noun}Data $data, Closure $next): {Noun}Data`. Pipes follow the action rules (guards in `ensure*`-named pipes or methods) and the mechanics rules (randomness only through Rollers).
- **A pipe stops the pipeline by not calling `$next`**, after recording why on the Data class (a missed move, an immune target). Work that must happen however the pipeline ends belongs in the caller, after the pipeline, not in a last pipe.
- **A step that repeats runs its own nested pipeline** with its own Data class: `TakeActions` sends a `TurnActionData` through the `Pipes/TurnAction/` pipes once per side, so an early stop ends only that side's action.
- Pipes can be **reused by another action**: `ThrowBallAction` sends a `TurnData` through part of the same turn pipeline when the Pokémon breaks free.

The **Data class** (`TurnData`, `TurnActionData`) is `final`, mutable and unvalidated: public properties the pipes read and write, with the action's inputs as `readonly` constructor properties. It only holds state and methods that read or replace it (`activeCombatant()`, `replaceActiveCombatant()`, `log()`), plus checked getters that throw a `LogicException` before their value exists. Game rules, rolls and queries stay in pipes and mechanics. State for one repetition of a step goes in its own Data class kept on the parent (`TurnData::$playerAction`), never in flat fields that must be reset between repetitions.

Pipelines are **tested through their action**: the action's tests are the contract for the whole pipeline. A pipe gets its own test only when it gains logic the action's tests can't reach.

### 3.10 Events and listeners

Domains talk to each other in two ways:

1. **Directly**, when another domain's action is part of this action's own work and must commit with it. `CatchPokemonAction` calls `Inventory`'s `RemoveInventoryItemAction` and `Pokemon`'s `CreatePokemonAction` inside its transaction; `EndBattleAction` pays experience through `AwardExperienceAction`. If any part fails, nothing is saved.
2. **Through events**, for everything else. When an action finishes something other domains may care about, it announces it, and those domains react in their own listeners. The action does not know who listens. (Missions advancing when a Pokémon is caught is the planned first use.)

Events:

- live in the domain that raises them, are named in the past tense (`PokemonCaught`, `PokemonLevelledUp`, `BattleEnded`);
- are `final readonly` classes with the `Dispatchable` trait, carrying **only IDs and plain values**, never models (a queued listener gets a small, serialisable payload and loads fresh data itself);
- implement `ShouldDispatchAfterCommit` and are dispatched **after** the action's transaction. Even when the action itself runs inside a caller's transaction, the event waits for that outer commit. Listeners therefore only hear about saved data, and a failing listener never undoes the change that raised the event.

Listeners:

- live in `Listeners/` of the domain that **reacts**, are named `{Verb}{Noun}On{Event}` (`AdvanceMissionsOnPokemonCaught`) and have `handle({Event} $event)`;
- are discovered automatically from that type-hint; never register them by hand;
- run synchronously or implement `ShouldQueue`, decided per feature. A queued listener sets `$tries` and `$backoff` and is **idempotent** (handling the same event twice changes nothing the second time), so retries are safe and a listener that keeps failing waits in `failed_jobs` for `php artisan queue:retry`.

### 3.11 The HTTP layer

#### Routes

- Each domain has `Http/Web/routes.php` (and `Http/Api/routes.php` if it has JSON endpoints), registered in `bootstrap/app.php` with a `{domain}.web.` or `{domain}.api.` name prefix.
- Route files reference controllers only (`[Controller::class, 'index']`, an invokable `Controller::class`, or `Route::inertia()` for static pages), never closures. They add the last name segment, the middleware and constraints:

  ```php
  Route::prefix('/incubator/{incubatorIndex}')
      ->name('incubators.')
      ->whereNumber('incubatorIndex')
      ->group(function (): void {
          Route::post('/purchase', PurchaseController::class)->name('purchase');  // pokemon.web.incubators.purchase
      });
  ```

- Links and redirects always use `route('name')` on both sides (PHP's helper and the generated TypeScript one), never `url()` or hard-coded paths.
- A route parameter that is a value object gets a binding in `AppServiceProvider::configureRouteBindings()`, which turns an invalid value into a 404.

#### Controllers

Controllers are thin: they resolve inputs, call one action or build one PageProps, flash a toast and respond.

- `final`, extend `Shared\Base\Controller`, no private or protected methods (enforced: logic that needs a helper method belongs in an action or a PageProps).
- Public methods are limited to `__invoke`, `index`, `show`, `create`, `store`, `edit`, `update`, `delete`, `destroy`.
- **Pages** use resource-style methods (`BoxController::index()`, `PokedexController::show()`). **Each form action gets its own invokable controller** (`PurchaseController`, `PlaceEggController`, `TakeTurnController`), so every POST endpoint maps to exactly one action.
- The authenticated user is always `currentUser()`, never `Auth::user()` or `auth()->user()`.
- Success toasts use `ToastHelper::success($title, $message)`. Titles start uppercase with no ending punctuation; messages start uppercase and end with `.`, `!` or `?`.

#### Form requests

All validation lives in a `FormRequest` with `rules()`, never `$request->validate()` in a controller. Input keys are camelCase, like the frontend (`pokeballId`, `moveSlot`). Limits come from config (`'between:1,' . config('game.pokemon.max_active_moves')`).

#### PageProps

A PageProps class builds the props of one Inertia page.

- `final`, implements `ProvidesInertiaProperties`, only `__construct` and `toInertiaProperties()` are public. Records the page is about come in through the constructor (`new PokedexShowPageProps($species)`).
- Each prop is a **closure** pointing at a private `get{Name}Prop()` method (`'incubators' => $this->getIncubatorsProp(...)`). Inertia only evaluates the props a request asks for, so a partial reload (`only: ['incubators']` after placing an egg) runs only that query.
- Use Inertia's prop types where they help: `Inertia::optional(...)` for data loaded only on demand (the "Pokémon found here" dialog), `->once()` for data that doesn't change between visits, `Inertia::once()` / `shareOnce()` for shared data.
- Props map models to **exactly the fields the page uses**, in camelCase (`frontSprite`, `readyToHatchAt`). Never send whole models.
- Paginated lists use `->paginate($n)->withQueryString()` and `AppHelper::paginatorToArray($paginator, fn ($row) => [...])`, which keeps the meta keys camelCase. No `simplePaginate` or `cursorPaginate`.

#### Queries and filters

- A **Query** (`SearchBoxQuery`) builds a complex or reused read and returns the `Builder`, so callers can paginate, limit or select. It calls Eloquent directly; there is no repository layer. Each filter is a private `apply{Filter}()` step chained with `->when(...)`.
- A **Filters** class (`SearchBoxFilters`) turns the query string into typed values (`fromRequest()`, lenient: unknown values are dropped, missing ones get defaults) and lists the choices the page offers (`filterOptions()`, built from enums with `FilterOption::listFromEnum()`). The PageProps sends both the options and the active filters to the page.
- Controllers may build small queries themselves (an autocomplete endpoint reusing `SearchPokedexQuery` with `->limit(5)`).

#### Middleware

Global middleware lives in `Shared/Middlewares` (`HandleInertiaRequests` shares the app name, the user and enum labels). Middleware used by one domain's routes lives in that domain's `Http/Web/Middlewares` (`ShareAuthLayoutProps`).

### 3.12 Models, migrations, enums and casts

#### Models (`src/Shared/Models`)

- `final`, UUID primary keys (`HasUuids`), `#[Unguarded]` unless a model holds something sensitive, in which case `#[Fillable([...])]` with an explicit list (`User`).
- Casts in a `casts()` method, never the `$casts` property. Value objects use `As{ValueObject}` cast classes, never inline `Attribute` casts.
- Scopes use the `#[Scope]` attribute on protected methods (`inParty()`, `ongoing()`, `owned()`).
- A PHPDoc block lists every column and relation with its type, for PHPStan.
- Models hold relations, scopes and small questions about themselves (`isGenderless()`, `getDisplayName()`, `getStats()`), not business logic: changes go through actions.
- `Model::shouldBeStrict()` and `Model::automaticallyEagerLoadRelationships()` are on globally. Add `->with()` only to override for a specific case (selecting columns, for instance).

#### Migrations

- Numbered `001_create_…`, `002_create_…` instead of timestamps, so the files read in dependency order.
- **Until the game has live data, change the table's existing create migration** instead of adding `add_…` / `drop_…` migrations; only a new table gets a new file. Rebuild with `php artisan migrate:fresh --seed`. This rule ends at the first release.
- `foreignIdFor(Model::class)->constrained()` for model references; real `down()` methods.
- Enum columns are real database enums: `$table->enum('status', Arr::pluck(BattleStatus::cases(), 'value'))`.

#### Enums

- Backed enums with `UPPER_SNAKE_CASE` cases and lowercase (or int) values.
- A `label()` method with a `match` when the UI needs a display name; without one, labels fall back to the headline-cased case name.
- In `Shared/Enums` when stored in a column or used by several domains, otherwise in the domain (see [3.2](#32-domains)). Each enum the frontend uses has a hand-written TypeScript copy in the matching place (see [4](#4-frontend)).

#### Casts (`src/Shared/Casts`)

`final` classes named `As{ValueObject}` implementing `CastsAttributes`. `get()` builds the value object (so a stored value is validated when read), `set()` refuses anything that isn't the value object and returns its stored form.

### 3.13 Support, helpers and global configuration

**Domain `Support/`**

- `LockKeys` and `CacheKeys`: static methods that build every lock and cache key, never inline strings. Format: `{domain}::{lock|query}:{what}:{scope}-{id}` (`pokemon::lock:incubator-place-egg:user-{id}:incubator-{n}`, `dex::query:species-base-moves:species-{id}`). One place lists every key a domain uses, and typos can't split a lock in two.
- Small read models an action builds for its callers (`Incubator`, returned by `ListIncubatorsAction` and used by the Nursery page and the incubator actions).

**`Shared/Helpers`**

| Helper | Use |
| --- | --- |
| `currentUser()` | The authenticated `User`, typed. Use it instead of `Auth::user()`. |
| `RandomHelper` | All randomness (see [3.8](#38-mechanics-and-randomness)). |
| `ToastHelper` | `success()`, `error()`, `info()`, `warning()` flash a toast to the next page. |
| `AppHelper` | `paginatorToArray()`, `allIncubatorIndexes()`. |
| `enumLabels()` | `[value => label]` for an enum, shared with the frontend. |
| `value_object_array_contains()`, `value_object_array_diff()` | Array operations that compare value objects with `equals()`. |

**`AppServiceProvider`** sets the global behaviour, one private method per concern:

- immutable dates (`Date::use(CarbonImmutable::class)`), strict models, automatic eager loading, destructive DB commands blocked in production, production password rules, HTTPS in production;
- Inertia page names: `Module::Path/Page` resolves to `resources/js/app/Module/pages/Path/Page/index.vue`; any other format throws;
- the Inertia exception handler (`DomainException` → warning toast and back; too many requests → toast);
- rate limiters (`login`, `registration`, `autocomplete`);
- route bindings for value objects;
- the morph map for polymorphic relations (inventory items);
- collection macros (`toUniqueEnums()`).

### 3.14 Naming reference

Before naming anything game-related, check [`docs/specs/glossary.md`](docs/specs/glossary.md): one name per idea, used in specs, code, database, tests and UI.

**Classes**

| Kind | Pattern | Examples |
| --- | --- | --- |
| Action | `{Verb}{Noun}Action` | `CatchPokemonAction`, `TakeTurnAction`, `ListIncubatorsAction` |
| Query | `{Purpose}Query` | `SearchBoxQuery`, `AreaSpeciesQuery` |
| Filters | `{Purpose}Filters` | `SearchBoxFilters` |
| PageProps | `{Page}{Index\|Show}PageProps` or `{Page}PageProps` | `BoxIndexPageProps`, `NurseryPageProps` |
| Controller | `{Noun}Controller` (pages), `{Verb}{Noun}Controller` (one action) | `BoxController`, `TakeTurnController` |
| Form request | `{Verb}{Noun}Request` | `CatchPokemonRequest`, `StoreLoginRequest` |
| Exception | `{Noun}{Problem}Exception` | `BoxFullException`, `IncubatorNotIdleException` |
| Event | `{Noun}{PastTenseVerb}` | `PokemonCaught`, `BattleEnded` |
| Listener | `{Verb}{Noun}On{Event}` | `AdvanceMissionsOnPokemonCaught` |
| Mechanics | `{Noun}Calculator` / `Roller` / `Checker` | `DamageCalculator`, `EncounterRoller` |
| Pipe | Imperative step | `RollTurnOrder`, `DealHits`, `SkipFaintedAttacker` |
| Data | `{Noun}Data` | `TurnData`, `TurnActionData` |
| Cast | `As{ValueObject}` | `AsBattleState` |
| Helper | `{Purpose}Helper` | `RandomHelper` |

**Methods**

| Pattern | Means | Examples |
| --- | --- | --- |
| `handle()` | The one public method of Actions, Queries, Pipes, Listeners and middleware. | |
| `{verb}{Noun}()` | An action's work method, named after the action. | `catchPokemon()`, `hatchEgg()`, `takeTurn()` |
| `load{Noun}()` | Loads into a property; decides nothing. | `loadIncubator()`, `loadWildSpecies()` |
| `ensure{Condition}()` | Guard: no arguments, throws when the condition is false. | `ensureIncubatorIsIdle()`, `ensureBattleIsWild()` |
| `{qualifier}{Noun}()` | Checked getter: non-null value after its guard. | `activeIncubation()`, `existingEncounter()`, `wildCombatant()` |
| `lock{Noun}()` | `lockForUpdate()` on the owning row. | `lockOwner()`, `lockPokemon()` |
| `mark{Noun}As{State}()` | Saves a state change. | `markEggAsCaught()`, `markIncubatorAsPurchased()` |
| `create{Noun}()` / `add{Noun}()` | Writes new rows. | `createIncubationRecord()`, `addBaseMoves()` |
| `build{Noun}()` | Builds an object in memory. | `buildPlayerCombatant()` |
| `roll{Noun}()` / `rollIs{Adjective}()` | Rolls a random result. | `rollEvolutionStage()`, `rollIsShiny()`, `rollIsCaught()` |
| `is…()` / `has…()` / `can…()` | Answers yes or no. | `isFainted()`, `hasCombatantsLeft()`, `hasRoomInParty()` |
| `with{Change}()` | Copy of a value object with one change. | `withCurrentHp()`, `withActiveIndex()` |
| `from{Source}()` / `to{Target}()` | Conversions. | `fromRequest()`, `fromArray()`, `toArray()` |
| `get{Name}Prop()` | Builds one PageProps prop. | `getIncubatorsProp()` |
| `apply{Filter}()` | One step of a Query. | `applyTypes()`, `applySorting()` |
| Calculators: what they compute | | `firstMoverChance()`, `experienceForLevel()`, `typeMultiplier()` |

Booleans read as questions (`$isAdded`, `$hasOngoingBattle`, `$shouldJoin`, `isShiny`). Abbreviations are avoided (`$incubatorIndex`, not `$idx`).

---

## 4. Frontend

### Folder layout

```
resources/js/
├── app.ts                      Inertia app: page resolution, default layout, title
├── app/{Domain}/               One folder per backend domain
│   ├── pages/{Path}/{Page}/    One folder per page
│   │   ├── index.vue           The page
│   │   └── SpeciesHero.vue     Components used only by this page
│   ├── components/             Components used by several pages of this domain
│   ├── layouts/                Layouts used by this domain (AuthLayout)
│   ├── composables/            Composables used by this domain (useBattleLayout)
│   └── types/
│       ├── page-props/         One type file per PageProps class
│       └── enums/              TypeScript copies of this domain's PHP enums
└── shared/
    ├── components/
    │   ├── ui/                 shadcn-vue primitives (managed by the CLI, never edited by hand)
    │   ├── layout/             App chrome: sidebar, nav, page header, breadcrumbs, toaster
    │   ├── form/               Form wrappers (AppInput, AppSelect, AppCheckbox…)
    │   ├── list/               Pagination, sortable table heads, search filter dialog
    │   ├── content/            Sections, info cards, empty states, indicators, countdown
    │   └── game/               Game-aware components used by 2+ domains (SpeciesSprite, PokemonTypeBadges)
    ├── composables/            useSearchForm, useSortable
    ├── layouts/                AppLayout, AuthenticatedLayout
    ├── lib/                    Plain utilities (cn(), removeFalsyValues(), the test-ID stripper)
    └── types/
        ├── enums/              TypeScript copies of Shared PHP enums
        ├── response/           Shapes of models as the frontend sees them (Pokemon, Species…)
        ├── helpers/route.ts    Generated typed route() helper (composer typescript)
        └── global.d.ts         Shared page props and flash data types
```

The frontend mirrors the backend: `src/App/Pokemon` ↔ `resources/js/app/Pokemon`, `src/Shared` ↔ `resources/js/shared`.

### Pages

- A controller renders `inertia('Pokemon::Box/Index')`; `AppServiceProvider` maps it to `app/Pokemon/pages/Box/Index/index.vue`.
- Every page is a **folder** named after the page, with the page as `index.vue` and its private components next to it.
- The default layout is `AuthenticatedLayout` (set in `app.ts`). A page that needs another sets it with `defineOptions({ layout: AuthLayout })`.

### Where components go

| Used by | Goes in |
| --- | --- |
| One page | That page's folder, next to `index.vue` |
| Several pages of one domain | `app/{Domain}/components/` |
| Several domains | `shared/components/{group}/` |

Start in the narrowest place and move a component up only when a second page needs it.

When building a page, look for components in this order: game components (`shared/components/game/`), app wrappers (`shared/components/{layout,content,form,list}/`), then shadcn-vue primitives (`shared/components/ui/`). Write a new component only when none fits. A missing shadcn-vue primitive is added with the shadcn-vue CLI, never written by hand.

### Naming

- Name a component by **what it is, with the entity as prefix**: `SpeciesHero`, `IncubatorCard`, `MoveTable`. Don't repeat the page name (`SpeciesHero`, not `PokedexSpeciesHero`). Use the glossary's words (`Move*`, not `Attack*`).
- Variants of a component share its prefix and add the state: `IncubatorIdleCard`, `IncubatorReadyToCatchCard`, all wrapping `IncubatorCard`.
- `App*` marks our wrappers around shadcn primitives (`AppPageHeader`, `AppInput`, `AppEmptyState`).
- Inside a domain, import components with explicit relative paths (`./IncubatorCard.vue`); shared code through `@/shared/...`.

### Props and page data

- **Page-specific components read page props with `usePage<PageProps>()`** instead of receiving them from the page through `defineProps`. Pages stay free of prop wiring, and a component can be moved around the page without threading props through.
- **Reusable components receive props.** They don't know which page they're on.
- Each PageProps class has a matching TypeScript type in `app/{Domain}/types/page-props/{Name}PageProps.ts`, written by hand to mirror what the PHP class returns. Types reuse the shared response types (`Pick<Pokemon, 'name' | 'frontSprite'>`) and enum types.
- PHP enums the frontend uses have hand-written TypeScript copies in `shared/types/enums/` or `app/{Domain}/types/enums/`, mirroring where the PHP enum lives. Labels come from the shared `labels` prop (built with `enumLabels()`), so display names are defined once, in PHP.
- Shared props (`auth`, `labels`, …) and the toast flash are typed once in `shared/types/global.d.ts`.

### Forms and requests

- Use Inertia's `useForm`, posting to the typed helper: `form.post(route('pokemon.web.incubators.place-egg', { incubatorIndex }), …)`. The helper is generated from Laravel's named routes, so a renamed route breaks the type check, not production.
- Reload only what changed with `only: [...]`.
- Disable the button and the actions it competes with while `form.processing`, so a double click never sends twice. (The backend's locks protect the data either way; this protects the player from confusing errors.)
- Field errors show under their field with `FieldError`. Server-side rule violations arrive as a toast.
- Toasts flashed by the backend (`ToastHelper`, the exception handler) are shown by `TheToaster`, which listens to Inertia's `flash` event.

### Design rules

The full guide is [`.ai/rules/design.md`](.ai/rules/design.md). In short:

- Every page starts with `AppPageHeader`; content is grouped in `AppSection`s; related information sits in a `Card` (never cards in cards); grids use auto-fill columns.
- One primary button per screen or dialog; secondary actions are `outline`, row actions `ghost`; anything permanent is `destructive` and asks for confirmation. Labels are verbs.
- Colour carries meaning (type colours, gold for shiny/rare, green good, yellow warning, red danger), always paired with text or an icon, and always with a `dark:` variant.
- Sentence case everywhere. A shiny Pokémon's name always includes "Shiny" (`PokemonDisplayName`, `getDisplayName()`).
- Every screen handles loading (skeletons), empty (`AppEmptyState` with a next step), error (toast or `FieldError`) and success (toast; dialogs only for big moments).
- Designed for 360px first; touch targets at least 40px; no horizontal page scroll.
- Icons from `@tabler/icons-vue` only.

### Test IDs

Elements that browser tests target carry `data-test="{block}__{element}-{type}"` (full rules in [`.ai/rules/testids.md`](.ai/rules/testids.md)):

- `{block}` is the kebab-case component name (`IncubatorCard` → `incubator-card`), including its suffix, without the `App` prefix. The component's root uses the bare block name.
- Inner elements append `__{element}-{type}` (`__place-egg-button`, `__pokeball-radio`); variants append `--{variant}` to the block (`incubator-1-card--idle`).
- Instance, state and position are baked into the ID (`incubator-${index}-card--idle`, `base-moves-card__row-1-name`), never put in extra `data-*` attributes.
- Shared and base components don't own test IDs; the consuming component passes them in.
- Test IDs are stripped from production builds by a Vite compiler transform (`shared/lib/strip-test-ids.ts`), so they cost nothing in production.

---

## 5. Tests

### Layout

```
tests/
├── App/{Domain}/
│   ├── Unit/{Kind}/        One file per class: Actions/, Mechanics/, Queries/, ValueObjects/… (mirrors src)
│   ├── Feature/            One file per page or endpoint (mirrors feature subfolders: Feature/Incubator/)
│   ├── Browser/            Smoke and flow tests in a real browser
│   └── RequestFactories/   One factory per FormRequest (worksome/request-factories)
├── Shared/Unit/            Models, value objects, helpers, enums, commands, seeders
└── Architectural/          The arch() rules that enforce this README
```

### What each kind covers

- **Unit tests for actions** (`{ActionName}Test.php`) are the main contract of the business logic: every guard, every write, every event, and the concurrency cases (`PurchaseActionTest` checks that a stale user can't erase another purchase). Pipelines are tested through their action.
- **Unit tests for mechanics** pin the formulas with concrete numbers from the specs.
- **Unit tests for value objects** cover each validation rule and `equals()`.
- **Feature tests** start with a guest-redirect test, then a "loads the page with expected structure" test (`assertInertia(fn (Assert $page) => $page->component('Pokemon::Nursery')…)`), then scoping tests showing a player only sees their own data, and the HTTP behaviour of each POST (redirect, toast, validation errors).
- **Browser tests** use Pest's `visit()`, `click()`, `fill()`, `assertNoSmoke()` and `assertNoAccessibilityIssues()`, selecting elements with `@test-id` and scoping text assertions to a component (`assertSeeIn('@moves-table', 'Tackle')`). Run `npm run build:testing` first.
- **Architectural tests** check every folder's rules. When you add a new **kind** of folder, add an `arch()` rule for it.

### Rules for writing tests

- Read the `testing-best-practices` skill before writing tests.
- **Arrange every test inline.** No helper functions in test files; each test builds its own data with factories (and their states) and constructors, so it reads on its own. Vary inputs with datasets.
- **Assert the row count** (`assertDatabaseCount(Incubation::class, 1)`) after creating or updating records: `assertDatabaseHas()` alone doesn't prove no duplicate was written.
- **Control every roll** with `RandomHelper::fake([...])`.
- **Control time** with `travelTo()`.
- Test the behaviour that changed and its important failure modes, not more.

---

## 6. Specs, rules and the AI workflow

The project is built with AI agents, and its documentation is organised so an agent (or a new developer) can find the right rule for any file.

| Location | What it holds |
| --- | --- |
| [`docs/specs/`](docs/specs/README.md) | What the game does: rules, formulas, numbers, page designs. One file per feature area, with a status. **The specs win** over what the code suggests; a disagreement is raised, not silently resolved. |
| [`docs/specs/glossary.md`](docs/specs/glossary.md) | One name per idea, and recipes for new names. Names in tasks are suggestions; when the final name differs, the glossary is updated in the same change. |
| [`docs/specs/tasks.md`](docs/specs/tasks.md) | Planned work in order, each task small enough to build and merge alone, with a `- [ ] Done` box ticked in the change that finishes it. |
| [`docs/mockups/`](docs/mockups) | Interactive HTML mock-ups linked from screen tasks. Match their layout, content and every state, but build them from the project's components. |
| [`.ai/rules/`](.ai/rules/index.md) | The enforceable rules, one file per kind of file, mapped from path globs in `index.md`. |
| [`AGENTS.md`](AGENTS.md) | Instructions for AI agents: tools, conventions, the spec and comment rules, the commit style. |

A change that alters a rule, number or page updates the spec in the same change. Commit messages are a single line in conventional style (`feat(exploration): add area page`), with no body.

---

## 7. Checklist: building a feature end to end

1. **Read the spec** and the task; take names from the glossary.
2. **Pick the domain** (or create one, see [3.2](#32-domains)).
3. **Mechanics** for any formula, odds or roll, with unit tests using the spec's numbers. Numbers go in `config/game.php`.
4. **Models and migrations** in `Shared`; edit the existing create migration while there's no live data. Value objects for values with rules, with a cast if stored.
5. **Action** for each change: inputs as models and value objects, `load*()` → `ensure*()` → writes in a transaction, a lock (cache lock, or `lockForUpdate()` if it can run nested) when it guards on saved data, `refresh()` inside the lock when it reads columns of a model passed in, events after the commit. `DomainException`s for player-facing rule violations. Unit tests, including the race cases.
6. **HTTP**: a FormRequest, an invokable controller per action, PageProps for the page, routes with names. Feature tests.
7. **Frontend**: the page folder, its page-props type, enum copies, components from the shared library first, test IDs where browser tests need them. Run `composer typescript` if routes changed.
8. **Browser test** for the main flow (`npm run build:testing` first).
9. **Run `composer format`** and the affected tests; tick the task, update the spec and glossary if anything changed.
