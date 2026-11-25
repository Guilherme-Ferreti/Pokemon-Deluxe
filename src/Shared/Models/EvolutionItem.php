<?php

declare(strict_types=1);

namespace Shared\Models;

use Database\Factories\EvolutionItemFactory;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property string $description
 * @property string|null $sprite
 * @property int|null $price
 */
#[WithoutTimestamps]
final class EvolutionItem extends Model
{
    /** @use HasFactory<EvolutionItemFactory> */
    use HasFactory, HasUuids;

    #[Override]
    protected function casts(): array
    {
        return [
            'price' => 'integer',
        ];
    }
}
