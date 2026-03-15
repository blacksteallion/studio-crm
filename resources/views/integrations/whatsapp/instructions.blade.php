@extends('layouts.app')
@section('header', 'WhatsApp Setup: Step 3')

@section('content')
<div class="max-w-4xl mx-auto mb-10">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('integrations.whatsapp.app-setup') }}" class="text-sm font-bold text-gray-500 hover:text-gray-800 transition"><i class="fas fa-arrow-left mr-2"></i> Back to Step 2</a>
        <div class="flex gap-2">
            <span class="h-2 w-8 rounded-full bg-green-200"></span>
            <span class="h-2 w-8 rounded-full bg-green-200"></span>
            <span class="h-2 w-8 rounded-full bg-green-600"></span>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-8 md:p-12">
            <h2 class="text-2xl font-bold text-slate-800 mb-2">Step 3: Connect & Configure Webhooks</h2>
            <p class="text-gray-500 mb-8">Finalize the connection by generating a permanent token and linking the CRM webhook.</p>

            <div class="space-y-10">
                <div class="flex gap-5">
                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-green-100 text-green-700 font-bold flex items-center justify-center">1</div>
                    <div class="flex-1">
                        <h4 class="font-bold text-slate-800 text-lg">Generate Permanent Access Token</h4>
                        <p class="text-sm text-gray-600 mt-1 mb-2">We need a token that never expires so your CRM never disconnects.</p>
                        <ul class="text-sm text-gray-600 list-decimal ml-5 space-y-2">
                            <li>Go to <a href="https://business.facebook.com/settings" target="_blank" class="text-blue-600 font-bold hover:underline">Business Settings</a> &rarr; <strong>Users</strong> &rarr; <strong>System Users</strong>.</li>
                            <li>Click <strong>Add</strong>. Name it "CRM Bot" and assign the <strong>Admin</strong> role.</li>
                            <li>Select your new user and click <strong>Add Assets</strong> &rarr; <strong>Apps</strong> &rarr; Select your WhatsApp App and grant <strong>Full Control</strong>.</li>
                            <li>Click <strong>Generate New Token</strong>. Ensure Expiration is set to <strong>Never</strong>.</li>
                            <li>Check the boxes for <code>whatsapp_business_messaging</code> and <code>whatsapp_business_management</code>.</li>
                            <li>Click Generate and <strong>Copy the Token</strong> immediately.</li>
                        </ul>
                    </div>
                </div>

                <div class="flex gap-5">
                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-green-100 text-green-700 font-bold flex items-center justify-center">2</div>
                    <div class="flex-1">
                        <h4 class="font-bold text-slate-800 text-lg">Save Credentials in CRM</h4>
                        <p class="text-sm text-gray-600 mt-1">Return to your <a href="{{ route('settings.index', ['tab' => 'whatsapp']) }}" target="_blank" class="text-blue-600 font-bold hover:underline">CRM Settings Page</a>.</p>
                        <ul class="text-sm text-gray-600 list-disc ml-5 space-y-1 mt-2">
                            <li>Create a random password/phrase for your <strong>Webhook Verify Token</strong> (e.g. <code>my_studio_123</code>).</li>
                            <li>Click <strong>Add Number</strong> and paste your <strong>Phone Number ID</strong> (From Step 2) and your <strong>Permanent Access Token</strong> (From above).</li>
                            <li>Save everything.</li>
                        </ul>
                    </div>
                </div>

                <div class="flex gap-5">
                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-green-100 text-green-700 font-bold flex items-center justify-center">3</div>
                    <div class="flex-1">
                        <h4 class="font-bold text-slate-800 text-lg">Configure Webhooks in Meta</h4>
                        <p class="text-sm text-gray-600 mt-1 mb-3">Go back to your Meta App Dashboard. On the left sidebar, click <strong>Configuration</strong>.</p>
                        <img src="{{ asset('images/configuration.png') }}" alt="Configuration Screen" class="w-full rounded-lg border border-gray-200 shadow-sm mb-3">
                        <ul class="text-sm text-gray-600 list-disc ml-5 space-y-1">
                            <li>Under the Webhook section, click <strong>Edit</strong>.</li>
                            <li><strong>Callback URL:</strong> Paste the URL provided in your CRM settings.</li>
                            <li><strong>Verify Token:</strong> Paste the custom phrase you created in Step 3.2.</li>
                            <li>Click <strong>Verify and save</strong>.</li>
                        </ul>
                    </div>
                </div>

                <div class="flex gap-5">
                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-green-100 text-green-700 font-bold flex items-center justify-center">4</div>
                    <div class="flex-1">
                        <h4 class="font-bold text-slate-800 text-lg">Subscribe to Messages</h4>
                        <p class="text-sm text-gray-600 mt-1">Directly below the Webhook section you just saved (under "Webhook fields"), click <strong>Manage</strong>. Find <code>messages</code> in the list and click <strong>Subscribe</strong>.</p>
                        
                        <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3 text-green-800">
                            <i class="fas fa-check-circle text-2xl"></i>
                            <div>
                                <h5 class="font-bold">You are done!</h5>
                                <p class="text-xs">Your CRM is now actively listening for incoming WhatsApp Ad leads.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            
            <div class="mt-10 flex justify-center border-t border-gray-100 pt-8">
                <a href="{{ route('settings.index', ['tab' => 'whatsapp']) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-10 rounded-xl transition">Finish & Return to Settings</a>
            </div>
        </div>
    </div>
</div>
@endsection