<?php

use App\Models\User;
use App\Models\Location;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $permissions = ['view expenses', 'create expenses', 'edit expenses', 'delete expenses', 'export expenses'];
    foreach ($permissions as $permission) { 
        Permission::firstOrCreate(['name' => $permission]); 
    }

    $this->location = Location::firstOrCreate(['name' => 'Main Test Studio', 'is_active' => true]);
});

// --- 1. AUTHORIZATION TESTS (The "Doors") ---

it('blocks users without permission from viewing the expenses list', function () {
    $user = User::factory()->withLocation()->create(); 
    $this->actingAs($user)->get(route('expenses.index'))->assertStatus(403);
});

it('allows users with permission to view the expenses list', function () {
    $user = User::factory()->withLocation()->create();
    $user->givePermissionTo('view expenses');
    $this->actingAs($user)->get(route('expenses.index'))->assertStatus(200);
});

// --- 2. DATA VALIDATION & ISOLATION TESTS (The "Rooms") ---

it('fails to record an expense if location_id is missing', function () {
    $user = User::factory()->withLocation()->create();
    $user->givePermissionTo('create expenses');

    // Attempt to post without the required location_id
    $response = $this->actingAs($user)->post(route('expenses.store'), [
        'title' => 'Electricity Bill',
        'amount' => 1500,
        'category' => 'Operational',
        'expense_date' => now()->format('Y-m-d'),
    ]);

    // Assert validation catches it and nothing saves
    $response->assertSessionHasErrors('location_id');
    $this->assertDatabaseCount('expenses', 0);
});

it('successfully records an expense and locks it to the correct location', function () {
    $user = User::factory()->withLocation()->create();
    $user->givePermissionTo('create expenses');

    $response = $this->actingAs($user)->post(route('expenses.store'), [
        'location_id' => $this->location->id,
        'title' => 'Electricity Bill',
        'amount' => 1500,
        'category' => 'Operational',
        'expense_date' => now()->format('Y-m-d'),
    ]);

    // Prove the expense was saved AND locked to the correct branch
    $this->assertDatabaseHas('expenses', [
        'location_id' => $this->location->id,
        'title' => 'Electricity Bill',
        'amount' => 1500,
    ]);
});