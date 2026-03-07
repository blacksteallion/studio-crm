<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ProcessMetaLead;
use App\Models\Setting; // <--- Import Setting
use Illuminate\Support\Facades\Log;

class MetaWebhookController extends Controller
{
    /**
     * Step 1: Facebook Verification Challenge
     */
    public function verify(Request $request)
    {
        // Use Dynamic App Secret as the Verify Token
        $verifyToken = Setting::get('meta_app_secret');

        if ($request->input('hub_mode') === 'subscribe' && 
            $request->input('hub_verify_token') === $verifyToken) {
            return response($request->input('hub_challenge'), 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Step 2: Handle Incoming Leads (POST)
     */
    public function handle(Request $request)
    {
        $entry = $request->input('entry.0');
        $changes = $entry['changes'][0] ?? null;

        if ($changes && $changes['field'] === 'leadgen') {
            $value = $changes['value'];
            $leadgenId = $value['leadgen_id'];
            $pageId = $value['page_id'];

            ProcessMetaLead::dispatch($leadgenId, $pageId);
        }

        return response('EVENT_RECEIVED', 200);
    }
}