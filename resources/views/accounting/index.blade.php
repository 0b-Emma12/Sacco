@extends('layouts.app')
@section('title', 'General Ledger')
@section('content')
<div class="mx-auto w-full max-w-6xl flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">General Ledger</h1>
            <p class="text-sm text-slate-500 mt-1">View all double-entry accounting records across the SACCO.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('accounting.create') }}" class="flex items-center gap-2 px-4 py-2 bg-[#38CE3C] hover:bg-[#2cb030] text-white rounded-xl shadow-xs transition font-semibold text-sm">
                <i data-lucide="plus" class="w-4 h-4"></i>
                New Entry
            </a>
            <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-[#8E32E9]/10 text-[#8E32E9] rounded-xl hover:bg-[#8E32E9]/20 transition font-semibold text-sm dark:bg-[#8E32E9]/20 dark:text-[#8E32E9] print-hidden">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Print GL
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm font-semibold text-[#38CE3C] bg-[#38CE3C]/10 border border-[#38CE3C]/20 rounded-xl print-hidden" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <!-- Trial Balance Summary -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden dark:bg-[#181824] dark:border-[#272738] mb-2">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Trial Balance Snapshot</h3>
            <div class="text-xs px-3 py-1 bg-slate-100 text-slate-600 rounded-full dark:bg-slate-800 dark:text-slate-300 font-semibold">
                Live Status: 
                @if(abs($totalDebit - $totalCredit) < 0.01)
                    <span class="text-[#38CE3C] font-bold ml-1">Balanced</span>
                @else
                    <span class="text-[#FF4D6B] font-bold ml-1">Unbalanced</span>
                @endif
            </div>
        </div>
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/50 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Account Code</th>
                        <th class="px-6 py-4 font-semibold">Account Name</th>
                        <th class="px-6 py-4 font-semibold">Type</th>
                        <th class="px-6 py-4 font-semibold text-right">Debit Balance</th>
                        <th class="px-6 py-4 font-semibold text-right">Credit Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($trialBalance as $account)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-3 text-slate-600 font-medium">{{ $account['code'] }}</td>
                        <td class="px-6 py-3 text-slate-800 dark:text-slate-200">{{ $account['name'] }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ $account['type'] }}</td>
                        <td class="px-6 py-3 text-right font-medium text-[#38CE3C]">
                            {{ $account['debit_total'] > 0 ? number_format($account['debit_total'], 2) : '-' }}
                        </td>
                        <td class="px-6 py-3 text-right font-medium text-[#FF4D6B]">
                            {{ $account['credit_total'] > 0 ? number_format($account['credit_total'], 2) : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">No active accounts with balances found.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($trialBalance) > 0)
                <tfoot class="bg-slate-50 dark:bg-slate-950/50 border-t-2 border-slate-200 dark:border-slate-700">
                    <tr>
                        <td colspan="3" class="px-6 py-4 font-bold text-right text-slate-700 dark:text-slate-300">TOTALS:</td>
                        <td class="px-6 py-4 font-bold text-right text-[#38CE3C]">UGX {{ number_format($totalDebit, 2) }}</td>
                        <td class="px-6 py-4 font-bold text-right text-[#FF4D6B]">UGX {{ number_format($totalCredit, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Recent Journal Entries -->
    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mt-4 mb-2">Recent Journal Entries</h3>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden dark:bg-slate-900 dark:border-slate-800">
        @if($journals->isEmpty())
            <div class="p-12 flex flex-col items-center justify-center text-center">
                <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 dark:bg-slate-800 text-slate-400">
                    <i data-lucide="book-open" class="w-8 h-8"></i>
                </div>
                <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-200">No Journal Entries Found</h3>
                <p class="text-slate-500 mt-1 max-w-sm">When financial transactions like deposits or loans occur, they will automatically appear here as balanced journal entries.</p>
                <a href="{{ route('accounting.create') }}" class="mt-4 px-4 py-2 bg-sacco text-white rounded-lg hover:bg-sacco-dark transition font-medium text-sm">Post First Entry</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-950/50 border-b border-slate-100 dark:border-slate-800">
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Reference & Description</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Account</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Debit</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Credit</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right print-hidden">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                        @foreach($journals as $journal)
                            @foreach($journal->lineItems as $index => $line)
                                <tr class="{{ $index === 0 ? 'bg-white dark:bg-slate-900 border-t-4 border-slate-100 dark:border-slate-800/50' : 'bg-slate-50/30 dark:bg-slate-900/50' }} hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                    @if($index === 0)
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300 align-top" rowspan="{{ $journal->lineItems->count() }}">
                                            {{ $journal->posted_at->format('M d, Y') }}
                                            <div class="text-xs text-slate-400 mt-0.5">{{ $journal->posted_at->format('H:i') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap align-top" rowspan="{{ $journal->lineItems->count() }}">
                                            <div class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $journal->reference }}</div>
                                            <div class="text-xs text-slate-500 mt-0.5 whitespace-pre-wrap">{{ $journal->description }}</div>
                                        </td>
                                    @endif
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 mr-2">
                                            {{ $line->account_code }}
                                        </span>
                                        <span class="text-sm text-slate-600 dark:text-slate-300">{{ $line->account_name }}</span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-right text-sm font-medium text-[#38CE3C]">
                                        {{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-right text-sm font-medium text-[#FF4D6B]">
                                        {{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}
                                    </td>
                                    @if($index === 0)
                                        <td class="px-6 py-3 whitespace-nowrap text-right text-sm font-medium align-top print-hidden" rowspan="{{ $journal->lineItems->count() }}">
                                            <a href="{{ route('accounting.show', $journal->id) }}" class="text-sacco hover:underline">View</a>
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
    </div>
</div>
@endsection
