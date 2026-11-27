<?php

declare(strict_types=1);

use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

it('redirects guests to login', function (string $routeName) {
    post(route($routeName, Pokemon::factory()->create()))
        ->assertRedirectToRoute('auth.web.login.create');
})->with(['pokemon.web.set-active-move', 'pokemon.web.reorder-active-moves', 'pokemon.web.teach-tm']);

it("gives 404 for another player's pokemon", function (string $routeName) {
    actingAs(User::factory()->create())
        ->post(route($routeName, Pokemon::factory()->create()), ['moveId' => Move::factory()->create()->id, 'slot' => 1, 'moveIds' => [Move::factory()->create()->id]])
        ->assertNotFound();
})->with(['pokemon.web.set-active-move', 'pokemon.web.reorder-active-moves', 'pokemon.web.teach-tm']);

it('puts a known move in an active slot', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->create();
    $tackle  = Move::factory()->create();
    $growl   = Move::factory()->create();

    $pokemon->knownMoves()->attach([$tackle->id => ['slot' => 1], $growl->id => ['slot' => null]]);

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.set-active-move', $pokemon), ['moveId' => $growl->id, 'slot' => 1])
        ->assertRedirect(route('pokemon.web.box.show', $pokemon));

    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $growl->id, 'slot' => 1]);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $tackle->id, 'slot' => null]);
});

it('warns when the move is not known', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.set-active-move', $pokemon), ['moveId' => Move::factory()->create()->id, 'slot' => 1])
        ->assertRedirect(route('pokemon.web.box.show', $pokemon))
        ->assertInertiaFlash('toast.type', 'warning');
});

it('validates the active move', function (Closure $makeData, array $invalidFields) {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.set-active-move', $pokemon), $makeData())
        ->assertSessionHasErrors($invalidFields);
})->with([
    'missing everything' => [fn () => [], ['moveId', 'slot']],
    'unknown move'       => [fn () => ['moveId' => fake()->uuid(), 'slot' => 1], ['moveId']],
    'slot 0'             => [fn () => ['moveId' => Move::factory()->create()->id, 'slot' => 0], ['slot']],
    'slot 5'             => [fn () => ['moveId' => Move::factory()->create()->id, 'slot' => 5], ['slot']],
]);

it('reorders the active moves', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->create();
    $tackle  = Move::factory()->create();
    $ember   = Move::factory()->create();

    $pokemon->knownMoves()->attach([$tackle->id => ['slot' => 1], $ember->id => ['slot' => 2]]);

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.reorder-active-moves', $pokemon), ['moveIds' => [$ember->id, $tackle->id]])
        ->assertRedirect(route('pokemon.web.box.show', $pokemon));

    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $ember->id, 'slot' => 1]);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $tackle->id, 'slot' => 2]);
});

it('validates the new order', function (Closure $makeData) {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.reorder-active-moves', $pokemon), $makeData())
        ->assertSessionHasErrors();
})->with([
    'missing'      => [fn () => []],
    'not an array' => [fn () => ['moveIds' => 'move']],
    'too many'     => [fn () => ['moveIds' => [fake()->uuid(), fake()->uuid(), fake()->uuid(), fake()->uuid(), fake()->uuid()]]],
    'repeated'     => [fn () => ['moveIds' => ['same', 'same']]],
]);

it('teaches a tm and says what was learned', function () {
    $user       = User::factory()->create();
    $charmeleon = Species::factory()->create(['name' => 'Charmeleon']);
    $pokemon    = Pokemon::factory()->for($user)->for($charmeleon)->regular()->create(['level' => 36]);
    $iceBeam    = Move::factory()->plainDamage()->create(['name' => 'Ice Beam']);
    $stack      = InventoryItem::factory()->for($user)->tm($iceBeam)->create(['quantity' => 1]);

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.teach-tm', $pokemon), ['moveId' => $iceBeam->id])
        ->assertRedirect(route('pokemon.web.box.show', $pokemon))
        ->assertInertiaFlash('toast.type', 'success')
        ->assertInertiaFlash('toast.title', 'Move learned')
        ->assertInertiaFlash('toast.message', 'Charmeleon learned Ice Beam.');

    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $iceBeam->id]);

    expect($stack->fresh()->quantity)->toBe(0);
});

it('warns when the tm cannot be taught', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->create(['level' => 50]);
    $zMove   = Move::factory()->plainDamage()->create(['is_z_move' => true]);

    InventoryItem::factory()->for($user)->tm($zMove)->create(['quantity' => 1]);

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.teach-tm', $pokemon), ['moveId' => $zMove->id])
        ->assertRedirect(route('pokemon.web.box.show', $pokemon))
        ->assertInertiaFlash('toast.type', 'warning');
});

it('validates the tm', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.teach-tm', $pokemon), ['moveId' => fake()->uuid()])
        ->assertSessionHasErrors(['moveId']);
});
