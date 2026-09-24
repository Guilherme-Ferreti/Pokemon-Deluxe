---
paths:
  - 'src/App/**'
---

# App

## Organize features as domain modules
Organize features into `src/App/{Domain}/` folders. Each domain contains `Actions/`, `Http/Web/Controllers/`, `Http/Web/PageProps/`, `Http/Web/Requests/`, `Filters/`, `Queries/`, `Enums/`, and `Http/Web/routes.php`. A domain may also have `Http/Api/` in the future. Shared code lives in `src/Shared/`.

## Query Eloquent directly in Query objects
No repository abstraction. Query objects call Eloquent directly. Controllers may also build lightweight queries themselves (e.g. API autocomplete) without routing them through a Query class.

## Use named routes with route() for URL generation
All redirects and links use `route('name')`. No `url()` or `action()` usage. Route names follow `{domain}.{web|api}.{action}` convention (e.g. `auth.web.login.create`); the `{domain}.{web|api}.` prefix comes from `bootstrap/app.php`.
