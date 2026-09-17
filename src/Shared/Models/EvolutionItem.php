<?php

declare(strict_types=1);

namespace Shared\Models;

use Database\Factories\EvolutionItemFactory;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property string $description
 * @property string|null $sprite
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[WithoutTimestamps]
final class EvolutionItem extends Model
{
    /** @use HasFactory<EvolutionItemFactory> */
    use HasFactory, HasUuids;
}
