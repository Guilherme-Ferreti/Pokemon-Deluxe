<?php

declare(strict_types=1);

namespace Shared\Models;

use Carbon\CarbonImmutable;
use Database\Factories\TmRotationFactory;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string $id
 * @property CarbonImmutable $date
 * @property list<string> $move_ids
 */
#[Unguarded]
#[WithoutTimestamps]
final class TmRotation extends Model
{
    /** @use HasFactory<TmRotationFactory> */
    use HasFactory, HasUuids;

    #[Override]
    protected function casts(): array
    {
        return [
            'date'     => 'immutable_date',
            'move_ids' => 'array',
        ];
    }
}
