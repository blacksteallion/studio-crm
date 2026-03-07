@extends('layouts.app')
@section('header', 'Invoice Details')

@section('content')
<div class="max-w-4xl mx-auto mb-10" x-data="{ paymentModalOpen: false }">
    
    <div class="flex justify-between items-center mb-6 print:hidden">
        <a href="{{ route('orders.index') }}" class="flex items-center text-gray-500 hover:text-gray-700 font-bold transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>
        <div class="flex gap-3">
            @can('download order pdf')
            <a href="{{ route('orders.pdf', $order->id) }}" class="h-10 inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 text-sm font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-file-pdf mr-2"></i> Download PDF
            </a>
            @endcan
            
            @can('delete orders')
            <form action="{{ route('orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('Delete this invoice?');">
                @csrf @method('DELETE')
                <button type="submit" class="h-10 inline-flex items-center justify-center rounded-xl bg-red-50 text-red-600 px-5 text-sm font-bold hover:bg-red-100 transition shadow-sm">
                    <i class="fas fa-trash-alt mr-2"></i> Delete
                </button>
            </form>
            @endcan
        </div>
    </div>

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl">
            <strong>Error:</strong> {{ session('error') }}
        </div>
    @endif

    <div class="bg-white border border-gray-200 shadow-lg rounded-2xl overflow-hidden print:shadow-none print:border-none mb-6">
        
        <div class="p-10 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start gap-6">
            <div>
                <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Invoice</h1>
                <p class="text-gray-500 font-mono text-sm mt-1">{{ $order->invoice_number }}</p>
                
                <div class="mt-3 flex items-center gap-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wide
                    {{ $order->status == 'Paid' ? 'bg-green-100 text-green-700' : 
                       ($order->status == 'Partially Paid' ? 'bg-blue-100 text-blue-700' : 
                       ($order->status == 'Unpaid' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700')) }}">
                        {{ $order->status }}
                    </span>
                </div>
            </div>

            <div class="md:text-right space-y-1 text-sm">
                <div class="flex justify-between md:justify-end gap-8">
                    <span class="text-gray-500 w-24">Invoice Date:</span>
                    <span class="font-bold text-slate-800">{{ $order->invoice_date->format('d M, Y') }}</span>
                </div>
                @if($order->due_date)
                <div class="flex justify-between md:justify-end gap-8">
                    <span class="text-gray-500 w-24">Due Date:</span>
                    <span class="font-bold text-slate-800">{{ $order->due_date->format('d M, Y') }}</span>
                </div>
                @endif
                @if($order->booking_id)
                <div class="flex justify-between md:justify-end gap-8">
                    <span class="text-gray-500 w-24">Reference:</span>
                    <a href="{{ route('bookings.show', $order->booking_id) }}" class="font-bold text-blue-600 hover:underline">
                        Booking #BKG-{{ $order->booking_id }}
                    </a>
                </div>
                @endif
                
                <div class="flex justify-between md:justify-end gap-8">
                    <span class="text-gray-500 w-24">Location:</span>
                    <span class="font-bold text-slate-800">{{ $order->location->name ?? 'Unassigned' }}</span>
                </div>
            </div>
        </div>

        <div class="p-10 grid grid-cols-1 md:grid-cols-2 gap-12 border-b border-gray-50">
            
            {{-- BILL FROM (STUDIO) --}}
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Bill From</h3>
                <h4 class="text-xl font-bold text-slate-800 mb-2">{{ $settings['company_name'] ?? 'TC Studio' }}</h4>
                <div class="text-sm text-gray-500 leading-relaxed">
                    @if(!empty($settings['company_address']))
                        {!! nl2br(e($settings['company_address'])) !!}<br>
                    @else
                        101 Tech Park, SG Highway<br>Ahmedabad, Gujarat 380054<br>
                    @endif
                    
                    {{ $settings['company_email'] ?? 'contact@techcelerity.in' }}<br>
                    
                    @if(!empty($settings['company_phone']))
                        {{ $settings['company_phone'] }}
                    @endif
                </div>
            </div>

            {{-- BILL TO (CUSTOMER) --}}
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Bill To</h3>
                
                {{-- Business Name --}}
                <h4 class="text-xl font-bold text-slate-800 mb-2">
                    {{ $order->customer->business_name ?: $order->customer->name }}
                </h4>

                <div class="text-sm text-gray-500 leading-relaxed">
                    {{-- Address Line 1 --}}
                    @if(!empty($order->customer->address_1))
                        {{ $order->customer->address_1 }}<br>
                    @endif

                    {{-- Address Line 2, City, Zip --}}
                    @if(!empty($order->customer->address_2) || !empty($order->customer->city) || !empty($order->customer->zip))
                        {{ !empty($order->customer->address_2) ? $order->customer->address_2 . ', ' : '' }}
                        {{ $order->customer->city }} {{ $order->customer->zip }}<br>
                    @endif

                    {{-- Email --}}
                    @if(!empty($order->customer->email))
                        {{ $order->customer->email }}<br>
                    @endif

                    {{-- Mobile --}}
                    @if(!empty($order->customer->mobile))
                        {{ $order->customer->mobile }}<br>
                    @endif

                    {{-- GST --}}
                    @if(!empty($order->customer->gst_number))
                        <span class="font-medium text-slate-600">GST: {{ $order->customer->gst_number }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="px-10 pb-10 pt-8">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-gray-100">
                        <th class="py-3 text-xs font-bold text-gray-400 uppercase tracking-wider w-1/2">Description</th>
                        <th class="py-3 text-xs font-bold text-gray-400 uppercase tracking-wider w-20 text-center">Qty</th>
                        <th class="py-3 text-xs font-bold text-gray-400 uppercase tracking-wider w-32 text-right">Price</th>
                        <th class="py-3 text-xs font-bold text-gray-400 uppercase tracking-wider w-32 text-right">GST</th>
                        <th class="py-3 text-xs font-bold text-gray-400 uppercase tracking-wider w-32 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($order->items as $item)
                    <tr>
                        <td class="py-4 text-sm font-medium text-slate-700">{{ $item->item_name }}</td>
                        <td class="py-4 text-sm text-gray-500 text-center">{{ (float)$item->quantity }}</td>
                        <td class="py-4 text-sm text-gray-500 text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-4 text-sm text-gray-600 text-right">
                            ₹{{ number_format($item->gst_amount, 2) }}
                            <span class="block text-[10px] text-gray-400">({{ $item->gst_rate }}%)</span>
                        </td>
                        <td class="py-4 text-sm font-bold text-slate-800 text-right">₹{{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-10 pb-10 flex flex-col md:flex-row justify-between items-start gap-12 border-t border-gray-100 pt-8">
            <div class="w-full md:w-1/2">
                @if($order->notes)
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Notes / Terms</h3>
                <p class="text-sm text-gray-500 bg-gray-50 p-4 rounded-xl border border-gray-100 leading-relaxed">{{ $order->notes }}</p>
                @endif
            </div>

            <div class="w-full md:w-5/12 space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="font-medium text-gray-500">Subtotal</span>
                    <span class="font-bold text-slate-700">₹{{ number_format($order->subtotal, 2) }}</span>
                </div>
                
                @if($order->discount > 0)
                <div class="flex justify-between text-sm">
                    <span class="font-medium text-gray-500">Discount</span>
                    <span class="font-bold text-green-600">- ₹{{ number_format($order->discount, 2) }}</span>
                </div>
                @endif

                @if($order->tax > 0)
                <div class="flex justify-between text-sm">
                    <span class="font-medium text-gray-500">Total Tax</span>
                    <span class="font-bold text-red-500">+ ₹{{ number_format($order->tax, 2) }}</span>
                </div>
                @endif

                <div class="border-t border-gray-100 pt-3 flex justify-between text-lg">
                    <span class="font-bold text-slate-800">Total</span>
                    <span class="font-black text-blue-600">₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
                
                <div class="flex justify-between text-sm pt-2 {{ $order->balance_due > 0 ? 'text-red-600' : 'text-green-600' }}">
                    <span class="font-bold">Balance Due</span>
                    <span class="font-bold">₹{{ number_format($order->balance_due, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- PAYMENT HISTORY AND MODAL --}}
    <div class="bg-gray-50 border border-gray-200 shadow-sm rounded-2xl overflow-hidden print:hidden">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 text-lg">Payment History</h3>
            
            @can('add payments')
            @if($order->balance_due > 0)
            <button @click="paymentModalOpen = true" class="h-9 inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 text-xs font-bold text-white hover:bg-blue-700 transition shadow-sm">
                <i class="fas fa-plus mr-2"></i> Record Payment
            </button>
            @endif
            @endcan
        </div>
        
        <div class="p-0">
            @if($order->payments->isEmpty())
                <div class="p-8 text-center text-gray-400 text-sm">
                    No payments recorded yet.
                </div>
            @else
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-100 text-gray-500 uppercase text-xs font-bold">
                        <tr>
                            <th class="py-3 px-6">Date</th>
                            <th class="py-3 px-6">Method</th>
                            <th class="py-3 px-6">Reference</th>
                            <th class="py-3 px-6 text-right">Amount</th>
                            <th class="py-3 px-6 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($order->payments as $payment)
                        <tr>
                            <td class="py-3 px-6 font-medium text-slate-700">{{ $payment->transaction_date->format('d M, Y') }}</td>
                            <td class="py-3 px-6">{{ $payment->payment_method }}</td>
                            <td class="py-3 px-6 text-gray-500 font-mono text-xs">{{ $payment->reference_number ?? '-' }}</td>
                            <td class="py-3 px-6 text-right font-bold text-green-600">₹{{ number_format($payment->amount, 2) }}</td>
                            <td class="py-3 px-6 text-right">
                                @can('delete payments')
                                <form action="{{ route('payments.destroy', $payment->id) }}" method="POST" onsubmit="return confirm('Delete this payment entry?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-trash-alt"></i></button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div x-show="paymentModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.away="paymentModalOpen = false">
            <h3 class="font-bold text-slate-800 text-lg mb-4">Record Payment</h3>
            
            <form action="{{ route('payments.store', $order->id) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Date</label>
                        <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 px-3 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Amount (₹)</label>
                        <input type="number" name="amount" value="{{ $order->balance_due }}" max="{{ $order->balance_due }}" step="0.01" class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 px-3 text-sm outline-none font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 px-3 text-sm outline-none">
                            <option value="Cash">Cash</option>
                            <option value="UPI">UPI / GPay / PhonePe</option>
                            <option value="Bank Transfer">Bank Transfer (NEFT/IMPS)</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Reference No. (Optional)</label>
                        <input type="text" name="reference_number" placeholder="Transaction ID / Cheque No." class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 px-3 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Notes (Optional)</label>
                        <textarea name="notes" rows="2" class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 px-3 text-sm outline-none"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="paymentModalOpen = false" class="px-4 py-2 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-100 transition">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-green-600 hover:bg-green-700 shadow-md transition">Save Payment</button>
                </div>
            </form>
        </div>
    </div>

</div>

<style>
    @media print {
        @page { margin: 0; size: auto; }
        body { background-color: white; -webkit-print-color-adjust: exact; }
        nav, aside, header, .print\:hidden { display: none !important; }
        .max-w-4xl { max-width: 100% !important; margin: 0 !important; }
        .shadow-lg { box-shadow: none !important; }
        .border { border: none !important; }
        .bg-gray-50 { background-color: #f9fafb !important; }
    }
</style>
@endsection