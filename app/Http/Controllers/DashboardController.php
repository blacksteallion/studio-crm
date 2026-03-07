<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Expense; 
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startMonth = Carbon::now()->startOfMonth();
        $endMonth = Carbon::now()->endOfMonth();
        $startLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endLastMonth = Carbon::now()->subMonth()->endOfMonth();

        // Capture Active Location Context
        $locationId = session('active_location_id');

        $data = [];

        // --- 1. FINANCIAL BI SNAPSHOT (Requires permissions) ---
        if (Auth::user()->can('view orders') || Auth::user()->can('view reports')) {
            
            // Current Month Revenue
            $revQ = Order::whereBetween('invoice_date', [$startMonth, $endMonth]);
            if ($locationId !== 'all') $revQ->where('location_id', $locationId);
            $monthRevenue = $revQ->sum('total_amount');

            // Current Month Expense
            $expQ = Expense::whereBetween('expense_date', [$startMonth, $endMonth]);
            if ($locationId !== 'all') $expQ->where('location_id', $locationId);
            $monthExpense = $expQ->sum('amount');

            $monthProfit = $monthRevenue - $monthExpense;

            // Last Month Trends (For % comparison)
            $lastRevQ = Order::whereBetween('invoice_date', [$startLastMonth, $endLastMonth]);
            if ($locationId !== 'all') $lastRevQ->where('location_id', $locationId);
            $lastRevenue = $lastRevQ->sum('total_amount');

            $lastExpQ = Expense::whereBetween('expense_date', [$startLastMonth, $endLastMonth]);
            if ($locationId !== 'all') $lastExpQ->where('location_id', $locationId);
            $lastExpense = $lastExpQ->sum('amount');

            $lastProfit = $lastRevenue - $lastExpense;

            $data['monthRevenue'] = $monthRevenue;
            $data['monthExpense'] = $monthExpense;
            $data['monthProfit'] = $monthProfit;
            $data['trends'] = [
                'revenue' => $this->calculateChange($monthRevenue, $lastRevenue),
                'expense' => $this->calculateChange($monthExpense, $lastExpense),
                'profit'  => $this->calculateChange($monthProfit, $lastProfit)
            ];

            // 6-Month Trend Chart Data
            $months = collect(range(0, 5))->map(fn ($i) => Carbon::now()->subMonths($i))->reverse()->values();
            $chartLabels = [];
            $revenueTrend = [];
            $expenseTrend = [];

            foreach ($months as $m) {
                $chartLabels[] = $m->format('M Y');
                
                $mStart = $m->copy()->startOfMonth();
                $mEnd = $m->copy()->endOfMonth();

                $rQ = Order::whereBetween('invoice_date', [$mStart, $mEnd]);
                if ($locationId !== 'all') $rQ->where('location_id', $locationId);
                $revenueTrend[] = (float) $rQ->sum('total_amount');

                $eQ = Expense::whereBetween('expense_date', [$mStart, $mEnd]);
                if ($locationId !== 'all') $eQ->where('location_id', $locationId);
                $expenseTrend[] = (float) $eQ->sum('amount');
            }

            $data['chartLabels'] = $chartLabels;
            $data['revenueTrend'] = $revenueTrend;
            $data['expenseTrend'] = $expenseTrend;
        }

        // --- 2. OPERATIONAL PULSE (Requires permissions) ---
        if (Auth::user()->can('view bookings')) {
            $groupConcat = DB::connection()->getDriverName() === 'sqlite' 
                ? "GROUP_CONCAT(booking_items.item_name, ', ')" 
                : "GROUP_CONCAT(booking_items.item_name SEPARATOR ', ')";

            $schedQ = Booking::select('bookings.*', DB::raw("$groupConcat as service_name"))
                ->leftJoin('booking_items', 'bookings.id', '=', 'booking_items.booking_id')
                ->with(['customer', 'assignedStaff', 'location'])
                ->whereDate('bookings.booking_date', $today)
                ->where('bookings.status', '!=', 'Cancelled')
                ->orderBy('bookings.start_time')
                ->groupBy('bookings.id');
                
            if ($locationId !== 'all') $schedQ->where('bookings.location_id', $locationId);
            
            $data['todaysSchedule'] = $schedQ->get();
            $data['todaysBookingsCount'] = $data['todaysSchedule']->count();
        }

        // --- 3. LEAD PIPELINE (Requires permissions) ---
        if (Auth::user()->can('view inquiries')) {
            $inqStatsQ = Inquiry::whereBetween('created_at', [$startMonth, $endMonth]);
            if ($locationId !== 'all') $inqStatsQ->where('location_id', $locationId);

            $inquiryStats = $inqStatsQ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $data['funnelData'] = [
                'New' => $inquiryStats['New'] ?? 0,
                'In Progress' => $inquiryStats['In Progress'] ?? 0,
                'Qualified' => $inquiryStats['Qualified'] ?? 0,
                'Slot Reserved' => $inquiryStats['Slot Reserved'] ?? 0,
                'Lost' => $inquiryStats['Lost'] ?? 0,
            ];

            $recentInqQ = Inquiry::with(['customer', 'location'])->latest()->take(5);
            if ($locationId !== 'all') $recentInqQ->where('location_id', $locationId);
            
            $data['recentInquiries'] = $recentInqQ->get();
        }

        return view('dashboard', $data);
    }

    private function calculateChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return (($current - $previous) / $previous) * 100;
    }
}