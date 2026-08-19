---
paths:
  - 'src/App/**/Http/Web/Requests/**'
---

# Requests

## Use Form Request classes for validation
All validation lives in dedicated `FormRequest` subclasses with a `rules()` method. No inline `$request->validate()` in controllers.
