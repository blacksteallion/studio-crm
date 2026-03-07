<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    $this->user = User::first(); 
});

test('expense index page renders correctly on desktop', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/expenses')
                ->assertPathIs('/expenses')
                ->assertSee('Expense Manager')
                ->assertVisible('table')
                ->assertSee('Add Expense');
    });
});

test('advanced search drawer toggles open and shows filters', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/expenses')
                
                // Open the advanced search drawer
                ->click('button[title="Advanced Search"]')
                ->pause(500)
                
                // Verify the filter fields are visible
                ->assertVisible('input[name="title"]')
                ->assertVisible('select[name="category"]')
                ->assertVisible('input[name="min_amount"]')
                ->assertVisible('input[name="max_amount"]')
                
                // Flatpickr dynamically hides the original date inputs
                ->assertPresent('input[name="start_date"]')
                ->assertPresent('input[name="end_date"]');
    });
});

test('expense index switches to card layout on mobile', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(390, 844) // iPhone dimensions
                ->visit('/expenses')
                
                // Check for the mobile-specific toggle search button
                ->assertVisible('button[title="Toggle Search"]')
                
                // Verify the mobile card block is present
                ->assertPresent('.block.md\\:hidden');
    });
});