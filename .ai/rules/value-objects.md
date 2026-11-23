---
paths:
  - 'src/Shared/ValueObjects/**'
  - 'src/App/**/ValueObjects/**'
---

# Value Objects

## Put a value object in Shared only when more than one domain uses it
`Shared\ValueObjects` holds value objects stored in a database column through a cast in `src/Shared/Casts` (e.g. `BattleState` and the `BattleSide` and `Combatant` inside it, `SpeciesSprites`) and value objects that two or more domains use (e.g. `PokemonStats`, `FilterOption`). A value object used by only one domain lives in that domain's `App\{Domain}\ValueObjects` (e.g. `App\Battle\ValueObjects\MoveOutcome`), including an action's input that other domains pass in (e.g. `App\Pokemon\ValueObjects\NewPokemon` for `CreatePokemonAction`). When a second domain starts using it, move it to Shared. Tests mirror the location (`tests/App/{Domain}/Unit/ValueObjects/`).

## Use final readonly value objects implementing Shared\Interfaces\ValueObject
Value objects are `final readonly` classes implementing `Shared\Interfaces\ValueObject` with `equals(ValueObject $other): bool`. Use promoted public properties, or a validated public `$value` when the constructor enforces constraints. Convert to/from storage with a dedicated cast in `src/Shared/Casts`.

## Name properties in camelCase and keep the stored format in snake_case
Properties and constructor parameters are camelCase (`currentHp`, `isShiny`, `speciesId`), even when they hold the same value as a snake_case model column. The stored format keeps snake_case keys like database columns: the keys `toArray()` writes and `fromArray()` reads (`'current_hp'`), the JSON a cast saves, and the PHPStan array shapes that describe them (`CombatantArray`). Renaming a property therefore never changes saved data or seed files.
