---
paths:
  - 'resources/js/**'
  - 'tests/**/Browser/**'
---

# Test IDs

## Name test IDs as `{block}__{element}-{type}`
Elements targeted by browser tests carry a `data-test` attribute. Only add test IDs to elements that a test targets.

- `{block}` is the kebab-case name of the component that renders the element, **including** its suffix (`Card`, `Table`, ...): `SpeciesHero` → `species-hero`, `OverviewCard` → `overview-card`, `LearnableMovesLevelUpTable` → `learnable-moves-level-up-table`, `PokemonCard` → `pokemon-card`. Drop only the `App` prefix (`AppSearchFiltersDialog` → `search-filters-dialog`). Page-level context is not part of the block (`Pokedex` show page's `SpeciesHero` is `species-hero`, not `pokedex-species-hero`). If a component's name does not match its block, rename the component.
- The component's root element uses the bare block name (`evolution-chain-card`, `incubators-grid`, `learnable-moves-level-up-table`).
- Inner elements append `__{element}-{type}`, where `{type}` describes the element: `button`, `link`, `input`, `radio`, `checkbox`, `select`, `list`, `item`, `dialog`, `indicator`, `empty-state`, `title`, `head`, `row`.
- In tables, address cells through the table's own element types: header cells are `__head-{sortBy}` (`learnable-moves-level-up-table__head-level-learn`), body cells are `__row-{position}-{column}` with positions starting at 1 (`learnable-moves-level-up-table__row-1-name`).
- A component rendered in a distinct state or variant appends `--{variant}` to the block. Variant cards that wrap a base card: `IncubatorIdleCard` renders `IncubatorCard` as `incubator-{index}-card--idle`, and its button is `incubator-{index}-card--idle__place-egg-button`, not `incubator-card__place-egg-button`.
- Base and shared components do not own test IDs. The consuming component passes the test ID from its own block: `LearnableMovesLevelUpTable` passes `learnable-moves-level-up-table__head-power` to `AppTableHeadSortableButton`, which hardcodes none.

## Interpolate state, identity, and position into the test ID
When an element has a variable instance, state, or position, bake it into the test ID instead of exposing it through companion attributes such as `data-index`, `data-status`, or `data-value`.

```vue
<!-- Bad -->
<Card data-test="incubator-card" :data-index="index" :data-status="status" />
<Field data-test="incubator-catch-pokemon-dialog__pokeball-radio" :data-value="pokeball.id" />

<!-- Good -->
<IncubatorCard :data-test="`incubator-${index}-card--idle`" />
<TableCell :data-test="`learnable-moves-level-up-table__row-${key + 1}-power`" />
<Field :data-test="`incubator-catch-pokemon-dialog__pokeball-${pokeball.id}-radio`" />
```

Keep attributes such as `data-invalid` only when they drive styling or generic framework state, not test targeting.

## Select test IDs with the `@` shorthand in browser tests
Use the `@block__element-type` shorthand whenever the test ID is unique on the page, including interpolated instances resolved in the test (`@incubator-1-card--idle`, `@learnable-moves-level-up-table__row-1-name`). Interpolated test IDs already encode the instance, state, and position, so never chain `[data-test][data-...]` attribute selectors for scoping.

Assert activity/presence of an element with `assertVisible()` or `assertPresent()`, and absence with `assertMissing()` or `assertNotPresent()`.

Scope text assertions to the component instead of the page by targeting its root block: `assertSeeIn('@moves-table', 'Tackle')` and `assertDontSeeIn('@moves-table', 'Ember')`, not page-wide `assertSee()` / `assertDontSee()`. This pins the assertion to that component and avoids false matches from unrelated text elsewhere. This is also why a component that contains asserted content always gets the bare block name on its root element (`moves-table`, `pokemon-grid`), even when no test targets the root's existence itself.

Never use `assertDontSee()` with a selector: it only matches text, so it always passes when given one. Reserve plain `assertSee()` / `assertDontSee()` for global UI such as the page title or flashes.
