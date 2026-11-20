<?php

declare(strict_types=1);

namespace App\Battle\Mechanics;

use App\Battle\ValueObjects\CatchContext;
use Shared\Models\Pokeball;

final class BallMultiplierCalculator
{
    private const string GUARANTEED_BALL_SLUG = 'master-ball';

    public function multiplier(Pokeball $pokeball, CatchContext $context): float
    {
        return $pokeball->catch_rate;
    }

    public function isGuaranteed(Pokeball $pokeball): bool
    {
        return $pokeball->slug === self::GUARANTEED_BALL_SLUG;
    }
}
