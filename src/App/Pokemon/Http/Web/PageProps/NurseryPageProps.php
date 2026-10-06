<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\PageProps;

use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Models\EggIncubationProcess;

final class NurseryPageProps implements ProvidesInertiaProperties
{
    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'activeEggIncubationProcesses' => $this->getActiveEggIncubationProcessesProp(),
            'incubatorAvailability'        => $this->getIncubatorAvailabilityProp(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getActiveEggIncubationProcessesProp(): array
    {
        return EggIncubationProcess::query()
            ->whereBelongsTo(currentUser())
            ->active()
            ->get()
            ->map(fn (EggIncubationProcess $process) => [
                'eggIncubatorIndex' => $process->egg_incubator_index,
                'eggReadyToHatchAt' => $process->egg_ready_to_hatch_at,
                'isIncubating'      => $process->isIncubating(),
                'isReadyToHatch'    => $process->isReadyToHatch(),
                'isReadyToCapture'  => $process->isReadyToCapture(),
                'pokemon'           => when($process->isReadyToCapture(), fn () => [
                    'name'        => $process->species->getDisplayName($process->is_shiny),
                    'frontSprite' => $process->species->getDisplayFrontSprite($process->gender, $process->is_shiny),
                    'gender'      => $process->gender,
                ]),
                'createdAt' => $process->created_at,
            ])
            ->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    private function getIncubatorAvailabilityProp(): array
    {
        $inUse = EggIncubationProcess::query()
            ->whereBelongsTo(currentUser())
            ->active()
            ->get('egg_incubator_index')
            ->pluck('egg_incubator_index')
            ->values()
            ->toArray();

        $idle = collect(currentUser()->unlocked_egg_incubator_indexes)->diff($inUse)->values()->toArray();

        $locked = collect()->range(1, config('game.max_egg_incubators_per_user'))->diff(currentUser()->unlocked_egg_incubator_indexes)->values()->toArray();

        return [
            'inUse'  => $inUse,
            'idle'   => $idle,
            'locked' => $locked,
        ];
    }
}
