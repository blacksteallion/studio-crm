@extends('layouts.app')
@section('header', 'Bookings')

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

<x-card title="Booking List">
    
    <x-slot name="action">
        <div class="flex items-center justify-end gap-1.5 sm:gap-2">
            
            <button @click="$dispatch('toggle-search')" class="md:hidden h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-black hover:text-blue-600 hover:border-blue-300 transition shadow-sm" title="Toggle Search">
                <i class="fas fa-search"></i>
            </button>

            <form action="{{ route('bookings.index') }}" method="GET" class="hidden md:block relative w-64 search-form">
                @foreach(request()->only(['start_date', 'end_date', 'status', 'staff_id', 'customer_id']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach

                <div class="relative h-9">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-black">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search"
                           class="search-input h-full w-full rounded-lg border border-gray-200 bg-gray-50 pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-black text-black" 
                           placeholder="Search bookings..." 
                           value="{{ request('search') }}"
                           autocomplete="off">
                </div>
            </form>

            <button @click="$dispatch('toggle-advanced')" class="h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-black hover:text-blue-600 hover:border-blue-300 transition shadow-sm" title="Advanced Search">
                <i class="fas fa-sliders-h"></i>
            </button>

            @can('export bookings')
            <button type="button" onclick="confirmExport()" class="h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-green-600 hover:text-green-700 hover:border-green-300 transition shadow-sm" title="Export to Excel">
                <i class="fas fa-file-excel"></i>
            </button>
            @endcan

            @can('view booking calendar')
            <div class="hidden md:block">
                <a href="{{ route('bookings.calendar') }}" class="h-9 inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-3 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition">
                    <i class="fas fa-calendar-alt text-gray-500"></i> Calendar View
                </a>
            </div>
            <a href="{{ route('bookings.calendar') }}" class="md:hidden h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 shadow-sm hover:bg-gray-50 transition" title="Calendar View">
                <i class="fas fa-calendar-alt text-gray-500"></i>
            </a>
            @endcan

            @can('create bookings')
            <div class="hidden md:block">
                <x-button href="{{ route('bookings.create') }}" icon="fas fa-plus" class="!h-9 !py-0 flex items-center">
                    New Booking
                </x-button>
            </div>
            <a href="{{ route('bookings.create') }}" class="md:hidden h-9 w-9 flex items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm hover:bg-blue-700 transition" title="New Booking">
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
        <form action="{{ route('bookings.index') }}" method="GET" class="relative w-full search-form">
            @foreach(request()->only(['start_date', 'end_date', 'status', 'staff_id', 'customer_id']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <div class="relative h-10 w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-black">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" 
                    class="search-input h-full w-full rounded-xl border border-gray-200 bg-white pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-gray-400 text-black shadow-sm" 
                    placeholder="Search bookings..." 
                    value="{{ request('search') }}" 
                    autocomplete="off">
            </div>
        </form>
    </div>

    <div x-data="{ show: {{ request()->hasAny(['start_date', 'end_date', 'status', 'staff_id', 'customer_id']) ? 'true' : 'false' }} }"
         @toggle-advanced.window="show = !show; if(show) $dispatch('close-search')"
         @close-advanced.window="show = false"
         x-show="show" x-collapse
         class="bg-gray-50 border-b border-gray-100 p-5 mb-4">
        
        <form action="{{ route('bookings.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
                
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
                    <label class="block text-xs font-bold text-black uppercase mb-2">Status</label>
                    <div class="relative z-20 bg-white rounded-xl shadow-sm">
                        <select name="status" class="relative z-20 w-full appearance-none rounded-xl border border-gray-200 bg-transparent py-2.5 pl-4 pr-10 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black">
                            <option value="">All Statuses</option>
                            <option value="Scheduled" {{ request('status') == 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                            <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="No Show" {{ request('status') == 'No Show' ? 'selected' : '' }}>No Show</option>
                        </select>
                        <span class="absolute top-1/2 right-3 z-30 -translate-y-1/2 text-black">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">Assigned Staff</label>
                    <div class="relative z-20 bg-white rounded-xl shadow-sm">
                        <select name="staff_id" class="relative z-20 w-full appearance-none rounded-xl border border-gray-200 bg-transparent py-2.5 pl-4 pr-10 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black">
                            <option value="">All Staff</option>
                            @foreach($staffMembers as $staff)
                                <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute top-1/2 right-3 z-30 -translate-y-1/2 text-black">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>

                <div class="lg:col-span-2">
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

            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('bookings.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-black shadow-sm hover:bg-gray-50 transition">
                    <i class="fas fa-undo"></i> Reset
                </a>

                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 transition">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <div class="hidden md:block">
        <x-table :headers="['Bkg. ID / Source', 'Customer', 'Date & Time', 'Staff', 'Status', 'Actions']">
            @forelse ($bookings as $booking)
            <tr class="hover:bg-gray-50 transition duration-200">
                
                <td class="px-6 py-4 align-top">
                    <div class="font-bold text-blue-600 text-sm">BKG-{{ $booking->id }}</div>
                    <div class="text-xs text-black mt-1 font-medium bg-gray-100 inline-block px-1.5 py-0.5 rounded border border-gray-200">
                        @php 
                            $sourceName = $booking->inquiry->leadSource->name ?? 'Direct';
                            if($sourceName == 'Reference / Referral') $sourceName = 'Reference';
                        @endphp
                        {{ $sourceName }}
                    </div>
                </td>

                <td class="px-6 py-4 align-top">
                    <div class="font-bold text-black text-sm">
                        {{ $booking->customer->business_name ?? 'Individual' }}
                    </div>
                    <div class="text-sm text-black mt-0.5 font-medium">
                        <i class="far fa-user mr-1"></i> {{ $booking->customer->name }}
                    </div>
                </td>
                
                <td class="px-6 py-4 align-top">
                    <div class="font-bold text-black text-sm">{{ $booking->booking_date->format('d M, Y') }}</div>
                    <div class="text-sm text-black mt-0.5">
                        <span class="font-bold">Time:</span> {{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('h:i A') }}
                    </div>
                </td>

                <td class="px-6 py-4 align-top">
                    @if($booking->assignedStaff)
                        <div class="flex items-center gap-1.5">
                            <div class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-black border border-gray-200">
                                {{ substr($booking->assignedStaff->name, 0, 1) }}
                            </div>
                            <span class="text-sm font-medium text-black">{{ explode(' ', $booking->assignedStaff->name)[0] }}</span>
                        </div>
                    @else
                        <span class="text-black text-sm italic">Unassigned</span>
                    @endif
                </td>

                <td class="px-6 py-4 align-top">
                    <x-badge :status="$booking->status" />
                </td>

                <td class="px-6 py-4 align-top text-right">
                    <div class="flex justify-end items-center gap-1">
                        @can('view bookings')
                        <x-button variant="secondary" href="{{ route('bookings.show', $booking->id) }}" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-black hover:!bg-gray-100" title="View Details">
                            <i class="fas fa-eye"></i>
                        </x-button>
                        @endcan

                        @can('create orders')
                        <x-button variant="secondary" href="{{ route('orders.create', ['booking_id' => $booking->id]) }}" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-green-600 hover:!bg-green-50" title="Create Invoice">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </x-button>
                        @endcan
                        
                        @can('edit bookings')
                        <x-button variant="secondary" href="{{ route('bookings.edit', $booking->id) }}" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-blue-500 hover:!bg-blue-50" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </x-button>
                        @endcan

                        @can('delete bookings')
                            @if($booking->orders->isNotEmpty())
                                <button type="button" class="p-1.5 rounded-lg text-red-200 hover:bg-red-50 cursor-not-allowed transition" 
                                    title="Cannot delete: Invoice Generated"
                                    onclick="window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'warning', title: 'Action Blocked', message: 'Cannot delete this booking because an Invoice has been generated. Please delete the invoice first.' } }))">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            @else
                                <form id="del-bkg-{{ $booking->id }}" action="{{ route('bookings.destroy', $booking->id) }}" method="POST" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                                <x-button variant="secondary" type="button" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-red-500 hover:!bg-red-50" title="Delete"
                                    onclick="confirmDelete('del-bkg-{{ $booking->id }}', 'Are you sure you want to delete this booking?')">
                                    <i class="fas fa-trash-alt"></i>
                                </x-button>
                            @endif
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No bookings found.</td>
                </tr>
            @endforelse

            <x-slot name="pagination">
                @if($bookings->hasPages())
                    {{ $bookings->withQueryString()->links() }}
                @endif
            </x-slot>
        </x-table>
    </div>

    <div class="block md:hidden space-y-4 p-4">
        @forelse ($bookings as $booking)
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 flex flex-col gap-4">
            
            <div class="flex justify-between items-start">
                <div>
                    <div class="font-bold text-blue-600 text-sm">BKG-{{ $booking->id }}</div>
                    <div class="text-[11px] text-gray-500 mt-0.5">
                        @php 
                            $sourceName = $booking->inquiry->leadSource->name ?? 'Direct';
                            if($sourceName == 'Reference / Referral') $sourceName = 'Reference';
                        @endphp
                        {{ $sourceName }}
                    </div>
                </div>
                <div>
                    <x-badge :status="$booking->status" />
                </div>
            </div>

            <div class="flex flex-col gap-1.5 bg-gray-50 p-3 rounded-xl border border-gray-100">
                <div class="font-bold text-gray-900 text-sm">{{ $booking->customer->business_name ?? 'Individual' }}</div>
                <div class="text-[12px] text-gray-700 flex items-center gap-2">
                    <i class="far fa-user w-3 text-center opacity-70"></i> {{ $booking->customer->name }}
                </div>
            </div>

            <div class="bg-blue-50 p-3 rounded-xl border border-blue-100 flex flex-col justify-center">
                <span class="text-[10px] text-blue-600 font-bold uppercase mb-0.5">Booking Date & Time</span>
                <span class="font-bold text-gray-900 text-sm">{{ $booking->booking_date->format('d M, Y') }}</span>
                <span class="text-xs text-gray-600 mt-0.5">{{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('h:i A') }}</span>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                <div class="flex items-center gap-1.5">
                    @if($booking->assignedStaff)
                        <div class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-bold text-black border border-gray-200 shadow-sm">
                            {{ substr($booking->assignedStaff->name, 0, 1) }}
                        </div>
                        <span class="text-xs font-bold text-gray-700">{{ explode(' ', $booking->assignedStaff->name)[0] }}</span>
                    @else
                        <span class="text-xs text-gray-500 italic">Unassigned</span>
                    @endif
                </div>

                <div class="flex items-center gap-1.5">
                    @can('view bookings')
                        <a href="{{ route('bookings.show', $booking->id) }}" class="p-2 rounded-lg bg-gray-50 text-gray-600 hover:text-black border border-gray-200 shadow-sm transition" title="View Details">
                            <i class="fas fa-eye w-3.5 h-3.5 flex items-center justify-center"></i>
                        </a>
                    @endcan

                    @can('create orders')
                        <a href="{{ route('orders.create', ['booking_id' => $booking->id]) }}" class="p-2 rounded-lg bg-gray-50 text-green-600 hover:text-green-800 border border-gray-200 shadow-sm transition" title="Create Invoice">
                            <i class="fas fa-file-invoice-dollar w-3.5 h-3.5 flex items-center justify-center"></i>
                        </a>
                    @endcan
                    
                    @can('edit bookings')
                        <a href="{{ route('bookings.edit', $booking->id) }}" class="p-2 rounded-lg bg-gray-50 text-blue-600 hover:text-blue-800 border border-gray-200 shadow-sm transition" title="Edit">
                            <i class="fas fa-pencil-alt w-3.5 h-3.5 flex items-center justify-center"></i>
                        </a>
                    @endcan

                    @can('delete bookings')
                        @if($booking->orders->isNotEmpty())
                            <button type="button" class="p-2 rounded-lg bg-gray-50 text-red-200 border border-gray-200 shadow-sm cursor-not-allowed" title="Cannot delete: Invoice Generated" 
                                onclick="window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'warning', title: 'Action Blocked', message: 'Cannot delete this booking because an Invoice has been generated. Please delete the invoice first.' } }))">
                                <i class="fas fa-trash-alt w-3.5 h-3.5 flex items-center justify-center"></i>
                            </button>
                        @else
                            <form id="del-bkg-mobile-{{ $booking->id }}" action="{{ route('bookings.destroy', $booking->id) }}" method="POST" class="hidden">
                                @csrf @method('DELETE')
                            </form>
                            <button type="button" class="p-2 rounded-lg bg-gray-50 text-red-600 hover:text-red-800 border border-gray-200 shadow-sm transition" title="Delete"
                                onclick="confirmDelete('del-bkg-mobile-{{ $booking->id }}', 'Are you sure you want to delete this booking?')">
                                <i class="fas fa-trash-alt w-3.5 h-3.5 flex items-center justify-center"></i>
                            </button>
                        @endif
                    @endcan
                </div>
            </div>
            
        </div>
        @empty
            <div class="p-4 text-center text-gray-500 bg-white border border-gray-200 rounded-2xl">
                No bookings found.
            </div>
        @endforelse

        @if($bookings->hasPages())
            <div class="pt-2">
                {{ $bookings->withQueryString()->links() }}
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

    @can('export bookings')
    // Excel Export Confirmation
    function confirmExport() {
        const urlParams = new URLSearchParams(window.location.search);
        const exportUrl = "{{ route('bookings.export') }}?" + urlParams.toString();

        window.dispatchEvent(new CustomEvent('confirm', { 
            detail: { 
                title: 'Export Confirmation', 
                message: 'Do you want to export the current filtered list of bookings to Excel?', 
                type: 'info', 
                action: () => window.location.href = exportUrl 
            } 
        }));
    }
    @endcan
</script>
@endsection