<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class LocationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:manage settings'),
        ];
    }

    public function index(Request $request)
    {
        $query = Location::query();

        // 1. Fix Auto-Search Logic
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('f_name')) {
            $query->where('name', 'like', '%' . $request->f_name . '%');
        }

        if ($request->filled('f_status')) {
            $query->where('is_active', $request->f_status);
        }

        // 2. Fix Pagination (Solves the firstItem() error)
        $locations = $query->orderBy('name')->paginate(10);
        
        return view('locations.index', compact('locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:locations,name',
            'address' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
        ]);

        // 3. Fix SQL Crash: Map 'phone' input to DB column 'contact_number'
        Location::create([
            'name' => $request->name,
            'address' => $request->address,
            'contact_number' => $request->phone,
            'is_active' => true
        ]);

        return redirect()->back()->with('success', 'Studio location added successfully.');
    }

    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:locations,name,' . $location->id,
            'address' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
        ]);

        // 3. Fix SQL Crash: Map 'phone' input to DB column 'contact_number'
        $location->update([
            'name' => $request->name,
            'address' => $request->address,
            'contact_number' => $request->phone,
        ]);

        return redirect()->back()->with('success', 'Studio location updated successfully.');
    }

    public function destroy(Location $location)
    {
        if ($location->bookings()->exists() || $location->inquiries()->exists() || $location->orders()->exists()) {
            return back()->with('error', 'Cannot delete a location that has active data tied to it. Please deactivate it instead.');
        }

        $location->delete();
        return back()->with('success', 'Location deleted permanently.');
    }

    public function toggleStatus(Location $location)
    {
        $location->is_active = !$location->is_active;
        $location->save();

        // 4. Fix Toggle Status: Must return JSON for the Javascript Fetch API
        return response()->json([
            'success' => true, 
            'message' => 'Location status updated successfully.'
        ]);
    }
}