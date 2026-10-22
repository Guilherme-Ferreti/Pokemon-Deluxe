---
paths:
  - 'tests/**'
---

# Tests

## Assert the record count after creating or updating records
A test that creates or updates records must assert the expected number of rows with `assertDatabaseCount()` (e.g. `assertDatabaseCount(Incubation::class, 1)`). A single `assertDatabaseHas()` does not prove that no duplicate or extra rows were written, so the count assertion is required to catch unintended inserts.

## Use worksome/request-factories for form request testing
Create `RequestFactory` subclasses per form request for test data generation. Place them in `tests/{Domain}/RequestFactories/`.
