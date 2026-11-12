@extends('layouts.app')
@section('title', 'Transactions')
@section('content')
<div class="mx-auto w-full max-w-7xl flex flex-col gap-6">
    @if(session('success'))
        <div class="p-4 text-sm text-[#38CE3C] font-semibold rounded-xl bg-[#38CE3C]/10 border border-[#38CE3C]/20" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <x-page-header
        eyebrow="Financial Operations"
        title="Transactions"
        subtitle="Every deposit, withdrawal and transfer posted to member accounts.">
        <a href="{{ route('transactions.create') }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold transition inline-flex items-center gap-2 shadow-sm">
            <x-icon name="plus-circle" class="w-4 h-4"/>
            New transaction
        </a>
    </x-page-header>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Transactions" :value="number_format($summary['count'])" meta="Across the current search" icon="arrow-left-right" tone="green"/>
        <x-stat-card label="Total inflow" :value="'UGX '.number_format($summary['inflow'], 2)" meta="Deposits, dividends, disbursements" icon="trending-up" tone="blue"/>
        <x-stat-card label="Total outflow" :value="'UGX '.number_format($summary['outflow'], 2)" meta="Withdrawals, repayments, fees" icon="trending-down" tone="coral"/>
        <x-stat-card label="Net movement" :value="'UGX '.number_format($summary['inflow'] - $summary['outflow'], 2)" meta="Inflow less outflow" icon="coins" tone="purple"/>
    </div>

    @php
        $typeLabels = [
            'deposit' => 'Deposits',
            'withdrawal' => 'Withdrawals',
            'share_transfer' => 'Share transfers',
            'loan_disbursement' => 'Disbursements',
            'loan_repayment' => 'Repayments',
            'dividend' => 'Dividends',
            'fee' => 'Fees',
        ];
        $orderedTypes = [];
        foreach (['deposit', 'withdrawal', 'share_transfer', 'loan_disbursement', 'loan_repayment', 'dividend', 'fee'] as $known) {
            if ($typeCounts->has($known)) {
                $orderedTypes[$known] = (int) $typeCounts[$known];
            }
        }
        foreach ($typeCounts as $unknown => $count) {
            if (! isset($orderedTypes[$unknown])) {
                $orderedTypes[$unknown] = (int) $count;
                $typeLabels[$unknown] = ucfirst(str_replace('_', ' ', $unknown));
            }
        }
    @endphp

    <x-panel title="Transaction ledger"
             subtitle="Every movement posted to member accounts, newest first."
             icon="arrow-left-right">
        <x-slot name="actions">
            <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap items-center gap-2 m-0">
                @if($type !== '')
                    <input type="hidden" name="type" value="{{ $type }}">
                @endif
                <div class="relative">
                    <x-icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                    <input type="search" name="search" value="{{ request('search') }}"
                           placeholder="Reference, account, narration..."
                           class="h-10 w-full sm:w-64 pl-9 pr-3 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">
                </div>
                <button type="submit" class="h-10 px-4 rounded-xl bg-[#181824] text-white text-sm font-semibold hover:bg-slate-700 transition dark:bg-white dark:text-slate-900">Search</button>
                @if(request('search') || $type !== '')
                    <a href="{{ route('transactions.index') }}" class="h-10 px-3 inline-flex items-center rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition dark:border-slate-700 dark:text-slate-300">Clear</a>
                @endif
            </form>
            <a href="{{ route('prints.transactions', array_filter(['search' => request('search'), 'type' => $type])) }}"
               target="_blank" rel="noopener" title="Print every posting in the current filter"
               class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold inline-flex items-center gap-2 hover:bg-slate-50 transition dark:bg-[#181824] dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                <x-icon name="printer" class="w-4 h-4"/>
                Print
            </a>
        </x-slot>

        <div class="flex items-center gap-2 px-5 py-3 overflow-x-auto border-b border-slate-100 dark:border-slate-800">
            <a href="{{ route('transactions.index', array_filter(['search' => request('search')])) }}"
               class="h-8 shrink-0 inline-flex items-center gap-1.5 rounded-lg border px-3 text-xs font-semibold transition {{ $type === '' ? 'bg-[#181824] border-[#181824] text-white dark:bg-white dark:border-white dark:text-slate-900' : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300 hover:text-slate-800 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300' }}">
                All
                <span class="rounded px-1.5 py-0.5 text-[10px] font-bold {{ $type === '' ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">{{ number_format($summary['count']) }}</span>
            </a>
            @foreach($orderedTypes as $key => $count)
                <a href="{{ route('transactions.index', array_filter(['search' => request('search'), 'type' => $key])) }}"
                   class="h-8 shrink-0 inline-flex items-center gap-1.5 rounded-lg border px-3 text-xs font-semibold transition {{ $type === $key ? 'bg-[#181824] border-[#181824] text-white dark:bg-white dark:border-white dark:text-slate-900' : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300 hover:text-slate-800 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300' }}">
                    {{ $typeLabels[$key] }}
                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold {{ $type === $key ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">{{ number_format($count) }}</span>
                </a>
            @endforeach
        </div>

        <div class="w-full overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/60 dark:bg-slate-950/40">
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Reference</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Account</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Amount</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Narration</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Served by</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Posted</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($transactions as $transaction)
                        @php
                            $badges = [
                                'deposit' => 'bg-[#38CE3C]/15 text-[#38CE3C] border-[#38CE3C]/20',
                                'withdrawal' => 'bg-[#FF4D6B]/15 text-[#FF4D6B] border-[#FF4D6B]/20',
                                'share_transfer' => 'bg-[#8E32E9]/15 text-[#8E32E9] border-[#8E32E9]/20',
                                'dividend' => 'bg-[#FFDE73]/30 text-amber-700 border-[#FFDE73]/40 dark:text-amber-300',
                                'loan_disbursement' => 'bg-blue-500/15 text-blue-600 border-blue-500/20 dark:text-blue-400',
                                'loan_repayment' => 'bg-teal-500/15 text-teal-600 border-teal-500/20 dark:text-teal-400',
                            ];
                            $badge = $badges[$transaction->transaction_type] ?? 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
                            $isCredit = in_array($transaction->transaction_type, ['deposit', 'dividend', 'loan_disbursement'], true);
                            $member = $transaction->account->member ?? null;
                        @endphp
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="font-mono text-[13px] font-semibold text-slate-800 dark:text-slate-100">{{ $transaction->reference }}</div>
                                <div class="mt-0.5 flex items-center gap-1.5 text-[11px] text-slate-400">
                                    <span>#{{ $transaction->id }}</span>
                                    @if($transaction->is_reversal)
                                        <span class="px-1.5 py-0.5 rounded bg-[#FF4D6B]/10 text-[#FF4D6B] font-semibold">Reversal</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $transaction->account_number }}</div>
                                @if($member)
                                    <div class="text-[11px] text-slate-400">{{ $member->first_name }} {{ $member->last_name }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold border whitespace-nowrap {{ $badge }}">
                                    {{ $typeLabels[$transaction->transaction_type] ?? ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap tabular-nums">
                                <span class="text-[11px] font-medium text-slate-400 mr-1">UGX</span>
                                <span class="text-sm font-bold {{ $isCredit ? 'text-[#38CE3C]' : 'text-[#FF4D6B]' }}">{{ $isCredit ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-slate-600 dark:text-slate-300">
                                @if($transaction->narration)
                                    <p class="max-w-56 truncate" title="{{ $transaction->narration }}">{{ $transaction->narration }}</p>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-sm text-slate-600 dark:text-slate-300">
                                <span class="inline-block max-w-40 truncate" title="{{ $transaction->performed_by_username }}">{{ $transaction->performed_by_username ?: '—' }}</span>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ $transaction->created_at->format('d M Y') }}</div>
                                <div class="text-[11px] text-slate-400">{{ $transaction->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <a href="{{ route('transactions.show', $transaction->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] text-xs font-semibold hover:bg-[#38CE3C]/20 transition">View</a>
                                <a href="{{ route('transactions.receipt', $transaction->id) }}" title="Thermal receipt"
                                   class="inline-flex items-center justify-center h-7 w-7 align-middle rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition dark:hover:text-slate-200 dark:hover:bg-slate-800">
                                    <x-icon name="receipt" class="w-4 h-4"/>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <x-empty-state colspan="8" icon="inbox" title="No transactions match this filter"
                                       message="Try a different search term, or record the first transaction for the SACCO.">
                            <a href="{{ route('transactions.create') }}" class="h-9 px-4 rounded-lg bg-[#38CE3C] text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-[#2cb030] transition">
                                <x-icon name="plus" class="w-3.5 h-3.5"/> New transaction
                            </a>
                        </x-empty-state>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-3.5 border-t border-slate-100 dark:border-slate-800">
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Showing
                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $transactions->firstItem() ?? 0 }}–{{ $transactions->lastItem() ?? 0 }}</span>
                of
                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ number_format($transactions->total()) }}</span>
                {{ \Illuminate\Support\Str::plural('posting', $transactions->total()) }}
                @if($type !== '')
                    <span class="text-slate-300 dark:text-slate-600">·</span>
                    {{ $typeLabels[$type] }} only
                @endif
            </p>
            @if($transactions->hasPages())
                <div class="shrink-0">{{ $transactions->links('pagination.compact') }}</div>
            @endif
        </div>
    </x-panel>
</div>
@endsection
