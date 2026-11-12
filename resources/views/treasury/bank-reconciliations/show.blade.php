@extends('layouts.app')
@section('title', 'Reconciliation '.$bankReconciliation->account_number)

@section('content')
<div class="mx-auto w-full max-w-6xl flex flex-col gap-6">
    <x-page-header
        eyebrow="Treasury / F04"
        title="{{ $bankReconciliation->bank_name }} · {{ $bankReconciliation->account_number }}"
        subtitle="{{ $bankReconciliation->period_from->format('d M Y') }} – {{ $bankReconciliation->period_to->format('d M Y') }}">
        <a href="{{ route('treasury.bank-reconciliations.index') }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2">
            <x-icon name="arrow-left" class="w-4 h-4"/> Back
        </a>
    </x-page-header>

    @if (session('success'))
        <div class="p-4 text-sm text-[#38CE3C] font-semibold bg-[#38CE3C]/10 border border-[#38CE3C]/20 rounded-xl" role="alert">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="p-4 text-sm text-[#FF4D6B] bg-[#FF4D6B]/10 border border-[#FF4D6B]/20 rounded-xl" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Balance per statement" :value="'UGX '.number_format($bankReconciliation->statement_balance, 2)" meta="Bank statement closing balance" icon="file-text" tone="slate"/>
        <x-stat-card label="Adjusted bank" :value="'UGX '.number_format($bankReconciliation->adjusted_bank_balance, 2)" meta="After deposits and cheques" icon="landmark" tone="green"/>
        <x-stat-card label="Adjusted book" :value="'UGX '.number_format($bankReconciliation->adjusted_book_balance, 2)" meta="After bank-only entries" icon="wallet" tone="blue"/>
        <x-stat-card label="Difference" :value="'UGX '.number_format($bankReconciliation->difference, 2)" :meta="$bankReconciliation->isBalanced() ? 'Balanced and ready for review' : 'Investigate before review'" icon="alert-triangle" :tone="$bankReconciliation->isBalanced() ? 'green' : 'coral'"/>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-panel title="Bank side" subtitle="Statement balance adjusted for timing items." icon="landmark" tone="blue">
            <dl class="grid gap-y-3 p-5 sm:p-6 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Balance per statement</dt><dd class="font-medium text-slate-800 dark:text-slate-100">{{ number_format($bankReconciliation->statement_balance, 2) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Add: deposits in transit</dt><dd class="font-medium text-slate-800 dark:text-slate-100">{{ number_format($bankReconciliation->deposits_in_transit, 2) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Less: unpresented cheques</dt><dd class="font-medium text-slate-800 dark:text-slate-100">({{ number_format($bankReconciliation->unpresented_cheques, 2) }})</dd></div>
                <div class="flex justify-between gap-4 border-t border-slate-100 dark:border-slate-800 pt-3"><dt class="font-semibold text-slate-700 dark:text-slate-200">Adjusted bank balance</dt><dd class="font-bold text-slate-900 dark:text-slate-100">UGX {{ number_format($bankReconciliation->adjusted_bank_balance, 2) }}</dd></div>
            </dl>
        </x-panel>

        <x-panel title="Book side" subtitle="Ledger balance adjusted for bank-only entries." icon="wallet" tone="purple">
            <dl class="grid gap-y-3 p-5 sm:p-6 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Balance per SACCO records</dt><dd class="font-medium text-slate-800 dark:text-slate-100">{{ number_format($bankReconciliation->book_balance, 2) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Add: bank credits not recorded</dt><dd class="font-medium text-slate-800 dark:text-slate-100">{{ number_format($bankReconciliation->bank_credits_not_recorded, 2) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Less: bank charges not recorded</dt><dd class="font-medium text-slate-800 dark:text-slate-100">({{ number_format($bankReconciliation->bank_charges_not_recorded, 2) }})</dd></div>
                <div class="flex justify-between gap-4 border-t border-slate-100 dark:border-slate-800 pt-3"><dt class="font-semibold text-slate-700 dark:text-slate-200">Adjusted book balance</dt><dd class="font-bold text-slate-900 dark:text-slate-100">UGX {{ number_format($bankReconciliation->adjusted_book_balance, 2) }}</dd></div>
            </dl>
        </x-panel>
    </div>

    <x-panel title="Difference" subtitle="Adjusted bank less adjusted book. It must be zero before review." icon="alert-triangle" :tone="$bankReconciliation->isBalanced() ? 'green' : 'amber'">
        <div class="p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Unreconciled amount</div>
                    <div class="text-2xl font-extrabold {{ $bankReconciliation->isBalanced() ? 'text-[#38CE3C]' : 'text-[#FF4D6B]' }} mt-1">UGX {{ number_format($bankReconciliation->difference, 2) }}</div>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $bankReconciliation->isReconciled() ? 'bg-[#38CE3C]/15 text-[#38CE3C] border-[#38CE3C]/20' : 'bg-[#FFDE73]/30 text-amber-700 border-[#FFDE73]/40 dark:text-amber-300' }}">{{ ucfirst(strtolower($bankReconciliation->status)) }}</span>
            </div>
            @if ($bankReconciliation->reconciliation_items)
                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Reconciliation items</div>
                    <p class="text-sm whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $bankReconciliation->reconciliation_items }}</p>
                </div>
            @endif
        </div>
    </x-panel>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-panel title="Sign-off" subtitle="Preparer and reviewer trail." icon="shield" tone="slate">
            <dl class="grid gap-y-3 p-5 sm:p-6 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd class="font-semibold {{ $bankReconciliation->isReconciled() ? 'text-[#38CE3C]' : 'text-amber-600' }}">{{ ucfirst(strtolower($bankReconciliation->status)) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Prepared by</dt><dd class="font-medium text-slate-800 dark:text-slate-100">{{ $bankReconciliation->prepared_by }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Reviewed by</dt><dd class="font-medium text-slate-800 dark:text-slate-100">{{ $bankReconciliation->reviewed_by ?: '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Reviewed at</dt><dd class="font-medium text-slate-800 dark:text-slate-100">{{ optional($bankReconciliation->reviewed_at)->format('d M Y H:i') ?: '—' }}</dd></div>
            </dl>
        </x-panel>

        @if (! $bankReconciliation->isReconciled() && Auth::user()->canApproveLoans())
        <x-panel title="Review reconciliation" subtitle="Review locks the statement record. A non-zero difference blocks review." icon="check-circle" tone="green">
            <div class="p-5 sm:p-6">
                <form action="{{ route('treasury.bank-reconciliations.review', $bankReconciliation) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full h-10 px-4 text-sm font-semibold text-white bg-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition disabled:opacity-40" {{ $bankReconciliation->isBalanced() ? '' : 'disabled' }}>Mark Reviewed</button>
                </form>
            </div>
        </x-panel>
        @endif
    </div>
</div>
@endsection
