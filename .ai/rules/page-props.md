---
paths:
  - 'src/App/**/Http/Web/PageProps/**'
---

# Page Props

## Use PageProps classes implementing ProvidesInertiaProperties
Create `final` PageProps classes implementing `ProvidesInertiaProperties`. They are responsible for building the props passed to the Inertia page. Use deferred props via `fn () =>` closures. Controllers pass them via `inertia('Page')->with(new PageProps)`.
