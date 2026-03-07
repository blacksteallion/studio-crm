<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    $this->user = User::first(); 
});

test('financial report renders charts and ledger correctly', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/reports') 
                ->assertPathIs('/reports')
                ->assertSee('Financial Overview')
                
                // We assert normal headings instead of CSS uppercase text 
                // to prevent Headless Chrome text-transform failures!
                ->assertSee('Income vs Expense Trend')
                ->assertSee('Detailed Financial Ledger')
                
                // Verify static ApexCharts container and table render
                ->assertPresent('#pnlChart')
                ->assertPresent('table'); 
    });
});

test('growth report renders cohort matrix and charts correctly', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/reports/growth')
                ->assertPathIs('/reports/growth')
                ->assertSee('Growth & Inquiries')
                ->assertSee('Inquiry Conversion by Source')
                
                ->assertPresent('#trendChart')
                ->assertPresent('table'); 
    });
});

test('operations report renders staff matrix and charts correctly', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/reports/operations')
                ->assertPathIs('/reports/operations')
                ->assertSee('Operations & Bookings')
                ->assertSee('Staff Performance Matrix')
                
                // We assert the heading exists instead of #serviceChart, 
                // because the chart itself is hidden when the database is empty!
                ->assertSee('Top Services by Booking Volume')
                ->assertPresent('#trendChart')
                ->assertPresent('table'); 
    });
});

test('reports mobile filter drawer toggles correctly', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(390, 844) // iPhone dimensions
                ->visit('/reports')
                
                // Click the mobile filter toggle button
                ->click('button[title="Filter Dates"]')
                ->pause(500)
                
                // Verify the wrapper block becomes visible
                ->assertVisible('div[x-show="showFilters"]')
                
                // Because Flatpickr's "altInput: true" hides the original inputs, 
                // we use assertPresent instead of assertVisible!
                ->assertPresent('div[x-show="showFilters"] input[name="start_date"]')
                ->assertPresent('div[x-show="showFilters"] input[name="end_date"]')
                ->assertSee('Apply');
    });
});