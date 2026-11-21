<?php

declare(strict_types=1);

namespace Tests\App\Auth\RequestFactories;

use App\Auth\Http\Web\Requests\StoreRegistrationRequest;
use Shared\Models\User;
use Worksome\RequestFactories\RequestFactory;

final class StoreRegistrationRequestFactory extends RequestFactory
{
    public static string $factory = StoreRegistrationRequest::class;

    public function definition(): array
    {
        return [
            'name'                 => 'John Doe',
            'email'                => 'john@example.com',
            'password'             => 'password',
            'passwordConfirmation' => 'password',
            'starter'              => 'bulbasaur',
        ];
    }

    public function withExistingEmail(): static
    {
        return $this->state([
            'email' => User::factory()->create()->email,
        ]);
    }

    public function withStarter(?string $starter): static
    {
        return $this->state([
            'starter' => $starter,
        ]);
    }

    public function withPasswordMismatch(): static
    {
        return $this->state([
            'passwordConfirmation' => 'wrong-password',
        ]);
    }
}
