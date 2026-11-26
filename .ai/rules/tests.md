---
paths:
  - 'tests/**'
---

# Tests

## Name the arguments of a multi-line handle() call
A `handle()` call split over several lines names each argument, one per line, as in `src.md` ("Name the arguments of a multi-line handle() call").

## Assert the record count after creating or updating records
A test that creates or updates records must assert the expected number of rows with `assertDatabaseCount()` (e.g. `assertDatabaseCount(Incubation::class, 1)`). A single `assertDatabaseHas()` does not prove that no duplicate or extra rows were written, so the count assertion is required to catch unintended inserts.

## Use worksome/request-factories for form request testing
Create `RequestFactory` subclasses per form request for test data generation. Place them in `tests/{Domain}/RequestFactories/`.

## Arrange every test inline, without helper functions
Do not declare helper functions in test files (e.g. `makeSearchBoxFilters()`, `createPokemonOfSpecies()`), even when they would make the tests shorter. Each test builds its own data with factories and constructors, so it can be read on its own. Use datasets to vary inputs, and factory states for reusable setup.
