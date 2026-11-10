@extends('layouts.app')
@section('title', 'Transaction Details')
@section('content')
<div class="mx-auto w-full max-w-2xl flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('transactions.index') }}" class="p-2 bg-white rounded-lg border border-slate-200 text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition print-hidden">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Transaction Receipt</h1>
                <p class="text-sm text-slate-500 mt-1 print-hidden">View transaction information.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('transactions.receipt', $transaction->id) }}" class="flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-700 border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-200 transition print-hidden">
                <i data-lucide="receipt" class="w-4 h-4"></i>
                Thermal
            </a>
            <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition print-hidden">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Print A4
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8 dark:bg-slate-900 dark:border-slate-800">
        <dl class="grid gap-4 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-slate-500">Reference</dt>
                <dd class="mt-1 font-medium text-slate-800 dark:text-slate-100">{{ $transaction->reference }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Transaction type</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-100">{{ ucwords(str_replace('_', ' ', $transaction->transaction_type)) }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Account number</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-100">{{ $transaction->account_number }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Amount</dt>
                <dd class="mt-1 font-medium text-slate-800 dark:text-slate-100">UGX {{ number_format($transaction->amount, 2) }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Processed by</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-100">{{ $transaction->performed_by_username ?: 'â€”' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Date and time</dt>
                <dd class="mt-1 text-slate-800 dark:text-slate-100">{{ $transaction->created_at->format('Y-m-d H:i') }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-slate-500">Narration</dt>
                <dd class="mt-1 whitespace-pre-wrap text-slate-800 dark:text-slate-100">{{ $transaction->narration ?: 'â€”' }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection
