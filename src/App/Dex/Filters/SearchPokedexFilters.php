<?php

declare(strict_types=1);

namespace App\Dex\Filters;

use Illuminate\Http\Request;
use Shared\Interfaces\Filter;

final class SearchPokedexFilters implements Filter
{
    public function __construct(
        public ?string $search,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->query('search'),
        );
    }

    public static function filterOptions(): array
    {
        return [];
    }
}
