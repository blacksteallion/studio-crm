<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Location;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'location_id' => Location::firstOrCreate(['name' => 'Factory Studio', 'is_active' => true])->id,
            'customer_id' => Customer::firstOrCreate(['mobile' => '0000000000'], ['name' => 'Factory Customer'])->id,
            'booking_date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'status' => 'Scheduled',
        ];
    }
}