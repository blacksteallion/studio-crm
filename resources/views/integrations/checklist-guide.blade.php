@extends('layouts.app')
@section('header', 'Pre-Integration Checklist')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-2xl border border-gray-200 shadow-sm p-8 mb-10">
    
    <div class="border-b border-gray-100 pb-6 mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Step 1: Readiness Checklist</h1>
        <p class="text-gray-500 mt-2">Complete these items <strong>before</strong> creating your App.</p>
    </div>

    <div class="space-y-6">
        
        <div class="flex gap-4">
            <div class="flex-shrink-0 mt-1">
                <i class="fas fa-user-shield text-blue-600 text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800">1. Admin Access Required</h3>
                <p class="text-sm text-gray-600 mt-1">You must be an <strong>Admin</strong> of the Facebook Page you want to connect.</p>
            </div>
        </div>

        <hr class="border-gray-100">

        <div class="flex gap-4">
            <div class="flex-shrink-0 mt-1">
                <i class="fas fa-building text-purple-600 text-xl"></i>
            </div>
            <div class="w-full">
                <h3 class="font-bold text-slate-800">2. Identify Your Account Type</h3>
                <p class="text-sm text-gray-600 mt-1 mb-3">Does your Page belong to a "Business Manager" account?</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <div class="font-bold text-sm text-slate-700 mb-1">Standard Page</div>
                        <p class="text-xs text-gray-500">You created the page yourself and manage it from your personal profile.</p>
                        <div class="mt-2 text-green-600 text-xs font-bold"><i class="fas fa-check"></i> Easy Setup</div>
                    </div>

                    <div class="bg-purple-50 p-3 rounded-lg border border-purple-200">
                        <div class="font-bold text-sm text-purple-900 mb-1">Business Manager</div>
                        <p class="text-xs text-purple-800">Your page is owned by an Agency or Business Portfolio.</p>
                        <div class="mt-2 text-purple-700 text-xs font-bold"><i class="fas fa-exclamation-circle"></i> Requires Extra Step</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="border-gray-100">

        <div class="flex gap-4">
            <div class="flex-shrink-0 mt-1">
                <i class="fas fa-code text-blue-600 text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800">3. Meta Developer Account</h3>
                <p class="text-sm text-gray-600 mt-1">Ensure you have a verified account at <a href="https://developers.facebook.com" target="_blank" class="text-blue-600 underline">developers.facebook.com</a>.</p>
            </div>
        </div>

    </div>

    <div class="mt-8 flex justify-end">
        <a href="{{ route('integrations.facebook.app-setup') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-all">
            Go to Step 2: Create App &rarr;
        </a>
    </div>

</div>
@endsection