<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Override;
use Shared\Enums\SpecialItemCategory;
use Shared\Models\SpecialItem;

/**
 * @extends Factory<SpecialItem>
 */
final class SpecialItemFactory extends Factory
{
    #[Override]
    protected $model = SpecialItem::class;

    public function definition(): array
    {
        return [
            'slug'        => fake()->unique()->uuid(),
            'name'        => fake()->unique()->word(),
            'description' => fake()->sentence(),
            'sprite'      => fake()->optional()->imageUrl(),
            'category'    => fake()->randomElement(SpecialItemCategory::cases()),
        ];
    }

    public function boostStone(): static
    {
        return $this->state([
            'slug'     => 'boost-stone',
            'name'     => 'Boost Stone',
            'category' => SpecialItemCategory::BOOST_STONE,
        ]);
    }

    public function xpShare(): static
    {
        return $this->state([
            'slug'     => 'xp-share',
            'name'     => 'XP Share',
            'category' => SpecialItemCategory::KEY_ITEM,
        ]);
    }
}
