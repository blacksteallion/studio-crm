@extends('layouts.app')
@section('header', 'WhatsApp Setup: Step 2')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('integrations.whatsapp.checklist') }}" class="text-sm font-bold text-gray-500 hover:text-gray-800 transition"><i class="fas fa-arrow-left mr-2"></i> Back to Step 1</a>
        <div class="flex gap-2">
            <span class="h-2 w-8 rounded-full bg-green-200"></span>
            <span class="h-2 w-8 rounded-full bg-green-600"></span>
            <span class="h-2 w-8 rounded-full bg-gray-200"></span>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-8 md:p-12">
            <h2 class="text-2xl font-bold text-slate-800 mb-2">Step 2: Create & Configure App</h2>
            <p class="text-gray-500 mb-8">Follow these steps exactly to register your number on the WhatsApp API.</p>

            <div class="space-y-10">
                <div class="flex gap-5">
                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-green-100 text-green-700 font-bold flex items-center justify-center">1</div>
                    <div class="flex-1">
                        <h4 class="font-bold text-slate-800 text-lg">Create New App</h4>
                        <p class="text-sm text-gray-600 mt-1 mb-3">Go to <a href="https://developers.facebook.com/apps" target="_blank" class="text-blue-600 font-bold hover:underline">Meta Developers > My Apps</a> and click <strong>Create App</strong>.</p>
                        <ul class="text-sm text-gray-600 list-disc ml-5 space-y-1">
                            <li>Select <strong>"Other"</strong> &rarr; <strong>"Business"</strong>.</li>
                            <li>Select the <strong>"Connect with customers through WhatsApp"</strong> use case.</li>
                            <li>Select your Business Portfolio and click Create.</li>
                        </ul>
                    </div>
                </div>

                <div class="flex gap-5">
                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-green-100 text-green-700 font-bold flex items-center justify-center">2</div>
                    <div class="flex-1">
                        <h4 class="font-bold text-slate-800 text-lg">Navigate to API Setup</h4>
                        <p class="text-sm text-gray-600 mt-1 mb-3">On your new App Dashboard, click <strong>"Customize the Connect with customers through WhatsApp use case"</strong>. This will land you on the <em>Quickstart</em> page. Look at the left sidebar and click <strong>API Setup</strong>.</p>
                        <img src="{{ asset('images/quickstart.jpg') }}" alt="Quickstart Menu" class="w-full rounded-lg border border-gray-200 shadow-sm mt-2">
                    </div>
                </div>

                <div class="flex gap-5">
                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-green-100 text-green-700 font-bold flex items-center justify-center">3</div>
                    <div class="flex-1">
                        <h4 class="font-bold text-slate-800 text-lg">Add Your Phone Number</h4>
                        <p class="text-sm text-gray-600 mt-1 mb-3">On the API Setup page, scroll down to <strong>Step 5: Add a phone number</strong>. Click the button and follow the prompts to verify your business number using an SMS OTP.</p>
                        <img src="{{ asset('images/api-setup.jpg') }}" alt="API Setup Screen" class="w-full rounded-lg border border-gray-200 shadow-sm mt-2">
                    </div>
                </div>

                <div class="flex gap-5">
                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-green-100 text-green-700 font-bold flex items-center justify-center">4</div>
                    <div class="flex-1">
                        <h4 class="font-bold text-slate-800 text-lg">Copy Your Phone Number ID</h4>
                        <p class="text-sm text-gray-600 mt-1 mb-3">Once verified, scroll back up to <strong>Step 1: Select phone numbers</strong>. Copy your <strong>Phone Number ID</strong>. You will need to paste this into the CRM in the next step!</p>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-xs text-yellow-800 font-bold">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Note: Do NOT copy the "Access Token" on this page. It is temporary and expires in 24 hours. We will generate a permanent one next.
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex justify-end">
                <a href="{{ route('integrations.whatsapp.instructions') }}" class="bg-slate-800 hover:bg-black text-white font-bold py-3 px-8 rounded-xl shadow-lg transition transform hover:scale-105">Go to Step 3: Connect Webhooks &rarr;</a>
            </div>
        </div>
    </div>
</div>
@endsection