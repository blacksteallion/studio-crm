<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    $this->user = User::first(); 
});

test('general settings render and save correctly', function () {
    $this->browse(function (Browser $browser) {
        $companyName = 'Dusk Tech ' . rand(100, 999);
        
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/settings')
                ->assertPathIs('/settings')
                ->assertSee('System Settings')
                ->assertSee('Company Profile')
                
                // Update company name and invoice prefix
                ->clear('company_name')
                ->type('company_name', $companyName)
                ->clear('invoice_prefix')
                ->type('invoice_prefix', 'DSK-')
                
                // Save and wait for the page to reload
                ->press('Save Settings')
                ->waitForLocation('/settings', 5)
                
                // Verify the database saved the new values and repopulated the inputs
                ->assertInputValue('company_name', $companyName)
                ->assertInputValue('invoice_prefix', 'DSK-');
    });
});

test('integrations tab renders and saves meta credentials', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/settings');
                
        // Safely switch to the Integrations tab using JavaScript to find the Facebook icon
        $browser->script("document.querySelector('.fa-facebook').closest('button').click();");
        
        $browser->pause(500)
                ->assertSee('Meta App Configuration')
                
                // Type in dummy API credentials
                ->clear('meta_app_id')
                ->type('meta_app_id', '1234567890')
                ->clear('meta_app_secret')
                ->type('meta_app_secret', 'dusk_secret_key')
                
                // Save and wait for the page to reload
                ->press('Save Keys')
                ->waitForLocation('/settings', 5);
                
        // Navigate back to the Integrations tab after the reload
        $browser->script("document.querySelector('.fa-facebook').closest('button').click();");
        
        // Verify the API keys were successfully saved
        $browser->pause(500)
                ->assertInputValue('meta_app_id', '1234567890')
                ->assertInputValue('meta_app_secret', 'dusk_secret_key');
    });
});

test('integrations tab is hidden on mobile screens', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                // Resize down to an iPhone screen width
                ->resize(390, 844) 
                ->visit('/settings')
                ->pause(500)
                
                // Because the tab has the Tailwind 'hidden' class on mobile, 
                // Selenium correctly registers the text as completely invisible!
                ->assertDontSee('Integrations'); 
    });
});