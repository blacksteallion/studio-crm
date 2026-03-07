<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $permissions = ['view products', 'create products', 'edit products', 'delete products'];
    foreach ($permissions as $permission) { Permission::firstOrCreate(['name' => $permission]); }
});

it('blocks users without permission from viewing the products list', function () {
    $user = User::factory()->create(); 
    $this->actingAs($user)->get(route('product_services.index'))->assertStatus(403);
});

it('allows users with permission to view the products list', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view products');
    $this->actingAs($user)->get(route('product_services.index'))->assertStatus(200);
});