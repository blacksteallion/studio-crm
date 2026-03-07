<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Location;
use App\Models\Customer;
use App\Models\Inquiry;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class MultiBranchIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Spatie Roles & Permissions for the test environment
        Permission::firstOrCreate(['name' => 'view inquiries']);
        Permission::firstOrCreate(['name' => 'view dashboard']);
        
        $staffRole = Role::firstOrCreate(['name' => 'Staff']);
        $staffRole->givePermissionTo(['view inquiries', 'view dashboard']);

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdminRole->givePermissionTo(['view inquiries', 'view dashboard']);
    }

    public function test_session_defaults_to_assigned_location_for_staff()
    {
        // Setup: Create a location and assign a staff member to it
        $location = Location::create(['name' => 'Main Studio', 'is_active' => true]);
        $staff = User::factory()->create(['role' => 'staff']);
        $staff->assignRole('Staff');
        $staff->locations()->attach($location->id);

        // Action: Staff logs in and visits the dashboard
        $this->actingAs($staff);
        $this->get('/dashboard'); 

        // Assert: The middleware automatically set their session to their assigned location
        $this->assertEquals($location->id, session('active_location_id'));
    }

    public function test_session_defaults_to_global_for_super_admin()
    {
        // Setup: Create a Super Admin
        $superAdmin = User::factory()->create(['role' => 'admin']);
        $superAdmin->assignRole('Super Admin');

        // Action: Admin logs in and visits the dashboard
        $this->actingAs($superAdmin);
        $this->get('/dashboard');

        // Assert: The middleware automatically grants them 'all' location access
        $this->assertEquals('all', session('active_location_id'));
    }

    public function test_staff_can_only_see_data_from_their_active_location()
    {
        // Setup: Create two distinct locations
        $locationA = Location::create(['name' => 'Studio A', 'is_active' => true]);
        $locationB = Location::create(['name' => 'Studio B', 'is_active' => true]);

        // Setup: Create a dummy customer
        $customer = Customer::create([
            'name' => 'Test Customer', 
            'mobile' => '9999999999', 
            'email' => 'test@domain.com'
        ]);

        // Setup: Seed 3 Inquiries into Studio A (Added required times)
        for ($i = 0; $i < 3; $i++) {
            Inquiry::create([
                'location_id' => $locationA->id,
                'customer_id' => $customer->id,
                'status' => 'New',
                'primary_date' => now()->addDays(2),
                'from_time' => '10:00',
                'to_time' => '12:00',
                'total_hours' => 2,
            ]);
        }

        // Setup: Seed 2 Inquiries into Studio B (Added required times)
        for ($i = 0; $i < 2; $i++) {
            Inquiry::create([
                'location_id' => $locationB->id,
                'customer_id' => $customer->id,
                'status' => 'New',
                'primary_date' => now()->addDays(2),
                'from_time' => '10:00',
                'to_time' => '12:00',
                'total_hours' => 2,
            ]);
        }

        // Setup: Create Staff member restricted to Studio A
        $staffA = User::factory()->create(['role' => 'staff']);
        $staffA->assignRole('Staff');
        $staffA->locations()->attach($locationA->id);

        // Action: Staff A views the Inquiry List
        $this->actingAs($staffA);
        $this->get('/dashboard'); // Boot the session middleware
        $response = $this->get('/inquiries');

        // Assert: The page loaded successfully
        $response->assertStatus(200);

        // THE ULTIMATE TEST: Ensure only 3 inquiries were loaded from the database, completely ignoring Studio B's records.
        $response->assertViewHas('inquiries', function ($inquiries) {
            return $inquiries->total() === 3; 
        });
    }

    public function test_super_admin_can_see_data_from_all_locations()
    {
        // Setup: Create two distinct locations & a dummy customer
        $locationA = Location::create(['name' => 'Studio A', 'is_active' => true]);
        $locationB = Location::create(['name' => 'Studio B', 'is_active' => true]);
        $customer = Customer::create(['name' => 'Test', 'mobile' => '9999999998', 'email' => 'test2@domain.com']);

        // Setup: 1 Inquiry in Studio A, 1 Inquiry in Studio B
        Inquiry::create([
            'location_id' => $locationA->id, 'customer_id' => $customer->id, 'status' => 'New', 
            'primary_date' => now(), 'from_time' => '10:00', 'to_time' => '12:00', 'total_hours' => 2
        ]);
        
        Inquiry::create([
            'location_id' => $locationB->id, 'customer_id' => $customer->id, 'status' => 'New', 
            'primary_date' => now(), 'from_time' => '10:00', 'to_time' => '12:00', 'total_hours' => 2
        ]);

        // Setup: Create Super Admin
        $superAdmin = User::factory()->create(['role' => 'admin']);
        $superAdmin->assignRole('Super Admin');

        // Action: Admin views the Inquiry List
        $this->actingAs($superAdmin);
        $this->get('/dashboard'); 
        $response = $this->get('/inquiries');
        
        // Assert: Admin sees ALL 2 records because their session is 'all'
        $response->assertViewHas('inquiries', function ($inquiries) {
            return $inquiries->total() === 2; 
        });
    }
}