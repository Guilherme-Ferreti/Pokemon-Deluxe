<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\PageProps;

use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Models\Pokemon;

final class PartyPageProps implements ProvidesInertiaProperties
{
    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'party'           => $this->getPartyProp(...),
            'isBattleOngoing' => fn () => currentUser()->battles()->ongoing()->exists(),
            'maxPartySize'    => (int) config('game.user.max_party_size'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getPartyProp(): array
    {
        return array_values(currentUser()
            ->pokemon()
            ->inPartyOrder()
            ->with('species')
            ->get()
            ->map(fn (Pokemon $pokemon) => [
                'id'       => $pokemon->id,
                'name'     => $pokemon->getDisplayName(),
                'gender'   => $pokemon->gender,
                'portrait' => $pokemon->getDisplayPortrait(),
                'level'    => $pokemon->level,
                'boost'    => $pokemon->boost,
                'types'    => $pokemon->species->types,
            ])
            ->all());
    }
}
