<?php

declare(strict_types=1);

namespace App\Battle\Mechanics;

use App\Battle\ValueObjects\MoveHit;
use App\Battle\ValueObjects\MoveOutcome;
use Shared\Enums\Ailment;
use Shared\Helpers\RandomHelper;
use Shared\Models\Move;

final class MoveOutcomeRoller
{
    public function roll(Move $move): MoveOutcome
    {
        if (! RandomHelper::chance($move->accuracy)) {
            return MoveOutcome::miss();
        }

        return new MoveOutcome(
            hasHit: true,
            hits: $this->rollHits($move),
            causesFlinch: $this->rollFlinch($move),
            inflictedAilment: $this->rollAilment($move),
        );
    }

    /**
     * @return list<MoveHit>
     */
    private function rollHits(Move $move): array
    {
        if ($move->power === null) {
            return [];
        }

        $hitCount = $this->rollHitCount($move);
        $hits     = [];

        for ($hitNumber = 1; $hitNumber <= $hitCount; $hitNumber++) {
            $hits[] = new MoveHit(
                isCriticalHit: RandomHelper::chance((float) config('game.battle.critical_hit_chance')),
                randomFactor: RandomHelper::float((float) config('game.battle.damage_random_min'), 1.0),
            );
        }

        return $hits;
    }

    private function rollHitCount(Move $move): int
    {
        $minHits = $move->min_hits ?? 1;
        $maxHits = $move->max_hits ?? $minHits;

        return $minHits === $maxHits ? $minHits : RandomHelper::integer($minHits, $maxHits);
    }

    private function rollFlinch(Move $move): bool
    {
        return $move->flinch_chance !== null && RandomHelper::chance($move->flinch_chance);
    }

    private function rollAilment(Move $move): ?Ailment
    {
        if ($move->ailment === null || ! RandomHelper::chance($move->ailment_chance ?? 100)) {
            return null;
        }

        if ($move->ailment === Ailment::UNKNOWN) {
            return RandomHelper::pick([Ailment::BURN, Ailment::PARALYSIS, Ailment::FREEZE]);
        }

        return $move->ailment;
    }
}
