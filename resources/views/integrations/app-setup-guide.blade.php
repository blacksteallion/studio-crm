@extends('layouts.app')
@section('header', 'App Configuration Guide')

@section('content')
<div class="max-w-4xl mx-auto mb-20" x-data="{ hasBusinessManager: false }">
    
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
        <div class="bg-slate-50 border-b border-gray-200 p-8">
            <h1 class="text-2xl font-bold text-slate-800">Step 2: Create & Configure App</h1>
            <p class="text-gray-600 mt-2">Follow these steps strictly in order.</p>
        </div>

        <div class="p-8 space-y-10">
            <div class="flex gap-4">
                <div class="flex-shrink-0"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600 font-bold text-sm">1</span></div>
                <div class="space-y-2">
                    <h3 class="font-bold text-slate-800">Create New App</h3>
                    <p class="text-sm text-gray-600">
                        Go to <a href="https://developers.facebook.com/apps/" target="_blank" class="text-blue-600 underline font-bold">Meta Developers > My Apps</a> and click <strong>Create App</strong>.
                    </p>
                    <ul class="list-disc pl-5 text-sm text-gray-500">
                        <li>Select <strong>"Other"</strong> -> <strong>"Business"</strong>.</li>
                        <li>Enter App Name (e.g. "My CRM Connect").</li>
                    </ul>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex-shrink-0"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600 font-bold text-sm">2</span></div>
                <div class="space-y-2">
                    <h3 class="font-bold text-slate-800">Get Credentials (Required for Next Steps)</h3>
                    <p class="text-sm text-gray-600">Go to <strong>App Settings > Basic</strong>.</p>
                    <div class="bg-yellow-50 p-3 rounded border border-yellow-100 text-sm">
                        <p><strong>Copy your App ID and App Secret now.</strong></p>
                        <p class="text-xs text-yellow-700 mt-1">You will need the "App Secret" to verify the Webhook in Step 3.</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex-shrink-0"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600 font-bold text-sm">3</span></div>
                <div class="w-full space-y-4">
                    <h3 class="font-bold text-slate-800">Add & Configure Products</h3>
                    <p class="text-sm text-gray-600">Return to the "Dashboard" and set up these two products:</p>
                    
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-sm">
                        <span class="font-bold text-slate-700 block mb-2">A. Facebook Login for Business</span>
                        <ul class="list-disc pl-5 space-y-1 text-gray-500">
                            <li>Click "Set Up". Go to <strong>Settings</strong>.</li>
                            <li>In <strong>Valid OAuth Redirect URIs</strong>, paste:</li>
                        </ul>
                        <code class="block bg-white border border-gray-300 rounded p-2 mt-2 font-mono text-xs select-all">{{ route('integrations.facebook.callback') }}</code>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-sm">
                        <span class="font-bold text-slate-700 block mb-2">B. Webhooks</span>
                        <ul class="list-disc pl-5 space-y-1 text-gray-500">
                            <li>Click "Set Up". Select <strong>"Page"</strong> from dropdown.</li>
                            <li>Click <strong>"Subscribe to this object"</strong>.</li>
                            <li><strong>Callback URL:</strong> Paste the URL found in your CRM Settings.</li>
                            <li><strong>Verify Token:</strong> Paste your <strong>App Secret</strong> (from Step 2).</li>
                            <li>Click "Verify and Save".</li>
                            <li><strong>Important:</strong> Find <code>leadgen</code> in the list and click <strong>Subscribe</strong>.</li>
                        </ul>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <div class="bg-indigo-900 rounded-2xl shadow-lg p-8 text-white relative overflow-hidden">
        <h2 class="text-xl font-bold mb-4 relative z-10">Crucial Step: Do you use "Business Manager"?</h2>
        <p class="text-indigo-200 text-sm mb-6 max-w-2xl relative z-10">
            If your Facebook Page is owned by a Business Portfolio, you <strong>MUST</strong> authorize this new App, or leads will be blocked.
        </p>

        <div class="flex flex-col md:flex-row gap-4 relative z-10">
            <button @click="hasBusinessManager = false" 
                :class="!hasBusinessManager ? 'bg-white text-indigo-900 ring-2 ring-indigo-400' : 'bg-indigo-800 text-indigo-300 hover:bg-indigo-700'"
                class="flex-1 py-4 px-6 rounded-xl font-bold text-left transition-all flex items-center justify-between">
                <div>
                    <div class="text-sm uppercase tracking-wide opacity-70">Option A</div>
                    <div class="text-lg">No, I manage it personally</div>
                </div>
                <i class="fas fa-user text-2xl" :class="!hasBusinessManager ? 'text-indigo-600' : 'text-indigo-400'"></i>
            </button>

            <button @click="hasBusinessManager = true" 
                :class="hasBusinessManager ? 'bg-white text-indigo-900 ring-2 ring-indigo-400' : 'bg-indigo-800 text-indigo-300 hover:bg-indigo-700'"
                class="flex-1 py-4 px-6 rounded-xl font-bold text-left transition-all flex items-center justify-between">
                <div>
                    <div class="text-sm uppercase tracking-wide opacity-70">Option B</div>
                    <div class="text-lg">Yes, I use Business Manager</div>
                </div>
                <i class="fas fa-briefcase text-2xl" :class="hasBusinessManager ? 'text-indigo-600' : 'text-indigo-400'"></i>
            </button>
        </div>

        <div x-show="hasBusinessManager" class="mt-8 bg-white/10 rounded-xl p-6 border border-white/20 backdrop-blur-sm">
            <h3 class="font-bold text-lg text-white mb-4"><i class="fas fa-lock-open mr-2"></i> How to Unlock Access</h3>

            <div class="space-y-6">
                <div class="flex items-start gap-3">
                    <div class="bg-indigo-500 h-6 w-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0">1</div>
                    <div>
                        <p class="text-sm font-bold text-white">Add App to Business</p>
                        <p class="text-xs text-indigo-200 mt-1">Go to <a href="https://business.facebook.com/settings/apps" target="_blank" class="underline hover:text-white">Business Settings > Accounts > Apps</a>.</p>
                        <p class="text-xs text-indigo-200">Click "Add" -> "Connect an App ID". Paste your ID.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="bg-indigo-500 h-6 w-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0">2</div>
                    <div>
                        <p class="text-sm font-bold text-white">Add Assets (Page) to App</p>
                        <p class="text-xs text-indigo-200 mt-1">Click on the App you just added.</p>
                        <p class="text-xs text-indigo-200">Click <strong>"Add Assets"</strong> -> Select <strong>Classic Pages</strong> -> Select your Page -> Click Add.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="bg-indigo-500 h-6 w-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0">3</div>
                    <div>
                        <p class="text-sm font-bold text-white">Assign CRM Permission</p>
                        <p class="text-xs text-indigo-200 mt-1">Go to <a href="https://business.facebook.com/settings/leads-accesses" target="_blank" class="underline hover:text-white">Integrations > Leads Access</a>.</p>
                        <p class="text-xs text-indigo-200">Select Page -> "Assign CRMs" -> Select your App -> Add.</p>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="!hasBusinessManager" class="mt-6 text-center text-indigo-200 text-sm">
            Great! You can skip the advanced configuration.
        </div>
    </div>
    
    <div class="mt-8 flex justify-center">
        <a href="{{ route('integrations.facebook.instructions') }}" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-all">
            Done! Go to Step 3: Connect & Test &rarr;
        </a>
    </div>

</div>
@endsection