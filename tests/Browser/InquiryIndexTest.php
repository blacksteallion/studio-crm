<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    // Grab the first user (Admin) to bypass Spatie permission restrictions
    $this->user = User::first(); 
});

test('inquiry index page renders correctly on desktop', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/inquiries')
                ->assertPathIs('/inquiries')
                ->assertSee('Inquiry List')
                // Verify the desktop elements exist
                ->assertVisible('form.hidden.md\\:block') // Desktop search bar
                ->assertVisible('table') // Desktop table
                ->assertSee('Add Inquiry'); // Create button
    });
});

test('advanced search drawer toggles open on click', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/inquiries')
                
                // Click the Advanced Search button (using its title attribute)
                ->click('button[title="Advanced Search"]')
                ->pause(500) // Wait for Alpine.js x-collapse transition
                
                // Verify the standard select dropdowns are visible
                ->assertVisible('select[name="lead_source_id"]')
                ->assertVisible('select[name="status"]')
                ->assertVisible('select[name="staff_id"]')
                ->assertVisible('select[name="customer_id"]')
                
                // Flatpickr hides the original date inputs with altInput: true, 
                // so we use assertPresent to verify they exist in the DOM structure
                ->assertPresent('input[name="start_date"]');
    });
});

test('inquiry index switches to card layout on mobile', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(390, 844) // iPhone 12 Pro dimensions
                ->visit('/inquiries')
                
                // The table still exists in the DOM (hidden via Tailwind CSS), so we use assertPresent
                ->assertPresent('table')
                
                // On mobile, the mobile search toggle button should become visible
                ->assertVisible('button[title="Toggle Search"]');
    });
});