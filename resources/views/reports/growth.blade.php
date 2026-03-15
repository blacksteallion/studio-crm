@extends('layouts.app')
@section('header', 'Growth Reports')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar { border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; }
    .flatpickr-day.selected { background: #2563eb !important; border-color: #2563eb !important; }
    
    .sticky-col { position: sticky; left: 0; background: #fff; z-index: 10; }
    .sticky-col-2 { position: sticky; left: 100px; background: #fff; z-index: 10; border-right: 2px solid #f1f5f9; }
</style>

@php
    $isGlobal = session('active_location_id', 'all') === 'all';
@endphp

@if($isGlobal)
<div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-xl flex items-center gap-3 shadow-sm">
    <i class="fas fa-globe text-xl"></i>
    <div class="text-sm">
        <strong>Global View Active:</strong> You are viewing aggregated lead and conversion data across all branches.
    </div>
</div>
@endif

<div x-data="{ showFilters: false }" class="mb-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        
        <div class="w-full md:w-auto flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Growth & Inquiries</h1>
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

            <form action="{{ route('reports.growth') }}" method="GET" class="hidden md:flex items-center gap-2 bg-white p-2 rounded-xl border border-gray-200 shadow-sm">
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
                <a href="{{ route('reports.growth') }}" class="bg-gray-100 hover:bg-red-50 text-gray-500 hover:text-red-600 p-2 rounded-lg transition border border-gray-200" title="Reset to FY">
                    <i class="fas fa-undo-alt text-sm"></i>
                </a>
            </form>
        </div>
    </div>

    <div x-show="showFilters" x-collapse class="md:hidden mt-4">
        <form action="{{ route('reports.growth') }}" method="GET" class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex flex-col gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Start Date</label>
                <input type="text" name="start_date" value="{{ request('start_date', $start->format('Y-m-d')) }}" class="datepicker-reports w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 px-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-700 shadow-sm" placeholder="Start Date">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">End Date</label>
                <input type="text" name="end_date" value="{{ request('end_date', $end->format('Y-m-d')) }}" class="datepicker-reports w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 px-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-slate-700 shadow-sm" placeholder="End Date">
            </div>
            <div class="flex gap-2 mt-2">
                <a href="{{ route('reports.growth') }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2.5 rounded-xl transition border border-gray-200 shadow-sm">Reset</a>
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-xl transition shadow-sm">Apply</button>
            </div>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Inquiries</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalInquiries) }}</h3>
            
            <div class="flex items-center gap-1 mt-2 text-xs font-bold">
                @if($trends['inquiries'] >= 0)
                    <span class="text-green-600 bg-green-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-up"></i> {{ number_format($trends['inquiries'], 1) }}%
                    </span>
                @else
                    <span class="text-red-600 bg-red-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-down"></i> {{ number_format(abs($trends['inquiries']), 1) }}%
                    </span>
                @endif
                <span class="text-gray-400 font-medium ml-1">vs previous period</span>
            </div>
        </div>
        <div class="h-12 w-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
            <i class="fas fa-bullhorn"></i>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Slot Reserved (Converted)</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($convertedCount) }}</h3>
            
            <div class="flex items-center gap-1 mt-2 text-xs font-bold">
                @if($trends['converted'] >= 0)
                    <span class="text-green-600 bg-green-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-up"></i> {{ number_format($trends['converted'], 1) }}%
                    </span>
                @else
                    <span class="text-red-600 bg-red-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-down"></i> {{ number_format(abs($trends['converted']), 1) }}%
                    </span>
                @endif
                <span class="text-gray-400 font-medium ml-1">vs previous period</span>
            </div>
        </div>
        <div class="h-12 w-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-xl">
            <i class="fas fa-check-circle"></i>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Conversion Rate</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($conversionRate, 1) }}%</h3>
            
            <div class="flex items-center gap-1 mt-2 text-xs font-bold">
                @if($trends['rate'] >= 0)
                    <span class="text-green-600 bg-green-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-up"></i> {{ number_format($trends['rate'], 1) }}%
                    </span>
                @else
                    <span class="text-red-600 bg-red-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-down"></i> {{ number_format(abs($trends['rate']), 1) }}%
                    </span>
                @endif
                <span class="text-gray-400 font-medium ml-1">vs previous period</span>
            </div>
        </div>
        <div class="h-12 w-12 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
            <i class="fas fa-percent"></i>
        </div>
    </div>
