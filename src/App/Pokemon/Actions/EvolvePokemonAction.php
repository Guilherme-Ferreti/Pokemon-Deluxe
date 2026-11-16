<?php

declare(strict_types=1);

namespace App\Pokemon\Actions;

use App\Inventory\Actions\RemoveInventoryItemAction;
use App\Pokemon\Enums\EvolutionRequirement;
use App\Pokemon\Events\PokemonEvolved;
use App\Pokemon\Exceptions\EvolutionNotAllowedException;
use App\Pokemon\Mechanics\EvolutionRequirementChecker;
use App\Pokemon\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Shared\Enums\InventoryItemType;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Pokemon;
use Shared\Models\SpeciesEvolution;

final class EvolvePokemonAction
{
    private Pokemon $pokemon;

    private SpeciesEvolution $evolution;

    /** @var list<string> */
    private array $ownedEvolutionItemIds = [];

    public function handle(Pokemon $pokemon, SpeciesEvolution $evolution): Pokemon
    {
        $this->pokemon   = $pokemon;
        $this->evolution = $evolution;

        return Cache::withoutOverlapping(
            key: LockKeys::pokemonEvolution($pokemon->id),
            callback: fn () => $this->evolve(),
        );
    }

    private function evolve(): Pokemon
    {
        $this->pokemon->refresh();
        $this->evolution->loadMissing(['evolutionItem', 'into']);

        $this->loadOwnedEvolutionItemIds();

        $this->ensureEvolutionIsOfSpecies();
        $this->ensureRequirementsAreMet();

        $fromSpeciesId = $this->pokemon->species_id;

        DB::transaction(function (): void {
            $this->useUpEvolutionItem();

            $this->pokemon->update(['species_id' => $this->evolution->into_species_id]);

            $this->addNewBaseMoves();
        });

        PokemonEvolved::dispatch(
            $this->pokemon->id,
            $this->pokemon->user_id,
            $fromSpeciesId,
            $this->evolution->into_species_id,
            $this->evolution->into->is_mega,
        );

        return $this->pokemon;
    }

    private function loadOwnedEvolutionItemIds(): void
    {
        $ownedItems = InventoryItem::query()
            ->where('user_id', $this->pokemon->user_id)
            ->where('item_type', InventoryItemType::EVOLUTION_ITEM)
            ->owned()
            ->get(['item_id']);

        $this->ownedEvolutionItemIds = array_values($ownedItems->map(fn (InventoryItem $inventoryItem) => $inventoryItem->item_id)->all());
    }

    private function ensureEvolutionIsOfSpecies(): void
    {
        throw_if(
            $this->evolution->from_species_id !== $this->pokemon->species_id,
            InvalidArgumentException::class,
            "The evolution [{$this->evolution->id}] does not start from the Pokémon's species.",
        );
    }

    private function ensureRequirementsAreMet(): void
    {
        $unmetRequirement = new EvolutionRequirementChecker()->check($this->pokemon, $this->evolution, $this->ownedEvolutionItemIds);

        if ($unmetRequirement instanceof EvolutionRequirement) {
            throw new EvolutionNotAllowedException($unmetRequirement, $this->evolution);
        }
    }

    private function useUpEvolutionItem(): void
    {
        if (! $this->evolution->evolutionItem instanceof EvolutionItem) {
            return;
        }

        new RemoveInventoryItemAction()->handle($this->pokemon->user, $this->evolution->evolutionItem);
    }

    private function addNewBaseMoves(): void
    {
        $knownMoveIds = $this->pokemon->knownMoves()->pluck('moves.id');

        $newMoveIds = $this->evolution->into->baseMoves()
            ->whereKeyNot($knownMoveIds->all())
            ->pluck('moves.id');

        $this->pokemon->knownMoves()->attach(
            $newMoveIds->mapWithKeys(fn (string $moveId) => [$moveId => ['slot' => null]])->all(),
        );
    }
}
