<?php

declare(strict_types=1);

namespace App\Dex\Filters;

use App\Dex\Enums\SearchPokedexSortBy;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Shared\Enums\ElementalType;
use Shared\Enums\Generation;
use Shared\Enums\SortDirection;
use Shared\Enums\SpeciesTrait;
use Shared\Interfaces\Filter;
use Shared\ValueObjects\FilterOption;

final class SearchPokedexFilters implements Filter
{
    public function __construct(
        public ?string $search,

        /** @var Collection<int, ElementalType> */
        public Collection $types,

        /** @var Collection<int, SpeciesTrait> */
        public Collection $traits,

        /** @var Collection<int, Generation> */
        public Collection $generations,

        public SearchPokedexSortBy $sort,

        public SortDirection $dir,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $types = $request
            ->collect('types')
            ->map(fn (string $value) => ElementalType::tryFrom($value))
            ->unique()
            ->filter()
            ->take(2)
            ->values();

        $traits = $request
            ->collect('traits')
            ->map(fn (string $value) => SpeciesTrait::tryFrom($value))
            ->unique()
            ->filter()
            ->values();

        $generations = $request
            ->collect('generations')
            ->map(fn (string $value) => Generation::tryFrom((int) $value))
            ->unique()
            ->filter()
            ->values();

        return new self(
            search: $request->query('search'),
            types: $types,
            traits: $traits,
            generations: $generations,
            sort: $request->enum('sort', SearchPokedexSortBy::class, SearchPokedexSortBy::POKEDEX_NUMBER),
            dir: $request->enum('dir', SortDirection::class, SortDirection::ASC),
        );
    }

    /**
     * @return array<string, FilterOption[]>
     */
    public static function filterOptions(): array
    {
        return [
            'types'       => FilterOption::listFromEnum(ElementalType::class),
            'generations' => FilterOption::listFromEnum(Generation::class),
            'traits'      => FilterOption::listFromEnum(SpeciesTrait::class),
        ];
    }
}
