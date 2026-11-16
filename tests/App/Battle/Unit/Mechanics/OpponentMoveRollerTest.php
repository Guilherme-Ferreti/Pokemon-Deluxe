<?php

declare(strict_types=1);

use App\Battle\Mechanics\OpponentMoveRoller;
use Shared\Helpers\RandomHelper;

it('picks one of the usable moves', function () {
    RandomHelper::fake(['move-2']);

    expect(new OpponentMoveRoller()->rollMoveId(['move-1', 'move-2']))->toBe('move-2');
});
