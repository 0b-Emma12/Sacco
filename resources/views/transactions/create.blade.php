@extends('layouts.app')
@section('title', 'Transactions - Create')
@section('content')
<div class="mx-auto w-full max-w-2xl flex flex-col gap-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('transactions.index') }}" class="p-2 bg-white rounded-lg border border-slate-200 text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition">
            <x-icon name="arrow-left" class="w-5 h-5"/>
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
                <label for="account_number" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Account Number</label>
                <input type="text" name="account_number" id="account_number" value="{{ old('account_number', request('account_number')) }}" class="w-full h-11 px-4 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100" placeholder="Enter account number" required>
                @error('account_number')
                    <p class="text-xs font-semibold text-[#FF4D6B] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label for="transaction_type" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Transaction Type</label>
                <select name="transaction_type" id="transaction_type" class="w-full h-11 px-4 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100" required>
                    <option value="" disabled {{ !old('transaction_type') && !request('type') ? 'selected' : '' }}>Select transaction type</option>
                    <option value="deposit" {{ old('transaction_type', request('type')) == 'deposit' ? 'selected' : '' }}>Deposit</option>
                    <option value="withdrawal" {{ old('transaction_type', request('type')) == 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                </select>
                @error('transaction_type')
                    <p class="text-xs font-semibold text-[#FF4D6B] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label for="amount" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Amount (UGX)</label>
                <input type="number" name="amount" id="amount" value="{{ old('amount') }}" step="0.01" min="1" class="w-full h-11 px-4 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100" placeholder="Enter amount" required>
                @error('amount')
                    <p class="text-xs font-semibold text-[#FF4D6B] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label for="narration" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Narration</label>
                <input type="text" name="narration" id="narration" value="{{ old('narration') }}" class="w-full h-11 px-4 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100" placeholder="Enter narration">
                @error('narration')
                    <p class="text-xs font-semibold text-[#FF4D6B] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="payment_method" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="w-full h-11 px-4 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">
                        @foreach(['CASH' => 'Cash', 'MOBILE_MONEY' => 'Mobile Money', 'BANK_TRANSFER' => 'Bank Transfer', 'CHEQUE' => 'Cheque'] as $value => $label)
                            <option value="{{ $value }}" {{ old('payment_method', 'CASH') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('payment_method')
                        <p class="text-xs font-semibold text-[#FF4D6B] mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="space-y-2">
                    <label for="external_reference" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">External Reference</label>
                    <input type="text" name="external_reference" id="external_reference" value="{{ old('external_reference') }}" class="w-full h-11 px-4 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100" placeholder="MoMo / bank slip / cheque no.">
                    @error('external_reference')
                        <p class="text-xs font-semibold text-[#FF4D6B] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-x-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('transactions.index') }}" class="h-11 px-6 flex items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 font-semibold transition dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Cancel</a>
                <button type="submit" class="h-11 px-6 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white font-bold shadow-md shadow-[#38CE3C]/20 transition">Save Transaction</button>
            </div>
        </form>
    </div>
</div>
@endsection
