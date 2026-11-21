<?php

declare(strict_types=1);

use Illuminate\Validation\Rules\Password;
use Inertia\Testing\AssertableInertia as Assert;
use Shared\Enums\Generation;
use Shared\Enums\InventoryItemType;
use Shared\Models\InventoryItem;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\SpeciesPortraits;
use Tests\App\Auth\RequestFactories\StoreRegistrationRequestFactory;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('redirects authenticated users to dashboard', function () {
    actingAs(User::factory()->create())
        ->get(route('auth.web.login.create'))
        ->assertRedirectToRoute('dashboard.web.home');
});

it('loads the registration page with the starters grouped by region', function () {
    config()->set('game.registration.starters', ['bulbasaur', 'charmander', 'chikorita']);

    Species::factory()->create(['slug' => 'chikorita', 'name' => 'Chikorita', 'pokedex_number' => 152, 'generation' => Generation::II, 'portraits' => new SpeciesPortraits('chikorita.png', null)]);
    Species::factory()->create(['slug' => 'charmander', 'name' => 'Charmander', 'pokedex_number' => 4, 'generation' => Generation::I, 'portraits' => new SpeciesPortraits('charmander.png', null)]);
    Species::factory()->create(['slug' => 'bulbasaur', 'name' => 'Bulbasaur', 'pokedex_number' => 1, 'generation' => Generation::I, 'portraits' => new SpeciesPortraits('bulbasaur.png', null)]);
    Species::factory()->create(['slug' => 'pikachu', 'pokedex_number' => 25, 'generation' => Generation::I]);

    get(route('auth.web.registration.create'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/pages/Registration/index')
            ->where('starterRegions', [
                ['name' => 'Kanto', 'starters' => [
                    ['slug' => 'bulbasaur', 'name' => 'Bulbasaur', 'portrait' => 'bulbasaur.png'],
                    ['slug' => 'charmander', 'name' => 'Charmander', 'portrait' => 'charmander.png'],
                ]],
                ['name' => 'Johto', 'starters' => [
                    ['slug' => 'chikorita', 'name' => 'Chikorita', 'portrait' => 'chikorita.png'],
                ]],
            ])
        );
});

it('registers a new user with the chosen starter and the starting balls', function () {
    $bulbasaur = Species::factory()->create(['slug' => 'bulbasaur']);
    $pokeBall  = Pokeball::factory()->create(['slug' => 'poke-ball']);
    $greatBall = Pokeball::factory()->create(['slug' => 'great-ball']);
    $data      = StoreRegistrationRequestFactory::new()->withStarter('bulbasaur')->create();

    post(route('auth.web.registration.store'), $data)
        ->assertRedirectToRoute('dashboard.web.home');

    $user = User::query()->where('email', $data['email'])->firstOrFail();

    assertDatabaseCount(User::class, 1);
    assertDatabaseHas(User::class, ['name' => $data['name'], 'email' => $data['email']]);

    assertDatabaseCount(Pokemon::class, 1);
    assertDatabaseHas(Pokemon::class, [
        'user_id'     => $user->id,
        'species_id'  => $bulbasaur->id,
        'level'       => 5,
        'is_shiny'    => false,
        'in_party'    => true,
        'pokeball_id' => $pokeBall->id,
    ]);

    assertDatabaseCount(InventoryItem::class, 2);
    assertDatabaseHas(InventoryItem::class, ['user_id' => $user->id, 'item_type' => InventoryItemType::POKEBALL, 'item_id' => $pokeBall->id, 'quantity' => 20]);
    assertDatabaseHas(InventoryItem::class, ['user_id' => $user->id, 'item_type' => InventoryItemType::POKEBALL, 'item_id' => $greatBall->id, 'quantity' => 5]);
});

it('rejects registration without a starter from the starters list', function (?string $starter) {
    Species::factory()->create(['slug' => 'pikachu']);

    $data = StoreRegistrationRequestFactory::new()->withStarter($starter)->create();

    post(route('auth.web.registration.store'), $data)
        ->assertRedirectBackWithErrors('starter');

    assertDatabaseCount(User::class, 0);
})->with([
    'missing'       => [null],
    'not a starter' => ['pikachu'],
]);

it('rejects registration with an existing email', function () {
    $data = StoreRegistrationRequestFactory::new()->withExistingEmail()->create();

    post(route('auth.web.registration.store'), $data)
        ->assertRedirectBackWithErrors('email');
});

it('rejects registration if passwords do not match', function () {
    $data = StoreRegistrationRequestFactory::new()->withPasswordMismatch()->create();

    post(route('auth.web.registration.store'), $data)
        ->assertRedirectBackWithErrors('password');
});

it('rejects a password that does not meet the configured password policy', function () {
    Password::defaults(fn () => Password::min(12));
    $data = StoreRegistrationRequestFactory::new()->create();

    post(route('auth.web.registration.store'), $data)
        ->assertRedirectBackWithErrors('password');

    assertDatabaseCount(User::class, 0);
});

it('throttles repeated registration attempts', function () {
    $data = StoreRegistrationRequestFactory::new()->withPasswordMismatch()->create();

    foreach (range(1, 30) as $attempt) {
        post(route('auth.web.registration.store'), $data)
            ->assertRedirectBackWithErrors('password');
    }

    post(route('auth.web.registration.store'), $data)
        ->assertRedirectBackWithoutErrors()
        ->assertInertiaFlash('toast.type', 'warning')
        ->assertInertiaFlash('toast.title', 'Too many attempts');
});
