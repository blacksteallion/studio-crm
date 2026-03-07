@extends('layouts.app')

@section('header', 'Add Customer')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        
        <div class="border-b border-gray-100 py-4 px-6">
            <h3 class="font-bold text-slate-800 text-lg">Customer Details</h3>
        </div>

        <form action="{{ route('customers.store') }}" method="POST" class="p-6" novalidate>
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                
                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Business / Company Name</label>
                    <input type="text" name="business_name" value="{{ old('business_name') }}"
                        class="w-full rounded-xl border py-3 px-5 outline-none transition 
                        @error('business_name') border-red-500 bg-red-50 text-red-900 placeholder-red-400 focus:border-red-500 focus:ring-red-500 @else border-gray-200 bg-gray-50 text-slate-800 focus:border-blue-500 focus:bg-white placeholder-gray-400 @enderror"
                        placeholder="e.g. Acme Studio Productions">
                    @error('business_name')
                        <p class="mt-1 text-xs font-bold text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Contact Person Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required 
                        class="w-full rounded-xl border py-3 px-5 outline-none transition 
                        @error('name') border-red-500 bg-red-50 text-red-900 placeholder-red-400 focus:border-red-500 focus:ring-red-500 @else border-gray-200 bg-gray-50 text-slate-800 focus:border-blue-500 focus:bg-white placeholder-gray-400 @enderror"
                        placeholder="John Doe">
                    @error('name')
                        <p class="mt-1 text-xs font-bold text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Mobile Number <span class="text-red-500">*</span></label>
                    <div class="flex">
                        <span class="inline-flex items-center px-4 rounded-l-xl border border-r-0 border-gray-200 bg-gray-100 text-gray-500 text-sm font-bold">91</span>
                        <input type="text" name="mobile" value="{{ old('mobile') }}" required 
                            class="w-full rounded-r-xl border py-3 px-5 outline-none transition 
                            @error('mobile') border-red-500 bg-red-50 text-red-900 placeholder-red-400 focus:border-red-500 focus:ring-red-500 @else border-gray-200 bg-gray-50 text-slate-800 focus:border-blue-500 focus:bg-white placeholder-gray-400 @enderror"
                            placeholder="98765xxxxx">
                    </div>
                    @error('mobile')
                        <p class="mt-1 text-xs font-bold text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="w-full rounded-xl border py-3 px-5 outline-none transition 
                        @error('email') border-red-500 bg-red-50 text-red-900 placeholder-red-400 focus:border-red-500 focus:ring-red-500 @else border-gray-200 bg-gray-50 text-slate-800 focus:border-blue-500 focus:bg-white placeholder-gray-400 @enderror"
                        placeholder="john@example.com">
                    @error('email')
                        <p class="mt-1 text-xs font-bold text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Website URL</label>
                    <input type="url" name="website" value="{{ old('website') }}"
                        class="w-full rounded-xl border py-3 px-5 outline-none transition 
                        @error('website') border-red-500 bg-red-50 text-red-900 placeholder-red-400 focus:border-red-500 focus:ring-red-500 @else border-gray-200 bg-gray-50 text-slate-800 focus:border-blue-500 focus:bg-white placeholder-gray-400 @enderror"
                        placeholder="https://example.com">
                    @error('website')
                        <p class="mt-1 text-xs font-bold text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">GST / Tax Number</label>
                    <input type="text" name="gst_number" value="{{ old('gst_number') }}"
                        class="w-full rounded-xl border py-3 px-5 outline-none transition uppercase
                        @error('gst_number') border-red-500 bg-red-50 text-red-900 placeholder-red-400 focus:border-red-500 focus:ring-red-500 @else border-gray-200 bg-gray-50 text-slate-800 focus:border-blue-500 focus:bg-white placeholder-gray-400 @enderror"
                        placeholder="22AAAAA0000A1Z5">
                    @error('gst_number')
                        <p class="mt-1 text-xs font-bold text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-1 md:col-span-2 border-t border-gray-100 pt-6 mt-2">
                    <h4 class="font-bold text-slate-800 text-md mb-4">Billing Address</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="col-span-1 md:col-span-2">
                            <label class="mb-2 block font-bold text-sm text-slate-700">Address Line 1</label>
                            <input type="text" name="address_line1" value="{{ old('address_line1') }}"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white placeholder-gray-400"
                                placeholder="Flat / House No, Building, Street">
                        </div>
                        
                        <div class="col-span-1 md:col-span-2">
                            <label class="mb-2 block font-bold text-sm text-slate-700">Address Line 2</label>
                            <input type="text" name="address_line2" value="{{ old('address_line2') }}"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white placeholder-gray-400"
                                placeholder="Area, Landmark (Optional)">
                        </div>

                        <div>
                            <label class="mb-2 block font-bold text-sm text-slate-700">City</label>
                            <input type="text" name="city" value="{{ old('city') }}"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white placeholder-gray-400"
                                placeholder="Ahmedabad">
                        </div>

                        <div>
                            <label class="mb-2 block font-bold text-sm text-slate-700">Pin Code</label>
                            <input type="text" name="pincode" value="{{ old('pincode') }}"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white placeholder-gray-400"
                                placeholder="380001">
                        </div>

                        <div>
                            <label class="mb-2 block font-bold text-sm text-slate-700">State</label>
                            <select name="state" class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-5 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white">
                                <option value="" disabled selected>Select State</option>
                                @foreach($states as $state)
                                    <option value="{{ $state }}" {{ old('state') == $state ? 'selected' : '' }}>{{ $state }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block font-bold text-sm text-slate-700">Country</label>
                            <input type="text" name="country" value="India" readonly
                                class="w-full rounded-xl border border-gray-200 bg-gray-100 py-3 px-5 text-gray-500 cursor-not-allowed outline-none">
                        </div>
                    </div>
                </div>

                <div class="col-span-1 md:col-span-2 border-t border-gray-100 pt-6 mt-2">
                    <label class="mb-2 block font-bold text-sm text-slate-700">Remarks</label>
                    <textarea name="remarks" rows="3" 
                        class="w-full rounded-xl border py-3 px-5 outline-none transition 
                        @error('remarks') border-red-500 bg-red-50 text-red-900 placeholder-red-400 focus:border-red-500 focus:ring-red-500 @else border-gray-200 bg-gray-50 text-slate-800 focus:border-blue-500 focus:bg-white placeholder-gray-400 @enderror"
                        placeholder="Enter any additional notes here...">{{ old('remarks') }}</textarea>
                    @error('remarks')
                        <p class="mt-1 text-xs font-bold text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label class="mb-3 block font-bold text-sm text-slate-700">Account Status</label>
                    <div class="flex items-center gap-3 p-4 rounded-xl border border-gray-100 bg-gray-50">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="status" value="1" {{ old('status', '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                        </label>
                        <span class="text-sm font-medium text-gray-600">Enable Customer Account</span>
                    </div>
                </div>

            </div>

            <div class="border-t border-gray-100 pt-6 flex justify-end gap-3">
                <a href="{{ route('customers.index') }}" class="rounded-xl border border-gray-300 bg-white py-3 px-6 font-bold text-gray-700 hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" class="rounded-xl bg-blue-600 py-3 px-8 font-bold text-white hover:bg-blue-700 transition shadow-md">Save Customer</button>
            </div>
        </form>
    </div>
</div>
@endsection