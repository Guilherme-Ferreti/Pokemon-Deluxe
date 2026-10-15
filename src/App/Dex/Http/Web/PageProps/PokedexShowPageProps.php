<?php

declare(strict_types=1);

namespace App\Dex\Http\Web\PageProps;

use App\Battle\Services\DamageCalculator;
use App\Dex\Actions\CalculateSpeciesCaptureStatistics;
use App\Dex\Actions\CalculateUserSpeciesCaptureStatistics;
use App\Dex\Queries\NextSpeciesQuery;
use App\Dex\Queries\PreviousSpeciesQuery;
use App\Dex\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Enums\ElementalType;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;
use Shared\Models\SpeciesLearnableMove;

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
            'species'           => $this->getSpeciesProp(),
            'hero'              => $this->getHeroProp(),
            'metadata'          => $this->getMetadataProp(),
            'previousSpecies'   => $this->getPreviousSpeciesProp(),
            'nextSpecies'       => $this->getNextSpeciesProp(),
            'captureStatistics' => $this->getCaptureStatisticsProp(),
            'damageTaken'       => $this->getDamageTakenProp(),
            'evolutionChain'    => $this->getEvolutionChainProp(),
            'learnableMoves'    => $this->getLearnableMovesProp(),
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
    private function getMetadataProp(): array
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
            'generation'            => $this->species->generation,
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
            key: CacheKeys::speciesCaptureStatistics($this->species->id),
            ttl: now()->endOfDay(),
            callback: fn () => new CalculateSpeciesCaptureStatistics()->handle($this->species)
        );

        $currentUser = Cache::remember(
            key: CacheKeys::userSpeciesCaptureStatistics(currentUser()->id, $this->species->id),
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
     * @return array<string, mixed>
     */
    private function getLearnableMovesProp(): array
    {
        $callback = function (): array {
            $levelUpMoves = $this->species
                ->learnableMoves
                ->filter(fn (SpeciesLearnableMove $learnableMove) => $learnableMove->isLearnedByLevelUp())
                ->map(fn (SpeciesLearnableMove $learnableMove) => [
                    'name'          => $learnableMove->move->name,
                    'type'          => $learnableMove->move->type,
                    'category'      => $learnableMove->move->category,
                    'power'         => $learnableMove->move->power,
                    'accuracy'      => $learnableMove->move->accuracy,
                    'isZMove'       => $learnableMove->move->is_z_move,
                    'isDynamaxMove' => $learnableMove->move->is_dynamax_move,
                    'levelLearn'    => $learnableMove->level_learn,
                ])
                ->sortBy([
                    ['levelLearn', 'asc'],
                    ['name', 'asc'],
                ])
                ->values()
                ->toArray();

            $tmMoves = $this->species
                ->learnableMoves
                ->filter(fn (SpeciesLearnableMove $learnableMove) => $learnableMove->isLearnedByTm())
                ->map(fn (SpeciesLearnableMove $learnableMove) => [
                    'name'          => $learnableMove->move->name,
                    'type'          => $learnableMove->move->type,
                    'category'      => $learnableMove->move->category,
                    'power'         => $learnableMove->move->power,
                    'accuracy'      => $learnableMove->move->accuracy,
                    'isZMove'       => $learnableMove->move->is_z_move,
                    'isDynamaxMove' => $learnableMove->move->is_dynamax_move,
                    'tmCode'        => $learnableMove->move->tm_code,
                ])
                ->sortBy('tmCode')
                ->values()
                ->toArray();

            return [
                'levelUp' => $levelUpMoves,
                'tm'      => $tmMoves,
            ];
        };

        return Cache::rememberForever(
            key: CacheKeys::speciesLearnableMoves($this->species->id),
            callback: $callback
        );
    }
}
