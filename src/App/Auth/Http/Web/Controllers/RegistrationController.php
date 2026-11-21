<?php

declare(strict_types=1);

namespace App\Auth\Http\Web\Controllers;

use App\Auth\Actions\LoginUserAction;
use App\Auth\Actions\RegisterUserAction;
use App\Auth\Http\Web\Requests\StoreRegistrationRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Shared\Base\Controller;
use Shared\Models\Species;

final class RegistrationController extends Controller
{
    public function create(): Response
    {
        return inertia('Auth::Registration');
    }

    public function store(StoreRegistrationRequest $request): RedirectResponse
    {
        $user = new RegisterUserAction()->handle(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            starter: Species::query()->where('slug', $request->validated('starter'))->firstOrFail(),
        );

        new LoginUserAction()->handle($user->email, $request->validated('password'));

        return redirect()->intended(route('dashboard.web.home'));
    }
}
