<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Homeowner;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $cardBrands = ['Visa', 'MasterCard', 'Amex', 'Discover'];

        return [
            'homeowner_id' => Homeowner::factory(), // Automatically creates a homeowner if not provided
            'payment_method_id' => $this->faker->uuid(),
            'amount' => $this->faker->randomFloat(2, 10, 1000), // Amount between 10 and 1000
            'currency' => $this->faker->randomElement(['usd', 'eur', 'gbp']),
            'status' => $this->faker->randomElement(['pending', 'succeeded', 'failed']),
            'card_brand' => Crypt::encryptString($this->faker->randomElement(['Visa', 'MasterCard', 'Amex', 'Discover'])),
            'card_last4number' => Crypt::encryptString($this->faker->numerify('####')),
            'exp_month' => $this->faker->numberBetween(1, 12),
            'exp_year' => $this->faker->numberBetween(date('Y'), date('Y') + 5),
        ];
    }
}
