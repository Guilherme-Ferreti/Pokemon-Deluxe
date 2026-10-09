<?php

declare(strict_types=1);

namespace App\Auth\Http\Web\Controllers;

use App\Auth\Actions\LoginUserAction;
use App\Auth\Http\Web\Requests\StoreLoginRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Shared\Base\Controller;

final class LoginController extends Controller
{
    public function create(): Response
    {
        return inertia('Auth/Login');
    }

    public function store(StoreLoginRequest $request): RedirectResponse
    {
        $authenticated = new LoginUserAction()->handle(
            email: $request->validated('email'),
            password: $request->validated('password'),
            remember: $request->boolean('remember'),
        );

        if (! $authenticated) {
            return back()->withErrors([
                'email' => __('auth.failed'),
            ])->onlyInput('email');
        }

        return redirect()->intended(route('dashboard.web.home'));
    }
}
