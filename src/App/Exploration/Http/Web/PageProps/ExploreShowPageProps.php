<?php

declare(strict_types=1);

namespace App\Exploration\Http\Web\PageProps;

use Illuminate\Support\Collection;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Enums\Area;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Models\Encounter;

final readonly class ExploreShowPageProps implements ProvidesInertiaProperties
{
    public function __construct(
        private Area $area,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'area'               => $this->getAreaProp(...),
            'encounter'          => $this->getEncounterProp(...),
            'isBattleInProgress' => fn (): bool => currentUser()->battles()->ongoing()->exists(),
        ];
    }

    /**
     * @return array{slug: string, label: string, types: array{PokemonType, PokemonType}}
     */
    private function getAreaProp(): array
    {
        return [
            'slug'  => $this->area->value,
            'label' => $this->area->label(),
            'types' => $this->area->types(),
        ];
    }

    /**
     * @return ?array{sprite: string, name: string, gender: PokemonGender, level: int, types: Collection<int, PokemonType>, isShiny: bool, isLegendary: bool, isMythical: bool}
     */
    private function getEncounterProp(): ?array
    {
        $encounter = currentUser()->encounter()->where('area', $this->area)->with('species')->first();

        if (! $encounter instanceof Encounter) {
            return null;
        }

        return [
            'sprite'      => $encounter->species->getDisplayFrontSprite($encounter->gender, $encounter->is_shiny),
            'name'        => $encounter->species->getDisplayName($encounter->is_shiny),
            'gender'      => $encounter->gender,
            'level'       => $encounter->level,
            'types'       => $encounter->species->types,
            'isShiny'     => $encounter->is_shiny,
            'isLegendary' => $encounter->species->is_legendary,
            'isMythical'  => $encounter->species->is_mythical,
        ];
    }
}
