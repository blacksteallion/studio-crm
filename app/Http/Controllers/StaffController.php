<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Location; // <--- ADDED
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Exports\StaffExport;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StaffController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view staff', only: ['index', 'show']),
            new Middleware('can:create staff', only: ['create', 'store']),
            new Middleware('can:edit staff', only: ['edit', 'update']),
            new Middleware('can:delete staff', only: ['destroy']),
            new Middleware('can:export staff', only: ['export']),
            new Middleware('can:toggle staff status', only: ['toggleStatus']),
        ];
    }

    public function index(Request $request)
    {
        $query = User::where('role', 'staff')->with(['roles', 'locations']); // Eager load locations

        $query = $this->applyFilters($query, $request);

        $staffMembers = $query->latest()->paginate(10);
        return view('staff.index', compact('staffMembers'));
    }

    public function export(Request $request)
    {
        $query = User::where('role', 'staff')->with(['roles', 'locations']);
        $query = $this->applyFilters($query, $request);
        $staffMembers = $query->latest()->get();

        return Excel::download(new StaffExport($staffMembers), 'staff_report_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    private function applyFilters($query, $request)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $status = $request->status == 'active' ? 1 : 0;
            $query->where('status', $status);
        }
        return $query;
    }

    public function create()
    {
        $roles = Role::where('name', '!=', 'Super Admin')->get();
        $locations = Location::where('is_active', true)->get(); // <--- ADDED
        
        return view('staff.create', compact('roles', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'system_role' => 'required|exists:roles,name',
            'locations' => 'required|array', // <--- Ensure locations are assigned
            'locations.*' => 'exists:locations,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->except(['photo', 'password', 'system_role', 'locations']);
        $data['password'] = Hash::make($request->password);
        $data['role'] = 'staff'; 
        
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('staff_photos', $filename, 'public');
            $data['photo'] = $filename;
        }

        $staff = User::create($data);
        
        // Assign Spatie Role
        $staff->assignRole($request->system_role);

        // Assign Locations
        $staff->locations()->sync($request->locations);

        return redirect()->route('staff.index')->with('success', 'Staff member added successfully.');
    }

    public function show($id)
    {
        $staff = User::with(['roles', 'locations'])->findOrFail($id);
        return view('staff.show', compact('staff'));
    }

    public function edit($id)
    {
        $staff = User::with(['roles', 'locations'])->findOrFail($id);
        $roles = Role::where('name', '!=', 'Super Admin')->get();
        $locations = Location::where('is_active', true)->get(); // <--- ADDED

        return view('staff.edit', compact('staff', 'roles', 'locations'));
    }

    public function update(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:users,email,' . $staff->id,
            'password' => 'nullable|string|min:8',
            'system_role' => 'required|exists:roles,name',
            'locations' => 'required|array', // <--- Ensure locations are assigned
            'locations.*' => 'exists:locations,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->except(['photo', 'password', 'system_role', 'locations']);
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            if ($staff->photo && Storage::disk('public')->exists('staff_photos/' . $staff->photo)) {
                Storage::disk('public')->delete('staff_photos/' . $staff->photo);
            }
            $file = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('staff_photos', $filename, 'public');
            $data['photo'] = $filename;
        }

        $staff->update($data);
        
        // Sync Roles and Locations
        $staff->syncRoles([$request->system_role]);
        $staff->locations()->sync($request->locations);

        return redirect()->route('staff.index')->with('success', 'Staff updated successfully.');
    }

    public function destroy($id)
    {
        $staff = User::findOrFail($id);
        if ($staff->photo && Storage::disk('public')->exists('staff_photos/' . $staff->photo)) {
            Storage::disk('public')->delete('staff_photos/' . $staff->photo);
        }
        $staff->delete();
        return redirect()->route('staff.index')->with('success', 'Staff deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $staff = User::findOrFail($id);
        $staff->status = !$staff->status;
        $staff->save();
        return back()->with('success', 'Staff status updated.');
    }
}