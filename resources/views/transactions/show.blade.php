@extends('layouts.app')
@section('title', 'Transaction Details')
@section('content')
<div class="mx-auto w-full max-w-2xl flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('transactions.index') }}" class="p-2 bg-white rounded-xl border border-slate-200 text-slate-500 hover:text-[#38CE3C] hover:border-[#38CE3C]/30 hover:bg-[#38CE3C]/5 transition print-hidden">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Transaction Receipt</h1>
                <p class="text-sm text-slate-500 mt-1 print-hidden">View transaction information.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('transactions.receipt', $transaction->id) }}" class="flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-700 border border-slate-200 text-sm font-semibold rounded-xl hover:bg-slate-200 transition print-hidden">
                <i data-lucide="receipt" class="w-4 h-4"></i>
                Thermal
            </a>
            <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-[#181824] text-white text-sm font-semibold rounded-xl hover:bg-slate-700 transition print-hidden">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Print A4
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8 dark:bg-slate-900 dark:border-slate-800">
        <!-- Transaction Type Badge -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Reference</div>
                <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $transaction->reference }}</div>
            </div>
            @if($transaction->transaction_type === 'withdrawal')
                <span class="px-3 py-1.5 text-sm font-semibold rounded-xl bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20">Withdrawal</span>
            @elseif($transaction->transaction_type === 'share_transfer')
                <span class="px-3 py-1.5 text-sm font-semibold rounded-xl bg-[#8E32E9]/15 text-[#8E32E9] border border-[#8E32E9]/20">Share Transfer</span>
            @else
                <span class="px-3 py-1.5 text-sm font-semibold rounded-xl bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20">{{ ucfirst($transaction->transaction_type) }}</span>
            @endif
        </div>

        <dl class="grid gap-5 text-sm sm:grid-cols-2 border-t border-slate-100 dark:border-slate-800 pt-5">
            <div>
                <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Account Number</dt>
                <dd class="font-semibold text-slate-800 dark:text-slate-100">{{ $transaction->account_number }}</dd>
            </div>
            <div>
                <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Amount</dt>
                <dd class="font-bold text-lg {{ $transaction->transaction_type === 'withdrawal' ? 'text-[#FF4D6B]' : 'text-[#38CE3C]' }}">
                    {{ $transaction->transaction_type === 'withdrawal' ? '-' : '+' }} UGX {{ number_format($transaction->amount, 2) }}
                </dd>
            </div>
            <div>
                <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Processed by</dt>
                <dd class="text-slate-800 dark:text-slate-100">{{ $transaction->performed_by_username ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Date and Time</dt>
                <dd class="text-slate-800 dark:text-slate-100">{{ $transaction->created_at->format('Y-m-d H:i') }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Narration</dt>
                <dd class="whitespace-pre-wrap text-slate-800 dark:text-slate-100">{{ $transaction->narration ?: '—' }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection
