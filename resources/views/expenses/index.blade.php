@extends('layouts.app')
@section('header', 'Expense Manager')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Custom Flatpickr Styles to match Tailwind UI */
    .flatpickr-calendar {
        border-radius: 1rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
    }
    .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange, .flatpickr-day.selected.inRange, .flatpickr-day.startRange.inRange, .flatpickr-day.endRange.inRange, .flatpickr-day.selected:focus, .flatpickr-day.startRange:focus, .flatpickr-day.endRange:focus, .flatpickr-day.selected:hover, .flatpickr-day.startRange:hover, .flatpickr-day.endRange:hover, .flatpickr-day.selected.prevMonthDay, .flatpickr-day.startRange.prevMonthDay, .flatpickr-day.endRange.prevMonthDay, .flatpickr-day.selected.nextMonthDay, .flatpickr-day.startRange.nextMonthDay, .flatpickr-day.endRange.nextMonthDay {
        background: #2563eb; 
        border-color: #2563eb;
    }
</style>

<x-card title="Expense List">
    
    <x-slot name="action">
        <div class="flex items-center justify-end gap-1.5 sm:gap-2">
            
            <button @click="$dispatch('toggle-search')" class="md:hidden h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-black hover:text-blue-600 hover:border-blue-300 transition shadow-sm" title="Toggle Search">
                <i class="fas fa-search"></i>
            </button>

            <form action="{{ route('expenses.index') }}" method="GET" class="hidden md:block relative w-64 search-form">
                @foreach(request()->only(['title', 'category', 'reference_no', 'min_amount', 'max_amount', 'start_date', 'end_date']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach

                <div class="relative h-9">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-black">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search"
                           class="search-input h-full w-full rounded-lg border border-gray-200 bg-gray-50 pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-black text-black" 
                           placeholder="Search expenses..." 
                           value="{{ request('search') }}"
                           autocomplete="off">
                </div>
            </form>

            <button @click="$dispatch('toggle-advanced')" class="h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-black hover:text-blue-600 hover:border-blue-300 transition shadow-sm" title="Advanced Search">
                <i class="fas fa-sliders-h"></i>
            </button>

            @can('export expenses')
            <button type="button" onclick="confirmExport()" class="h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-green-600 hover:text-green-700 hover:border-green-300 transition shadow-sm" title="Export to Excel">
                <i class="fas fa-file-excel"></i>
            </button>
            @endcan

            @can('create expenses')
            <div class="hidden md:block">
                <x-button href="{{ route('expenses.create') }}" icon="fas fa-plus" class="!h-9 !py-0 flex items-center">
                    Add Expense
                </x-button>
            </div>
            <a href="{{ route('expenses.create') }}" class="md:hidden h-9 w-9 flex items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm hover:bg-blue-700 transition" title="Add Expense">
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
        <form action="{{ route('expenses.index') }}" method="GET" class="relative w-full search-form">
            @foreach(request()->only(['title', 'category', 'reference_no', 'min_amount', 'max_amount', 'start_date', 'end_date']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <div class="relative h-10 w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-black">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" 
                    class="search-input h-full w-full rounded-xl border border-gray-200 bg-white pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-gray-400 text-black shadow-sm" 
                    placeholder="Search expenses..." 
                    value="{{ request('search') }}" 
                    autocomplete="off">
            </div>
        </form>
    </div>

    <div x-data="{ show: {{ request()->hasAny(['title', 'category', 'reference_no', 'min_amount', 'max_amount', 'start_date', 'end_date']) ? 'true' : 'false' }} }"
         @toggle-advanced.window="show = !show; if(show) $dispatch('close-search')"
         @close-advanced.window="show = false"
         x-show="show" x-collapse
         class="bg-gray-50 border-b border-gray-100 p-5 mb-4">
        
        <form action="{{ route('expenses.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
                
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-black uppercase mb-2">Title / Payee</label>
                    <input type="text" name="title" value="{{ request('title') }}" class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black placeholder-black shadow-sm" placeholder="e.g. Office Rent">
                </div>

                <div class="lg:col-span-1">
                    <label class="block text-xs font-bold text-black uppercase mb-2">Category</label>
                    <div class="relative z-20 bg-white shadow-sm rounded-xl">
                        <select name="category" class="relative z-20 w-full appearance-none rounded-xl border border-gray-200 bg-transparent py-2.5 pl-4 pr-10 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black">
                            <option value="">All Categories</option>
                            @foreach($categories as $key => $val)
                                @if(is_array($val))
                                    <optgroup label="{{ $key }}">
                                        @foreach($val as $subCat)
                                            <option value="{{ $subCat }}" {{ request('category') == $subCat ? 'selected' : '' }}>
                                                {{ $subCat }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @else
                                    <option value="{{ $val }}" {{ request('category') == $val ? 'selected' : '' }}>
                                        {{ $val }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <span class="absolute top-1/2 right-3 z-30 -translate-y-1/2 text-black">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <label class="block text-xs font-bold text-black uppercase mb-2">Ref No</label>
                    <input type="text" name="reference_no" value="{{ request('reference_no') }}" class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black placeholder-black shadow-sm" placeholder="Receipt #">
                </div>

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

                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-black uppercase mb-2">Amount Range</label>
                    <div class="flex items-center gap-2">
                        <div class="relative w-full shadow-sm rounded-xl">
                            <span class="absolute top-1/2 left-3 -translate-y-1/2 text-black text-xs">₹</span>
                            <input type="number" name="min_amount" value="{{ request('min_amount') }}" class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-7 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black placeholder-black" placeholder="Min">
                        </div>
                        <span class="text-black font-bold">-</span>
                        <div class="relative w-full shadow-sm rounded-xl">
                            <span class="absolute top-1/2 left-3 -translate-y-1/2 text-black text-xs">₹</span>
                            <input type="number" name="max_amount" value="{{ request('max_amount') }}" class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-7 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black placeholder-black" placeholder="Max">
                        </div>
                    </div>
                </div>

            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('expenses.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-black shadow-sm hover:bg-gray-50 transition">
                    <i class="fas fa-undo"></i> Reset
                </a>

                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <div class="hidden md:block">
        <x-table :headers="['EXP ID / Location', 'Date', 'Title / Payee', 'Category', 'Amount', 'Receipt', 'Actions']">
            @forelse ($expenses as $expense)
            <tr class="hover:bg-gray-50 transition duration-200">
                
                <td class="px-6 py-4 align-top">
                    <div class="font-bold text-blue-600 text-sm">EXP-{{ $expense->id }}</div>
                    <div class="text-[11px] font-bold text-red-500 mt-1">
                        <i class="fas fa-map-marker-alt"></i> {{ $expense->location->name ?? 'Global / Unassigned' }}
                    </div>
                </td>

                <td class="px-6 py-4 align-top">
                    <div class="text-sm text-black font-medium">{{ $expense->expense_date->format('d-M-Y') }}</div>
                </td>

                <td class="px-6 py-4 align-top">
                    <div class="font-bold text-black text-sm">{{ $expense->title }}</div>
                    @if($expense->reference_no)
                        <div class="text-xs text-black mt-0.5 font-mono">Ref: {{ $expense->reference_no }}</div>
                    @endif
                </td>

                <td class="px-6 py-4 align-top">
                    <div class="text-xs text-black mt-1 font-medium bg-gray-100 inline-block px-1.5 py-0.5 rounded border border-gray-200">
                        {{ $expense->category }}
                    </div>
                </td>

                <td class="px-6 py-4 align-top text-right">
                    <div class="font-bold text-black text-sm font-mono">₹{{ number_format($expense->amount, 2) }}</div>
                </td>

                <td class="px-6 py-4 align-top text-center">
                    @if($expense->receipt_path)
                        <a href="{{ asset('storage/'.$expense->receipt_path) }}" target="_blank" class="inline-flex items-center justify-center h-7 w-7 rounded bg-blue-50 text-blue-500 hover:bg-blue-100 transition" title="View Receipt">
                            <i class="fas fa-file-image text-xs"></i>
                        </a>
                    @else
                        <span class="text-black text-xs">-</span>
                    @endif
                </td>

                <td class="px-6 py-4 align-top text-right">
                    <div class="flex justify-end items-center gap-1">
                        @can('edit expenses')
                        <x-button variant="secondary" href="{{ route('expenses.edit', $expense->id) }}" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-blue-500 hover:!bg-blue-50" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </x-button>
                        @endcan

                        @can('delete expenses')
                        <form id="del-exp-{{ $expense->id }}" action="{{ route('expenses.destroy', $expense->id) }}" method="POST" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                        
                        <x-button variant="secondary" type="button" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-red-500 hover:!bg-red-50" title="Delete"
                            onclick="confirmDelete('del-exp-{{ $expense->id }}', 'Are you sure you want to delete this expense record?')">
                            <i class="fas fa-trash-alt"></i>
                        </x-button>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No expenses found.</td>
                </tr>
            @endforelse

            <x-slot name="pagination">
                @if($expenses->hasPages())
                    {{ $expenses->withQueryString()->links() }}
                @endif
            </x-slot>
        </x-table>
    </div>

    <div class="block md:hidden space-y-4 p-4">
        @forelse ($expenses as $expense)
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 flex flex-col gap-4">
            
            <div class="flex justify-between items-start border-b border-gray-100 pb-3">
                <div>
                    <div class="font-bold text-gray-900 text-lg font-mono leading-tight">₹{{ number_format($expense->amount, 2) }}</div>
                    <div class="text-[11px] text-gray-500 mt-0.5">
                        {{ $expense->expense_date->format('d M, Y') }}
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-blue-600 text-[11px] uppercase tracking-wide">EXP-{{ $expense->id }}</div>
                    <div class="text-[10px] font-bold text-red-500 mt-0.5">
                        <i class="fas fa-map-marker-alt"></i> {{ $expense->location->name ?? 'Unassigned' }}
                    </div>
                    <div class="text-[10px] text-gray-600 mt-1 bg-gray-100 inline-block px-1.5 py-0.5 rounded border border-gray-200">
                        {{ $expense->category }}
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-1.5 bg-gray-50 p-3 rounded-xl border border-gray-100">
                <div class="font-bold text-gray-900 text-sm">{{ $expense->title }}</div>
                @if($expense->reference_no)
                    <div class="text-[12px] text-gray-600 font-mono mt-0.5">Ref: {{ $expense->reference_no }}</div>
                @endif
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-gray-100 gap-1.5">
                <div>
                    @if($expense->receipt_path)
                        <a href="{{ asset('storage/'.$expense->receipt_path) }}" target="_blank" class="text-[11px] text-blue-600 font-medium bg-blue-50 hover:bg-blue-100 inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-blue-100 transition shadow-sm">
                            <i class="fas fa-file-image"></i> View Receipt
                        </a>
                    @endif
                </div>

                <div class="flex items-center gap-1.5">
                    @can('edit expenses')
                        <a href="{{ route('expenses.edit', $expense->id) }}" class="p-2 rounded-lg bg-gray-50 text-blue-600 hover:text-blue-800 border border-gray-200 shadow-sm transition" title="Edit">
                            <i class="fas fa-pencil-alt w-3.5 h-3.5 flex items-center justify-center"></i>
                        </a>
                    @endcan

                    @can('delete expenses')
                        <form id="del-exp-mobile-{{ $expense->id }}" action="{{ route('expenses.destroy', $expense->id) }}" method="POST" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                        <button type="button" class="p-2 rounded-lg bg-gray-50 text-red-600 hover:text-red-800 border border-gray-200 shadow-sm transition" title="Delete"
                            onclick="confirmDelete('del-exp-mobile-{{ $expense->id }}', 'Are you sure you want to delete this expense record?')">
                            <i class="fas fa-trash-alt w-3.5 h-3.5 flex items-center justify-center"></i>
                        </button>
                    @endcan
                </div>
            </div>
            
        </div>
        @empty
            <div class="p-4 text-center text-gray-500 bg-white border border-gray-200 rounded-2xl">
                No expenses found.
            </div>
        @endforelse

        @if($expenses->hasPages())
            <div class="pt-2">
                {{ $expenses->withQueryString()->links() }}
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

    @can('export expenses')
    // Excel Export Confirmation
    function confirmExport() {
        // Get current URL parameters to preserve filters
        const urlParams = new URLSearchParams(window.location.search);
        const exportUrl = "{{ route('expenses.export') }}?" + urlParams.toString();

        window.dispatchEvent(new CustomEvent('confirm', { 
            detail: { 
                title: 'Export Confirmation', 
                message: 'Do you want to export the current filtered list of expenses to Excel?', 
                type: 'info', 
                action: () => window.location.href = exportUrl 
            } 
        }));
    }
    @endcan
</script>
@endsection