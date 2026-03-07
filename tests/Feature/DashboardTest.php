<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // 1. Create the permission and role
    $permission = Permission::firstOrCreate(['name' => 'view dashboard']);
    $role = Role::firstOrCreate(['name' => 'Super Admin']);
    
    // 2. Attach permission to role
    $role->givePermissionTo($permission);
});

it('redirects unauthenticated users away from the dashboard', function () {
    // Guest tries to access dashboard, expecting them to be blocked and redirected to login
    $this->get('/dashboard')->assertRedirect('/login');
});

it('loads the dashboard for authenticated users with correct permissions', function () {
    // 1. Create a fake user and give them the Super Admin role
    $user = User::factory()->create(['status' => 1]);
    $user->assignRole('Super Admin');

    // 2. Act as that user and request the dashboard
    $this->actingAs($user)
         ->get('/dashboard')
         ->assertStatus(200)
         ->assertSee('Dashboard'); // Checks if the word Dashboard is rendered on the screen
});