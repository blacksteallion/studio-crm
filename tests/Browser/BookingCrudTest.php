<?php

use App\Models\User;
use App\Models\Location;
use App\Models\Customer;
use App\Models\ProductService;
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
        
        // Fetch REAL IDs currently in the database
        $locId = Location::first()->id ?? 1;
        $cusId = Customer::first()->id ?? 1;
        $prodId = ProductService::first()->id ?? 1;

        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/bookings/create')
                ->pause(1500);

        $browser->script("document.querySelectorAll('[required]').forEach(el => el.removeAttribute('required'));");

        // Attach our Ultimate JS Injector globally to the window
        $browser->script("
            window.setDropdown = function(selector, val) {
                let el = document.querySelector(selector);
                if(el) {
                    el.value = val;
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                    if(el.tomselect) el.tomselect.setValue(val);
                    if(typeof jQuery !== 'undefined' && jQuery(el).hasClass('select2-hidden-accessible')) {
                        jQuery(el).val(val).trigger('change');
                    }
                }
            };
        ");

        // 1. Select Location and WAIT for Livewire/Alpine to re-render the DOM
        $browser->script("window.setDropdown('select[name=\"location_id\"]', '{$locId}');");
        $browser->pause(2000); 

        // 2. Select Customer
        $browser->script("window.setDropdown('select[name=\"customer_id\"]', '{$cusId}');");
        $browser->pause(1000);

        // 3. Select Product (Wait for Livewire to fetch default price)
        $browser->script("window.setDropdown('select[name=\"items[0][product_service_id]\"]', '{$prodId}');");
        $browser->pause(1500); 

        // 4. Now that DOM is stable, set Price and Quantity
        $browser->script("
            let price = document.getElementsByName('items[0][price]')[0];
            if(price) { price.value = '500'; price.dispatchEvent(new Event('input', { bubbles: true })); }

            let qty = document.getElementsByName('items[0][quantity]')[0];
            if(qty) { qty.value = '2'; qty.dispatchEvent(new Event('input', { bubbles: true })); }
        ");
        $browser->pause(500);

        // Set Dates
        $browser->script([
            "document.querySelector('input[name=\"booking_date\"]')._flatpickr.setDate('2026-12-01', true);",
            "document.querySelector('input[name=\"start_time\"]')._flatpickr.setDate('10:00', true);",
            "document.querySelector('input[name=\"end_time\"]')._flatpickr.setDate('14:00', true);"
        ]);
        
        $browser->type('notes', 'Automated Dusk Test Booking')
                ->pause(500)
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