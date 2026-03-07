<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    $this->user = User::first(); 
});

test('payment index page renders correctly on desktop', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/payments')
                ->assertPathIs('/payments')
                ->assertSee('Payment History')
                ->assertVisible('table')
                ->assertPresent('button[title="Advanced Search"]');
    });
});

test('advanced search drawer toggles open and shows filters', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/payments')
                
                // Open the drawer
                ->click('button[title="Advanced Search"]')
                ->pause(500)
                
                // Verify the dropdowns and amount inputs are visible
                ->assertVisible('select[name="payment_method"]')
                ->assertVisible('input[name="min_amount"]')
                ->assertVisible('input[name="max_amount"]')
                
                // Flatpickr dynamically hides the original date inputs, so we use assertPresent
                ->assertPresent('input[name="start_date"]')
                ->assertPresent('input[name="end_date"]');
    });
});

test('payment index switches to card layout on mobile', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(390, 844) // iPhone 12/13/14 Pro dimensions
                ->visit('/payments')
                
                // Check for the mobile-specific toggle search button
                ->assertVisible('button[title="Toggle Search"]')
                
                // Verify the desktop table is hidden and mobile block is present
                ->assertPresent('.block.md\\:hidden');
    });
});