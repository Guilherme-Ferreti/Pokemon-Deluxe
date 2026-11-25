<?php

declare(strict_types=1);

namespace App\Shop\Http\Web\Controllers;

use App\Shop\Actions\BuyItemAction;
use App\Shop\Http\Web\Requests\BuyItemRequest;
use Illuminate\Http\RedirectResponse;
use LogicException;
use Shared\Base\Controller;
use Shared\Enums\InventoryItemType;
use Shared\Helpers\ItemHelper;
use Shared\Helpers\ToastHelper;
use Shared\Models\EvolutionItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;

final class BuyItemController extends Controller
{
    public function __invoke(BuyItemRequest $request): RedirectResponse
    {
        $itemId   = $request->string('item_id')->toString();
        $quantity = $request->integer('quantity');

        $item = match ($request->enum('item_type', InventoryItemType::class)) {
            InventoryItemType::POKEBALL       => Pokeball::query()->findOrFail($itemId),
            InventoryItemType::EVOLUTION_ITEM => EvolutionItem::query()->findOrFail($itemId),
            InventoryItemType::TM             => Move::query()->findOrFail($itemId),
            default                           => throw new LogicException('Only validated, buyable item types reach the controller.'),
        };

        new BuyItemAction()->handle(currentUser(), $item, $quantity);

        ToastHelper::success('Purchase complete', sprintf('You bought %d %s.', $quantity, ItemHelper::name($item, $quantity)));

        return back();
    }
}
