<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\PageProps;

use App\Pokemon\Actions\EggIncubator\GetEggIncubatorsAvailabilityAction;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Enums\EggIncubationStatus;
use Shared\Models\EggIncubation;

final class NurseryPageProps implements ProvidesInertiaProperties
{
    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'activeEggIncubations'  => fn () => $this->getActiveEggIncubationProp(),
            'incubatorAvailability' => fn () => $this->getIncubatorAvailabilityProp(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getActiveEggIncubationProp(): array
    {
        return EggIncubation::query()
            ->whereBelongsTo(currentUser())
            ->active()
            ->get()
            ->map(fn (EggIncubation $eggIncubation) => [
                'id'                => $eggIncubation->id,
                'eggIncubatorIndex' => $eggIncubation->egg_incubator_index->value,
                'readyToHatchAt'    => $eggIncubation->ready_to_hatch_at,
                'status'            => $eggIncubation->getStatus(),
                'pokemon'           => when($eggIncubation->getStatus() === EggIncubationStatus::READY_TO_CAPTURE, fn () => [
                    'name'        => $eggIncubation->species->getDisplayName($eggIncubation->is_shiny),
                    'frontSprite' => $eggIncubation->species->getDisplayFrontSprite($eggIncubation->gender, $eggIncubation->is_shiny),
                    'gender'      => $eggIncubation->gender,
                ]),
                'createdAt' => $eggIncubation->created_at,
            ])
            ->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    private function getIncubatorAvailabilityProp(): array
    {
        return new GetEggIncubatorsAvailabilityAction()->handle(currentUser());
    }
}
