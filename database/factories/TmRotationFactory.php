<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Override;
use Shared\Models\Move;
use Shared\Models\TmRotation;

/**
 * @extends Factory<TmRotation>
 */
final class TmRotationFactory extends Factory
{
    #[Override]
    protected $model = TmRotation::class;

    public function definition(): array
    {
        return [
            'date'     => today(),
            'move_ids' => fn () => Move::factory()->count(3)->create()->modelKeys(),
        ];
    }
}
