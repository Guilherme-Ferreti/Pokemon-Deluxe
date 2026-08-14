<?php

declare(strict_types=1);

namespace Shared\Middlewares;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Lottery;
use Inertia\Middleware;
use Override;
use Shared\Models\Species;
use Shared\ValueObjects\PokemonSprites;

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
                'user'          => fn () => $request->user()?->only('id', 'name', 'email'),
                'layoutSprites' => fn () => $this->getAuthLayoutSprites(),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    private function getAuthLayoutSprites(): array
    {
        return Cache::remember('auth-layout-sprites', now()->addDay(), fn () => Species::query()
            ->whereNotNull('sprites->front_default')
            ->whereNotNull('sprites->front_shiny')
            ->inRandomOrder()
            ->limit(49)
            ->pluck('sprites')
            ->map(fn (PokemonSprites $sprites) => Lottery::odds(1, 100)
                ->winner(fn () => $sprites->front_shiny)
                ->loser(fn () => $sprites->front_default)
                ->choose()
            )
            ->toArray()
        );
    }
}
