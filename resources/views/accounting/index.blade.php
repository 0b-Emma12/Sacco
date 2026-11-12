@extends('layouts.app')
@section('title', 'General Ledger')
@section('content')
@php
    $isBalanced = abs($totalDebit - $totalCredit) < 0.01;
@endphp
<div class="mx-auto w-full max-w-7xl flex flex-col gap-6">
    @if(session('success'))
        <div class="p-4 text-sm font-semibold text-[#38CE3C] bg-[#38CE3C]/10 border border-[#38CE3C]/20 rounded-xl print-hidden" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <x-page-header
        eyebrow="Accounting"
        title="General Ledger"
        subtitle="Double-entry records and the live trial balance across every SACCO account.">
        <a href="{{ route('accounting.create') }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold shadow-sm inline-flex items-center gap-2 transition">
            <x-icon name="plus" class="w-4 h-4"/>
            New entry
        </a>
        <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2 transition print-hidden">
            <x-icon name="printer" class="w-4 h-4"/>
            Print GL
        </a>
    </x-page-header>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Ledger accounts" :value="number_format(count($trialBalance))" meta="Accounts with posted balances" icon="book-open" tone="blue"/>
        <x-stat-card label="Total debits" :value="'UGX '.number_format($totalDebit, 2)" meta="Debit side of the trial balance" icon="arrow-down-left" tone="green"/>
        <x-stat-card label="Total credits" :value="'UGX '.number_format($totalCredit, 2)" meta="Credit side of the trial balance" icon="arrow-up-right" tone="coral"/>
        <x-stat-card label="Journal entries" :value="number_format($journals->total())" meta="Posted to the register" icon="file-text" tone="purple"/>
    </div>

    <!-- Trial Balance Summary -->
    <x-panel title="Trial Balance Snapshot" subtitle="Chart of accounts balances, debit and credit side." icon="book-open">
        <x-slot name="actions">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $isBalanced ? 'bg-[#38CE3C]/10 text-[#38CE3C] border-[#38CE3C]/20' : 'bg-[#FF4D6B]/10 text-[#FF4D6B] border-[#FF4D6B]/20' }}">
                <span class="h-1.5 w-1.5 rounded-full {{ $isBalanced ? 'bg-[#38CE3C]' : 'bg-[#FF4D6B]' }}"></span>
                {{ $isBalanced ? 'Live status: Balanced' : 'Live status: Unbalanced' }}
            </span>
        </x-slot>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-left whitespace-nowrap">
                <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Account code</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Account name</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Debit balance</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Credit balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($trialBalance as $account)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4 text-sm font-mono text-slate-500 dark:text-slate-400">{{ $account['code'] }}</td>
                        <td class="px-5 py-4 text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $account['name'] }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">{{ $account['type'] }}</span>
                        </td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-[#38CE3C]">
                            {{ $account['debit_total'] > 0 ? number_format($account['debit_total'], 2) : '—' }}
                        </td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-[#FF4D6B]">
                            {{ $account['credit_total'] > 0 ? number_format($account['credit_total'], 2) : '—' }}
                        </td>
                    </tr>
                    @empty
                    <x-empty-state colspan="5" icon="book-open" title="No accounts with balances"
                                   message="Post a journal entry and the chart of accounts will populate this snapshot.">
                        <a href="{{ route('accounting.create') }}" class="h-9 px-4 rounded-lg bg-[#38CE3C] text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-[#2cb030] transition">
                            <x-icon name="plus" class="w-3.5 h-3.5"/> New entry
                        </a>
                    </x-empty-state>
                    @endforelse
                </tbody>
                @if(count($trialBalance) > 0)
                <tfoot class="bg-slate-50/50 dark:bg-slate-950/50 border-t border-slate-200 dark:border-slate-700">
                    <tr>
                        <td colspan="3" class="px-5 py-4 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider">Totals</td>
                        <td class="px-5 py-4 text-right text-sm font-extrabold text-[#38CE3C]">UGX {{ number_format($totalDebit, 2) }}</td>
                        <td class="px-5 py-4 text-right text-sm font-extrabold text-[#FF4D6B]">UGX {{ number_format($totalCredit, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-panel>

    <!-- Recent Journal Entries -->
    <x-panel title="Recent journal entries" subtitle="Newest postings first, ten per page." icon="arrow-left-right" tone="purple">
        @if($journals->isEmpty())
            <x-empty-state icon="book-open" title="No journal entries found"
                           message="When financial transactions like deposits or loans occur, they will automatically appear here as balanced journal entries.">
                <a href="{{ route('accounting.create') }}" class="h-9 px-4 rounded-lg bg-[#38CE3C] text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-[#2cb030] transition">
                    <x-icon name="plus" class="w-3.5 h-3.5"/> Post first entry
                </a>
            </x-empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Reference &amp; description</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Account</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Debit</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Credit</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right print-hidden">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($journals as $journal)
                            @foreach($journal->lineItems as $index => $line)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors {{ $index === 0 ? 'border-t border-slate-200 dark:border-slate-700' : '' }}">
                                    @if($index === 0)
                                        <td class="px-5 py-4 whitespace-nowrap align-top" rowspan="{{ $journal->lineItems->count() }}">
                                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $journal->posted_at->format('d M Y') }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $journal->posted_at->format('H:i') }}</div>
                                        </td>
                                        <td class="px-5 py-4 align-top max-w-72" rowspan="{{ $journal->lineItems->count() }}">
                                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $journal->reference }}</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 whitespace-pre-wrap">{{ $journal->description }}</div>
                                        </td>
                                    @endif
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
                                    @if($index === 0)
                                        <td class="px-5 py-4 whitespace-nowrap text-right align-top print-hidden" rowspan="{{ $journal->lineItems->count() }}">
                                            <a href="{{ route('accounting.show', $journal->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] text-xs font-semibold hover:bg-[#38CE3C]/20 transition">View</a>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($journals->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 print-hidden">
                {{ $journals->links() }}
            </div>
            @endif
        @endif
    </x-panel>
</div>
@endsection
