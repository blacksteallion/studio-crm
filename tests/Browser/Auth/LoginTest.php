<?php

use App\Models\User;
use Laravel\Dusk\Browser;

test('login page UI renders correctly on desktop', function () {
    $this->browse(function (Browser $browser) {
        $browser->resize(1920, 1080) // 1080p Desktop Screen
                ->visit('/login')
                ->assertSee('Welcome Back!')
                ->assertSee('Please sign in to continue')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]')
                ->assertPresent('button[type="submit"]')
                // The elements are hidden by Tailwind CSS, but we verify they are 
                // successfully loaded into the DOM so the JS can reveal them.
                ->assertPresent('#installPwaContainer') 
                ->assertPresent('#iosInstallPrompt');
    });
});

test('login page UI renders correctly on mobile', function () {
    $this->browse(function (Browser $browser) {
        $browser->resize(390, 844) // iPhone 12 Pro dimensions
                ->visit('/login')
                ->assertSee('Welcome Back!')
                ->assertPresent('input[name="email"]');
    });
});

test('UI displays error styling when authentication fails', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
                ->type('email', 'wrong@company.com')
                ->type('password', 'invalidpassword')
                ->press('Sign In')
                ->waitForText('Authentication Failed') // The specific red box we designed
                ->assertSee('Authentication Failed')
                ->assertPathIs('/login');
    });
});

test('user can log in and UI redirects to dashboard', function () {
    // Safely find the user or create them, updating the password to match the test
    User::updateOrCreate(
        ['email' => 'admin@tcstudio.com'],
        ['name' => 'Admin Test', 'password' => bcrypt('SecurePassword123!')]
    );

    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
                ->type('email', 'admin@tcstudio.com')
                ->type('password', 'SecurePassword123!')
                ->press('Sign In')
                ->waitForLocation('/dashboard')
                ->assertPathIs('/dashboard');
    });
});