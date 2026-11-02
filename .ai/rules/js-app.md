---
paths:
  - 'resources/js/app/**'
---

# Js App

## Read page props with usePage in page-specific components
Page-specific components should call `usePage<PageProps>()` and read props directly from `page.props` instead of receiving them from the page via `defineProps`. This avoids prop-drilling and keeps page templates free of prop wiring.

Reserve props for genuinely reusable shared components that are not tied to a single page.

## Pages and their components

- Every page is a folder named after the page, with the page as `index.vue` and its
  private components next to it: `pages/Pokedex/Show/index.vue`,
  `pages/Pokedex/Show/SpeciesHero.vue`.
- Controllers use `inertia('Module::Path/Page')`, for example `Dex::Pokedex/Show`.
  `AppServiceProvider` maps it to `Module/pages/Path/Page/index`.

## Where components go

| Used by | Goes in |
|---|---|
| One page | That page's folder, next to `index.vue` |
| Several pages of one domain | `<Domain>/components/` |
| Several domains | `shared/components/<group>/` |

Start with the narrowest place and move a component up only when a second page needs it.

## Imports and names

- Inside a domain, import components explicitly with relative paths.
- Name by what the component is, with the entity as prefix (`SpeciesHero`, `MoveTable`).
  Do not repeat the page name. Use `Move*`, not `Attack*`.
- `App*` marks our wrappers around shadcn primitives (layout, form, list, content).

## Shared groups

- `ui/` shadcn-vue primitives (managed by the CLI, never edited by hand)
- `layout/` app chrome (sidebar, nav, page header, toaster)
- `form/` form wrappers (AppInput, AppSelect, ...)
- `list/` pagination, sortable table heads, search filter dialog
- `content/` sections, info cards, empty states, indicators
- `game/` game-aware display components used by 2+ domains

