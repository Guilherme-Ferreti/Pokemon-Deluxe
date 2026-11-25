<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\PageProps;

use App\Pokemon\Actions\Incubator\ListIncubatorsAction;
use App\Pokemon\Enums\IncubatorStatus;
use App\Pokemon\Support\Incubator;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Models\InventoryItem;
use Shared\Models\Pokeball;

final class NurseryPageProps implements ProvidesInertiaProperties
{
    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'incubators' => $this->getIncubatorsProp(...),
            'pokeballs'  => $this->getPokeballsProp(...),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function getIncubatorsProp(): array
    {
        return new ListIncubatorsAction()
            ->handle(currentUser())
            ->map(function (Incubator $incubator): array {
                $incubation = $incubator->incubation;

                return [
                    'index'               => $incubator->index->value,
                    'status'              => $incubator->status(),
                    'incubationStartedAt' => $incubation?->created_at,
                    'readyToHatchAt'      => $incubation?->ready_to_hatch_at,
                    'pokemon'             => $incubator->isActive() && $incubator->status() === IncubatorStatus::READY_TO_CATCH ? [
                        'name'        => $incubation->species->getDisplayName($incubation->is_shiny),
                        'frontSprite' => $incubation->species->getDisplayFrontSprite($incubation->gender, $incubation->is_shiny),
                        'gender'      => $incubation->gender,
                    ] : null,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    private function getPokeballsProp(): array
    {
        return currentUser()
            ->inventoryItems()
            ->pokeballs()
            ->owned()
            ->with('item:id,name,sprite')
            ->get(['id', 'item_id', 'item_type', 'quantity'])
            ->map(function (InventoryItem $inventoryItem) {
                /** @var Pokeball $pokeball */
                $pokeball = $inventoryItem->item;

                return [
                    'id'       => $pokeball->id,
                    'name'     => $pokeball->name,
                    'sprite'   => $pokeball->sprite,
                    'quantity' => $inventoryItem->quantity,
                ];
            })
            ->toArray();
    }
}
