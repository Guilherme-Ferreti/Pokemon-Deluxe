<?php

declare(strict_types=1);

namespace App\Dex\Filters;

use App\Dex\Enums\SearchPokedexSortBy;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Shared\Enums\Generation;
use Shared\Enums\PokemonType;
use Shared\Enums\SortDirection;
use Shared\Enums\SpeciesTrait;
use Shared\Interfaces\Filter;
use Shared\ValueObjects\FilterOption;

final class SearchPokedexFilters implements Filter
{
    public function __construct(
        public ?string $search,

        /** @var Collection<int, PokemonType> */
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
        return new self(...[
            'search'      => $request->query('search'),
            'types'       => $request->collect('types')->toUniqueEnums(PokemonType::class)->take(2),
            'traits'      => $request->collect('traits')->toUniqueEnums(SpeciesTrait::class),
            'generations' => $request->collect('generations')->toUniqueEnums(Generation::class),
            'sort'        => $request->enum('sort', SearchPokedexSortBy::class, SearchPokedexSortBy::POKEDEX_NUMBER),
            'dir'         => $request->enum('dir', SortDirection::class, SortDirection::ASC),
        ]);
    }

    /**
     * @return array<string, FilterOption[]>
     */
    public static function filterOptions(): array
    {
        return [
            'types'       => FilterOption::listFromEnum(PokemonType::class),
            'generations' => FilterOption::listFromEnum(Generation::class),
            'traits'      => FilterOption::listFromEnum(SpeciesTrait::class),
        ];
    }
}
