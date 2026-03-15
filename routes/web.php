<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController; 
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\MetaWebhookController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController; 
use App\Http\Controllers\LocationController; 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- GUEST ROUTES (Login & Password Reset) ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// --- META WEBHOOKS ---
Route::get('/webhooks/meta', [MetaWebhookController::class, 'verify'])->name('webhooks.meta.verify');
Route::post('/webhooks/meta', [MetaWebhookController::class, 'handle'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhooks.meta.handle');

// --- PROTECTED ROUTES ---
Route::middleware('auth')->group(function () {
    
    // --- LOCATION SWITCHER ---
    Route::post('/locations/switch', function (\Illuminate\Http\Request $request) {
        $request->validate(['location_id' => 'required']);
        session(['active_location_id' => $request->location_id]);
        return back();
    })->name('locations.switch');

    // --- LOCATIONS MANAGER ---
    Route::resource('locations', LocationController::class)->except(['create', 'show', 'edit']);
    Route::post('/locations/{location}/toggle-status', [LocationController::class, 'toggleStatus'])->name('locations.toggle-status');

    // Dashboard
    Route::get('/', function () { return redirect()->route('dashboard'); });
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Modules
    Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export');
    Route::resource('customers', CustomerController::class);
    
    // Staff & Export
    Route::get('/staff/export', [StaffController::class, 'export'])->name('staff.export');
    Route::resource('staff', StaffController::class);
    
    // Inquiries
    Route::get('/inquiries/export', [InquiryController::class, 'export'])->name('inquiries.export');
    Route::resource('inquiries', InquiryController::class);
    
    Route::post('/inquiries/{id}/log', [InquiryController::class, 'storeLog'])->name('inquiries.log');
    Route::get('/inquiries/{id}/activity', [InquiryController::class, 'activity'])->name('inquiries.activity');
    Route::put('/inquiries/logs/{log}', [InquiryController::class, 'updateLog'])->name('inquiries.logs.update');
    Route::delete('/inquiries/logs/{log}', [InquiryController::class, 'destroyLog'])->name('inquiries.logs.destroy');
    Route::post('/inquiries/{id}/convert', [InquiryController::class, 'convertToBooking'])->name('inquiries.convert');

    // Booking Calendar & Export
    Route::get('/bookings/export', [BookingController::class, 'export'])->name('bookings.export');
    Route::get('/bookings/calendar', [BookingController::class, 'calendar'])->name('bookings.calendar');
    Route::resource('bookings', BookingController::class);
    
    // Orders
    Route::get('/orders/{id}/pdf', [OrderController::class, 'downloadPdf'])->name('orders.pdf');
    Route::get('/orders/export', [OrderController::class, 'export'])->name('orders.export');
    Route::resource('orders', OrderController::class);
    
    // Payments
    Route::get('/payments/export', [PaymentController::class, 'export'])->name('payments.export');
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/orders/{id}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::delete('/payments/{id}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    // Expenses & Export
    Route::get('/expenses/export', [ExpenseController::class, 'export'])->name('expenses.export');
    Route::resource('expenses', ExpenseController::class);
    
    // Product Service
    Route::resource('product_services', ProductServiceController::class);

    // Settings, Roles & Toggles
    Route::resource('roles', RoleController::class)->middleware('can:manage roles');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    
    // WhatsApp Multi-Number Routing
    Route::post('/settings/whatsapp-numbers', [SettingController::class, 'storeWhatsappNumber'])->name('settings.whatsapp.store');
    Route::delete('/settings/whatsapp-numbers/{id}', [SettingController::class, 'destroyWhatsappNumber'])->name('settings.whatsapp.destroy');

    Route::post('/staff/{id}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');
    Route::post('/customers/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');

    // Integrations
    Route::middleware('can:manage integrations')->group(function () {
        // Facebook Lead Ads
        Route::get('/integrations', [IntegrationController::class, 'showChecklistGuide'])->name('integrations.index'); 
        Route::get('/integrations/facebook/checklist', [IntegrationController::class, 'showChecklistGuide'])->name('integrations.facebook.checklist'); 
        Route::get('/integrations/facebook/app-setup', [IntegrationController::class, 'showAppSetupGuide'])->name('integrations.facebook.app-setup'); 
        Route::get('/integrations/facebook/instructions', [IntegrationController::class, 'showMetaInstructions'])->name('integrations.facebook.instructions'); 
        Route::get('/integrations/facebook/connect', [IntegrationController::class, 'redirectToFacebook'])->name('integrations.facebook.connect');
        Route::get('/integrations/facebook/callback', [IntegrationController::class, 'handleFacebookCallback'])->name('integrations.facebook.callback');
        Route::post('/integrations/facebook/save-page', [IntegrationController::class, 'savePageSelection'])->name('integrations.facebook.save-page');
        Route::post('/integrations/mapping', [IntegrationController::class, 'updateMapping'])->name('integrations.mapping.update');
        Route::delete('/integrations/disconnect', [IntegrationController::class, 'disconnect'])->name('integrations.disconnect');

        // WhatsApp CTWA Documentation
        Route::get('/integrations/whatsapp/checklist', [IntegrationController::class, 'showWaChecklistGuide'])->name('integrations.whatsapp.checklist'); 
        Route::get('/integrations/whatsapp/app-setup', [IntegrationController::class, 'showWaAppSetupGuide'])->name('integrations.whatsapp.app-setup'); 
        Route::get('/integrations/whatsapp/instructions', [IntegrationController::class, 'showWaInstructions'])->name('integrations.whatsapp.instructions'); 
    });

    // --- REPORTS ---
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export_pdf');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/growth', [ReportController::class, 'growth'])->name('reports.growth');
    Route::get('/reports/operations', [ReportController::class, 'operations'])->name('reports.operations'); 
});