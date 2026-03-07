@extends('layouts.app')

@section('header', 'Edit Staff')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        
        <div class="border-b border-gray-100 py-4 px-6">
            <h3 class="font-bold text-slate-800 text-lg">Edit Details</h3>
        </div>

        <form action="{{ route('staff.update', $staff->id) }}" method="POST" enctype="multipart/form-data" class="p-6" novalidate>
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                
                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $staff->name) }}" required 
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
                    <input type="text" name="mobile" value="{{ old('mobile', $staff->mobile) }}" required 
                        class="w-full rounded-xl border py-3 px-5 outline-none transition 
                        @error('mobile') border-red-500 bg-red-50 @else border-gray-200 bg-gray-50 focus:border-blue-500 focus:bg-white @enderror">
                    @error('mobile')
                        <p class="mt-1 text-xs font-bold text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Email Address (Login ID) <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $staff->email) }}" required autocomplete="off"
                        class="w-full rounded-xl border py-3 px-5 outline-none transition 
                        @error('email') border-red-500 bg-red-50 @else border-gray-200 bg-gray-50 focus:border-blue-500 focus:bg-white @enderror">
                    @error('email')
                        <p class="mt-1 text-xs font-bold text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block font-bold text-sm text-slate-700">Reset Password</label>
                    <input type="password" name="password" placeholder="Leave blank to keep current password" autocomplete="new-password"
                        class="w-full rounded-xl border py-3 px-5 outline-none transition 
                        @error('password') border-red-500 bg-red-50 @else border-gray-200 bg-gray-50 focus:border-blue-500 focus:bg-white @enderror">
                    @error('password')
                        <p class="mt-1 text-xs font-bold text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label class="mb-2 block font-bold text-sm text-slate-700">System Role <span class="text-red-500">*</span></label>
                    <div class="relative w-full md:w-1/2">
                        <select name="system_role" required class="w-full appearance-none rounded-xl border py-3 px-5 outline-none transition 
                            @error('system_role') border-red-500 bg-red-50 @else border-gray-200 bg-gray-50 focus:border-blue-500 focus:bg-white @enderror">
                            <option value="">Select a role...</option>
                            @php $currentRole = old('system_role', $staff->roles->first()->name ?? ''); @endphp
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ $currentRole == $role->name ? 'selected' : '' }}>
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
                    <label class="mb-2 block font-bold text-sm text-slate-700">Assigned Studio Locations <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @php 
                            $assignedLocs = old('locations', $staff->locations->pluck('id')->toArray());
                        @endphp
                        @foreach($locations as $loc)
                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition shadow-sm {{ in_array($loc->id, $assignedLocs) ? 'bg-blue-50 border-blue-200 ring-1 ring-blue-500' : 'bg-white' }}">
                                <input type="checkbox" name="locations[]" value="{{ $loc->id }}" 
                                       {{ in_array($loc->id, $assignedLocs) ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                                <span class="text-sm font-bold text-slate-700">{{ $loc->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('locations')
                        <p class="mt-1 text-xs font-bold text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label class="mb-2 block font-bold text-sm text-slate-700">Profile Photo</label>
                    @if($staff->photo)
                        <div class="mb-3">
                            <img src="{{ asset('storage/staff_photos/' . $staff->photo) }}" class="w-16 h-16 rounded-xl object-cover border-2 border-white shadow-md">
                        </div>
                    @endif
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
                <button type="submit" class="rounded-xl bg-blue-600 py-3 px-8 font-bold text-white hover:bg-blue-700 transition shadow-md">Update Staff Member</button>
            </div>
        </form>
    </div>
</div>
@endsection