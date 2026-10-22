<?php

declare(strict_types=1);

namespace App\Pokemon\Filters;

use App\Pokemon\Enums\SearchBoxSortBy;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Shared\Enums\ElementalType;
use Shared\Enums\Generation;
use Shared\Enums\PokemonTrait;
use Shared\Enums\SortDirection;
use Shared\Interfaces\Filter;
use Shared\ValueObjects\FilterOption;

final class SearchBoxFilters implements Filter
{
    public function __construct(
        public ?string $search,

        /** @var Collection<int, ElementalType> */
        public Collection $types,

        /** @var Collection<int, PokemonTrait> */
        public Collection $traits,

        /** @var Collection<int, Generation> */
        public Collection $generations,

        public SearchBoxSortBy $sort,

        public SortDirection $dir,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(...[
            'search'      => $request->query('search'),
            'types'       => $request->collect('types')->toUniqueEnums(ElementalType::class)->take(2),
            'traits'      => $request->collect('traits')->toUniqueEnums(PokemonTrait::class),
            'generations' => $request->collect('generations')->toUniqueEnums(Generation::class),
            'sort'        => $request->enum('sort', SearchBoxSortBy::class, SearchBoxSortBy::CATCH_DATE),
            'dir'         => $request->enum('dir', SortDirection::class, SortDirection::DESC),
        ]);
    }

    /**
     * @return array<string, FilterOption[]>
     */
    public static function filterOptions(): array
    {
        return [
            'types'       => FilterOption::listFromEnum(ElementalType::class),
            'traits'      => FilterOption::listFromEnum(PokemonTrait::class),
            'generations' => FilterOption::listFromEnum(Generation::class),
            'sort'        => FilterOption::listFromEnum(SearchBoxSortBy::class),
            'dir'         => FilterOption::listFromEnum(SortDirection::class),
        ];
    }
}
