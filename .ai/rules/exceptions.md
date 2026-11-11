---
paths:
  - 'src/App/**/Exceptions/**'
  - 'src/Shared/Exceptions/**'
---

# Exceptions

## Throw DomainException subclasses for user-facing domain rule violations
Domain rule violations throw final exception classes extending `Shared\Base\DomainException`, each implementing `getKey()` with a user-facing message. The global Inertia exception handler flashes a warning toast and redirects back, so do not catch them in controllers or turn them into 500s.

## Report game data errors with Shared\Exceptions instead of throwing
Integrity errors in the game's core data files (e.g. a species without base moves) are final `Shared\Exceptions\*Exception` classes extending `RuntimeException`, built through named constructors. Pass them to `report()` and continue with a safe fallback instead of throwing. Their `report()` method logs them to the `game-data` channel (`storage/logs/game-data.log`).
