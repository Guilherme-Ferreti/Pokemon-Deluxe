<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Shared\Middlewares\HandleInertiaRequests;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        then: function (): void {
            Route::middleware('web')->group(function (): void {
                Route::name('auth.web.')->group(base_path('src/App/Auth/Http/Web/routes.php'));

                Route::name('dashboard.web.')->group(base_path('src/App/Dashboard/Http/Web/routes.php'));

                Route::name('dex.web.')->group(base_path('src/App/Dex/Http/Web/routes.php'));
                Route::name('dex.api.')->group(base_path('src/App/Dex/Http/Api/routes.php'));

                Route::name('pokemon.web.')->group(base_path('src/App/Pokemon/Http/Web/routes.php'));

                Route::name('public.web.')->group(base_path('src/App/Public/Http/Web/routes.php'));
            });
        }
    )
    ->withCommands([
        __DIR__ . '/../src/Shared/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('auth.web.login.create'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create()
    ->useAppPath(__DIR__ . '/../src/Shared');
