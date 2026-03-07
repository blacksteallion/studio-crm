<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Booking;
use App\Models\ProductService;
use App\Models\Setting;
use App\Models\Location; // <--- ADDED LOCATION MODEL
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view orders', only: ['index', 'show']),
            new Middleware('can:create orders', only: ['create', 'store']),
            new Middleware('can:edit orders', only: ['edit', 'update']),
            new Middleware('can:delete orders', only: ['destroy']),
            new Middleware('can:export orders', only: ['export']),
            new Middleware('can:download order pdf', only: ['downloadPdf']),
        ];
    }

    public function index(Request $request)
    {
        $query = Order::with(['customer', 'location']);

        $customers = Customer::orderBy('name')->get(['id', 'name']);

        $query = $this->applyFilters($query, $request);

        $orders = $query->latest('invoice_date')->paginate(10);
        
        return view('orders.index', compact('orders', 'customers'));
    }

    public function export(Request $request)
    {
        $query = Order::with(['customer', 'items', 'location']);
        
        $query = $this->applyFilters($query, $request);
        
        $orders = $query->latest('invoice_date')->get();

        return Excel::download(new OrdersExport($orders), 'orders_report_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    private function applyFilters($query, $request)
    {
        if (session('active_location_id') !== 'all') {
            $query->where('location_id', session('active_location_id'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('invoice_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('invoice_date', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            if ($request->status == 'pending') {
                $query->whereIn('status', ['Unpaid', 'Partially Paid']);
            } elseif ($request->status == 'overdue') {
                $query->where('status', '!=', 'Paid')->whereDate('due_date', '<', Carbon::today());
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('min_amount')) {
            $query->where('total_amount', '>=', $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('total_amount', '<=', $request->max_amount);
        }

        return $query;
    }

    public function create(Request $request)
    {
        // FIX: Admin Bypass
        $locations = Auth::user()->hasRole('Super Admin') 
            ? Location::where('is_active', true)->orderBy('name')->get() 
            : Auth::user()->locations()->where('is_active', true)->orderBy('name')->get();
            
        $booking = null;
        $customer = null;
        $prefilledItems = collect([]); 
        
        if ($request->has('booking_id')) {
            $booking = Booking::with(['customer', 'items'])->find($request->booking_id);
            if ($booking) {
                $customer = $booking->customer;
                
                if ($booking->items->isNotEmpty()) {
                    $prefilledItems = $booking->items->map(function($item) {
                        return [
                            'product_service_id' => $item->product_service_id,
                            'name' => $item->item_name,
                            'qty' => $item->quantity,
                            'price' => $item->unit_price,
                            'gst_rate' => $item->gst_rate
                        ];
                    });
                }
            }
        }

        $customers = Customer::orderBy('name')->get();
        $products = ProductService::where('is_active', true)->orderBy('name')->get();
        
        $prefix = Setting::get('invoice_prefix', 'INV-');
        $nextId = Order::max('id') + 1;
        $invoiceNumber = $prefix . date('dmY') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $defaultNotes = Setting::get('default_invoice_notes');

        return view('orders.create', compact('locations', 'booking', 'customer', 'customers', 'invoiceNumber', 'products', 'prefilledItems', 'defaultNotes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_service_id' => 'required|exists:product_services,id',
            'items.*.qty' => 'required|numeric|min:0.1',
        ]);

        $prefix = Setting::get('invoice_prefix', 'INV-');
        $nextId = Order::max('id') + 1;
        $formattedInvoiceNumber = $prefix . date('dmY') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $calculatedSubtotal = 0;
        $calculatedTax = 0;
        $orderItemsData = [];

        foreach ($request->items as $item) {
            $product = ProductService::find($item['product_service_id']);
            if ($product) {
                $qty = $item['qty'];
                $price = $item['price']; 
                $gstRate = $product->gst_rate ?? 0;

                $lineBase = $qty * $price;
                $lineTax = $lineBase * ($gstRate / 100);
                $lineTotal = $lineBase + $lineTax;

                $calculatedSubtotal += $lineBase;
                $calculatedTax += $lineTax;

                $orderItemsData[] = [
                    'item_name' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'gst_rate' => $gstRate,
                    'gst_amount' => $lineTax,
                    'amount' => $lineTotal
                ];
            }
        }

        $discount = $request->discount ?? 0;
        $grandTotal = max(0, $calculatedSubtotal + $calculatedTax - $discount);

        $order = Order::create([
            'location_id' => $request->location_id,
            'customer_id' => $request->customer_id,
            'booking_id' => $request->booking_id,
            'invoice_number' => $formattedInvoiceNumber, 
            'invoice_date' => $request->invoice_date,
            'due_date' => $request->due_date,
            'subtotal' => $calculatedSubtotal,
            'tax' => $calculatedTax,
            'discount' => $discount,
            'total_amount' => $grandTotal,
            'status' => 'Unpaid',
            'notes' => $request->notes
        ]);

        foreach ($orderItemsData as $data) {
            OrderItem::create([
                'order_id' => $order->id,
                'item_name' => $data['item_name'],
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'],
                'gst_rate' => $data['gst_rate'],
                'gst_amount' => $data['gst_amount'],
                'amount' => $data['amount']
            ]);
        }

        return redirect()->route('orders.index')->with('success', 'Invoice generated successfully.');
    }

    public function show($id)
    {
        $order = Order::with(['customer', 'items', 'booking', 'location'])->findOrFail($id);
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        return view('orders.show', compact('order', 'settings'));
    }

    public function edit(Order $order)
    {
        if ($order->status == 'Paid') {
            return back()->with('error', 'Paid invoices cannot be edited. Please delete payments first.');
        }

        // FIX: Admin Bypass
        $locations = Auth::user()->hasRole('Super Admin') 
            ? Location::where('is_active', true)->orderBy('name')->get() 
            : Auth::user()->locations()->where('is_active', true)->orderBy('name')->get();
            
        $order->load(['items', 'customer', 'location']);
        $customers = Customer::orderBy('name')->get();
        $products = ProductService::where('is_active', true)->orderBy('name')->get();

        return view('orders.edit', compact('order', 'locations', 'customers', 'products'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_service_id' => 'required|exists:product_services,id',
            'items.*.qty' => 'required|numeric|min:0.1',
        ]);

        $calculatedSubtotal = 0;
        $calculatedTax = 0;
        $orderItemsData = [];

        foreach ($request->items as $item) {
            $product = ProductService::find($item['product_service_id']);
            if ($product) {
                $qty = $item['qty'];
                $price = $item['price'];
                $gstRate = $product->gst_rate ?? 0;

                $lineBase = $qty * $price;
                $lineTax = $lineBase * ($gstRate / 100);
                $lineTotal = $lineBase + $lineTax;

                $calculatedSubtotal += $lineBase;
                $calculatedTax += $lineTax;

                $orderItemsData[] = [
                    'order_id' => $order->id,
                    'item_name' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'gst_rate' => $gstRate,
                    'gst_amount' => $lineTax,
                    'amount' => $lineTotal
                ];
            }
        }

        $discount = $request->discount ?? 0;
        $grandTotal = max(0, $calculatedSubtotal + $calculatedTax - $discount);

        $order->update([
            'location_id' => $request->location_id,
            'customer_id' => $request->customer_id,
            'invoice_date' => $request->invoice_date,
            'due_date' => $request->due_date,
            'subtotal' => $calculatedSubtotal,
            'tax' => $calculatedTax,
            'discount' => $discount,
            'total_amount' => $grandTotal,
            'notes' => $request->notes
        ]);

        OrderItem::where('order_id', $order->id)->delete();
        foreach ($orderItemsData as $data) {
            OrderItem::create($data);
        }

        return redirect()->route('orders.index')->with('success', 'Invoice updated successfully.');
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);

        if ($order->payments()->exists()) {
            return back()->with('error', 'Cannot delete this invoice because it has associated payment entries. Delete payments first.');
        }

        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Invoice deleted successfully.');
    }

    public function downloadPdf($id)
    {
        $order = Order::with(['customer', 'items', 'payments', 'location'])->findOrFail($id);
        
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        $pdf = Pdf::loadView('orders.pdf', compact('order', 'settings'));
        return $pdf->download('Invoice-' . $order->invoice_number . '.pdf');
    }
}