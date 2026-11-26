<?php

declare(strict_types=1);

use Shared\Helpers\RandomHelper;
use Shared\Models\InventoryItem;
use Shared\Models\Pokemon;
use Shared\Models\SpecialItem;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('redirects guests to login', function () {
    post(route('pokemon.web.boost', Pokemon::factory()->create()), ['max_stones' => 1])
        ->assertRedirectToRoute('auth.web.login.create');
});

it("gives 404 for another player's pokemon", function () {
    actingAs(User::factory()->create())
        ->post(route('pokemon.web.boost', Pokemon::factory()->create()), ['max_stones' => 1])
        ->assertNotFound();
});

it('uses boost stones and flashes the result', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->boosted(14)->create();
    $stack   = InventoryItem::factory()->for($user)->specialItem(SpecialItem::factory()->boostStone()->create())->create(['quantity' => 5]);

    RandomHelper::fake([true, false, true]);

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.boost', $pokemon), ['max_stones' => 3])
        ->assertRedirect(route('pokemon.web.box.show', $pokemon))
        ->assertInertiaFlash('boostResult', [
            'stonesUsed'  => 3,
            'successes'   => 2,
            'failures'    => 1,
            'boostBefore' => 14,
            'boostAfter'  => 16,
        ]);

    expect($pokemon->fresh()->boost)->toBe(16)
        ->and($stack->fresh()->quantity)->toBe(2);
});

it('stops at the target boost', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->boosted(10)->create();

    InventoryItem::factory()->for($user)->specialItem(SpecialItem::factory()->boostStone()->create())->create(['quantity' => 50]);

    RandomHelper::fake([true, true]);

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.boost', $pokemon), ['max_stones' => 50, 'target_boost' => 12])
        ->assertInertiaFlash('boostResult.stonesUsed', 2)
        ->assertInertiaFlash('boostResult.boostAfter', 12);
});

it('warns when the player owns no boost stones', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->create();

    SpecialItem::factory()->boostStone()->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.boost', $pokemon), ['max_stones' => 1])
        ->assertRedirect(route('pokemon.web.box.show', $pokemon))
        ->assertInertiaFlash('toast.type', 'warning')
        ->assertInertiaFlashMissing('boostResult');
});

it('warns when the pokemon is at the max boost', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->boosted(100)->create();

    InventoryItem::factory()->for($user)->specialItem(SpecialItem::factory()->boostStone()->create())->create(['quantity' => 5]);

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.boost', $pokemon), ['max_stones' => 1])
        ->assertInertiaFlash('toast.type', 'warning');
});

it('validates the request', function (array $data, array $invalidFields) {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.boost', $pokemon), $data)
        ->assertSessionHasErrors($invalidFields);
})->with([
    'missing max stones'   => [[], ['max_stones']],
    'zero stones'          => [['max_stones' => 0], ['max_stones']],
    'target boost 0'       => [['max_stones' => 1, 'target_boost' => 0], ['target_boost']],
    'target boost too big' => [['max_stones' => 1, 'target_boost' => 101], ['target_boost']],
]);
