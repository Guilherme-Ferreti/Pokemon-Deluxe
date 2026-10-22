---
paths:
  - 'resources/js/app/**'
---

# Js App

## Read page props with usePage in page-specific components
Page-specific components should call `usePage<PageProps>()` and read props directly from `page.props` instead of receiving them from the page via `defineProps`. This avoids prop-drilling and keeps page templates free of prop wiring.

Reserve props for genuinely reusable shared components that are not tied to a single page.
