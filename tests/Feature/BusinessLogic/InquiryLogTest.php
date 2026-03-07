<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'manage inquiry logs']);
});

it('adds a log and successfully updates the parent inquiry status and follow-up date', function () {
    $this->withoutExceptionHandling();

    $user = User::factory()->create();
    $user->givePermissionTo('manage inquiry logs');

    // 1. Setup Dummy Data
    $customerId = DB::table('customers')->insertGetId([
        'name' => 'Log Tester',
        'mobile' => '1112223333',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $inquiryId = DB::table('inquiries')->insertGetId([
        'customer_id' => $customerId,
        'primary_date' => now()->toDateString(),
        'from_time' => '10:00:00',
        'to_time' => '11:00:00',
        'status' => 'New', 
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $newFollowUpDate = now()->addDays(5)->toDateString();

    // 2. Action: Staff logs a call and changes status to "In Progress"
    $response = $this->actingAs($user)->post(route('inquiries.log', ['id' => $inquiryId]), [
        'type' => 'Call',
        'log_date' => now()->toDateString(),
        'log_time' => '14:30:00',
        'message' => 'Customer requested a callback next week.',
        'update_status' => 'In Progress',
        'next_follow_up' => $newFollowUpDate,
    ]);

    $response->assertSessionHasNoErrors();

    // 3. Assertion A: Verify the log was created
    $this->assertDatabaseHas('inquiry_logs', [
        'inquiry_id' => $inquiryId,
        'type' => 'Call',
    ]);

    // 4. Assertion B: Verify the parent Inquiry was actually updated!
    // FIX: Appended 00:00:00 because SQLite stores dates as full timestamps
    $this->assertDatabaseHas('inquiries', [
        'id' => $inquiryId,
        'status' => 'In Progress',
        'follow_up_date' => $newFollowUpDate . ' 00:00:00', 
    ]);
});