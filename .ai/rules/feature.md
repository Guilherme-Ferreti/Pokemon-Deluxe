---
paths:
  - 'tests/**/Feature/**'
---

# Feature

## Use assertInertia() for Inertia page assertions in feature tests
Feature tests assert page structure via `assertInertia(fn (Assert $page) => ...)`. Use `Inertia\Testing\AssertableInertia as Assert`.

## Feature test skeleton: guest redirect, structure, user scoping
Open each feature test file with a guest-redirect test (route() + assertRedirectToRoute('auth.web.login.create')), then a 'loads the X page with expected structure' test asserting ->component('{Domain}/pages/...') and props via assertInertia, plus user-scoping tests for authenticated pages using actingAs() and factory states.
