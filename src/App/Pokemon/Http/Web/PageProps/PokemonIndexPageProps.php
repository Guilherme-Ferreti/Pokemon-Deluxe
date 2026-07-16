<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\PageProps;

use App\Pokemon\Filters\SearchBoxFilters;
use Closure;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Helpers\AppHelper;
use Shared\Models\Pokemon;

final class PokemonIndexPageProps implements ProvidesInertiaProperties
{
    public function __construct(
        public Closure $pokemonInParty,

        public Closure $pokemonBox,

        public SearchBoxFilters $searchFilters,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'pokemonInParty' => fn () => $this->mapPokemonInParty(),
            'pokemonBox'     => fn () => $this->mapPokemonBox(),
            'searchFilters'  => fn () => $this->searchFilters,
            'filterOptions'  => fn () => SearchBoxFilters::filterOptions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPokemonInParty(): array
    {
        return ($this->pokemonInParty)()
            ->map(fn (Pokemon $pokemon) => [
                'id'          => $pokemon->id,
                'name'        => $pokemon->getName(),
                'frontSprite' => $pokemon->getFrontSprite(),
                'level'       => $pokemon->level,
            ])
            ->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPokemonBox(): array
    {
        return AppHelper::paginatorToArray(($this->pokemonBox)(), fn (Pokemon $pokemon) => [
            'id'          => $pokemon->id,
            'name'        => $pokemon->getName(),
            'frontSprite' => $pokemon->getFrontSprite(),
            'level'       => $pokemon->level,
            'experience'  => $pokemon->experience,
            'pokedex'     => [
                'primaryType'   => $pokemon->pokedex->primary_type,
                'secondaryType' => $pokemon->pokedex->secondary_type,
            ],
            'moves' => [
                [
                    'name' => 'Giga Impact',
                ],
                [
                    'name' => 'Flamethrower',
                ],
                [
                    'name' => 'Earthquake',
                ],
                [
                    'name' => 'Ice Beam',
                ],
            ],
        ]);

    }
}
