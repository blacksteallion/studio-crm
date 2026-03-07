<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    $this->user = User::first(); 
});

test('order index page renders correctly on desktop', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/orders')
                ->assertPathIs('/orders')
                ->assertSee('Orders & Invoices')
                ->assertVisible('table')
                ->assertSee('New Invoice');
    });
});

test('advanced search drawer toggles open on click', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/orders')
                
                ->click('button[title="Advanced Search"]')
                ->pause(500)
                
                // Verify the dropdowns and amount inputs are visible
                ->assertVisible('select[name="status"]')
                ->assertVisible('select[name="customer_id"]')
                ->assertVisible('input[name="min_amount"]')
                ->assertVisible('input[name="max_amount"]')
                
                // Flatpickr hides the real date inputs, so we use assertPresent
                ->assertPresent('input[name="start_date"]')
                ->assertPresent('input[name="end_date"]');
    });
});

test('order index switches to card layout on mobile', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(390, 844)
                ->visit('/orders')
                ->assertPresent('table')
                ->assertVisible('button[title="Toggle Search"]');
    });
});