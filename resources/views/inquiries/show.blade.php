@extends('layouts.app')
@section('header', 'Inquiry Details')

@section('content')
<div class="max-w-5xl mx-auto mb-10">
    <div class="flex justify-between items-center mb-6">
        <a href="{{ route('inquiries.index') }}" class="flex items-center text-gray-500 hover:text-gray-700 font-bold transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>
        <div class="flex gap-3">
            @can('manage inquiry logs')
            <a href="{{ route('inquiries.activity', $inquiry->id) }}" class="h-10 inline-flex items-center justify-center rounded-xl bg-purple-600 px-5 text-sm font-bold text-white hover:bg-purple-700 transition shadow-sm hover:shadow-md">
                <i class="fas fa-history mr-2"></i> Activity Hub
            </a>
            @endcan
            
            @can('edit inquiries')
            <a href="{{ route('inquiries.edit', $inquiry->id) }}" class="h-10 inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 text-sm font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-pencil-alt mr-2"></i> Edit
            </a>
            @endcan
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm mb-6 flex justify-between items-center">
        <div>
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Studio Location</span>
            <div class="mt-1 font-bold text-slate-800">
                <i class="fas fa-map-marker-alt text-red-500 mr-1"></i> {{ $inquiry->location->name ?? 'Unassigned' }}
            </div>
        </div>
        <div>
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Current Status</span>
            <div class="mt-1">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold 
                {{ $inquiry->status == 'New' ? 'bg-blue-100 text-blue-700' : 
                   ($inquiry->status == 'In Progress' ? 'bg-yellow-100 text-yellow-700' : 
                   ($inquiry->status == 'Qualified' ? 'bg-purple-100 text-purple-700' : 
                   ($inquiry->status == 'Slot Reserved' ? 'bg-green-100 text-green-700' : 
                   ($inquiry->status == 'Lost' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')))) }}">
                    {{ $inquiry->status }}
                </span>
            </div>
        </div>
        <div>
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Assigned Staff</span>
            <div class="flex items-center gap-2 mt-1">
                @if($inquiry->assignedStaff)
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">
                        {{ substr($inquiry->assignedStaff->name, 0, 1) }}
                    </div>
                    <span class="font-bold text-slate-800">{{ $inquiry->assignedStaff->name }}</span>
                @else
                    <span class="text-gray-400 italic">Unassigned</span>
                @endif
            </div>
        </div>
        <div class="text-right">
             <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Next Follow Up</span>
             <div class="mt-1 font-bold {{ $inquiry->follow_up_date && $inquiry->follow_up_date->isPast() ? 'text-red-500' : 'text-slate-800' }}">
                 {{ $inquiry->follow_up_date ? $inquiry->follow_up_date->format('d M, Y') : 'Not Scheduled' }}
             </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <div class="md:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h4 class="text-sm font-bold text-slate-800 mb-4 border-b border-gray-100 pb-2">Customer Info</h4>
                <div class="space-y-4">
                    <div>
                        <span class="block text-xs text-gray-400 font-bold uppercase">Name</span>
                        <span class="block text-slate-800 font-medium">{{ $inquiry->customer->name }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 font-bold uppercase">Business</span>
                        <span class="block text-slate-800 font-medium">{{ $inquiry->business_name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 font-bold uppercase">Mobile</span>
                        <span class="block text-slate-800 font-medium">{{ $inquiry->customer->mobile }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 font-bold uppercase">Email</span>
                        <span class="block text-slate-800 font-medium break-all">{{ $inquiry->customer->email ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 font-bold uppercase">Source</span>
                        <span class="inline-block mt-1 text-xs font-bold bg-gray-100 text-gray-600 px-2 py-1 rounded">
                            {{ $inquiry->leadSource->name ?? 'Direct' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="md:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h4 class="text-sm font-bold text-slate-800 mb-4 border-b border-gray-100 pb-2">Requirement Details</h4>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <span class="block text-xs text-gray-400 font-bold uppercase">Primary Date</span>
                        <span class="block text-blue-600 font-bold text-lg">{{ $inquiry->primary_date->format('d M, Y') }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 font-bold uppercase">Alternate Date</span>
                        <span class="block text-slate-800 font-medium">{{ $inquiry->alternate_date ? $inquiry->alternate_date->format('d M, Y') : '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 font-bold uppercase">Time Slot</span>
                        <span class="block text-slate-800 font-medium">
                            {{ \Carbon\Carbon::parse($inquiry->from_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($inquiry->to_time)->format('h:i A') }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 font-bold uppercase">Total Hours</span>
                        <span class="block text-slate-800 font-medium">{{ abs($inquiry->total_hours) }} Hrs</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 font-bold uppercase">Customer Budget</span>
                        <span class="block text-green-600 font-bold">₹{{ $inquiry->budget ?? '0' }}</span>
                    </div>
                </div>

                @if($inquiry->items->isNotEmpty())
                    <div class="mt-8">
                        <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-3">Interested Services / Products</h4>
                        <div class="overflow-x-auto rounded-lg border border-gray-100">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-xs font-bold text-gray-500 uppercase">Item</th>
                                        <th class="px-4 py-2 text-xs font-bold text-gray-500 uppercase text-center">Qty/Hrs</th>
                                        <th class="px-4 py-2 text-xs font-bold text-gray-500 uppercase text-right">Price</th>
                                        <th class="px-4 py-2 text-xs font-bold text-gray-500 uppercase text-right">GST (₹)</th>
                                        <th class="px-4 py-2 text-xs font-bold text-gray-500 uppercase text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($inquiry->items as $item)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-slate-800 font-medium">{{ $item->item_name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-600 text-center">{{ $item->quantity }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-600 text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500 text-right">
                                            ₹{{ number_format($item->gst_amount, 2) }}
                                            <span class="text-[10px] text-gray-400 block">({{ $item->gst_rate }}%)</span>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-slate-800 font-bold text-right">₹{{ number_format($item->total, 2) }}</td>
                                    </tr>
                                    @endforeach
                                    <tr class="bg-blue-50">
                                        <td colspan="4" class="px-4 py-3 text-sm font-bold text-blue-800 text-right uppercase">Total Estimate</td>
                                        <td class="px-4 py-3 text-sm font-black text-blue-600 text-right">₹{{ number_format($inquiry->items->sum('total'), 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <div class="bg-gray-50 rounded-2xl border border-gray-200 p-6 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-sm font-bold text-slate-800">Latest Activity</h4>
                    @can('manage inquiry logs')
                    <a href="{{ route('inquiries.activity', $inquiry->id) }}" class="text-xs font-bold text-blue-600 hover:underline">View All</a>
                    @endcan
                </div>
                
                <div class="space-y-4">
                    @forelse($inquiry->logs->take(3) as $log)
                    <div class="flex gap-3">
                        <div class="mt-1">
                            @if($log->type == 'Call') <i class="fas fa-phone text-blue-400"></i>
                            @elseif($log->type == 'Meeting') <i class="fas fa-handshake text-purple-400"></i>
                            @elseif($log->type == 'Note') <i class="fas fa-sticky-note text-yellow-400"></i>
                            @elseif($log->type == 'Status Change') <i class="fas fa-exchange-alt text-gray-400"></i>
                            @else <i class="fas fa-info-circle text-gray-400"></i> @endif
                        </div>
                        <div>
                            <p class="text-sm text-slate-700">{{ $log->message }}</p>
                            <span class="text-xs text-gray-400">{{ $log->created_at->format('d M, h:i A') }} • {{ $log->user->name ?? 'System' }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 italic">No activity recorded yet.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>
@endsection