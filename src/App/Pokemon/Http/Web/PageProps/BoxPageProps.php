<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\PageProps;

use App\Pokemon\Filters\SearchBoxFilters;
use Closure;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Helpers\AppHelper;
use Shared\Models\Pokemon;

final readonly class BoxPageProps implements ProvidesInertiaProperties
{
    public function __construct(
        public Closure $box,

        public SearchBoxFilters $activeFilters,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'box'           => fn () => $this->mapBox(),
            'activeFilters' => fn () => $this->activeFilters,
            'filterOptions' => fn () => SearchBoxFilters::filterOptions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapBox(): array
    {
        return AppHelper::paginatorToArray(($this->box)(), fn (Pokemon $pokemon) => [
            'id'          => $pokemon->id,
            'name'        => $pokemon->getName(),
            'frontSprite' => $pokemon->getFrontSprite(),
            'level'       => $pokemon->level,
            'experience'  => $pokemon->experience,
            'pokedex'     => [
                'types' => $pokemon->pokedex->types,
            ],
            'moves' => [
                [
                    'name' => 'Giga Impact',
                ],
                [
                    'name' => 'Flamethrower',
                ],
                [
                    'name' => 'Earthquake',
                ],
                [
                    'name' => 'Ice Beam',
                ],
            ],
        ]);

    }
}
