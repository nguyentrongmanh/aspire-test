<?php

namespace Database\Factories;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Repayment>
 */
class RepaymentFactory extends Factory
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
            'due_date' => fake()->date(),
            'state' => LoanStatus::getRandomValue(),
            'loan_id' => 1,
        ];
    }
}
