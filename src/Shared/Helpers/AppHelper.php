<?php

declare(strict_types=1);

namespace Shared\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class AppHelper
{
    /**
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     * @return array<string, string|int|Collection<(int|string), mixed>|mixed[]|null>
     */
    public static function paginatorToArray(LengthAwarePaginator $paginator, ?callable $mapItems = null): array
    {
        return [
            'currentPage'  => $paginator->currentPage(),
            'firstPageUrl' => $paginator->url(1),
            'from'         => $paginator->firstItem(),
            'lastPage'     => $paginator->lastPage(),
            'lastPageUrl'  => $paginator->url($paginator->lastPage()),
            'links'        => $paginator->linkCollection()->toArray(),
            'nextPageUrl'  => $paginator->nextPageUrl(),
            'path'         => $paginator->path(),
            'perPage'      => $paginator->perPage(),
            'prevPageUrl'  => $paginator->previousPageUrl(),
            'to'           => $paginator->lastItem(),
            'total'        => $paginator->total(),
            'data'         => $mapItems
                ? $paginator->getCollection()->map($mapItems)->toArray()
                : $paginator->getCollection()->toArray(),
        ];
    }
}
