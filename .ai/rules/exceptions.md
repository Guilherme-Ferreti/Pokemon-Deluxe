---
paths:
  - 'src/App/**/Exceptions/**'
---

# Exceptions

## Throw DomainException subclasses for user-facing domain rule violations
Domain rule violations throw final exception classes extending `Shared\Base\DomainException`, each implementing `getKey()` with a user-facing message. The global Inertia exception handler flashes a warning toast and redirects back, so do not catch them in controllers or turn them into 500s.
