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
        public Collection $pokemonInParty
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
        ];
    }
}
