<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasDiscount = $this->faker->boolean(30);
        $discountIsPercentage = $hasDiscount ? $this->faker->boolean(70) : false;

        return [
            'name' => $this->faker->words(2, true),
            'sku' => strtoupper($this->faker->bothify('SKU-####')),
            'description' => $this->faker->sentence(10),
            'unit' => $this->faker->randomElement(['pcs', 'hour', 'kg']),
            'category_id' => Category::factory(),
            'tax_id' => $this->faker->boolean(40) ? Tax::factory() : null,
            'unit_price' => $this->faker->randomFloat(2, 5, 500),
            'cost_price' => $this->faker->randomFloat(2, 2, 400),
            'has_discount' => $hasDiscount,
            'discount_amount' => $hasDiscount
                ? ($discountIsPercentage ? $this->faker->numberBetween(1, 30) : $this->faker->randomFloat(2, 1, 150))
                : null,
            'discount_is_percentage' => $hasDiscount ? $discountIsPercentage : false,
            'is_active' => $this->faker->boolean(85),
        ];
    }
}
