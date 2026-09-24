---
paths:
  - 'src/Shared/Enums/**'
---

# Enums

## Map enums to DB enum() columns with PHP enum casts
Enum-backed columns are declared `$table->enum('col', Arr::pluck(Enum::cases(), 'value'))` in migrations, cast via `EnumClass::class` in model `casts()`. Enum cases are UPPER_SNAKE_CASE with lowercase/int backing values.

## Provide a label() method on backed enums when display names are needed
When an enum needs a human-readable label, define a `label()` method using a `match` expression. Not all enums require one.
