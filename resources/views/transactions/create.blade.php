@extends('layouts.app')
@section('title', 'Transactions - Create')
@section('content')
<div class="mx-auto w-full max-w-2xl flex flex-col gap-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('transactions.index') }}" class="p-2 bg-white rounded-lg border border-slate-200 text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Create Transaction</h1>
            <p class="text-sm text-slate-500 mt-1">Record a new deposit or withdrawal.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8 dark:bg-slate-900 dark:border-slate-800">
        <form action="{{ route('transactions.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="space-y-2">
                <label for="account_number" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Account Number</label>
                <input type="text" name="account_number" id="account_number" value="{{ old('account_number', request('account_number')) }}" class="w-full h-11 px-4 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100" placeholder="Enter account number" required>
                @error('account_number')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label for="transaction_type" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Transaction Type</label>
                <select name="transaction_type" id="transaction_type" class="w-full h-11 px-4 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100" required>
                    <option value="" disabled {{ !old('transaction_type') && !request('type') ? 'selected' : '' }}>Select transaction type</option>
                    <option value="deposit" {{ old('transaction_type', request('type')) == 'deposit' ? 'selected' : '' }}>Deposit</option>
                    <option value="withdrawal" {{ old('transaction_type', request('type')) == 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                </select>
                @error('transaction_type')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label for="amount" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Amount</label>
                <input type="number" name="amount" id="amount" value="{{ old('amount') }}" step="0.01" min="1" class="w-full h-11 px-4 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100" placeholder="Enter amount" required>
                @error('amount')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label for="narration" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Narration</label>
                <input type="text" name="narration" id="narration" value="{{ old('narration') }}" class="w-full h-11 px-4 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100" placeholder="Enter narration">
                @error('narration')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-x-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('transactions.index') }}" class="h-11 px-6 flex items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-medium transition dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Cancel</a>
                <button type="submit" class="h-11 px-6 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition">Save Transaction</button>
            </div>
        </form>
    </div>
</div>
@endsection
