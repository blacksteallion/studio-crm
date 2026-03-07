@extends('layouts.app')
@section('header', 'New Booking')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar { border-radius: 12px; }
    .date-input, .time-input { background-color: #f9fafb !important; cursor: pointer !important; }
</style>

{{-- PREPARE DATA SAFELY --}}
@php
    $initialItems = $prefilledItems->isNotEmpty() 
        ? $prefilledItems->map(function($item) {
            return [
                'product_service_id' => $item['product_service_id'],
                'price' => (float) $item['price'],
                'quantity' => (float) $item['quantity'],
                'gst_rate' => 0,
                'pricing_model' => $item['pricing_model'] ?? ''
            ];
        })
        : [['product_service_id' => '', 'price' => 0, 'quantity' => 1, 'gst_rate' => 0, 'pricing_model' => '']];
@endphp

<div class="max-w-5xl mx-auto">
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 py-4 px-6">
            <h3 class="font-bold text-slate-800 text-lg">Create Booking</h3>
        </div>
        
        <form action="{{ route('bookings.store') }}" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="inquiry_id" value="{{ $inquiry ? $inquiry->id : '' }}">

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
                            <select name="location_id" required class="w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white @error('location_id') !border-red-500 !bg-red-50 @enderror">
                                <option value="">-- Select Location --</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('location_id', ($inquiry ? $inquiry->location_id : session('active_location_id'))) == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                                @endforeach
                            </select>
                            <span class="absolute right-4 top-3.5 text-gray-500 pointer-events-none"><i class="fas fa-chevron-down"></i></span>
                        </div>
                    </div>
                    <div>
                        <label class="mb-2 block font-bold text-sm text-slate-700">Customer <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="customer_id" class="w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white @error('customer_id') !border-red-500 !bg-red-50 @enderror">
                                <option value="">-- Select Customer --</option>
                                @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}" {{ ($inquiry && $inquiry->customer_id == $cust->id) ? 'selected' : '' }}>{{ $cust->name }}</option>
                                @endforeach
                            </select>
                            <span class="absolute right-4 top-3.5 text-gray-500 pointer-events-none"><i class="fas fa-chevron-down"></i></span>
                        </div>
                    </div>
                </div>

                @if($inquiry)
                <div class="col-span-1 md:col-span-2 bg-blue-50 border border-blue-100 rounded-xl p-4 flex items-center gap-3">
                    <div class="bg-blue-200 text-blue-700 h-10 w-10 rounded-full flex items-center justify-center"><i class="fas fa-info-circle"></i></div>
                    <div>
                        <h4 class="font-bold text-blue-800 text-sm">Converting Inquiry #{{ $inquiry->id }}</h4>
                        <p class="text-xs text-blue-600">Primary Date: {{ $inquiry->primary_date->format('d M, Y') }} | Time: {{ \Carbon\Carbon::parse($inquiry->from_time)->format('h:i A') }}</p>
                    </div>
                </div>
                @endif

                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Booking Date <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="text" name="booking_date" value="{{ old('booking_date', $inquiry ? $inquiry->primary_date->format('Y-m-d') : '') }}" required class="date-input w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white @error('booking_date') !border-red-500 !bg-red-50 @enderror">
                        <span class="absolute right-4 top-3.5 text-gray-400 pointer-events-none"><i class="fas fa-calendar-alt"></i></span>
                    </div>
                </div>
                
                <div class="flex gap-4">
                    <div class="w-1/2">
                        <label class="mb-2 block font-bold text-sm text-slate-700">Start Time <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" name="start_time" value="{{ old('start_time', $inquiry ? \Carbon\Carbon::parse($inquiry->from_time)->format('H:i') : '') }}" required class="time-input w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white @error('start_time') !border-red-500 !bg-red-50 @enderror">
                            <span class="absolute right-4 top-3.5 text-gray-400 pointer-events-none"><i class="fas fa-clock"></i></span>
                        </div>
                    </div>
                    <div class="w-1/2">
                        <label class="mb-2 block font-bold text-sm text-slate-700">End Time <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" name="end_time" value="{{ old('end_time', $inquiry ? \Carbon\Carbon::parse($inquiry->to_time)->format('H:i') : '') }}" required class="time-input w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white @error('end_time') !border-red-500 !bg-red-50 @enderror">
                            <span class="absolute right-4 top-3.5 text-gray-400 pointer-events-none"><i class="fas fa-clock"></i></span>
                        </div>
                    </div>
                </div>

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
                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Assign Staff</label>
                    <div class="relative">
                        <select name="staff_id" class="w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white">
                            <option value="">-- Unassigned --</option>
                            @foreach($staffMembers as $staff)
                                <option value="{{ $staff->id }}" {{ ($inquiry && $inquiry->assigned_staff_id == $staff->id) ? 'selected' : '' }}>{{ $staff->name }}</option>
                            @endforeach
                        </select>
                        <span class="absolute right-4 top-3.5 text-gray-500 pointer-events-none"><i class="fas fa-chevron-down"></i></span>
                    </div>
                </div>
                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Status</label>
                    <div class="relative">
                        <select name="status" class="w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white">
                            <option value="Scheduled">Scheduled</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="No Show">No Show</option>
                        </select>
                        <span class="absolute right-4 top-3.5 text-gray-500 pointer-events-none"><i class="fas fa-chevron-down"></i></span>
                    </div>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label class="mb-2 block font-bold text-sm text-slate-700">Notes</label>
                    <textarea name="notes" rows="3" class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white"></textarea>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-6 flex justify-end gap-3">
                <a href="{{ route('bookings.index') }}" class="rounded-xl border border-gray-300 bg-white py-3 px-6 font-bold text-gray-700 hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" class="rounded-xl bg-blue-600 py-3 px-8 font-bold text-white hover:bg-blue-700 transition shadow-md">Create Booking</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".date-input", { dateFormat: "Y-m-d", minDate: "today", disableMobile: "true" });
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