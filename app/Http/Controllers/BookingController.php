<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Customer;
use App\Models\User;
use App\Models\Inquiry;
use App\Models\InquiryLog;
use App\Models\ProductService;
use App\Models\Location;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BookingsExport;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class BookingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view bookings', only: ['index', 'show']),
            new Middleware('can:create bookings', only: ['create', 'store']),
            new Middleware('can:edit bookings', only: ['edit', 'update']),
            new Middleware('can:delete bookings', only: ['destroy']),
            new Middleware('can:export bookings', only: ['export']),
            new Middleware('can:view booking calendar', only: ['calendar']),
        ];
    }

    public function index(Request $request)
    {
        $query = Booking::with(['customer', 'assignedStaff', 'inquiry.leadSource', 'location']);

        $staffMembers = User::where('role', 'staff')->where('status', 1)->get(['id', 'name']);
        $customers = Customer::orderBy('name')->get(['id', 'name']);

        $query = $this->applyFilters($query, $request);

        $bookings = $query->latest('booking_date')->paginate(10);
        
        return view('bookings.index', compact('bookings', 'staffMembers', 'customers'));
    }

    public function export(Request $request)
    {
        $query = Booking::with(['customer', 'assignedStaff', 'inquiry.leadSource', 'location']);
        $query = $this->applyFilters($query, $request);
        $bookings = $query->latest('booking_date')->get();

        return Excel::download(new BookingsExport($bookings), 'bookings_report_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    private function applyFilters($query, $request)
    {
        if (session('active_location_id') !== 'all') {
            $query->where('location_id', session('active_location_id'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('booking_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('booking_date', '<=', $request->end_date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->has('date') && $request->date == 'today') {
            $query->whereDate('booking_date', Carbon::today());
        }

        return $query;
    }

    public function calendar(Request $request)
    {
        $query = Booking::with(['customer', 'assignedStaff', 'location']);

        $activeLocation = $request->input('location_id', session('active_location_id'));
        if ($activeLocation !== 'all') {
            $query->where('location_id', $activeLocation);
        }

        $bookings = $query->get();
        
        // FIX: Admin Bypass
        $locations = Auth::user()->hasRole('Super Admin') 
            ? Location::where('is_active', true)->orderBy('name')->get() 
            : Auth::user()->locations()->where('is_active', true)->orderBy('name')->get();

        $events = $bookings->map(function ($booking) {
            $color = '#3b82f6'; 
            if ($booking->status == 'Completed') $color = '#10b981';
            if ($booking->status == 'Cancelled') $color = '#ef4444'; 
            if ($booking->status == 'No Show')   $color = '#f59e0b'; 

            $start = $booking->booking_date->format('Y-m-d') . 'T' . $booking->start_time->format('H:i:s');
            $end = $booking->booking_date->format('Y-m-d') . 'T' . $booking->end_time->format('H:i:s');

            return [
                'id' => $booking->id,
                'title' => $booking->customer->business_name ?? $booking->customer->name,
                'start' => $start,
                'end'   => $end,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'status' => $booking->status,
                    'company_name' => $booking->customer->business_name ?? $booking->customer->name,
                    'customer_name' => $booking->customer->name,
                    'customer_mobile' => $booking->customer->mobile,
                    'staff' => $booking->assignedStaff ? $booking->assignedStaff->name : 'Unassigned',
                    'location' => $booking->location ? $booking->location->name : 'Unassigned',
                    'notes' => $booking->notes ?? 'No additional notes.',
                    'edit_url' => route('bookings.edit', $booking->id)
                ]
            ];
        });

        return view('bookings.calendar', compact('events', 'locations'));
    }

    public function create(Request $request)
    {
        // FIX: Admin Bypass
        $locations = Auth::user()->hasRole('Super Admin') 
            ? Location::where('is_active', true)->orderBy('name')->get() 
            : Auth::user()->locations()->where('is_active', true)->orderBy('name')->get();
            
        $staffMembers = User::where('role', 'staff')->where('status', 1)->get();
        $customers = Customer::orderBy('name')->get();
        $products = ProductService::where('is_active', true)->orderBy('name')->get(); 
        
        $inquiry = null;
        $prefilledItems = collect([]); 

        if ($request->has('inquiry_id')) {
            $inquiry = Inquiry::with('items')->find($request->inquiry_id);
            if ($inquiry && $inquiry->items->isNotEmpty()) {
                $prefilledItems = $inquiry->items->map(function($item) {
                    return [
                        'product_service_id' => $item->product_service_id,
                        'price' => $item->unit_price,
                        'quantity' => $item->quantity,
                        'pricing_model' => $item->productService->pricing_model ?? 'Fixed'
                    ];
                });
            }
        }

        return view('bookings.create', compact('locations', 'staffMembers', 'customers', 'inquiry', 'products', 'prefilledItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'customer_id' => 'required|exists:customers,id',
            'booking_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'status' => 'required',
            'items' => 'nullable|array', 
            'items.*.product_service_id' => 'required|exists:product_services,id',
            'items.*.quantity' => 'required|numeric|min:0.1'
        ]);

        $bookingDate = Carbon::parse($request->booking_date);
        $bookingStart = Carbon::parse($request->booking_date . ' ' . $request->start_time);

        if ($bookingDate->isPast() && !$bookingDate->isToday()) {
            return back()->withInput()->withErrors(['booking_date' => 'You cannot schedule a booking on a past date.']);
        }

        if ($bookingStart->isPast()) {
            return back()->withInput()->withErrors(['start_time' => 'You cannot schedule a time in the past.']);
        }

        $overlap = Booking::where('location_id', $request->location_id)
            ->where('booking_date', $request->booking_date)
            ->where('status', '!=', 'Cancelled')
            ->where(function ($query) use ($request) {
                $query->where('start_time', '<', $request->end_time)
                      ->where('end_time', '>', $request->start_time);
            })
            ->exists();

        if ($overlap) {
            return back()->withInput()->withErrors(['booking_date' => 'The selected time slot overlaps with an existing booking at this location.']);
        }

        $booking = Booking::create($request->except(['items', '_token']));

        if ($request->has('items')) {
            foreach ($request->items as $item) {
                $product = ProductService::find($item['product_service_id']);
                if($product) {
                    $gstRate = $product->gst_rate ?? 0;
                    $baseTotal = $item['price'] * $item['quantity'];
                    $gstAmount = $baseTotal * ($gstRate / 100);
                    $finalTotal = $baseTotal + $gstAmount;

                    BookingItem::create([
                        'booking_id' => $booking->id,
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

        if ($request->filled('inquiry_id')) {
            $inquiry = Inquiry::find($request->inquiry_id);
            if ($inquiry) {
                $inquiry->update(['status' => 'Slot Reserved']);
                
                InquiryLog::create([
                    'inquiry_id' => $inquiry->id,
                    'user_id' => Auth::id(),
                    'type' => 'System',
                    'message' => 'Booking #' . $booking->id . ' created. Status changed to Slot Reserved.',
                    'log_date' => now(),
                    'log_time' => now()
                ]);
            }
        }

        return redirect()->route('bookings.index')->with('success', 'Booking created successfully.');
    }

    public function show($id)
    {
        $booking = Booking::with(['customer', 'assignedStaff', 'inquiry.leadSource', 'orders', 'items', 'location'])->findOrFail($id);
        return view('bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        // FIX: Admin Bypass
        $locations = Auth::user()->hasRole('Super Admin') 
            ? Location::where('is_active', true)->orderBy('name')->get() 
            : Auth::user()->locations()->where('is_active', true)->orderBy('name')->get();
            
        $booking->load(['inquiry.customer', 'items.productService', 'location']); 
        $staffMembers = User::where('role', 'staff')->where('status', 1)->get();
        $customers = Customer::orderBy('name')->get();
        $products = ProductService::where('is_active', true)->orderBy('name')->get();

        return view('bookings.edit', compact('booking', 'locations', 'staffMembers', 'customers', 'products'));
    }

    public function update(Request $request, Booking $booking)
    {
        if (in_array($booking->status, ['Cancelled', 'No Show'])) {
            return back()->with('error', 'This booking is locked (' . $booking->status . ') and cannot be edited.');
        }

        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'booking_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'status' => 'required',
            'items' => 'nullable|array',
        ]);

        $bookingDate = Carbon::parse($request->booking_date);
        $bookingStart = Carbon::parse($request->booking_date . ' ' . $request->start_time);

        if ($bookingDate->isPast() && !$bookingDate->isToday()) {
            return back()->withInput()->withErrors(['booking_date' => 'You cannot reschedule to a past date.']);
        }

        if ($bookingStart->isPast()) {
            return back()->withInput()->withErrors(['start_time' => 'You cannot reschedule to a past time.']);
        }

        $overlap = Booking::where('location_id', $request->location_id)
            ->where('booking_date', $request->booking_date)
            ->where('id', '!=', $booking->id)
            ->where('status', '!=', 'Cancelled')
            ->where(function ($query) use ($request) {
                $query->where('start_time', '<', $request->end_time)
                      ->where('end_time', '>', $request->start_time);
            })
            ->exists();

        if ($overlap) {
            return back()->withInput()->withErrors(['booking_date' => 'The selected time slot overlaps with an existing booking at this location.']);
        }

        $booking->update($request->except(['items', '_token']));

        BookingItem::where('booking_id', $booking->id)->delete();
        
        if ($request->has('items')) {
            foreach ($request->items as $item) {
                $product = ProductService::find($item['product_service_id']);
                if($product) {
                    $gstRate = $product->gst_rate ?? 0;
                    $baseTotal = $item['price'] * $item['quantity'];
                    $gstAmount = $baseTotal * ($gstRate / 100);
                    $finalTotal = $baseTotal + $gstAmount;

                    BookingItem::create([
                        'booking_id' => $booking->id,
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

        if (in_array($request->status, ['Cancelled', 'No Show']) && $booking->inquiry_id) {
            $inquiry = Inquiry::find($booking->inquiry_id);
            if ($inquiry && $inquiry->status == 'Slot Reserved') {
                $inquiry->update(['status' => 'Qualified']);
                
                InquiryLog::create([
                    'inquiry_id' => $inquiry->id,
                    'user_id' => Auth::id(),
                    'type' => 'System',
                    'message' => 'Booking #' . $booking->id . ' marked as ' . $request->status . '. Inquiry reverted to Qualified.',
                    'log_date' => now(),
                    'log_time' => now()
                ]);
            }
        }

        return redirect()->route('bookings.index')->with('success', 'Booking updated successfully.');
    }

    public function destroy(Booking $booking)
    {
        if ($booking->orders()->exists()) {
            return back()->with('error', 'Cannot delete this booking because an Order/Invoice has been generated for it.');
        }

        if ($booking->inquiry_id) {
            $inquiry = Inquiry::find($booking->inquiry_id);
            if ($inquiry && $inquiry->status == 'Slot Reserved') {
                $inquiry->update(['status' => 'Qualified']);
                
                InquiryLog::create([
                    'inquiry_id' => $inquiry->id,
                    'user_id' => Auth::id(),
                    'type' => 'System',
                    'message' => 'Booking #' . $booking->id . ' deleted. Inquiry reverted to Qualified.',
                    'log_date' => now(),
                    'log_time' => now()
                ]);
            }
        }

        $booking->delete();
        return redirect()->route('bookings.index')->with('success', 'Booking deleted.');
    }
}