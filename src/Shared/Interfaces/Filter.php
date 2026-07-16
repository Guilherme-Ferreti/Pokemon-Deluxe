<?php

declare(strict_types=1);

namespace Shared\Interfaces;

use Illuminate\Http\Request;
use Shared\ValueObjects\FilterOption;

interface Filter
{
    public static function fromRequest(Request $request): static;

    /** @return array<string, FilterOption[]> */
    public static function filterOptions(): array;
}
