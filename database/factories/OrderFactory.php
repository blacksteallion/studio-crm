<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Location;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'location_id' => Location::firstOrCreate(['name' => 'Factory Studio', 'is_active' => true])->id,
            'customer_id' => Customer::firstOrCreate(['mobile' => '0000000000'], ['name' => 'Factory Customer'])->id,
            'staff_id' => User::factory(),
            'order_number' => 'ORD-' . $this->faker->unique()->numberBetween(1000, 9999),
            'booking_date' => now()->format('Y-m-d'), // <--- ADDED: Satisfies DB constraint
            'total_amount' => 5000,
            'status' => 'Unpaid',
        ];
    }
}