<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Shared\Enums\InventoryItemType;
use Shared\Models\EvolutionItem;
use Shared\Models\Incubation;
use Shared\Models\InventoryItem;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $user = User::factory()->create([
                'name'  => 'Test User',
                'email' => 'test@example.com',
            ]);

            $this->addPokemonToParty($user);
            $this->addPokemonToBox($user);
            $this->attachMovesToPokemon($user);
            $this->addIncubations($user);
            $this->addItemsToInventory($user);
        });
    }

    private function addPokemonToParty(User $user): void
    {
        $species = Species::query()
            ->inRandomOrder()
            ->take(5)
            ->get();

        Pokemon::factory()
            ->for($user)
            ->for($user, 'originalUser')
            ->recycle(Pokeball::all())
            ->recycle($species)
            ->inParty()
            ->count($species->count())
            ->sequence(fn (Sequence $sequence) => ['species_id' => $species[$sequence->index]->id])
            ->create();
    }

    private function addPokemonToBox(User $user): void
    {
        $species = Species::all();

        $rows = Pokemon::factory()
            ->for($user)
            ->for($user, 'originalUser')
            ->recycle(Pokeball::all())
            ->inBox()
            ->count($species->count() * 2)
            ->sequence(function (Sequence $sequence) use ($species) {
                $rowSpecies = $species[intdiv($sequence->index, 2)];

                return [
                    'species_id' => $rowSpecies->id,
                    'is_shiny'   => $sequence->index % 2 === 1,
                    'gender'     => $rowSpecies->getRandomGender()->value,
                ];
            })
            ->raw();

        collect($rows)
            ->map(fn (array $attributes) => [
                ...$attributes,
                'id'         => new Pokemon()->newUniqueId(),
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->chunk(1000)
            ->each(fn (Collection $chunk) => Pokemon::insert($chunk->all()));
    }

    private function attachMovesToPokemon(User $user): void
    {
        $baseMoveIdsBySpecies = $this->getBaseMoveIdsBySpecies();

        $user->pokemon()
            ->toBase()
            ->pluck('species_id', 'id')
            ->flatMap(fn (string $speciesId, string $pokemonId) => Arr::map($baseMoveIdsBySpecies[$speciesId] ?? [], fn (string $moveId, int $index) => [
                'pokemon_id' => $pokemonId,
                'move_id'    => $moveId,
                'slot'       => $index + 1,
            ]))
            ->chunk(3000)
            ->each(fn (Collection $chunk) => DB::table('pokemon_move')->insert($chunk->all()));
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function getBaseMoveIdsBySpecies(): array
    {
        return DB::table('species_base_move')
            ->get(['species_id', 'move_id'])
            ->groupBy('species_id')
            ->map(fn (Collection $rows) => $rows->pluck('move_id')->take(4)->values()->all())
            ->all();
    }

    private function addIncubations(User $user): void
    {
        $user->update([
            'purchased_incubator_indexes' => [
                new IncubatorIndex(1),
                new IncubatorIndex(2),
                new IncubatorIndex(3),
                new IncubatorIndex(4),
                new IncubatorIndex(5),
                new IncubatorIndex(6),
            ],
        ]);

        $species = Species::query()
            ->where('egg_obtainable', true)
            ->inRandomOrder()
            ->take(10)
            ->get();

        Incubation::factory()
            ->incubating(now()->addHour())
            ->for($user)
            ->recycle($species)
            ->count(2)
            ->sequence(fn (Sequence $sequence) => ['incubator_index' => new IncubatorIndex($sequence->index + 1)])
            ->create();

        Incubation::factory()
            ->readyToHatch()
            ->for($user)
            ->recycle($species)
            ->count(2)
            ->sequence(fn (Sequence $sequence) => ['incubator_index' => new IncubatorIndex($sequence->index + 3)])
            ->create();

        Incubation::factory()
            ->readyToCatch()
            ->for($user)
            ->recycle($species)
            ->create(['incubator_index' => new IncubatorIndex(5)]);

        Incubation::factory()
            ->completed()
            ->for($user)
            ->recycle($species)
            ->create();
    }

    private function addItemsToInventory(User $user): void
    {
        $pokeballs = Pokeball::query()
            ->get()
            ->map(fn (Pokeball $pokeball) => [
                'item_type' => InventoryItemType::POKEBALL->value,
                'item_id'   => $pokeball->id,
                'quantity'  => 10,
            ])
            ->toArray();

        $evolutionItems = EvolutionItem::query()
            ->get()
            ->map(fn (EvolutionItem $evolutionItem) => [
                'item_type' => InventoryItemType::EVOLUTION_ITEM->value,
                'item_id'   => $evolutionItem->id,
                'quantity'  => 2,
            ])
            ->toArray();

        $addDefaultAttributes = fn (array $attributes) => [
            ...$attributes,
            'id'         => new InventoryItem()->newUniqueId(),
            'user_id'    => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $pokeballs      = array_map($addDefaultAttributes, $pokeballs);
        $evolutionItems = array_map($addDefaultAttributes, $evolutionItems);

        InventoryItem::insert([...$pokeballs, ...$evolutionItems]);
    }
}
