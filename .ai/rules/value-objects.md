---
paths:
  - 'src/Shared/ValueObjects/**'
---

# Value Objects

## Use final readonly value objects implementing Shared\Interfaces\ValueObject
Value objects are `final readonly` classes implementing `Shared\Interfaces\ValueObject` with `equals(ValueObject $other): bool`. Use promoted public properties, or a validated public `$value` when the constructor enforces constraints. Convert to/from storage with a dedicated cast in `src/Shared/Casts`.
