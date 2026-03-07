<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'location_id' => Location::firstOrCreate(['name' => 'Factory Studio', 'is_active' => true])->id,
            'title' => 'Test Office Supplies',
            'amount' => 500,
            'category' => 'Operational',
            'expense_date' => now()->format('Y-m-d'),
        ];
    }
}