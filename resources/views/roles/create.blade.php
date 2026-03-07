@extends('layouts.app')
@section('header', 'Create Role')

@section('content')

<form action="{{ route('roles.store') }}" method="POST">
    @csrf

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm mb-6">
        <div class="border-b border-gray-100 py-4 px-6">
            <h3 class="font-bold text-slate-800 text-lg">Role Details</h3>
        </div>
        <div class="p-6">
            <div class="w-full md:w-1/2">
                <label class="block text-sm font-bold text-slate-700 mb-2">Role Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 px-4 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-500 text-slate-800 placeholder-gray-400"
                       placeholder="e.g. Sales Executive, Accountant, Front Desk">
                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <h2 class="text-lg font-bold text-slate-800 mb-4 px-1">Assign Permissions</h2>

    @php
        // 1. Flatten all permissions from the original groupings
        $allPermissions = [];
        foreach ($groupedPermissions as $group => $perms) {
            foreach ($perms as $perm) {
                $allPermissions[] = $perm;
            }
        }

        // 2. Re-group them accurately based on keywords and specific overrides
        $newGrouped = [];
        foreach ($allPermissions as $perm) {
            $name = $perm->name;
            $targetGroup = 'Other';

            // Specific Overrides Requested
            if ($name === 'download order pdf') { $targetGroup = 'Orders & Invoices'; }
            elseif ($name === 'manage inquiry logs') { $targetGroup = 'Inquiries'; }
            elseif ($name === 'toggle customer status') { $targetGroup = 'Customers'; }
            elseif ($name === 'view booking calendar') { $targetGroup = 'Bookings'; }
            elseif ($name === 'toggle staff status') { $targetGroup = 'Staff'; } // Safety catch
            
            // Standard Groupings
            elseif (str_contains($name, 'staff')) { $targetGroup = 'Staff'; }
            elseif (str_contains($name, 'customer')) { $targetGroup = 'Customers'; }
            elseif (str_contains($name, 'inquir')) { $targetGroup = 'Inquiries'; }
            elseif (str_contains($name, 'booking')) { $targetGroup = 'Bookings'; }
            elseif (str_contains($name, 'order')) { $targetGroup = 'Orders & Invoices'; }
            elseif (str_contains($name, 'payment')) { $targetGroup = 'Payments'; }
            elseif (str_contains($name, 'expense')) { $targetGroup = 'Expenses'; }
            elseif (str_contains($name, 'product')) { $targetGroup = 'Products & Services'; }
            elseif (str_contains($name, 'report')) { $targetGroup = 'Reports'; }
            elseif (str_contains($name, 'dashboard')) { $targetGroup = 'Dashboard'; }
            elseif (str_contains($name, 'setting') || str_contains($name, 'role') || str_contains($name, 'integration')) { $targetGroup = 'System Administration'; }

            $newGrouped[$targetGroup][] = $perm;
        }

        // 3. Define the exact sequence requested
        $preferredOrder = [
            'Dashboard',
            'Staff',
            'Customers',
            'Inquiries',
            'Bookings',
            'Orders & Invoices',
            'Payments',
            'Expenses',
            'Products & Services',
            'Reports',
            'System Administration'
        ];

        // 4. Sort the permissions based on the sequence (and completely drop 'Other')
        $sortedGroups = [];
        foreach ($preferredOrder as $group) {
            if (isset($newGrouped[$group]) && count($newGrouped[$group]) > 0) {
                $sortedGroups[$group] = $newGrouped[$group];
            }
        }
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @foreach($sortedGroups as $group => $permissions)
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm flex flex-col overflow-hidden">
            <div class="bg-gray-50/50 border-b border-gray-100 px-5 py-3.5 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 text-sm">{{ $group }}</h3>
                <label class="text-xs text-blue-600 font-bold cursor-pointer hover:underline flex items-center gap-1">
                    <input type="checkbox" class="hidden select-all-btn" data-target="group-{{ Str::slug($group) }}">
                    Select All
                </label>
            </div>
            <div class="p-5 flex-1 space-y-3" id="group-{{ Str::slug($group) }}">
                @foreach($permissions as $permission)
                <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative flex items-center justify-center h-4 w-4 shrink-0">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                               class="peer h-4 w-4 cursor-pointer appearance-none rounded border border-gray-300 bg-gray-50 transition-all checked:border-blue-600 checked:bg-blue-600 hover:border-blue-400">
                        <div class="pointer-events-none absolute text-white opacity-0 transition-opacity peer-checked:opacity-100 flex items-center justify-center">
                            <i class="fas fa-check text-[10px]"></i>
                        </div>
                    </div>
                    <span class="text-sm font-medium text-slate-700 group-hover:text-blue-600 transition select-none">
                        {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                    </span>
                </label>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <div class="flex justify-end gap-3 border-t border-gray-200 pt-6">
        <a href="{{ route('roles.index') }}" class="rounded-xl border border-gray-300 bg-white py-2.5 px-6 text-sm font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm">Cancel</a>
        <button type="submit" class="rounded-xl bg-blue-600 py-2.5 px-8 text-sm font-bold text-white hover:bg-blue-700 transition shadow-sm">Save Role</button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.select-all-btn').forEach(button => {
            button.addEventListener('change', function () {
                const targetId = this.getAttribute('data-target');
                const checkboxes = document.querySelectorAll(`#${targetId} input[type="checkbox"]`);
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        });
    });
</script>
@endsection