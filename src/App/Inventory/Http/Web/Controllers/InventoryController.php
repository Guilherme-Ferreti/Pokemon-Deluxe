<?php

declare(strict_types=1);

namespace App\Inventory\Http\Web\Controllers;

use App\Inventory\Http\Web\PageProps\InventoryIndexPageProps;
use Inertia\Response;
use Shared\Base\Controller;

final class InventoryController extends Controller
{
    public function index(): Response
    {
        return inertia('Inventory::Inventory/Index')->with(new InventoryIndexPageProps);
    }
}
