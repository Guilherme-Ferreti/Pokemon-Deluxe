---
paths:
  - 'src/App/**/Actions/**'
---

# Actions

## Use Action classes with handle() for business logic
Create `final` Action classes with a single `handle()` method. Controllers instantiate and call them directly (`new Action()->handle(...)`). Actions are not injected via the container.
