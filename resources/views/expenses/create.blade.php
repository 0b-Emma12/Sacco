@extends('layouts.app')
@section('title', 'Record Expense')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-4xl">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Expenses</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">Record Expense</h1>
        </div>
        <a href="{{ route('expenses.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Back to Expenses</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 p-6 md:p-8">
        
        @if ($errors->any())
            <div class="bg-[#FF4D6B]/10 text-[#FF4D6B] border border-[#FF4D6B]/20 p-4 rounded-xl mb-6 text-sm font-semibold">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('expenses.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="space-y-4">
                <div class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-md space-y-5">
                    
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                        <input type="text" name="description" value="{{ old('description') }}" placeholder="E.g. Office Supplies" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Amount</label>
                        <input type="number" step="0.01" name="amount" value="{{ old('amount', '0.00') }}" placeholder="0.00" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Category</label>
                        <select name="category" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                            <option value="" disabled {{ old('category') ? '' : 'selected' }}>Select a category</option>
                            <option value="Operational Expenses" {{ old('category') == 'Operational Expenses' ? 'selected' : '' }}>Operational Expenses</option>
                            <option value="Interest Expense on Savings" {{ old('category') == 'Interest Expense on Savings' ? 'selected' : '' }}>Interest Expense on Savings</option>
                            <option value="Marketing" {{ old('category') == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                            <option value="Software/IT" {{ old('category') == 'Software/IT' ? 'selected' : '' }}>Software/IT</option>
                            <option value="Utilities" {{ old('category') == 'Utilities' ? 'selected' : '' }}>Utilities</option>
                            <option value="Other" {{ old('category') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Date</label>
                        <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">
                    </div>

                </div>
            </div>

            <div class="pt-6 flex justify-end gap-3">
                <a href="{{ route('expenses.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 rounded hover:bg-slate-200 dark:hover:bg-slate-700 transition">Cancel</a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-sacco rounded hover:bg-sacco-dark transition">Record Expense</button>
            </div>
        </form>
    </div>
</div>
@endsection
