<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // 1. Ensure all Customer permissions exist in the testing database
    $permissions = [
        'view customers', 'create customers', 'edit customers', 
        'delete customers', 'toggle customer status', 'export customers'
    ];
    
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }
});

// --- VIEW CUSTOMERS ---
it('blocks users without permission from viewing the customer list', function () {
    $user = User::factory()->create(); 
    $this->actingAs($user)->get(route('customers.index'))->assertStatus(403);
});

it('allows users with permission to view the customer list', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view customers');
    $this->actingAs($user)->get(route('customers.index'))->assertStatus(200);
});

// --- CREATE CUSTOMERS ---
it('blocks users without permission from accessing the create customer page', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('customers.create'))->assertStatus(403);
});

it('allows users with permission to access the create customer page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create customers');
    $this->actingAs($user)->get(route('customers.create'))->assertStatus(200);
});