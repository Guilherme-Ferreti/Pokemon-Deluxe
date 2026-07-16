<?php

declare(strict_types=1);

namespace App\Pokemon\Filters;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Shared\Enums\ElementalType;
use Shared\Enums\Generation;
use Shared\Enums\PokemonCharacteristic;
use Shared\Enums\SearchPokemonInBoxSortBy;
use Shared\Enums\SortDirection;

final class SearchPokemonInBoxFilters
{
    public function __construct(
        public ?string $search,

        /** @var Collection<int, ElementalType> */
        public Collection $elementalTypes,

        /** @var Collection<int, PokemonCharacteristic> */
        public Collection $characteristics,

        /** @var Collection<int, Generation> */
        public Collection $generations,

        public SearchPokemonInBoxSortBy $sortBy,

        public SortDirection $sortDirection,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $elementalTypes = $request
            ->collect('elementalTypes')
            ->map(fn (string $value) => ElementalType::tryFrom($value))
            ->unique()
            ->filter()
            ->take(2)
            ->values();

        $characteristics = $request
            ->collect('characteristics')
            ->map(fn (string $value) => PokemonCharacteristic::tryFrom($value))
            ->unique()
            ->filter()
            ->values();

        $generations = $request
            ->collect('generations')
            ->map(fn (string $value) => Generation::tryFrom((int) $value))
            ->unique()
            ->filter()
            ->values();

        $searchPokemonInBoxSortBy = $request->enum('sortBy', SearchPokemonInBoxSortBy::class, SearchPokemonInBoxSortBy::CAPTURE_DATE);

        $sortDirection = $request->enum('sortDirection', SortDirection::class, SortDirection::ASC);

        return new self(
            search: $request->query('search'),
            elementalTypes: $elementalTypes,
            characteristics: $characteristics,
            generations: $generations,
            sortBy: $searchPokemonInBoxSortBy,
            sortDirection: $sortDirection
        );
    }
}
