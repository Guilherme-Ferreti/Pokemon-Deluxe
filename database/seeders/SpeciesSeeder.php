<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Shared\Models\EvolutionItem;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;

/**
 * @phpstan-type SeederEvolution array{evolves_into:string, item:?string, min_level:?int, gender:?string}
 * @phpstan-type SeederSprites array{back_default:?string, back_female:?string, back_shiny:?string, back_shiny_female:?string, front_default:?string, front_female:?string, front_shiny:?string, front_shiny_female:?string}
 * @phpstan-type SeederPortraits array{regular:?string, shiny:?string}
 * @phpstan-type SeederSpecies array{id:string, name:string, types:list<string>, pokedex_number:int, genus:string, flavor_text:string, sprites:SeederSprites, portraits:SeederPortraits, is_baby:bool, is_legendary:bool, is_mythical:bool, is_mega:bool, is_gmax:bool, generation:int, capture_rate:?int, gender_rate:int, egg_obtainable:bool, evolutions:list<SeederEvolution>}
 */
final class SpeciesSeeder extends Seeder
{
    /** @var array<string, string> */
    private array $speciesIds = [];

    /** @var array<string, string> */
    private array $evolutionItemIds = [];

    public function run(): void
    {
        $this->evolutionItemIds = $this->getEvolutionItemIds();

        $this->insertSpeciesFromFiles();
        $this->insertSpeciesEvolutionFromFiles();
    }

    private function insertSpeciesFromFiles(): void
    {
        $data = [];

        foreach ($this->getFilesList() as $path) {
            $speciesList = json_decode((string) file_get_contents($path), true);

            foreach ($speciesList as $species) {
                $data[] = $this->getSpeciesInfo($species);
            }
        }

        collect($data)
            ->chunk(100)
            ->each(fn (Collection $chunk) => Species::insert($chunk->toArray()));
    }

    private function insertSpeciesEvolutionFromFiles(): void
    {
        $data = [];

        foreach ($this->getFilesList() as $path) {
            $speciesList = json_decode((string) file_get_contents($path), true);

            foreach ($speciesList as $species) {
                $data[] = $this->getEvolutionInfo($species);
            }
        }

        collect($data)
            ->flatten(1)
            ->filter(fn (array $item) => $item !== [])
            ->chunk(100)
            ->each(fn (Collection $chunk) => SpeciesEvolution::insert($chunk->toArray()));
    }

    /**
     * @return list<string>
     */
    private function getFilesList(): array
    {
        return [
            __DIR__ . '/files/species/gen-I.json',
            __DIR__ . '/files/species/gen-II.json',
            __DIR__ . '/files/species/gen-III.json',
            __DIR__ . '/files/species/gen-IV.json',
            __DIR__ . '/files/species/gen-V.json',
            __DIR__ . '/files/species/gen-VI.json',
            __DIR__ . '/files/species/gen-VII.json',
            __DIR__ . '/files/species/gen-VIII.json',
            __DIR__ . '/files/species/gen-IX.json',
        ];
    }

    /**
     * @param  SeederSpecies  $species
     * @return array<string, mixed>
     */
    private function getSpeciesInfo(array $species): array
    {
        return [
            'id'             => $this->getSpeciesId($species['id']),
            'slug'           => $species['id'],
            'name'           => $species['name'],
            'types'          => json_encode($species['types']),
            'pokedex_number' => $species['pokedex_number'],
            'genus'          => $species['genus'],
            'text_entry'     => $species['flavor_text'],
            'sprites'        => json_encode([
                'back_default'       => $species['sprites']['back_default'],
                'back_female'        => $species['sprites']['back_female'],
                'back_shiny'         => $species['sprites']['back_shiny'],
                'back_shiny_female'  => $species['sprites']['back_shiny_female'],
                'front_default'      => $species['sprites']['front_default'],
                'front_female'       => $species['sprites']['front_female'],
                'front_shiny'        => $species['sprites']['front_shiny'],
                'front_shiny_female' => $species['sprites']['front_shiny_female'],
            ]),
            'portraits' => json_encode([
                'regular' => $species['portraits']['regular'],
                'shiny'   => $species['portraits']['shiny'],
            ]),
            'is_baby'        => $species['is_baby'],
            'is_legendary'   => $species['is_legendary'],
            'is_mythical'    => $species['is_mythical'],
            'is_mega'        => $species['is_mega'],
            'is_gmax'        => $species['is_gmax'],
            'generation'     => $species['generation'],
            'capture_rate'   => $species['capture_rate'],
            'gender_rate'    => $species['gender_rate'],
            'egg_obtainable' => $species['egg_obtainable'],
        ];
    }

    /**
     * @param  SeederSpecies  $species
     * @return list<array<string, mixed>>
     */
    private function getEvolutionInfo(array $species): array
    {
        return array_map(fn (array $evolution) => [
            'id'                => (new SpeciesEvolution)->newUniqueId(),
            'from_species_id'   => $this->getSpeciesId($species['id']),
            'into_species_id'   => $this->getSpeciesId($evolution['evolves_into']),
            'evolution_item_id' => is_null($evolution['item']) ? null : $this->evolutionItemIds[$evolution['item']],
            'min_level'         => $evolution['min_level'],
            'gender'            => $evolution['gender'],
        ], $species['evolutions']);
    }

    private function getSpeciesId(string $id): string
    {
        return $this->speciesIds[$id] ??= (new Species)->newUniqueId();
    }

    /**
     * @return array<string, string>
     */
    private function getEvolutionItemIds(): array
    {
        return EvolutionItem::pluck('id', 'slug')->toArray();
    }
}
