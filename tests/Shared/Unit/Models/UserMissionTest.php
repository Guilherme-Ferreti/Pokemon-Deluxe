<?php

declare(strict_types=1);

use Shared\Models\UserMission;

it('is claimable only when completed and not claimed', function (?string $completedAt, ?string $claimedAt, bool $isClaimable) {
    $mission = UserMission::factory()->create([
        'completed_at' => $completedAt,
        'claimed_at'   => $claimedAt,
    ]);

    expect($mission->isClaimable())->toBe($isClaimable);
})->with([
    'in progress' => [null, null, false],
    'completed'   => ['2026-11-27 10:00:00', null, true],
    'claimed'     => ['2026-11-27 10:00:00', '2026-11-27 11:00:00', false],
]);
