<?php

declare(strict_types=1);

use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;

it('blocks concurrent requests of the same session on every web route', function () {
    $webRoutesWithoutBlocking = collect(Route::getRoutes()->getRoutes())
        ->filter(fn (RoutingRoute $route) => in_array('web', $route->gatherMiddleware(), true))
        ->reject(fn (RoutingRoute $route) => $route->locksFor() !== null)
        ->map(fn (RoutingRoute $route) => $route->uri())
        ->values()
        ->all();

    expect($webRoutesWithoutBlocking)->toBe([]);
});
