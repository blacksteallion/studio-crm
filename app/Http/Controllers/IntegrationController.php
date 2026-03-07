<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Models\Setting; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class IntegrationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:manage integrations'),
        ];
    }

    // --- STEP 1: Checklist Guide ---
    public function showChecklistGuide()
    {
        return view('integrations.checklist-guide');
    }

    // --- STEP 2: App Setup Guide ---
    public function showAppSetupGuide()
    {
        return view('integrations.app-setup-guide');
    }

    // --- STEP 3: Connection Instructions ---
    public function showMetaInstructions()
    {
        return view('integrations.meta-instructions');
    }

    // --- START OAUTH FLOW ---
    public function redirectToFacebook()
    {
        $appId = Setting::get('meta_app_id');
        $redirectUri = route('integrations.facebook.callback');
        
        // SCOPE: Ensure we ask for everything needed to see Pages & Leads
        $scope = 'pages_show_list,pages_read_engagement,leads_retrieval,business_management';

        if (!$appId) {
            return redirect()->route('settings.index', ['tab' => 'integrations'])
                ->with('error', 'App ID missing. Please save configuration first.');
        }

        // CRITICAL FIX: "auth_type=rerequest"
        // This forces Facebook to re-show the permission popup so you can check the missing boxes.
        $url = "https://www.facebook.com/v19.0/dialog/oauth?client_id={$appId}&redirect_uri={$redirectUri}&scope={$scope}&response_type=code&auth_type=rerequest";

        return redirect($url);
    }

    // --- HANDLE FACEBOOK CALLBACK ---
    public function handleFacebookCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('settings.index', ['tab' => 'integrations'])
                ->with('error', 'Facebook authorization failed: ' . $request->error_reason);
        }

        $code = $request->code;
        $appId = Setting::get('meta_app_id');
        $appSecret = Setting::get('meta_app_secret');
        $redirectUri = route('integrations.facebook.callback');

        // 1. Exchange Code for User Access Token
        $response = Http::get("https://graph.facebook.com/v19.0/oauth/access_token", [
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'client_secret' => $appSecret,
            'code' => $code,
        ]);

        if ($response->failed()) {
            return redirect()->route('settings.index', ['tab' => 'integrations'])
                ->with('error', 'Failed to get access token. Check App Secret.');
        }

        $userAccessToken = $response->json()['access_token'];

        // 2. Fetch Pages this user manages
        $pagesResponse = Http::get("https://graph.facebook.com/v19.0/me/accounts", [
            'access_token' => $userAccessToken,
            'fields' => 'name,access_token,id', 
        ]);

        if ($pagesResponse->failed()) {
            return redirect()->route('settings.index', ['tab' => 'integrations'])
                ->with('error', 'Failed to fetch Facebook Pages.');
        }

        $pages = $pagesResponse->json()['data'];

        // Redirect back to settings with the list of pages
        return redirect()->route('settings.index', ['tab' => 'integrations'])
            ->with('facebook_pages', $pages);
    }

    // --- SAVE SELECTED PAGE & SUBSCRIBE ---
    public function savePageSelection(Request $request)
    {
        $request->validate([
            'page_id' => 'required',
            'page_name' => 'required',
            'page_access_token' => 'required',
        ]);

        // CRITICAL STEP: Subscribe App to Page Events
        // This tells Facebook: "Hey, send leads from this specific Page to my Webhook"
        $subscribeResponse = Http::post("https://graph.facebook.com/v19.0/{$request->page_id}/subscribed_apps", [
            'subscribed_fields' => 'leadgen',
            'access_token' => $request->page_access_token
        ]);

        if ($subscribeResponse->failed()) {
            // We log the error but don't stop the user, as sometimes manual subscription works too
            Log::error('Facebook Subscription Failed: ' . $subscribeResponse->body());
        }

        // Save to Database
        Integration::updateOrCreate(
            ['platform' => 'meta'],
            [
                'page_id' => $request->page_id,
                'page_name' => $request->page_name,
                'access_token' => $request->page_access_token,
                'is_active' => true,
                'field_mapping' => [
                    'full_name' => 'name',
                    'email' => 'email',
                    'phone_number' => 'mobile',
                ]
            ]
        );

        return redirect()->route('settings.index', ['tab' => 'integrations'])
            ->with('success', 'Facebook Page connected & subscribed successfully!');
    }

    // --- UPDATE FIELD MAPPING ---
    public function updateMapping(Request $request)
    {
        $integration = Integration::where('platform', 'meta')->firstOrFail();
        
        $mapping = [];
        foreach($request->meta_field as $index => $key) {
            if(!empty($key) && !empty($request->crm_field[$index])) {
                $mapping[$key] = $request->crm_field[$index];
            }
        }

        $integration->update(['field_mapping' => $mapping]);

        return redirect()->route('settings.index', ['tab' => 'integrations'])
            ->with('success', 'Field mapping updated.');
    }

    // --- DISCONNECT ---
    public function disconnect()
    {
        Integration::where('platform', 'meta')->delete();
        return redirect()->route('settings.index', ['tab' => 'integrations'])
            ->with('success', 'Facebook integration disconnected.');
    }
}