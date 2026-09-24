---
paths:
  - 'database/migrations/**'
---

# Migrations

## Use numeric-prefix ordering for migration filenames
Migrations use `001_`, `002_`, etc. for ordering instead of timestamps (except vendor migrations).

## Use foreignIdFor(Model::class) for model foreign keys
Use `foreignIdFor(Model::class)->constrained()` for model references. Use `foreignId()` for non-model foreign keys. Include real `down()` methods.

## Declare enum-backed columns as DB enum() columns
Enum-backed columns are declared `$table->enum('col', Arr::pluck(Enum::cases(), 'value'))`, never a plain string (plain string only for genuinely non-enum columns). Models still cast with EnumClass::class in casts().
