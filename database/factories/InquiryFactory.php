<?php

namespace Database\Factories;

use App\Models\Inquiry;
use App\Models\Location;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class InquiryFactory extends Factory
{
    protected $model = Inquiry::class;

    public function definition(): array
    {
        return [
            // If a location doesn't exist, create a dummy one for the test
            'location_id' => Location::firstOrCreate(['name' => 'Factory Studio', 'is_active' => true])->id,
            
            // If a customer doesn't exist, create a dummy one for the test
            'customer_id' => Customer::firstOrCreate(['mobile' => '0000000000'], ['name' => 'Factory Customer'])->id,
            
            'business_name' => $this->faker->company(),
            'primary_date' => now()->addDays(2)->format('Y-m-d'),
            'from_time' => '10:00',
            'to_time' => '12:00',
            'total_hours' => 2,
            'status' => 'New',
        ];
    }
}