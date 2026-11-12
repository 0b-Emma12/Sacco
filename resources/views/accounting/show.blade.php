@extends('layouts.app')

@section('title', 'Journal Entry Details')

@section('content')
@php
    $totalDebit = 0;
    $totalCredit = 0;
    foreach ($journal->lineItems as $line) {
        $totalDebit += $line->debit;
        $totalCredit += $line->credit;
    }
    $isBalanced = abs($totalDebit - $totalCredit) <= 0.001;
@endphp
<div class="mx-auto w-full max-w-7xl flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div class="flex items-start gap-3">
            <a href="{{ route('accounting.index') }}" class="mt-1 p-2 bg-white rounded-xl border border-slate-200 text-slate-500 hover:text-[#38CE3C] hover:border-[#38CE3C]/30 hover:bg-[#38CE3C]/5 transition dark:bg-[#181824] dark:border-[#272738] print-hidden" title="Back to ledger">
                <x-icon name="arrow-left" class="w-4 h-4"/>
            </a>
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Journal entry</div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">{{ $journal->reference }}</h1>
                <p class="text-sm text-slate-500 mt-1">Posted {{ $journal->posted_at->format('d M Y') }} at {{ $journal->posted_at->format('H:i') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 print-hidden">
            <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" class="h-10 px-4 inline-flex items-center gap-2 rounded-xl bg-[#181824] text-white text-sm font-semibold hover:bg-slate-700 transition">
                <x-icon name="printer" class="w-4 h-4"/> Print A4
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 flex flex-col gap-6">
            <x-panel title="Journal particulars" subtitle="Reference, posting details and the entry description." icon="book-open">
                <dl class="grid gap-x-6 gap-y-5 p-5 sm:p-6 sm:grid-cols-2 text-sm">
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Reference</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-100">{{ $journal->reference }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Date &amp; time</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-100">{{ $journal->posted_at->format('F d, Y h:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Posted by</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-100">{{ $journal->posted_by_name ?? 'System' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Line items</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-100">{{ number_format($journal->lineItems->count()) }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Description</dt>
                        <dd class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $journal->description }}</dd>
                    </div>
                </dl>
            </x-panel>

            <x-panel title="Line items" subtitle="Debit and credit postings for this journal entry." icon="file-text" tone="purple">
                <div class="w-full overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Account</th>
                                <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Debit</th>
                                <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Credit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($journal->lineItems as $line)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                                                {{ $line->account_code }}
                                            </span>
                                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ $line->account_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right text-sm font-bold text-[#38CE3C]">
                                        {{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right text-sm font-bold text-[#FF4D6B]">
                                        {{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50/50 dark:bg-slate-950/50 border-t border-slate-200 dark:border-slate-700">
                            <tr>
                                <td class="px-5 py-4 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total</td>
                                <td class="px-5 py-4 text-right text-sm font-extrabold text-[#38CE3C]">{{ number_format($totalDebit, 2) }}</td>
                                <td class="px-5 py-4 text-right text-sm font-extrabold text-[#FF4D6B]">{{ number_format($totalCredit, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if(! $isBalanced)
                    <div class="m-5 p-4 text-sm text-[#FF4D6B] bg-[#FF4D6B]/10 rounded-xl border border-[#FF4D6B]/20 flex items-center gap-2 font-semibold">
                        <x-icon name="alert-triangle" class="w-5 h-5"/>
                        <strong>Warning:</strong> This journal entry is unbalanced!
                    </div>
                @else
                    <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2 text-xs font-bold text-[#38CE3C]">
                        <x-icon name="check-circle" class="w-4 h-4"/>
                        Balanced Entry
                    </div>
                @endif
            </x-panel>
        </div>

        <div class="flex flex-col gap-6">
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 dark:bg-[#181824] dark:border-[#272738]">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">Journal value</span>
                    <span class="p-2 rounded-xl bg-[#8E32E9]/10 text-[#8E32E9]">
                        <x-icon name="book-open" class="w-4 h-4"/>
                    </span>
                </div>
                <div class="mt-3 text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    UGX {{ number_format($totalDebit, 2) }}
                </div>
                <div class="mt-3">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $isBalanced ? 'bg-[#38CE3C]/10 text-[#38CE3C] border-[#38CE3C]/20' : 'bg-[#FF4D6B]/10 text-[#FF4D6B] border-[#FF4D6B]/20' }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $isBalanced ? 'bg-[#38CE3C]' : 'bg-[#FF4D6B]' }}"></span>
                        {{ $isBalanced ? 'Balanced entry' : 'Out of balance by UGX '.number_format(abs($totalDebit - $totalCredit), 2) }}
                    </span>
                </div>
            </div>

            <x-panel title="Document trail" subtitle="Print or return to the register." icon="printer" tone="purple">
                <div class="p-5 space-y-2">
                    <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition dark:bg-slate-900/50 dark:hover:bg-slate-800/50 print-hidden">
                        <span class="flex items-center gap-2.5 text-sm font-medium text-slate-700 dark:text-slate-200">
                            <x-icon name="printer" class="w-4 h-4 text-[#8E32E9]"/> Print journal entry
                        </span>
                        <x-icon name="arrow-right" class="w-4 h-4 text-slate-400"/>
                    </a>
                    <a href="{{ route('accounting.index') }}" class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition dark:bg-slate-900/50 dark:hover:bg-slate-800/50 print-hidden">
                        <span class="flex items-center gap-2.5 text-sm font-medium text-slate-700 dark:text-slate-200">
                            <x-icon name="book-open" class="w-4 h-4 text-[#38CE3C]"/> General ledger
                        </span>
                        <x-icon name="arrow-right" class="w-4 h-4 text-slate-400"/>
                    </a>
                </div>
            </x-panel>
        </div>
    </div>
</div>
@endsection
