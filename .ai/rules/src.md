---
paths:
  - 'src/**'
---

# Src

## Make all application classes final
Every class in `src/` must be `final`. Enforced by Pint (`final_class: true`) and architectural tests.

## Mark overridden methods with #[Override]
Every method that overrides a parent method carries the native PHP `#[Override]` attribute.
