<?php

declare(strict_types=1);

namespace App\Dex\Http\Web\PageProps;

use App\Battle\Services\DamageCalculator;
use App\Dex\Actions\CalculateSpeciesCatchStatisticsAction;
use App\Dex\Actions\CalculateUserSpeciesCatchStatisticsAction;
use App\Dex\Queries\NextSpeciesQuery;
use App\Dex\Queries\PreviousSpeciesQuery;
use App\Dex\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Enums\PokemonType;
use Shared\Models\Move;
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
            'species'         => $this->getSpeciesProp(),
            'hero'            => $this->getHeroProp(),
            'overview'        => $this->getOverviewProp(),
            'previousSpecies' => $this->getPreviousSpeciesProp(),
            'nextSpecies'     => $this->getNextSpeciesProp(),
            'catchStatistics' => $this->getCatchStatisticsProp(),
            'damageTaken'     => $this->getDamageTakenProp(),
            'evolutionChain'  => $this->getEvolutionChainProp(),
            'baseMoves'       => $this->getBaseMovesProp(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getSpeciesProp(): array
    {
        return [
            'name' => $this->species->name,
            'slug' => $this->species->slug,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getHeroProp(): array
    {
        return [
            'name'          => $this->species->name,
            'pokedexNumber' => $this->species->pokedex_number,
            'genus'         => $this->species->genus,
            'textEntry'     => $this->species->text_entry,
            'sprites'       => [
                'frontDefault'     => $this->species->sprites->front_default,
                'frontFemale'      => $this->species->sprites->front_female,
                'frontShiny'       => $this->species->sprites->front_shiny,
                'frontShinyFemale' => $this->species->sprites->front_shiny_female,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getOverviewProp(): array
    {
        return [
            'types'     => $this->species->types,
            'portraits' => $this->species->portraits,
            'traits'    => [
                'isBaby'      => $this->species->is_baby,
                'isLegendary' => $this->species->is_legendary,
                'isMythical'  => $this->species->is_mythical,
                'isMega'      => $this->species->is_mega,
                'isGmax'      => $this->species->is_gmax,
            ],
            'generation'          => $this->species->generation,
            'catchRatePercentage' => $this->species->getCatchRatePercentage(),
            'isGenderless'        => $this->species->isGenderless(),
            'malePercentage'      => $this->species->gender_rate->malePercentage(),
            'femalePercentage'    => $this->species->gender_rate->femalePercentage(),
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
    private function getCatchStatisticsProp(): array
    {
        $global = Cache::remember(
            key: CacheKeys::speciesCatchStatistics($this->species->id),
            ttl: now()->endOfDay(),
            callback: fn () => new CalculateSpeciesCatchStatisticsAction()->handle($this->species)
        );

        $currentUser = Cache::remember(
            key: CacheKeys::userSpeciesCatchStatistics(currentUser()->id, $this->species->id),
            ttl: now()->endOfDay(),
            callback: fn () => new CalculateUserSpeciesCatchStatisticsAction()->handle(currentUser(), $this->species)
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

        foreach (PokemonType::cases() as $attackType) {
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
                ->with('from:id,slug,name,portraits', 'evolutionItem:id,name,sprite')
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
                ->with('into:id,slug,name,portraits', 'evolutionItem:id,name,sprite')
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
            key: CacheKeys::speciesEvolutionChain($this->species->id),
            callback: $callback
        );
    }

    /**
     * @return array<int, mixed>
     */
    private function getBaseMovesProp(): array
    {
        return Cache::rememberForever(
            key: CacheKeys::speciesBaseMoves($this->species->id),
            callback: fn (): array => $this->species
                ->baseMoves
                ->sortBy('name')
                ->map(fn (Move $move) => [
                    'name'          => $move->name,
                    'type'          => $move->type,
                    'category'      => $move->category,
                    'power'         => $move->power,
                    'accuracy'      => $move->accuracy,
                    'ailment'       => $move->ailment,
                    'ailmentChance' => $move->ailment_chance,
                    'flinchChance'  => $move->flinch_chance,
                    'isZMove'       => $move->is_z_move,
                    'isDynamaxMove' => $move->is_dynamax_move,
                ])
                ->values()
                ->toArray()
        );
    }
}
