<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

it('successfully toggles a customer status from active to inactive', function () {
    $this->withoutExceptionHandling();

    // FIX: Created the permission and assigned it to the test user so they don't get blocked!
    Permission::firstOrCreate(['name' => 'toggle customer status']);
    $user = User::factory()->create();
    $user->givePermissionTo('toggle customer status');

    // 1. Create an ACTIVE customer (status = 1)
    $customerId = DB::table('customers')->insertGetId([
        'name' => 'Active Customer',
        'mobile' => '9998887777',
        'status' => 1, // 1 = Active
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 2. Action: Hit the toggle route
    $response = $this->actingAs($user)->post(route('customers.toggle-status', ['id' => $customerId]));

    // 3. Assertion: Verify the database flipped the status to 0 (Inactive)
    $this->assertDatabaseHas('customers', [
        'id' => $customerId,
        'status' => 0, 
    ]);
});