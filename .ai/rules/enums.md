---
paths:
  - 'src/Shared/Enums/**'
  - 'src/App/**/Enums/**'
---

# Enums

## Put an enum in Shared only when more than one domain uses it
`Shared\Enums` holds enums stored in a database column (e.g. `BattleStatus`, `Ailment`, `PokemonType`) and enums that two or more domains use (e.g. `SortDirection`). An enum used by only one domain lives in that domain's `App\{Domain}\Enums` (e.g. `App\Battle\Enums\ActionBlocker`, `App\Dex\Enums\MoveTrait`). When a second domain starts using it, move it to Shared. Its hand-written TypeScript copy mirrors the location: `resources/js/shared/types/enums/` or `resources/js/app/{Domain}/types/enums/`.

## Map enums to DB enum() columns with PHP enum casts
Enum-backed columns are declared `$table->enum('col', Arr::pluck(Enum::cases(), 'value'))` in migrations, cast via `EnumClass::class` in model `casts()`. Enum cases are UPPER_SNAKE_CASE with lowercase/int backing values.

## Provide a label() method on backed enums when display names are needed
When an enum needs a human-readable label, define a `label()` method using a `match` expression. Not all enums require one.
