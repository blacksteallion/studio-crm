<?php

use App\Http\Controllers\Api\WhatsAppWebhookController;

// WhatsApp Webhook Routes
Route::get('/whatsapp/webhook', [WhatsAppWebhookController::class, 'verify']);
Route::post('/whatsapp/webhook', [WhatsAppWebhookController::class, 'handle']);
