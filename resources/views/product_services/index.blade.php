@extends('layouts.app')
@section('header', 'Products & Services')

@section('content')

<x-card title="Products & Services">
    
    <x-slot name="action">
        <div class="flex items-center justify-end gap-1.5 sm:gap-2">
            
            <button @click="$dispatch('toggle-search')" class="md:hidden h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-black hover:text-blue-600 hover:border-blue-300 transition shadow-sm" title="Toggle Search">
                <i class="fas fa-search"></i>
            </button>

            <form action="{{ route('product_services.index') }}" method="GET" class="hidden md:block relative w-64 search-form">
                @foreach(request()->only(['name', 'type', 'pricing_model', 'price', 'gst_rate']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach

                <div class="relative h-9">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-black">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search"
                           class="search-input h-full w-full rounded-lg border border-gray-200 bg-gray-50 pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-black text-black" 
                           placeholder="Search (min 3 chars)..." 
                           value="{{ request('search') }}"
                           autocomplete="off">
                </div>
            </form>

            <button @click="$dispatch('toggle-advanced')" class="h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-black hover:text-blue-600 hover:border-blue-300 transition shadow-sm" title="Advanced Search">
                <i class="fas fa-sliders-h"></i>
            </button>

            @can('create products')
            <div class="hidden md:block">
                <x-button href="{{ route('product_services.create') }}" icon="fas fa-plus" class="!h-9 !py-0 flex items-center">
                    Add New
                </x-button>
            </div>
            <a href="{{ route('product_services.create') }}" class="md:hidden h-9 w-9 flex items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm hover:bg-blue-700 transition" title="Add New">
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
        <form action="{{ route('product_services.index') }}" method="GET" class="relative w-full search-form">
            @foreach(request()->only(['name', 'type', 'pricing_model', 'price', 'gst_rate']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <div class="relative h-10 w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-black">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" 
                    class="search-input h-full w-full rounded-xl border border-gray-200 bg-white pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-gray-400 text-black shadow-sm" 
                    placeholder="Search (min 3 chars)..." 
                    value="{{ request('search') }}" 
                    autocomplete="off">
            </div>
        </form>
    </div>

    <div x-data="{ show: {{ request()->hasAny(['name', 'type', 'pricing_model', 'price', 'gst_rate']) ? 'true' : 'false' }} }"
         @toggle-advanced.window="show = !show; if(show) $dispatch('close-search')"
         @close-advanced.window="show = false"
         x-show="show" x-collapse
         class="bg-gray-50 border-b border-gray-100 p-5 mb-4">
        
        <form action="{{ route('product_services.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                
                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">Name</label>
                    <input type="text" name="name" value="{{ request('name') }}" class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black placeholder-gray-400 shadow-sm" placeholder="Item Name">
                </div>

                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">Type</label>
                    <div class="relative z-20 bg-white shadow-sm rounded-xl">
                        <select name="type" class="relative z-20 w-full appearance-none rounded-xl border border-gray-200 bg-transparent py-2.5 pl-4 pr-10 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black">
                            <option value="">All Types</option>
                            <option value="Product" {{ request('type') == 'Product' ? 'selected' : '' }}>Product</option>
                            <option value="Service" {{ request('type') == 'Service' ? 'selected' : '' }}>Service</option>
                        </select>
                        <span class="absolute top-1/2 right-3 z-30 -translate-y-1/2 text-black">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">Pricing Model</label>
                    <div class="relative z-20 bg-white shadow-sm rounded-xl">
                        <select name="pricing_model" class="relative z-20 w-full appearance-none rounded-xl border border-gray-200 bg-transparent py-2.5 pl-4 pr-10 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black">
                            <option value="">All Models</option>
                            <option value="Per Unit" {{ request('pricing_model') == 'Per Unit' ? 'selected' : '' }}>Per Unit</option>
                            <option value="Hourly" {{ request('pricing_model') == 'Hourly' ? 'selected' : '' }}>Hourly</option>
                            <option value="Fixed" {{ request('pricing_model') == 'Fixed' ? 'selected' : '' }}>Fixed</option>
                            <option value="Monthly" {{ request('pricing_model') == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                        <span class="absolute top-1/2 right-3 z-30 -translate-y-1/2 text-black">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">Price (Max)</label>
                    <div class="relative shadow-sm rounded-xl">
                        <span class="absolute top-1/2 left-3 -translate-y-1/2 text-black text-xs">₹</span>
                        <input type="number" name="price" value="{{ request('price') }}" step="0.01" class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-7 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black placeholder-gray-400" placeholder="Max Price">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">GST %</label>
                    <div class="relative z-20 bg-white shadow-sm rounded-xl">
                        <select name="gst_rate" class="relative z-20 w-full appearance-none rounded-xl border border-gray-200 bg-transparent py-2.5 pl-4 pr-10 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black">
                            <option value="">All Rates</option>
                            <option value="0" {{ request('gst_rate') === '0' ? 'selected' : '' }}>0% (Exempt)</option>
                            <option value="5" {{ request('gst_rate') == '5' ? 'selected' : '' }}>5%</option>
                            <option value="12" {{ request('gst_rate') == '12' ? 'selected' : '' }}>12%</option>
                            <option value="18" {{ request('gst_rate') == '18' ? 'selected' : '' }}>18%</option>
                            <option value="28" {{ request('gst_rate') == '28' ? 'selected' : '' }}>28%</option>
                        </select>
                        <span class="absolute top-1/2 right-3 z-30 -translate-y-1/2 text-black">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('product_services.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-black shadow-sm hover:bg-gray-50 transition">
                    <i class="fas fa-undo"></i> Reset
                </a>

                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <div class="hidden md:block">
        <x-table :headers="['Name', 'Type', 'Pricing Model', 'Price', 'GST', 'Actions']">
            @forelse ($items as $item)
            <tr class="hover:bg-gray-50 transition duration-200 {{ !$item->is_active ? 'opacity-50 grayscale' : '' }}">
                
                <td class="px-6 py-4 align-top w-[40%]">
                    <div class="font-bold text-black text-sm">{{ $item->name }}</div>
                    @if(!$item->is_active) 
                        <span class="text-[10px] text-red-500 font-bold uppercase tracking-wide">[Inactive]</span> 
                    @endif
                </td>

                <td class="px-6 py-4 align-top whitespace-nowrap">
                    <div class="text-xs text-black font-medium bg-gray-100 inline-block px-1.5 py-0.5 rounded border border-gray-200">
                        {{ $item->type }}
                    </div>
                </td>

                <td class="px-6 py-4 align-top text-black text-sm font-medium whitespace-nowrap">
                    {{ $item->pricing_model }}
                </td>

                <td class="px-6 py-4 align-top font-bold text-black text-sm font-mono text-right whitespace-nowrap w-[10%]">
                    ₹{{ number_format($item->price, 2) }}
                </td>

                <td class="px-6 py-4 align-top text-black text-sm font-medium font-mono whitespace-nowrap">
                    {{ number_format($item->gst_rate, 2) }}%
                </td>

                <td class="px-6 py-4 align-top text-right whitespace-nowrap">
                    <div class="flex justify-end items-center gap-1">
                        @can('edit products')
                        <x-button variant="secondary" href="{{ route('product_services.edit', $item->id) }}" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-blue-500 hover:!bg-blue-50" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </x-button>
                        @endcan

                        @can('delete products')
                        <form id="del-item-{{ $item->id }}" action="{{ route('product_services.destroy', $item->id) }}" method="POST" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                        
                        <x-button variant="secondary" type="button" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-red-500 hover:!bg-red-50" title="Delete"
                            onclick="confirmDelete('del-item-{{ $item->id }}', 'Are you sure you want to delete this item?')">
                            <i class="fas fa-trash-alt"></i>
                        </x-button>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No products or services found.</td>
                </tr>
            @endforelse

            <x-slot name="pagination">
                @if($items->hasPages())
                    {{ $items->withQueryString()->links() }}
                @endif
            </x-slot>
        </x-table>
    </div>

    <div class="block md:hidden space-y-4 p-4">
        @forelse ($items as $item)
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 flex flex-col gap-4 {{ !$item->is_active ? 'opacity-60 grayscale' : '' }}">
            
            <div class="flex justify-between items-start border-b border-gray-100 pb-3">
                <div class="flex-1 pr-3">
                    <div class="font-bold text-gray-900 text-base leading-tight">{{ $item->name }}</div>
                    @if(!$item->is_active) 
                        <div class="text-[10px] text-red-500 font-bold uppercase tracking-wide mt-1">[Inactive]</div> 
                    @endif
                </div>
                <div>
                    <span class="text-[10px] text-gray-700 font-bold uppercase bg-gray-100 px-2 py-1 rounded border border-gray-200 whitespace-nowrap">
                        {{ $item->type }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div class="bg-blue-50 p-2.5 rounded-xl border border-blue-100 flex flex-col justify-center">
                    <span class="text-[10px] text-blue-600 font-bold uppercase mb-0.5">Price</span>
                    <span class="font-bold text-gray-900 text-sm font-mono">₹{{ number_format($item->price, 2) }}</span>
                    <span class="text-[10px] text-gray-600 mt-0.5">{{ $item->pricing_model }}</span>
                </div>
                
                <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-200 flex flex-col justify-center">
                    <span class="text-[10px] text-gray-500 font-bold uppercase mb-0.5">Tax (GST)</span>
                    <span class="font-bold text-gray-900 text-sm font-mono">{{ number_format($item->gst_rate, 2) }}%</span>
                </div>
            </div>

            <div class="flex items-center justify-end pt-2 border-t border-gray-100 gap-1.5">
                @can('edit products')
                    <a href="{{ route('product_services.edit', $item->id) }}" class="p-2 rounded-lg bg-gray-50 text-blue-600 hover:text-blue-800 border border-gray-200 shadow-sm transition" title="Edit">
                        <i class="fas fa-pencil-alt w-3.5 h-3.5 flex items-center justify-center"></i>
                    </a>
                @endcan

                @can('delete products')
                    <form id="del-item-mobile-{{ $item->id }}" action="{{ route('product_services.destroy', $item->id) }}" method="POST" class="hidden">
                        @csrf @method('DELETE')
                    </form>
                    <button type="button" class="p-2 rounded-lg bg-gray-50 text-red-600 hover:text-red-800 border border-gray-200 shadow-sm transition" title="Delete"
                        onclick="confirmDelete('del-item-mobile-{{ $item->id }}', 'Are you sure you want to delete this item?')">
                        <i class="fas fa-trash-alt w-3.5 h-3.5 flex items-center justify-center"></i>
                    </button>
                @endcan
            </div>
            
        </div>
        @empty
            <div class="p-4 text-center text-gray-500 bg-white border border-gray-200 rounded-2xl">
                No products or services found.
            </div>
        @endforelse

        @if($items->hasPages())
            <div class="pt-2">
                {{ $items->withQueryString()->links() }}
            </div>
        @endif
    </div>

</x-card>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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
</script>
@endsection