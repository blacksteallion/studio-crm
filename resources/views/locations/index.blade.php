@extends('layouts.app')
@section('header', 'Studio Locations')

@section('content')

<x-card title="Locations List">
    
    <x-slot name="action">
        <div class="flex items-center justify-end gap-1.5 sm:gap-2">
            
            <button @click="$dispatch('toggle-search')" class="md:hidden h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-black hover:text-blue-600 hover:border-blue-300 transition shadow-sm" title="Toggle Search">
                <i class="fas fa-search"></i>
            </button>

            <form action="{{ route('locations.index') }}" method="GET" class="hidden md:block relative w-64 search-form">
                @foreach(request()->only(['f_name', 'f_status']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                
                <div class="relative h-9">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-black">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" 
                        class="search-input h-full w-full rounded-lg border border-gray-200 bg-gray-50 pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-black text-black" 
                        placeholder="Search locations..." 
                        value="{{ request('search') }}" 
                        autocomplete="off">
                </div>
            </form>

            <button @click="$dispatch('toggle-advanced')" class="h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-black hover:text-blue-600 hover:border-blue-300 transition shadow-sm" title="Advanced Search">
                <i class="fas fa-sliders-h"></i>
            </button>

            <div class="hidden md:block">
                <x-button type="button" @click="$dispatch('open-create-modal')" icon="fas fa-plus" class="!h-9 !py-0 flex items-center">
                    Add Location
                </x-button>
            </div>
            <button type="button" @click="$dispatch('open-create-modal')" class="md:hidden h-9 w-9 flex items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm hover:bg-blue-700 transition" title="Add Location">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </x-slot>

    <div x-data="{ searchOpen: {{ request('search') ? 'true' : 'false' }} }"
         @toggle-search.window="searchOpen = !searchOpen; if(searchOpen) $dispatch('close-advanced')"
         @close-search.window="searchOpen = false"
         x-show="searchOpen" x-collapse
         class="md:hidden bg-gray-50 border-b border-gray-100 p-4">
        <form action="{{ route('locations.index') }}" method="GET" class="relative w-full search-form">
            @foreach(request()->only(['f_name', 'f_status']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <div class="relative h-10 w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-black">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" 
                    class="search-input h-full w-full rounded-xl border border-gray-200 bg-white pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-gray-400 text-black shadow-sm" 
                    placeholder="Search locations..." 
                    value="{{ request('search') }}" 
                    autocomplete="off">
            </div>
        </form>
    </div>

    <div x-data="{ show: {{ request()->hasAny(['f_name', 'f_status']) ? 'true' : 'false' }} }"
         @toggle-advanced.window="show = !show; if(show) $dispatch('close-search')"
         @close-advanced.window="show = false"
         x-show="show" x-collapse
         class="bg-gray-50 border-b border-gray-100 p-5 mb-4">
        
        <form action="{{ route('locations.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">Location Name</label>
                    <input type="text" name="f_name" value="{{ request('f_name') }}" class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-4 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black placeholder-gray-400 shadow-sm" placeholder="Search by name">
                </div>
                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">Status</label>
                    <div class="relative z-20 bg-white rounded-xl shadow-sm">
                        <select name="f_status" class="relative z-20 w-full appearance-none rounded-xl border border-gray-200 bg-transparent py-2.5 pl-4 pr-10 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black">
                            <option value="">All Status</option>
                            <option value="1" {{ request('f_status') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('f_status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <span class="absolute top-1/2 right-3 z-30 -translate-y-1/2 text-black">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('locations.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-black shadow-sm hover:bg-gray-50 transition">
                    <i class="fas fa-undo"></i> Reset
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 transition">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <div class="hidden md:block">
        <x-table :headers="['#', 'Location Details', 'Contact Number', 'Status', 'Actions']">
            @forelse ($locations as $index => $location)
            <tr class="hover:bg-gray-50 transition duration-200">
                <td class="px-6 py-4 text-black font-medium w-12 align-middle">
                    {{ $locations->firstItem() + $index }}
                </td>
                
                <td class="px-6 py-4 align-middle">
                    <div class="font-bold text-black text-sm">{{ $location->name }}</div>
                    <div class="text-xs text-gray-500 mt-0.5 max-w-xs truncate" title="{{ $location->address }}">
                        <i class="fas fa-map-marker-alt mr-1"></i> {{ $location->address ?? 'No address provided' }}
                    </div>
                </td>
                
                <td class="px-6 py-4 align-middle">
                    <div class="text-sm text-black font-medium flex items-center gap-2">
                        <i class="fas fa-phone-alt text-black opacity-70"></i> {{ $location->contact_number ?? 'N/A' }}
                    </div>
                </td>
                
                <td class="px-6 py-4 align-middle">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" 
                            onchange="toggleStatus({{ $location->id }})" 
                            {{ $location->is_active ? 'checked' : '' }} 
                            class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500"></div>
                    </label>
                </td>
                
                <td class="px-6 py-4 text-right align-middle">
                    <div class="flex justify-end items-center gap-1">
                        
                        <x-button variant="secondary" type="button" 
                                  @click="$dispatch('open-edit-modal', { id: {{ $location->id }}, name: '{{ addslashes($location->name) }}', address: '{{ addslashes($location->address ?? '') }}', phone: '{{ addslashes($location->contact_number ?? '') }}' })" 
                                  class="!p-1.5 !rounded-lg !border-0 !shadow-none text-blue-500 hover:!bg-blue-50" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </x-button>

                        <form id="del-loc-{{ $location->id }}" action="{{ route('locations.destroy', $location->id) }}" method="POST" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                        <x-button variant="secondary" type="button" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-red-500 hover:!bg-red-50" title="Delete"
                            onclick="confirmDelete('del-loc-{{ $location->id }}', 'Are you sure you want to delete this location? Note: You cannot delete a location if it has associated bookings or orders.')">
                            <i class="fas fa-trash-alt"></i>
                        </x-button>
                    </div>
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No locations found.</td>
                </tr>
            @endforelse

            <x-slot name="pagination">
                @if($locations->hasPages())
                    {{ $locations->withQueryString()->links() }}
                @endif
            </x-slot>
        </x-table>
    </div>

    <div class="block md:hidden space-y-4 p-4">
        @forelse ($locations as $index => $location)
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 flex flex-col gap-4">
            
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-lg border border-blue-100 shadow-sm">
                    <i class="fas fa-building"></i>
                </div>
                <div class="flex-1">
                    <div class="font-bold text-gray-900 text-sm">{{ $location->name }}</div>
                    @if($location->contact_number)
                    <div class="text-[12px] text-gray-500 mt-0.5">
                        <i class="fas fa-phone-alt mr-1"></i> {{ $location->contact_number }}
                    </div>
                    @endif
                </div>
            </div>

            @if($location->address)
            <div class="flex flex-col gap-2 bg-gray-50 p-3 rounded-xl border border-gray-100">
                <div class="text-sm text-gray-700 flex items-start gap-3">
                    <i class="fas fa-map-marker-alt mt-1 opacity-50 w-4 text-center"></i> 
                    <span class="flex-1">{{ $location->address }}</span>
                </div>
            </div>
            @endif

            <div class="flex items-center justify-between pt-1 border-t border-gray-50 mt-1">
                <div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" 
                            onchange="toggleStatus({{ $location->id }})" 
                            {{ $location->is_active ? 'checked' : '' }} 
                            class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500"></div>
                    </label>
                </div>

                <div class="flex justify-end items-center gap-2">
                    
                    <button type="button" 
                            @click="$dispatch('open-edit-modal', { id: {{ $location->id }}, name: '{{ addslashes($location->name) }}', address: '{{ addslashes($location->address ?? '') }}', phone: '{{ addslashes($location->contact_number ?? '') }}' })" 
                            class="p-2 rounded-lg bg-gray-50 text-blue-600 hover:text-blue-800 border border-gray-200 shadow-sm transition" title="Edit">
                        <i class="fas fa-pencil-alt w-4 h-4 flex items-center justify-center"></i>
                    </button>
                    
                    <form id="del-loc-mobile-{{ $location->id }}" action="{{ route('locations.destroy', $location->id) }}" method="POST" class="hidden">
                        @csrf @method('DELETE')
                    </form>
                    <button type="button" class="p-2 rounded-lg bg-gray-50 text-red-600 hover:text-red-800 border border-gray-200 shadow-sm transition" title="Delete"
                        onclick="confirmDelete('del-loc-mobile-{{ $location->id }}', 'Are you sure you want to delete this location?')">
                        <i class="fas fa-trash-alt w-4 h-4 flex items-center justify-center"></i>
                    </button>
                </div>
            </div>
            
        </div>
        @empty
            <div class="p-4 text-center text-gray-500 bg-white border border-gray-200 rounded-2xl">
                No locations found.
            </div>
        @endforelse

        @if($locations->hasPages())
            <div class="pt-2">
                {{ $locations->withQueryString()->links() }}
            </div>
        @endif
    </div>
</x-card>

<div x-data="{ open: false }" 
     @open-create-modal.window="open = true" 
     x-show="open" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-95"
     class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm px-4" 
     style="display: none;">
    <div @click.away="open = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <form action="{{ route('locations.store') }}" method="POST">
            @csrf
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-lg text-gray-900">Add New Location</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-700 bg-white rounded-full p-1.5 shadow-sm border border-gray-200 transition"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 space-y-5">
                <div>
                    <label class="block text-sm font-bold mb-1 text-gray-700">Location Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full rounded-xl border border-gray-300 p-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="e.g. S G Highway Branch">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1 text-gray-700">Full Address</label>
                    <textarea name="address" rows="3" class="w-full rounded-xl border border-gray-300 p-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Street, City, Pincode"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1 text-gray-700">Contact Number</label>
                    <input type="text" name="phone" class="w-full rounded-xl border border-gray-300 p-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Manager or Branch Phone">
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                <button type="button" @click="open = false" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl font-bold shadow-sm hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl font-bold shadow-sm hover:bg-blue-700 transition">Save Location</button>
            </div>
        </form>
    </div>
</div>

<div x-data="{ open: false, id: '', name: '', address: '', phone: '' }" 
     @open-edit-modal.window="open = true; id = $event.detail.id; name = $event.detail.name; address = $event.detail.address; phone = $event.detail.phone;" 
     x-show="open" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-95"
     class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm px-4" 
     style="display: none;">
    <div @click.away="open = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <form :action="`/locations/${id}`" method="POST">
            @csrf
            @method('PUT')
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-lg text-gray-900">Edit Location</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-700 bg-white rounded-full p-1.5 shadow-sm border border-gray-200 transition"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 space-y-5">
                <div>
                    <label class="block text-sm font-bold mb-1 text-gray-700">Location Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="name" required class="w-full rounded-xl border border-gray-300 p-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1 text-gray-700">Full Address</label>
                    <textarea name="address" x-model="address" rows="3" class="w-full rounded-xl border border-gray-300 p-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1 text-gray-700">Contact Number</label>
                    <input type="text" name="phone" x-model="phone" class="w-full rounded-xl border border-gray-300 p-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                <button type="button" @click="open = false" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl font-bold shadow-sm hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl font-bold shadow-sm hover:bg-blue-700 transition">Update Location</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInputs = document.querySelectorAll('.search-input');
        let timeout = null;

        searchInputs.forEach(input => {
            if (input.value.length > 0 && document.activeElement !== input) {
                if(window.innerWidth > 768) {
                    input.focus();
                    const val = input.value;
                    input.value = '';
                    input.value = val;
                }
            }

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

    function toggleStatus(id) {
        fetch(`/locations/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { type: 'success', title: 'Success', message: data.message || 'Status updated successfully.' } 
            }));
        })
        .catch(error => {
            console.error('Error:', error);
            const checkboxes = document.querySelectorAll(`input[onchange="toggleStatus(${id})"]`);
            checkboxes.forEach(checkbox => checkbox.checked = !checkbox.checked);
            
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { type: 'danger', title: 'Error', message: 'Failed to update location status.' } 
            }));
        });
    }
</script>
@endsection