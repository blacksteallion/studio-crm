<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Run the Roles and Permissions Seeder first
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Create the Test User
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 3. Assign the Super Admin role to the Test User so you have full access
        $user->assignRole('Super Admin');
    }
}