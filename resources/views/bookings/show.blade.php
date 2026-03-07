@extends('layouts.app')
@section('header', 'Booking Details')

@section('content')
<div class="max-w-5xl mx-auto mb-10">
    <div class="flex justify-between items-center mb-6">
        <a href="{{ route('bookings.index') }}" class="flex items-center text-gray-500 hover:text-gray-700 font-bold transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>
        <div class="flex gap-3">
            @can('create orders')
            <a href="{{ route('orders.create', ['booking_id' => $booking->id]) }}" class="h-10 inline-flex items-center justify-center rounded-xl bg-green-600 px-5 text-sm font-bold text-white hover:bg-green-700 transition shadow-sm hover:shadow-md">
                <i class="fas fa-file-invoice-dollar mr-2"></i> Create Invoice
            </a>
            @endcan

            @can('edit bookings')
            <a href="{{ route('bookings.edit', $booking->id) }}" class="h-10 inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 text-sm font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-pencil-alt mr-2"></i> Edit
            </a>
            @endcan
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm mb-6 grid grid-cols-2 md:grid-cols-4 gap-4 items-center">
        <div>
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Booking ID</span>
            <div class="mt-1 text-xl font-black text-blue-600">BKG-{{ $booking->id }}</div>
        </div>
        <div>
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Studio Location</span>
            <div class="mt-1 font-bold text-slate-800">
                <i class="fas fa-map-marker-alt text-red-500 mr-1"></i> {{ $booking->location->name ?? 'Unassigned' }}
            </div>
        </div>
        <div>
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Status</span>
            <div class="mt-1">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold 
                {{ $booking->status == 'Scheduled' ? 'bg-blue-100 text-blue-700' : 
                   ($booking->status == 'Completed' ? 'bg-green-100 text-green-700' : 
                   ($booking->status == 'Cancelled' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700')) }}">
                    {{ $booking->status }}
                </span>
            </div>
        </div>
        <div class="md:text-right">
             <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Date</span>
             <div class="mt-1 font-bold text-slate-800">
                 {{ $booking->booking_date->format('d M, Y') }}
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
                        <span class="block text-slate-800 font-medium">{{ $booking->customer->name }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 font-bold uppercase">Business</span>
                        <span class="block text-slate-800 font-medium">{{ $booking->customer->business_name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 font-bold uppercase">Mobile</span>
                        <span class="block text-slate-800 font-medium">{{ $booking->customer->mobile }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 font-bold uppercase">Source</span>
                        <span class="inline-block mt-1 text-xs font-bold bg-gray-100 text-gray-600 px-2 py-1 rounded">
                            {{ $booking->inquiry->leadSource->name ?? 'Direct' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="md:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h4 class="text-sm font-bold text-slate-800 mb-4 border-b border-gray-100 pb-2">Booking Details</h4>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <span class="block text-xs text-gray-400 font-bold uppercase">Time Slot</span>
                        <span class="block text-slate-800 font-medium">
                            {{ $booking->start_time->format('h:i A') }} - {{ $booking->end_time->format('h:i A') }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 font-bold uppercase">Assigned Staff</span>
                        @if($booking->assignedStaff)
                            <div class="flex items-center gap-2 mt-1">
                                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">
                                    {{ substr($booking->assignedStaff->name, 0, 1) }}
                                </div>
                                <span class="font-medium text-slate-800">{{ $booking->assignedStaff->name }}</span>
                            </div>
                        @else
                            <span class="block text-slate-800 font-medium mt-1">Unassigned</span>
                        @endif
                    </div>
                    <div class="col-span-2">
                        <span class="block text-xs text-gray-400 font-bold uppercase">Notes</span>
                        <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg mt-1 border border-gray-100">
                            {{ $booking->notes ?? 'No notes provided.' }}
                        </p>
                    </div>
                </div>

                @if($booking->items->isNotEmpty())
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
                                    @foreach($booking->items as $item)
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
                                        <td class="px-4 py-3 text-sm font-black text-blue-600 text-right">₹{{ number_format($booking->items->sum('total'), 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            @if($booking->orders->isNotEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h4 class="text-sm font-bold text-slate-800 mb-4 border-b border-gray-100 pb-2">Linked Invoices</h4>
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-gray-400 uppercase text-xs">
                            <th class="pb-2">Invoice #</th>
                            <th class="pb-2 text-right">Amount</th>
                            <th class="pb-2 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($booking->orders as $order)
                        <tr>
                            <td class="py-2">
                                <a href="{{ route('orders.show', $order->id) }}" class="text-blue-600 font-bold hover:underline">
                                    {{ $order->invoice_number }}
                                </a>
                            </td>
                            <td class="py-2 text-right font-medium">₹{{ number_format($order->total_amount) }}</td>
                            <td class="py-2 text-right">
                                <span class="text-xs font-bold {{ $order->status == 'Paid' ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection