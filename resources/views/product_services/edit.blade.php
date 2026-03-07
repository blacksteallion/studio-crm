@extends('layouts.app')
@section('header', 'Edit Product / Service')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
        <div class="border-b border-gray-100 py-4 px-6 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 text-lg">Edit Item</h3>
            <span class="text-xs font-mono text-gray-400">ID: {{ $productService->id }}</span>
        </div>
        
        <form action="{{ route('product_services.update', $productService->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm font-bold">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6">
                <label class="mb-2 block font-bold text-sm text-slate-700">Item Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $productService->name) }}" required class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Type <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="type" class="w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white">
                            <option value="Service" {{ old('type', $productService->type) == 'Service' ? 'selected' : '' }}>Service</option>
                            <option value="Product" {{ old('type', $productService->type) == 'Product' ? 'selected' : '' }}>Product</option>
                        </select>
                        <span class="absolute right-4 top-3.5 text-gray-500 pointer-events-none"><i class="fas fa-chevron-down"></i></span>
                    </div>
                </div>
                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Pricing Model <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="pricing_model" class="w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white">
                            <option value="Hourly" {{ old('pricing_model', $productService->pricing_model) == 'Hourly' ? 'selected' : '' }}>Hourly</option>
                            <option value="Fixed" {{ old('pricing_model', $productService->pricing_model) == 'Fixed' ? 'selected' : '' }}>Fixed</option>
                            <option value="Per Unit" {{ old('pricing_model', $productService->pricing_model) == 'Per Unit' ? 'selected' : '' }}>Per Unit</option>
                        </select>
                        <span class="absolute right-4 top-3.5 text-gray-500 pointer-events-none"><i class="fas fa-chevron-down"></i></span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Standard Price (₹) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $productService->price) }}" required class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white">
                </div>
                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">GST Rate (%) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" step="0.01" name="gst_rate" value="{{ old('gst_rate', $productService->gst_rate) }}" required class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white">
                        <span class="absolute right-4 top-3.5 text-gray-400 font-bold text-xs">%</span>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="mb-2 block font-bold text-sm text-slate-700">Description</label>
                <textarea name="description" rows="3" class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white">{{ old('description', $productService->description) }}</textarea>
            </div>

            <div class="mb-6">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $productService->is_active) ? 'checked' : '' }} class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    <span class="ms-3 text-sm font-medium text-gray-900">Active Status</span>
                </label>
            </div>

            <div class="border-t border-gray-100 pt-6 flex justify-end gap-3">
                <a href="{{ route('product_services.index') }}" class="rounded-xl border border-gray-300 bg-white py-3 px-6 font-bold text-gray-700 hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" class="rounded-xl bg-blue-600 py-3 px-8 font-bold text-white hover:bg-blue-700 transition shadow-md">Update Item</button>
            </div>
        </form>
    </div>
</div>
@endsection