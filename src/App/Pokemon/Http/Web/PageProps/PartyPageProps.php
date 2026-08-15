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
            'party' => fn () => $this->getPartyProp(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getPartyProp(): array
    {
        return currentUser()
            ->pokemon()
            ->inParty()
            ->get()
            ->map(fn (Pokemon $pokemon) => [
                'id'          => $pokemon->id,
                'name'        => $pokemon->getName(),
                'frontSprite' => $pokemon->getFrontSprite(),
                'level'       => $pokemon->level,
            ])
            ->toArray();
    }
}
