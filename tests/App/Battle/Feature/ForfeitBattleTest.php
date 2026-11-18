<?php

declare(strict_types=1);

use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Models\Battle;
use Shared\Models\User;
use Shared\ValueObjects\BattleLogEntry;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('redirects guests to login', function () {
    post(route('battle.web.forfeit'))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('runs from the ongoing battle', function () {
    $user   = User::factory()->create();
    $battle = Battle::factory()->for($user)->create(['status' => BattleStatus::ONGOING]);

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.forfeit'))
        ->assertRedirect(route('battle.web.show'));

    expect($battle->fresh())
        ->status->toBe(BattleStatus::FORFEITED)
        ->state->log->toEqual([new BattleLogEntry(1, BattleLogKind::INFO, 'You got away safely!')]);
});

it('warns when there is no battle in progress', function () {
    $user             = User::factory()->create();
    $otherUsersBattle = Battle::factory()->create(['status' => BattleStatus::ONGOING]);

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.forfeit'))
        ->assertRedirect(route('battle.web.show'))
        ->assertInertiaFlash('toast.type', 'warning');

    expect($otherUsersBattle->fresh()?->status)->toBe(BattleStatus::ONGOING);
});
