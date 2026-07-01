<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasDiscount = $this->faker->boolean(35);
        $discountIsPercentage = $hasDiscount ? $this->faker->boolean(70) : false;

        return [
            'name' => $this->faker->words(2, true),
            'tax_id' => null,
            'is_active' => $this->faker->boolean(85),
            'has_discount' => $hasDiscount,
            'discount_amount' => $hasDiscount
                ? ($discountIsPercentage ? $this->faker->numberBetween(1, 30) : $this->faker->randomFloat(2, 1, 150))
                : null,
            'discount_is_percentage' => $hasDiscount ? $discountIsPercentage : false,
        ];
    }
}
