<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\InquiryLog;
use App\Models\InquiryItem;
use App\Models\ProductService;
use App\Models\Customer;
use App\Models\User;
use App\Models\LeadSource; 
use App\Models\Location; // <--- ADDED LOCATION MODEL
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Exports\InquiriesExport; 
use Maatwebsite\Excel\Facades\Excel; 
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class InquiryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view inquiries', only: ['index', 'show', 'activity']),
            new Middleware('can:create inquiries', only: ['create', 'store']),
            new Middleware('can:edit inquiries', only: ['edit', 'update']),
            new Middleware('can:delete inquiries', only: ['destroy']),
            new Middleware('can:export inquiries', only: ['export']),
            new Middleware('can:manage inquiry logs', only: ['storeLog', 'updateLog', 'destroyLog']),
            new Middleware('can:convert inquiries', only: ['convertToBooking']),
        ];
    }

    public function index(Request $request)
    {
        // ADDED: location relationship
        $query = Inquiry::with(['customer', 'assignedStaff', 'logs', 'bookings', 'leadSource', 'location']);

        // ADDED: Filter by Active Location Session (unless Super Admin selects "All")
        if (session('active_location_id') !== 'all') {
            $query->where('location_id', session('active_location_id'));
        }

        // 0. Fetch data for dropdown filters
        $staffMembers = User::where('role', 'staff')->where('status', 1)->get(['id', 'name']);
        $leadSources = LeadSource::where('status', 1)->orderBy('name')->get(['id', 'name']);
        $customers = Customer::orderBy('name')->get(['id', 'name']);

        // 1. Global Quick Search (Auto-Search)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('customer', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                         ->orWhere('mobile', 'like', "%{$search}%");
                })
                ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        // 2. Advanced Filters
        if ($request->filled('start_date')) {
            $query->whereDate('primary_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('primary_date', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->whereIn('status', ['New', 'In Progress', 'Slot Reserved']);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('lead_source_id')) {
            $query->where('lead_source_id', $request->lead_source_id);
        }

        if ($request->filled('staff_id')) {
            $query->where('assigned_staff_id', $request->staff_id);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $inquiries = $query->latest()->paginate(10);
        
        return view('inquiries.index', compact('inquiries', 'staffMembers', 'leadSources', 'customers'));
    }

    /**
     * Export filtered inquiries to Excel.
     */
    public function export(Request $request)
    {
        $query = Inquiry::with(['customer', 'assignedStaff', 'leadSource', 'location']);

        // ADDED: Filter by Active Location Session
        if (session('active_location_id') !== 'all') {
            $query->where('location_id', session('active_location_id'));
        }

        // Apply exactly the same filters as the index method
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('customer', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                         ->orWhere('mobile', 'like', "%{$search}%");
                })
                ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('primary_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('primary_date', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->whereIn('status', ['New', 'In Progress', 'Slot Reserved']);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('lead_source_id')) {
            $query->where('lead_source_id', $request->lead_source_id);
        }

        if ($request->filled('staff_id')) {
            $query->where('assigned_staff_id', $request->staff_id);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $inquiries = $query->latest()->get();

        return Excel::download(new InquiriesExport($inquiries), 'inquiries_report_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    public function create()
    {
        // FIX: Super Admins should see all active locations. Regular staff only see their assigned locations.
        $locations = Auth::user()->hasRole('Super Admin') 
            ? Location::where('is_active', true)->orderBy('name')->get() 
            : Auth::user()->locations()->where('is_active', true)->orderBy('name')->get();
            
        $staffMembers = User::where('role', 'staff')->where('status', 1)->get();
        $leadSources = LeadSource::where('status', 1)->orderBy('name')->get();
        $products = ProductService::where('is_active', true)->orderBy('name')->get();
        
        return view('inquiries.create', compact('locations', 'staffMembers', 'leadSources', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id', // <--- ADDED VALIDATION
            'name' => 'required|string',
            'mobile' => 'required|string',
            'primary_date' => 'required|date',
            'from_time' => 'required',
            'to_time' => 'required',
            'items' => 'nullable|array',
            'items.*.product_service_id' => 'required|exists:product_services,id',
            'items.*.quantity' => 'required|numeric|min:0.1'
        ]);

        $customer = Customer::firstOrCreate(
            ['mobile' => $request->mobile],
            [
                'name' => $request->name,
                'email' => $request->email,
                'business_name' => $request->business_name,
                'status' => 1
            ]
        );

        $start = Carbon::parse($request->from_time);
        $end = Carbon::parse($request->to_time);
        if ($end->lessThan($start)) {
            $end->addDay();
        }
        $hours = abs($end->diffInMinutes($start)) / 60;

        $inquiry = Inquiry::create([
            'location_id' => $request->location_id, // <--- ADDED SAVE LOGIC
            'customer_id' => $customer->id,
            'business_name' => $request->business_name,
            'lead_source_id' => $request->lead_source_id,
            'primary_date' => $request->primary_date,
            'alternate_date' => $request->alternate_date,
            'from_time' => $request->from_time,
            'to_time' => $request->to_time,
            'total_hours' => $hours,
            'budget' => $request->budget,
            'assigned_staff_id' => $request->assigned_staff_id,
            'status' => $request->status ?? 'New',
            'follow_up_date' => $request->follow_up_date
        ]);

        if ($request->has('items')) {
            foreach ($request->items as $item) {
                $product = ProductService::find($item['product_service_id']);
                if($product) {
                    $gstRate = $product->gst_rate ?? 0;
                    $baseTotal = $item['price'] * $item['quantity'];
                    $gstAmount = $baseTotal * ($gstRate / 100);
                    $finalTotal = $baseTotal + $gstAmount;

                    InquiryItem::create([
                        'inquiry_id' => $inquiry->id,
                        'product_service_id' => $product->id,
                        'item_name' => $product->name, 
                        'unit_price' => $item['price'], 
                        'quantity' => $item['quantity'],
                        'gst_rate' => $gstRate,
                        'gst_amount' => $gstAmount,
                        'total' => $finalTotal
                    ]);
                }
            }
        }

        InquiryLog::create([
            'inquiry_id' => $inquiry->id,
            'user_id' => Auth::id(),
            'type' => 'System',
            'message' => 'Inquiry created.',
            'log_date' => now(),
            'log_time' => now()
        ]);

        return redirect()->route('inquiries.index')->with('success', 'Inquiry added successfully.');
    }

    public function show($id)
    {
        // ADDED: location relationship
        $inquiry = Inquiry::with(['customer', 'assignedStaff', 'leadSource', 'items', 'location'])->findOrFail($id);
        return view('inquiries.show', compact('inquiry'));
    }

    public function activity($id)
    {
        $inquiry = Inquiry::with(['customer', 'assignedStaff', 'logs.user'])->findOrFail($id);
        return view('inquiries.activity', compact('inquiry'));
    }

    public function edit(Inquiry $inquiry)
    {
        // FIX: Super Admins should see all active locations. Regular staff only see their assigned locations.
        $locations = Auth::user()->hasRole('Super Admin') 
            ? Location::where('is_active', true)->orderBy('name')->get() 
            : Auth::user()->locations()->where('is_active', true)->orderBy('name')->get();
            
        $staffMembers = User::where('role', 'staff')->where('status', 1)->get();
        $leadSources = LeadSource::where('status', 1)->orderBy('name')->get();
        $products = ProductService::where('is_active', true)->orderBy('name')->get();
        $inquiry->load(['items.productService']); 
        
        return view('inquiries.edit', compact('inquiry', 'locations', 'staffMembers', 'leadSources', 'products'));
    }

    public function update(Request $request, Inquiry $inquiry)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id', // <--- ADDED VALIDATION
            'primary_date' => 'required|date',
            'from_time' => 'required',
            'to_time' => 'required',
            'status' => 'required|string',
            'items' => 'nullable|array',
        ]);

        $start = Carbon::parse($request->from_time);
        $end = Carbon::parse($request->to_time);
        if ($end->lessThan($start)) {
            $end->addDay();
        }
        $hours = abs($end->diffInMinutes($start)) / 60;

        if ($inquiry->status !== $request->status) {
            InquiryLog::create([
                'inquiry_id' => $inquiry->id,
                'user_id' => Auth::id(),
                'type' => 'Status Change',
                'message' => "Status changed from {$inquiry->status} to {$request->status}",
                'log_date' => now(),
                'log_time' => now()
            ]);
        }

        $inquiry->update([
            'location_id' => $request->location_id, // <--- ADDED SAVE LOGIC
            'business_name' => $request->business_name,
            'lead_source_id' => $request->lead_source_id,
            'primary_date' => $request->primary_date,
            'alternate_date' => $request->alternate_date,
            'from_time' => $request->from_time,
            'to_time' => $request->to_time,
            'total_hours' => $hours,
            'budget' => $request->budget,
            'assigned_staff_id' => $request->assigned_staff_id,
            'status' => $request->status,
            'follow_up_date' => $request->follow_up_date
        ]);

        $inquiry->customer->update([
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'business_name' => $request->business_name
        ]);

        InquiryItem::where('inquiry_id', $inquiry->id)->delete();
        
        if ($request->has('items')) {
            foreach ($request->items as $item) {
                $product = ProductService::find($item['product_service_id']);
                if($product) {
                    $gstRate = $product->gst_rate ?? 0;
                    $baseTotal = $item['price'] * $item['quantity'];
                    $gstAmount = $baseTotal * ($gstRate / 100);
                    $finalTotal = $baseTotal + $gstAmount;

                    InquiryItem::create([
                        'inquiry_id' => $inquiry->id,
                        'product_service_id' => $product->id,
                        'item_name' => $product->name,
                        'unit_price' => $item['price'],
                        'quantity' => $item['quantity'],
                        'gst_rate' => $gstRate,
                        'gst_amount' => $gstAmount,
                        'total' => $finalTotal
                    ]);
                }
            }
        }

        return redirect()->route('inquiries.index')->with('success', 'Inquiry updated successfully.');
    }

    public function destroy(Inquiry $inquiry)
    {
        $inquiry->delete();
        return redirect()->route('inquiries.index')->with('success', 'Inquiry deleted successfully.');
    }

    public function storeLog(Request $request, $id)
    {
        $request->validate(['type' => 'required', 'log_date' => 'required', 'log_time' => 'required']);
        $inquiry = Inquiry::findOrFail($id);
        InquiryLog::create([
            'inquiry_id' => $id, 'user_id' => Auth::id(), 'type' => $request->type,
            'message' => $request->message, 'log_date' => $request->log_date, 'log_time' => $request->log_time
        ]);
        if ($request->filled('next_follow_up')) $inquiry->follow_up_date = $request->next_follow_up;
        if ($request->filled('update_status')) $inquiry->status = $request->update_status;
        $inquiry->save();
        return redirect()->back()->with('success', 'Activity logged successfully.');
    }

    public function updateLog(Request $request, $id)
    {
        $log = InquiryLog::findOrFail($id);
        $log->update(['type' => $request->type, 'message' => $request->message]);
        return redirect()->back()->with('success', 'Log updated successfully.');
    }

    public function destroyLog($id)
    {
        InquiryLog::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Log deleted successfully.');
    }
    
    public function convertToBooking(Request $request, $id) {
         return redirect()->route('bookings.create', ['inquiry_id' => $id]);
    }
}