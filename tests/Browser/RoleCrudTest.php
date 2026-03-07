<?php

use App\Models\User;
use Laravel\Dusk\Browser;

beforeEach(function () {
    $this->user = User::first(); 
});

test('roles index page renders correctly', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/roles')
                ->assertPathIs('/roles')
                ->assertSee('System Roles')
                // Verify the undeletable system default is present
                ->assertSee('Super Admin') 
                ->assertSee('Create Role');
    });
});

test('role form catches missing required fields', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->user)
                ->resize(1920, 1080)
                ->visit('/roles/create')
                ->press('Save Role')
                // Verify validation blocked the submission (Name is required)
                ->assertPathIs('/roles/create'); 
    });
});

test('user can create edit and delete a role', function () {
    $this->browse(function (Browser $browser) {
        $uniqueRole = 'Dusk Manager ' . rand(1000, 9999);
        $updatedRole = $uniqueRole . ' V2';

        $browser->loginAs($this->user)
                ->resize(1920, 1080);

        // --- 1. CREATE ROLE ---
        $browser->visit('/roles/create')
                ->type('name', $uniqueRole);

        // Use JS to trigger the hidden "Select All" checkbox for the first permission group
        $browser->script("
            let selectAllBtn = document.querySelector('.select-all-btn');
            if(selectAllBtn) {
                selectAllBtn.checked = true;
                selectAllBtn.dispatchEvent(new Event('change', { bubbles: true }));
            }
        ");

        $browser->pause(500)
                ->press('Save Role')
                ->waitForLocation('/roles', 5)
                ->assertSee($uniqueRole);

        // --- 2. SEARCH AND EDIT ROLE ---
        // Dynamically find the row containing our new role and click its specific Edit button
        $browser->script("
            let rows = Array.from(document.querySelectorAll('tr'));
            let targetRow = rows.find(r => r.innerText.includes('{$uniqueRole}'));
            if(targetRow) {
                let editBtn = targetRow.querySelector('a[title=\"Edit Role\"]');
                if(editBtn) editBtn.click();
            }
        ");

        $browser->waitForText('Edit Role', 5)
                ->clear('name')
                ->type('name', $updatedRole)
                ->press('Update Role')
                ->waitForLocation('/roles', 5)
                ->assertSee($updatedRole);

        // --- 3. DELETE THE UPDATED ROLE ---
        // Dynamically find the updated row and click its specific Delete button
        $browser->script("
            let rows = Array.from(document.querySelectorAll('tr'));
            let targetRow = rows.find(r => r.innerText.includes('{$updatedRole}'));
            if(targetRow) {
                let deleteBtn = targetRow.querySelector('button[title=\"Delete Role\"]');
                if(deleteBtn) deleteBtn.click();
            }
        ");

        $browser->pause(500)
                ->press('Yes, Proceed') // Click the confirmation modal
                ->pause(1500)
                
                // Verify it is completely removed from the table
                ->assertDontSee($updatedRole);
    });
});