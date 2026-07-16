<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use BackedEnum;

final class FilterOption
{
    public function __construct(
        public string $label,
        public string|int $value,
    ) {}

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
            fn (BackedEnum $case) => self::fromEnum($case),
            $class::cases(),
        );
    }
}
