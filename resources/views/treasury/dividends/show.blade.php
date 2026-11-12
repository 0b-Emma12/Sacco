@extends('layouts.app')
@section('title', 'Surplus Distribution FY '.$dividend->financial_year)

@section('content')
<div class="mx-auto w-full max-w-6xl flex flex-col gap-6">
    <x-page-header
        eyebrow="Treasury / F06"
        title="Surplus Distribution FY {{ $dividend->financial_year }}"
        subtitle="Board approval locks the split; posting credits member savings accounts.">
        <a href="{{ route('prints.dividends.bulk', $dividend) }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold shadow-sm inline-flex items-center gap-2 transition">
            <x-icon name="printer" class="w-4 h-4"/> Print all statements
        </a>
        <a href="{{ route('treasury.dividends.index') }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2">
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
        <x-stat-card label="Net surplus" :value="'UGX '.number_format($dividend->net_surplus, 2)" meta="After reserve and welfare" icon="trending-up" tone="green"/>
        <x-stat-card label="Dividend pool" :value="'UGX '.number_format($dividend->dividend_pool, 2)" :meta="rtrim(rtrim(number_format($dividend->dividend_on_shares_pct, 2), '0'), '.').'% of distributable'" icon="wallet" tone="purple"/>
        <x-stat-card label="Savings interest" :value="'UGX '.number_format($dividend->savings_interest_pool, 2)" :meta="rtrim(rtrim(number_format($dividend->interest_on_savings_pct, 2), '0'), '.').'% allocated'" icon="coins" tone="blue"/>
        <x-stat-card label="Paid out" :value="'UGX '.number_format($summary->total, 2)" :meta="number_format($summary->members).' members paid'" icon="check-circle" tone="amber"/>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 flex flex-col gap-6">
            <x-panel title="Distribution computation" subtitle="How the surplus is split across the pools." icon="pie-chart">
                <dl class="grid gap-y-3 p-5 sm:p-6 text-sm">
                    <div class="flex justify-between gap-4 pb-3 border-b border-slate-100 dark:border-slate-800"><dt class="text-slate-500">Interest income (4000)</dt><dd class="font-medium text-slate-800 dark:text-slate-100">{{ number_format($dividend->total_loan_interest_income, 2) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Savings interest expense (5200)</dt><dd class="font-medium text-slate-800 dark:text-slate-100">({{ number_format($dividend->total_savings_interest_expense, 2) }})</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Operational expenses (5000/5100)</dt><dd class="font-medium text-slate-800 dark:text-slate-100">({{ number_format($dividend->total_operational_expenses, 2) }})</dd></div>
                    <div class="flex justify-between gap-4 border-t border-slate-100 dark:border-slate-800 pt-3"><dt class="font-semibold text-slate-700 dark:text-slate-200">Net surplus</dt><dd class="font-bold text-slate-900 dark:text-slate-100">UGX {{ number_format($dividend->net_surplus, 2) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Statutory reserve ({{ rtrim(rtrim(number_format($dividend->statutory_reserve_pct, 2), '0'), '.') }}%)</dt><dd class="font-medium text-slate-800 dark:text-slate-100">({{ number_format($dividend->statutory_reserve_amount, 2) }})</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Education / welfare ({{ rtrim(rtrim(number_format($dividend->education_welfare_pct, 2), '0'), '.') }}%)</dt><dd class="font-medium text-slate-800 dark:text-slate-100">({{ number_format($dividend->education_welfare_amount, 2) }})</dd></div>
                    <div class="flex justify-between gap-4 border-t border-slate-100 dark:border-slate-800 pt-3"><dt class="text-slate-500">Dividend on shares ({{ rtrim(rtrim(number_format($dividend->dividend_on_shares_pct, 2), '0'), '.') }}% of distributable)</dt><dd class="font-semibold text-slate-800 dark:text-slate-100">{{ number_format($dividend->dividend_pool, 2) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Interest on savings ({{ rtrim(rtrim(number_format($dividend->interest_on_savings_pct, 2), '0'), '.') }}%)</dt><dd class="font-semibold text-slate-800 dark:text-slate-100">{{ number_format($dividend->savings_interest_pool, 2) }}</dd></div>
                    <div class="flex justify-between gap-4 border-t border-slate-100 dark:border-slate-800 pt-3"><dt class="font-semibold text-slate-700 dark:text-slate-200">Members paid</dt><dd class="font-medium text-slate-800 dark:text-slate-100">{{ $summary->members }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="font-semibold text-slate-700 dark:text-slate-200">Total paid out</dt><dd class="font-bold text-[#38CE3C]">UGX {{ number_format($summary->total, 2) }}</dd></div>
                </dl>
            </x-panel>
        </div>

        <div class="flex flex-col gap-6">
            <x-panel title="Status" subtitle="Approval and payout position." icon="shield" tone="purple">
                <div class="p-5">
                    <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-semibold border {{ $dividend->status === 'COMPLETED' ? 'bg-[#38CE3C]/15 text-[#38CE3C] border-[#38CE3C]/20' : ($dividend->status === 'APPROVED' ? 'bg-[#8E32E9]/15 text-[#8E32E9] border-[#8E32E9]/20' : 'bg-[#FFDE73]/30 text-amber-700 border-[#FFDE73]/40 dark:text-amber-300') }}">{{ ucfirst(strtolower($dividend->status)) }}</span>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Board approved</dt><dd class="font-medium text-slate-800 dark:text-slate-100">{{ $dividend->approved_by_board ? 'Yes' : 'No' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">UMRA reference</dt><dd class="font-medium text-slate-800 dark:text-slate-100">{{ $dividend->approved_by_umra_reference ?: '—' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">AGM / payout date</dt><dd class="font-medium text-slate-800 dark:text-slate-100">{{ optional($dividend->agm_date)->format('d M Y') ?: '—' }}</dd></div>
                    </dl>
                </div>
            </x-panel>

            @if ($dividend->status === 'DRAFT' && Auth::user()->canApproveLoans())
            <x-panel title="Board approval" subtitle="Confirm the board approved this split and record any UMRA reference." icon="check-circle" tone="green">
                <div class="p-5">
                    <form action="{{ route('treasury.dividends.approve', $dividend) }}" method="POST" class="space-y-3">
                        @csrf
                        <label class="flex items-start gap-3">
                            <input type="checkbox" name="approved_by_board" value="1" class="mt-0.5 rounded border-slate-300 text-sacco focus:ring-sacco" required>
                            <span class="text-sm text-slate-700 dark:text-slate-300">The board approved this distribution</span>
                        </label>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">UMRA approval reference (if required)</label>
                            <input type="text" name="umra_reference" maxlength="100" class="w-full h-10 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">AGM date</label>
                            <input type="date" name="agm_date" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" class="w-full h-10 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100">
                        </div>
                        <button type="submit" class="w-full h-10 px-4 text-sm font-semibold text-white bg-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition">Approve Distribution</button>
                    </form>
                    <form action="{{ route('treasury.dividends.recompute', $dividend) }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full h-10 px-4 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition dark:bg-slate-800 dark:text-slate-300">Recompute from ledger</button>
                    </form>
                </div>
            </x-panel>
            @endif

            @if ($dividend->status === 'APPROVED' && Auth::user()->canDisburseLoans())
            <x-panel title="Post payouts" subtitle="Credits each active member's savings account and posts Dr 3100 / Cr 2000." icon="send" tone="blue">
                <div class="p-5">
                    <form action="{{ route('treasury.dividends.post', $dividend) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Payout date</label>
                            <input type="date" name="payout_date" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" class="w-full h-10 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100">
                        </div>
                        <button type="submit" class="w-full h-10 px-4 text-sm font-semibold text-white bg-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition">Post Payouts</button>
                    </form>
                </div>
            </x-panel>
            @endif
        </div>
    </div>

    <x-panel title="Member payouts" subtitle="Computed from average share and savings balances for the year." icon="users" tone="blue">
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap">
                <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Member</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Avg shares</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Avg savings</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Dividend</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Savings interest</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Total payout</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">WHT</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Net payout</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Reference</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($dividends as $payout)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $payout->member->first_name ?? 'Unknown' }} {{ $payout->member->last_name ?? '' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $payout->member->membership_number ?? '' }}</div>
                        </td>
                        <td class="px-5 py-4 text-right text-sm text-slate-700 dark:text-slate-300">{{ number_format($payout->avg_share_balance, 2) }}</td>
                        <td class="px-5 py-4 text-right text-sm text-slate-700 dark:text-slate-300">{{ number_format($payout->avg_savings_balance, 2) }}</td>
                        <td class="px-5 py-4 text-right text-sm text-slate-700 dark:text-slate-300">{{ number_format($payout->dividend_earned, 2) }}</td>
                        <td class="px-5 py-4 text-right text-sm text-slate-700 dark:text-slate-300">{{ number_format($payout->interest_earned, 2) }}</td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-slate-800 dark:text-slate-100">{{ number_format($payout->total_payout, 2) }}</td>
                        <td class="px-5 py-4 text-right text-sm text-slate-700 dark:text-slate-300">{{ number_format($payout->withholding_tax, 2) }}</td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-[#38CE3C]">{{ number_format($payout->net_payout, 2) }}</td>
                        <td class="px-5 py-4 text-sm text-slate-500">{{ $payout->payout_reference ?: '—' }}</td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            @if($payout->member)
                                <a href="{{ route('prints.members.dividend', [$payout->member_id, $payout->id]) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] text-xs font-semibold hover:bg-[#38CE3C]/20 transition">Statement</a>
                                <a href="{{ route('prints.members.wht', [$payout->member_id, $payout->id]) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition dark:bg-slate-800 dark:text-slate-300">WHT</a>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                        <x-empty-state colspan="10" icon="inbox" title="Payouts are calculated when the distribution is posted"
                                       message="Once the board approves and payouts are posted, member statements appear here."/>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dividends->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $dividends->links() }}
            </div>
        @endif
    </x-panel>
</div>
@endsection
