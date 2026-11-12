@extends('layouts.app')
@section('title', 'New Petty Cash Voucher')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-3xl">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Treasury / F05</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">New Petty Cash Voucher</h1>
        </div>
        <a href="{{ route('treasury.petty-cash.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Back</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 p-6 md:p-8">
        @if ($errors->any())
            <div class="bg-[#FF4D6B]/10 text-[#FF4D6B] border border-[#FF4D6B]/20 p-4 rounded-xl mb-6 text-sm font-semibold">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('treasury.petty-cash.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-md space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Date</label>
                        <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Amount (UGX)</label>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Paid to</label>
                        <input type="text" name="paid_to" value="{{ old('paid_to') }}" maxlength="150" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Purpose</label>
                        <input type="text" name="purpose" value="{{ old('purpose') }}" maxlength="255" placeholder="E.g. Office cleaning supplies" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-start gap-3">
                            <input type="hidden" name="receipt_attached" value="0">
                            <input type="checkbox" name="receipt_attached" value="1" {{ old('receipt_attached') ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 text-sacco focus:ring-sacco">
                            <span class="text-sm text-slate-700 dark:text-slate-300">Receipt attached</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="pt-2 flex justify-end gap-3">
                <a href="{{ route('treasury.petty-cash.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 rounded hover:bg-slate-200 transition">Cancel</a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-sacco rounded hover:bg-sacco-dark transition">Submit Voucher</button>
            </div>
        </form>
    </div>
</div>
@endsection
