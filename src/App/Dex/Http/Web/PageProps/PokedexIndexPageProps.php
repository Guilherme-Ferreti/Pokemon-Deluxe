<?php

declare(strict_types=1);

namespace App\Dex\Http\Web\PageProps;

use Closure;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Helpers\AppHelper;
use Shared\Models\Pokedex;

final readonly class PokedexIndexPageProps implements ProvidesInertiaProperties
{
    public function __construct(public Closure $pokedex) {}

    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'pokedex' => fn () => $this->mapPokedex(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPokedex(): array
    {
        return AppHelper::paginatorToArray(($this->pokedex)(), fn (Pokedex $pokedex) => [
            'id'            => $pokedex->id,
            'portrait'      => 'https://raw.githubusercontent.com/PMDCollab/SpriteCollab/refs/heads/master/portrait/' . str_pad((string) $pokedex->pokedex_number, 4, '0', STR_PAD_LEFT) . '/Normal.png',
            'name'          => $pokedex->name,
            'pokedexNumber' => $pokedex->pokedex_number,
            'frontSprite'   => $pokedex->sprites->front_default,
            'types'         => $pokedex->types,
            'generation'    => $pokedex->generation->label(),
        ]);
    }
}