</div>

<div class="bg-white md:p-6 rounded-2xl md:border border-gray-200 shadow-sm mb-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 px-4 pt-4 md:px-0 md:pt-0">
        <h3 class="font-bold text-slate-800">Inquiry Conversion by Source (Cohort Analysis)</h3>
        <span class="text-xs text-gray-400 mt-1 md:mt-0">* Click numbers to view details</span>
    </div>
    
    <div class="hidden md:block overflow-x-auto border rounded-xl border-gray-100">
        <table class="w-full text-left border-collapse text-xs whitespace-nowrap">
            <thead>
                <tr class="bg-gray-50 text-gray-500 uppercase font-bold border-b border-gray-200">
                    <th class="py-3 px-4 border-r border-gray-200 sticky-col min-w-[120px]">Source</th>
                    <th class="py-3 px-4 border-r border-gray-200 sticky-col-2 min-w-[100px]">Inquiry Month</th>
                    <th class="py-3 px-2 border-r border-gray-200 text-center w-24">Total Added</th>
                    
                    <th class="py-1 px-2 border-r border-gray-200 text-center bg-blue-50 text-blue-600" colspan="{{ count($monthGrid) }}">
                        Conversion Month
                    </th>
                    
                    <th class="py-3 px-2 border-r border-gray-200 text-center w-24">Total Conv.</th>
                    <th class="py-3 px-2 text-center w-20">Ratio</th>
                </tr>
                <tr class="bg-gray-50 text-gray-400 text-[10px] uppercase font-bold border-b border-gray-200">
                    <th class="py-1 px-4 border-r border-gray-200 sticky-col"></th>
                    <th class="py-1 px-4 border-r border-gray-200 sticky-col-2"></th>
                    <th class="py-1 px-2 border-r border-gray-200"></th>
                    @foreach($monthGrid as $mLabel)
                        <th class="py-1 px-2 border-r border-gray-200 text-center min-w-[50px]">{{ $mLabel }}</th>
                    @endforeach
                    <th class="py-1 px-2 border-r border-gray-200"></th>
                    <th class="py-1 px-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($cohortMatrix as $sourceName => $months)
                    @php $first = true; $rowCount = count($months); @endphp
                    @foreach($months as $mKey => $row)
                        <tr class="hover:bg-gray-50 transition">
                            @if($first)
                                <td class="py-3 px-4 font-bold text-gray-800 border-r border-gray-200 bg-white sticky-col align-top pt-4" rowspan="{{ $rowCount }}">
                                    {{ $sourceName }}
                                </td>
                                @php $first = false; @endphp
                            @endif

                            <td class="py-3 px-4 text-gray-600 border-r border-gray-200 font-medium sticky-col-2 bg-white">
                                {{ $monthGrid[$mKey] }}
                            </td>

                            <td class="py-3 px-2 text-center border-r border-gray-200 font-bold bg-gray-50">
                                @if($row['total_added'] > 0)
                                    <a href="{{ route('inquiries.index', ['lead_source_id' => $row['source_id'], 'created_month' => $mKey]) }}" 
                                       class="text-blue-600 hover:underline" target="_blank">
                                        {{ $row['total_added'] }}
                                    </a>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>

                            @foreach($monthGrid as $cKey => $cLabel)
                                <td class="py-3 px-2 text-center border-r border-gray-100">
                                    @php $conv = $row['conversions'][$cKey] ?? 0; @endphp
                                    @if($conv > 0)
                                        <a href="{{ route('inquiries.index', ['lead_source_id' => $row['source_id'], 'created_month' => $mKey, 'converted_month' => $cKey, 'status' => 'Slot Reserved']) }}" 
                                           class="text-green-600 font-bold hover:underline" target="_blank">
                                            {{ $conv }}
                                        </a>
                                    @else
                                        <span class="text-gray-100 text-[10px]">-</span>
                                    @endif
                                </td>
                            @endforeach

                            <td class="py-3 px-2 text-center border-r border-gray-200 font-bold bg-green-50 text-green-700">
                                @if($row['total_converted'] > 0)
                                    <a href="{{ route('inquiries.index', ['lead_source_id' => $row['source_id'], 'created_month' => $mKey, 'status' => 'Slot Reserved']) }}" 
                                       class="hover:underline" target="_blank">
                                        {{ $row['total_converted'] }}
                                    </a>
                                @else
                                    0
                                @endif
                            </td>

                            <td class="py-3 px-2 text-center text-gray-500 font-mono text-[11px]">
                                {{ $row['total_added'] > 0 ? number_format(($row['total_converted'] / $row['total_added']) * 100, 1) : '0.0' }}%
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr><td colspan="20" class="py-8 text-center text-gray-400">No data found for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="block md:hidden space-y-4 px-4 pb-4">
        @forelse($cohortMatrix as $sourceName => $months)
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 shadow-sm">
                <h4 class="font-bold text-slate-800 mb-3 text-lg border-b border-gray-200 pb-2">
                    <i class="fas fa-share-alt text-gray-400 text-sm mr-1"></i> {{ $sourceName }}
                </h4>
                
                <div class="space-y-3">
                    @foreach($months as $mKey => $row)
                        <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">Inq. Month</span>
                                <span class="text-sm font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">{{ $monthGrid[$mKey] }}</span>
                            </div>
                            
                            <div class="grid grid-cols-3 gap-2 mb-3 border-t border-gray-100 pt-2">
                                <div class="text-center">
                                    <div class="text-[10px] text-gray-400 uppercase font-bold mb-0.5">Added</div>
                                    @if($row['total_added'] > 0)
                                        <a href="{{ route('inquiries.index', ['lead_source_id' => $row['source_id'], 'created_month' => $mKey]) }}" 
                                           class="font-bold text-blue-600 hover:underline text-sm" target="_blank">
                                            {{ $row['total_added'] }}
                                        </a>
                                    @else
                                        <span class="font-bold text-gray-800 text-sm">0</span>
                                    @endif
                                </div>
                                <div class="text-center border-l border-r border-gray-100">
                                    <div class="text-[10px] text-gray-400 uppercase font-bold mb-0.5">Converted</div>
                                    @if($row['total_converted'] > 0)
                                        <a href="{{ route('inquiries.index', ['lead_source_id' => $row['source_id'], 'created_month' => $mKey, 'status' => 'Slot Reserved']) }}" 
                                           class="font-bold text-green-600 hover:underline text-sm" target="_blank">
                                            {{ $row['total_converted'] }}
                                        </a>
                                    @else
                                        <span class="font-bold text-green-600 text-sm">0</span>
                                    @endif
                                </div>
                                <div class="text-center">
                                    <div class="text-[10px] text-gray-400 uppercase font-bold mb-0.5">Ratio</div>
                                    <div class="font-mono text-gray-600 text-xs mt-0.5">
                                        {{ $row['total_added'] > 0 ? number_format(($row['total_converted'] / $row['total_added']) * 100, 1) : '0.0' }}%
                                    </div>
                                </div>
                            </div>
                            
                            @if(array_filter($row['conversions'] ?? []))
                            <div class="bg-gray-50 rounded-lg p-2 mt-2">
                                <div class="text-[10px] font-bold text-gray-500 uppercase mb-1.5 flex items-center gap-1">
                                    <i class="fas fa-history text-gray-400"></i> Conversion Timeline:
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($monthGrid as $cKey => $cLabel)
                                        @php $conv = $row['conversions'][$cKey] ?? 0; @endphp
                                        @if($conv > 0)
                                            <a href="{{ route('inquiries.index', ['lead_source_id' => $row['source_id'], 'created_month' => $mKey, 'converted_month' => $cKey, 'status' => 'Slot Reserved']) }}" 
                                               class="text-[11px] bg-green-50 text-green-700 border border-green-200 px-2 py-1 rounded hover:bg-green-100 transition shadow-sm" target="_blank">
                                                {{ $cLabel }}: <b class="ml-1">{{ $conv }}</b>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center text-gray-400 py-6 bg-gray-50 rounded-xl border border-gray-100">
                <i class="fas fa-chart-line text-2xl mb-2 opacity-20"></i>
                <p class="text-sm">No data found for this period.</p>
            </div>
        @endforelse
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <h3 class="font-bold text-slate-800 mb-4">Avg. Time to Convert Inquiry (by Source)</h3>
        <div class="overflow-y-auto max-h-64">
            <table class="w-full">
                <thead class="sticky top-0 bg-white">
                    <tr class="text-xs text-gray-400 text-left border-b border-gray-100">
                        <th class="pb-2 pl-2">Source</th>
                        <th class="pb-2 pr-2 text-right">Avg Duration</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($avgTimeSource as $row)
                    <tr>
                        <td class="py-3 pl-2 text-sm font-medium text-gray-700">{{ $row->source }}</td>
                        <td class="py-3 pr-2 text-sm font-bold text-blue-600 text-right">{{ number_format($row->avg_days, 1) }} days</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="py-4 text-center text-xs text-gray-400">No converted inquiries yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <h3 class="font-bold text-slate-800 mb-4">Avg. Time to Convert Inquiry (by Staff)</h3>
        <div class="overflow-y-auto max-h-64 space-y-4 pr-2">
            @forelse($avgTimeStaff as $staffName => $sources)
                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 border-b border-gray-100 pb-1">{{ $staffName }}</h4>
                    <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                        @foreach($sources as $row)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">{{ $row->source }}</span>
                                <span class="font-bold text-gray-800">{{ number_format($row->avg_days, 1) }} days</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center text-xs text-gray-400 py-8">No staff conversion data available.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <h3 class="font-bold text-slate-800 mb-4">Inquiry Volume Trend</h3>
        <div id="trendChart"></div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <h3 class="font-bold text-slate-800 mb-4">Inquiry Source Distribution</h3>
        @if(count($sourceValues) > 0)
            <div id="sourceChart" class="flex justify-center"></div>
        @else
            <div class="h-64 flex items-center justify-center text-gray-400 text-sm">No data available</div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <h3 class="font-bold text-slate-800 mb-4">Inquiry Status Funnel</h3>
        @if(count($statusValues) > 0)
            <div id="statusChart"></div>
        @else
            <div class="h-64 flex items-center justify-center text-gray-400 text-sm">No data available</div>
        @endif
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <h3 class="font-bold text-slate-800 mb-4">Staff Workload (Top 8)</h3>
        @if(count($staffValues) > 0)
            <div id="staffChart"></div>
        @else
            <div class="h-64 flex items-center justify-center text-gray-400 text-sm">No data available</div>
        @endif
    </div>
