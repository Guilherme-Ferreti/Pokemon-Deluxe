<?php

declare(strict_types=1);

namespace App\Pokemon\Filters;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Shared\Enums\Generation;

final class SearchPokemonInBoxFilters
{
    public function __construct(
        public ?string $search,

        /**
         * @var Collection<int, 'baby'|'gmax'|'legendary'|'mega'|'mythical'|'shiny'>
         */
        public Collection $characteristics,

        /** @var Collection<int, Generation> $generations */
        public Collection $generations,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $characteristics = $request
            ->collect('characteristics')
            ->filter(fn (string $characteristic) => in_array($characteristic, ['shiny', 'baby', 'legendary', 'mythical', 'mega', 'gmax'], true))
            ->values();

        $generations = $request
            ->collect('generations')
            ->map(fn (string $generation) => Generation::tryFrom((int) $generation))
            ->unique()
            ->filter()
            ->values();

        return new self(
            search: $request->query('search'),
            characteristics: $characteristics,
            generations: $generations
        );
    }
}
