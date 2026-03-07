@extends('layouts.app')
@section('header', 'Operations Reports')

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
        <strong>Global View Active:</strong> You are viewing aggregated operational data across all studio branches.
    </div>
</div>
@endif

<div x-data="{ showFilters: false }" class="mb-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        
        <div class="w-full md:w-auto flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Operations & Bookings</h1>
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

            <form action="{{ route('reports.operations') }}" method="GET" class="hidden md:flex items-center gap-2 bg-white p-2 rounded-xl border border-gray-200 shadow-sm">
                <div class="relative">
                    <input type="text" name="start_date" value="{{ request('start_date', $start->format('Y-m-d')) }}" class="datepicker-reports w-28 border-none text-sm text-slate-700 font-medium focus:ring-0 rounded-lg bg-gray-50 py-2 pl-3 pr-2" placeholder="Start">
                </div>
                <span class="text-gray-400 font-bold">-</span>
                <div class="relative">
                    <input type="text" name="end_date" value="{{ request('end_date', $end->format('Y-m-d')) }}" class="datepicker-reports w-28 border-none text-sm text-slate-700 font-medium focus:ring-0 rounded-lg bg-gray-50 py-2 pl-3 pr-2" placeholder="End">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-lg transition shadow-sm" title="Apply Filter">
                    <i class="fas fa-check text-sm"></i>
                </button>
                <a href="{{ route('reports.operations') }}" class="bg-gray-100 hover:bg-red-50 text-gray-500 hover:text-red-600 p-2 rounded-lg transition border border-gray-200" title="Reset to FY">
                    <i class="fas fa-undo-alt text-sm"></i>
                </a>
            </form>
        </div>
    </div>

    <div x-show="showFilters" x-collapse class="md:hidden mt-4">
        <form action="{{ route('reports.operations') }}" method="GET" class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex flex-col gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Start Date</label>
                <input type="text" name="start_date" value="{{ request('start_date', $start->format('Y-m-d')) }}" class="datepicker-reports w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 px-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-700 shadow-sm" placeholder="Start Date">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">End Date</label>
                <input type="text" name="end_date" value="{{ request('end_date', $end->format('Y-m-d')) }}" class="datepicker-reports w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 px-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-700 shadow-sm" placeholder="End Date">
            </div>
            <div class="flex gap-2 mt-2">
                <a href="{{ route('reports.operations') }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2.5 rounded-xl transition border border-gray-200 shadow-sm">Reset</a>
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-xl transition shadow-sm">Apply</button>
            </div>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Bookings</p>
        <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalBookings) }}</h3>
        
        <div class="flex items-center gap-1 mt-2 text-xs font-bold">
            @if($trends['bookings'] >= 0)
                <span class="text-green-600 bg-green-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                    <i class="fas fa-arrow-up"></i> {{ number_format($trends['bookings'], 1) }}%
                </span>
            @else
                <span class="text-red-600 bg-red-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                    <i class="fas fa-arrow-down"></i> {{ number_format(abs($trends['bookings']), 1) }}%
                </span>
            @endif
            <span class="text-gray-400 font-medium ml-1">vs previous period</span>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Completion Rate</p>
        <h3 class="text-2xl font-bold text-green-600 mt-1">{{ number_format($completionRate, 1) }}%</h3>
        
        <div class="flex items-center gap-1 mt-2 text-xs font-bold">
            @if($trends['completion'] >= 0)
                <span class="text-green-600 bg-green-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                    <i class="fas fa-arrow-up"></i> {{ number_format($trends['completion'], 1) }}%
                </span>
            @else
                <span class="text-red-600 bg-red-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                    <i class="fas fa-arrow-down"></i> {{ number_format(abs($trends['completion']), 1) }}%
                </span>
            @endif
            <span class="text-gray-400 font-medium ml-1">vs prev</span>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Cancellation Rate</p>
        <h3 class="text-2xl font-bold text-red-600 mt-1">{{ number_format($cancellationRate, 1) }}%</h3>
        
        <div class="flex items-center gap-1 mt-2 text-xs font-bold">
            @if($trends['cancellation'] > 0)
                <span class="text-red-600 bg-red-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                    <i class="fas fa-arrow-up"></i> {{ number_format($trends['cancellation'], 1) }}%
                </span>
            @else
                <span class="text-green-600 bg-green-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                    <i class="fas fa-arrow-down"></i> {{ number_format(abs($trends['cancellation']), 1) }}%
                </span>
            @endif
            <span class="text-gray-400 font-medium ml-1">vs prev</span>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Staff</p>
        <h3 class="text-2xl font-bold text-blue-600 mt-1">{{ $activeStaff }}</h3>
        
        <div class="flex items-center gap-1 mt-2 text-xs font-bold">
            @if($trends['staff'] >= 0)
                <span class="text-green-600 bg-green-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                    <i class="fas fa-arrow-up"></i> {{ number_format($trends['staff'], 1) }}%
                </span>
            @else
                <span class="text-red-600 bg-red-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                    <i class="fas fa-arrow-down"></i> {{ number_format(abs($trends['staff']), 1) }}%
                </span>
            @endif
            <span class="text-gray-400 font-medium ml-1">vs prev</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <h3 class="font-bold text-slate-800 mb-4">Booking Volume Trend</h3>
        <div id="trendChart"></div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <h3 class="font-bold text-slate-800 mb-4">Booking Status Breakdown</h3>
        @if(count($statusValues) > 0)
            <div id="statusChart" class="flex justify-center"></div>
        @else
            <div class="h-64 flex items-center justify-center text-gray-400 text-sm">No data available</div>
        @endif
    </div>
