---
paths:
  - 'src/Shared/Enums/**'
---

# Enums

## Store enums as string columns with PHP enum casts
Enums use `string` columns on the DB side, cast via `EnumClass::class` in model `casts()`. Enum cases are UPPER_SNAKE_CASE with lowercase/int backing values.

## Provide a label() method on backed enums when display names are needed
When an enum needs a human-readable label, define a `label()` method using a `match` expression. Not all enums require one.
