<?php

namespace Database\Factories;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Loan>
 */
class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'amount' => fake()->numberBetween($min = 1000, $max = 9000),
            'term' => fake()->numberBetween($min = 2, $max = 5),
            'submitted_date' => fake()->date(),
            'state' => LoanStatus::getRandomValue(),
            'user_id' => 1,
        ];
    }
}
