<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'convert inquiries']);
});

it('redirects to the booking creation page when converting an inquiry', function () {
    $this->withoutExceptionHandling(); 

    $user = User::factory()->create();
    $user->givePermissionTo('convert inquiries');

    // 1. Setup a dummy Customer
    $customerId = DB::table('customers')->insertGetId([
        'name' => 'Jane Smith',
        'mobile' => '0987654321',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 2. Setup an invisible "New" Inquiry linked to the Customer
    $inquiryId = DB::table('inquiries')->insertGetId([
        'customer_id' => $customerId,
        'primary_date' => now()->toDateString(), 
        'from_time' => '10:00:00',
        'to_time' => '11:00:00', 
        'status' => 'New',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 3. Action: The user clicks the "Convert" button
    $response = $this->actingAs($user)->post(route('inquiries.convert', ['id' => $inquiryId]));

    // 4. Assertion: Verify it successfully redirects them to the Booking page!
    $response->assertRedirect(route('bookings.create', ['inquiry_id' => $inquiryId]));
});