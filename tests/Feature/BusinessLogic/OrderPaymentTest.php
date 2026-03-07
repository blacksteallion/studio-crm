<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'add payments']);
});

it('successfully records a payment against an order', function () {
    $this->withoutExceptionHandling(); 

    $user = User::factory()->create();
    $user->givePermissionTo('add payments');

    $customerId = DB::table('customers')->insertGetId([
        'name' => 'John Doe',
        'mobile' => '1234567890',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $orderId = DB::table('orders')->insertGetId([
        'order_number' => 'ORD-999',
        'customer_id' => $customerId,
        'staff_id' => $user->id,
        'booking_date' => now()->toDateString(),
        'total_amount' => 1000.00,
        'status' => 'Pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($user)->post(route('payments.store', ['id' => $orderId]), [
        'order_id' => $orderId, 
        'amount' => 1000.00,
        'transaction_date' => now()->toDateString(), // Matched to controller
        'payment_method' => 'Cash',
        'reference_number' => 'TXN12345',            // Matched to controller
        'notes' => 'Full payment received'
    ]);

    $this->assertDatabaseHas('payments', [
        'order_id' => $orderId,
        'amount' => 1000.00
    ]);
});