<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inquiry;
use App\Models\Setting;
use App\Models\WhatsappNumber; // <--- Import the new model
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $verifyToken = Setting::get('whatsapp_verify_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response($challenge, 200);
        }

        return response()->json(['error' => 'Invalid verify token'], 403);
    }

    public function handle(Request $request)
    {
        $data = $request->all();

        $entry = $data['entry'][0] ?? null;
        $change = $entry['changes'][0]['value'] ?? null;
        $message = $change['messages'][0] ?? null;
        $contact = $change['contacts'][0] ?? null;
        
        // NEW: Grab the specific business number ID that received this message
        $metadata = $change['metadata'] ?? null;
        $receiverPhoneId = $metadata['phone_number_id'] ?? null;

        if (!$message) {
            return response()->json(['status' => 'ignored'], 200);
        }

        if (isset($message['referral']) && $receiverPhoneId) {
            $this->processCTWALead($message, $contact, $receiverPhoneId);
        }

        return response()->json(['status' => 'success'], 200);
    }

    private function processCTWALead($message, $contact, $receiverPhoneId)
    {
        $phone = $message['from'];
        $name = $contact['profile']['name'] ?? 'WhatsApp Lead';
        $adHeadline = $message['referral']['headline'] ?? 'Instagram/Facebook Ad';
        $sourceId = $message['referral']['source_id'] ?? 'N/A';

        // Find which business number received this, and who it is assigned to
        $waNumber = WhatsappNumber::where('phone_number_id', $receiverPhoneId)->first();

        // Create the Inquiry and dynamically assign the staff member!
        $inquiry = Inquiry::firstOrCreate(
            ['mobile' => $phone], 
            [
                'name' => $name,
                'lead_source' => 'Meta CTWA: ' . $adHeadline,
                'status' => 'New',
                'internal_notes' => "Lead generated directly from WhatsApp Ad. Ad Source ID: " . $sourceId,
                'assigned_to' => $waNumber ? $waNumber->assigned_staff_id : null // Automatically routes to the assigned staff!
            ]
        );

        // Only send the auto-reply if this was a BRAND NEW lead and we have the API keys for this number
        if ($inquiry->wasRecentlyCreated && $waNumber && $waNumber->is_active) {
            $this->sendInteractiveWelcomeMessage($phone, $waNumber);
        }
    }

    private function sendInteractiveWelcomeMessage($phoneNumber, $waNumber)
    {
        $url = "https://graph.facebook.com/v19.0/" . $waNumber->phone_number_id . "/messages";

        $response = Http::withToken($waNumber->access_token)->post($url, [
            'messaging_product' => 'whatsapp',
            'to' => $phoneNumber,
            'type' => 'template',
            'template' => [
                'name' => $waNumber->welcome_template_name,
                'language' => [
                    'code' => 'en' 
                ]
            ]
        ]);

        if ($response->failed()) {
            Log::error('Failed to send WhatsApp Welcome Message: ' . $response->body());
        }
    }
}