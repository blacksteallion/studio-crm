@extends('layouts.app')
@section('header', 'WhatsApp Setup: Step 1')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('settings.index', ['tab' => 'whatsapp']) }}" class="text-sm font-bold text-gray-500 hover:text-gray-800 transition"><i class="fas fa-arrow-left mr-2"></i> Back to Settings</a>
        <div class="flex gap-2">
            <span class="h-2 w-8 rounded-full bg-green-600"></span>
            <span class="h-2 w-8 rounded-full bg-gray-200"></span>
            <span class="h-2 w-8 rounded-full bg-gray-200"></span>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-8 md:p-12">
            <h2 class="text-2xl font-bold text-slate-800 mb-2">Step 1: Readiness Checklist</h2>
            <p class="text-gray-500 mb-8">Complete these items <strong>before</strong> touching the Meta Developer dashboard.</p>

            <div class="space-y-8">
                <div class="flex gap-4 items-start border-b border-gray-100 pb-8">
                    <div class="mt-1 text-green-600"><i class="fab fa-whatsapp text-2xl"></i></div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-lg">1. Dedicated Phone Number Required</h4>
                        <p class="text-sm text-gray-600 mt-1">You cannot connect a number that is currently active on the standard WhatsApp Business mobile app. If your number is on the app, you must open the app settings and completely <strong>delete the account</strong> before proceeding.</p>
                    </div>
                </div>

                <div class="flex gap-4 items-start border-b border-gray-100 pb-8">
                    <div class="mt-1 text-green-600"><i class="fas fa-user-shield text-2xl"></i></div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-lg">2. Admin Access Required</h4>
                        <p class="text-sm text-gray-600 mt-1">You must be an <strong>Admin</strong> of the Meta Business Manager account that owns your Facebook Page and Instagram Account.</p>
                    </div>
                </div>

                <div class="flex gap-4 items-start pb-4">
                    <div class="mt-1 text-green-600"><i class="fas fa-code text-2xl"></i></div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-lg">3. Meta Developer Account</h4>
                        <p class="text-sm text-gray-600 mt-1">Ensure you have registered and verified your account at <a href="https://developers.facebook.com" target="_blank" class="text-blue-600 hover:underline">developers.facebook.com</a>.</p>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex justify-end">
                <a href="{{ route('integrations.whatsapp.app-setup') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition transform hover:scale-105">Go to Step 2: Create App &rarr;</a>
            </div>
        </div>
    </div>
</div>
@endsection