<?php

declare(strict_types=1);

namespace Tests\App\Auth\RequestFactories;

use App\Auth\Http\Web\Requests\StoreRegisterRequest;
use Shared\Models\User;
use Worksome\RequestFactories\RequestFactory;

final class StoreRegisterRequestFactory extends RequestFactory
{
    public static string $factory = StoreRegisterRequest::class;

    public function definition(): array
    {
        return [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ];
    }

    public function withExistingEmail(): static
    {
        return $this->state([
            'email' => User::factory()->create()->email,
        ]);
    }
}
