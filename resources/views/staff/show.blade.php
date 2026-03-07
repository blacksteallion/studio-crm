@extends('layouts.app')

@section('header', 'View Staff')

@section('content')
<div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
    
    <div class="border-b border-gray-100 py-4 px-6 flex justify-between items-center">
        <h3 class="font-bold text-slate-800 text-lg">Staff Details</h3>
        <div class="flex gap-2">
            <a href="{{ route('staff.index') }}" class="rounded-lg bg-gray-100 py-2 px-4 text-sm font-bold text-gray-600 hover:bg-gray-200 transition">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
            <a href="{{ route('staff.edit', $staff->id) }}" class="rounded-lg bg-blue-50 border border-blue-100 py-2 px-4 text-sm font-bold text-blue-600 hover:bg-blue-100 transition">
                <i class="fas fa-pencil-alt mr-1"></i> Edit
            </a>
        </div>
    </div>

    <div class="p-8">
        <div class="flex flex-col md:flex-row gap-10">
            
            <div class="w-full md:w-1/4 flex flex-col items-center text-center border-r border-gray-100 pr-0 md:pr-10">
                <div class="relative mb-4">
                    @if($staff->photo)
                        <img src="{{ asset('storage/staff_photos/' . $staff->photo) }}" class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-md">
                    @else
                        <div class="w-32 h-32 rounded-full bg-blue-50 flex items-center justify-center text-blue-300 mb-4 border-4 border-white shadow-md">
                            <i class="fas fa-user text-5xl"></i>
                        </div>
                    @endif
                    <div class="absolute bottom-1 right-1 h-5 w-5 rounded-full border-2 border-white {{ $staff->status ? 'bg-green-500' : 'bg-red-500' }}"></div>
                </div>
                
                <h2 class="text-xl font-bold text-slate-800">{{ $staff->name }}</h2>
                <span class="mt-2 inline-block rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700 uppercase tracking-wide">
                    {{ ucfirst($staff->role) }}
                </span>
            </div>

            <div class="w-full md:w-3/4">
                <h4 class="text-sm font-bold text-blue-600 uppercase tracking-wider mb-6 border-b border-gray-100 pb-2">Contact Information</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-8 gap-x-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Email Address</label>
                        <p class="text-slate-800 font-medium text-base">{{ $staff->email }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Mobile Number</label>
                        <p class="text-slate-800 font-medium text-base">{{ $staff->mobile }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Account Status</label>
                        @if($staff->status)
                            <span class="text-green-600 font-bold text-sm flex items-center"><i class="fas fa-check-circle mr-2"></i> Active</span>
                        @else
                            <span class="text-red-500 font-bold text-sm flex items-center"><i class="fas fa-times-circle mr-2"></i> Inactive</span>
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Joined On</label>
                        <p class="text-slate-800 text-sm font-medium">{{ $staff->created_at->format('d M, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection