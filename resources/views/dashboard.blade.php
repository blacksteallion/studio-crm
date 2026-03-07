@extends('layouts.app')
@section('header', 'Executive Dashboard')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

@php
    $isGlobal = session('active_location_id') === 'all';
@endphp

@if($isGlobal)
<div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-xl flex items-center gap-3">
    <i class="fas fa-globe text-xl"></i>
    <div class="text-sm">
        <strong>Global View Active:</strong> You are viewing aggregated data across all studio branches. Select a specific location from the top navigation to view isolated metrics.
    </div>
</div>
@endif

@canany(['view orders', 'view reports'])
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between hover:shadow-md transition relative overflow-hidden">
        <div class="absolute left-0 top-0 bottom-0 w-1 bg-green-500"></div>
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Revenue (This Month)</p>
            <h3 class="text-2xl font-black text-slate-800 mt-1">₹{{ number_format($monthRevenue ?? 0) }}</h3>
            
            <div class="flex items-center gap-1 mt-2 text-xs font-bold">
                @if(($trends['revenue'] ?? 0) >= 0)
                    <span class="text-green-600 bg-green-50 px-1.5 py-0.5 rounded flex items-center gap-1 border border-green-100">
                        <i class="fas fa-arrow-up"></i> {{ number_format($trends['revenue'] ?? 0, 1) }}%
                    </span>
                @else
                    <span class="text-red-600 bg-red-50 px-1.5 py-0.5 rounded flex items-center gap-1 border border-red-100">
                        <i class="fas fa-arrow-down"></i> {{ number_format(abs($trends['revenue'] ?? 0), 1) }}%
                    </span>
                @endif
                <span class="text-gray-400 font-medium ml-1">vs last month</span>
            </div>
        </div>
        <div class="h-14 w-14 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-2xl shadow-inner">
            <i class="fas fa-coins"></i>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between hover:shadow-md transition relative overflow-hidden">
        <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Expenses (This Month)</p>
            <h3 class="text-2xl font-black text-slate-800 mt-1">₹{{ number_format($monthExpense ?? 0) }}</h3>
            
            <div class="flex items-center gap-1 mt-2 text-xs font-bold">
                @if(($trends['expense'] ?? 0) > 0)
                    <span class="text-red-600 bg-red-50 px-1.5 py-0.5 rounded flex items-center gap-1 border border-red-100">
                        <i class="fas fa-arrow-up"></i> {{ number_format($trends['expense'] ?? 0, 1) }}%
                    </span>
                @else
                    <span class="text-green-600 bg-green-50 px-1.5 py-0.5 rounded flex items-center gap-1 border border-green-100">
                        <i class="fas fa-arrow-down"></i> {{ number_format(abs($trends['expense'] ?? 0), 1) }}%
                    </span>
                @endif
                <span class="text-gray-400 font-medium ml-1">vs last month</span>
            </div>
        </div>
        <div class="h-14 w-14 rounded-full bg-red-50 text-red-600 flex items-center justify-center text-2xl shadow-inner">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between hover:shadow-md transition relative overflow-hidden">
        <div class="absolute left-0 top-0 bottom-0 w-1 {{ ($monthProfit ?? 0) >= 0 ? 'bg-blue-500' : 'bg-red-500' }}"></div>
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Net Profit (This Month)</p>
            <h3 class="text-2xl font-black {{ ($monthProfit ?? 0) >= 0 ? 'text-blue-600' : 'text-red-600' }} mt-1">
                ₹{{ number_format($monthProfit ?? 0) }}
            </h3>
            
            <div class="flex items-center gap-1 mt-2 text-xs font-bold">
                @if(($trends['profit'] ?? 0) >= 0)
                    <span class="text-green-600 bg-green-50 px-1.5 py-0.5 rounded flex items-center gap-1 border border-green-100">
                        <i class="fas fa-arrow-up"></i> {{ number_format($trends['profit'] ?? 0, 1) }}%
                    </span>
                @else
                    <span class="text-red-600 bg-red-50 px-1.5 py-0.5 rounded flex items-center gap-1 border border-red-100">
                        <i class="fas fa-arrow-down"></i> {{ number_format(abs($trends['profit'] ?? 0), 1) }}%
                    </span>
                @endif
                <span class="text-gray-400 font-medium ml-1">vs last month</span>
            </div>
        </div>
        <div class="h-14 w-14 rounded-full {{ ($monthProfit ?? 0) >= 0 ? 'bg-blue-50 text-blue-600' : 'bg-red-50 text-red-600' }} flex items-center justify-center text-2xl shadow-inner">
            <i class="fas fa-chart-line"></i>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2">
        <x-card title="Financial Trend (6 Months)">
            <div class="p-4">
                <div id="trendChart" class="flex justify-center -ml-2"></div>
            </div>
        </x-card>
    </div>
    
    @can('view inquiries')
    <div>
        <x-card title="Lead Pipeline (This Month)">
            <div class="p-5 flex items-center justify-center h-full">
                @if(array_sum($funnelData ?? []) > 0)
                    <div id="pipelineChart" class="flex justify-center -ml-2"></div>
                @else
                    <div class="text-center text-gray-400 py-10">
                        <i class="fas fa-filter text-4xl mb-3 opacity-20"></i>
                        <p class="text-sm font-medium">No inquiry data for this month yet.</p>
                    </div>
                @endif
            </div>
        </x-card>
    </div>
    @endcan
</div>
@endcanany

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    @can('view bookings')
    <div class="space-y-6">
        <x-card>
            <x-slot name="title">
                <div class="flex items-center gap-2">
                    <div class="bg-blue-100 text-blue-600 p-1.5 rounded-lg"><i class="fas fa-calendar-day"></i></div>
                    <span>Today's Schedule</span>
                </div>
            </x-slot>
            
            <x-slot name="action">
                <span class="text-xs font-bold bg-gray-100 text-gray-600 px-2 py-1 rounded border border-gray-200 uppercase">
                    {{ \Carbon\Carbon::today()->format('d M, Y') }}
                </span>
            </x-slot>

            <div class="p-4 max-h-[400px] overflow-y-auto no-scrollbar">
                @forelse($todaysSchedule ?? [] as $booking)
                <div class="flex items-center gap-4 p-3 mb-3 bg-white rounded-xl border border-gray-200 hover:border-blue-300 shadow-sm transition group relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1 {{ $booking->status == 'Completed' ? 'bg-green-500' : 'bg-blue-500' }}"></div>
                    
                    <div class="pl-2 w-16 text-center border-r border-gray-100">
                        <span class="block text-sm font-black text-slate-800">{{ \Carbon\Carbon::parse($booking->start_time)->format('h:i') }}</span>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">{{ \Carbon\Carbon::parse($booking->start_time)->format('A') }}</span>
                    </div>

                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">{{ $booking->customer->name }}</h4>
                                <p class="text-[11px] text-blue-600 font-bold mt-0.5 uppercase tracking-wide">
                                    <i class="fas fa-concierge-bell mr-1"></i> {{ $booking->service_name ?? 'Studio Service' }}
                                </p>
                                @if($isGlobal)
                                    <p class="text-[10px] text-red-500 font-bold mt-1.5"><i class="fas fa-map-marker-alt"></i> {{ $booking->location->name ?? 'Unassigned' }}</p>
                                @endif
                            </div>
                            <a href="{{ route('bookings.show', $booking->id) }}" class="h-8 w-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                    <div class="bg-gray-50 p-4 rounded-full mb-3 border border-gray-100">
                        <i class="far fa-calendar-check text-2xl text-gray-300"></i>
                    </div>
                    <p class="text-sm font-medium">No bookings scheduled for today.</p>
                    <a href="{{ route('bookings.create') }}" class="mt-4 text-blue-600 font-bold text-xs hover:underline uppercase tracking-wide">Schedule One Now</a>
                </div>
                @endforelse
            </div>
        </x-card>
    </div>
    @endcan

    @can('view inquiries')
    <div class="space-y-6">
        <x-card title="Fresh Inquiries">
            <x-slot name="action">
                <a href="{{ route('inquiries.index') }}" class="text-[10px] font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded border border-blue-100 uppercase tracking-wide transition">View All</a>
            </x-slot>

            <div class="divide-y divide-gray-50">
                @forelse($recentInquiries ?? [] as $inquiry)
                <a href="{{ route('inquiries.show', $inquiry->id) }}" class="flex items-center justify-between p-5 hover:bg-blue-50 transition group">
                    <div>
                        <div class="font-bold text-sm text-slate-800">{{ $inquiry->customer->name }}</div>
                        <div class="flex items-center gap-3 mt-1">
                            <span class="text-[10px] text-gray-400 font-medium"><i class="far fa-clock"></i> {{ $inquiry->created_at->diffForHumans() }}</span>
                            @if($isGlobal)
                                <span class="text-[10px] text-red-500 font-bold"><i class="fas fa-map-marker-alt"></i> {{ $inquiry->location->name ?? 'Unassigned' }}</span>
                            @endif
                        </div>
                    </div>
                    <x-badge :status="$inquiry->status" />
                </a>
                @empty
                <div class="p-10 text-center text-sm font-medium text-gray-400 flex flex-col items-center">
                    <i class="fas fa-inbox text-3xl mb-3 opacity-20"></i>
                    No new inquiries to show.
                </div>
                @endforelse
            </div>
        </x-card>

        @can('create inquiries')
        <a href="{{ route('inquiries.create') }}" class="flex items-center justify-center w-full py-4 rounded-xl border border-dashed border-gray-300 bg-white text-sm font-bold text-gray-500 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition shadow-sm">
            <i class="fas fa-plus-circle mr-2 text-lg"></i> Record New Inquiry
        </a>
        @endcan
    </div>
    @endcan

