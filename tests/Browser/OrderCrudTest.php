<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    $this->user = User::first(); 
});

test('invoice form catches missing required fields', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/orders/create')
                ->press('Create Invoice')
                ->assertPathIs('/orders/create'); 
    });
});

test('alpinejs dynamically calculates invoice totals and discounts', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/orders/create')
                ->waitFor('input[name="items[0][price]"]')
                ->pause(500); 
                
        $browser->script("
            let priceField = document.getElementsByName('items[0][price]')[0];
            if(priceField) { priceField.value = '500'; priceField.dispatchEvent(new Event('input', { bubbles: true })); }

            let qtyField = document.getElementsByName('items[0][qty]')[0];
            if(qtyField) { qtyField.value = '2'; qtyField.dispatchEvent(new Event('input', { bubbles: true })); }
            
            let discountField = document.querySelector('input[name=\"discount\"]');
            if(discountField) {
                discountField.value = '100';
                discountField.dispatchEvent(new Event('input', { bubbles: true }));
            }
        ");
                
        $browser->waitUntil("return document.querySelector('input[name=\"total_amount\"]').value == '900';", 5);
    });
});

test('user can create an invoice, record payment, and delete it', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/orders/create')
                ->pause(1500);

        $browser->script("document.querySelectorAll('[required]').forEach(el => el.removeAttribute('required'));");

        // 1. Select Valid Location
        $browser->script("
            let loc = document.querySelector('select[name=\"location_id\"]');
            if(loc) { loc.value = '1'; loc.dispatchEvent(new Event('change', { bubbles: true })); }
        ");

        $browser->pause(1500); // Wait for Cascade

        // 2. Select Valid Customer and Valid Product
        $browser->script("
            let cus = document.querySelector('select[name=\"customer_id\"]');
            if(cus) { cus.value = '29'; cus.dispatchEvent(new Event('change', { bubbles: true })); }

            let prod = document.querySelector('select[name=\"items[0][product_service_id]\"]');
            if(prod) { prod.value = '1'; prod.dispatchEvent(new Event('change', { bubbles: true })); }

            let price = document.getElementsByName('items[0][price]')[0];
            if(price) { price.value = '500'; price.dispatchEvent(new Event('input', { bubbles: true })); }

            let qty = document.getElementsByName('items[0][qty]')[0];
            if(qty) { qty.value = '2'; qty.dispatchEvent(new Event('input', { bubbles: true })); }
        ");

        $today = date('Y-m-d');
        $dueDate = date('Y-m-d', strtotime('+14 days'));

        $browser->script([
            "document.querySelector('.invoice-picker')._flatpickr.setDate('{$today}', true);",
            "document.querySelector('.due-picker')._flatpickr.setDate('{$dueDate}', true);"
        ]);
        
        $browser->pause(500)
                ->press('Create Invoice')
                ->waitForLocation('/orders', 10) 
                ->click('a[title="View Details"]')
                ->waitForText('Invoice Details', 10);
                
        // 3. Record Payment
        $browser->script("
            let btn = Array.from(document.querySelectorAll('button')).find(el => el.textContent.includes('Record Payment'));
            if(btn) btn.click();
        ");
        
        $browser->pause(500)
                ->type('reference_number', 'DUSK-PAY-123')
                ->press('Save Payment')
                ->pause(1500) 
                ->assertSee('DUSK-PAY-123'); 
                
        // 4. Strict Cleanup Order (Payment -> Alert -> Order)
        $browser->script("document.querySelector('form[action*=\"payments\"] button[type=\"submit\"]').click();");
        
        $browser->pause(500)
                ->acceptDialog() 
                ->pause(1500)
                ->visit('/orders')
                ->click('button[title="Delete"]')
                ->pause(500)
                ->press('Yes, Proceed') 
                ->pause(1500)
                ->assertPathIs('/orders');
    });
});