<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    $this->user = User::first(); 
});

test('booking form catches missing required fields', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/bookings/create')
                ->press('Create Booking')
                ->assertPathIs('/bookings/create'); 
    });
});

test('alpinejs dynamically calculates total estimate', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/bookings/create')
                ->click('button[type="button"].text-blue-600') 
                ->waitFor('input[name="items[0][price]"]')
                ->pause(500); 
                
        $browser->script("
            let priceField = document.getElementsByName('items[0][price]')[0];
            if(priceField) { priceField.value = '500'; priceField.dispatchEvent(new Event('input', { bubbles: true })); }

            let qtyField = document.getElementsByName('items[0][quantity]')[0];
            if(qtyField) { qtyField.value = '2'; qtyField.dispatchEvent(new Event('input', { bubbles: true })); }
        ");
                
        $browser->waitUntil("return document.querySelector('.text-lg.text-blue-600').innerText.includes('1000.00');", 5);
    });
});

test('user can create and delete a booking', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/bookings/create')
                ->pause(1500);

        // Nuke HTML required popups so backend validation can actually show us errors if they happen
        $browser->script("document.querySelectorAll('[required]').forEach(el => el.removeAttribute('required'));");

        // 1. Select Valid Location 
        $browser->script("
            let loc = document.querySelector('select[name=\"location_id\"]');
            if(loc) { loc.value = '1'; loc.dispatchEvent(new Event('change', { bubbles: true })); }
        ");
        
        $browser->pause(1500); // Wait for Cascade

        // 2. Select Valid Customer and Valid Product from tcstudio.sql
        $browser->script("
            let cus = document.querySelector('select[name=\"customer_id\"]');
            if(cus) { cus.value = '29'; cus.dispatchEvent(new Event('change', { bubbles: true })); }

            let prod = document.querySelector('select[name=\"items[0][product_service_id]\"]');
            if(prod) { prod.value = '1'; prod.dispatchEvent(new Event('change', { bubbles: true })); }

            let price = document.getElementsByName('items[0][price]')[0];
            if(price) { price.value = '500'; price.dispatchEvent(new Event('input', { bubbles: true })); }

            let qty = document.getElementsByName('items[0][quantity]')[0];
            if(qty) { qty.value = '2'; qty.dispatchEvent(new Event('input', { bubbles: true })); }
        ");

        $browser->pause(500);

        // 3. Set Dates
        $browser->script([
            "document.querySelector('input[name=\"booking_date\"]')._flatpickr.setDate('2026-12-01', true);",
            "document.querySelector('input[name=\"start_time\"]')._flatpickr.setDate('10:00', true);",
            "document.querySelector('input[name=\"end_time\"]')._flatpickr.setDate('14:00', true);"
        ]);
        
        $browser->type('notes', 'Automated Dusk Test Booking')
                ->press('Create Booking')
                ->waitForLocation('/bookings', 10) 
                ->assertSee('BKG-')
                ->click('button[title="Delete"]')
                ->pause(500)
                ->press('Yes, Proceed')
                ->pause(1500)
                ->assertPathIs('/bookings');
    });
});