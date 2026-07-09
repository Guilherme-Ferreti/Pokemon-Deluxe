<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\PageProps;

use Illuminate\Database\Eloquent\Collection;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Models\Pokemon;

final class PokemonIndexPageProps implements ProvidesInertiaProperties
{
    public function __construct(
        /** @var Collection<int, Pokemon> */
        public Collection $pokemonInParty,

        /** @var Collection<int, Pokemon> */
        public Collection $pokemonInBox
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'pokemonInParty' => $this->pokemonInParty->map(fn (Pokemon $pokemon): array => [
                'id'          => $pokemon->id,
                'name'        => $pokemon->getName(),
                'frontSprite' => $pokemon->getFrontSprite(),
                'level'       => $pokemon->level,
            ]),
            'pokemonInBox' => $this->pokemonInBox->map(fn (Pokemon $pokemon): array => [
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
            ]),
        ];
    }
}