</div>

<div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm mb-6 w-full overflow-x-auto">
    <div class="flex items-center justify-between mb-2">
        <h3 class="font-bold text-slate-800">Resource Utilization Heatmap</h3>
        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Bookings by Day & Time</span>
    </div>
    <div id="heatmapChart" class="w-full min-w-[700px]"></div>
</div>

<div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm mb-6">
    <h3 class="font-bold text-slate-800 mb-4">Top Services by Booking Volume</h3>
    @if(count($serviceValues) > 0)
        <div id="serviceChart"></div>
    @else
        <div class="h-64 flex items-center justify-center text-gray-400 text-sm">No service data available</div>
    @endif
</div>

<div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-6">
    <div class="py-5 px-6 border-b border-gray-100 flex justify-between items-center">
        <h3 class="text-xl font-bold text-slate-800">Staff Performance Matrix</h3>
    </div>
    
    <div class="hidden md:block overflow-x-auto p-2">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                    <th class="py-4 px-4 rounded-l-lg">Staff Member</th>
                    <th class="py-4 px-4 text-center">Total Bookings</th>
                    <th class="py-4 px-4 text-center text-green-600">Completed</th>
                    <th class="py-4 px-4 text-center text-red-600">Cancelled</th>
                    <th class="py-4 px-4 text-right rounded-r-lg">Efficiency</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($staffMatrix as $row)
                <tr class="hover:bg-gray-50 transition">
                    <td class="py-4 px-4 font-medium text-slate-800">{{ $row->name }}</td>
                    <td class="py-4 px-4 text-center font-bold">{{ $row->total }}</td>
                    <td class="py-4 px-4 text-center text-green-600">{{ $row->completed }}</td>
                    <td class="py-4 px-4 text-center text-red-600">{{ $row->cancelled }}</td>
                    <td class="py-4 px-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <span class="text-xs font-bold">{{ number_format($row->efficiency, 1) }}%</span>
                            <div class="w-16 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full" style="width: {{ $row->efficiency }}%"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-8 text-center text-gray-400">No staff performance data available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="block md:hidden space-y-4 p-4">
        @forelse($staffMatrix as $row)
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col gap-3">
                <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                    <span class="font-bold text-slate-800 text-base">{{ $row->name }}</span>
                    <span class="text-xs font-bold px-2 py-1 bg-blue-50 text-blue-600 rounded-lg">Total: {{ $row->total }}</span>
                </div>
                
                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-white p-2 rounded-lg border border-gray-100 text-center shadow-sm">
                        <span class="block text-[10px] text-gray-400 font-bold uppercase mb-1">Completed</span>
                        <span class="font-bold text-green-600 text-sm">{{ $row->completed }}</span>
                    </div>
                    <div class="bg-white p-2 rounded-lg border border-gray-100 text-center shadow-sm">
                        <span class="block text-[10px] text-gray-400 font-bold uppercase mb-1">Cancelled</span>
                        <span class="font-bold text-red-600 text-sm">{{ $row->cancelled }}</span>
                    </div>
                </div>
                
                <div class="bg-white p-3 rounded-lg border border-gray-100 flex items-center justify-between gap-3 mt-1 shadow-sm">
                    <span class="text-[10px] text-gray-400 font-bold uppercase">Efficiency</span>
                    <div class="flex-1 flex items-center justify-end gap-2">
                        <div class="w-full max-w-[100px] h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full" style="width: {{ $row->efficiency }}%"></div>
                        </div>
                        <span class="text-xs font-bold text-slate-800">{{ number_format($row->efficiency, 1) }}%</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-400 py-6 bg-white rounded-xl border border-gray-100">
                <i class="fas fa-users text-2xl mb-2 opacity-20"></i>
                <p class="text-sm">No staff performance data available.</p>
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

    // 1. Trend Chart
    var trendOptions = {
        series: [{ name: 'Bookings', data: @json(array_values($bookingTrend)) }],
        chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'Satoshi, sans-serif' },
        colors: ['#8b5cf6'],
        stroke: { curve: 'smooth', width: 3 },
        fill: { opacity: 0.1 },
        xaxis: { categories: @json(array_keys($bookingTrend)) },
        dataLabels: { enabled: false },
        grid: { borderColor: '#f1f5f9' }
    };
    new ApexCharts(document.querySelector("#trendChart"), trendOptions).render();

    // 2. Status Chart
    @if(count($statusValues) > 0)
    var statusOptions = {
        series: @json($statusValues),
        labels: @json($statusLabels),
        chart: { type: 'donut', height: 300, fontFamily: 'Satoshi, sans-serif' },
        colors: ['#3b82f6', '#10b981', '#ef4444', '#f59e0b', '#6b7280'],
        legend: { position: 'bottom' },
        plotOptions: { pie: { donut: { size: '65%' } } }
    };
    new ApexCharts(document.querySelector("#statusChart"), statusOptions).render();
    @endif

    // 3. Service Chart
    @if(count($serviceValues) > 0)
    var serviceOptions = {
        series: [{ name: 'Bookings', data: @json($serviceValues) }],
        chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Satoshi, sans-serif' },
        colors: ['#0ea5e9'],
        plotOptions: { bar: { borderRadius: 4, horizontal: false, columnWidth: '50%' } },
        xaxis: { categories: @json($serviceLabels) },
        grid: { borderColor: '#f1f5f9' }
    };
    new ApexCharts(document.querySelector("#serviceChart"), serviceOptions).render();
    @endif

    // 4. Resource Heatmap Chart
    var heatmapOptions = {
        series: @json($heatmapSeries),
        chart: {
            height: 350,
            type: 'heatmap',
            fontFamily: 'Satoshi, sans-serif',
            toolbar: { show: false }
        },
        dataLabels: { enabled: false },
        colors: ['#2563eb'],
        title: { text: '' },
        plotOptions: {
            heatmap: {
                shadeIntensity: 0.5,
                radius: 4,
                useFillColorAsStroke: false,
                colorScale: {
                    ranges: [
                        { from: 0, to: 0, color: '#f8fafc', name: 'Empty' }
                    ]
                }
            }
        }
    };
    new ApexCharts(document.querySelector("#heatmapChart"), heatmapOptions).render();

</script>
@endsection