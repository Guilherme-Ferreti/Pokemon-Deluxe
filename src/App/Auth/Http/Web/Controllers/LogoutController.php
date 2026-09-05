<?php

declare(strict_types=1);

namespace App\Auth\Http\Web\Controllers;

use App\Auth\Actions\LogoutCurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Shared\Base\Controller;

final class LogoutController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        new LogoutCurrentUser()->handle($request);

        return redirect()->route('public.web.home');
    }
}
