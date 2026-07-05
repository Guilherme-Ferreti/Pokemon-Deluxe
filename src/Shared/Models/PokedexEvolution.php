<?php

declare(strict_types=1);

namespace Shared\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $from_pokedex_id
 * @property string $to_pokedex_id
 * @property string|null $item_id
 * @property int|null $min_level
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Pokedex $from
 * @property-read Pokedex $to
 * @property-read EvolutionItem|null $evolutionItem
 */
final class PokedexEvolution extends Model
{
    use HasUuids;

    /**
     * @return BelongsTo<Pokedex, $this>
     */
    public function from(): BelongsTo
    {
        return $this->belongsTo(Pokedex::class, 'from_pokedex_id');
    }

    /**
     * @return BelongsTo<Pokedex, $this>
     */
    public function to(): BelongsTo
    {
        return $this->belongsTo(Pokedex::class, 'to_pokedex_id');
    }

    /**
     * @return BelongsTo<EvolutionItem, $this>
     */
    public function evolutionItem(): BelongsTo
    {
        return $this->belongsTo(EvolutionItem::class);
    }
}
