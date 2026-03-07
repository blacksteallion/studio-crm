@extends('layouts.app')
@section('header', 'Staff')

@section('content')

<x-card title="Staff List">
    
    <x-slot name="action">
        <div class="flex items-center justify-end gap-1.5 sm:gap-2">
            
            <button @click="$dispatch('toggle-search')" class="md:hidden h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-black hover:text-blue-600 hover:border-blue-300 transition shadow-sm" title="Toggle Search">
                <i class="fas fa-search"></i>
            </button>

            <form action="{{ route('staff.index') }}" method="GET" class="hidden md:block relative w-64 search-form">
                @foreach(request()->only(['f_name', 'f_email', 'f_mobile', 'f_status']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <div class="relative h-9">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-black">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" 
                        class="search-input h-full w-full rounded-lg border border-gray-200 bg-gray-50 pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-black text-black" 
                        placeholder="Search staff..." 
                        value="{{ request('search') }}" 
                        autocomplete="off">
                </div>
            </form>

            <button @click="$dispatch('toggle-advanced')" class="h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-black hover:text-blue-600 hover:border-blue-300 transition shadow-sm" title="Advanced Search">
                <i class="fas fa-sliders-h"></i>
            </button>

            @can('export staff')
            <button type="button" onclick="confirmExport()" class="h-9 w-9 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-green-600 hover:text-green-700 hover:border-green-300 transition shadow-sm" title="Export to Excel">
                <i class="fas fa-file-excel"></i>
            </button>
            @endcan

            @can('create staff')
            <div class="hidden md:block">
                <x-button href="{{ route('staff.create') }}" icon="fas fa-plus" class="!h-9 !py-0 flex items-center">
                    Add Staff
                </x-button>
            </div>
            <a href="{{ route('staff.create') }}" class="md:hidden h-9 w-9 flex items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm hover:bg-blue-700 transition" title="Add Staff">
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
        <form action="{{ route('staff.index') }}" method="GET" class="relative w-full search-form">
            @foreach(request()->only(['f_name', 'f_email', 'f_mobile', 'f_status']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <div class="relative h-10 w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-black">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" 
                    class="search-input h-full w-full rounded-xl border border-gray-200 bg-white pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-gray-400 text-black shadow-sm" 
                    placeholder="Search staff..." 
                    value="{{ request('search') }}" 
                    autocomplete="off">
            </div>
        </form>
    </div>

    <div x-data="{ show: {{ request()->hasAny(['f_name', 'f_email', 'f_mobile', 'f_status']) ? 'true' : 'false' }} }"
         @toggle-advanced.window="show = !show; if(show) $dispatch('close-search')"
         @close-advanced.window="show = false"
         x-show="show" x-collapse
         class="bg-gray-50 border-b border-gray-100 p-5 mb-4">
        
        <form action="{{ route('staff.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                
                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">Staff Name</label>
                    <input type="text" name="f_name" value="{{ request('f_name') }}" class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-4 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black placeholder-gray-400 shadow-sm" placeholder="Full name">
                </div>

                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">Email</label>
                    <input type="text" name="f_email" value="{{ request('f_email') }}" class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-4 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black placeholder-gray-400 shadow-sm" placeholder="email@domain.com">
                </div>

                <div>
                    <label class="block text-xs font-bold text-black uppercase mb-2">Mobile</label>
                    <input type="text" name="f_mobile" value="{{ request('f_mobile') }}" class="w-full rounded-xl border border-gray-200 bg-white py-2.5 px-4 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-black placeholder-gray-400 shadow-sm" placeholder="10 Digits">
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
                <a href="{{ route('staff.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-black shadow-sm hover:bg-gray-50 transition">
                    <i class="fas fa-undo"></i> Reset
                </a>

                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 transition">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <div class="hidden md:block">
        <x-table :headers="['#', 'Photo', 'Name', 'Contact', 'Role', 'Status', 'Actions']">
            @forelse ($staffMembers as $index => $staff)
            <tr class="hover:bg-gray-50 transition duration-200">
                
                <td class="px-6 py-4 text-black font-medium w-12 align-middle">
                    {{ $staffMembers->firstItem() + $index }}
                </td>

                <td class="px-6 py-4 w-16 align-middle">
                    @if($staff->photo)
                        <img src="{{ asset('storage/staff_photos/' . $staff->photo) }}" 
                             class="w-10 h-10 rounded-full object-cover border border-gray-200 shadow-sm">
                    @else
                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-black border border-gray-200 shadow-sm">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                </td>

                <td class="px-6 py-4 align-middle">
                    <div class="font-bold text-black text-sm">{{ $staff->name }}</div>
                    <div class="text-xs text-black mt-0.5">
                        Joined {{ $staff->created_at->format('d M, Y') }}
                    </div>
                </td>

                <td class="px-6 py-4 align-middle">
                    <div class="text-sm text-black font-medium flex items-center gap-2">
                        <i class="fas fa-phone-alt text-black opacity-70"></i> {{ $staff->mobile }}
                    </div>
                    <div class="text-sm text-black mt-1 flex items-center gap-2">
                        <i class="fas fa-envelope text-black opacity-70"></i> {{ $staff->email }}
                    </div>
                </td>

                <td class="px-6 py-4 align-middle">
                    <div class="text-xs text-black font-medium bg-gray-100 inline-block px-1.5 py-0.5 rounded border border-gray-200 uppercase tracking-wide">
                        {{ $staff->roles->first()->name ?? 'No Role' }}
                    </div>
                </td>

                <td class="px-6 py-4 align-middle">
                    @can('toggle staff status')
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   onchange="toggleStatus({{ $staff->id }})"
                                   {{ $staff->status ? 'checked' : '' }} 
                                   class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500"></div>
                        </label>
                    @else
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold {{ $staff->status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $staff->status ? 'Active' : 'Inactive' }}
                        </span>
                    @endcan
                </td>

                <td class="px-6 py-4 text-right align-middle">
                    <div class="flex justify-end items-center gap-1">
                        <x-button variant="secondary" href="{{ route('staff.show', $staff->id) }}" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-black hover:!bg-gray-100" title="View">
                            <i class="fas fa-eye"></i>
                        </x-button>

                        @can('edit staff')
                        <x-button variant="secondary" href="{{ route('staff.edit', $staff->id) }}" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-blue-500 hover:!bg-blue-50" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </x-button>
                        @endcan
                        
                        @can('delete staff')
                        <form id="del-staff-{{ $staff->id }}" action="{{ route('staff.destroy', $staff->id) }}" method="POST" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                        <x-button variant="secondary" type="button" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-red-500 hover:!bg-red-50" title="Delete"
                            onclick="confirmDelete('del-staff-{{ $staff->id }}', 'Are you sure you want to remove this staff member?')">
                            <i class="fas fa-trash-alt"></i>
                        </x-button>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No staff members found.</td>
                </tr>
            @endforelse

            <x-slot name="pagination">
                @if($staffMembers->hasPages())
                    {{ $staffMembers->withQueryString()->links() }}
                @endif
            </x-slot>
        </x-table>
    </div>

    <div class="block md:hidden space-y-4 p-4">
        @forelse ($staffMembers as $index => $staff)
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 flex flex-col gap-4">
            
            <div class="flex items-center gap-3">
                @if($staff->photo)
                    <img src="{{ asset('storage/staff_photos/' . $staff->photo) }}" class="w-12 h-12 rounded-full object-cover border border-gray-200 shadow-sm">
                @else
                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-black border border-gray-200 shadow-sm">
                        <i class="fas fa-user"></i>
                    </div>
                @endif
                <div>
                    <div class="font-bold text-gray-900 text-sm">{{ $staff->name }}</div>
                    <div class="text-[11px] text-gray-500 mt-0.5">
                        Joined {{ $staff->created_at->format('d M, Y') }}
                    </div>
                </div>
                <div class="ml-auto">
                    <div class="text-[10px] font-bold bg-gray-100 px-2 py-1 rounded border border-gray-200 uppercase tracking-wide text-gray-700">
                        {{ $staff->roles->first()->name ?? 'No Role' }}
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-2 bg-gray-50 p-3 rounded-xl border border-gray-100">
                <div class="text-sm text-gray-700 flex items-center gap-3">
                    <i class="fas fa-phone-alt opacity-50 w-4 text-center"></i> {{ $staff->mobile }}
                </div>
                <div class="text-sm text-gray-700 flex items-center gap-3">
                    <i class="fas fa-envelope opacity-50 w-4 text-center"></i> {{ $staff->email }}
                </div>
            </div>

            <div class="flex items-center justify-between pt-1">
                <div>
                    @can('toggle staff status')
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   onchange="toggleStatus({{ $staff->id }})"
                                   {{ $staff->status ? 'checked' : '' }} 
                                   class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500"></div>
                        </label>
                    @else
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold {{ $staff->status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $staff->status ? 'Active' : 'Inactive' }}
                        </span>
                    @endcan
                </div>

                <div class="flex justify-end items-center gap-2">
                    <a href="{{ route('staff.show', $staff->id) }}" class="p-2 rounded-lg bg-gray-50 text-gray-600 hover:text-black border border-gray-200 shadow-sm transition" title="View">
                        <i class="fas fa-eye w-4 h-4 flex items-center justify-center"></i>
                    </a>

                    @can('edit staff')
                    <a href="{{ route('staff.edit', $staff->id) }}" class="p-2 rounded-lg bg-gray-50 text-blue-600 hover:text-blue-800 border border-gray-200 shadow-sm transition" title="Edit">
                        <i class="fas fa-pencil-alt w-4 h-4 flex items-center justify-center"></i>
                    </a>
                    @endcan
                    
                    @can('delete staff')
                    <form id="del-staff-mobile-{{ $staff->id }}" action="{{ route('staff.destroy', $staff->id) }}" method="POST" class="hidden">
                        @csrf @method('DELETE')
                    </form>
                    <button type="button" class="p-2 rounded-lg bg-gray-50 text-red-600 hover:text-red-800 border border-gray-200 shadow-sm transition" title="Delete"
                        onclick="confirmDelete('del-staff-mobile-{{ $staff->id }}', 'Are you sure you want to remove this staff member?')">
                        <i class="fas fa-trash-alt w-4 h-4 flex items-center justify-center"></i>
                    </button>
                    @endcan
                </div>
            </div>
            
        </div>
        @empty
            <div class="p-4 text-center text-gray-500 bg-white border border-gray-200 rounded-2xl">
                No staff members found.
            </div>
        @endforelse

        @if($staffMembers->hasPages())
            <div class="pt-2">
                {{ $staffMembers->withQueryString()->links() }}
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
                // We only focus on desktop to prevent mobile keyboards from popping up unexpectedly
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

    @can('export staff')
    function confirmExport() {
        const urlParams = new URLSearchParams(window.location.search);
        const exportUrl = "{{ route('staff.export') }}?" + urlParams.toString();

        window.dispatchEvent(new CustomEvent('confirm', { 
            detail: { 
                title: 'Export Confirmation', 
                message: 'Do you want to export the current filtered list of staff members to Excel?', 
                type: 'info', 
                action: () => window.location.href = exportUrl 
            } 
        }));
    }
    @endcan

    @can('toggle staff status')
    function toggleStatus(id) {
        fetch(`/staff/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                window.dispatchEvent(new CustomEvent('notify', { 
                    detail: { type: 'danger', title: 'Error', message: 'Failed to update status.' } 
                }));
                // Find all checkboxes with this ID (handles both desktop and mobile views)
                const checkboxes = document.querySelectorAll(`input[onchange="toggleStatus(${id})"]`);
                checkboxes.forEach(checkbox => checkbox.checked = !checkbox.checked);
            } else {
                window.dispatchEvent(new CustomEvent('notify', { 
                    detail: { type: 'success', title: 'Updated', message: 'Staff status updated successfully.' } 
                }));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { type: 'danger', title: 'System Error', message: 'An unexpected error occurred.' } 
            }));
        });
    }
    @endcan
</script>
@endsection