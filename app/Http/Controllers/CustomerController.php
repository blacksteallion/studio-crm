<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Exports\CustomersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CustomerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view customers', only: ['index', 'show']),
            new Middleware('can:create customers', only: ['create', 'store']),
            new Middleware('can:edit customers', only: ['edit', 'update']),
            new Middleware('can:delete customers', only: ['destroy']),
            new Middleware('can:export customers', only: ['export']),
            new Middleware('can:toggle customer status', only: ['toggleStatus']),
        ];
    }

    // Helper to get Indian States
    private function getIndianStates()
    {
        return [
            'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat', 'Haryana', 
            'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 
            'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu', 
            'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal', 
            'Andaman and Nicobar Islands', 'Chandigarh', 'Dadra and Nagar Haveli and Daman and Diu', 
            'Delhi', 'Jammu and Kashmir', 'Ladakh', 'Lakshadweep', 'Puducherry'
        ];
    }

    public function index(Request $request)
    {
        $query = Customer::withCount(['inquiries', 'bookings', 'orders']);

        $query = $this->applyFilters($query, $request);

        $customers = $query->latest()->paginate(10);
        return view('customers.index', compact('customers'));
    }

    public function export(Request $request)
    {
        $query = Customer::withCount(['inquiries', 'bookings', 'orders']);
        
        $query = $this->applyFilters($query, $request);
        
        $customers = $query->latest()->get();

        return Excel::download(new CustomersExport($customers), 'customers_report_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    private function applyFilters($query, $request)
    {
        // 1. Global Quick Search (Auto-Search)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        // 2. Advanced Filters
        if ($request->filled('f_name')) {
            $query->where('name', 'like', '%' . $request->f_name . '%');
        }
        if ($request->filled('f_business')) {
            $query->where('business_name', 'like', '%' . $request->f_business . '%');
        }
        if ($request->filled('f_email')) {
            $query->where('email', 'like', '%' . $request->f_email . '%');
        }
        if ($request->filled('f_mobile')) {
            $query->where('mobile', 'like', '%' . $request->f_mobile . '%');
        }
        if ($request->filled('f_status')) {
            $query->where('status', $request->f_status);
        }

        return $query;
    }

    public function create()
    {
        $states = $this->getIndianStates();
        return view('customers.create', compact('states'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_name' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'mobile' => 'required|digits:10|unique:customers,mobile',
            'email' => 'nullable|email|unique:customers,email',
            'website' => 'nullable|url|max:255',
            'gst_number' => 'nullable|string|max:20',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['status'] = $request->has('status') ? 1 : 0;
        $data['country'] = $data['country'] ?? 'India'; 

        Customer::create($data);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function show($id)
    {
        $customer = Customer::with(['inquiries', 'bookings', 'orders'])->findOrFail($id);
        return view('customers.show', compact('customer'));
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        $states = $this->getIndianStates();
        return view('customers.edit', compact('customer', 'states'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'business_name' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'mobile' => 'required|digits:10|unique:customers,mobile,' . $id,
            'email' => 'nullable|email|unique:customers,email,' . $id,
            'website' => 'nullable|url|max:255',
            'gst_number' => 'nullable|string|max:20',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['status'] = $request->has('status') ? 1 : 0;

        $customer->update($data);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy($id)
    {
        $customer = Customer::withCount(['inquiries', 'bookings', 'orders'])->findOrFail($id);

        if ($customer->inquiries_count > 0 || $customer->bookings_count > 0 || $customer->orders_count > 0) {
            return back()->with('error', 'Cannot delete this customer because they have associated records.');
        }

        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->status = !$customer->status;
        $customer->save();

        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }
}