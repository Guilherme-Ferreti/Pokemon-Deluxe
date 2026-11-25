<?php

declare(strict_types=1);

namespace Shared\Models;

use Carbon\CarbonImmutable;
use Database\Factories\InventoryItemFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Override;
use Shared\Enums\InventoryItemType;

/**
 * @property string $id
 * @property string $user_id
 * @property InventoryItemType $item_type
 * @property string $item_id
 * @property int $quantity
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $user
 * @property-read EvolutionItem|Pokeball|Move|SpecialItem $item
 */
#[Unguarded]
final class InventoryItem extends Model
{
    /** @use HasFactory<InventoryItemFactory> */
    use HasFactory, HasUuids;

    #[Override]
    protected function casts(): array
    {
        return [
            'item_type' => InventoryItemType::class,
            'quantity'  => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function item(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function forItem(Builder $query, EvolutionItem|Pokeball|Move|SpecialItem $item): void
    {
        $query
            ->where('item_id', $item->getKey())
            ->where('item_type', InventoryItemType::fromModel($item));
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function pokeballs(Builder $query): void
    {
        $query->where('item_type', InventoryItemType::POKEBALL);
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function owned(Builder $query): void
    {
        $query->where('quantity', '>', 0);
    }
}
