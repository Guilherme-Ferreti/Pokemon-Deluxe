---
paths:
  - 'src/App/**/Support/**'
---

# Support

## Declare cache and lock keys as static methods on domain Support classes
Cache and lock keys live as static factory methods on a final `CacheKeys` or `LockKeys` class in `App\{Domain}\Support`, never as inline strings. Wrap mutating actions with `Cache::withoutOverlapping` using a `LockKeys` method. An action that can run inside a caller's transaction locks the owning row instead (see `.ai/rules/actions.md`).
