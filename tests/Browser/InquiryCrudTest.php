<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    $this->user = User::first(); 
});

test('inquiry form catches missing required fields', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/inquiries/create')
                ->press('Save Inquiry')
                ->assertPathIs('/inquiries/create'); 
    });
});

test('alpinejs dynamically calculates total estimate', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/inquiries/create')
                ->click('button[type="button"].text-blue-600') 
                ->waitFor('input[name="items[0][price]"]')
                ->pause(500); 
                
        $browser->script("
            let priceField = document.getElementsByName('items[0][price]')[0];
            if(priceField) { priceField.value = '500'; priceField.dispatchEvent(new Event('input', { bubbles: true })); }

            let qtyField = document.getElementsByName('items[0][quantity]')[0];
            if(qtyField) { qtyField.value = '2'; qtyField.dispatchEvent(new Event('input', { bubbles: true })); }
        ");
                
        $browser->pause(1500)
                ->assertInputValue('calculated_budget', '1000.00'); 
    });
});

test('user can create search and delete an inquiry', function () {
    $this->browse(function (Browser $browser) {
        $uniqueMobile = '888' . rand(1000000, 9999999);
        $customerName = 'Dusk Alpine ' . rand(100, 999);

        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/inquiries/create');

        // Safely set the Location using JS (Unchained)
        $browser->script("
            let loc = document.querySelector('select[name=\"location_id\"]');
            if(loc) { loc.value = '1'; loc.dispatchEvent(new Event('change', { bubbles: true })); }
        ");
        
        $browser->pause(500);

        $browser->type('name', $customerName)
                ->type('mobile', $uniqueMobile)
                ->type('business_name', 'Techcelerity Testing');
                
        // Safely inject Flatpickr dates (Unchained)
        $browser->script([
            "document.querySelector('input[name=\"primary_date\"]')._flatpickr.setDate('2025-12-01');",
            "document.querySelector('input[name=\"from_time\"]')._flatpickr.setDate('10:00');",
            "document.querySelector('input[name=\"to_time\"]')._flatpickr.setDate('14:00');"
        ]);
                
        $browser->press('Save Inquiry')
                ->waitForLocation('/inquiries')
                ->assertSee($customerName)
                ->type('search', $customerName)
                ->pause(1500) 
                ->click('button[title="Delete"]')
                ->pause(500)
                ->press('Yes, Proceed')
                ->pause(1500)
                ->assertDontSee($customerName);
    });
});