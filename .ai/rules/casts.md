---
paths:
  - 'src/Shared/Casts/**'
---

# Casts

## Cast value objects with dedicated CastsAttributes classes
Value-object-backed columns use a dedicated final `CastsAttributes` class in `src/Shared/Casts` named `As{ValueObject}`, referenced from the model's `casts()`. Do not use inline `Attribute` casts.
