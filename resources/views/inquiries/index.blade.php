@extends('layouts.app')
@section('header', 'Inquiries')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar { border-radius: 1rem; border: 1px solid #e5e7eb; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    .flatpickr-day.selected { background: #2563eb; border-color: #2563eb; }

    /* --- REFINED LIGHT THEME TOOLTIP --- */
    .activity-wrapper { 
        position: relative; 
        display: inline-flex; 
        align-items: center;
    }

    .activity-ribbon {
        visibility: hidden;
        position: absolute;
        right: 120%; 
        top: 50%;
        transform: translateY(-50%);
        background-color: #f8fafc; 
        color: #1e293b; 
        padding: 12px;
        border-radius: 10px;
        width: 320px; 
        white-space: normal;
        z-index: 99999;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        font-size: 12px;
        border: 1px solid #e2e8f0; 
        line-height: 1.5;
        text-align: left;
    }

    .activity-ribbon::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 100%;
        margin-top: -6px;
        border-width: 6px;
        border-style: solid;
        border-color: transparent transparent transparent #f8fafc;
    }

    @media (min-width: 768px) {
        .activity-wrapper:hover .activity-ribbon { 
            visibility: visible; 
        }
    }

    .ribbon-title { font-weight: 800; text-transform: uppercase; margin-bottom: 4px; display: block; color: #2563eb; }
    .ribbon-meta { margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 10px; }

    .force-visible { overflow: visible !important; }
</style>

<x-card title="Inquiry List" class="force-visible">
    
    <x-slot name="action">
        <div class="flex items-center justify-end gap-1.5 sm:gap-2">
            
            <button @click="$dispatch('toggle-search')" class="md:hidden h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-black hover:text-blue-600 hover:border-blue-300 transition shadow-sm" title="Toggle Search">
                <i class="fas fa-search"></i>
            </button>

            <form action="{{ route('inquiries.index') }}" method="GET" class="hidden md:block relative w-64 search-form">
                @foreach(request()->only(['start_date', 'end_date', 'status', 'lead_source_id', 'staff_id', 'customer_id']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach

                <div class="relative h-9">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-black">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search"
                           class="search-input h-full w-full rounded-lg border border-gray-200 bg-gray-50 pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-black text-black" 
                           placeholder="Search inquiries..." 
                           value="{{ request('search') }}"
                           autocomplete="off">
                </div>
            </form>

            <button @click="$dispatch('toggle-advanced')" class="h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-black hover:text-blue-600 hover:border-blue-300 transition shadow-sm" title="Advanced Search">
                <i class="fas fa-sliders-h"></i>
            </button>

            @can('export inquiries')
            <button type="button" onclick="confirmExport()" class="h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-green-600 hover:text-green-700 hover:border-green-300 transition shadow-sm" title="Export to Excel">
                <i class="fas fa-file-excel"></i>
            </button>
            @endcan

            @can('create inquiries')
            <div class="hidden md:block">
                <x-button href="{{ route('inquiries.create') }}" icon="fas fa-plus" class="!h-9 !py-0 flex items-center">
                    Add Inquiry
                </x-button>
            </div>
            <a href="{{ route('inquiries.create') }}" class="md:hidden h-9 w-9 flex items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm hover:bg-blue-700 transition" title="Add Inquiry">
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
        <form action="{{ route('inquiries.index') }}" method="GET" class="relative w-full search-form">
            @foreach(request()->only(['start_date', 'end_date', 'status', 'lead_source_id', 'staff_id', 'customer_id']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <div class="relative h-10 w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-black">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" 
                    class="search-input h-full w-full rounded-xl border border-gray-200 bg-white pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-gray-400 text-black shadow-sm" 
                    placeholder="Search inquiries..." 
                    value="{{ request('search') }}" 
                    autocomplete="off">
            </div>
        </form>
    </div>

    <div x-data="{ show: {{ request()->hasAny(['start_date', 'end_date', 'status', 'lead_source_id', 'staff_id', 'customer_id']) ? 'true' : 'false' }} }"
         @toggle-advanced.window="show = !show; if(show) $dispatch('close-search')"
         @close-advanced.window="show = false"
         x-show="show" x-collapse
         class="bg-gray-50 border-b border-gray-100 p-5 mb-4">
        
        <form action="{{ route('inquiries.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
                
                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">Inquiry Source</label>
                    <div class="relative z-20 bg-white shadow-sm rounded-xl">
                        <select name="lead_source_id" class="relative z-20 w-full appearance-none rounded-xl border border-gray-200 bg-transparent py-2.5 pl-4 pr-10 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black">
                            <option value="">All Sources</option>
                            @foreach($leadSources as $source)
                                <option value="{{ $source->id }}" {{ request('lead_source_id') == $source->id ? 'selected' : '' }}>
                                    {{ $source->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute top-1/2 right-3 z-30 -translate-y-1/2 text-black">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">Status</label>
                    <div class="relative z-20 bg-white shadow-sm rounded-xl">
                        <select name="status" class="relative z-20 w-full appearance-none rounded-xl border border-gray-200 bg-transparent py-2.5 pl-4 pr-10 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black">
                            <option value="">All Statuses</option>
                            <option value="New" {{ request('status') == 'New' ? 'selected' : '' }}>New</option>
                            <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="Slot Reserved" {{ request('status') == 'Slot Reserved' ? 'selected' : '' }}>Slot Reserved</option>
                            <option value="Qualified" {{ request('status') == 'Qualified' ? 'selected' : '' }}>Qualified</option>
                            <option value="Lost" {{ request('status') == 'Lost' ? 'selected' : '' }}>Lost</option>
                        </select>
                        <span class="absolute top-1/2 right-3 z-30 -translate-y-1/2 text-black">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">Assigned Staff</label>
                    <div class="relative z-20 bg-white shadow-sm rounded-xl">
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

                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">From Date</label>
                    <div class="relative shadow-sm rounded-xl">
                        <input type="text" name="start_date" value="{{ request('start_date') }}" class="datepicker w-full rounded-xl border border-gray-200 bg-white py-2.5 px-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black placeholder-black" placeholder="Select Date">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-black">
                            <i class="fas fa-calendar-alt text-xs"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">To Date</label>
                    <div class="relative shadow-sm rounded-xl">
                        <input type="text" name="end_date" value="{{ request('end_date') }}" class="datepicker w-full rounded-xl border border-gray-200 bg-white py-2.5 px-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black placeholder-black" placeholder="Select Date">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-black">
                            <i class="fas fa-calendar-alt text-xs"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">Customer</label>
                    <div class="relative z-20 bg-white shadow-sm rounded-xl">
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
                <a href="{{ route('inquiries.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-black shadow-sm hover:bg-gray-50 transition">
                    <i class="fas fa-undo"></i> Reset
                </a>

                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 transition">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <div class="hidden md:block force-visible">
        <x-table :headers="['Inq. ID / Location', 'Customer', 'Status', 'Follow Up', 'Date & Time', 'Staff', 'Actions']" class="force-visible">
            @forelse ($inquiries as $inquiry)
            <tr class="hover:bg-gray-50 transition duration-200 force-visible">
                
                <td class="px-6 py-4 align-top">
                    <div class="font-bold text-blue-600 text-sm">INQ-{{ $inquiry->id }}</div>
                    
                    <div class="text-[11px] font-bold text-red-500 mt-1 mb-1">
                        <i class="fas fa-map-marker-alt"></i> {{ $inquiry->location->name ?? 'Global / Unassigned' }}
                    </div>

                    <div class="text-[10px] text-gray-600 mt-1 font-bold bg-gray-100 inline-block px-1.5 py-0.5 rounded border border-gray-200">
                        @php 
                            $sourceName = $inquiry->leadSource->name ?? 'Direct';
                            if($sourceName == 'Reference / Referral') $sourceName = 'Reference';
                        @endphp
                        {{ $sourceName }}
                    </div>
                </td>

                <td class="px-6 py-4 align-top">
                    <div class="font-bold text-black text-sm">
                        {{ $inquiry->business_name ?? 'Individual' }}
                    </div>
                    <div class="text-sm text-black mt-0.5 font-medium">
                        <i class="far fa-user mr-1"></i> {{ $inquiry->customer->name }}
                    </div>
                    <div class="text-sm text-black mt-0.5">
                        <i class="fas fa-phone-alt mr-1"></i> {{ $inquiry->customer->mobile }}
                    </div>
                </td>
                
                <td class="px-6 py-4 align-top">
                    <x-badge :status="$inquiry->status" />
                </td>

                <td class="px-6 py-4 align-top text-sm">
                    @if($inquiry->follow_up_date)
                        <div class="{{ $inquiry->follow_up_date->isPast() && !$inquiry->follow_up_date->isToday() ? 'text-red-500 font-bold' : 'text-black font-medium' }}">
                            {{ $inquiry->follow_up_date->format('d-M-Y') }}
                        </div>
                    @else <span class="text-black">-</span> @endif
                </td>

                <td class="px-6 py-4 align-top">
                    <div class="flex flex-col text-sm space-y-1">
                        <div><span class="text-black font-bold">Pref:</span> <span class="text-black font-medium">{{ $inquiry->primary_date->format('d-M') }}</span></div>
                        @if($inquiry->alternate_date) <div><span class="text-black font-bold">Alt:</span> <span class="text-black font-medium">{{ $inquiry->alternate_date->format('d-M') }}</span></div> @endif
                        <div class="text-black"><span class="font-bold">Time:</span> {{ \Carbon\Carbon::parse($inquiry->from_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($inquiry->to_time)->format('h:i A') }}</div>
                    </div>
                </td>

                <td class="px-6 py-4 align-top">
                    @if($inquiry->assignedStaff)
                        <div class="flex items-center gap-1.5">
                            <div class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-black border border-gray-200">
                                {{ substr($inquiry->assignedStaff->name, 0, 1) }}
                            </div>
                            <span class="text-sm font-medium text-black">{{ explode(' ', $inquiry->assignedStaff->name)[0] }}</span>
                        </div>
                    @else
                        <span class="text-black text-sm italic">Unassigned</span>
                    @endif
                </td>

                <td class="px-6 py-4 align-top text-right force-visible">
                    <div class="flex flex-col items-end gap-2 force-visible">
                        
                        <div class="flex items-center justify-end gap-1 force-visible">
                            @can('manage inquiry logs')
                            <div class="activity-wrapper">
                                <x-button variant="secondary" href="{{ route('inquiries.activity', $inquiry->id) }}" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-purple-500 hover:!bg-purple-50">
                                    <i class="fas fa-history"></i>
                                </x-button>
                                <div class="activity-ribbon hidden md:block">
                                    @if($inquiry->logs->isNotEmpty())
                                        @php $lastLog = $inquiry->logs->first(); @endphp
                                        <span class="ribbon-title">{{ $lastLog->type }}</span>
                                        <p class="text-slate-700 font-medium">{{ $lastLog->message }}</p>
                                        <div class="ribbon-meta">
                                            <i class="far fa-calendar-alt mr-1"></i> {{ $lastLog->log_date->format('d M, Y') }} 
                                            <i class="far fa-clock ml-2 mr-1"></i> {{ \Carbon\Carbon::parse($lastLog->log_time)->format('h:i A') }}
                                        </div>
                                    @else
                                        <p class="text-slate-500 italic">No activity recorded yet.</p>
                                    @endif
                                </div>
                            </div>
                            @endcan

                            @can('convert inquiries')
                                @if($inquiry->status != 'Slot Reserved' && $inquiry->status != 'Lost')
                                    <x-button variant="secondary" href="{{ route('bookings.create', ['inquiry_id' => $inquiry->id]) }}" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-green-600 hover:!bg-green-50" title="Convert">
                                        <i class="fas fa-calendar-check"></i>
                                    </x-button>
                                @endif
                            @endcan
                        </div>

                        <div class="flex items-center justify-end gap-1">
                            @can('view inquiries')
                            <x-button variant="secondary" href="{{ route('inquiries.show', $inquiry->id) }}" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-black hover:!bg-gray-100" title="View">
                                <i class="fas fa-eye"></i>
                            </x-button>
                            @endcan
                            
                            @can('edit inquiries')
                            <x-button variant="secondary" href="{{ route('inquiries.edit', $inquiry->id) }}" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-blue-500 hover:!bg-blue-50" title="Edit">
                                <i class="fas fa-pencil-alt"></i>
                            </x-button>
                            @endcan

                            @can('delete inquiries')
                                @php $hasBookings = $inquiry->bookings->whereIn('status', ['Scheduled', 'Completed', 'Cancelled'])->isNotEmpty(); @endphp
                                @if($hasBookings)
                                    <button type="button" class="p-1.5 text-red-200 cursor-not-allowed" title="Cannot delete: Associated bookings" 
                                        onclick="window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'warning', title: 'Action Blocked', message: 'Cannot delete this inquiry because it has associated bookings.' } }))">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                @else
                                    <form id="del-inq-{{ $inquiry->id }}" action="{{ route('inquiries.destroy', $inquiry->id) }}" method="POST" class="hidden">
                                        @csrf @method('DELETE')
                                    </form>
                                    <x-button variant="secondary" type="button" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-red-500 hover:!bg-red-50" title="Delete"
                                        onclick="confirmDelete('del-inq-{{ $inquiry->id }}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </x-button>
                                @endif
                            @endcan
                        </div>

                    </div>
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No inquiries found.</td>
                </tr>
            @endforelse

            <x-slot name="pagination">
                @if($inquiries->hasPages())
                    {{ $inquiries->withQueryString()->links() }}
                @endif
            </x-slot>
        </x-table>
    </div>

    <div class="block md:hidden space-y-4 p-4">
        @forelse ($inquiries as $inquiry)
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 flex flex-col gap-4">
            
            <div class="flex justify-between items-start">
                <div>
                    <div class="font-bold text-blue-600 text-sm">INQ-{{ $inquiry->id }}</div>
                    
                    <div class="text-[11px] font-bold text-red-500 mt-0.5">
                        <i class="fas fa-map-marker-alt"></i> {{ $inquiry->location->name ?? 'Global / Unassigned' }}
                    </div>

                    <div class="text-[11px] text-gray-500 mt-0.5">
                        @php 
                            $sourceName = $inquiry->leadSource->name ?? 'Direct';
                            if($sourceName == 'Reference / Referral') $sourceName = 'Reference';
                        @endphp
                        {{ $sourceName }}
                    </div>
                </div>
                <div>
                    <x-badge :status="$inquiry->status" />
                </div>
            </div>

            <div class="flex flex-col gap-1.5 bg-gray-50 p-3 rounded-xl border border-gray-100">
                <div class="font-bold text-gray-900 text-sm">{{ $inquiry->business_name ?? 'Individual' }}</div>
                <div class="text-[12px] text-gray-700 flex items-center gap-2">
                    <i class="far fa-user w-3 text-center opacity-70"></i> {{ $inquiry->customer->name }}
                </div>
                <div class="text-[12px] text-gray-700 flex items-center gap-2">
                    <i class="fas fa-phone-alt w-3 text-center opacity-70"></i> {{ $inquiry->customer->mobile }}
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div class="bg-blue-50 p-2.5 rounded-xl border border-blue-100 flex flex-col justify-center">
                    <span class="text-[10px] text-blue-600 font-bold uppercase mb-0.5">Pref. Date & Time</span>
                    <span class="font-bold text-gray-900 text-xs">{{ $inquiry->primary_date->format('d-M-Y') }}</span>
                    <span class="text-xs text-gray-600 mt-0.5">{{ \Carbon\Carbon::parse($inquiry->from_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($inquiry->to_time)->format('h:i A') }}</span>
                </div>
                
                <div class="{{ $inquiry->follow_up_date && $inquiry->follow_up_date->isPast() && !$inquiry->follow_up_date->isToday() ? 'bg-red-50 border-red-100' : 'bg-gray-50 border-gray-200' }} p-2.5 rounded-xl border flex flex-col justify-center">
                    <span class="text-[10px] {{ $inquiry->follow_up_date && $inquiry->follow_up_date->isPast() && !$inquiry->follow_up_date->isToday() ? 'text-red-600' : 'text-gray-500' }} font-bold uppercase mb-0.5">Follow Up</span>
                    <span class="font-bold {{ $inquiry->follow_up_date && $inquiry->follow_up_date->isPast() && !$inquiry->follow_up_date->isToday() ? 'text-red-600' : 'text-gray-900' }} text-xs">
                        {{ $inquiry->follow_up_date ? $inquiry->follow_up_date->format('d-M-Y') : 'Not Set' }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                <div class="flex items-center gap-1.5">
                    @if($inquiry->assignedStaff)
                        <div class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-bold text-black border border-gray-200 shadow-sm">
                            {{ substr($inquiry->assignedStaff->name, 0, 1) }}
                        </div>
                        <span class="text-xs font-bold text-gray-700">{{ explode(' ', $inquiry->assignedStaff->name)[0] }}</span>
                    @else
                        <span class="text-xs text-gray-500 italic">Unassigned</span>
                    @endif
                </div>

                <div class="flex items-center gap-1.5">
                    @can('manage inquiry logs')
                        <a href="{{ route('inquiries.activity', $inquiry->id) }}" class="p-2 rounded-lg bg-gray-50 text-purple-600 hover:text-purple-800 border border-gray-200 shadow-sm transition" title="Activity History">
                            <i class="fas fa-history w-3.5 h-3.5 flex items-center justify-center"></i>
                        </a>
                    @endcan

                    @can('convert inquiries')
                        @if($inquiry->status != 'Slot Reserved' && $inquiry->status != 'Lost')
                            <a href="{{ route('bookings.create', ['inquiry_id' => $inquiry->id]) }}" class="p-2 rounded-lg bg-gray-50 text-green-600 hover:text-green-800 border border-gray-200 shadow-sm transition" title="Convert to Booking">
                                <i class="fas fa-calendar-check w-3.5 h-3.5 flex items-center justify-center"></i>
                            </a>
                        @endif
                    @endcan

                    @can('view inquiries')
                        <a href="{{ route('inquiries.show', $inquiry->id) }}" class="p-2 rounded-lg bg-gray-50 text-gray-600 hover:text-black border border-gray-200 shadow-sm transition" title="View">
                            <i class="fas fa-eye w-3.5 h-3.5 flex items-center justify-center"></i>
                        </a>
                    @endcan
                    
                    @can('edit inquiries')
                        <a href="{{ route('inquiries.edit', $inquiry->id) }}" class="p-2 rounded-lg bg-gray-50 text-blue-600 hover:text-blue-800 border border-gray-200 shadow-sm transition" title="Edit">
                            <i class="fas fa-pencil-alt w-3.5 h-3.5 flex items-center justify-center"></i>
                        </a>
                    @endcan

                    @can('delete inquiries')
                        @php $hasBookings = $inquiry->bookings->whereIn('status', ['Scheduled', 'Completed', 'Cancelled'])->isNotEmpty(); @endphp
                        @if($hasBookings)
                            <button type="button" class="p-2 rounded-lg bg-gray-50 text-red-200 border border-gray-200 shadow-sm cursor-not-allowed" title="Cannot delete: Associated bookings" 
                                onclick="window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'warning', title: 'Action Blocked', message: 'Cannot delete this inquiry because it has associated bookings.' } }))">
                                <i class="fas fa-trash-alt w-3.5 h-3.5 flex items-center justify-center"></i>
                            </button>
                        @else
                            <form id="del-inq-mobile-{{ $inquiry->id }}" action="{{ route('inquiries.destroy', $inquiry->id) }}" method="POST" class="hidden">
                                @csrf @method('DELETE')
                            </form>
                            <button type="button" class="p-2 rounded-lg bg-gray-50 text-red-600 hover:text-red-800 border border-gray-200 shadow-sm transition" title="Delete"
                                onclick="confirmDelete('del-inq-mobile-{{ $inquiry->id }}')">
                                <i class="fas fa-trash-alt w-3.5 h-3.5 flex items-center justify-center"></i>
                            </button>
                        @endif
                    @endcan
                </div>
            </div>
            
        </div>
        @empty
            <div class="p-4 text-center text-gray-500 bg-white border border-gray-200 rounded-2xl">
                No inquiries found.
            </div>
        @endforelse

        @if($inquiries->hasPages())
            <div class="pt-2">
                {{ $inquiries->withQueryString()->links() }}
            </div>
        @endif
    </div>

</x-card>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".datepicker", { dateFormat: "Y-m-d", allowInput: true, altInput: true, altFormat: "F j, Y", placeholder: "Select Date" });
        
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

    @can('export inquiries')
    function confirmExport() {
        // Get current URL parameters to preserve filters
        const urlParams = new URLSearchParams(window.location.search);
        const exportUrl = "{{ route('inquiries.export') }}?" + urlParams.toString();

        window.dispatchEvent(new CustomEvent('confirm', { 
            detail: { 
                title: 'Export Confirmation', 
                message: 'Do you want to export the current filtered list of inquiries to Excel?', 
                type: 'info', 
                action: () => window.location.href = exportUrl 
            } 
        }));
    }
    @endcan
</script>
@endsection