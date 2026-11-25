<?php

declare(strict_types=1);

namespace App\Shop\Http\Web\Controllers;

use App\Shop\Http\Web\PageProps\ShopIndexPageProps;
use Inertia\Response;
use Shared\Base\Controller;

final class ShopController extends Controller
{
    public function index(): Response
    {
        return inertia('Shop::Shop/Index')->with(new ShopIndexPageProps);
    }
}
