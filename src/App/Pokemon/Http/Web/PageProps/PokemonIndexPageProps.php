<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\PageProps;

use Closure;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Helpers\AppHelper;
use Shared\Models\Pokemon;

final class PokemonIndexPageProps implements ProvidesInertiaProperties
{
    public function __construct(
        public Closure $pokemonInParty,

        public Closure $pokemonInBox
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'pokemonInParty' => fn (): array => $this->mapPokemonInParty(),
            'pokemonInBox'   => fn (): array => $this->mapPokemonInBox(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPokemonInParty(): array
    {
        return ($this->pokemonInParty)()
            ->map(fn (Pokemon $pokemon): array => [
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
    private function mapPokemonInBox(): array
    {
        return AppHelper::paginatorToArray(($this->pokemonInBox)(), fn (Pokemon $pokemon): array => [
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
