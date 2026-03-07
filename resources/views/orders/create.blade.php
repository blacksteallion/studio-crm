@extends('layouts.app')
@section('header', 'Create Invoice')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar { border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    .date-input { background-color: #ffffff !important; cursor: pointer !important; }
</style>

{{-- PREPARE DATA FOR JAVASCRIPT SAFELY --}}
@php
    $initialRows = $prefilledItems->isEmpty() 
        ? [['product_service_id' => '', 'name' => '', 'qty' => 1, 'price' => 0, 'gst_rate' => 0]] 
        : $prefilledItems->map(function($item) {
            return [
                'product_service_id' => $item['product_service_id'],
                'name' => $item['name'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'gst_rate' => $item['gst_rate'] ?? 0
            ];
        });
@endphp

<div class="max-w-5xl mx-auto" x-data="invoiceLogic()">
    
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 py-4 px-6 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 text-lg">New Invoice</h3>
            <span class="text-sm text-gray-500 font-mono">{{ $invoiceNumber }}</span>
        </div>
        
        <form action="{{ route('orders.store') }}" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="booking_id" value="{{ $booking ? $booking->id : '' }}">
            <input type="hidden" name="invoice_number" value="{{ $invoiceNumber }}">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Studio Location <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="location_id" required class="w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white @error('location_id') !border-red-500 !bg-red-50 @enderror">
                            <option value="">-- Select Location --</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('location_id', ($booking ? $booking->location_id : session('active_location_id'))) == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                        <span class="absolute right-4 top-3.5 text-gray-500 pointer-events-none"><i class="fas fa-chevron-down"></i></span>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Customer <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="customer_id" required class="w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white">
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->id }}" {{ (isset($customer) && $customer->id == $cust->id) ? 'selected' : '' }}>{{ $cust->name }}</option>
                            @endforeach
                        </select>
                        <span class="absolute right-4 top-3.5 text-gray-500 pointer-events-none"><i class="fas fa-chevron-down"></i></span>
                    </div>
                </div>
                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Invoice Date <span class="text-red-500">*</span></label>
                    <input type="text" name="invoice_date" value="{{ date('Y-m-d') }}" required class="date-input invoice-picker w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none" placeholder="Select Date">
                </div>
                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Due Date</label>
                    <input type="text" name="due_date" class="date-input due-picker w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none" placeholder="Select Date">
                </div>
            </div>

            @if($booking)
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-8 flex gap-4 items-center">
                <div class="bg-blue-200 text-blue-700 h-10 w-10 rounded-full flex items-center justify-center"><i class="fas fa-calendar-check"></i></div>
                <div>
                    <h4 class="font-bold text-blue-800 text-sm">Linked to Booking #{{ $booking->id }}</h4>
                    <p class="text-xs text-blue-600">{{ $booking->booking_date->format('d M, Y') }} • {{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }}</p>
                </div>
            </div>
            @endif

            <div class="mb-8">
                <h4 class="font-bold text-slate-800 text-sm mb-4 uppercase tracking-wider border-b border-gray-100 pb-2">Line Items</h4>
                
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-xs text-gray-400 uppercase">
                            <th class="pb-3 w-5/12">Product / Service</th>
                            <th class="pb-3 w-2/12">Qty</th>
                            <th class="pb-3 w-2/12">Price (₹)</th>
                            <th class="pb-3 w-1/12">GST (₹)</th>
                            <th class="pb-3 w-2/12 text-right">Amount</th>
                            <th class="pb-3 w-8"></th>
                        </tr>
                    </thead>
                    <tbody class="space-y-2">
                        <template x-for="(row, index) in rows" :key="index">
                            <tr>
                                <td class="pr-2 pb-2">
                                    <select :name="'items['+index+'][product_service_id]'" x-model="row.product_service_id" @change="updateItem(index)" class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 px-3 text-sm outline-none">
                                        <option value="">Select Item</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-gst="{{ $product->gst_rate }}" data-name="{{ $product->name }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" :name="'items['+index+'][name]'" :value="row.name">
                                </td>
                                <td class="pr-2 pb-2">
                                    <input type="number" :name="'items['+index+'][qty]'" x-model="row.qty" min="0.1" step="0.1" class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 px-3 text-sm outline-none text-center">
                                </td>
                                <td class="pr-2 pb-2">
                                    <input type="number" :name="'items['+index+'][price]'" x-model="row.price" min="0" step="0.01" class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 px-3 text-sm outline-none text-right">
                                </td>
                                <td class="pb-2 text-sm text-gray-500 font-medium">
                                    <span x-text="formatMoney((row.qty * row.price) * (row.gst_rate / 100))"></span>
                                    <div class="text-[10px] text-gray-400" x-text="row.gst_rate + '%'"></div>
                                </td>
                                <td class="pb-2 text-right font-bold text-slate-700">
                                    <span x-text="formatMoney((row.qty * row.price) + ((row.qty * row.price) * (row.gst_rate / 100)))"></span>
                                </td>
                                <td class="pb-2 text-right">
                                    <button type="button" @click="removeRow(index)" class="text-red-400 hover:text-red-600"><i class="fas fa-trash-alt"></i></button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                
                <button type="button" @click="addRow()" class="mt-2 text-sm font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                    <i class="fas fa-plus-circle"></i> Add Item
                </button>
            </div>

            <div class="flex justify-end">
                <div class="w-full md:w-1/3 space-y-3">
                    <div class="flex justify-between text-sm text-gray-500">
                        <span>Subtotal (Excl. Tax)</span>
                        <span class="font-medium text-slate-800" x-text="formatMoney(subtotal)"></span>
                        <input type="hidden" name="subtotal" :value="subtotal">
                    </div>
                    <div class="flex justify-between text-sm text-gray-500">
                        <span>Total Tax</span>
                        <span class="font-medium text-red-500" x-text="formatMoney(taxAmount)"></span>
                        <input type="hidden" name="tax" :value="taxAmount">
                    </div>
                    <div class="flex justify-between items-center text-sm text-gray-500">
                        <span>Discount</span>
                        <input type="number" name="discount" x-model="discount" class="w-24 rounded border border-gray-200 py-1 px-2 text-right text-xs outline-none">
                    </div>
                    <div class="border-t border-gray-200 pt-3 flex justify-between text-lg font-bold text-slate-800">
                        <span>Grand Total</span>
                        <span x-text="formatMoney(total)"></span>
                        <input type="hidden" name="total_amount" :value="total">
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-6 mt-8 flex justify-end gap-3">
                <a href="{{ route('orders.index') }}" class="rounded-xl border border-gray-300 bg-white py-3 px-6 font-bold text-gray-700 hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" class="rounded-xl bg-blue-600 py-3 px-8 font-bold text-white hover:bg-blue-700 transition shadow-md">Create Invoice</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".invoice-picker", { dateFormat: "Y-m-d", disableMobile: "true" });
        flatpickr(".due-picker", { dateFormat: "Y-m-d", minDate: "today", disableMobile: "true" });
    });

    function invoiceLogic() {
        return {
            rows: @json($initialRows),
            discount: 0,

            get subtotal() {
                return this.rows.reduce((sum, row) => sum + (row.qty * row.price), 0);
            },
            get taxAmount() {
                return this.rows.reduce((sum, row) => sum + ((row.qty * row.price) * (row.gst_rate / 100)), 0);
            },
            get total() {
                return Math.max(0, this.subtotal + this.taxAmount - this.discount);
            },
            addRow() {
                this.rows.push({ product_service_id: '', name: '', qty: 1, price: 0, gst_rate: 0 });
            },
            removeRow(index) {
                if(this.rows.length > 1) {
                    this.rows.splice(index, 1);
                }
            },
            updateItem(index) {
                setTimeout(() => {
                    let selects = document.querySelectorAll('select[x-model="row.product_service_id"]');
                    if (selects[index]) {
                        let selectedOption = selects[index].selectedOptions[0];
                        if (selectedOption && selectedOption.value) {
                            let price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                            let gst = parseFloat(selectedOption.getAttribute('data-gst')) || 0;
                            let name = selectedOption.getAttribute('data-name');
                            
                            this.rows[index].price = price;
                            this.rows[index].gst_rate = gst;
                            this.rows[index].name = name;
                        }
                    }
                }, 50);
            },
            formatMoney(value) {
                return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(value);
            }
        }
    }
</script>
@endsection