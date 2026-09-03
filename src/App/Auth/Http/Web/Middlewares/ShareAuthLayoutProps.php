<?php

declare(strict_types=1);

namespace App\Auth\Http\Web\Middlewares;

use App\Auth\Actions\ListLayoutSprites;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ShareAuthLayoutProps
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        inertia()->shareOnce('authLayout.sprites', fn () => new ListLayoutSprites()->handle());

        return $next($request);
    }
}
