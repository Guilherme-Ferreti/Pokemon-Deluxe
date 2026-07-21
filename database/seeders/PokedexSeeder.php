<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Shared\Models\EvolutionItem;
use Shared\Models\Pokedex;
use Shared\Models\PokedexEvolution;

/**
 * @phpstan-type PokeApiEvolution array{evolves_to:string, item:?string, min_level:?int}
 * @phpstan-type PokeApiSprites array{back_default:?string, back_female:?string, back_shiny:?string, back_shiny_female:?string, front_default:?string, front_female:?string, front_shiny:?string, front_shiny_female:?string}
 * @phpstan-type PokeApiPokemon array{id:string, external_id:string, name:string, types:list<string>, national_dex_entry_number:int, genus:string, flavor_text:string, sprites:PokeApiSprites, is_baby:bool, is_legendary:bool, is_mythical:bool, is_mega:bool, is_gmax:bool, generation:int, capture_rate:?int, evolutions:list<PokeApiEvolution>}
 */
final class PokedexSeeder extends Seeder
{
    /** @var array<string, string> */
    private array $pokedexIds = [];

    /** @var array<string, string> */
    private array $evolutionItemIds = [];

    public function run(): void
    {
        $this->evolutionItemIds = $this->getEvolutionItemIds();

        $this->insertPokedexFromFiles();
        $this->insertPokedexEvolutionFromFiles();
    }

    private function insertPokedexFromFiles(): void
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
            ->each(fn (Collection $chunk) => Pokedex::insert($chunk->toArray()));
    }

    private function insertPokedexEvolutionFromFiles(): void
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
            ->each(fn (Collection $chunk) => PokedexEvolution::insert($chunk->toArray()));
    }

    /**
     * @return list<string>
     */
    private function getFilesList(): array
    {
        return [
            __DIR__ . '/files/pokedex/gen-I.json',
            __DIR__ . '/files/pokedex/gen-II.json',
            __DIR__ . '/files/pokedex/gen-III.json',
            __DIR__ . '/files/pokedex/gen-IV.json',
            __DIR__ . '/files/pokedex/gen-V.json',
            __DIR__ . '/files/pokedex/gen-VI.json',
            __DIR__ . '/files/pokedex/gen-VII.json',
            __DIR__ . '/files/pokedex/gen-VIII.json',
            __DIR__ . '/files/pokedex/gen-IX.json',
        ];
    }

    /**
     * @param  PokeApiPokemon  $pokemon
     * @return array<string, mixed>
     */
    private function getPokemonInfo(array $pokemon): array
    {
        return [
            'id'                        => $this->getPokedexId($pokemon['id']),
            'name'                      => $pokemon['name'],
            'types'                     => json_encode($pokemon['types']),
            'national_dex_entry_number' => $pokemon['national_dex_entry_number'],
            'genus'                     => $pokemon['genus'],
            'text_entry'                => $pokemon['flavor_text'],
            'sprites'                   => json_encode([
                'back_default'       => $pokemon['sprites']['back_default'],
                'back_female'        => $pokemon['sprites']['back_female'],
                'back_shiny'         => $pokemon['sprites']['back_shiny'],
                'back_shiny_female'  => $pokemon['sprites']['back_shiny_female'],
                'front_default'      => $pokemon['sprites']['front_default'],
                'front_female'       => $pokemon['sprites']['front_female'],
                'front_shiny'        => $pokemon['sprites']['front_shiny'],
                'front_shiny_female' => $pokemon['sprites']['front_shiny_female'],
            ]),
            'is_baby'      => $pokemon['is_baby'],
            'is_legendary' => $pokemon['is_legendary'],
            'is_mythical'  => $pokemon['is_mythical'],
            'is_mega'      => $pokemon['is_mega'],
            'is_gmax'      => $pokemon['is_gmax'],
            'generation'   => $pokemon['generation'],
            'capture_rate' => $pokemon['capture_rate'],
            'internal_id'  => $pokemon['id'],
            'external_id'  => $pokemon['external_id'],
            'created_at'   => now(),
            'updated_at'   => now(),
        ];
    }

    /**
     * @param  PokeApiPokemon  $pokemon
     * @return list<array<string, mixed>>
     */
    private function getEvolutionInfo(array $pokemon): array
    {
        return array_map(fn (array $evolution) => [
            'id'                => (new PokedexEvolution)->newUniqueId(),
            'from_pokedex_id'   => $this->getPokedexId($pokemon['id']),
            'to_pokedex_id'     => $this->getPokedexId($evolution['evolves_to']),
            'evolution_item_id' => is_null($evolution['item']) ? null : $this->evolutionItemIds[$evolution['item']],
            'min_level'         => $evolution['min_level'],
            'created_at'        => now(),
            'updated_at'        => now(),
        ], $pokemon['evolutions']);
    }

    private function getPokedexId(string $externalId): string
    {
        if (! isset($this->pokedexIds[$externalId])) {
            $this->pokedexIds[$externalId] = (new Pokedex)->newUniqueId();
        }

        return $this->pokedexIds[$externalId];
    }

    /**
     * @return array<string, string>
     */
    private function getEvolutionItemIds(): array
    {
        return EvolutionItem::pluck('id', 'external_id')->toArray();
    }
}
