<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Location; // <--- ADDED
use App\Models\User;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    // --- NEW: Helper to assign a location to a test user ---
    public function withLocation()
    {
        return $this->afterCreating(function (User $user) {
            $location = Location::firstOrCreate(['name' => 'Test Studio', 'is_active' => true]);
            $user->locations()->attach($location->id);
        });
    }
}