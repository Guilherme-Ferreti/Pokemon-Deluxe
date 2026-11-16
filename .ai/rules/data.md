---
paths:
  - 'src/App/**/Data/**'
---

# Data

## Use a mutable Data class to carry state through a pipeline
A `{Noun}Data` class (e.g. `App\Battle\Data\TurnData`) is the object a pipeline passes from pipe to pipe. It is `final`, mutable and unvalidated: public properties that pipes read and write, with the action's inputs as `readonly` constructor properties. Validation stays in the value objects it holds (`BattleState`, `Combatant`). Unlike value objects, Data classes are never stored, compared or shared outside the pipeline that built them.

## Keep only state access on the Data class
Methods on a Data class read or replace the state it carries (`activeCombatant()`, `replaceActiveCombatant()`, `damageActiveCombatant()`, `displayName()`, `startActionFor()`, `log()`), and give a checked value its non-null type (`rolledMoveOutcome()` throws a `LogicException` before the roll). Game rules, rolls and database queries belong in pipes and mechanics, never on the Data class.

## Give a repeated step its own Data class
State that belongs to one repetition of a step (one side's action in a turn) goes in its own Data class (`TurnActionData`), created per repetition and kept on the parent (`TurnData::$playerAction`, `$opponentAction`), never in flat fields on the parent that must be reset between repetitions. The child holds the parent as a `readonly` property to reach shared state.
