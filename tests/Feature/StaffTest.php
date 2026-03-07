<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // 1. Ensure all Staff permissions exist in the testing database
    $permissions = [
        'view staff', 'create staff', 'edit staff', 
        'delete staff', 'toggle staff status', 'export staff'
    ];
    
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }
});

// --- VIEW STAFF ---
it('blocks users without permission from viewing the staff list', function () {
    $user = User::factory()->create(); // User with NO permissions
    $this->actingAs($user)->get(route('staff.index'))->assertStatus(403);
});

it('allows users with permission to view the staff list', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view staff');
    $this->actingAs($user)->get(route('staff.index'))->assertStatus(200);
});

// --- CREATE STAFF ---
it('blocks users without permission from accessing the create staff page', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('staff.create'))->assertStatus(403);
});

it('allows users with permission to access the create staff page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create staff');
    $this->actingAs($user)->get(route('staff.create'))->assertStatus(200);
});