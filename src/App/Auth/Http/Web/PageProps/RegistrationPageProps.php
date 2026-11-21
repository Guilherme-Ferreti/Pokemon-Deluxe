<?php

declare(strict_types=1);

namespace App\Auth\Http\Web\PageProps;

use Illuminate\Support\Collection;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Models\Species;

final class RegistrationPageProps implements ProvidesInertiaProperties
{
    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'starterRegions' => $this->getStarterRegionsProp(...),
        ];
    }

    /**
     * @return list<array{name: string, starters: list<array{slug: string, name: string, portrait: ?string}>}>
     */
    private function getStarterRegionsProp(): array
    {
        /** @var list<string> $starterSlugs */
        $starterSlugs = config('game.registration.starters');

        $regions = Species::query()
            ->whereIn('slug', $starterSlugs)
            ->orderBy('pokedex_order')
            ->get(['id', 'slug', 'name', 'generation', 'portraits'])
            ->groupBy(fn (Species $starter) => $starter->generation->region())
            ->map(fn (Collection $starters, string $region) => [
                'name'     => $region,
                'starters' => array_values($starters
                    ->map(fn (Species $starter) => [
                        'slug'     => $starter->slug,
                        'name'     => $starter->name,
                        'portrait' => $starter->portraits->regular,
                    ])
                    ->all()),
            ])
            ->all();

        return array_values($regions);
    }
}
