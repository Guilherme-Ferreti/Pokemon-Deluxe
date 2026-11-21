<?php

declare(strict_types=1);

use Shared\Enums\Generation;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;
use Tests\App\Auth\RequestFactories\StoreRegistrationRequestFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('loads the registration page without errors', function () {
    visit(route('auth.web.registration.create'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSeeIn('@auth-form__title', 'Create an account');
});

it('registers a new user with the chosen starter in their party', function () {
    $charmander = Species::factory()->create(['slug' => 'charmander', 'name' => 'Charmander', 'generation' => Generation::I]);
    Pokeball::factory()->create(['slug' => 'poke-ball', 'name' => 'Poké Ball']);
    Pokeball::factory()->create(['slug' => 'great-ball', 'name' => 'Great Ball']);
    $data = StoreRegistrationRequestFactory::new()->create();

    visit(route('auth.web.registration.create'))
        ->fill('name', $data['name'])
        ->fill('email', $data['email'])
        ->fill('password', $data['password'])
        ->fill('passwordConfirmation', $data['password'])
        ->click('@starter-select__starter-select')
        ->click('@starter-select__starter-charmander-item')
        ->assertSeeIn('@starter-select__starter-select', 'Charmander')
        ->press('@auth-form__register-button')
        ->assertPathIs(route('dashboard.web.home', absolute: false))
        ->assertNoJavaScriptErrors();

    assertDatabaseCount(Pokemon::class, 1);
    assertDatabaseHas(Pokemon::class, [
        'user_id'    => User::query()->where('email', $data['email'])->value('id'),
        'species_id' => $charmander->id,
        'in_party'   => true,
    ]);
});

it('asks for a starter when registering without one', function () {
    Species::factory()->create(['slug' => 'charmander', 'name' => 'Charmander', 'generation' => Generation::I]);
    $data = StoreRegistrationRequestFactory::new()->create();

    visit(route('auth.web.registration.create'))
        ->fill('name', $data['name'])
        ->fill('email', $data['email'])
        ->fill('password', $data['password'])
        ->fill('passwordConfirmation', $data['password'])
        ->press('@auth-form__register-button')
        ->assertSeeIn('@starter-select', 'Choose a starter to continue.')
        ->assertNoJavaScriptErrors();

    assertDatabaseCount(User::class, 0);
});

it('shows validation errors on registration', function () {
    $data = StoreRegistrationRequestFactory::new()->withExistingEmail()->create();

    visit(route('auth.web.registration.create'))
        ->fill('name', $data['name'])
        ->fill('email', $data['email'])
        ->fill('password', $data['password'])
        ->fill('passwordConfirmation', $data['password'])
        ->press('@auth-form__register-button')
        ->assertVisible('@error-message');
});
