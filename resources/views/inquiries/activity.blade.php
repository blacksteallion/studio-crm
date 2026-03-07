@extends('layouts.app')
@section('header', 'Activity Hub')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar { border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    .timeline-item { position: relative; padding-left: 2.5rem; padding-bottom: 2.5rem; }
    .timeline-item:last-child { padding-bottom: 0; }
    /* Match input style from Booking form */
    .date-input { background-color: #ffffff !important; cursor: pointer !important; }
</style>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <div class="lg:col-span-1 space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-6">
            <h3 class="font-bold text-slate-800 text-xl mb-1">{{ $inquiry->customer->name }}</h3>
            <p class="text-sm text-gray-500 mb-6">{{ $inquiry->business_name }}</p>
            
            <div class="flex flex-col gap-3">
                <div class="flex justify-between items-center bg-gray-50 p-4 rounded-xl">
                    <span class="text-sm font-bold text-gray-500">Status</span>
                    <span class="font-bold text-blue-600">{{ $inquiry->status }}</span>
                </div>
                <div class="flex justify-between items-center bg-gray-50 p-4 rounded-xl">
                    <span class="text-sm font-bold text-gray-500">Next Follow Up</span>
                    <span class="font-bold {{ $inquiry->follow_up_date && $inquiry->follow_up_date->isPast() ? 'text-red-500' : 'text-slate-800' }}">
                        {{ $inquiry->follow_up_date ? $inquiry->follow_up_date->format('d M, Y') : '-' }}
                    </span>
                </div>
            </div>
            
            <div class="mt-6 pt-4 border-t border-gray-100 text-center">
                <a href="{{ route('inquiries.show', $inquiry->id) }}" class="text-blue-600 font-bold text-sm hover:underline">View Full Details</a>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-6">
        
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 py-4 px-6">
                <h4 class="font-bold text-slate-800">Log New Activity</h4>
            </div>
            <div class="p-6">
                <form id="logForm" action="{{ route('inquiries.log', $inquiry->id) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <div class="md:col-span-1">
                            <label class="mb-2 block text-slate-700 font-bold text-xs uppercase">Type</label>
                            <div class="relative">
                                <select name="type" class="w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 py-2.5 px-3 text-sm outline-none transition focus:border-blue-500 focus:bg-white">
                                    <option value="Call">Call</option>
                                    <option value="Meeting">Meeting</option>
                                    <option value="Email">Email</option>
                                    <option value="Note">Note</option>
                                </select>
                                <span class="absolute right-3 top-3 text-gray-400 text-xs pointer-events-none"><i class="fas fa-chevron-down"></i></span>
                            </div>
                        </div>
                        <div class="md:col-span-3">
                            <label class="mb-2 block text-slate-700 font-bold text-xs uppercase">Comments</label>
                            <input type="text" name="message" class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 px-4 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white" placeholder="What happened?" required>
                        </div>
                    </div>
                    
                    <div class="flex flex-col md:flex-row gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100 items-end">
                        <div class="flex-1 w-full">
                            <label class="mb-1 block text-gray-400 font-bold text-xs uppercase">Update Status (Opt)</label>
                            <select name="update_status" class="w-full rounded-lg border border-gray-200 bg-white py-2 px-3 text-sm outline-none">
                                <option value="">Keep: {{ $inquiry->status }}</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Qualified">Qualified</option>
                                <option value="Slot Reserved">Slot Reserved</option>
                                <option value="Lost">Lost</option>
                            </select>
                        </div>
                        <div class="flex-1 w-full">
                            <label class="mb-1 block text-gray-400 font-bold text-xs uppercase">Next Follow Up (Opt)</label>
                            <input type="text" name="next_follow_up" class="date-input w-full rounded-lg border border-gray-200 bg-white py-2 px-3 text-sm outline-none" placeholder="Select Date">
                        </div>
                        <div class="w-full md:w-auto">
                            <input type="hidden" name="log_date" id="client_log_date">
                            <input type="hidden" name="log_time" id="client_log_time">
                            
                            <button type="submit" class="w-full md:w-auto rounded-lg bg-purple-600 py-2 px-6 font-bold text-white hover:bg-purple-700 transition">Post</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-6">
            <h4 class="font-bold text-slate-800 mb-6 text-lg">Activity History</h4>
            
            <div class="relative">
                @forelse($inquiry->logs as $log)
                <div class="timeline-item" x-data="{ editing: false }">
                    <div class="absolute left-[11px] top-8 bottom-0 w-[2px] bg-gray-100 z-0"></div>
                    <div class="absolute left-0 top-1 h-6 w-6 rounded-full border-2 border-white flex items-center justify-center z-10 shadow-sm
                        {{ $log->type == 'Call' ? 'bg-green-500 text-white' : 
                          ($log->type == 'Meeting' ? 'bg-purple-500 text-white' : 'bg-gray-400 text-white') }}">
                        <i class="fas {{ $log->type == 'Call' ? 'fa-phone' : ($log->type == 'Meeting' ? 'fa-users' : 'fa-info') }} text-[10px]"></i>
                    </div>

                    <div class="ml-2 group">
                        <div x-show="!editing" class="bg-gray-50 border border-gray-100 rounded-xl p-4 hover:bg-gray-100 transition">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <span class="font-bold text-slate-800 text-sm block">{{ $log->type }}</span>
                                    <span class="text-xs text-gray-500">by {{ $log->user->name ?? 'System' }}</span>
                                </div>
                                <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="editing = true" class="text-blue-600 hover:text-blue-800"><i class="fas fa-pencil-alt"></i></button>
                                    <form action="{{ route('inquiries.logs.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Delete log?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>
                            </div>
                            
                            <p class="text-sm text-slate-700 mb-3">{{ $log->message }}</p>
                            
                            <div class="flex flex-wrap gap-4 text-xs text-gray-400 border-t border-gray-200 pt-2 font-medium">
                                <span><i class="far fa-calendar mr-1"></i> {{ $log->log_date->format('d M, Y') }}</span>
                                <span><i class="far fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($log->log_time)->format('h:i A') }}</span>
                                <span><i class="fas fa-plus-circle mr-1"></i> Added: {{ $log->created_at->format('d M H:i') }}</span>
                            </div>
                        </div>

                        <div x-show="editing" class="bg-white border border-blue-200 rounded-xl p-4 shadow-sm">
                            <form action="{{ route('inquiries.logs.update', $log->id) }}" method="POST">
                                @csrf @method('PUT')
                                <select name="type" class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 px-3 text-sm mb-2 outline-none">
                                    @foreach(['Call','Meeting','Email','Note'] as $t)
                                        <option value="{{ $t }}" {{ $log->type == $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                                <textarea name="message" class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 px-3 text-sm outline-none" rows="2">{{ $log->message }}</textarea>
                                <div class="flex justify-end gap-2 mt-2">
                                    <button type="button" @click="editing = false" class="text-xs text-gray-500 hover:text-black font-bold">Cancel</button>
                                    <button type="submit" class="text-xs bg-blue-600 text-white px-4 py-1.5 rounded-lg font-bold">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-gray-400 text-sm italic">No activity recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="//unpkg.com/alpinejs" defer></script> 
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Flatpickr for "Next Follow Up"
        flatpickr(".date-input", { 
            dateFormat: "Y-m-d", 
            minDate: "today", 
            disableMobile: "true" 
        });

        // Set Client Device Time on Form Submit
        const logForm = document.getElementById('logForm');
        if(logForm){
            logForm.addEventListener('submit', function() {
                const now = new Date();
                
                // Format Date: YYYY-MM-DD
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const dateString = `${year}-${month}-${day}`;
                
                // Format Time: HH:MM
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const timeString = `${hours}:${minutes}`;

                document.getElementById('client_log_date').value = dateString;
                document.getElementById('client_log_time').value = timeString;
            });
        }
    });
</script>
@endsection