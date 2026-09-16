<?php

declare(strict_types=1);

namespace App\Dex\Http\Web\PageProps;

use App\Battle\Services\DamageCalculator;
use App\Dex\Actions\CalculateSpeciesCaptureStatistics;
use App\Dex\Actions\CalculateUserSpeciesCaptureStatistics;
use App\Dex\CacheKeys\DexCacheKeys;
use App\Dex\Queries\NextSpeciesQuery;
use App\Dex\Queries\PreviousSpeciesQuery;
use Illuminate\Support\Facades\Cache;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Enums\ElementalType;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;

final readonly class PokedexShowPageProps implements ProvidesInertiaProperties
{
    public function __construct(
        private Species $species
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'species'           => fn () => $this->getSpeciesProp(),
            'previousSpecies'   => fn () => $this->getPreviousSpeciesProp(),
            'nextSpecies'       => fn () => $this->getNextSpeciesProp(),
            'captureStatistics' => fn () => $this->getCaptureStatisticsProp(),
            'damageTaken'       => fn () => $this->getDamageTakenProp(),
            'evolutionChain'    => fn () => $this->getEvolutionChainProp(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getSpeciesProp(): array
    {
        return [
            'id'            => $this->species->id,
            'name'          => $this->species->name,
            'types'         => $this->species->types,
            'pokedexNumber' => $this->species->pokedex_number,
            'genus'         => $this->species->genus,
            'textEntry'     => $this->species->text_entry,
            'sprites'       => [
                'backDefault'      => $this->species->sprites->back_default,
                'backFemale'       => $this->species->sprites->back_female,
                'backShiny'        => $this->species->sprites->back_shiny,
                'backShinyFemale'  => $this->species->sprites->back_shiny_female,
                'frontDefault'     => $this->species->sprites->front_default,
                'frontFemale'      => $this->species->sprites->front_female,
                'frontShiny'       => $this->species->sprites->front_shiny,
                'frontShinyFemale' => $this->species->sprites->front_shiny_female,
            ],
            'portraits' => $this->species->portraits,
            'traits'    => [
                'isBaby'      => $this->species->is_baby,
                'isLegendary' => $this->species->is_legendary,
                'isMythical'  => $this->species->is_mythical,
                'isMega'      => $this->species->is_mega,
                'isGmax'      => $this->species->is_gmax,
            ],
            'generation'            => $this->species->generation->label(),
            'slug'                  => $this->species->slug,
            'isCapturable'          => $this->species->isCapturable(),
            'captureRatePercentage' => $this->species->getCaptureRatePercentage(),
            'isGenderless'          => $this->species->isGenderless(),
            'malePercentage'        => $this->species->getMalePercentage(),
            'femalePercentage'      => $this->species->getFemalePercentage(),
        ];
    }

    /**
     * @return ?array<string, mixed>
     */
    private function getPreviousSpeciesProp(): ?array
    {
        $species = new PreviousSpeciesQuery()->handle($this->species);

        if (! $species instanceof Species) {
            return null;
        }

        return [
            'name'          => $species->name,
            'pokedexNumber' => $species->pokedex_number,
            'portrait'      => $species->portraits->regular,
            'slug'          => $species->slug,
        ];
    }

    /**
     * @return ?array<string, mixed>
     */
    private function getNextSpeciesProp(): ?array
    {
        $species = new NextSpeciesQuery()->handle($this->species);

        if (! $species instanceof Species) {
            return null;
        }

        return [
            'name'          => $species->name,
            'pokedexNumber' => $species->pokedex_number,
            'portrait'      => $species->portraits->regular,
            'slug'          => $species->slug,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getCaptureStatisticsProp(): array
    {
        $global = Cache::remember(
            key: DexCacheKeys::speciesCaptureStatistics($this->species->id),
            ttl: now()->endOfDay(),
            callback: fn () => new CalculateSpeciesCaptureStatistics()->handle($this->species)
        );

        $currentUser = Cache::remember(
            key: DexCacheKeys::userSpeciesCaptureStatistics(currentUser()->id, $this->species->id),
            ttl: now()->endOfDay(),
            callback: fn () => new CalculateUserSpeciesCaptureStatistics()->handle(currentUser(), $this->species)
        );

        return [
            'global'      => $global,
            'currentUser' => $currentUser,
        ];
    }

    /**
     * @return array<string, float>
     */
    private function getDamageTakenProp(): array
    {
        $damageCalculator = new DamageCalculator;
        $damageTaken      = [];

        foreach (ElementalType::cases() as $attackType) {
            $damageTaken[$attackType->value] = $damageCalculator->calculateDamageMultiplier($attackType, ...$this->species->types);
        }

        return $damageTaken;
    }

    /**
     * @return array<string, mixed>
     */
    private function getEvolutionChainProp(): array
    {
        /**
         * @return array<string, mixed>
         */
        $callback = function (): array {
            $previous = $this->species
                ->previousEvolutions()
                ->get()
                ->map(fn (SpeciesEvolution $evolution) => [
                    'species' => [
                        'name'     => $evolution->from->name,
                        'slug'     => $evolution->from->slug,
                        'portrait' => $evolution->from->portraits->regular,
                    ],
                    'evolutionItem' => $evolution->evolutionItem ? [
                        'name'   => $evolution->evolutionItem->name,
                        'sprite' => $evolution->evolutionItem->sprite,
                    ] : null,
                    'minLevel' => $evolution->min_level,
                    'gender'   => $evolution->gender?->value,
                ])
                ->toArray();

            $into = $this->species
                ->evolutions()
                ->get()
                ->map(fn (SpeciesEvolution $evolution) => [
                    'species' => [
                        'name'     => $evolution->into->name,
                        'slug'     => $evolution->into->slug,
                        'portrait' => $evolution->into->portraits->regular,
                    ],
                    'evolutionItem' => $evolution->evolutionItem ? [
                        'name'   => $evolution->evolutionItem->name,
                        'sprite' => $evolution->evolutionItem->sprite,
                    ] : null,
                    'minLevel' => $evolution->min_level,
                    'gender'   => $evolution->gender?->value,
                ])
                ->toArray();

            return [
                'previous' => $previous,
                'into'     => $into,
            ];
        };

        return Cache::rememberForever(
            key: DexCacheKeys::speciesEvolutionChain($this->species->id),
            callback: $callback
        );
    }
}
