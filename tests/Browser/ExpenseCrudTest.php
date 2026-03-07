<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    $this->user = User::first(); 
});

test('expense form catches missing required fields', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/expenses/create');
                
        $browser->script("document.querySelector('#expense_date')._flatpickr.clear();");
                
        $browser->press('Save Expense')
                ->assertPathIs('/expenses/create'); 
    });
});

test('user can create and delete an expense', function () {
    $this->browse(function (Browser $browser) {
        $uniqueExpense = 'Dusk Taxi ' . rand(1000, 9999);
        $today = date('Y-m-d');

        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/expenses/create')
                ->pause(1500);
                
        $browser->script("document.querySelectorAll('[required]').forEach(el => el.removeAttribute('required'));");

        // Use valid Location and exact string Category expected by backend
        $browser->script("
            let loc = document.querySelector('select[name=\"location_id\"]');
            if(loc) { loc.value = '1'; loc.dispatchEvent(new Event('change', { bubbles: true })); }
            
            let cat = document.querySelector('select[name=\"category\"]');
            if(cat) { cat.value = 'Rent & Utilities'; cat.dispatchEvent(new Event('change', { bubbles: true })); }
            
            document.querySelector('#expense_date')._flatpickr.setDate('{$today}', true);
        ");
        
        $browser->type('amount', '850')
                ->type('title', $uniqueExpense)
                ->type('description', 'Automated Dusk Test Expense')
                ->press('Save Expense')
                ->waitForLocation('/expenses', 10)
                ->pause(1000) 
                ->assertSee($uniqueExpense)
                ->click('button[title="Delete"]')
                ->pause(500)
                ->press('Yes, Proceed') 
                ->pause(1500)
                ->assertDontSee($uniqueExpense);
    });
});