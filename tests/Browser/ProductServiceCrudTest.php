<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    $this->user = User::first(); 
});

test('product service form catches missing required fields', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/product_services/create')
                ->press('Save Item')
                // Verify validation blocked the submission (Name and Price are required)
                ->assertPathIs('/product_services/create'); 
    });
});

test('user can create edit and delete a product or service', function () {
    $this->browse(function (Browser $browser) {
        $uniqueItemName = 'Dusk Premium Hosting ' . rand(1000, 9999);
        $updatedItemName = $uniqueItemName . ' V2';

        $browser->loginAs($this->user)
                ->resize(1920, 1080);

        // --- 1. CREATE THE ITEM ---
        $browser->visit('/product_services/create')
                ->type('name', $uniqueItemName)
                ->select('type', 'Service')
                ->select('pricing_model', 'Monthly')
                ->type('price', '499.99')
                ->type('gst_rate', '18')
                ->type('description', 'Automated Dusk creation test.')
                ->press('Save Item')
                
                // Wait for redirect and verify it appears in the table
                ->waitForLocation('/product_services', 5)
                ->pause(500)
                ->assertSee($uniqueItemName);

        // --- 2. SEARCH & EDIT THE ITEM ---
        $browser->type('search', $uniqueItemName) // Use debounced search
                ->pause(1500) // Wait for results to filter
                ->click('a[title="Edit"]')
                
                // Change the name and price
                ->waitForText('Edit Item', 5)
                ->clear('name')->type('name', $updatedItemName)
                ->clear('price')->type('price', '599.99')
                ->press('Update Item')
                
                // Verify redirect back to list
                ->waitForLocation('/product_services', 5)
                ->pause(500);

        // --- 3. SEARCH & DELETE THE UPDATED ITEM ---
        $browser->type('search', $updatedItemName)
                ->pause(1500)
                ->assertSee($updatedItemName)
                ->assertSee('599.99')
                
                ->click('button[title="Delete"]')
                ->pause(500)
                ->press('Yes, Proceed') // Confirms your custom Alpine delete modal
                ->pause(1500)
                
                // Ensure it is completely gone
                ->assertDontSee($updatedItemName);
    });
});