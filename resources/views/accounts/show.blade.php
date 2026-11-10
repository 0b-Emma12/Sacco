@extends('layouts.app')
@section('title', 'Account Details')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-5xl">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Accounts</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">
                Account {{ $account->account_number }}
            </h1>
        </div>
        <div class="flex items-center gap-3">
            @if($account->is_active && !in_array(strtolower($account->member->status ?? ''), ['exited', 'closed']))
            <a href="{{ route('transactions.create', ['account_number' => $account->account_number, 'type' => 'deposit']) }}" class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-emerald-700 bg-emerald-100 border border-emerald-200 rounded hover:bg-emerald-200 transition">
                <i data-lucide="arrow-down-to-line" class="w-4 h-4"></i> Deposit
            </a>
            <a href="{{ route('transactions.create', ['account_number' => $account->account_number, 'type' => 'withdrawal']) }}" class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-rose-700 bg-rose-100 border border-rose-200 rounded hover:bg-rose-200 transition">
                <i data-lucide="arrow-up-from-line" class="w-4 h-4"></i> Withdraw
            </a>
            <div class="w-px h-5 bg-slate-300 mx-1"></div>
            @endif
            <a href="{{ route('accounts.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Back to Accounts</a>
        </div>
    </div>

    <!-- Overview Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 dark:bg-slate-900 dark:border-slate-800">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <div class="text-xs text-slate-500 mb-1">Account Holder</div>
                <div class="font-semibold text-slate-800 dark:text-slate-200">
                    <a href="{{ route('members.show', $account->member->id) }}" class="text-sacco hover:underline">
                        {{ $account->member->first_name }} {{ $account->member->last_name }}
                    </a>
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-500 mb-1">Product Type</div>
                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $account->product->name ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500 mb-1">Current Balance</div>
                <div class="font-bold text-emerald-600 dark:text-emerald-400">UGX {{ number_format($account->balance, 2) }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500 mb-1">Status</div>
                @if($account->is_active)
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">
                        Active
                    </span>
                @else
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">
                        Inactive
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Transactions Ledger -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden dark:bg-slate-900 dark:border-slate-800">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Transaction History</h3>
                <p class="text-xs text-slate-500 mt-1">Recent deposits and withdrawals for this account.</p>
            </div>
        </div>
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/50 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Date</th>
                        <th class="px-6 py-4 font-semibold">Reference</th>
                        <th class="px-6 py-4 font-semibold">Type</th>
                        <th class="px-6 py-4 font-semibold">Amount</th>
                        <th class="px-6 py-4 font-semibold">Narration</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($transactions as $tx)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-4 font-medium text-slate-800 dark:text-slate-200">{{ $tx->reference }}</td>
                        <td class="px-6 py-4">
                            @if($tx->transaction_type === 'deposit')
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Deposit</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-rose-50 text-rose-700 border border-rose-200">Withdrawal</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-semibold {{ $tx->transaction_type === 'deposit' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $tx->transaction_type === 'deposit' ? '+' : '-' }} UGX {{ number_format($tx->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-xs">{{ $tx->narration }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">No transactions recorded for this account.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
