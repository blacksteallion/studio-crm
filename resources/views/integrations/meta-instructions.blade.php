@extends('layouts.app')
@section('header', 'Connection & Testing')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-2xl border border-gray-200 shadow-sm p-8 mb-10">
    
    <div class="border-b border-gray-100 pb-6 mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Step 3: Connect & Test</h1>
        <p class="text-gray-500 mt-2">Finalize the connection and verify that leads are syncing.</p>
    </div>

    <div class="mb-10">
        <div class="flex items-center gap-4 mb-4">
            <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">1</div>
            <h3 class="font-bold text-lg text-slate-800">Enter Credentials</h3>
        </div>
        <div class="ml-12 text-sm text-gray-600 space-y-3">
            <p>1. Return to the <a href="{{ route('settings.index', ['tab' => 'integrations']) }}" class="text-blue-600 font-bold hover:underline">CRM Settings Page</a>.</p>
            <p>2. Paste your <strong>App ID</strong> and <strong>App Secret</strong>.</p>
            <p>3. Click <strong>Save Keys</strong>.</p>
        </div>
    </div>

    <div class="mb-10">
        <div class="flex items-center gap-4 mb-4">
            <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">2</div>
            <h3 class="font-bold text-lg text-slate-800">Connect Facebook Page</h3>
        </div>
        <div class="ml-12 text-sm text-gray-600 space-y-3">
            <p>1. Click the <strong>Continue with Facebook</strong> button.</p>
            <p>2. A popup will appear. Click <strong>Edit Access</strong> and ensure ALL permissions are enabled for your Page.</p>
            <p>3. Select your Page in the list and click <strong>Connect Page</strong>.</p>
            <p class="text-green-600 text-xs font-bold"><i class="fas fa-check-circle"></i> This will automatically subscribe the App to your Page.</p>
        </div>
    </div>

    <div class="mb-6 bg-slate-50 border border-slate-200 rounded-xl p-6">
        <div class="flex items-center gap-4 mb-4">
            <div class="h-8 w-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold">3</div>
            <h3 class="font-bold text-lg text-slate-800">Verify & Test (Recommended)</h3>
        </div>
        <div class="ml-12 text-sm text-gray-600 space-y-3">
            <p>Don't wait for a real lead. Force a test lead now:</p>
            <ol class="list-decimal pl-5 space-y-2">
                <li>Go to the <a href="https://developers.facebook.com/tools/lead-ads-testing" target="_blank" class="text-blue-600 underline font-bold">Lead Ads Testing Tool</a>.</li>
                <li>Select your Page and App.</li>
                <li>Click <strong>Create Lead</strong>.</li>
                <li><strong>Check CRM:</strong> Go to "Inquiries" in the CRM. You should see a "Dummy Lead".</li>
            </ol>

            <div class="mt-4 p-3 bg-white border border-red-100 rounded text-xs text-red-600">
                <strong>Troubleshooting:</strong> If the lead doesn't appear, click the <strong>"Track Status"</strong> button in the testing tool.
                <ul class="list-disc pl-5 mt-1">
                    <li><strong>Status 200:</strong> Success (Check your spam/filters).</li>
                    <li><strong>Status 103/Policy Error:</strong> Business Manager permission issue (See Step 2 Guide).</li>
                    <li><strong>App Not Subscribed:</strong> Try reconnecting the page in the CRM.</li>
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection