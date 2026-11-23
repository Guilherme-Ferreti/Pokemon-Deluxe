---
paths:
  - 'src/App/**/Exceptions/**'
  - 'src/Shared/Exceptions/**'
---

# Exceptions

## Throw DomainException subclasses for user-facing domain rule violations
Domain rule violations throw final exception classes extending `Shared\Base\DomainException`. The constructor passes the user-facing message to `parent::__construct()`: a full sentence that starts with an uppercase letter and ends with punctuation. `getKey()` returns a short key naming what the error is about (`'incubator'`, `'pokemon'`, `'encounter'`), reserved as the key the error will be reported under in forms (e.g. `errors.incubator`); it is not the message. The global Inertia exception handler flashes a warning toast with `getMessage()` and redirects back, so do not catch them in controllers or turn them into 500s.

## Throw SPL exceptions for programming mistakes
A value that can never be valid (a negative quantity, a value object built out of range) throws `InvalidArgumentException`; code that runs in the wrong order or reaches a state its own checks rule out (a checked getter called before its guard) throws `LogicException`. Both are bugs, so they stay 500s and reach the error logs; never turn them into a `DomainException`.
