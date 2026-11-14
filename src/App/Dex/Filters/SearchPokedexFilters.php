<?php

declare(strict_types=1);

namespace App\Dex\Filters;

use App\Dex\Enums\SearchPokedexSortBy;
use Illuminate\Http\Request;
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

        /** @var list<PokemonType> */
        public array $types,

        /** @var list<SpeciesTrait> */
        public array $traits,

        /** @var list<Generation> */
        public array $generations,

        public SearchPokedexSortBy $sort,

        public SortDirection $dir,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(...[
            'search'      => $request->query('search'),
            'types'       => $request->collect('types')->toUniqueEnums(PokemonType::class)->take(2)->all(),
            'traits'      => $request->collect('traits')->toUniqueEnums(SpeciesTrait::class)->all(),
            'generations' => $request->collect('generations')->toUniqueEnums(Generation::class)->all(),
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
