<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Exports\PaymentsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PaymentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view payments', only: ['index']),
            new Middleware('can:add payments', only: ['store']),
            new Middleware('can:delete payments', only: ['destroy']),
            new Middleware('can:export payments', only: ['export']),
        ];
    }

    public function index(Request $request)
    {
        // ADDED: order.location to prevent N+1 queries
        $query = Payment::with(['order.customer', 'order.location']);

        // 0. Fetch distinct payment methods for the filter dropdown
        $paymentMethods = Payment::select('payment_method')->distinct()->pluck('payment_method');

        $query = $this->applyFilters($query, $request);

        $payments = $query->latest('transaction_date')->paginate(15);
        
        return view('payments.index', compact('payments', 'paymentMethods'));
    }

    public function export(Request $request)
    {
        $query = Payment::with(['order.customer', 'order.location']);
        
        $query = $this->applyFilters($query, $request);
        
        $payments = $query->latest('transaction_date')->get();

        return Excel::download(new PaymentsExport($payments), 'payments_report_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    private function applyFilters($query, $request)
    {
        // --- ADDED: Filter Payments by the Active Location of their associated Order ---
        if (session('active_location_id') !== 'all') {
            $query->whereHas('order', function($q) {
                $q->where('location_id', session('active_location_id'));
            });
        }

        // 1. Global Search (Auto-Search)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('payment_method', 'like', "%{$search}%")
                  ->orWhereHas('order', function($qOrder) use ($search) {
                      $qOrder->where('invoice_number', 'like', "%{$search}%")
                             ->orWhereHas('customer', function($qCust) use ($search) {
                                 $qCust->where('name', 'like', "%{$search}%");
                             });
                  });
            });
        }

        // 2. Advanced Filters
        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }
        if ($request->has('date') && $request->date == 'today') {
            $query->whereDate('transaction_date', Carbon::today());
        }
        if ($request->has('customer_id')) {
            $query->whereHas('order', function($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            });
        }

        return $query;
    }

    public function store(Request $request, $order_id)
    {
        $order = Order::findOrFail($order_id);

        $request->validate([
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $newBalance = $order->total_amount - ($order->paid_amount + $request->amount);
        
        if ($newBalance < -1) { 
             return back()->with('error', 'Payment amount exceeds the balance due.');
        }

        Payment::create([
            'order_id' => $order->id,
            'transaction_date' => $request->transaction_date,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'reference_number' => $request->reference_number,
            'notes' => $request->notes
        ]);

        $this->updateOrderStatus($order);

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $order = $payment->order;
        
        $payment->delete();

        $this->updateOrderStatus($order);

        return back()->with('success', 'Payment deleted successfully.');
    }

    private function updateOrderStatus(Order $order)
    {
        $order->load('payments');
        $paid = $order->payments->sum('amount');
        $total = $order->total_amount;

        if ($paid >= $total) {
            $status = 'Paid';
        } elseif ($paid > 0) {
            $status = 'Partially Paid';
        } else {
            $status = 'Unpaid';
        }

        $order->update(['status' => $status]);
    }
}