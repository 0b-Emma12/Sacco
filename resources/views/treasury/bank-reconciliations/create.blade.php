@extends('layouts.app')
@section('title', 'New Bank Reconciliation')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-5xl">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Treasury / F04</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">New Bank Reconciliation</h1>
        </div>
        <a href="{{ route('treasury.bank-reconciliations.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Back</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 p-6 md:p-8">
        @if ($errors->any())
            <div class="bg-[#FF4D6B]/10 text-[#FF4D6B] border border-[#FF4D6B]/20 p-4 rounded-xl mb-6 text-sm font-semibold">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('treasury.bank-reconciliations.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-md space-y-5">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">Statement details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Bank name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name') }}" maxlength="100" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Account number</label>
                        <input type="text" name="account_number" value="{{ old('account_number') }}" maxlength="50" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Statement period from</label>
                        <input type="date" name="period_from" value="{{ old('period_from') }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Statement period to</label>
                        <input type="date" name="period_to" value="{{ old('period_to', now()->toDateString()) }}" max="{{ now()->toDateString() }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Bank balance per statement (UGX)</label>
                        <input type="number" step="0.01" name="statement_balance" value="{{ old('statement_balance', '0.00') }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Book balance per SACCO records (UGX)</label>
                        <input type="number" step="0.01" name="book_balance" value="{{ old('book_balance', number_format($bookBalance, 2, '.', '')) }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                        <p class="text-[11px] text-slate-500 mt-1">Prefilled from ledger account 1000 Cash in Bank.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-md space-y-4">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">Bank side adjustments</h3>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Add: deposits in transit</label>
                        <input type="number" step="0.01" min="0" name="deposits_in_transit" value="{{ old('deposits_in_transit', '0.00') }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Less: unpresented cheques</label>
                        <input type="number" step="0.01" min="0" name="unpresented_cheques" value="{{ old('unpresented_cheques', '0.00') }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-md space-y-4">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">Book side adjustments</h3>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Add: bank credits not recorded</label>
                        <input type="number" step="0.01" min="0" name="bank_credits_not_recorded" value="{{ old('bank_credits_not_recorded', '0.00') }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Less: bank charges not recorded</label>
                        <input type="number" step="0.01" min="0" name="bank_charges_not_recorded" value="{{ old('bank_charges_not_recorded', '0.00') }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-md">
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Reconciliation items</label>
                <textarea name="reconciliation_items" rows="4" maxlength="5000" placeholder="List unmatched items with dates and amounts" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">{{ old('reconciliation_items') }}</textarea>
            </div>

            <div class="pt-2 flex justify-end gap-3">
                <a href="{{ route('treasury.bank-reconciliations.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 rounded hover:bg-slate-200 transition">Cancel</a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-sacco rounded hover:bg-sacco-dark transition">Save Reconciliation</button>
            </div>
        </form>
    </div>
</div>
@endsection
