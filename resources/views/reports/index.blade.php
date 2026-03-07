@extends('layouts.app')
@section('header', 'Financial Reports')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar { border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; }
    .flatpickr-day.selected { background: #2563eb !important; border-color: #2563eb !important; }
</style>

@php
    $isGlobal = session('active_location_id') === 'all';
@endphp

@if($isGlobal)
<div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-xl flex items-center gap-3 shadow-sm">
    <i class="fas fa-globe text-xl"></i>
    <div class="text-sm">
        <strong>Global View Active:</strong> You are viewing aggregated financial data across all studio branches. Select a specific location from the top navigation to view an isolated P&L.
    </div>
</div>
@endif

<div x-data="{ showFilters: false }" class="mb-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        
        <div class="w-full md:w-auto flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Financial Overview</h1>
                <p class="text-sm text-gray-500 mt-1">
                    <i class="far fa-calendar-alt mr-1"></i> 
                    {{ $start->format('d M Y') }} - {{ $end->format('d M Y') }}
                </p>
            </div>
        </div>
        
        <div class="flex items-center justify-end gap-2 w-full md:w-auto">
            
            <button @click="showFilters = !showFilters" class="md:hidden h-10 w-10 flex items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700 shadow-sm hover:text-blue-600 hover:border-blue-300 transition" title="Filter Dates">
                <i class="fas fa-filter"></i>
            </button>

            <form action="{{ route('reports.index') }}" method="GET" class="hidden md:flex items-center gap-2 bg-white p-2 rounded-xl border border-gray-200 shadow-sm">
                <div class="relative">
                    <input type="text" name="start_date" value="{{ request('start_date', $start->format('Y-m-d')) }}" 
                           class="datepicker-reports w-28 border-none text-sm text-slate-700 font-medium focus:ring-0 rounded-lg bg-gray-50 py-2 pl-3 pr-2"
                           placeholder="Start">
                </div>
                <span class="text-gray-400 font-bold">-</span>
                <div class="relative">
                    <input type="text" name="end_date" value="{{ request('end_date', $end->format('Y-m-d')) }}" 
                           class="datepicker-reports w-28 border-none text-sm text-slate-700 font-medium focus:ring-0 rounded-lg bg-gray-50 py-2 pl-3 pr-2"
                           placeholder="End">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-lg transition shadow-sm" title="Apply Filter">
                    <i class="fas fa-check text-sm"></i>
                </button>
                <a href="{{ route('reports.index') }}" class="bg-gray-100 hover:bg-red-50 text-gray-500 hover:text-red-600 p-2 rounded-lg transition border border-gray-200" title="Reset to FY">
                    <i class="fas fa-undo-alt text-sm"></i>
                </a>
            </form>

            @can('export reports')
            <a href="{{ route('reports.export', ['start_date' => request('start_date', $start->format('Y-m-d')), 'end_date' => request('end_date', $end->format('Y-m-d'))]) }}" 
               class="h-10 w-10 md:w-auto md:px-4 flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl shadow-sm transition" title="Export CSV">
                <i class="fas fa-file-csv"></i>
                <span class="hidden md:inline text-sm">CSV</span>
            </a>

            <a href="{{ route('reports.export_pdf', ['start_date' => request('start_date', $start->format('Y-m-d')), 'end_date' => request('end_date', $end->format('Y-m-d'))]) }}" 
               class="h-10 w-10 md:w-auto md:px-4 flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-sm transition" title="Export PDF">
                <i class="fas fa-file-pdf"></i>
                <span class="hidden md:inline text-sm">PDF</span>
            </a>
            @endcan
        </div>
    </div>

    <div x-show="showFilters" x-collapse class="md:hidden mt-4">
        <form action="{{ route('reports.index') }}" method="GET" class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex flex-col gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Start Date</label>
                <input type="text" name="start_date" value="{{ request('start_date', $start->format('Y-m-d')) }}" class="datepicker-reports w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 px-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-700 shadow-sm" placeholder="Start Date">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">End Date</label>
                <input type="text" name="end_date" value="{{ request('end_date', $end->format('Y-m-d')) }}" class="datepicker-reports w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 px-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-700 shadow-sm" placeholder="End Date">
            </div>
            <div class="flex gap-2 mt-2">
                <a href="{{ route('reports.index') }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2.5 rounded-xl transition border border-gray-200 shadow-sm">Reset</a>
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-xl transition shadow-sm">Apply</button>
            </div>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Revenue</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">₹{{ number_format($totalRevenue, 2) }}</h3>
            
            <div class="flex items-center gap-1 mt-2 text-xs font-bold">
                @if($trends['revenue'] >= 0)
                    <span class="text-green-600 bg-green-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-up"></i> {{ number_format($trends['revenue'], 1) }}%
                    </span>
                @else
                    <span class="text-red-600 bg-red-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-down"></i> {{ number_format(abs($trends['revenue']), 1) }}%
                    </span>
                @endif
                <span class="text-gray-400 font-medium ml-1">vs previous period</span>
            </div>
        </div>
        <div class="h-12 w-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-xl">
            <i class="fas fa-coins"></i>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Expenses</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">₹{{ number_format($totalExpenses, 2) }}</h3>
            
            <div class="flex items-center gap-1 mt-2 text-xs font-bold">
                @if($trends['expense'] > 0)
                    <span class="text-red-600 bg-red-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-up"></i> {{ number_format($trends['expense'], 1) }}%
                    </span>
                @else
                    <span class="text-green-600 bg-green-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-down"></i> {{ number_format(abs($trends['expense']), 1) }}%
                    </span>
                @endif
                <span class="text-gray-400 font-medium ml-1">vs previous period</span>
            </div>
        </div>
        <div class="h-12 w-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center text-xl">
            <i class="fas fa-wallet"></i>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Net Profit</p>
            <h3 class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-blue-600' : 'text-red-600' }} mt-1">
                ₹{{ number_format($netProfit, 2) }}
            </h3>
            
            <div class="flex items-center gap-1 mt-2 text-xs font-bold">
                @if($trends['profit'] >= 0)
                    <span class="text-green-600 bg-green-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-up"></i> {{ number_format($trends['profit'], 1) }}%
                    </span>
                @else
                    <span class="text-red-600 bg-red-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-down"></i> {{ number_format(abs($trends['profit']), 1) }}%
                    </span>
                @endif
                <span class="text-gray-400 font-medium ml-1">vs previous period</span>
            </div>
        </div>
        <div class="h-12 w-12 rounded-full {{ $netProfit >= 0 ? 'bg-blue-50 text-blue-600' : 'bg-red-50 text-red-600' }} flex items-center justify-center text-xl">
            <i class="fas fa-chart-line"></i>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm mb-6 w-full overflow-hidden">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-slate-800">Income vs Expense Trend</h3>
    </div>
    <div id="pnlChart" class="w-full"></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-800">Income (Top Clients)</h3>
        </div>
        @if(count($incomeValues) > 0)
            <div id="incomePieChart" class="flex justify-center"></div>
        @else
            <div class="flex flex-col items-center justify-center h-64 text-gray-400">
                <i class="fas fa-chart-pie text-4xl mb-3 opacity-20"></i>
                <p class="text-sm">No income data available</p>
            </div>
        @endif
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-800">Income (Products)</h3>
        </div>
        @if(count($productValues) > 0)
            <div id="productPieChart" class="flex justify-center"></div>
        @else
            <div class="flex flex-col items-center justify-center h-64 text-gray-400">
                <i class="fas fa-box-open text-4xl mb-3 opacity-20"></i>
                <p class="text-sm">No product sales data</p>
            </div>
        @endif
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-800">Expense Breakdown</h3>
        </div>
        @if(count($expenseValues) > 0)
            <div id="expensePieChart" class="flex justify-center"></div>
        @else
            <div class="flex flex-col items-center justify-center h-64 text-gray-400">
                <i class="fas fa-wallet text-4xl mb-3 opacity-20"></i>
                <p class="text-sm">No expenses to display</p>
            </div>
        @endif
    </div>
</div>

<div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-6 overflow-hidden">
    <div class="py-5 px-6 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Receivables & Aging Matrix</h3>
            <p class="text-xs text-gray-500 mt-1">Outstanding invoice balances grouped by days overdue</p>
        </div>
        <div class="bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg text-sm font-bold border border-blue-100 shadow-sm">
            Total Outstanding: ₹{{ number_format(array_sum($agingBuckets), 2) }}
        </div>
    </div>
    <div class="p-6 grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 text-center hover:shadow-md transition">
            <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Current / Not Due</p>
            <h4 class="text-xl font-bold text-slate-800">₹{{ number_format($agingBuckets['not_due'], 2) }}</h4>
        </div>
        <div class="bg-yellow-50 p-4 rounded-xl border border-yellow-100 text-center hover:shadow-md transition">
            <p class="text-[11px] font-bold text-yellow-600 uppercase tracking-wider mb-1">1 - 30 Days</p>
            <h4 class="text-xl font-bold text-yellow-700">₹{{ number_format($agingBuckets['1_30'], 2) }}</h4>
        </div>
        <div class="bg-orange-50 p-4 rounded-xl border border-orange-100 text-center hover:shadow-md transition">
            <p class="text-[11px] font-bold text-orange-600 uppercase tracking-wider mb-1">31 - 60 Days</p>
            <h4 class="text-xl font-bold text-orange-700">₹{{ number_format($agingBuckets['31_60'], 2) }}</h4>
        </div>
        <div class="bg-red-50 p-4 rounded-xl border border-red-100 text-center hover:shadow-md transition">
            <p class="text-[11px] font-bold text-red-500 uppercase tracking-wider mb-1">61 - 90 Days</p>
            <h4 class="text-xl font-bold text-red-600">₹{{ number_format($agingBuckets['61_90'], 2) }}</h4>
        </div>
        <div class="bg-rose-50 p-4 rounded-xl border border-rose-200 text-center hover:shadow-md transition shadow-inner">
            <p class="text-[11px] font-bold text-rose-600 uppercase tracking-wider mb-1">90+ Days Overdue</p>
            <h4 class="text-xl font-black text-rose-700">₹{{ number_format($agingBuckets['90_plus'], 2) }}</h4>
        </div>
    </div>
</div>

<div class="rounded-2xl border border-gray-200 bg-white shadow-sm mb-6">
    <div class="py-5 px-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h3 class="text-xl font-bold text-slate-800">Detailed Financial Ledger</h3>
        <span class="text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100 border border-gray-200 px-3 py-1.5 rounded-lg w-fit">
            All Transactions
        </span>
    </div>

    <div class="hidden md:block overflow-x-auto p-2">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-left rounded-lg overflow-hidden">
                    <th class="py-4 px-4 font-bold text-gray-400 uppercase text-xs rounded-l-lg">Date</th>
                    <th class="py-4 px-4 font-bold text-gray-400 uppercase text-xs">Ref ID</th>
                    <th class="py-4 px-4 font-bold text-gray-400 uppercase text-xs">Description / Payee</th>
                    <th class="py-4 px-4 font-bold text-gray-400 uppercase text-xs">Category</th>
                    <th class="py-4 px-4 font-bold text-gray-400 uppercase text-xs text-right">Income</th>
                    <th class="py-4 px-4 font-bold text-gray-400 uppercase text-xs text-right rounded-r-lg">Expense</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($ledger as $row)
                <tr class="hover:bg-gray-50 transition duration-200">
                    <td class="py-4 px-4 font-mono text-gray-600">{{ $row->date->format('d-M-Y') }}</td>
                    <td class="py-4 px-4">
                        <span class="font-bold text-xs px-2 py-1 rounded {{ $row->type == 'Income' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                            {{ $row->ref }}
                        </span>
                    </td>
                    <td class="py-4 px-4 font-medium text-slate-800">
                        {{ $row->desc }}
                        @if($isGlobal)
                            <div class="text-[10px] text-red-500 font-bold mt-1.5"><i class="fas fa-map-marker-alt"></i> {{ $row->location }}</div>
                        @endif
                    </td>
                    <td class="py-4 px-4">
                        <span class="text-xs bg-white text-gray-600 px-2 py-1 rounded border border-gray-200 shadow-sm">{{ $row->category }}</span>
                    </td>
                    <td class="py-4 px-4 text-right font-bold text-green-600">
                        @if($row->credit > 0) +₹{{ number_format($row->credit, 2) }} @else - @endif
                    </td>
                    <td class="py-4 px-4 text-right font-bold text-red-600">
                        @if($row->debit > 0) -₹{{ number_format($row->debit, 2) }} @else - @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-search text-3xl mb-3 opacity-20"></i>
                            <p>No transactions found for this period.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="block md:hidden space-y-4 p-4">
        @forelse($ledger as $row)
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 flex flex-col gap-3">
            
            <div class="flex justify-between items-start border-b border-gray-100 pb-3">
                <div>
                    <span class="font-bold text-[11px] px-2 py-1 rounded {{ $row->type == 'Income' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                        {{ $row->ref }}
                    </span>
                    <div class="text-[11px] text-gray-500 mt-2 font-mono">
                        {{ $row->date->format('d M, Y') }}
                    </div>
                </div>
                <div class="text-right">
                    @if($row->type == 'Income')
                        <div class="font-bold text-green-600 text-lg font-mono leading-tight">+₹{{ number_format($row->credit, 2) }}</div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase mt-1">Income</div>
                    @else
                        <div class="font-bold text-red-600 text-lg font-mono leading-tight">-₹{{ number_format($row->debit, 2) }}</div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase mt-1">Expense</div>
                    @endif
                </div>
            </div>
            
            <div class="flex flex-col gap-2 bg-gray-50 p-3 rounded-xl border border-gray-100">
                <div class="font-bold text-gray-900 text-sm">{{ $row->desc }}</div>
                @if($isGlobal)
                    <div class="text-[10px] text-red-500 font-bold"><i class="fas fa-map-marker-alt"></i> {{ $row->location }}</div>
                @endif
                <div class="text-[12px] text-gray-600 mt-1">
                    <span class="text-[11px] bg-white text-gray-600 px-2 py-1 rounded border border-gray-200 shadow-sm">{{ $row->category }}</span>
                </div>
            </div>
            
        </div>
        @empty
        <div class="p-6 text-center text-gray-500 bg-gray-50 border border-gray-100 rounded-xl">
            <i class="fas fa-search text-3xl mb-3 opacity-20"></i>
            <p class="text-sm">No transactions found for this period.</p>
        </div>
        @endforelse
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr(".datepicker-reports", { 
        dateFormat: "Y-m-d", 
        altInput: true, 
        altFormat: "d/m/Y", 
        allowInput: true 
    });

    // 1. P&L Chart
    var pnlOptions = {
        series: [{ name: 'Revenue', data: @json(array_values($revenueData)) }, { name: 'Expenses', data: @json(array_values($expenseData)) }],
        chart: { type: 'area', height: 350, toolbar: { show: false }, fontFamily: 'Satoshi, sans-serif' },
        colors: ['#22c55e', '#ef4444'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        xaxis: { categories: @json(array_keys($revenueData)) },
        fill: { opacity: 0.1 },
        grid: { borderColor: '#f1f5f9' },
        tooltip: { y: { formatter: function (val) { return "₹" + val.toLocaleString(); } } }
    };
    new ApexCharts(document.querySelector("#pnlChart"), pnlOptions).render();

    // 2. Income Pie Chart
    @if(count($incomeValues) > 0)
    var incomeOptions = {
        series: @json($incomeValues),
        labels: @json($incomeLabels),
        chart: { type: 'donut', height: 300, fontFamily: 'Satoshi, sans-serif' },
        colors: ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899'], 
        legend: { position: 'bottom' },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: function (val) { return "₹" + val.toLocaleString(); } } },
        plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', formatter: function (w) { return "₹" + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString() } } } } } }
    };
    new ApexCharts(document.querySelector("#incomePieChart"), incomeOptions).render();
    @endif

    // 3. Product Pie Chart
    @if(count($productValues) > 0)
    var productOptions = {
        series: @json($productValues),
        labels: @json($productLabels),
        chart: { type: 'donut', height: 300, fontFamily: 'Satoshi, sans-serif' },
        colors: ['#6366f1', '#8b5cf6', '#d946ef', '#f43f5e', '#f97316', '#eab308'], 
        legend: { position: 'bottom' },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: function (val) { return "₹" + val.toLocaleString(); } } },
        plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', formatter: function (w) { return "₹" + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString() } } } } } }
    };
    new ApexCharts(document.querySelector("#productPieChart"), productOptions).render();
    @endif

    // 4. Expenses Pie Chart
    @if(count($expenseValues) > 0)
    var expenseOptions = {
        series: @json($expenseValues),
        labels: @json($expenseLabels),
        chart: { type: 'donut', height: 300, fontFamily: 'Satoshi, sans-serif' },
        colors: ['#ef4444', '#f97316', '#f59e0b', '#84cc16', '#06b6d4'], 
        legend: { position: 'bottom' },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: function (val) { return "₹" + val.toLocaleString(); } } },
        plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', formatter: function (w) { return "₹" + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString() } } } } } }
    };
    new ApexCharts(document.querySelector("#expensePieChart"), expenseOptions).render();
    @endif
</script>
@endsection