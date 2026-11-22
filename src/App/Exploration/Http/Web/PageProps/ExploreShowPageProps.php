<?php

declare(strict_types=1);

namespace App\Exploration\Http\Web\PageProps;

use App\Exploration\Queries\AreaSpeciesQuery;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Enums\Area;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Models\Encounter;
use Shared\Models\Species;

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
            'areaSpecies'        => Inertia::optional($this->getAreaSpeciesProp(...))->once(),
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
     * @return array<int, array{slug: string, name: string, portrait: ?string, types: Collection<int, PokemonType>}>
     */
    private function getAreaSpeciesProp(): array
    {
        return new AreaSpeciesQuery()
            ->handle($this->area)
            ->select('id', 'slug', 'name', 'portraits', 'types')
            ->get()
            ->map(fn (Species $species): array => [
                'slug'     => $species->slug,
                'name'     => $species->name,
                'portrait' => $species->portraits->regular,
                'types'    => $species->types,
            ])
            ->values()
            ->all();
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
