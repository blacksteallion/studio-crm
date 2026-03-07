@extends('layouts.app')
@section('header', 'Edit Expense')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar { border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; }
    .flatpickr-day.selected { background: #2563eb !important; border-color: #2563eb !important; }
</style>

<div class="max-w-2xl mx-auto">
    
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-8 py-5 border-b border-gray-100">
            <h3 class="font-bold text-lg text-slate-800">Edit Expense Details</h3>
        </div>

        <form action="{{ route('expenses.update', $expense->id) }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Studio Location <span class="text-red-500">*</span></label>
                    <select name="location_id" required class="w-full rounded-lg border border-gray-200 py-2.5 px-3 bg-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Location...</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('location_id', $expense->location_id) == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Date <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="text" id="expense_date" name="expense_date" 
                               value="{{ $expense->expense_date->format('Y-m-d') }}" required 
                               class="w-full rounded-lg border border-gray-200 py-2.5 pl-3 pr-10 focus:border-blue-500 focus:ring-blue-500 text-slate-700 font-medium bg-white"
                               placeholder="DD/MM/YYYY">
                        
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                            <i class="far fa-calendar-alt text-lg"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Category <span class="text-red-500">*</span></label>
                    <select name="category" required class="w-full rounded-lg border border-gray-200 py-2.5 px-3 bg-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Category...</option>
                        @foreach($categories as $group => $items)
                            <optgroup label="{{ $group }}">
                                @foreach($items as $item)
                                    <option value="{{ $item }}" {{ $expense->category == $item ? 'selected' : '' }}>{{ $item }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Amount <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 font-bold">₹</span>
                        </div>
                        <input type="number" step="1" name="amount" value="{{ $expense->amount }}" placeholder="0" required 
                               class="w-full rounded-lg border border-gray-200 py-2.5 pl-8 pr-4 font-bold text-slate-800 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Title / Payee <span class="text-red-500">*</span></label>
                    <input list="expense-titles" name="title" value="{{ $expense->title }}" placeholder="e.g. Uber to Client Meeting" required 
                           class="w-full rounded-lg border border-gray-200 py-2.5 px-3 focus:border-blue-500 focus:ring-blue-500">
                    <datalist id="expense-titles">
                        <option value="Uber/Taxi">
                        <option value="Office Rent">
                        <option value="Staff Salary">
                    </datalist>
                </div>

                <div class="md:col-span-2" x-data="{ fileName: '{{ $expense->receipt_path ? 'Current File Attached' : 'No file selected.' }}' }">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Receipt / Invoice</label>
                    <div class="flex items-center rounded-lg border border-gray-200 bg-gray-50 overflow-hidden">
                        <label class="cursor-pointer bg-blue-50 text-blue-700 font-bold px-5 py-2.5 hover:bg-blue-100 transition border-r border-blue-100 text-sm">
                            Browse...
                            <input type="file" name="receipt" class="hidden" @change="fileName = $event.target.files[0].name">
                        </label>
                        <span class="px-4 text-sm text-gray-500 truncate" x-text="fileName"></span>
                    </div>
                    @if($expense->receipt_path)
                        <div class="mt-2">
                            <a href="{{ asset('storage/'.$expense->receipt_path) }}" target="_blank" class="text-xs text-blue-600 hover:underline font-bold">
                                <i class="fas fa-paperclip"></i> View Current Receipt
                            </a>
                        </div>
                    @endif
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Notes</label>
                    <textarea name="description" rows="2" class="w-full rounded-lg border border-gray-200 py-2.5 px-3 focus:border-blue-500 focus:ring-blue-500">{{ $expense->description }}</textarea>
                </div>

            </div>

            <div class="pt-6 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('expenses.index') }}" 
                   class="px-6 py-2.5 rounded-xl border border-gray-300 bg-white text-slate-700 font-bold hover:bg-gray-50 transition">
                   Cancel
                </a>
                
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-8 rounded-xl shadow-md transition-all">
                    Update Expense
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#expense_date", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        allowInput: true
    });
</script>
@endsection