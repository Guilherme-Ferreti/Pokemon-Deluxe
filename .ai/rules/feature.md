---
paths:
  - 'tests/**/Feature/**'
---

# Feature

## Use assertInertia() for Inertia page assertions in feature tests
Feature tests assert page structure via `assertInertia(fn (Assert $page) => ...)`. Use `Inertia\Testing\AssertableInertia as Assert`.