</div>

<div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-6 overflow-hidden">
    <div class="py-5 px-6 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-lg font-bold text-slate-800">Staff Conversion Leaderboard (Win Rate)</h3>
    </div>
    <div class="overflow-x-auto p-2">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                    <th class="py-4 px-4 rounded-l-lg w-16 text-center">Rank</th>
                    <th class="py-4 px-4">Sales Rep / Staff</th>
                    <th class="py-4 px-4 text-center">Total Assigned</th>
                    <th class="py-4 px-4 text-center text-green-600">Won (Converted)</th>
                    <th class="py-4 px-4 text-right rounded-r-lg min-w-[200px]">Win Rate %</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($staffLeaderboard as $index => $staff)
                <tr class="hover:bg-gray-50 transition">
                    <td class="py-4 px-4 text-center">
                        @if($index == 0)
                            <div class="h-8 w-8 mx-auto rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center font-bold border border-yellow-200"><i class="fas fa-trophy"></i></div>
                        @elseif($index == 1)
                            <div class="h-8 w-8 mx-auto rounded-full bg-gray-200 text-gray-600 flex items-center justify-center font-bold border border-gray-300">2</div>
                        @elseif($index == 2)
                            <div class="h-8 w-8 mx-auto rounded-full bg-orange-100 text-orange-600 flex items-center justify-center font-bold border border-orange-200">3</div>
                        @else
                            <div class="h-8 w-8 mx-auto rounded-full text-gray-400 flex items-center justify-center font-bold">{{ $index + 1 }}</div>
                        @endif
                    </td>
                    <td class="py-4 px-4 font-bold text-slate-800">{{ $staff->name }}</td>
                    <td class="py-4 px-4 text-center font-bold text-gray-600">{{ $staff->total_assigned }}</td>
                    <td class="py-4 px-4 text-center font-bold text-green-600">{{ $staff->total_converted }}</td>
                    <td class="py-4 px-4 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <span class="text-xs font-black {{ $staff->win_rate >= 50 ? 'text-green-600' : ($staff->win_rate >= 20 ? 'text-blue-600' : 'text-gray-500') }}">
                                {{ number_format($staff->win_rate, 1) }}%
                            </span>
                            <div class="w-full max-w-[120px] h-2.5 bg-gray-100 rounded-full overflow-hidden border border-gray-200">
                                <div class="h-full rounded-full {{ $staff->win_rate >= 50 ? 'bg-green-500' : ($staff->win_rate >= 20 ? 'bg-blue-500' : 'bg-gray-400') }}" style="width: {{ $staff->win_rate }}%"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-8 text-center text-gray-400">No staff data available for leaderboard.</td></tr>
                @endforelse
            </tbody>
        </table>
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
        series: [{ name: 'Inquiries', data: @json(array_values($trendData)) }],
        chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'Satoshi, sans-serif' },
        colors: ['#3b82f6'],
        stroke: { curve: 'smooth', width: 3 },
        fill: { opacity: 0.1 },
        xaxis: { categories: @json(array_keys($trendData)) },
        dataLabels: { enabled: false },
        grid: { borderColor: '#f1f5f9' }
    };
    new ApexCharts(document.querySelector("#trendChart"), trendOptions).render();

    // 2. Source Pie Chart
    @if(count($sourceValues) > 0)
    var sourceOptions = {
        series: @json($sourceValues),
        labels: @json($sourceLabels),
        chart: { type: 'donut', height: 300, fontFamily: 'Satoshi, sans-serif' },
        colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
        legend: { position: 'bottom' },
        dataLabels: { enabled: false },
        plotOptions: { pie: { donut: { size: '65%' } } }
    };
    new ApexCharts(document.querySelector("#sourceChart"), sourceOptions).render();
    @endif

    // 3. Status Bar Chart
    @if(count($statusValues) > 0)
    var statusOptions = {
        series: [{ name: 'Count', data: @json($statusValues) }],
        chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Satoshi, sans-serif' },
        colors: ['#8b5cf6'],
        plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '50%' } },
        xaxis: { categories: @json($statusLabels) },
        grid: { borderColor: '#f1f5f9' }
    };
    new ApexCharts(document.querySelector("#statusChart"), statusOptions).render();
    @endif

    // 4. Staff Bar Chart
    @if(count($staffValues) > 0)
    var staffOptions = {
        series: [{ name: 'Inquiries Assigned', data: @json($staffValues) }],
        chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Satoshi, sans-serif' },
        colors: ['#f59e0b'],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
        xaxis: { categories: @json($staffLabels) },
        grid: { borderColor: '#f1f5f9' }
    };
    new ApexCharts(document.querySelector("#staffChart"), staffOptions).render();
    @endif
</script>
@endsection