---
paths:
  - 'src/App/**/Http/Web/PageProps/**'
---

# Page Props

## Use PageProps classes implementing ProvidesInertiaProperties
Create `final` PageProps classes implementing `ProvidesInertiaProperties`. They are responsible for building the props passed to the Inertia page. Use deferred props via `fn () =>` closures. Controllers pass them via `inertia('Page')->with(new PageProps)`.

## Paginate with paginate() + withQueryString() and reshape via AppHelper::paginatorToArray()
Paginated index PageProps call `...->paginate($n)->withQueryString()` and pass the paginator through AppHelper::paginatorToArray($paginator, fn ($row) => [...]) so the JSON keeps camelCase meta keys (currentPage, lastPageUrl, links, data). Do not use simplePaginate/cursorPaginate.
