<?php

declare(strict_types=1);

namespace Shared\Middlewares;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;
use Override;
use Shared\Enums\Ailment;
use Shared\Enums\ElementalType;
use Shared\Enums\Generation;
use Shared\Enums\PokemonGender;

final class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    #[Override]
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user() ? Inertia::once(fn () => $request->user()->only('id', 'name', 'email')) : null,
            ],
        ];
    }

    #[Override]
    public function shareOnce(Request $request): array
    {
        return [
            ...parent::shareOnce($request),

            'labels' => fn () => [
                'elementalType' => enumLabels(ElementalType::class),
                'pokemonGender' => enumLabels(PokemonGender::class),
                'ailment'       => enumLabels(Ailment::class),
                'generation'    => enumLabels(Generation::class),
            ],

            'config' => fn () => [
                'game' => [
                    'maxEggIncubatorsPerUser' => config('game.max_egg_incubators_per_user'),
                ],
            ],
        ];
    }
}
