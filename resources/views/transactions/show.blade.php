@extends('layouts.app')
@section('title', 'Transaction Details')
@section('content')
@php
    $isCredit = in_array($transaction->transaction_type, ['deposit', 'dividend', 'loan_disbursement'], true);
    $badges = [
        'deposit' => 'bg-[#38CE3C]/15 text-[#38CE3C] border-[#38CE3C]/20',
        'withdrawal' => 'bg-[#FF4D6B]/15 text-[#FF4D6B] border-[#FF4D6B]/20',
        'share_transfer' => 'bg-[#8E32E9]/15 text-[#8E32E9] border-[#8E32E9]/20',
        'dividend' => 'bg-[#FFDE73]/30 text-amber-700 border-[#FFDE73]/40 dark:text-amber-300',
        'loan_disbursement' => 'bg-blue-500/15 text-blue-600 border-blue-500/20 dark:text-blue-400',
        'loan_repayment' => 'bg-teal-500/15 text-teal-600 border-teal-500/20 dark:text-teal-400',
    ];
    $badge = $badges[$transaction->transaction_type] ?? 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
@endphp
<div class="mx-auto w-full max-w-6xl flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div class="flex items-start gap-3">
            <a href="{{ route('transactions.index') }}" class="mt-1 p-2 bg-white rounded-xl border border-slate-200 text-slate-500 hover:text-[#38CE3C] hover:border-[#38CE3C]/30 hover:bg-[#38CE3C]/5 transition print-hidden" title="Back to transactions">
                <x-icon name="arrow-left" class="w-4 h-4"/>
            </a>
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Transaction</div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">{{ $transaction->reference }}</h1>
                <p class="text-sm text-slate-500 mt-1">Posted {{ $transaction->created_at->format('d M Y') }} at {{ $transaction->created_at->format('H:i') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 print-hidden">
            <a href="{{ route('transactions.receipt', $transaction->id) }}" class="h-10 px-4 inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200">
                <x-icon name="receipt" class="w-4 h-4"/> Thermal receipt
            </a>
            <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" class="h-10 px-4 inline-flex items-center gap-2 rounded-xl bg-[#181824] text-white text-sm font-semibold hover:bg-slate-700 transition">
                <x-icon name="printer" class="w-4 h-4"/> Print A4
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 flex flex-col gap-6">
            <x-panel title="Transaction particulars" subtitle="Posted value and the account it was applied to." icon="arrow-left-right">
                <dl class="grid gap-x-6 gap-y-5 p-5 sm:p-6 sm:grid-cols-2 text-sm">
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Reference</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-100">{{ $transaction->reference }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Account number</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-100">{{ $transaction->account_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Transaction type</dt>
                        <dd>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $badge }}">
                                {{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Amount</dt>
                        <dd class="text-lg font-extrabold {{ $isCredit ? 'text-[#38CE3C]' : 'text-[#FF4D6B]' }}">
                            {{ $isCredit ? '+' : '-' }} UGX {{ number_format($transaction->amount, 2) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Served by</dt>
                        <dd class="text-slate-800 dark:text-slate-100">{{ $transaction->performed_by_username ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Date and time</dt>
                        <dd class="text-slate-800 dark:text-slate-100">{{ $transaction->created_at->format('d M Y, H:i') }}</dd>
                    </div>
                </dl>
            </x-panel>

            <x-panel title="Narration" subtitle="What the posting was for." icon="file-text" tone="slate">
                <div class="p-5 sm:p-6">
                    <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $transaction->narration ?: 'No narration was recorded for this posting.' }}</p>
                </div>
            </x-panel>
        </div>

        <div class="flex flex-col gap-6">
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 dark:bg-[#181824] dark:border-[#272738]">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">Posted amount</span>
                    <span class="p-2 rounded-xl {{ $isCredit ? 'bg-[#38CE3C]/10 text-[#38CE3C]' : 'bg-[#FF4D6B]/10 text-[#FF4D6B]' }}">
                        <x-icon :name="$isCredit ? 'arrow-down-circle' : 'arrow-up-circle'" class="w-4 h-4"/>
                    </span>
                </div>
                <div class="mt-3 text-2xl font-extrabold {{ $isCredit ? 'text-[#38CE3C]' : 'text-[#FF4D6B]' }} tracking-tight">
                    {{ $isCredit ? '+' : '-' }} UGX {{ number_format($transaction->amount, 2) }}
                </div>
                <p class="text-[11px] font-medium text-slate-500 mt-1">{{ $isCredit ? 'Credited to the member account' : 'Debited from the member account' }}</p>
            </div>

            <x-panel title="Document trail" subtitle="Print or reprint from the register." icon="printer" tone="purple">
                <div class="p-5 space-y-2">
                    <a href="{{ route('transactions.receipt', $transaction->id) }}" class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition dark:bg-slate-900/50 dark:hover:bg-slate-800/50 print-hidden">
                        <span class="flex items-center gap-2.5 text-sm font-medium text-slate-700 dark:text-slate-200">
                            <x-icon name="receipt" class="w-4 h-4 text-[#8E32E9]"/> Thermal receipt
                        </span>
                        <x-icon name="arrow-right" class="w-4 h-4 text-slate-400"/>
                    </a>
                    <a href="{{ route('prints.statements') }}" class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition dark:bg-slate-900/50 dark:hover:bg-slate-800/50 print-hidden">
                        <span class="flex items-center gap-2.5 text-sm font-medium text-slate-700 dark:text-slate-200">
                            <x-icon name="file-text" class="w-4 h-4 text-[#38CE3C]"/> Member statements
                        </span>
                        <x-icon name="arrow-right" class="w-4 h-4 text-slate-400"/>
                    </a>
                </div>
            </x-panel>
        </div>
    </div>
</div>
@endsection
