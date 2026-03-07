@extends('layouts.app')
@section('header', 'Roles & Permissions')

@section('content')

<x-card title="System Roles">
    
    <x-slot name="action">
        <x-button href="{{ route('roles.create') }}" icon="fas fa-plus">
            Create Role
        </x-button>
    </x-slot>

    <x-table :headers="['Role Name', 'Permissions', 'Actions']">
        @forelse ($roles as $role)
        <tr class="hover:bg-gray-50 transition duration-200">
            
            <td class="px-6 py-4 align-top w-1/4">
                <div class="font-bold text-black text-sm">{{ $role->name }}</div>
                <div class="text-xs text-black mt-0.5 font-medium">
                    {{ $role->users()->count() ?? 0 }} Staff Member(s)
                </div>
            </td>

            <td class="px-6 py-4 align-top w-1/2">
                @if($role->name === 'Super Admin')
                    <span class="text-xs text-black font-medium bg-blue-100 inline-block px-2 py-1 rounded border border-blue-200">
                        All Access Granted
                    </span>
                @else
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($role->permissions->take(8) as $permission)
                            <span class="text-[11px] text-black font-medium bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200">
                                {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                            </span>
                        @endforeach
                        @if($role->permissions->count() > 8)
                            <span class="text-[11px] text-black font-bold bg-gray-200 px-1.5 py-0.5 rounded border border-gray-300">
                                +{{ $role->permissions->count() - 8 }} more
                            </span>
                        @endif
                        @if($role->permissions->count() == 0)
                            <span class="text-xs text-gray-400 italic">No permissions assigned</span>
                        @endif
                    </div>
                @endif
            </td>

            <td class="px-6 py-4 align-top text-right w-1/4">
                @if($role->name !== 'Super Admin')
                <div class="flex justify-end items-center gap-1">
                    <x-button variant="secondary" href="{{ route('roles.edit', $role->id) }}" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-blue-500 hover:!bg-blue-50" title="Edit Role">
                        <i class="fas fa-pencil-alt"></i>
                    </x-button>

                    <form id="del-role-{{ $role->id }}" action="{{ route('roles.destroy', $role->id) }}" method="POST" class="hidden">
                        @csrf @method('DELETE')
                    </form>
                    
                    <x-button variant="secondary" type="button" class="!p-1.5 !rounded-lg !border-0 !shadow-none text-red-500 hover:!bg-red-50" title="Delete Role"
                        onclick="confirmDelete('del-role-{{ $role->id }}', 'Are you sure you want to delete this role? Any staff with this role will lose their permissions.')">
                        <i class="fas fa-trash-alt"></i>
                    </x-button>
                </div>
                @else
                <span class="text-xs text-gray-400 italic font-medium tracking-wide">SYSTEM DEFAULT</span>
                @endif
            </td>
        </tr>
        @empty
            <tr>
                <td colspan="3" class="px-6 py-4 text-center text-gray-500">No roles found.</td>
            </tr>
        @endforelse
    </x-table>

</x-card>

@endsection