<?php

declare(strict_types=1);

namespace Shared\Models;

use Database\Factories\SpecialItemFactory;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;
use Shared\Enums\SpecialItemCategory;

/**
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property string $description
 * @property string|null $sprite
 * @property SpecialItemCategory $category
 */
#[WithoutTimestamps]
final class SpecialItem extends Model
{
    /** @use HasFactory<SpecialItemFactory> */
    use HasFactory, HasUuids;

    #[Override]
    protected function casts(): array
    {
        return [
            'category' => SpecialItemCategory::class,
        ];
    }
}
