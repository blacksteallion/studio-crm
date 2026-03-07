@extends('layouts.app')
@section('header', 'Edit Booking')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar { border-radius: 12px; }
    .date-input, .time-input { background-color: #f9fafb !important; cursor: pointer !important; }
</style>

{{-- PREPARE DATA SAFELY --}}
@php
    $initialItems = $booking->items->map(function($item) {
        return [
            'product_service_id' => $item->product_service_id,
            'price' => (float) $item->unit_price,
            'quantity' => (float) $item->quantity,
            'gst_rate' => (float) ($item->gst_rate ?? 0),
            'pricing_model' => $item->productService->pricing_model ?? 'Fixed'
        ];
    });
@endphp

<div class="max-w-4xl mx-auto">
    
    @php
        $isLocked = in_array($booking->status, ['Cancelled', 'No Show']);
    @endphp

    @if($isLocked)
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl flex items-center gap-3">
        <i class="fas fa-lock text-red-600 text-xl"></i>
        <div>
            <h4 class="font-bold text-red-800">Booking Locked</h4>
            <p class="text-sm text-red-700">This booking is marked as <strong>{{ $booking->status }}</strong> and cannot be edited. Create a new booking if needed.</p>
        </div>
    </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 py-4 px-6">
            <h3 class="font-bold text-slate-800 text-lg">Edit Booking #{{ $booking->id }}</h3>
        </div>
        
        <form action="{{ route('bookings.update', $booking->id) }}" method="POST" class="p-6">
            @csrf @method('PUT')

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm font-bold">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="mb-2 block font-bold text-sm text-slate-700">Studio Location <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="location_id" required {{ $isLocked ? 'disabled' : '' }} class="w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white disabled:opacity-60 disabled:cursor-not-allowed @error('location_id') !border-red-500 !bg-red-50 @enderror">
                                <option value="">-- Select Location --</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('location_id', $booking->location_id) == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                                @endforeach
                            </select>
                            <span class="absolute right-4 top-3.5 text-gray-500 pointer-events-none"><i class="fas fa-chevron-down"></i></span>
                        </div>
                    </div>
                    <div>
                        <label class="mb-2 block font-bold text-sm text-slate-700">Customer</label>
                        <input type="text" value="{{ $booking->customer->name }}" disabled class="w-full rounded-xl border border-gray-200 bg-gray-100 py-3 px-5 text-gray-500 cursor-not-allowed">
                    </div>
                </div>

                @if($booking->inquiry)
                <div class="col-span-1 md:col-span-2 bg-gray-50 border border-gray-200 rounded-xl p-5">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-200 pb-2">
                        Original Inquiry Details (#{{ $booking->inquiry->id }})
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-y-4 gap-x-6 text-sm">
                        <div><span class="block text-gray-500 text-xs font-bold mb-0.5">Business Name</span><span class="font-medium text-slate-800">{{ $booking->inquiry->business_name ?? '-' }}</span></div>
                        <div><span class="block text-gray-500 text-xs font-bold mb-0.5">Mobile</span><span class="font-medium text-slate-800">{{ $booking->inquiry->customer->mobile }}</span></div>
                        <div><span class="block text-gray-500 text-xs font-bold mb-0.5">Preferred Date</span><span class="font-bold text-blue-600">{{ $booking->inquiry->primary_date->format('d M, Y') }}</span></div>
                        <div><span class="block text-gray-500 text-xs font-bold mb-0.5">Requested Time</span><span class="font-medium text-slate-800">{{ \Carbon\Carbon::parse($booking->inquiry->from_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($booking->inquiry->to_time)->format('h:i A') }}</span></div>
                    </div>
                </div>
                @endif

                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Booking Date</label>
                    <div class="relative">
                        <input type="text" name="booking_date" value="{{ $booking->booking_date->format('Y-m-d') }}" required {{ $isLocked ? 'disabled' : '' }} class="date-input w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white disabled:opacity-60 disabled:cursor-not-allowed">
                        <span class="absolute right-4 top-3.5 text-gray-400 pointer-events-none"><i class="fas fa-calendar-alt"></i></span>
                    </div>
                </div>
                
                <div class="flex gap-4">
                    <div class="w-1/2">
                        <label class="mb-2 block font-bold text-sm text-slate-700">Start Time</label>
                        <div class="relative">
                            <input type="text" name="start_time" value="{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }}" required {{ $isLocked ? 'disabled' : '' }} class="time-input w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white disabled:opacity-60 disabled:cursor-not-allowed">
                            <span class="absolute right-4 top-3.5 text-gray-400 pointer-events-none"><i class="fas fa-clock"></i></span>
                        </div>
                    </div>
                    <div class="w-1/2">
                        <label class="mb-2 block font-bold text-sm text-slate-700">End Time</label>
                        <div class="relative">
                            <input type="text" name="end_time" value="{{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}" required {{ $isLocked ? 'disabled' : '' }} class="time-input w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white disabled:opacity-60 disabled:cursor-not-allowed">
                            <span class="absolute right-4 top-3.5 text-gray-400 pointer-events-none"><i class="fas fa-clock"></i></span>
                        </div>
                    </div>
                </div>

                @if(!$isLocked)
                <div class="col-span-1 md:col-span-2 mt-4" x-data="itemManager()">
                    <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-2">
                        <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Interested Services / Products</h4>
                        <button type="button" @click="addItem()" class="text-blue-600 font-bold text-xs hover:text-blue-800"><i class="fas fa-plus-circle"></i> Add Item</button>
                    </div>

                    <div class="space-y-3">
                        <div class="hidden md:flex gap-4 px-2 mb-2">
                            <div class="w-5/12 text-xs font-bold text-gray-400 uppercase">Item</div>
                            <div class="w-2/12 text-xs font-bold text-gray-400 uppercase">Qty / Hrs</div>
                            <div class="w-2/12 text-xs font-bold text-gray-400 uppercase">Price (₹)</div>
                            <div class="w-1/12 text-xs font-bold text-gray-400 uppercase">GST (₹)</div>
                            <div class="w-2/12 text-xs font-bold text-gray-400 uppercase text-right">Amount</div>
                            <div class="w-8"></div>
                        </div>

                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex flex-col md:flex-row gap-3 md:items-center bg-gray-50 p-3 rounded-xl border border-gray-100">
                                <div class="w-full md:w-5/12">
                                    <label class="md:hidden text-xs font-bold text-gray-400 uppercase block mb-1">Item</label>
                                    <select :name="'items['+index+'][product_service_id]'" x-model="item.product_service_id" @change="updateItemDetails(index)" class="w-full rounded-lg border border-gray-200 bg-white py-2 px-3 text-sm outline-none">
                                        <option value="">Select Item</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-gst="{{ $product->gst_rate }}" data-pricing="{{ $product->pricing_model }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="w-full md:w-2/12">
                                    <label class="md:hidden text-xs font-bold text-gray-400 uppercase block mb-1">Qty/Hrs</label>
                                    <input type="number" :step="item.pricing_model === 'Hourly' ? '0.5' : '1'" :name="'items['+index+'][quantity]'" x-model="item.quantity" class="w-full rounded-lg border border-gray-200 bg-white py-2 px-3 text-sm outline-none">
                                </div>

                                <div class="w-full md:w-2/12">
                                    <label class="md:hidden text-xs font-bold text-gray-400 uppercase block mb-1">Price</label>
                                    <input type="number" step="0.01" :name="'items['+index+'][price]'" x-model="item.price" class="w-full rounded-lg border border-gray-200 bg-white py-2 px-3 text-sm outline-none">
                                </div>

                                <div class="w-full md:w-1/12">
                                    <label class="md:hidden text-xs font-bold text-gray-400 uppercase block mb-1">GST (₹)</label>
                                    <div class="py-2 px-3 text-sm font-medium text-gray-500 bg-gray-100 rounded-lg border border-gray-200" 
                                         x-text="( (item.price * item.quantity) * (item.gst_rate / 100) ).toFixed(2)">
                                    </div>
                                </div>

                                <div class="w-full md:w-2/12 text-right">
                                    <label class="md:hidden text-xs font-bold text-gray-400 uppercase block mb-1">Amount</label>
                                    <div class="py-2 px-3 text-sm font-bold text-slate-700 bg-white rounded-lg border border-gray-200" 
                                         x-text="( (item.price * item.quantity) + ((item.price * item.quantity) * (item.gst_rate / 100)) ).toFixed(2)">
                                    </div>
                                </div>

                                <div class="w-full md:w-8 text-right">
                                    <button type="button" @click="items.splice(index, 1)" class="text-red-400 hover:text-red-600 mb-2"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            </div>
                        </template>
                        <div x-show="items.length === 0" class="text-center text-gray-400 text-xs italic py-2">No items added.</div>
                    </div>
                    
                    <div class="flex justify-between items-center mt-3 bg-blue-50 p-3 rounded-lg border border-blue-100" x-show="items.length > 0">
                        <span class="text-sm font-bold text-blue-800">Total Value</span>
                        <span class="text-lg font-bold text-blue-600" x-text="'₹' + totalEstimate.toFixed(2)"></span>
                    </div>
                </div>
                @endif
                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Assign Staff</label>
                    <div class="relative">
                        <select name="staff_id" {{ $isLocked ? 'disabled' : '' }} class="w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white disabled:opacity-60 disabled:cursor-not-allowed">
                            <option value="">-- Unassigned --</option>
                            @foreach($staffMembers as $staff)
                                <option value="{{ $staff->id }}" {{ $booking->staff_id == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                            @endforeach
                        </select>
                        <span class="absolute right-4 top-3.5 text-gray-500 pointer-events-none"><i class="fas fa-chevron-down"></i></span>
                    </div>
                </div>
                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Status</label>
                    <div class="relative">
                        <select name="status" {{ $isLocked ? 'disabled' : '' }} class="w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white disabled:opacity-60 disabled:cursor-not-allowed">
                            @foreach(['Scheduled', 'Completed', 'Cancelled', 'No Show'] as $status)
                                <option value="{{ $status }}" {{ $booking->status == $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                        <span class="absolute right-4 top-3.5 text-gray-500 pointer-events-none"><i class="fas fa-chevron-down"></i></span>
                    </div>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label class="mb-2 block font-bold text-sm text-slate-700">Notes</label>
                    <textarea name="notes" rows="3" {{ $isLocked ? 'disabled' : '' }} class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white disabled:opacity-60 disabled:cursor-not-allowed">{{ $booking->notes }}</textarea>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-6 flex justify-end gap-3">
                <a href="{{ route('bookings.index') }}" class="rounded-xl border border-gray-300 bg-white py-3 px-6 font-bold text-gray-700 hover:bg-gray-50 transition">
                    {{ $isLocked ? 'Back to List' : 'Cancel' }}
                </a>
                @if(!$isLocked)
                <button type="submit" class="rounded-xl bg-blue-600 py-3 px-8 font-bold text-white hover:bg-blue-700 transition shadow-md">Update Booking</button>
                @endif
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".date-input", { dateFormat: "Y-m-d" });
        flatpickr(".time-input", { enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: false });
    });

    function itemManager() {
        return {
            items: @json($initialItems),
            
            addItem() {
                this.items.push({ product_service_id: '', price: 0, quantity: 1, gst_rate: 0, pricing_model: '' });
            },
            updateItemDetails(index) {
                setTimeout(() => {
                    let select = document.getElementsByName(`items[${index}][product_service_id]`)[0];
                    if(select && select.selectedOptions.length > 0) {
                        let option = select.selectedOptions[0];
                        let price = parseFloat(option.getAttribute('data-price')) || 0;
                        let gstRate = parseFloat(option.getAttribute('data-gst')) || 0;
                        let pricing = option.getAttribute('data-pricing');
                        
                        this.items[index].price = price;
                        this.items[index].gst_rate = gstRate;
                        this.items[index].pricing_model = pricing;
                        
                        if (pricing !== 'Hourly') {
                            this.items[index].quantity = Math.floor(this.items[index].quantity) || 1;
                        }
                    }
                }, 50);
            },
            get totalEstimate() {
                return this.items.reduce((sum, item) => {
                    let base = item.price * item.quantity;
                    let tax = base * (item.gst_rate / 100);
                    return sum + base + tax;
                }, 0);
            }
        }
    }
</script>
@endsection