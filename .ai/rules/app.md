---
paths:
  - 'src/App/**'
---

# App

## Organize features as domain modules
Organize features into `src/App/{Domain}/` folders. Each domain contains `Actions/`, `Http/Web/Controllers/`, `Http/Web/PageProps/`, `Http/Web/Requests/`, `Filters/`, `Queries/`, `Enums/`, and `Http/Web/routes.php`. A domain may also have `Http/Api/` (controllers and routes), `Http/Web/Middlewares/`, `Exceptions/`, `Mechanics/`, `Support/`, and `Services/`. Every one of these folders is covered by an `arch()` rule in `tests/Architectural/ArchitecturalTest.php`; add a rule when introducing a new kind of folder. Shared code lives in `src/Shared/`.

A cohesive slice of a domain may be grouped further into a feature subfolder that mirrors across layers (e.g. `Actions/Incubator/` and `Http/Web/Controllers/Incubator/`). Mirror the same subfolder in the tests (e.g. `tests/App/{Domain}/Unit/Actions/Incubator/` and `tests/App/{Domain}/Feature/Incubator/`).

## Format toast messages consistently
Every toast `title` starts with an uppercase letter and has no punctuation at the end. Every toast `message` starts with an uppercase letter and ends with a punctuation mark (`.`, `!`, or `?`). This applies to all `ToastHelper::*` calls and any toast flashed via `Inertia::flash('toast', ...)`.

## Query Eloquent directly in Query objects
No repository abstraction. Query objects call Eloquent directly. Controllers may also build lightweight queries themselves (e.g. API autocomplete) without routing them through a Query class.

## Use named routes with route() for URL generation
All redirects and links use `route('name')`. No `url()` or `action()` usage. Route names follow `{domain}.{web|api}.{action}` convention (e.g. `auth.web.login.create`); the `{domain}.{web|api}.` prefix comes from `bootstrap/app.php`.
