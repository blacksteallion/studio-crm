<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    // Grab the first user (Admin) to bypass Spatie permission restrictions
    $this->user = User::first(); 
});

test('customer form requires name and mobile', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/customers/create')
                // Instantly press save without filling out the required fields
                ->press('Save Customer')
                // Verify we don't get redirected, meaning validation stopped us
                ->assertPathIs('/customers/create');
    });
});

test('user can create, search, and delete a customer', function () {
    $this->browse(function (Browser $browser) {
        // Generate random data to ensure we never hit a "Duplicate Mobile" database error
        $uniqueMobile = '999' . rand(1000000, 9999999); 
        $companyName = 'Dusk Automation LLC ' . rand(100, 999);

        // --- STEP 1: CREATE ---
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/customers/create')
                ->type('business_name', $companyName)
                ->type('name', 'Dusk Tester')
                ->type('mobile', $uniqueMobile)
                ->type('email', 'dusk@techcelerity.in')
                ->type('city', 'Ahmedabad')
                ->press('Save Customer')
                // Wait for the server to save and redirect us to the index page
                ->waitForLocation('/customers')
                ->assertSee($companyName);

        // --- STEP 2: SEARCH ---
        $browser->type('search', $companyName)
                // Your search bar has an 800ms debounce timeout before auto-submitting. 
                // We pause for 1.5 seconds to let the JS fire and the page reload.
                ->pause(1500) 
                ->assertSee($companyName);

        // --- STEP 3: DELETE ---
        // Click the trash can button
        $browser->click('button[title="Delete"]')
                // Wait half a second for your Alpine.js warning modal to slide onto the screen
                ->pause(500) 
                // Click the red confirm button from your app.blade.php layout
                ->press('Yes, Proceed')
                // Wait for the delete request to finish and page to reload
                ->pause(1500) 
                // Verify the customer is officially gone!
                ->assertDontSee($companyName);
    });
});