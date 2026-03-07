<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $permissions = ['manage integrations', 'manage roles', 'manage settings'];
    foreach ($permissions as $permission) { Permission::firstOrCreate(['name' => $permission]); }
});

// --- MANAGE SETTINGS ---
it('blocks users without permission from managing settings', function () {
    $user = User::factory()->create(); 
    $this->actingAs($user)->get(route('settings.index'))->assertStatus(403);
});

it('allows users with permission to manage settings', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage settings');
    $this->actingAs($user)->get(route('settings.index'))->assertStatus(200);
});

// --- MANAGE ROLES ---
it('blocks users without permission from managing roles', function () {
    $user = User::factory()->create(); 
    $this->actingAs($user)->get(route('roles.index'))->assertStatus(403);
});

it('allows users with permission to manage roles', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage roles');
    $this->actingAs($user)->get(route('roles.index'))->assertStatus(200);
});

// --- MANAGE INTEGRATIONS ---
it('blocks users without permission from managing integrations', function () {
    $user = User::factory()->create(); 
    // Fixed: Pointing to a real route that exists in your web.php
    $this->actingAs($user)->get(route('integrations.facebook.checklist'))->assertStatus(403);
});

it('allows users with permission to manage integrations', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage integrations');
    $this->actingAs($user)->get(route('integrations.facebook.checklist'))->assertStatus(200);
});