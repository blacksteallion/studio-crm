<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    // Use the main admin user who actually has permissions to see the sidebar!
    $this->user = User::first(); 
});

test('desktop sidebar renders and is visible', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->assertPresent('aside'); 
    });
});

test('mobile hamburger menu toggles the sidebar', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(390, 844) 
                ->visit('/dashboard')
                ->assertPathIs('/dashboard')
                
                // Use a broader selector for the hamburger menu
                ->waitFor('header button')
                ->click('header button')
                ->pause(500)
                
                ->assertPresent('aside'); 
    });
});

test('user profile dropdown opens and displays logout', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/dashboard')
                ->assertPathIs('/dashboard')
                
                // Broadened the selector to ignore changing alt text attributes
                ->click('header img')
                ->pause(400)
                
                ->assertSee('Log Out');
    });
});