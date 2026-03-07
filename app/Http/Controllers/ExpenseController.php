<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Location; // <--- ADDED LOCATION MODEL
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Exports\ExpensesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view expenses', only: ['index']),
            new Middleware('can:create expenses', only: ['create', 'store']),
            new Middleware('can:edit expenses', only: ['edit', 'update']),
            new Middleware('can:delete expenses', only: ['destroy']),
            new Middleware('can:export expenses', only: ['export']),
        ];
    }

    public function index(Request $request)
    {
        $query = Expense::with('location');
        $categories = Expense::categories(); 
        
        $now = Carbon::now();
        if ($now->month >= 4) {
            $start = Carbon::create($now->year, 4, 1)->startOfDay();
            $end = Carbon::create($now->year + 1, 3, 31)->endOfDay();
        } else {
            $start = Carbon::create($now->year - 1, 4, 1)->startOfDay();
            $end = Carbon::create($now->year, 3, 31)->endOfDay();
        }

        $hasFilter = $request->anyFilled(['search', 'title', 'category', 'reference_no', 'min_amount', 'max_amount', 'start_date', 'end_date']);

        $query = $this->applyFilters($query, $request, $start, $end, $hasFilter);

        $expenses = $query->orderBy('expense_date', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        $totalAmount = $query->sum('amount');

        return view('expenses.index', [
            'expenses' => $expenses,
            'categories' => $categories, 
            'totalAmount' => $totalAmount,
            'fyStart' => $start,
            'fyEnd' => $end,
        ]);
    }

    public function export(Request $request)
    {
        $query = Expense::with('location');
        
        $now = Carbon::now();
        if ($now->month >= 4) {
            $start = Carbon::create($now->year, 4, 1)->startOfDay();
            $end = Carbon::create($now->year + 1, 3, 31)->endOfDay();
        } else {
            $start = Carbon::create($now->year - 1, 4, 1)->startOfDay();
            $end = Carbon::create($now->year, 3, 31)->endOfDay();
        }

        $hasFilter = $request->anyFilled(['search', 'title', 'category', 'reference_no', 'min_amount', 'max_amount', 'start_date', 'end_date']);

        $query = $this->applyFilters($query, $request, $start, $end, $hasFilter);

        $expenses = $query->orderBy('expense_date', 'desc')->orderBy('created_at', 'desc')->get();

        return Excel::download(new ExpensesExport($expenses), 'expenses_report_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    private function applyFilters($query, $request, $start, $end, $hasFilter)
    {
        if (session('active_location_id') !== 'all') {
            $query->where('location_id', session('active_location_id'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('reference_no')) {
            $query->where('reference_no', 'like', '%' . $request->reference_no . '%');
        }
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }

        if (!$hasFilter) {
            $query->whereBetween('expense_date', [$start, $end]);
        }

        return $query;
    }

    public function create()
    {
        // FIX: Admin Bypass
        $locations = Auth::user()->hasRole('Super Admin') 
            ? Location::where('is_active', true)->orderBy('name')->get() 
            : Auth::user()->locations()->where('is_active', true)->orderBy('name')->get();
            
        return view('expenses.create', [
            'categories' => Expense::categories(),
            'locations' => $locations
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string',
            'expense_date' => 'required|date',
            'receipt' => 'nullable|file|mimes:jpg,png,pdf,jpeg|max:5120',
        ]);

        $data = $request->except('receipt');

        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
            $data['receipt_path'] = $path;
        }

        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully.');
    }

    public function edit(Expense $expense)
    {
        // FIX: Admin Bypass
        $locations = Auth::user()->hasRole('Super Admin') 
            ? Location::where('is_active', true)->orderBy('name')->get() 
            : Auth::user()->locations()->where('is_active', true)->orderBy('name')->get();
            
        return view('expenses.edit', [
            'expense' => $expense,
            'categories' => Expense::categories(),
            'locations' => $locations
        ]);
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string',
            'expense_date' => 'required|date',
            'receipt' => 'nullable|file|mimes:jpg,png,pdf,jpeg|max:5120',
        ]);

        $data = $request->except('receipt');

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            $path = $request->file('receipt')->store('receipts', 'public');
            $data['receipt_path'] = $path;
        }

        $expense->update($data);

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }
        
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }
}