</div>

@canany(['view orders', 'view reports'])
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- 6 MONTH FINANCIAL TREND CHART ---
        var trendOptions = {
            series: [{
                name: 'Revenue',
                data: @json($revenueTrend ?? [])
            }, {
                name: 'Expenses',
                data: @json($expenseTrend ?? [])
            }],
            chart: { 
                type: 'area', 
                height: 280, 
                fontFamily: 'Outfit, sans-serif', 
                toolbar: { show: false },
                animations: { enabled: true, easing: 'easeinout', speed: 800 }
            },
            colors: ['#2563eb', '#ef4444'],
            fill: { 
                type: 'gradient', 
                gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05, stops: [0, 90, 100] } 
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2.5 },
            xaxis: { 
                categories: @json($chartLabels ?? []), 
                tooltip: { enabled: false },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: { 
                labels: { 
                    formatter: function (value) { 
                        return "₹" + (value >= 1000 ? (value/1000).toFixed(1) + 'k' : value); 
                    },
                    style: { colors: '#64748b', fontWeight: 500 }
                } 
            },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            legend: { position: 'top', horizontalAlign: 'right', fontWeight: 600, markers: { radius: 12 } },
            tooltip: { 
                theme: 'light',
                y: { formatter: function (val) { return "₹" + val.toLocaleString('en-IN'); } } 
            }
        };
        if(document.querySelector("#trendChart")) {
            new ApexCharts(document.querySelector("#trendChart"), trendOptions).render();
        }

        // --- INQUIRY FUNNEL CHART ---
        @if(array_sum($funnelData ?? []) > 0)
        var pipelineOptions = {
            series: @json(array_values($funnelData ?? [])),
            labels: @json(array_keys($funnelData ?? [])),
            chart: { type: 'donut', height: 260, fontFamily: 'Outfit, sans-serif' },
            colors: ['#3b82f6', '#f59e0b', '#8b5cf6', '#10b981', '#ef4444'],
            legend: { position: 'right', fontSize: '12px', fontWeight: 500, markers: { width: 10, height: 10, radius: 10 } },
            dataLabels: { enabled: false },
            plotOptions: { 
                pie: { 
                    donut: { 
                        size: '75%',
                        labels: {
                            show: true,
                            name: { fontSize: '11px', fontWeight: 600, color: '#64748b' },
                            value: { fontSize: '20px', fontWeight: 800, color: '#1e293b' },
                            total: {
                                show: true,
                                label: 'Total Leads',
                                fontSize: '11px',
                                fontWeight: 600,
                                color: '#64748b'
                            }
                        }
                    } 
                } 
            },
            stroke: { show: false }
        };
        if(document.querySelector("#pipelineChart")) {
            new ApexCharts(document.querySelector("#pipelineChart"), pipelineOptions).render();
        }
        @endif
    });
</script>
@endcanany

@endsection