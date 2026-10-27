---
paths:
  - 'resources/js/**'
  - 'tests/**/Browser/**'
---

# Test IDs

## Name test IDs as `{block}__{element}-{type}`
Elements targeted by browser tests carry a `data-test` attribute. Only add test IDs to elements that a test targets.

- `{block}` is the kebab-case name of the component that renders the element, without the `App` prefix (`AppSearchFiltersDialog` → `search-filters-dialog`, `PokedexSpeciesHero` → `pokedex-species-hero`).
- The component's root element uses the bare block name (`incubators-list`, `incubator-catch-pokemon-dialog`).
- Inner elements append `__{element}-{type}`, where `{type}` describes the element: `button`, `link`, `input`, `radio`, `checkbox`, `select`, `list`, `item`, `dialog`, `indicator`, `empty-state`, `title`.
- A component that only wraps a base component and adds content for one variant reuses the base block. For example, `IncubatorIdleCard` wraps `IncubatorCard`, so its button is `incubator-card__place-egg-button`, not `incubator-idle-card__place-egg-button`.

## Keep test IDs static: expose state and identity through data attributes
Never interpolate state, IDs, or indexes into a test ID. Keep the test ID static and expose the variable part through a dedicated attribute such as `data-status`, `data-index`, or `data-value`.

```vue
<!-- Bad -->
<Card :data-test="`incubator-${status}`" />
<Field :data-test="`pokeball-${pokeball.id}`" />

<!-- Good -->
<Card data-test="incubator-card" :data-index="index" :data-status="status" />
<Field data-test="incubator-catch-pokemon-dialog__pokeball-radio" :data-value="pokeball.id" />
```

## Select test IDs with `@` when unique, attribute selectors when scoping
In browser tests, use the `@block__element-type` shorthand when the test ID is unique on the page. When the test needs a specific instance or state, use an attribute selector that combines the test ID with its data attributes, e.g. `[data-test="incubator-card"][data-index="1"][data-status="idle"]`.

Assert absence with `assertMissing()` or `assertNotPresent()`. `assertDontSee()` only matches text, so it always passes when given a selector.
