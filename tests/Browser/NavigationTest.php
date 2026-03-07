<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    // Safely grab or create our test user so the DB never crashes
    $this->user = User::firstOrCreate(
        ['email' => 'admin@tcstudio.com'],
        ['name' => 'Admin Test', 'password' => bcrypt('SecurePassword123!')]
    );
});

test('desktop sidebar renders and is visible', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080) // Desktop viewport
                ->visit('/dashboard')
                ->assertPathIs('/dashboard') // Ensure we didn't get bounced back to login
                ->assertVisible('aside'); // Verify the sidebar structure exists
    });
});

test('mobile hamburger menu toggles the sidebar', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(390, 844) // iPhone 12 Pro viewport
                ->visit('/dashboard')
                ->assertPathIs('/dashboard')
                
                // Wait for the mobile hamburger button to appear and click it
                ->waitFor('header button.lg\\:hidden')
                ->click('header button.lg\\:hidden')
                ->pause(500) // Pause for Alpine.js CSS transition
                
                // Verify the dark background overlay successfully appears on the screen
                ->assertVisible('.backdrop-blur-sm'); 
    });
});

test('user profile dropdown opens and displays logout', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/dashboard')
                ->assertPathIs('/dashboard')
                
                // Click the user profile avatar image in the top right
                ->click('header img[alt="User"]')
                ->pause(400) // Wait for the Alpine.js dropdown transition
                
                // Verify the dropdown reveals the universally visible logout button
                ->assertSee('Log Out');
    });
});