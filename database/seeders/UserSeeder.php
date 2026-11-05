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
        $user = User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->addPokemonToParty($user);
        $this->addPokemonToBox($user);
        $this->attachMovesToPokemon($user);
        $this->addIncubations($user);
        $this->addItemsToInventory($user);
    }

    private function addPokemonToParty(User $user): void
    {
        $pokeballs = Pokeball::all();

        Species::query()
            ->inRandomOrder()
            ->take(5)
            ->get()
            ->each(fn (Species $species) => Pokemon::factory()
                ->for($user)
                ->for($user, 'originalUser')
                ->for($species)
                ->recycle($pokeballs)
                ->inParty()
                ->create()
            );
    }

    private function addPokemonToBox(User $user): void
    {
        $pokeballs = Pokeball::all();

        Species::query()
            ->inRandomOrder()
            ->get()
            ->map(fn (Species $species): array => [
                $this->preparePokemonForInsert(Pokemon::factory()->for($user)->for($user, 'originalUser')->for($species)->recycle($pokeballs)->regular()->inBox()->make()),
                $this->preparePokemonForInsert(Pokemon::factory()->for($user)->for($user, 'originalUser')->for($species)->recycle($pokeballs)->shiny()->inBox()->make()),
            ])
            ->flatten(1)
            ->chunk(1000)
            ->each(fn (Collection $chunk) => Pokemon::insert($chunk->toArray()));
    }

    /**
     * @return array<string, mixed>
     */
    private function preparePokemonForInsert(Pokemon $pokemon): array
    {
        $attributes = $pokemon->toArray();

        $attributes['id']         = $pokemon->newUniqueId();
        $attributes['caught_at']  = $pokemon->caught_at->format('Y-m-d H:i:s');
        $attributes['created_at'] = now();
        $attributes['updated_at'] = now();

        return $attributes;
    }

    private function attachMovesToPokemon(User $user): void
    {
        $user->pokemon()
            ->get(['id', 'species_id'])
            ->flatMap(fn (Pokemon $pokemon) => Arr::map($this->getRandomLearnableMoves($pokemon->species_id), fn (string $moveId) => [
                'pokemon_id' => $pokemon->id,
                'move_id'    => $moveId,
            ]))
            ->chunk(3000)
            ->each(fn (Collection $chunk) => DB::table('pokemon_moves')->insert($chunk->toArray()));
    }

    /**
     * @return array<int, string>
     */
    private function getRandomLearnableMoves(string $speciesId): array
    {
        $allMoves = once(fn () => Species::query()
            ->with('learnableMoves:move_id,species_id')
            ->get()
            ->mapWithKeys(fn (Species $species) => [$species->id => $species->learnableMoves->pluck('move_id')->toArray()])
            ->toArray()
        );

        $moves = $allMoves[$speciesId];

        if (count($moves) >= 4) {
            return Arr::random($moves, 4);
        }

        while (count($moves) < 4) {
            $moves = [...$moves, ...$moves];
        }

        return Arr::take($moves, 4);
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
