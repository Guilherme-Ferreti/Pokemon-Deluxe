<?php

declare(strict_types=1);

namespace App\Battle\Mechanics;

use Shared\Enums\ActionBlocker;
use Shared\Enums\Ailment;
use Shared\Helpers\RandomHelper;
use Shared\ValueObjects\Combatant;

final class StatusEffectRoller
{
    public function rollActionBlocker(Combatant $combatant): ?ActionBlocker
    {
        if ($combatant->ailment === Ailment::FREEZE && ! RandomHelper::chance((float) config('game.battle.ailments.freeze_thaw_chance'))) {
            return ActionBlocker::FROZEN;
        }

        if ($combatant->ailment === Ailment::SLEEP && $combatant->ailment_turns_left > 0) {
            return ActionBlocker::ASLEEP;
        }

        if ($combatant->confusion_turns_left > 0 && RandomHelper::chance((float) config('game.battle.ailments.confusion_self_hit_chance'))) {
            return ActionBlocker::HURT_ITSELF_IN_CONFUSION;
        }

        if ($combatant->ailment === Ailment::PARALYSIS && RandomHelper::chance((float) config('game.battle.ailments.paralysis_chance'))) {
            return ActionBlocker::PARALYZED;
        }

        return null;
    }

    public function durationFor(Ailment $ailment): int
    {
        return match ($ailment) {
            Ailment::SLEEP     => RandomHelper::integer((int) config('game.battle.ailments.sleep_min_turns'), (int) config('game.battle.ailments.sleep_max_turns')),
            Ailment::CONFUSION => RandomHelper::integer((int) config('game.battle.ailments.confusion_min_turns'), (int) config('game.battle.ailments.confusion_max_turns')),
            default            => 0,
        };
    }
}
