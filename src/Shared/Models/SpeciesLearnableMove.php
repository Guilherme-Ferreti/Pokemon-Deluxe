<?php

declare(strict_types=1);

namespace Shared\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Override;

#[Table('species_learnable_moves')]
final class SpeciesLearnableMove extends Pivot
{
    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'level'   => 'integer',
            'is_base' => 'boolean',
        ];
    }
}
