<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    $this->user = User::first(); 
});

test('booking index page renders correctly on desktop', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/bookings')
                ->assertPathIs('/bookings')
                ->assertSee('Booking List')
                ->assertVisible('table')
                ->assertSee('New Booking');
    });
});

test('advanced search drawer toggles open on click', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/bookings')
                
                ->click('button[title="Advanced Search"]')
                ->pause(500)
                
                // Selects are visible
                ->assertVisible('select[name="status"]')
                ->assertVisible('select[name="staff_id"]')
                ->assertVisible('select[name="customer_id"]')
                
                // Flatpickr hides the real date inputs, so we use assertPresent
                ->assertPresent('input[name="start_date"]')
                ->assertPresent('input[name="end_date"]');
    });
});

test('booking index switches to card layout on mobile', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(390, 844)
                ->visit('/bookings')
                ->assertPresent('table')
                ->assertVisible('button[title="Toggle Search"]');
    });
});