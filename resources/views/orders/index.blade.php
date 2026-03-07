@extends('layouts.app')
@section('header', 'Invoices')

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
</style>

<x-card title="Orders & Invoices">
    
    <x-slot name="action">
        <div class="flex items-center justify-end gap-1.5 sm:gap-2">
            
            <button @click="$dispatch('toggle-search')" class="md:hidden h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-black hover:text-blue-600 hover:border-blue-300 transition shadow-sm" title="Toggle Search">
                <i class="fas fa-search"></i>
            </button>

            <form action="{{ route('orders.index') }}" method="GET" class="hidden md:block relative w-64 search-form">
                @foreach(request()->only(['start_date', 'end_date', 'status', 'customer_id', 'min_amount', 'max_amount']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach

                <div class="relative h-9">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-black">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search"
                           class="search-input h-full w-full rounded-lg border border-gray-200 bg-gray-50 pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-black text-black" 
                           placeholder="Search invoices..." 
                           value="{{ request('search') }}"
                           autocomplete="off">
                </div>
            </form>

            <button @click="$dispatch('toggle-advanced')" class="h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-black hover:text-blue-600 hover:border-blue-300 transition shadow-sm" title="Advanced Search">
                <i class="fas fa-sliders-h"></i>
            </button>

            @can('export orders')
            <button type="button" onclick="confirmExport()" class="h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-green-600 hover:text-green-700 hover:border-green-300 transition shadow-sm" title="Export to Excel">
                <i class="fas fa-file-excel"></i>
            </button>
            @endcan

            @can('create orders')
            <div class="hidden md:block">
                <x-button href="{{ route('orders.create') }}" icon="fas fa-plus" class="!h-9 !py-0 flex items-center">
                    New Invoice
                </x-button>
            </div>
            <a href="{{ route('orders.create') }}" class="md:hidden h-9 w-9 flex items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm hover:bg-blue-700 transition" title="New Invoice">
                <i class="fas fa-plus"></i>
            </a>
            @endcan
        </div>
    </x-slot>

    <div x-data="{ searchOpen: {{ request('search') ? 'true' : 'false' }} }"
         @toggle-search.window="searchOpen = !searchOpen; if(searchOpen) $dispatch('close-advanced')"
         @close-search.window="searchOpen = false"
         x-show="searchOpen" x-collapse
         class="md:hidden bg-gray-50 border-b border-gray-100 p-4">
        <form action="{{ route('orders.index') }}" method="GET" class="relative w-full search-form">
            @foreach(request()->only(['start_date', 'end_date', 'status', 'customer_id', 'min_amount', 'max_amount']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <div class="relative h-10 w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-black">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" 
                    class="search-input h-full w-full rounded-xl border border-gray-200 bg-white pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-gray-400 text-black shadow-sm" 
                    placeholder="Search invoices..." 
                    value="{{ request('search') }}" 
                    autocomplete="off">
            </div>
        </form>
    </div>

    <div x-data="{ show: {{ request()->hasAny(['start_date', 'end_date', 'status', 'customer_id', 'min_amount', 'max_amount']) ? 'true' : 'false' }} }"
         @toggle-advanced.window="show = !show; if(show) $dispatch('close-search')"
         @close-advanced.window="show = false"
         x-show="show" x-collapse
         class="bg-gray-50 border-b border-gray-100 p-5 mb-4">
        
        <form action="{{ route('orders.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
                
                <div class="lg:col-span-1">
                    <label class="block text-xs font-bold text-black uppercase mb-2">From Date</label>
                    <div class="relative">
                        <input type="text" name="start_date" value="{{ request('start_date') }}" class="datepicker w-full rounded-xl border border-gray-200 bg-white py-2.5 px-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black placeholder-black shadow-sm" placeholder="Select Date">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-black">
                            <i class="fas fa-calendar-alt text-xs"></i>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <label class="block text-xs font-bold text-black uppercase mb-2">To Date</label>
                    <div class="relative">
                        <input type="text" name="end_date" value="{{ request('end_date') }}" class="datepicker w-full rounded-xl border border-gray-200 bg-white py-2.5 px-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black placeholder-black shadow-sm" placeholder="Select Date">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-black">
                            <i class="fas fa-calendar-alt text-xs"></i>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <label class="block text-xs font-bold text-black uppercase mb-2">Status</label>
                    <div class="relative z-20 bg-white rounded-xl shadow-sm">
                        <select name="status" class="relative z-20 w-full appearance-none rounded-xl border border-gray-200 bg-transparent py-2.5 pl-4 pr-10 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black">
                            <option value="">All Statuses</option>
                            <option value="Unpaid" {{ request('status') == 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="Partially Paid" {{ request('status') == 'Partially Paid' ? 'selected' : '' }}>Partially Paid</option>
                            <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                        <span class="absolute top-1/2 right-3 z-30 -translate-y-1/2 text-black">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <label class="block text-xs font-bold text-black uppercase mb-2">Customer</label>
                    <div class="relative z-20 bg-white rounded-xl shadow-sm">
                        <select name="customer_id" class="relative z-20 w-full appearance-none rounded-xl border border-gray-200 bg-transparent py-2.5 pl-4 pr-10 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black">
                            <option value="">All Customers</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->id }}" {{ request('customer_id') == $cust->id ? 'selected' : '' }}>
                                    {{ $cust->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute top-1/2 right-3 z-30 -translate-y-1/2 text-black">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-black uppercase mb-2">Amount Range</label>
                    <div class="flex items-center gap-2">
                        <div class="relative w-full shadow-sm rounded-xl">
                            <span class="absolute top-1/2 left-3 -translate-y-1/2 text-black text-xs">₹</span>
                            <input type="number" name="min_amount" value="{{ request('min_amount') }}" class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-7 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-black text-black" placeholder="Min">
                        </div>
                        <span class="text-black font-bold">-</span>
                        <div class="relative w-full shadow-sm rounded-xl">
                            <span class="absolute top-1/2 left-3 -translate-y-1/2 text-black text-xs">₹</span>
                            <input type="number" name="max_amount" value="{{ request('max_amount') }}" class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-7 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-black text-black" placeholder="Max">
                        </div>
                    </div>
                </div>

            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('orders.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-black shadow-sm hover:bg-gray-50 transition">
                    <i class="fas fa-undo"></i> Reset
                </a>

                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <div class="hidden md:block">
        <x-table :headers="['Invoice / Location', 'Customer', 'Dates', 'Tax', 'Total', 'Status', 'Actions']">
            @forelse ($orders as $order)
            <tr class="hover:bg-gray-50 transition duration-200">
                
                <td class="px-6 py-4 align-top">
                    <div class="font-bold text-blue-600 text-sm">
                        <a href="{{ route('orders.show', $order->id) }}" class="hover:underline">{{ $order->invoice_number }}</a>
                    </div>
                    @if($order->booking_id)
                        <a href="{{ route('bookings.show', $order->booking_id) }}" class="text-xs text-black mt-1 font-medium bg-gray-100 inline-flex items-center gap-1 px-1.5 py-0.5 rounded border border-gray-200 hover:bg-gray-200 transition">
                            <i class="fas fa-link text-[10px]"></i> BKG-{{ $order->booking_id }}
                        </a>
                    @endif
                    <div class="text-[11px] font-bold text-red-500 mt-1.5">
                        <i class="fas fa-map-marker-alt"></i> {{ $order->location->name ?? 'Global / Unassigned' }}
                    </div>
                </td>

                <td class="px-6 py-4 align-top">
                    <div class="font-bold text-black text-sm">{{ $order->customer->business_name ?? 'Individual' }}</div>
                    <div class="text-sm text-black mt-0.5 font-medium">
                        <i class="far fa-user mr-1"></i> {{ $order->customer->name }}
                    </div>
                </td>

                <td class="px-6 py-4 align-top">
                    <div class="flex flex-col text-sm space-y-1">
                        <div><span class="text-black font-bold">Issued:</span> <span class="text-black font-medium">{{ $order->invoice_date->format('d M, Y') }}</span></div>
                        @if($order->due_date)
                            <div>
                                <span class="text-black font-bold">Due:</span> 
                                <span class="font-medium {{ $order->due_date->isPast() && $order->status != 'Paid' ? 'text-red-500 font-bold' : 'text-black' }}">
                                    {{ $order->due_date->format('d M, Y') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </td>

                <td class="px-6 py-4 align-top text-black text-sm font-medium font-mono">
                    ₹{{ number_format($order->tax, 2) }}
                </td>

                <td class="px-6 py-4 align-top">
                    <div class="font-bold text-black text-sm font-mono">₹{{ number_format($order->total_amount, 2) }}</div>
                    <div class="text-xs text-black mt-1 font-medium bg-gray-100 inline-block px-1.5 py-0.5 rounded border border-gray-200">
                        {{ $order->items->count() }} Items
                    </div>
                </td>

                <td class="px-6 py-4 align-top">
                    <x-badge :status="$order->status" />
                </td>

                <td class="px-6 py-4 align-top text-right">
                    <div class="flex justify-end items-center gap-1">
                        
                        @can('view orders')
                        <x-button variant="secondary" href="{{ route('orders.show', $order->id) }}" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-black hover:!bg-gray-100" title="View Details">
                            <i class="fas fa-eye"></i>
                        </x-button>
                        @endcan
                        
                        @can('edit orders')
                        @if($order->status != 'Paid')
                            <x-button variant="secondary" href="{{ route('orders.edit', $order->id) }}" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-blue-500 hover:!bg-blue-50" title="Edit">
                                <i class="fas fa-pencil-alt"></i>
                            </x-button>
                        @endif
                        @endcan

                        @can('delete orders')
                        @if($order->payments->isNotEmpty())
                            <button type="button" class="p-1.5 rounded-lg text-red-200 hover:bg-red-50 cursor-not-allowed transition" 
                                title="Cannot Delete: Associated Payments"
                                onclick="window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'warning', title: 'Action Blocked', message: 'Cannot delete this invoice because it has recorded payments. Please delete the payments first.' } }))">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        @else
                            <form id="del-ord-{{ $order->id }}" action="{{ route('orders.destroy', $order->id) }}" method="POST" class="hidden">
                                @csrf @method('DELETE')
                            </form>
                            <x-button variant="secondary" type="button" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-red-500 hover:!bg-red-50" title="Delete"
                                onclick="confirmDelete('del-ord-{{ $order->id }}', 'Are you sure you want to delete this invoice? This will also remove all invoice items.')">
                                <i class="fas fa-trash-alt"></i>
                            </x-button>
                        @endif
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No invoices found.</td>
                </tr>
            @endforelse

            <x-slot name="pagination">
                @if($orders->hasPages())
                    {{ $orders->withQueryString()->links() }}
                @endif
            </x-slot>
        </x-table>
    </div>

    <div class="block md:hidden space-y-4 p-4">
        @forelse ($orders as $order)
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 flex flex-col gap-4">
            
            <div class="flex justify-between items-start">
                <div>
                    <div class="font-bold text-blue-600 text-sm">
                        <a href="{{ route('orders.show', $order->id) }}" class="hover:underline">{{ $order->invoice_number }}</a>
                    </div>
                    @if($order->booking_id)
                        <a href="{{ route('bookings.show', $order->booking_id) }}" class="mt-1 text-[11px] text-gray-700 font-medium bg-gray-100 inline-flex items-center gap-1 px-1.5 py-0.5 rounded border border-gray-200 hover:bg-gray-200 transition">
                            <i class="fas fa-link text-[10px]"></i> BKG-{{ $order->booking_id }}
                        </a>
                    @endif
                    <div class="text-[11px] font-bold text-red-500 mt-1.5">
                        <i class="fas fa-map-marker-alt"></i> {{ $order->location->name ?? 'Global / Unassigned' }}
                    </div>
                </div>
                <div>
                    <x-badge :status="$order->status" />
                </div>
            </div>

            <div class="flex flex-col gap-1.5 bg-gray-50 p-3 rounded-xl border border-gray-100">
                <div class="font-bold text-gray-900 text-sm">{{ $order->customer->business_name ?? 'Individual' }}</div>
                <div class="text-[12px] text-gray-700 flex items-center gap-2">
                    <i class="far fa-user w-3 text-center opacity-70"></i> {{ $order->customer->name }}
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div class="bg-blue-50 p-2.5 rounded-xl border border-blue-100 flex flex-col justify-center">
                    <span class="text-[10px] text-blue-600 font-bold uppercase mb-0.5">Total Amount</span>
                    <span class="font-bold text-gray-900 text-sm font-mono">₹{{ number_format($order->total_amount, 2) }}</span>
                    <span class="text-[10px] text-gray-600 mt-0.5">{{ $order->items->count() }} Items | Tax: ₹{{ number_format($order->tax, 2) }}</span>
                </div>
                
                <div class="bg-gray-50 border-gray-200 p-2.5 rounded-xl border flex flex-col justify-center">
                    <span class="text-[10px] text-gray-500 font-bold uppercase mb-0.5">Issued: {{ $order->invoice_date->format('d M, Y') }}</span>
                    @if($order->due_date)
                        <span class="text-[10px] font-bold uppercase mt-1 {{ $order->due_date->isPast() && $order->status != 'Paid' ? 'text-red-600' : 'text-gray-600' }}">
                            Due: {{ $order->due_date->format('d M, Y') }}
                        </span>
                    @else
                        <span class="text-[10px] text-gray-500 font-bold uppercase mt-1">No Due Date</span>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-end pt-2 border-t border-gray-100 gap-1.5">
                @can('view orders')
                    <a href="{{ route('orders.show', $order->id) }}" class="p-2 rounded-lg bg-gray-50 text-gray-600 hover:text-black border border-gray-200 shadow-sm transition" title="View Details">
                        <i class="fas fa-eye w-3.5 h-3.5 flex items-center justify-center"></i>
                    </a>
                @endcan
                
                @can('edit orders')
                    @if($order->status != 'Paid')
                        <a href="{{ route('orders.edit', $order->id) }}" class="p-2 rounded-lg bg-gray-50 text-blue-600 hover:text-blue-800 border border-gray-200 shadow-sm transition" title="Edit">
                            <i class="fas fa-pencil-alt w-3.5 h-3.5 flex items-center justify-center"></i>
                        </a>
                    @endif
                @endcan

                @can('delete orders')
                    @if($order->payments->isNotEmpty())
                        <button type="button" class="p-2 rounded-lg bg-gray-50 text-red-200 border border-gray-200 shadow-sm cursor-not-allowed" title="Cannot Delete: Associated Payments" 
                            onclick="window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'warning', title: 'Action Blocked', message: 'Cannot delete this invoice because it has recorded payments. Please delete the payments first.' } }))">
                            <i class="fas fa-trash-alt w-3.5 h-3.5 flex items-center justify-center"></i>
                        </button>
                    @else
                        <form id="del-ord-mobile-{{ $order->id }}" action="{{ route('orders.destroy', $order->id) }}" method="POST" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                        <button type="button" class="p-2 rounded-lg bg-gray-50 text-red-600 hover:text-red-800 border border-gray-200 shadow-sm transition" title="Delete"
                            onclick="confirmDelete('del-ord-mobile-{{ $order->id }}', 'Are you sure you want to delete this invoice? This will also remove all invoice items.')">
                            <i class="fas fa-trash-alt w-3.5 h-3.5 flex items-center justify-center"></i>
                        </button>
                    @endif
                @endcan
            </div>
            
        </div>
        @empty
            <div class="p-4 text-center text-gray-500 bg-white border border-gray-200 rounded-2xl">
                No invoices found.
            </div>
        @endforelse

        @if($orders->hasPages())
            <div class="pt-2">
                {{ $orders->withQueryString()->links() }}
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

    @can('export orders')
    function confirmExport() {
        // Get current URL parameters to preserve filters
        const urlParams = new URLSearchParams(window.location.search);
        const exportUrl = "{{ route('orders.export') }}?" + urlParams.toString();

        window.dispatchEvent(new CustomEvent('confirm', { 
            detail: { 
                title: 'Export Confirmation', 
                message: 'Do you want to export the current filtered list of invoices to Excel?', 
                type: 'info', 
                action: () => window.location.href = exportUrl 
            } 
        }));
    }
    @endcan
</script>
@endsection