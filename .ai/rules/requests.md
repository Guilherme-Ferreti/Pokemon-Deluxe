---
paths:
  - 'src/App/**/Http/Web/Requests/**'
---

# Requests

## Use Form Request classes for validation
All validation lives in dedicated `FormRequest` subclasses with a `rules()` method. No inline `$request->validate()` in controllers.

## Name validated attributes in camelCase
Keys in a FormRequest's rules() are camelCase (`moveId`, `speciesEvolutionId`, `moveIds.*`), never snake_case, because the frontend sends camelCase data. Controllers read them with the same key (`$request->modelOrFail('moveId', Move::class)`), and tests post and assert errors with it. Database columns stay snake_case.
