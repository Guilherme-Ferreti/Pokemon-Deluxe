<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use BackedEnum;
use Shared\Interfaces\ValueObject;

final readonly class FilterOption implements ValueObject
{
    public function __construct(
        public string $label,
        public string|int $value,
    ) {}

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->label === $other->label
            && $this->value === $other->value;
    }

    public static function fromEnum(BackedEnum $case): self
    {
        $label = method_exists($case, 'label')
            ? $case->label()
            : str($case->name)->headline()->toString();

        return new self(
            label: $label,
            value: $case->value,
        );
    }

    /**
     * @param  class-string<BackedEnum>  $class
     * @return list<self>
     */
    public static function listFromEnum(string $class): array
    {
        return array_map(
            self::fromEnum(...),
            $class::cases(),
        );
    }
}
