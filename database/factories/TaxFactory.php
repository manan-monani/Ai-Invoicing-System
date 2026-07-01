<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tax>
 */
class TaxFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['percentage', 'fixed']);

        return [
            'name' => ucfirst($this->faker->unique()->word()).' Tax',
            'rate' => $type === 'percentage'
                ? $this->faker->randomFloat(2, 0, 20)
                : $this->faker->randomFloat(2, 0, 50),
            'type' => $type,
            'is_active' => $this->faker->boolean(85),
        ];
    }
}
