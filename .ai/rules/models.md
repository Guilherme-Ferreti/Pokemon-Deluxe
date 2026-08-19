---
paths:
  - 'src/Shared/Models/**'
---

# Models

## Use UUID primary keys on all models
All models use the `HasUuids` trait. Migrations use `$table->uuid('id')->primary()`.

## Use #[Unguarded] attribute for mass assignment
Use `#[Unguarded]` on models unless there is an extremely sensitive attribute that requires maximum care, in which case use `#[Fillable]` with an explicit allow-list.

## Use #[Scope] attribute for query scopes
Model scopes use the `#[Scope]` attribute on `protected` methods with a `Builder` parameter and `void` return type.

## Always use casts() method, never $casts property
Define casts via the `protected function casts(): array` method on the model. Never use the `$casts` property.

## Make all models final
All model classes must be `final`. Enforced by architectural tests.
