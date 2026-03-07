@extends('layouts.app')
@section('header', 'Transactions')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar {
        border-radius: 1rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .flatpickr-day.selected {
        background: #2563eb;
        border-color: #2563eb;
    }
    
    /* Strictly force equal widths for all 7 columns on desktop */
    .equal-cols-table table {
        table-layout: fixed !important;
        width: 100% !important;
    }
    .equal-cols-table table th,
    .equal-cols-table table td {
        width: 14.285% !important;
        word-wrap: break-word !important;
        white-space: normal !important;
    }
</style>

<x-card :title="(request('date') == 'today' ? 'Today\'s ' : '') . 'Payment History'">
    
    <x-slot name="action">
        <div class="flex items-center justify-end gap-1.5 sm:gap-2">
            
            <button @click="$dispatch('toggle-search')" class="md:hidden h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-black hover:text-blue-600 hover:border-blue-300 transition shadow-sm" title="Toggle Search">
                <i class="fas fa-search"></i>
            </button>

            <form action="{{ route('payments.index') }}" method="GET" class="hidden md:block relative w-64 search-form">
                @foreach(request()->only(['start_date', 'end_date', 'payment_method', 'min_amount', 'max_amount', 'customer_id']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach

                <div class="relative h-9">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-black">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search"
                           class="search-input h-full w-full rounded-lg border border-gray-200 bg-gray-50 pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-black text-black" 
                           placeholder="Search payments..." 
                           value="{{ request('search') }}"
                           autocomplete="off">
                </div>
            </form>

            <button @click="$dispatch('toggle-advanced')" class="h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-black hover:text-blue-600 hover:border-blue-300 transition shadow-sm" title="Advanced Search">
                <i class="fas fa-sliders-h"></i>
            </button>

            @can('export payments')
            <button type="button" onclick="confirmExport()" class="h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-green-600 hover:text-green-700 hover:border-green-300 transition shadow-sm" title="Export to Excel">
                <i class="fas fa-file-excel"></i>
            </button>
            @endcan
        </div>
    </x-slot>

    <div x-data="{ searchOpen: {{ request('search') ? 'true' : 'false' }} }"
         @toggle-search.window="searchOpen = !searchOpen; if(searchOpen) $dispatch('close-advanced')"
         @close-search.window="searchOpen = false"
         x-show="searchOpen" x-collapse
         class="md:hidden bg-gray-50 border-b border-gray-100 p-4">
        <form action="{{ route('payments.index') }}" method="GET" class="relative w-full search-form">
            @foreach(request()->only(['start_date', 'end_date', 'payment_method', 'min_amount', 'max_amount', 'customer_id']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <div class="relative h-10 w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-black">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" 
                    class="search-input h-full w-full rounded-xl border border-gray-200 bg-white pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-gray-400 text-black shadow-sm" 
                    placeholder="Search payments..." 
                    value="{{ request('search') }}" 
                    autocomplete="off">
            </div>
        </form>
    </div>

    <div x-data="{ show: {{ request()->hasAny(['start_date', 'end_date', 'payment_method', 'min_amount', 'max_amount']) ? 'true' : 'false' }} }"
         @toggle-advanced.window="show = !show; if(show) $dispatch('close-search')"
         @close-advanced.window="show = false"
         x-show="show" x-collapse
         class="bg-gray-50 border-b border-gray-100 p-5 mb-4">
        
        <form action="{{ route('payments.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4">
                
                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">From Date</label>
                    <div class="relative">
                        <input type="text" name="start_date" value="{{ request('start_date') }}" class="datepicker w-full rounded-xl border border-gray-200 bg-white py-2.5 px-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black placeholder-black shadow-sm" placeholder="Select Date">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-black">
                            <i class="fas fa-calendar-alt text-xs"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">To Date</label>
                    <div class="relative">
                        <input type="text" name="end_date" value="{{ request('end_date') }}" class="datepicker w-full rounded-xl border border-gray-200 bg-white py-2.5 px-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black placeholder-black shadow-sm" placeholder="Select Date">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-black">
                            <i class="fas fa-calendar-alt text-xs"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">Method</label>
                    <div class="relative z-20 bg-white rounded-xl shadow-sm">
                        <select name="payment_method" class="relative z-20 w-full appearance-none rounded-xl border border-gray-200 bg-transparent py-2.5 pl-4 pr-10 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black">
                            <option value="">All Methods</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method }}" {{ request('payment_method') == $method ? 'selected' : '' }}>
                                    {{ $method }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute top-1/2 right-3 z-30 -translate-y-1/2 text-black">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">Min Amount</label>
                    <div class="relative shadow-sm rounded-xl">
                        <span class="absolute top-1/2 left-3 -translate-y-1/2 text-black text-xs">₹</span>
                        <input type="number" name="min_amount" value="{{ request('min_amount') }}" class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-7 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-gray-400 text-black" placeholder="0.00">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">Max Amount</label>
                    <div class="relative shadow-sm rounded-xl">
                        <span class="absolute top-1/2 left-3 -translate-y-1/2 text-black text-xs">₹</span>
                        <input type="number" name="max_amount" value="{{ request('max_amount') }}" class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-7 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-gray-400 text-black" placeholder="0.00">
                    </div>
                </div>

            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('payments.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-black shadow-sm hover:bg-gray-50 transition">
                    <i class="fas fa-undo"></i> Reset
                </a>

                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <div class="hidden md:block equal-cols-table">
        <x-table :headers="['Date', 'Customer', 'Invoice', 'Location', 'Method', 'Amount', 'Actions']">
            @forelse ($payments as $payment)
            <tr class="hover:bg-gray-50 transition duration-200">
                
                <td class="px-6 py-4 align-top">
                    <div class="text-sm text-black font-medium">
                        {{ $payment->transaction_date->format('d-M-Y') }}
                    </div>
                    <div class="text-xs text-black mt-0.5">
                        {{ $payment->created_at->format('h:i A') }}
                    </div>
                </td>

                <td class="px-6 py-4 align-top">
                    @if($payment->order && $payment->order->customer)
                        <div class="font-bold text-black text-sm">
                            {{ $payment->order->customer->business_name ?? 'Individual' }}
                        </div>
                        <div class="text-sm text-black mt-0.5 font-medium">
                            <i class="far fa-user mr-1"></i> {{ $payment->order->customer->name }}
                        </div>
                    @else
                        <div class="font-bold text-black text-sm">Individual</div>
                        <div class="text-sm text-black mt-0.5 font-medium">
                            <i class="far fa-user mr-1"></i> Deleted Customer
                        </div>
                    @endif
                </td>

                <td class="px-6 py-4 align-top">
                    @if($payment->order)
                        <a href="{{ route('orders.show', $payment->order_id) }}" class="font-bold text-blue-600 text-sm hover:underline">
                            {{ $payment->order->invoice_number }}
                        </a>
                    @else
                        <span class="text-black text-sm italic">-</span>
                    @endif
                </td>

                <td class="px-6 py-4 align-top">
                    @if($payment->order)
                        <div class="text-[11px] font-bold text-red-500">
                            <i class="fas fa-map-marker-alt"></i> {{ $payment->order->location->name ?? 'Unassigned' }}
                        </div>
                    @else
                        <span class="text-black text-sm italic">-</span>
                    @endif
                </td>

                <td class="px-6 py-4 align-top">
                    <div class="text-xs text-black font-medium bg-gray-100 inline-block px-1.5 py-0.5 rounded border border-gray-200">
                        {{ $payment->payment_method }}
                    </div>
                    @if($payment->reference_number)
                        <div class="text-xs text-black mt-1 font-mono">Ref: {{ $payment->reference_number }}</div>
                    @endif
                </td>

                <td class="px-6 py-4 align-top text-right">
                    <div class="font-bold text-black text-sm font-mono">₹{{ number_format($payment->amount, 2) }}</div>
                </td>

                <td class="px-6 py-4 align-top text-right">
                    <div class="flex justify-end items-center gap-1">
                        @can('delete payments')
                        <form id="del-pay-{{ $payment->id }}" action="{{ route('payments.destroy', $payment->id) }}" method="POST" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                        
                        <x-button variant="secondary" type="button" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-red-500 hover:!bg-red-50" title="Delete"
                            onclick="confirmDelete('del-pay-{{ $payment->id }}', 'Are you sure you want to delete this payment record? This will update the invoice balance.')">
                            <i class="fas fa-trash-alt"></i>
                        </x-button>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No payments found.</td>
                </tr>
            @endforelse

            <x-slot name="pagination">
                @if($payments->hasPages())
                    {{ $payments->withQueryString()->links() }}
                @endif
            </x-slot>
        </x-table>
    </div>

    <div class="block md:hidden space-y-4 p-4">
        @forelse ($payments as $payment)
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 flex flex-col gap-4">
            
            <div class="flex justify-between items-start border-b border-gray-100 pb-3">
                <div>
                    <div class="font-bold text-gray-900 text-lg font-mono leading-tight">₹{{ number_format($payment->amount, 2) }}</div>
                    <div class="text-[11px] text-gray-500 mt-0.5">
                        {{ $payment->transaction_date->format('d M, Y') }} at {{ $payment->created_at->format('h:i A') }}
                    </div>
                </div>
                <div>
                    <span class="text-[10px] text-gray-700 font-bold uppercase bg-gray-100 px-2 py-1 rounded border border-gray-200">
                        {{ $payment->payment_method }}
                    </span>
                </div>
            </div>

            <div class="flex flex-col gap-1.5 bg-gray-50 p-3 rounded-xl border border-gray-100">
                @if($payment->order && $payment->order->customer)
                    <div class="font-bold text-gray-900 text-sm">{{ $payment->order->customer->business_name ?? 'Individual' }}</div>
                    <div class="text-[12px] text-gray-700 flex items-center gap-2">
                        <i class="far fa-user w-3 text-center opacity-70"></i> {{ $payment->order->customer->name }}
                    </div>
                @else
                    <div class="font-bold text-gray-900 text-sm">Individual</div>
                    <div class="text-[12px] text-gray-700 flex items-center gap-2">
                        <i class="far fa-user w-3 text-center opacity-70"></i> Deleted Customer
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-between">
                <div>
                    @if($payment->reference_number)
                        <div class="text-[11px] text-gray-600 font-mono">Ref: {{ $payment->reference_number }}</div>
                    @endif
                </div>
                @if($payment->order)
                    <div class="flex flex-col items-end gap-1">
                        <a href="{{ route('orders.show', $payment->order_id) }}" class="text-[11px] text-blue-600 font-medium bg-blue-50 hover:bg-blue-100 inline-flex items-center gap-1.5 px-2 py-1 rounded border border-blue-100 transition">
                            <i class="fas fa-link text-[10px]"></i> {{ $payment->order->invoice_number }}
                        </a>
                        <div class="text-[10px] font-bold text-red-500">
                            <i class="fas fa-map-marker-alt"></i> {{ $payment->order->location->name ?? 'Unassigned' }}
                        </div>
                    </div>
                @endif
            </div>

            @can('delete payments')
            <div class="flex items-center justify-end pt-2 border-t border-gray-100 gap-1.5">
                <form id="del-pay-mobile-{{ $payment->id }}" action="{{ route('payments.destroy', $payment->id) }}" method="POST" class="hidden">
                    @csrf @method('DELETE')
                </form>
                <button type="button" class="p-2 rounded-lg bg-gray-50 text-red-600 hover:text-red-800 border border-gray-200 shadow-sm transition" title="Delete"
                    onclick="confirmDelete('del-pay-mobile-{{ $payment->id }}', 'Are you sure you want to delete this payment record? This will update the invoice balance.')">
                    <i class="fas fa-trash-alt w-3.5 h-3.5 flex items-center justify-center"></i>
                </button>
            </div>
            @endcan
            
        </div>
        @empty
            <div class="p-4 text-center text-gray-500 bg-white border border-gray-200 rounded-2xl">
                No payments found.
            </div>
        @endforelse

        @if($payments->hasPages())
            <div class="pt-2">
                {{ $payments->withQueryString()->links() }}
            </div>
        @endif
    </div>

</x-card>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Flatpickr
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
            allowInput: true,
            altInput: true,
            altFormat: "F j, Y",
            placeholder: "Select Date"
        });

        // Handle BOTH desktop and mobile search inputs
        const searchInputs = document.querySelectorAll('.search-input');
        let timeout = null;

        searchInputs.forEach(input => {
            // Restore focus safely
            if (input.value.length > 0 && document.activeElement !== input) {
                if(window.innerWidth > 768) {
                    input.focus();
                    const val = input.value;
                    input.value = '';
                    input.value = val;
                }
            }

            // Auto-submit search (Debounced)
            input.addEventListener('input', function() {
                clearTimeout(timeout);
                const val = this.value;
                const form = this.closest('.search-form');
                
                timeout = setTimeout(() => {
                    if (val.length >= 2 || val.length === 0) {
                        form.submit();
                    }
                }, 800); 
            });
        });
    });

    @can('export payments')
    // Excel Export Confirmation
    function confirmExport() {
        // Get current URL parameters to preserve filters
        const urlParams = new URLSearchParams(window.location.search);
        const exportUrl = "{{ route('payments.export') }}?" + urlParams.toString();

        window.dispatchEvent(new CustomEvent('confirm', { 
            detail: { 
                title: 'Export Confirmation', 
                message: 'Do you want to export the current filtered list of payments to Excel?', 
                type: 'info', 
                action: () => window.location.href = exportUrl 
            } 
        }));
    }
    @endcan
</script>
@endsection