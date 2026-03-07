<?php

namespace App\Jobs;

use App\Models\Integration;
use App\Models\Inquiry;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessMetaLead implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $leadgenId;
    protected $pageId;

    public function __construct($leadgenId, $pageId)
    {
        $this->leadgenId = $leadgenId;
        $this->pageId = $pageId;
    }

    public function handle()
    {
        // 1. Find the active integration
        $integration = Integration::where('platform', 'meta')
            ->where('page_id', $this->pageId)
            ->where('is_active', true)
            ->first();

        if (!$integration) {
            Log::error("Meta Lead processing failed: No active integration found for Page ID {$this->pageId}");
            return;
        }

        // 2. Fetch Lead Details from Graph API
        $response = Http::get("https://graph.facebook.com/v19.0/{$this->leadgenId}", [
            'access_token' => $integration->access_token,
        ]);

        if ($response->failed()) {
            Log::error('Meta API Error: ' . $response->body());
            $integration->update(['last_error' => 'API Error: ' . $response->body()]);
            return;
        }

        $leadData = $response->json();
        
        // 3. Process Field Mapping
        $metaFields = [];
        foreach ($leadData['field_data'] as $field) {
            $key = $field['name'];
            $value = $field['values'][0] ?? null;
            $metaFields[$key] = $value;
        }

        // 4. Map to Inquiry Model
        $inquiryData = [
            'status' => 'New',
            'primary_date' => now()->toDateString(),
            'from_time' => '09:00:00', // FIX: Default time added to satisfy DB constraints
            'to_time' => '10:00:00',   // FIX: Default time added to satisfy DB constraints
        ];

        if ($integration->field_mapping) {
            foreach ($integration->field_mapping as $metaKey => $crmColumn) {
                if (isset($metaFields[$metaKey])) {
                    $inquiryData[$crmColumn] = $metaFields[$metaKey];
                }
            }
        }

        // 5. The Duplicate Check & Customer Creation
        $customerMobile = $inquiryData['mobile'] ?? 'Unknown';
        $customerName = $inquiryData['name'] ?? $inquiryData['customer_name'] ?? 'Meta Lead';
        $customerEmail = $inquiryData['email'] ?? null;

        $customer = Customer::firstOrCreate(
            ['mobile' => $customerMobile],
            [
                'name' => $customerName,
                'email' => $customerEmail,
                'status' => 1
            ]
        );

        // Attach the newly found/created customer to the Inquiry
        $inquiryData['customer_id'] = $customer->id;

        // 6. Create Inquiry
        try {
            Inquiry::create($inquiryData);
            
            // Update Integration Stats
            $integration->touch('last_synced_at');
            $integration->update(['last_error' => null]);
            
            Log::info("Meta Lead #{$this->leadgenId} imported successfully and attached to Customer ID {$customer->id}.");
            
        } catch (\Exception $e) {
            Log::error("Failed to save Meta Inquiry: " . $e->getMessage());
            $integration->update(['last_error' => 'DB Error: ' . $e->getMessage()]);
        }
    }
}