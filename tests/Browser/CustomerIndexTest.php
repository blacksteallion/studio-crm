<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    // Grab the first user in your database to bypass Spatie permission restrictions
    $this->user = User::first(); 
});

test('customer index page renders correctly on desktop', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/customers')
                ->assertPathIs('/customers')
                ->assertSee('Customer List')
                // Verify the desktop elements exist
                ->assertVisible('form.hidden.md\\:block') // Desktop search bar
                ->assertVisible('table') // Desktop table
                ->assertSee('Add Customer'); // Create button
    });
});

test('advanced search drawer toggles open on click', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/customers')
                
                // The advanced filters should be hidden by default
                ->assertMissing('input[name="f_name"]') 
                
                // Click the Advanced Search button (using its title attribute)
                ->click('button[title="Advanced Search"]')
                ->pause(500) // Wait for Alpine.js x-collapse transition
                
                // Verify the specific Customer filter fields are now visible
                ->assertVisible('input[name="f_name"]')
                ->assertVisible('input[name="f_business"]')
                ->assertVisible('input[name="f_mobile"]')
                ->assertVisible('input[name="f_email"]')
                ->assertVisible('select[name="f_status"]');
    });
});

test('customer index switches to card layout on mobile', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(390, 844) // iPhone 12 Pro dimensions
                ->visit('/customers')
                
                // The table still exists in the DOM (hidden via Tailwind CSS), so we use assertPresent
                ->assertPresent('table')
                
                // On mobile, the mobile search toggle button should become visible
                ->assertVisible('button[title="Toggle Search"]');
    });
});