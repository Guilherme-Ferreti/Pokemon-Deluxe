---
paths:
  - 'src/**'
---

# Src

## Make all application classes final
Every class in `src/` must be `final`. Enforced by Pint (`final_class: true`) and architectural tests.

## Mark overridden methods with #[Override]
Every method that overrides a parent method carries the native PHP `#[Override]` attribute.

## Name methods so they need no explaining comment
Do not write descriptive comments above functions or methods; their PHPDoc blocks hold only the type information PHPStan needs (`@param`, `@return`, `@var`, `@template`, `@throws`, array shapes). Give the function a name that says what it does instead of a generic name explained by a comment (`rollIsShiny()`, not `roll()` with "Rolls whether the egg is shiny"). See the Comments section in `AGENTS.md`.
