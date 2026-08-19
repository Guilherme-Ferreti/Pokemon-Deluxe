---
paths:
  - 'src/App/**/Filters/**'
---

# Filters

## Use Filter value objects implementing Shared\Interfaces\Filter
Create `final` Filter classes with constructor-promoted properties, a `fromRequest(Request)` factory, and a `filterOptions()` static method. Implement `Shared\Interfaces\Filter`.
