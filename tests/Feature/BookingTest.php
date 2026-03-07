<?php

use App\Models\User;
use App\Models\Location;
use App\Models\Customer;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // 1. Ensure all Booking permissions exist
    $permissions = [
        'view bookings', 'create bookings', 'edit bookings', 
        'delete bookings', 'export bookings', 'view booking calendar'
    ];
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

    // 2. Create base data for our tests
    $this->location = Location::firstOrCreate(['name' => 'Main Test Studio', 'is_active' => true]);
    $this->customer = Customer::firstOrCreate(['mobile' => '9999999999'], ['name' => 'Test Customer', 'email' => 'test@test.com']);
});

// --- 1. AUTHORIZATION TESTS (The "Doors") ---

it('blocks users without permission from viewing the booking list', function () {
    $user = User::factory()->withLocation()->create(); 
    $this->actingAs($user)->get(route('bookings.index'))->assertStatus(403);
});

it('allows users with permission to view the booking list', function () {
    $user = User::factory()->withLocation()->create();
    $user->givePermissionTo('view bookings');
    $this->actingAs($user)->get(route('bookings.index'))->assertStatus(200);
});

it('blocks users without permission from accessing the create booking page', function () {
    $user = User::factory()->withLocation()->create();
    $this->actingAs($user)->get(route('bookings.create'))->assertStatus(403);
});

it('allows users with permission to access the create booking page', function () {
    $user = User::factory()->withLocation()->create();
    $user->givePermissionTo('create bookings');
    $this->actingAs($user)->get(route('bookings.create'))->assertStatus(200);
});

it('blocks users without permission from viewing the booking calendar', function () {
    $user = User::factory()->withLocation()->create();
    $this->actingAs($user)->get(route('bookings.calendar'))->assertStatus(403);
});

it('allows users with permission to view the booking calendar', function () {
    $user = User::factory()->withLocation()->create();
    $user->givePermissionTo('view booking calendar');
    $this->actingAs($user)->get(route('bookings.calendar'))->assertStatus(200);
});

// --- 2. DATA VALIDATION & ISOLATION TESTS (The "Rooms") ---

it('fails to create a booking if location_id is missing', function () {
    $user = User::factory()->withLocation()->create();
    $user->givePermissionTo('create bookings');

    // Attempt to post without the required location_id
    $response = $this->actingAs($user)->post(route('bookings.store'), [
        'customer_id' => $this->customer->id,
        'booking_date' => now()->addDays(2)->format('Y-m-d'),
        'start_time' => '10:00',
        'end_time' => '12:00',
    ]);

    // Assert validation catches it and nothing saves
    $response->assertSessionHasErrors('location_id');
    $this->assertDatabaseCount('bookings', 0);
});

it('successfully creates a booking and saves it to the correct location', function () {
    $user = User::factory()->withLocation()->create();
    $user->givePermissionTo('create bookings');

    $response = $this->actingAs($user)->post(route('bookings.store'), [
        'location_id' => $this->location->id,
        'customer_id' => $this->customer->id,
        'booking_date' => now()->addDays(2)->format('Y-m-d'),
        'start_time' => '10:00',
        'end_time' => '12:00',
        'status' => 'Scheduled',
        // Note: If your controller requires an 'items' array for services, we may need to add it here!
    ]);

    // Prove the booking was saved AND locked to the correct branch
    $this->assertDatabaseHas('bookings', [
        'location_id' => $this->location->id,
        'customer_id' => $this->customer->id,
    ]);
});