---
paths:
  - 'src/App/**/Http/Web/Controllers/**'
---

# Controllers

## Keep controllers thin and delegate to Actions/PageProps
Controllers extend `Shared\Base\Controller`, are `final`, and contain minimal logic. Business logic goes to Actions; data preparation goes to PageProps. Allowed public methods: `__invoke`, `index`, `show`, `create`, `store`, `edit`, `update`, `delete`, `destroy`.
