<?php

use App\Models\User;
use App\Models\Location;
use App\Models\Customer;
use App\Models\Order;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $permissions = ['view payments', 'add payments', 'delete payments', 'export payments'];
    foreach ($permissions as $permission) { 
        Permission::firstOrCreate(['name' => $permission]); 
    }

    $this->location = Location::firstOrCreate(['name' => 'Main Test Studio', 'is_active' => true]);
    $this->customer = Customer::firstOrCreate(['mobile' => '9999999999'], ['name' => 'Test Customer', 'email' => 'test@test.com']);
    
    $this->order = Order::factory()->create([
        'location_id' => $this->location->id,
        'customer_id' => $this->customer->id,
        'total_amount' => 5000,
        'status' => 'Unpaid'
    ]);
});

it('blocks users without permission from viewing the payments list', function () {
    $user = User::factory()->withLocation()->create(); 
    $this->actingAs($user)->get(route('payments.index'))->assertStatus(403);
});

it('allows users with permission to view the payments list', function () {
    $user = User::factory()->withLocation()->create();
    $user->givePermissionTo('view payments');
    $this->actingAs($user)->get(route('payments.index'))->assertStatus(200);
});

it('successfully records a payment against an order', function () {
    $user = User::factory()->withLocation()->create();
    $user->givePermissionTo('add payments');

    $response = $this->actingAs($user)->post(route('payments.store', $this->order->id), [
        'amount' => 2000,
        'payment_method' => 'Cash',
        'transaction_date' => now()->format('Y-m-d'),
        'reference_no' => 'REC-123'
    ]);

    $this->assertDatabaseHas('payments', [
        'order_id' => $this->order->id,
        'amount' => 2000,
        'payment_method' => 'Cash'
    ]);
});