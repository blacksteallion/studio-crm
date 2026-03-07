<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

// Set up the database before each test
beforeEach(function () {
    // Ensure the role exists in our in-memory database
    Role::firstOrCreate(['name' => 'Super Admin']);
});

it('renders the login page successfully', function () {
    $this->get('/login')
        ->assertStatus(200)
        ->assertSee('Welcome Back!');
});

it('allows an active user to log in with correct credentials', function () {
    $user = User::factory()->create([
        'email' => 'admin@techcelerity.in',
        'password' => Hash::make('CorrectPassword123'),
        'status' => 1,
    ]);
    
    $user->assignRole('Super Admin');

    $this->post('/login', [
        'email' => 'admin@techcelerity.in',
        'password' => 'CorrectPassword123',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);
});

it('rejects login attempts with invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'admin@techcelerity.in',
        'password' => Hash::make('CorrectPassword123'),
        'status' => 1,
    ]);

    $this->post('/login', [
        'email' => 'admin@techcelerity.in',
        'password' => 'WrongPassword!',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('prevents inactive users from logging in', function () {
    $user = User::factory()->create([
        'email' => 'fired_staff@techcelerity.in',
        'password' => Hash::make('Password123'),
        'status' => 0, // Inactive
    ]);

    $this->post('/login', [
        'email' => 'fired_staff@techcelerity.in',
        'password' => 'Password123',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});