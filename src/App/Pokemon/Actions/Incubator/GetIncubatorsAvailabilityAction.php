<?php

declare(strict_types=1);

namespace App\Pokemon\Actions\Incubator;

use Shared\Helpers\AppHelper;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

final class GetIncubatorsAvailabilityAction
{
    private User $user;

    /**
     * @return array{all: list<IncubatorIndex>, purchased: list<IncubatorIndex>, notPurchased: list<IncubatorIndex>, active: list<IncubatorIndex>, idle: list<IncubatorIndex>}
     */
    public function handle(User $user): array
    {
        $this->user = $user;

        $all = AppHelper::allIncubatorIndexes();

        $purchased = $user->purchased_incubator_indexes;

        $notPurchased = value_object_array_diff($all, $purchased);

        $active = $this->getActiveIncubatorIndexes();

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
     * @return list<IncubatorIndex>
     */
    private function getActiveIncubatorIndexes(): array
    {
        /** @var list<IncubatorIndex> */
        $indexes = $this->user
            ->Incubations()
            ->active()
            ->pluck('incubator_index')
            ->values()
            ->all();

        return $indexes;
    }
}
