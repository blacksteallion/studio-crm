<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'amount' => 1000,
            'transaction_date' => now()->format('Y-m-d'), // <--- FIXED
            'payment_method' => 'Cash',
        ];
    }
}