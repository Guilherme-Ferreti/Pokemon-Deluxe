---
paths:
  - 'src/App/**/Queries/**'
---

# Queries

## Use Query classes with handle() for complex query logic
Create `final` Query classes with a single `handle()` that returns a `Builder`. May or may not receive a Filter value object depending on complexity. Controllers never build queries directly.
