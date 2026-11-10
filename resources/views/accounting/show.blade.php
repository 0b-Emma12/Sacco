@extends('layouts.app')

@section('title', 'Journal Entry Details')

@section('content')
<div class="mx-auto w-full max-w-4xl flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Journal Entry: {{ $journal->reference }}</h1>
            <p class="text-sm text-slate-500 mt-1">View details of the transaction.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('accounting.index') }}" class="flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition font-medium dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back to Ledger
            </a>
            <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-[#8E32E9]/10 text-[#8E32E9] rounded-xl hover:bg-[#8E32E9]/20 transition font-semibold text-xs dark:bg-[#8E32E9]/20 dark:text-[#8E32E9]">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Print
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden dark:bg-slate-900 dark:border-slate-800 p-6 md:p-8">
        
        <div class="flex flex-col md:flex-row justify-between mb-8 pb-8 border-b border-slate-100 dark:border-slate-800">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wider mb-1">Reference</p>
                <p class="font-medium text-slate-800 dark:text-slate-200 text-lg">{{ $journal->reference }}</p>
            </div>
            <div class="mt-4 md:mt-0">
                <p class="text-sm text-slate-500 uppercase tracking-wider mb-1">Date & Time</p>
                <p class="font-medium text-slate-800 dark:text-slate-200">{{ $journal->posted_at->format('F d, Y h:i A') }}</p>
            </div>
            <div class="mt-4 md:mt-0">
                <p class="text-sm text-slate-500 uppercase tracking-wider mb-1">Posted By</p>
                <p class="font-medium text-slate-800 dark:text-slate-200">{{ $journal->posted_by_name ?? 'System' }}</p>
            </div>
        </div>

        <div class="mb-8">
            <p class="text-sm text-slate-500 uppercase tracking-wider mb-1">Description</p>
            <p class="text-slate-700 dark:text-slate-300">{{ $journal->description }}</p>
        </div>

        <div>
            <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200 mb-4">Line Items</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Account</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Debit</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Credit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @php
                            $totalDebit = 0;
                            $totalCredit = 0;
                        @endphp
                        @foreach($journal->lineItems as $line)
                            @php
                                $totalDebit += $line->debit;
                                $totalCredit += $line->credit;
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 mr-2">
                                        {{ $line->account_code }}
                                    </span>
                                    <span class="text-sm text-slate-600 dark:text-slate-300">{{ $line->account_name }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-semibold text-[#38CE3C]">
                                    {{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-semibold text-[#FF4D6B]">
                                    {{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 dark:bg-slate-800/80 font-medium">
                            <td class="px-4 py-4 text-right text-sm text-slate-700 dark:text-slate-300 uppercase tracking-wider font-bold">Total</td>
                            <td class="px-4 py-4 text-right text-sm font-bold text-[#38CE3C] border-t-2 border-slate-200 dark:border-slate-700">
                                {{ number_format($totalDebit, 2) }}
                            </td>
                            <td class="px-4 py-4 text-right text-sm font-bold text-[#FF4D6B] border-t-2 border-slate-200 dark:border-slate-700">
                                {{ number_format($totalCredit, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            @if(abs($totalDebit - $totalCredit) > 0.001)
                <div class="mt-4 p-4 text-sm text-[#FF4D6B] bg-[#FF4D6B]/10 rounded-xl border border-[#FF4D6B]/20 flex items-center gap-2 font-semibold">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    <strong>Warning:</strong> This journal entry is unbalanced!
                </div>
            @else
                <div class="mt-4 flex items-center justify-end gap-2 text-xs font-bold text-[#38CE3C]">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    Balanced Entry
                </div>
            @endif
        </div>
        
    </div>
</div>
@endsection
