<?php

use App\Models\User;
use App\Models\Location;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // Ensure all Inquiry permissions exist in the testing database
    $permissions = [
        'view inquiries', 'create inquiries', 'edit inquiries', 
        'delete inquiries', 'export inquiries', 'convert inquiries', 'manage inquiry logs'
    ];
    
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

    // Create a base location for our test environment
    $this->location = Location::firstOrCreate(['name' => 'Main Test Studio', 'is_active' => true]);
});

// --- 1. AUTHORIZATION TESTS (The "Doors") ---

it('blocks users without permission from viewing the inquiry list', function () {
    $user = User::factory()->withLocation()->create(); 
    $this->actingAs($user)->get(route('inquiries.index'))->assertStatus(403);
});

it('allows users with permission to view the inquiry list', function () {
    $user = User::factory()->withLocation()->create();
    $user->givePermissionTo('view inquiries');
    
    $this->actingAs($user)->get(route('inquiries.index'))->assertStatus(200);
});

it('blocks users without permission from accessing the create inquiry page', function () {
    $user = User::factory()->withLocation()->create();
    $this->actingAs($user)->get(route('inquiries.create'))->assertStatus(403);
});

it('allows users with permission to access the create inquiry page', function () {
    $user = User::factory()->withLocation()->create();
    $user->givePermissionTo('create inquiries');
    
    $this->actingAs($user)->get(route('inquiries.create'))->assertStatus(200);
});

// --- 2. DATA VALIDATION & ISOLATION TESTS (The "Rooms") ---

it('fails to create an inquiry if location_id is missing', function () {
    $user = User::factory()->withLocation()->create();
    $user->givePermissionTo('create inquiries');

    // Attempt to post without the required location_id
    $response = $this->actingAs($user)->post(route('inquiries.store'), [
        'name' => 'John Doe',
        'mobile' => '9999999999',
        'primary_date' => now()->addDays(1)->format('Y-m-d'),
        'from_time' => '10:00',
        'to_time' => '12:00',
    ]);

    // Assert it throws a validation error for location_id
    $response->assertSessionHasErrors('location_id');
    
    // Assert nothing was saved to the database
    $this->assertDatabaseCount('inquiries', 0);
});

it('successfully creates an inquiry and saves it securely to the correct location', function () {
    $user = User::factory()->withLocation()->create();
    $user->givePermissionTo('create inquiries');

    $payload = [
        'location_id' => $this->location->id, // Passing the secure location
        'name' => 'Jane Smith',
        'mobile' => '8888888888',
        'email' => 'jane@example.com',
        'business_name' => 'Jane Corp',
        'primary_date' => now()->addDays(3)->format('Y-m-d'),
        'from_time' => '14:00',
        'to_time' => '16:00',
    ];

    $response = $this->actingAs($user)->post(route('inquiries.store'), $payload);

    // Assert it successfully redirects back to the index page (No validation errors)
    $response->assertRedirect(route('inquiries.index'));
    $response->assertSessionHas('success');

    // Prove the customer was created dynamically
    $this->assertDatabaseHas('customers', [
        'mobile' => '8888888888',
        'name' => 'Jane Smith',
    ]);

    // Prove the inquiry was saved AND locked to the correct branch
    $this->assertDatabaseHas('inquiries', [
        'location_id' => $this->location->id,
        'business_name' => 'Jane Corp',
        'from_time' => '14:00',
        'to_time' => '16:00',
        'total_hours' => 2, // Proving our controller calculation works
    ]);
});