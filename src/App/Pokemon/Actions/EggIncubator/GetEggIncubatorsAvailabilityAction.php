<?php

declare(strict_types=1);

namespace App\Pokemon\Actions\EggIncubator;

use Shared\Models\User;

final class GetEggIncubatorsAvailabilityAction
{
    private User $user;

    /**
     * @return array{all: list<positive-int>, purchased: list<positive-int>, notPurchased: list<positive-int>, active: list<positive-int>, idle: list<positive-int>}
     */
    public function handle(User $user): array
    {
        $this->user = $user;

        /** @var list<positive-int> */
        $all = range(1, config('game.max_egg_incubators_per_user'));

        $purchased = $user->purchased_egg_incubator_indexes;

        $notPurchased = array_diff($all, $purchased);

        $active = $this->getActiveEggIncubatorIndexes();

        $idle = array_diff($user->purchased_egg_incubator_indexes, $active);

        return [
            'all'          => $all,
            'purchased'    => $user->purchased_egg_incubator_indexes,
            'notPurchased' => array_values($notPurchased),
            'active'       => array_values($active),
            'idle'         => array_values($idle),
        ];
    }

    /**
     * @return array<mixed>
     */
    private function getActiveEggIncubatorIndexes(): array
    {
        return $this->user
            ->eggIncubations()
            ->active()
            ->get(['egg_incubator_index'])
            ->pluck('egg_incubator_index')
            ->values()
            ->toArray();
    }
}
