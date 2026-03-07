@extends('layouts.app')
@section('header', 'Customer Profile')

@section('content')
<div class="max-w-6xl mx-auto mb-10">
    
    <div class="flex justify-between items-center mb-6">
        <a href="{{ route('customers.index') }}" class="flex items-center text-gray-500 hover:text-gray-700 font-bold transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>
        
        @can('edit customers')
        <a href="{{ route('customers.edit', $customer->id) }}" class="h-10 inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 text-sm font-bold text-white hover:bg-blue-700 transition shadow-sm hover:shadow-md">
            <i class="fas fa-pencil-alt mr-2"></i> Edit Profile
        </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm h-fit">
            <div class="flex flex-col items-center text-center">
                <div class="w-24 h-24 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-3xl border-4 border-white shadow-md mb-4">
                    {{ substr($customer->name, 0, 1) }}
                </div>
                <h2 class="text-xl font-bold text-slate-800">{{ $customer->name }}</h2>
                @if($customer->business_name)
                    <p class="text-sm font-medium text-slate-500 mt-1">{{ $customer->business_name }}</p>
                @endif
                <div class="mt-4 flex gap-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold 
                        {{ $customer->status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $customer->status ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
            
            <div class="mt-8 space-y-4 border-t border-gray-100 pt-6">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase">Mobile</label>
                    <p class="text-slate-800 font-medium font-mono">{{ $customer->mobile }}</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase">Email</label>
                    <p class="text-slate-800 font-medium">{{ $customer->email ?? '-' }}</p>
                </div>
                
                @if($customer->website)
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase">Website</label>
                    <a href="{{ $customer->website }}" target="_blank" class="text-blue-600 font-medium hover:underline truncate block">
                        {{ $customer->website }}
                    </a>
                </div>
                @endif

                @if($customer->gst_number)
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase">GST Number</label>
                    <p class="text-slate-800 font-medium font-mono uppercase">{{ $customer->gst_number }}</p>
                </div>
                @endif

                @if($customer->address_line1 || $customer->city)
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase">Billing Address</label>
                    <p class="text-slate-700 text-sm font-medium mt-1 leading-relaxed">
                        {{ $customer->address_line1 }}<br>
                        @if($customer->address_line2) {{ $customer->address_line2 }}<br> @endif
                        {{ $customer->city }}@if($customer->pincode) - {{ $customer->pincode }}@endif<br>
                        {{ $customer->state }}, {{ $customer->country }}
                    </p>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase">Registered On</label>
                    <p class="text-slate-800 font-medium">{{ $customer->created_at->format('d M, Y') }}</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase">Remarks</label>
                    <p class="text-sm text-gray-500 leading-relaxed bg-gray-50 p-3 rounded-lg mt-1 border border-gray-100">
                        {{ $customer->remarks ?? 'No remarks added.' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            
            @php
                // Calculate Payments via Orders relationship
                $payments = $customer->orders->flatMap->payments;
                $totalPaid = $payments->sum('amount');
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                
                <a href="{{ route('inquiries.index', ['customer_id' => $customer->id]) }}" class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm text-center block hover:border-blue-300 hover:shadow-md transition">
                    <span class="block text-2xl font-bold text-blue-600">{{ $customer->inquiries->count() }}</span>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Inquiries</span>
                </a>

                <a href="{{ route('bookings.index', ['customer_id' => $customer->id]) }}" class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm text-center block hover:border-green-300 hover:shadow-md transition">
                    <span class="block text-2xl font-bold text-green-600">{{ $customer->bookings->count() }}</span>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Bookings</span>
                </a>

                <a href="{{ route('orders.index', ['customer_id' => $customer->id]) }}" class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm text-center block hover:border-purple-300 hover:shadow-md transition">
                    <span class="block text-2xl font-bold text-purple-600">{{ $customer->orders->count() }}</span>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Orders</span>
                </a>

                <a href="{{ route('payments.index', ['customer_id' => $customer->id]) }}" class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm text-center block hover:border-emerald-300 hover:shadow-md transition">
                    <span class="block text-2xl font-bold text-emerald-600">{{ $payments->count() }}</span>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Payments</span>
                </a>

            </div>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="font-bold text-slate-800">Recent Activity</h3>
                </div>
                
                <div class="divide-y divide-gray-100">
                    
                    {{-- 1. INQUIRIES --}}
                    @foreach($customer->inquiries->take(3) as $inquiry)
                    <div class="p-4 hover:bg-gray-50 flex justify-between items-center transition">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-headset text-sm"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100">INQUIRY</span>
                                    <span class="text-xs text-gray-400">{{ $inquiry->created_at->format('d M, Y') }}</span>
                                </div>
                                <div class="font-medium text-slate-700 text-sm mt-0.5">
                                    Status: {{ $inquiry->status }}
                                </div>
                            </div>
                        </div>
                        @can('view inquiries')
                            <a href="{{ route('inquiries.show', $inquiry->id) }}" class="text-gray-400 hover:text-blue-600 px-2"><i class="fas fa-chevron-right"></i></a>
                        @endcan
                    </div>
                    @endforeach

                    {{-- 2. BOOKINGS --}}
                    @foreach($customer->bookings->take(3) as $booking)
                    <div class="p-4 hover:bg-gray-50 flex justify-between items-center transition">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-calendar-check text-sm"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-green-600 bg-green-50 px-1.5 py-0.5 rounded border border-green-100">BOOKING</span>
                                    <span class="text-xs text-gray-400">{{ $booking->created_at->format('d M, Y') }}</span>
                                </div>
                                <div class="font-medium text-slate-700 text-sm mt-0.5">
                                    Scheduled for {{ $booking->booking_date->format('d M') }} ({{ $booking->start_time->format('H:i') }})
                                </div>
                            </div>
                        </div>
                        @can('view bookings')
                            <a href="{{ route('bookings.show', $booking->id) }}" class="text-gray-400 hover:text-blue-600 px-2"><i class="fas fa-chevron-right"></i></a>
                        @endcan
                    </div>
                    @endforeach

                    {{-- 3. ORDERS (INVOICES) --}}
                    @foreach($customer->orders->take(3) as $order)
                    <div class="p-4 hover:bg-gray-50 flex justify-between items-center transition">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-file-invoice text-sm"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-purple-600 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-100">INVOICE</span>
                                    <span class="text-xs text-gray-400">{{ $order->invoice_date->format('d M, Y') }}</span>
                                </div>
                                <div class="font-medium text-slate-700 text-sm mt-0.5">
                                    #{{ $order->invoice_number }} — <span class="font-bold">₹{{ number_format($order->total_amount) }}</span>
                                </div>
                            </div>
                        </div>
                        @can('view orders')
                            <a href="{{ route('orders.show', $order->id) }}" class="text-gray-400 hover:text-blue-600 px-2"><i class="fas fa-chevron-right"></i></a>
                        @endcan
                    </div>
                    @endforeach

                    {{-- 4. PAYMENTS --}}
                    @foreach($payments->take(5) as $payment)
                    <div class="p-4 hover:bg-gray-50 flex justify-between items-center transition">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-rupee-sign text-sm"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">PAYMENT</span>
                                    <span class="text-xs text-gray-400">{{ $payment->transaction_date->format('d M, Y') }}</span>
                                </div>
                                <div class="font-medium text-slate-700 text-sm mt-0.5">
                                    Received <span class="font-bold text-emerald-600">₹{{ number_format($payment->amount) }}</span> via {{ $payment->payment_method }}
                                </div>
                            </div>
                        </div>
                        @can('view orders')
                            <a href="{{ route('orders.show', $payment->order_id) }}" class="text-gray-400 hover:text-blue-600 px-2"><i class="fas fa-chevron-right"></i></a>
                        @endcan
                    </div>
                    @endforeach

                    @if($customer->inquiries->isEmpty() && $customer->bookings->isEmpty() && $customer->orders->isEmpty())
                        <div class="p-8 text-center text-gray-400 text-sm">
                            No activity found for this customer.
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</div>
@endsection