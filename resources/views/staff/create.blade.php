@extends('layouts.app')

@section('header', 'Add Staff')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        
        <div class="border-b border-gray-100 py-4 px-6">
            <h3 class="font-bold text-slate-800 text-lg">Primary Details</h3>
        </div>

        <form action="{{ route('staff.store') }}" method="POST" enctype="multipart/form-data" class="p-6" novalidate>
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                
                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required 
                        class="w-full rounded-xl border py-3 px-5 outline-none transition 
                        @error('name') 
                            border-red-500 bg-red-50 text-red-900 placeholder-red-400 focus:border-red-500 focus:ring-red-500 
                        @else 
                            border-gray-200 bg-gray-50 text-slate-800 focus:border-blue-500 focus:bg-white 
                        @enderror">
                    @error('name')
                        <p class="mt-1 text-xs font-bold text-red-500 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Mobile Number <span class="text-red-500">*</span></label>
                    <input type="text" name="mobile" value="{{ old('mobile') }}" required 
                        class="w-full rounded-xl border py-3 px-5 outline-none transition 
                        @error('mobile') border-red-500 bg-red-50 @else border-gray-200 bg-gray-50 focus:border-blue-500 focus:bg-white @enderror">
                    @error('mobile')
                        <p class="mt-1 text-xs font-bold text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Email Address (Login ID) <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="new-email"
                        class="w-full rounded-xl border py-3 px-5 outline-none transition 
                        @error('email') border-red-500 bg-red-50 @else border-gray-200 bg-gray-50 focus:border-blue-500 focus:bg-white @enderror">
                    @error('email')
                        <p class="mt-1 text-xs font-bold text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Set Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required autocomplete="new-password"
                        class="w-full rounded-xl border py-3 px-5 outline-none transition 
                        @error('password') border-red-500 bg-red-50 @else border-gray-200 bg-gray-50 focus:border-blue-500 focus:bg-white @enderror">
                    @error('password')
                        <p class="mt-1 text-xs font-bold text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">System Role <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="system_role" required class="w-full appearance-none rounded-xl border py-3 px-5 outline-none transition 
                            @error('system_role') border-red-500 bg-red-50 @else border-gray-200 bg-gray-50 focus:border-blue-500 focus:bg-white @enderror">
                            <option value="">Select a role...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ old('system_role') == $role->name ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute right-4 top-3.5 text-gray-500 pointer-events-none"><i class="fas fa-chevron-down"></i></span>
                    </div>
                    @error('system_role')
                        <p class="mt-1 text-xs font-bold text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label class="mb-2 block font-bold text-sm text-slate-700">Assign Studio Locations <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($locations as $loc)
                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition shadow-sm {{ is_array(old('locations')) && in_array($loc->id, old('locations')) ? 'bg-blue-50 border-blue-200 ring-1 ring-blue-500' : 'bg-white' }}">
                                <input type="checkbox" name="locations[]" value="{{ $loc->id }}" 
                                       {{ is_array(old('locations')) && in_array($loc->id, old('locations')) ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                                <span class="text-sm font-bold text-slate-700">{{ $loc->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('locations')
                        <p class="mt-1 text-xs font-bold text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Account Status</label>
                    <div class="flex items-center gap-3 mt-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="status" value="1" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                        <span class="text-sm font-bold text-gray-600">Enable Staff Account</span>
                    </div>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label class="mb-2 block font-bold text-sm text-slate-700">Profile Photo</label>
                    <input type="file" name="photo" accept=".jpg,.jpeg,.png" 
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-l-xl file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border rounded-xl bg-white cursor-pointer 
                        @error('photo') border-red-500 bg-red-50 @else border-gray-200 @enderror">
                    @error('photo')
                        <p class="mt-1 text-xs font-bold text-red-500 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <div class="border-t border-gray-100 pt-6 flex justify-end gap-3">
                <a href="{{ route('staff.index') }}" class="rounded-xl border border-gray-300 bg-white py-3 px-6 font-bold text-gray-700 hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" class="rounded-xl bg-blue-600 py-3 px-8 font-bold text-white hover:bg-blue-700 transition shadow-md">Save Staff Member</button>
            </div>
        </form>
    </div>
</div>
@endsection