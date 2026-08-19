---
paths:
  - 'src/Shared/Providers/**'
---

# Providers

## Use immutable dates via Date::use(CarbonImmutable::class)
Configure immutable dates globally in `AppServiceProvider::boot()` with `Date::use(CarbonImmutable::class)`.

## Enable automatic eager loading globally
Call `Model::automaticallyEagerLoadRelationships()` in `AppServiceProvider::boot()`. Do not add per-query `->with()` unless overriding for a specific case.
