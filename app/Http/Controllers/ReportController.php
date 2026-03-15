<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Expense;
use App\Models\Inquiry;
use App\Models\Booking; 
use App\Models\Setting;
use App\Models\Location;
use App\Models\LeadSource;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view reports', only: ['index', 'growth', 'operations']),
            new Middleware('can:export reports', only: ['export', 'exportPdf']),
        ];
    }

    // ==========================================
    // 1. FINANCIAL REPORTS
    // ==========================================

    public function index(Request $request)
    {
        $dates = $this->getDates($request);
        $start = $dates['start'];
        $end = $dates['end'];
        
        $prevDates = $this->getPreviousPeriod($start, $end);
        $prevStart = $prevDates['start'];
        $prevEnd = $prevDates['end'];

        $locationId = session('active_location_id', 'all');

        $totalRevenue = Order::whereBetween('created_at', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))
            ->sum('total_amount'); 

        $totalExpenses = Expense::whereBetween('expense_date', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))
            ->sum('amount');

        $netProfit = $totalRevenue - $totalExpenses;

        $prevRevenue = Order::whereBetween('created_at', [$prevStart, $prevEnd])
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))
            ->sum('total_amount');

        $prevExpenses = Expense::whereBetween('expense_date', [$prevStart, $prevEnd])
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))
            ->sum('amount');

        $prevProfit = $prevRevenue - $prevExpenses;

        $trends = [
            'revenue' => $this->calculateChange($totalRevenue, $prevRevenue),
            'expense' => $this->calculateChange($totalExpenses, $prevExpenses),
            'profit'  => $this->calculateChange($netProfit, $prevProfit)
        ];

        $revenueData = $this->getMonthlyData(new Order, 'created_at', 'total_amount', $start, $end);
        $expenseData = $this->getMonthlyData(new Expense, 'expense_date', 'amount', $start, $end);

        $expenseBreakdown = Expense::whereBetween('expense_date', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $expenseLabels = $expenseBreakdown->pluck('category')->toArray();
        $expenseValues = $expenseBreakdown->pluck('total')->map(fn($item) => (float)$item)->toArray();

        $incomeBreakdown = Order::whereBetween('created_at', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))
            ->with('customer')
            ->get()
            ->groupBy(function($order) {
                return $order->customer ? $order->customer->name : 'Walk-in / Guest';
            })
            ->map(function ($orders) {
                return $orders->sum('total_amount');
            })
            ->sortDesc()
            ->take(5);

        $incomeLabels = $incomeBreakdown->keys()->toArray();
        $incomeValues = $incomeBreakdown->values()->map(fn($item) => (float)$item)->toArray();

        $productBreakdown = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('orders.location_id', $locationId))
            ->select('order_items.item_name', DB::raw('SUM(order_items.amount) as total_revenue'))
            ->groupBy('order_items.item_name')
            ->orderByDesc('total_revenue')
            ->get();

        $productLabels = $productBreakdown->pluck('item_name')->toArray();
        $productValues = $productBreakdown->pluck('total_revenue')->map(fn($item) => (float)$item)->toArray();

        $ledger = $this->getLedgerData($start, $end, $locationId);

        // --- NEW BI FEATURE: RECEIVABLES & AGING MATRIX ---
        $agingOrders = Order::with('payments')->whereIn('status', ['Unpaid', 'Partially Paid'])
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))
            ->get();

        $agingBuckets = ['not_due' => 0, '1_30' => 0, '31_60' => 0, '61_90' => 0, '90_plus' => 0];

        foreach ($agingOrders as $order) {
            $balanceDue = $order->total_amount - $order->payments->sum('amount');
            if ($balanceDue <= 0) continue;

            if (!$order->due_date || Carbon::parse($order->due_date)->isFuture() || Carbon::parse($order->due_date)->isToday()) {
                $agingBuckets['not_due'] += $balanceDue;
            } else {
                $daysOverdue = Carbon::parse($order->due_date)->diffInDays(Carbon::today());
                if ($daysOverdue <= 30) $agingBuckets['1_30'] += $balanceDue;
                elseif ($daysOverdue <= 60) $agingBuckets['31_60'] += $balanceDue;
                elseif ($daysOverdue <= 90) $agingBuckets['61_90'] += $balanceDue;
                else $agingBuckets['90_plus'] += $balanceDue;
            }
        }

        return view('reports.index', [
            'start' => $start,
            'end' => $end,
            'totalRevenue' => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'trends' => $trends,
            'revenueData' => $revenueData,
            'expenseData' => $expenseData,
            'expenseLabels' => $expenseLabels,
            'expenseValues' => $expenseValues,
            'incomeLabels' => $incomeLabels,
            'incomeValues' => $incomeValues,
            'productLabels' => $productLabels,
            'productValues' => $productValues,
            'ledger' => $ledger,
            'agingBuckets' => $agingBuckets 
        ]);
    }

    public function export(Request $request)
    {
        $dates = $this->getDates($request);
        $start = $dates['start'];
        $end = $dates['end'];
        $locationId = session('active_location_id', 'all');
        
        $ledger = $this->getLedgerData($start, $end, $locationId);

        $totalIncome = $ledger->where('type', 'Income')->sum('credit');
        $totalExpense = $ledger->where('type', 'Expense')->sum('debit');
        $netProfit = $totalIncome - $totalExpense;

        $locationName = $locationId === 'all' ? 'All Branches (Global)' : (Location::find($locationId)->name ?? 'Unassigned');

        $fileName = 'financial_report_' . $start->format('Y-m-d') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($ledger, $start, $end, $totalIncome, $totalExpense, $netProfit, $locationName) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['FINANCIAL REPORT']);
            fputcsv($file, ['Location:', $locationName]);
            fputcsv($file, ['Period:', $start->format('d M Y') . ' - ' . $end->format('d M Y')]);
            fputcsv($file, ['Generated On:', Carbon::now()->format('d M Y H:i A')]);
            fputcsv($file, []); 
            
            fputcsv($file, ['SUMMARY']);
            fputcsv($file, ['Total Income', number_format($totalIncome, 2)]);
            fputcsv($file, ['Total Expense', number_format($totalExpense, 2)]);
            fputcsv($file, ['Net Profit', number_format($netProfit, 2)]);
            fputcsv($file, []); 

            fputcsv($file, ['Date', 'Location', 'Ref ID', 'Description', 'Category', 'Income (Credit)', 'Expense (Debit)']);
            
            foreach ($ledger as $row) {
                fputcsv($file, [
                    $row->date->format('d-m-Y'),
                    $row->location,
                    $row->ref,
                    $row->desc,
                    $row->category,
                    $row->credit > 0 ? $row->credit : '',
                    $row->debit > 0 ? $row->debit : ''
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $dates = $this->getDates($request);
        $start = $dates['start'];
        $end = $dates['end'];
        $locationId = session('active_location_id', 'all');

        $ledger = $this->getLedgerData($start, $end, $locationId);
        
        $company = [
            'name' => Setting::get('company_name', 'My Studio'),
            'address' => Setting::get('company_address', ''),
            'phone' => Setting::get('company_phone', ''),
            'logo' => Setting::get('company_logo'),
        ];

        $locationName = $locationId === 'all' ? 'All Branches (Global)' : (Location::find($locationId)->name ?? 'Unassigned');

        $totalIncome = $ledger->sum('credit');
        $totalExpense = $ledger->sum('debit');

        $pdf = Pdf::loadView('reports.pdf', [
            'ledger' => $ledger,
            'start' => $start,
            'end' => $end,
            'company' => $company,
            'locationName' => $locationName,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'netProfit' => $totalIncome - $totalExpense
        ]);

        return $pdf->download('Financial_Statement_' . $start->format('d-m-Y') . '.pdf');
    }

    // ==========================================
    // 2. GROWTH & LEADS REPORT
    // ==========================================

    public function growth(Request $request)
    {
        $dates = $this->getDates($request);
        $start = $dates['start'];
        $end = $dates['end'];
        $locationId = session('active_location_id', 'all');

        $prevDates = $this->getPreviousPeriod($start, $end);
        $prevStart = $prevDates['start'];
        $prevEnd = $prevDates['end'];

        $totalInquiries = Inquiry::whereBetween('created_at', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))->count();

        $convertedCount = Inquiry::whereBetween('created_at', [$start, $end])
            ->where('status', 'Slot Reserved')
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))->count();

        $conversionRate = $totalInquiries > 0 ? ($convertedCount / $totalInquiries) * 100 : 0;

        $prevTotalInquiries = Inquiry::whereBetween('created_at', [$prevStart, $prevEnd])
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))->count();

        $prevConvertedCount = Inquiry::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('status', 'Slot Reserved')
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))->count();

        $prevConversionRate = $prevTotalInquiries > 0 ? ($prevConvertedCount / $prevTotalInquiries) * 100 : 0;

        $trends = [
            'inquiries' => $this->calculateChange($totalInquiries, $prevTotalInquiries),
            'converted' => $this->calculateChange($convertedCount, $prevConvertedCount),
            'rate'      => $this->calculateChange($conversionRate, $prevConversionRate)
        ];

        $sourceStats = Inquiry::whereBetween('inquiries.created_at', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('inquiries.location_id', $locationId))
            ->join('lead_sources', 'inquiries.lead_source_id', '=', 'lead_sources.id')
            ->select('lead_sources.name', DB::raw('count(*) as total'))
            ->groupBy('lead_sources.name')
            ->orderByDesc('total')
            ->get();

        $sourceLabels = $sourceStats->pluck('name')->toArray();
        $sourceValues = $sourceStats->pluck('total')->toArray();

        $statusStats = Inquiry::whereBetween('created_at', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();
            
        $statusLabels = $statusStats->pluck('status')->toArray();
        $statusValues = $statusStats->pluck('total')->toArray();

        $trendData = $this->getMonthlyData(new Inquiry, 'created_at', 'id', $start, $end, 'count');

        $staffStats = Inquiry::whereBetween('inquiries.created_at', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('inquiries.location_id', $locationId))
            ->join('users', 'inquiries.assigned_staff_id', '=', 'users.id')
            ->select('users.name', DB::raw('count(*) as total'))
            ->groupBy('users.name')
            ->orderByDesc('total')
            ->take(8) 
            ->get();

        $staffLabels = $staffStats->pluck('name')->toArray();
        $staffValues = $staffStats->pluck('total')->toArray();

        // Cohort Matrix
        $period = CarbonPeriod::create($start, '1 month', $end);
        $monthGrid = [];
        foreach($period as $dt) {
            $monthGrid[$dt->format('Y-m')] = $dt->format('M-y'); 
        }

        $sources = LeadSource::pluck('name', 'id'); 
        $rawInquiries = Inquiry::whereBetween('created_at', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))
            ->get();

        $matrix = []; 

        foreach($sources as $sourceId => $sourceName) {
            $sourceInquiries = $rawInquiries->where('lead_source_id', $sourceId);
            if($sourceInquiries->isEmpty()) continue;

            foreach($monthGrid as $mKey => $mLabel) {
                $addedInMonth = $sourceInquiries->filter(function($i) use ($mKey) {
                    return $i->created_at->format('Y-m') === $mKey;
                });
                
                $row = [
                    'source_id' => $sourceId,
                    'total_added' => $addedInMonth->count(),
                    'conversions' => [],
                    'total_converted' => 0
                ];

                foreach($monthGrid as $cKey => $cLabel) {
                    $convCount = $addedInMonth->filter(function($i) use ($cKey) {
                        return ($i->status === 'Slot Reserved') && $i->updated_at->format('Y-m') === $cKey;
                    })->count();

                    $row['conversions'][$cKey] = $convCount;
                    $row['total_converted'] += $convCount;
                }
                $matrix[$sourceName][$mKey] = $row;
            }
        }

        $avgTimeSource = Inquiry::where('inquiries.status', 'Slot Reserved')
            ->whereBetween('inquiries.created_at', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('inquiries.location_id', $locationId))
            ->join('lead_sources', 'inquiries.lead_source_id', '=', 'lead_sources.id')
            ->select('lead_sources.name as source', DB::raw('AVG(DATEDIFF(inquiries.updated_at, inquiries.created_at)) as avg_days'))
            ->groupBy('source')
            ->orderBy('avg_days')
            ->get();

        $avgTimeStaff = Inquiry::where('inquiries.status', 'Slot Reserved')
            ->whereBetween('inquiries.created_at', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('inquiries.location_id', $locationId))
            ->join('users', 'inquiries.assigned_staff_id', '=', 'users.id')
            ->join('lead_sources', 'inquiries.lead_source_id', '=', 'lead_sources.id')
            ->select(
                'users.name as staff',
                'lead_sources.name as source',
                DB::raw('AVG(DATEDIFF(inquiries.updated_at, inquiries.created_at)) as avg_days')
            )
            ->groupBy('staff', 'source')
            ->get()
            ->groupBy('staff');

        // --- NEW BI FEATURE: STAFF CONVERSION LEADERBOARD ---
        $staffLeaderboard = DB::table('users')
            ->join('inquiries', 'users.id', '=', 'inquiries.assigned_staff_id')
            ->whereBetween('inquiries.created_at', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('inquiries.location_id', $locationId))
            ->select(
                'users.name',
                DB::raw('count(*) as total_assigned'),
                DB::raw("sum(case when inquiries.status = 'Slot Reserved' then 1 else 0 end) as total_converted")
            )
            ->groupBy('users.id', 'users.name')
            ->get()
            ->map(function($staff) {
                $staff->win_rate = $staff->total_assigned > 0 ? ($staff->total_converted / $staff->total_assigned) * 100 : 0;
                return $staff;
            })
            ->sortByDesc('win_rate')
            ->values();

        return view('reports.growth', [
            'start' => $start,
            'end' => $end,
            'totalInquiries' => $totalInquiries,
            'convertedCount' => $convertedCount,
            'conversionRate' => $conversionRate,
            'trends' => $trends,
            'sourceLabels' => $sourceLabels,
            'sourceValues' => $sourceValues,
            'statusLabels' => $statusLabels,
            'statusValues' => $statusValues,
            'trendData' => $trendData,
            'staffLabels' => $staffLabels,
            'staffValues' => $staffValues,
            'monthGrid' => $monthGrid,
            'cohortMatrix' => $matrix,
            'avgTimeSource' => $avgTimeSource,
            'avgTimeStaff' => $avgTimeStaff,
            'staffLeaderboard' => $staffLeaderboard 
        ]);
    }

    // ==========================================
    // 3. OPERATIONS & BOOKINGS REPORT
    // ==========================================

    public function operations(Request $request)
    {
        $dates = $this->getDates($request);
        $start = $dates['start'];
        $end = $dates['end'];
        $locationId = session('active_location_id', 'all');

        $prevDates = $this->getPreviousPeriod($start, $end);
        $prevStart = $prevDates['start'];
        $prevEnd = $prevDates['end'];

        $totalBookings = Booking::whereBetween('created_at', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))->count();

        $completedBookings = Booking::whereBetween('created_at', [$start, $end])
            ->where('status', 'Completed')
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))->count();

        $cancelledBookings = Booking::whereBetween('created_at', [$start, $end])
            ->where('status', 'Cancelled')
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))->count();
        
        $completionRate = $totalBookings > 0 ? ($completedBookings / $totalBookings) * 100 : 0;
        $cancellationRate = $totalBookings > 0 ? ($cancelledBookings / $totalBookings) * 100 : 0;

        $activeStaff = Booking::whereBetween('created_at', [$start, $end])
            ->where('status', 'Completed')
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))
            ->distinct('staff_id')
            ->count('staff_id');

        $prevTotalBookings = Booking::whereBetween('created_at', [$prevStart, $prevEnd])
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))->count();

        $prevCompleted = Booking::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('status', 'Completed')
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))->count();

        $prevCancelled = Booking::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('status', 'Cancelled')
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))->count();
        
        $prevCompletionRate = $prevTotalBookings > 0 ? ($prevCompleted / $prevTotalBookings) * 100 : 0;
        $prevCancellationRate = $prevTotalBookings > 0 ? ($prevCancelled / $prevTotalBookings) * 100 : 0;
        $prevActiveStaff = Booking::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('status', 'Completed')
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))
            ->distinct('staff_id')
            ->count('staff_id');

        $trends = [
            'bookings' => $this->calculateChange($totalBookings, $prevTotalBookings),
            'completion' => $this->calculateChange($completionRate, $prevCompletionRate),
            'cancellation' => $this->calculateChange($cancellationRate, $prevCancellationRate),
            'staff' => $this->calculateChange($activeStaff, $prevActiveStaff)
        ];

        $bookingTrend = $this->getMonthlyData(new Booking, 'created_at', 'id', $start, $end, 'count');

        $statusStats = Booking::whereBetween('created_at', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
        $statusLabels = $statusStats->pluck('status')->toArray();
        $statusValues = $statusStats->pluck('total')->toArray();

        $serviceStats = DB::table('booking_items')
            ->join('bookings', 'bookings.id', '=', 'booking_items.booking_id')
            ->whereBetween('bookings.created_at', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('bookings.location_id', $locationId))
            ->select('booking_items.item_name', DB::raw('count(*) as total'))
            ->groupBy('booking_items.item_name')
            ->orderByDesc('total')
            ->take(10)
            ->get();
            
        $serviceLabels = $serviceStats->pluck('item_name')->toArray();
        $serviceValues = $serviceStats->pluck('total')->toArray();

        $staffMatrix = DB::table('users')
            ->join('bookings', 'users.id', '=', 'bookings.staff_id')
            ->whereBetween('bookings.created_at', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('bookings.location_id', $locationId))
            ->select(
                'users.name',
                DB::raw('count(*) as total'),
                DB::raw("sum(case when bookings.status = 'Completed' then 1 else 0 end) as completed"), 
                DB::raw("sum(case when bookings.status = 'Cancelled' then 1 else 0 end) as cancelled")
            )
            ->groupBy('users.name')
            ->orderByDesc('completed')
            ->get()
            ->map(function($row) {
                $row->efficiency = $row->total > 0 ? ($row->completed / $row->total) * 100 : 0;
                return $row;
            });

        // --- NEW BI FEATURE: RESOURCE UTILIZATION HEATMAP ---
        $heatmapBookings = Booking::whereBetween('booking_date', [$start, $end])
            ->where('status', '!=', 'Cancelled')
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))
            ->select('booking_date', 'start_time')
            ->get();

        $hours = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'];
        $orderedDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $heatmapGrid = [];
        
        foreach ($orderedDays as $day) {
            foreach ($hours as $hour) {
                $heatmapGrid[$day][$hour] = 0;
            }
        }

        foreach ($heatmapBookings as $booking) {
            $day = Carbon::parse($booking->booking_date)->format('D'); 
            $hour = Carbon::parse($booking->start_time)->format('H:00'); 
            
            if (isset($heatmapGrid[$day][$hour])) {
                $heatmapGrid[$day][$hour]++;
            }
        }

        $heatmapSeries = [];
        foreach ($orderedDays as $day) {
            $dataPoints = [];
            foreach ($hours as $hour) {
                $dataPoints[] = [
                    'x' => $hour,
                    'y' => $heatmapGrid[$day][$hour]
                ];
            }
            $heatmapSeries[] = [
                'name' => $day,
                'data' => $dataPoints
            ];
        }

        return view('reports.operations', [
            'start' => $start,
            'end' => $end,
            'totalBookings' => $totalBookings,
            'completionRate' => $completionRate,
            'cancellationRate' => $cancellationRate,
            'activeStaff' => $activeStaff,
            'trends' => $trends,
            'bookingTrend' => $bookingTrend,
            'statusLabels' => $statusLabels,
            'statusValues' => $statusValues,
            'serviceLabels' => $serviceLabels,
            'serviceValues' => $serviceValues,
            'staffMatrix' => $staffMatrix,
            'heatmapSeries' => $heatmapSeries
        ]);
    }

    private function getDates($request) 
    {
        $now = Carbon::now();
        if ($now->month >= 4) {
            $start = Carbon::create($now->year, 4, 1)->startOfDay();
            $end = Carbon::create($now->year + 1, 3, 31)->endOfDay();
        } else {
            $start = Carbon::create($now->year - 1, 4, 1)->startOfDay();
            $end = Carbon::create($now->year, 3, 31)->endOfDay();
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();
        }
        return ['start' => $start, 'end' => $end];
    }

    private function getPreviousPeriod($start, $end)
    {
        $diffInDays = $start->diffInDays($end) + 1;
        $prevStart = $start->copy()->subDays($diffInDays);
        $prevEnd = $end->copy()->subDays($diffInDays);
        return ['start' => $prevStart, 'end' => $prevEnd];
    }

    private function calculateChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return (($current - $previous) / $previous) * 100;
    }

    private function getLedgerData($start, $end, $locationId) 
    {
        $income = Order::whereBetween('created_at', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))
            ->with(['customer', 'location'])
            ->get()
            ->map(function ($order) {
                return (object)[
                    'date' => $order->created_at,
                    'type' => 'Income',
                    'ref' => 'ORD-' . $order->id,
                    'location' => $order->location ? $order->location->name : 'Global',
                    'desc' => $order->customer ? $order->customer->name : 'Guest Customer',
                    'category' => 'Sales',
                    'credit' => $order->total_amount,
                    'debit' => 0
                ];
            });

        $expenses = Expense::whereBetween('expense_date', [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))
            ->with('location')
            ->get()
            ->map(function ($expense) {
                return (object)[
                    'date' => $expense->expense_date,
                    'type' => 'Expense',
                    'ref' => 'EXP-' . $expense->id,
                    'location' => $expense->location ? $expense->location->name : 'Global',
                    'desc' => $expense->title,
                    'category' => $expense->category,
                    'credit' => 0,
                    'debit' => $expense->amount
                ];
            });

        return $income->merge($expenses)->sortBy('date');
    }

    private function getMonthlyData($model, $dateCol, $amountCol, $start, $end, $type = 'sum')
    {
        $aggregate = $type === 'count' ? "COUNT($amountCol)" : "SUM($amountCol)";

        $driver = DB::connection()->getDriverName();
        $dateFormat = $driver === 'sqlite' 
            ? "strftime('%Y-%m', $dateCol)" 
            : "DATE_FORMAT($dateCol, '%Y-%m')";

        $locationId = session('active_location_id', 'all');

        $data = $model::whereBetween($dateCol, [$start, $end])
            ->when($locationId !== 'all', fn($q) => $q->where('location_id', $locationId))
            ->select(
                DB::raw("$dateFormat as month"), 
                DB::raw("$aggregate as total")
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $filledData = [];
        $period = CarbonPeriod::create($start, '1 month', $end);

        foreach ($period as $date) {
            $displayKey = $date->format('M-y'); 
            $dbKey = $date->format('Y-m');
            $filledData[$displayKey] = $data[$dbKey] ?? 0;
        }

        return $filledData;
    }
}