<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $permissions = ['view reports', 'export reports'];
    foreach ($permissions as $permission) { Permission::firstOrCreate(['name' => $permission]); }
});

// --- VIEW REPORTS ---
it('blocks users without permission from viewing reports', function () {
    $user = User::factory()->create(); 
    $this->actingAs($user)->get(route('reports.index'))->assertStatus(403);
});

it('allows users with permission to view reports', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view reports');
    $this->actingAs($user)->get(route('reports.index'))->assertStatus(200);
});