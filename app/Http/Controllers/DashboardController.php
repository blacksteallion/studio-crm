<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view dashboard'),
        ];
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('Super Admin');
        $locationId = session('active_location_id', 'all');

        // --- FILTER HELPERS ---
        $applyLocation = function ($query) use ($locationId) {
            if ($locationId !== 'all') {
                $query->where('location_id', $locationId);
            }
        };

        // --- 1. FINANCIAL METRICS (MoM) ---
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $monthRevenue = Order::whereBetween('invoice_date', [$thisMonthStart, $thisMonthEnd])->where($applyLocation)->sum('total_amount');
        $lastMonthRevenue = Order::whereBetween('invoice_date', [$lastMonthStart, $lastMonthEnd])->where($applyLocation)->sum('total_amount');

        $monthExpense = Expense::whereBetween('expense_date', [$thisMonthStart, $thisMonthEnd])->where($applyLocation)->sum('amount');
        $lastMonthExpense = Expense::whereBetween('expense_date', [$lastMonthStart, $lastMonthEnd])->where($applyLocation)->sum('amount');

        $monthProfit = $monthRevenue - $monthExpense;
        $lastMonthProfit = $lastMonthRevenue - $lastMonthExpense;

        $trends = [
            'revenue' => $this->calculateGrowth($monthRevenue, $lastMonthRevenue),
            'expense' => $this->calculateGrowth($monthExpense, $lastMonthExpense),
            'profit'  => $this->calculateGrowth($monthProfit, $lastMonthProfit),
        ];

        // --- 2. 6-MONTH TREND CHART ---
        $chartLabels = [];
        $revenueTrend = [];
        $expenseTrend = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();

            $chartLabels[] = $monthStart->format('M'); // E.g., Oct, Nov
            
            $revenueTrend[] = Order::whereBetween('invoice_date', [$monthStart, $monthEnd])->where($applyLocation)->sum('total_amount');
            $expenseTrend[] = Expense::whereBetween('expense_date', [$monthStart, $monthEnd])->where($applyLocation)->sum('amount');
        }

        // --- 3. INQUIRY FUNNEL CHART (This Month) ---
        $funnelData = Inquiry::whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->where(function ($query) use ($isSuperAdmin, $user, $locationId) {
                if (!$isSuperAdmin) {
                    $query->where('assigned_staff_id', $user->id);
                }
                if ($locationId !== 'all') {
                    $query->where('location_id', $locationId);
                }
            })
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // --- 4. TODAY'S SCHEDULE (Bookings) ---
        $todaysSchedule = Booking::with(['customer', 'location', 'items'])
            ->whereDate('booking_date', Carbon::today())
            ->where(function ($query) use ($isSuperAdmin, $user, $locationId) {
                if (!$isSuperAdmin) {
                    $query->where('staff_id', $user->id);
                }
                if ($locationId !== 'all') {
                    $query->where('location_id', $locationId);
                }
            })
            ->orderBy('start_time')
            ->take(8)
            ->get()
            ->map(function ($booking) {
                // Safely grab the first booked item name to show on the dashboard UI
                $booking->service_name = $booking->items->first()->item_name ?? 'Studio Service';
                return $booking;
            });

        // --- 5. RECENT INQUIRIES ---
        $recentInquiries = Inquiry::with(['customer', 'location'])
            ->where(function ($query) use ($isSuperAdmin, $user, $locationId) {
                if (!$isSuperAdmin) {
                    $query->where('assigned_staff_id', $user->id);
                }
                if ($locationId !== 'all') {
                    $query->where('location_id', $locationId);
                }
            })
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'monthRevenue', 'monthExpense', 'monthProfit', 'trends',
            'chartLabels', 'revenueTrend', 'expenseTrend',
            'funnelData', 'todaysSchedule', 'recentInquiries'
        ));
    }

    /**
     * Helper logic to calculate percentage growth safely without dividing by zero.
     */
    private function calculateGrowth($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return (($current - $previous) / abs($previous)) * 100;
    }
}