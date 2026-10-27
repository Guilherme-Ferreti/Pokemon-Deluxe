---
paths:
  - 'src/App/**/Actions/**'
---

# Actions

## Use Action classes with handle() for business logic
Create `final` Action classes suffixed with `Action` (e.g. `CatchPokemonAction`) with a single `handle()` method. Name their unit tests `{ActionName}Test.php` under `tests/App/{Domain}/Unit/Actions/`. Controllers instantiate and call them directly (`new Action()->handle(...)`). Actions are not injected via the container.
