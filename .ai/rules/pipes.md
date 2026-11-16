---
paths:
  - 'src/App/**/Pipes/**'
---

# Pipes

## Split a long action into Laravel Pipeline pipes
When an action's work is a long sequence of steps (e.g. `TakeTurnAction`), the action keeps its lock and refresh, then sends a Data class (see `.ai/rules/data.md`) through `Pipeline::send($data)->through([...])->thenReturn()`. Each step is a pipe in `src/App/{Domain}/Pipes/{Pipeline}/`, named after what it does (`RollTurnOrder`, `DealHits`, `EnsureBattleIsReadyForTurn`). A pipe is `final` with one public method, `handle({Noun}Data $data, Closure $next): {Noun}Data`, and follows the same rules as actions (guards in `ensure*()` methods) and mechanics (randomness only through Rollers).

## Stop a pipeline by not calling $next
A pipe that ends the work early (a missed move, an immune target) records why on the Data class and returns it without calling `$next`. Work that must happen however the pipeline ended goes in the caller, after the pipeline, not in a last pipe.

## Nest a pipeline for a step that repeats
A step that runs more than once inside a pipeline (each side's action in a turn) is one pipe that runs its own nested pipeline per repetition, sending that repetition's own Data class (`TakeActions` sends a `TurnActionData` for each side through the `Pipes/TurnAction` pipes), so an early stop ends only that repetition.

## Test pipelines through their action
The action's tests are the contract for the whole pipeline. Add a test for a single pipe only when it gains logic of its own that the action's tests can't reach.
