<?php

declare(strict_types=1);

namespace App\Auth\Http\Web\Controllers;

use App\Auth\Actions\LoginUser;
use App\Auth\Actions\RegisterUser;
use App\Auth\Http\Web\Requests\StoreRegisterRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Shared\Base\Controller;

final class RegisterController extends Controller
{
    public function create(): Response
    {
        return inertia('Auth/Register');
    }

    public function store(StoreRegisterRequest $request): RedirectResponse
    {
        $user = new RegisterUser()->handle(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
        );

        new LoginUser()->handle($user->email, $request->validated('password'));

        return redirect()->intended(route('dashboard.web.home'));
    }
}
