---
paths:
  - 'src/Shared/Helpers/**'
---

# Helpers

## Use currentUser() global helper for authenticated user access
Global function in `src/Shared/Helpers/functions.php` wrapping `Auth::user()`. Use `currentUser()` instead of `Auth::user()` or `auth()->user()`.

## Get randomness only from RandomHelper
Game code in `App` and `Shared` gets random numbers, rolls and picks only from `Shared\Helpers\RandomHelper` (`chance()`, `integer()`, `float()`, `pick()`), never from `rand`, `mt_rand`, `random_int`, `array_rand`, `shuffle`, `Lottery`, `Arr::random()`, `Collection::random()` or `Str::random()`. Chances are percentages from 0 to 100 everywhere (config, columns, calculators), so `chance()` takes them as is and throws outside that range. Tests control the rolls with `RandomHelper::fake([...])`, which returns the given values in order whatever method is called and throws when they run out; `tests/Pest.php` resets it after each test. The arch rule catches the functions and `Lottery`, but not single methods such as `Arr::random()`, so watch for those in review.

The one exception is picking random rows in the database with `inRandomOrder()` in a query (e.g. the egg species in `PlaceEggAction`); keep those in the query rather than loading every row to pick one.
