<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Shared\Models\EvolutionItem;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;

/**
 * @phpstan-type SeederEvolution array{evolves_to:string, item:?string, min_level:?int}
 * @phpstan-type SeederSprites array{back_default:?string, back_female:?string, back_shiny:?string, back_shiny_female:?string, front_default:?string, front_female:?string, front_shiny:?string, front_shiny_female:?string}
 * @phpstan-type SeederPortraits array{normal:?string, shiny:?string}
 * @phpstan-type SeederPokemon array{id:string, external_id:string, name:string, types:list<string>, pokedex_number:int, genus:string, flavor_text:string, sprites:SeederSprites, portraits:SeederPortraits, is_baby:bool, is_legendary:bool, is_mythical:bool, is_mega:bool, is_gmax:bool, generation:int, capture_rate:?int, evolutions:list<SeederEvolution>}
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
            $pokemonList = json_decode((string) file_get_contents($path), true);

            foreach ($pokemonList as $pokemon) {
                $data[] = $this->getPokemonInfo($pokemon);
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
            $pokemonList = json_decode((string) file_get_contents($path), true);

            foreach ($pokemonList as $pokemon) {
                $data[] = $this->getEvolutionInfo($pokemon);
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
     * @param  SeederPokemon  $pokemon
     * @return array<string, mixed>
     */
    private function getPokemonInfo(array $pokemon): array
    {
        return [
            'id'             => $this->getSpeciesId($pokemon['id']),
            'name'           => $pokemon['name'],
            'types'          => json_encode($pokemon['types']),
            'pokedex_number' => $pokemon['pokedex_number'],
            'genus'          => $pokemon['genus'],
            'text_entry'     => $pokemon['flavor_text'],
            'sprites'        => json_encode([
                'back_default'       => $pokemon['sprites']['back_default'],
                'back_female'        => $pokemon['sprites']['back_female'],
                'back_shiny'         => $pokemon['sprites']['back_shiny'],
                'back_shiny_female'  => $pokemon['sprites']['back_shiny_female'],
                'front_default'      => $pokemon['sprites']['front_default'],
                'front_female'       => $pokemon['sprites']['front_female'],
                'front_shiny'        => $pokemon['sprites']['front_shiny'],
                'front_shiny_female' => $pokemon['sprites']['front_shiny_female'],
            ]),
            'portraits' => json_encode([
                'normal' => $pokemon['portraits']['normal'],
                'shiny'  => $pokemon['portraits']['shiny'],
            ]),
            'is_baby'      => $pokemon['is_baby'],
            'is_legendary' => $pokemon['is_legendary'],
            'is_mythical'  => $pokemon['is_mythical'],
            'is_mega'      => $pokemon['is_mega'],
            'is_gmax'      => $pokemon['is_gmax'],
            'generation'   => $pokemon['generation'],
            'capture_rate' => $pokemon['capture_rate'],
            'slug'         => $this->generateSpeciesSlug($pokemon['name']),
            'external_id'  => $pokemon['external_id'],
            'created_at'   => now(),
            'updated_at'   => now(),
        ];
    }

    /**
     * @param  SeederPokemon  $pokemon
     * @return list<array<string, mixed>>
     */
    private function getEvolutionInfo(array $pokemon): array
    {
        return array_map(fn (array $evolution) => [
            'id'                => (new SpeciesEvolution)->newUniqueId(),
            'from_species_id'   => $this->getSpeciesId($pokemon['id']),
            'to_species_id'     => $this->getSpeciesId($evolution['evolves_to']),
            'evolution_item_id' => is_null($evolution['item']) ? null : $this->evolutionItemIds[$evolution['item']],
            'min_level'         => $evolution['min_level'],
            'created_at'        => now(),
            'updated_at'        => now(),
        ], $pokemon['evolutions']);
    }

    private function getSpeciesId(string $externalId): string
    {
        return $this->speciesIds[$externalId] ??= (new Species)->newUniqueId();
    }

    /**
     * @return array<string, string>
     */
    private function getEvolutionItemIds(): array
    {
        return EvolutionItem::pluck('id', 'external_id')->toArray();
    }

    private function generateSpeciesSlug(string $name): string
    {
        return Str::of($name)->slug()->replace('g-max-', 'gmax-')->toString();
    }
}
