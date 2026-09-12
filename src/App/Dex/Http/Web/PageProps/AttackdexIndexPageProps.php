<?php

declare(strict_types=1);

namespace App\Dex\Http\Web\PageProps;

use App\Dex\Filters\SearchAttackdexFilters;
use App\Dex\Queries\SearchAttackdexQuery;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Helpers\AppHelper;
use Shared\Models\Move;

final class AttackdexIndexPageProps implements ProvidesInertiaProperties
{
    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        $searchAttackdexFilters = SearchAttackdexFilters::fromRequest($context->request);

        return [
            'moves'         => fn () => $this->getMovesProp($searchAttackdexFilters),
            'activeFilters' => fn () => $searchAttackdexFilters,
            'filterOptions' => fn () => SearchAttackdexFilters::filterOptions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getMovesProp(SearchAttackdexFilters $searchAttackdexFilters): array
    {
        $moves = new SearchAttackdexQuery()->handle($searchAttackdexFilters)->paginate(50)->withQueryString();

        return AppHelper::paginatorToArray($moves, fn (Move $move) => [
            'id'            => $move->id,
            'name'          => $move->name,
            'type'          => $move->type,
            'category'      => $move->category,
            'power'         => $move->power,
            'accuracy'      => $move->accuracy,
            'ailment'       => $move->ailment,
            'ailmentChance' => $move->ailment_chance,
            'flinchChance'  => $move->flinch_chance,
            'isZmove'       => $move->is_zmove,
            'isDynamax'     => $move->is_dynamax,
        ]);
    }
}
