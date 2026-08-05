<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Filters\SearchBoxFilters;
use App\Pokemon\Http\Web\PageProps\BoxPageProps;
use App\Pokemon\Queries\SearchBoxQuery;
use Illuminate\Http\Request;
use Inertia\Response;
use Shared\Base\Controller;

final class BoxController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $searchBoxFilters = SearchBoxFilters::fromRequest($request);

        $box = fn () => new SearchBoxQuery()->handle(currentUser(), $searchBoxFilters)->paginate(12)->withQueryString();

        return inertia('Pokemon/Box')->with(new BoxPageProps(
            box: $box,
            activeFilters: $searchBoxFilters,
        ));
    }
}
