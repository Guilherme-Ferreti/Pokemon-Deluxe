---
paths:
  - 'src/App/**/Mechanics/**'
---

# Mechanics

## Keep pure game rules in Mechanics classes
Formulas, odds and rolls live in `src/App/{Domain}/Mechanics/`, in any domain. Each class is `final`, extends nothing and is named after its job (see `docs/specs/glossary.md`):
- `{Noun}Calculator`: pure math with no randomness (e.g. `DamageCalculator`).
- `{Noun}Roller`: rolls dice (e.g. `MoveOutcomeRoller`).
- `{Noun}Checker`: answers "is this allowed?", usually returning `null` or a reason string, or a bool for a plain yes or no (e.g. `EvolutionRequirementChecker`).

## Never touch the database, HTTP, sessions or auth
Mechanics may read the models and relations they are given, and `config('game.*')`. They never query or save to the database, and never use HTTP, sessions or auth. The caller (an Action, Query or PageProps) loads the data and saves the result.

## Get randomness only from RandomHelper, only in Rollers
Only `Roller` classes use randomness, and only through `Shared\Helpers\RandomHelper`. Calculators and Checkers receive any rolled value (e.g. a random factor) as an argument.
