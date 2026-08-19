---
paths:
  - 'src/Shared/Helpers/**'
---

# Helpers

## Use currentUser() global helper for authenticated user access
Global function in `src/Shared/Helpers/functions.php` wrapping `Auth::user()`. Use `currentUser()` instead of `Auth::user()` or `auth()->user()`.
