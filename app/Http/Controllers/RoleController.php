<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    // Helper to group permissions beautifully for the UI
    private function getGroupedPermissions()
    {
        $allPermissions = Permission::orderBy('name')->get();
        $grouped = [];

        $groupMapping = [
            'dashboard' => 'Dashboard',
            'customers' => 'Customers',
            'staff' => 'Staff',
            'inquiries' => 'Inquiries',
            'bookings' => 'Bookings',
            'orders' => 'Orders & Invoices',
            'payments' => 'Payments',
            'expenses' => 'Expenses',
            'products' => 'Products & Services',
            'reports' => 'Reports',
            'settings' => 'System Administration',
            'integrations' => 'System Administration',
            'roles' => 'System Administration',
        ];

        foreach ($allPermissions as $permission) {
            $groupAssigned = 'Other';
            foreach ($groupMapping as $keyword => $groupName) {
                if (str_contains(strtolower($permission->name), $keyword)) {
                    $groupAssigned = $groupName;
                    break;
                }
            }
            $grouped[$groupAssigned][] = $permission;
        }

        return $grouped;
    }

    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $groupedPermissions = $this->getGroupedPermissions();
        return view('roles.create', compact('groupedPermissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect()->route('roles.index')->with('error', 'The Super Admin role cannot be edited.');
        }

        $groupedPermissions = $this->getGroupedPermissions();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect()->route('roles.index')->with('error', 'The Super Admin role cannot be modified.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect()->route('roles.index')->with('error', 'The Super Admin role cannot be deleted.');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}