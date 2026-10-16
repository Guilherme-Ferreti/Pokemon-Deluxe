<?php

declare(strict_types=1);

namespace App\Pokemon\Actions\EggIncubator;

use Shared\Helpers\AppHelper;
use Shared\Models\User;
use Shared\ValueObjects\EggIncubatorIndex;

final class GetEggIncubatorsAvailabilityAction
{
    private User $user;

    /**
     * @return array{all: list<EggIncubatorIndex>, purchased: list<EggIncubatorIndex>, notPurchased: list<EggIncubatorIndex>, active: list<EggIncubatorIndex>, idle: list<EggIncubatorIndex>}
     */
    public function handle(User $user): array
    {
        $this->user = $user;

        $all = AppHelper::allEggIncubatorIndexes();

        $purchased = $user->purchased_egg_incubator_indexes;

        $notPurchased = value_object_array_diff($all, $purchased);

        $active = $this->getActiveEggIncubatorIndexes();

        $idle = value_object_array_diff($purchased, $active);

        return [
            'all'          => $all,
            'purchased'    => $purchased,
            'notPurchased' => array_values($notPurchased),
            'active'       => $active,
            'idle'         => array_values($idle),
        ];
    }

    /**
     * @return list<EggIncubatorIndex>
     */
    private function getActiveEggIncubatorIndexes(): array
    {
        /** @var list<EggIncubatorIndex> */
        $indexes = $this->user
            ->eggIncubations()
            ->active()
            ->pluck('egg_incubator_index')
            ->values()
            ->all();

        return $indexes;
    }
}
