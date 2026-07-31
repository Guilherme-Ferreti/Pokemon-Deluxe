<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\PageProps;

use Closure;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Models\Pokemon;

final class PartyPageProps implements ProvidesInertiaProperties
{
    public function __construct(
        public Closure $party,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'party' => fn () => $this->mapParty(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapParty(): array
    {
        return ($this->party)()
            ->map(fn (Pokemon $pokemon) => [
                'id'          => $pokemon->id,
                'name'        => $pokemon->getName(),
                'frontSprite' => $pokemon->getFrontSprite(),
                'level'       => $pokemon->level,
            ])
            ->toArray();
    }
}
