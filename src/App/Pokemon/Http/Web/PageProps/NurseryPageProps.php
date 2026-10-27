<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\PageProps;

use App\Pokemon\Actions\Incubator\GetIncubatorsAvailabilityAction;
use App\Pokemon\Enums\IncubatorStatus;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Enums\IncubationStatus;
use Shared\Helpers\AppHelper;
use Shared\Models\Incubation;
use Shared\Models\InventoryItem;
use Shared\ValueObjects\IncubatorIndex;

final class NurseryPageProps implements ProvidesInertiaProperties
{
    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'incubators' => fn () => $this->getIncubatorsProp(),
            'pokeballs'  => fn () => $this->getPokeballsProp(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getIncubatorsProp(): array
    {
        $availability      = new GetIncubatorsAvailabilityAction()->handle(currentUser());
        $activeIncubations = Incubation::query()->whereBelongsTo(currentUser())->active()->get();

        return collect(AppHelper::allIncubatorIndexes())
            ->map(function (IncubatorIndex $index) use ($availability, $activeIncubations): array {
                $isActive = value_object_array_contains($availability['active'], $index);

                if ($isActive) {
                    /** @var Incubation */
                    $incubation = $activeIncubations->firstWhere('incubator_index', $index);

                    return [
                        'index'               => $index->value,
                        'status'              => IncubatorStatus::fromIncubationStatus($incubation->getStatus()),
                        'incubationStartedAt' => $incubation->created_at,
                        'readyToHatchAt'      => $incubation->ready_to_hatch_at,
                        'pokemon'             => when($incubation->getStatus() === IncubationStatus::READY_TO_CATCH, fn () => [
                            'name'        => $incubation->species->getDisplayName($incubation->is_shiny),
                            'frontSprite' => $incubation->species->getDisplayFrontSprite($incubation->gender, $incubation->is_shiny),
                            'gender'      => $incubation->gender,
                        ]),
                    ];
                }

                return [
                    'index'               => $index->value,
                    'status'              => value_object_array_contains($availability['idle'], $index) ? IncubatorStatus::IDLE : IncubatorStatus::NOT_PURCHASED,
                    'incubationStartedAt' => null,
                    'readyToHatchAt'      => null,
                    'pokemon'             => null,
                ];
            })
            ->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    private function getPokeballsProp(): array
    {
        return currentUser()
            ->inventoryItems()
            ->pokeballs()
            ->with('item:id,name,sprite')
            ->get(['id', 'item_id', 'item_type', 'quantity'])
            ->map(fn (InventoryItem $inventoryItem) => [
                'id'       => $inventoryItem->item->id,
                'name'     => $inventoryItem->item->name,
                'sprite'   => $inventoryItem->item->sprite,
                'quantity' => $inventoryItem->quantity,
            ])
            ->toArray();
    }
}
