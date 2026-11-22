<?php

declare(strict_types=1);

namespace App\Exploration\Http\Web\PageProps;

use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Enums\Area;
use Shared\Enums\PokemonType;

final class ExploreIndexPageProps implements ProvidesInertiaProperties
{
    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'areas'              => $this->getAreasProp(...),
            'isBattleInProgress' => fn (): bool => currentUser()->battles()->ongoing()->exists(),
        ];
    }

    /**
     * @return list<array{slug: string, label: string, types: array{PokemonType, PokemonType}}>
     */
    private function getAreasProp(): array
    {
        return array_map(fn (Area $area): array => [
            'slug'  => $area->value,
            'label' => $area->label(),
            'types' => $area->types(),
        ], Area::cases());
    }
}
