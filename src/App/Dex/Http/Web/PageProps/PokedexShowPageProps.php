<?php

declare(strict_types=1);

namespace App\Dex\Http\Web\PageProps;

use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Models\Species;

final readonly class PokedexShowPageProps implements ProvidesInertiaProperties
{
    public function __construct(
        private Species $species
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'species' => fn () => $this->getSpeciesProp(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getSpeciesProp(): array
    {
        return [
            'id'   => $this->species->id,
            'name' => $this->species->name,
        ];
    }
}
