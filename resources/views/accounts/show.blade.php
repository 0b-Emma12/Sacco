@extends('layouts.app')
@section('title', 'Account Details')

@section('content')
<div class="mx-auto w-full max-w-7xl flex flex-col gap-6">
    <x-page-header
        eyebrow="Accounts"
        title="Account {{ $account->account_number }}"
        subtitle="{{ $account->product->name ?? 'Member account' }} · {{ $account->member->first_name }} {{ $account->member->last_name }}">
        <a href="{{ route('transactions.create', ['account_number' => $account->account_number, 'type' => 'deposit']) }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2 transition">
            <x-icon name="arrow-down-to-line" class="w-4 h-4 text-[#38CE3C]"/> Deposit
        </a>
        <a href="{{ route('transactions.create', ['account_number' => $account->account_number, 'type' => 'withdrawal']) }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2 transition">
            <x-icon name="arrow-up-from-line" class="w-4 h-4 text-[#FF4D6B]"/> Withdraw
        </a>
        <a href="{{ route('accounts.index') }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2 transition">
            <x-icon name="arrow-left" class="w-4 h-4"/> Back to accounts
        </a>
    </x-page-header>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Account balance" :value="'UGX '.number_format($account->balance, 2)" meta="Current posted balance" icon="wallet" tone="green"/>
        <x-stat-card label="Transactions" :value="number_format($transactions->count())" meta="Postings on this account" icon="arrow-left-right" tone="blue"/>
        <x-stat-card label="Member savings" :value="'UGX '.number_format($account->member->total_savings, 2)" meta="Across all member accounts" icon="coins" tone="purple"/>
        <x-stat-card label="Share capital" :value="'UGX '.number_format($account->member->share_capital, 2)" meta="Non-withdrawable equity" icon="landmark" tone="amber"/>
    </div>

    <x-panel title="Account particulars" subtitle="Holder, product and current standing." icon="wallet">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 p-5 sm:p-6">
            <div>
                <div class="text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">Account holder</div>
                <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                    <a href="{{ route('members.show', $account->member->id) }}" class="text-[#38CE3C] hover:underline">
                        {{ $account->member->first_name }} {{ $account->member->last_name }}
                    </a>
                </div>
            </div>
            <div>
                <div class="text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">Product type</div>
                <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $account->product->name ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">Current balance</div>
                <div class="text-lg font-extrabold text-[#38CE3C]">UGX {{ number_format($account->balance, 2) }}</div>
            </div>
            <div>
                <div class="text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">Status</div>
                @if($account->is_active)
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20">
                        Active
                    </span>
                @else
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20">
                        Inactive
                    </span>
                @endif
            </div>
        </div>
    </x-panel>

    <x-panel title="Transaction history" subtitle="Recent deposits and withdrawals for this account." icon="arrow-left-right">
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Reference</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Amount</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Narration</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($transactions as $tx)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-5 py-4 text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $tx->reference }}</td>
                        <td class="px-5 py-4">
                            @if($tx->transaction_type === 'deposit')
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20">Deposit</span>
                            @elseif($tx->transaction_type === 'share_transfer')
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-[#8E32E9]/15 text-[#8E32E9] border border-[#8E32E9]/20">Share Transfer</span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20">Withdrawal</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right text-sm font-semibold {{ $tx->transaction_type === 'deposit' ? 'text-[#38CE3C]' : 'text-[#FF4D6B]' }}">
                            {{ $tx->transaction_type === 'deposit' ? '+' : '-' }} UGX {{ number_format($tx->amount, 2) }}
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-600 dark:text-slate-400">
                            <p class="max-w-64 truncate" title="{{ $tx->narration }}">{{ $tx->narration ?: '—' }}</p>
                        </td>
                    </tr>
                    @empty
                        <x-empty-state colspan="5" icon="arrow-left-right" title="No transactions recorded"
                                       message="Deposits and withdrawals posted to this account will appear here.">
                        </x-empty-state>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-panel>
</div>
@endsection
