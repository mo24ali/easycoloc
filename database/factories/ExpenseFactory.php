<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Collocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'member_id' => User::factory(),
            'collocation_id' => Collocation::factory(),
            'category_id' => Category::factory(),
            'description' => $this->faker->optional()->sentence(),
            'expense_date' => $this->faker->dateTimeBetween('-6 months')->format('Y-m-d'),
        ];
    }
}
