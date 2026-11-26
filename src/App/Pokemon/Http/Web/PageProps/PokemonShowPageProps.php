<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\PageProps;

use App\Pokemon\Mechanics\BoostStoneCalculator;
use App\Pokemon\Mechanics\EvolutionRequirementChecker;
use App\Pokemon\Mechanics\ExperienceCalculator;
use App\Pokemon\Mechanics\StatsCalculator;
use Illuminate\Database\Eloquent\Collection;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Enums\InventoryItemType;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokemon;
use Shared\Models\SpecialItem;
use Shared\Models\SpeciesEvolution;

final readonly class PokemonShowPageProps implements ProvidesInertiaProperties
{
    public function __construct(private Pokemon $pokemon) {}

    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'pokemon'          => $this->getPokemonProp(...),
            'stats'            => $this->getStatsProp(...),
            'activeMoves'      => $this->getActiveMovesProp(...),
            'knownMoves'       => $this->getKnownMovesProp(...),
            'evolutionOptions' => $this->getEvolutionOptionsProp(...),
            'tms'              => $this->getTmsProp(...),
            'boostStones'      => $this->getBoostStonesProp(...),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getPokemonProp(): array
    {
        $this->pokemon->loadMissing(['species', 'pokeball', 'originalUser']);

        return [
            'id'          => $this->pokemon->id,
            'name'        => $this->pokemon->getDisplayName(),
            'frontSprite' => $this->pokemon->getDisplayFrontSprite(),
            'species'     => [
                'id'    => $this->pokemon->species->id,
                'slug'  => $this->pokemon->species->slug,
                'name'  => $this->pokemon->species->name,
                'types' => $this->pokemon->species->types,
            ],
            'gender'     => $this->pokemon->gender,
            'isShiny'    => $this->pokemon->is_shiny,
            'level'      => $this->pokemon->level,
            'boost'      => $this->pokemon->boost,
            'experience' => $this->getExperienceProgress(),
            'pokeball'   => [
                'name'   => $this->pokemon->pokeball->name,
                'sprite' => $this->pokemon->pokeball->sprite,
            ],
            'caughtAt'        => $this->pokemon->caught_at->toIso8601String(),
            'originalTrainer' => $this->pokemon->originalUser?->name,
            'isLocked'        => $this->pokemon->is_locked,
            'inParty'         => $this->pokemon->in_party,
            'partySize'       => currentUser()->pokemon()->inParty()->count(),
        ];
    }

    /**
     * @return array{current: int, needed: int}|null
     */
    private function getExperienceProgress(): ?array
    {
        if ($this->pokemon->level >= (int) config('game.pokemon.max_level')) {
            return null;
        }

        $experienceCalculator = new ExperienceCalculator;
        $levelStart           = $experienceCalculator->experienceForLevel($this->pokemon->level);

        return [
            'current' => $this->pokemon->experience - $levelStart,
            'needed'  => $experienceCalculator->experienceForLevel($this->pokemon->level + 1) - $levelStart,
        ];
    }

    /**
     * @return array<string, array{value: int|float, base: int|float, boostMultiplier: float, shinyMultiplier: float|null}>
     */
    private function getStatsProp(): array
    {
        $statsCalculator = new StatsCalculator;
        $stats           = $this->pokemon->getStats();
        $baseStats       = $statsCalculator->calculate($this->pokemon->level, 0, false);
        $boost           = $this->pokemon->boost;
        $isShiny         = $this->pokemon->is_shiny;

        return [
            'hp' => [
                'value'           => $stats->hp,
                'base'            => $baseStats->hp,
                'boostMultiplier' => round($statsCalculator->hpMultiplier($boost, false), 2),
                'shinyMultiplier' => $isShiny ? round($statsCalculator->hpMultiplier(0, true), 2) : null,
            ],
            'attack' => [
                'value'           => round($stats->attack, 2),
                'base'            => round($baseStats->attack, 2),
                'boostMultiplier' => round($statsCalculator->damageDoneMultiplier($boost, false), 2),
                'shinyMultiplier' => $isShiny ? round($statsCalculator->damageDoneMultiplier(0, true), 2) : null,
            ],
            'damageTaken' => [
                'value'           => round($stats->defense, 2),
                'base'            => 1,
                'boostMultiplier' => round($statsCalculator->damageTakenMultiplier($boost, false), 2),
                'shinyMultiplier' => $isShiny ? round($statsCalculator->damageTakenMultiplier(0, true), 2) : null,
            ],
        ];
    }

    /**
     * @return array<int, array{slot: int, move: array<string, mixed>}>
     */
    private function getActiveMovesProp(): array
    {
        return $this->knownMoves()
            ->filter(fn (Move $move) => $this->slotOf($move) !== null)
            ->sortBy(fn (Move $move) => $this->slotOf($move))
            ->map(fn (Move $move) => [
                'slot' => (int) $this->slotOf($move),
                'move' => $this->toMoveProp($move),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getKnownMovesProp(): array
    {
        return $this->knownMoves()
            ->sortBy('name')
            ->map(fn (Move $move) => [...$this->toMoveProp($move), 'slot' => $this->slotOf($move)])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getEvolutionOptionsProp(): array
    {
        $this->pokemon->loadMissing(['species.evolutions.into', 'species.evolutions.evolutionItem']);

        $ownedEvolutionItemIds = $this->ownedItemIds(InventoryItemType::EVOLUTION_ITEM);
        $checker               = new EvolutionRequirementChecker;

        return $this->pokemon->species->evolutions
            ->sortBy(fn (SpeciesEvolution $evolution) => $evolution->into->name)
            ->map(function (SpeciesEvolution $evolution) use ($checker, $ownedEvolutionItemIds) {
                $unmetRequirement = $checker->check($this->pokemon, $evolution, $ownedEvolutionItemIds);

                return [
                    'id'      => $evolution->id,
                    'species' => [
                        'id'       => $evolution->into->id,
                        'slug'     => $evolution->into->slug,
                        'name'     => $evolution->into->name,
                        'portrait' => $evolution->into->portraits->regular,
                        'types'    => $evolution->into->types,
                    ],
                    'minLevel'      => $evolution->min_level,
                    'gender'        => $evolution->gender,
                    'evolutionItem' => $evolution->evolutionItem === null ? null : [
                        'id'      => $evolution->evolutionItem->id,
                        'name'    => $evolution->evolutionItem->name,
                        'sprite'  => $evolution->evolutionItem->sprite,
                        'isOwned' => in_array($evolution->evolutionItem->id, $ownedEvolutionItemIds, true),
                    ],
                    'isAllowed'        => $unmetRequirement === null,
                    'unmetRequirement' => $unmetRequirement?->reason($evolution),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getTmsProp(): array
    {
        $knownMoveIds = $this->knownMoves()->modelKeys();

        return $this->ownedInventoryItems()
            ->filter(fn (InventoryItem $inventoryItem) => $inventoryItem->item instanceof Move)
            ->sortBy(fn (InventoryItem $inventoryItem) => $inventoryItem->item instanceof Move ? $inventoryItem->item->tm_code : '')
            ->map(function (InventoryItem $inventoryItem) use ($knownMoveIds) {
                /** @var Move $move */
                $move = $inventoryItem->item;

                return [
                    ...$this->toMoveProp($move),
                    'tmName'        => $move->tmName(),
                    'quantity'      => $inventoryItem->quantity,
                    'isKnown'       => in_array($move->id, $knownMoveIds, true),
                    'needsMaxLevel' => $move->is_z_move || $move->is_dynamax_move,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{quantity: int, failureChance: float|null}
     */
    private function getBoostStonesProp(): array
    {
        $quantity = $this->ownedInventoryItems()
            ->first(fn (InventoryItem $inventoryItem) => $inventoryItem->item instanceof SpecialItem && $inventoryItem->item->slug === 'boost-stone')
            ->quantity ?? 0;

        return [
            'quantity'      => $quantity,
            'failureChance' => $this->pokemon->boost >= (int) config('game.pokemon.max_boost')
                ? null
                : new BoostStoneCalculator()->failureChance($this->pokemon->boost),
        ];
    }

    /**
     * @return array{id: string, name: string, type: string, power: int|null, category: string, isZMove: bool, isDynamaxMove: bool}
     */
    private function toMoveProp(Move $move): array
    {
        return [
            'id'            => $move->id,
            'name'          => $move->name,
            'type'          => $move->type->value,
            'power'         => $move->power,
            'category'      => $move->category->value,
            'isZMove'       => $move->is_z_move,
            'isDynamaxMove' => $move->is_dynamax_move,
        ];
    }

    private function slotOf(Move $move): ?int
    {
        $slot = $move->getRelationValue('pivot')->slot;

        return $slot === null ? null : (int) $slot;
    }

    /**
     * @return Collection<int, Move>
     */
    private function knownMoves(): Collection
    {
        return once(fn () => $this->pokemon->knownMoves()->get());
    }

    /**
     * @return Collection<int, InventoryItem>
     */
    private function ownedInventoryItems(): Collection
    {
        return once(fn () => currentUser()->inventoryItems()->owned()->with('item')->get());
    }

    /**
     * @return list<string>
     */
    private function ownedItemIds(InventoryItemType $itemType): array
    {
        return array_values($this->ownedInventoryItems()
            ->filter(fn (InventoryItem $inventoryItem) => $inventoryItem->item_type === $itemType)
            ->map(fn (InventoryItem $inventoryItem) => $inventoryItem->item_id)
            ->all());
    }
}
