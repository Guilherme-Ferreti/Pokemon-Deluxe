<?php

declare(strict_types=1);

namespace App\Dex\Filters;

use App\Dex\Enums\SearchPokedexSortBy;
use Illuminate\Http\Request;
use Shared\Enums\SortDirection;
use Shared\Interfaces\Filter;

final class SearchPokedexFilters implements Filter
{
    public function __construct(
        public ?string $search,
        public SearchPokedexSortBy $sort,
        public SortDirection $dir,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->query('search'),
            sort: $request->enum('sort', SearchPokedexSortBy::class, SearchPokedexSortBy::POKEDEX_NUMBER),
            dir: $request->enum('dir', SortDirection::class, SortDirection::ASC),
        );
    }

    public static function filterOptions(): array
    {
        return [];
    }
}
