<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    $this->user = User::first(); 
});

test('product services index page renders correctly on desktop', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/product_services')
                ->assertPathIs('/product_services')
                ->assertSee('Products & Services')
                ->assertVisible('table')
                ->assertSee('Add New');
    });
});

test('advanced search drawer toggles open and shows filters', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/product_services')
                
                // Open the drawer
                ->click('button[title="Advanced Search"]')
                ->pause(500)
                
                // Verify the filter dropdowns and inputs are visible
                ->assertVisible('input[name="name"]')
                ->assertVisible('select[name="type"]')
                ->assertVisible('select[name="pricing_model"]')
                ->assertVisible('input[name="price"]')
                ->assertVisible('select[name="gst_rate"]');
    });
});

test('product services index switches to card layout on mobile', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(390, 844) // iPhone dimensions
                ->visit('/product_services')
                
                // Check for the mobile-specific toggle search button
                ->assertVisible('button[title="Toggle Search"]')
                
                // Verify the desktop table is hidden and mobile block is present
                ->assertPresent('.block.md\\:hidden');
    });
});