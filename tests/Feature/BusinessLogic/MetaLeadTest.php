<?php

use App\Jobs\ProcessMetaLead;
use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

it('successfully processes a meta lead, creates a customer, and creates an inquiry', function () {
    // 1. Setup the integration
    $integration = new Integration();
    $integration->platform = 'meta';
    $integration->page_id = 'PAGE_123';
    $integration->access_token = 'fake_token_for_testing';
    $integration->is_active = true;
    $integration->field_mapping = [
        'full_name' => 'customer_name', 
        'email' => 'email',
        'phone_number' => 'mobile'
    ];
    $integration->save();

    // 2. Fake the Facebook Graph API response!
    Http::fake([
        'https://graph.facebook.com/v19.0/LEAD_999*' => Http::response([
            'id' => 'LEAD_999',
            'field_data' => [
                ['name' => 'full_name', 'values' => ['Bruce Wayne']],
                ['name' => 'email', 'values' => ['bruce@wayneenterprises.com']],
                ['name' => 'phone_number', 'values' => ['5550199222']]
            ]
        ], 200)
    ]);

    // 3. Action: Run the Job exactly like the Webhook would
    $job = new ProcessMetaLead('LEAD_999', 'PAGE_123');
    $job->handle();

    // 4. Assertion A: Did it successfully create the Customer Profile?
    $this->assertDatabaseHas('customers', [
        'name' => 'Bruce Wayne',
        'email' => 'bruce@wayneenterprises.com',
        'mobile' => '5550199222',
    ]);

    // Grab that newly created customer ID to check the connection
    $customer = DB::table('customers')->where('mobile', '5550199222')->first();

    // 5. Assertion B: Did it successfully create the Inquiry and link it via ID?
    $this->assertDatabaseHas('inquiries', [
        'customer_id' => $customer->id,
        'status' => 'New',
    ]);
});