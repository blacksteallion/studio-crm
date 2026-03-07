<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    $this->user = User::first(); 
});

test('booking calendar renders correctly', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/bookings/calendar')
                ->assertPathIs('/bookings/calendar')
                // Wait for the FullCalendar JS library to mount and render its toolbar
                ->waitFor('.fc-toolbar-title', 5)
                ->assertPresent('#calendar');
    });
});