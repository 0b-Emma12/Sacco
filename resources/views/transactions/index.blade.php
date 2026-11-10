@extends('layouts.app')
@section('title', 'Transactions')
@section('content')
<div class="mx-auto w-full max-w-7xl flex flex-col gap-6">
    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Transactions</h1>
            <p class="text-sm text-slate-500 mt-1">Manage and view your transactions records.</p>
        </div>
        
        <a href="{{ route('transactions.create') }}" class="h-11 px-6 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition flex items-center gap-2">
            <i data-lucide="plus-circle" class="w-5 h-5"></i>
            Create Transaction
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-2 sm:p-6 dark:bg-slate-900 dark:border-slate-800">
        
        <div class="flex justify-end pb-6">
            <form action="{{ route('transactions.index') }}" method="GET" class="relative w-full md:w-80">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-[18px] h-[18px]"></i>
                <input
                    name="search"
                    placeholder="Search type, account, amount, narration..."
                    value="{{ request('search') }}"
                    class="w-full h-11 pl-10 pr-4 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
                />
            </form>
        </div>

        <div class="rounded-xl border border-slate-100 overflow-hidden dark:border-slate-800">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="h-12 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Transaction ID</th>
                        <th class="h-12 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Account Number</th>
                        <th class="h-12 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Transaction Type</th>
                        <th class="h-12 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                        <th class="h-12 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Narration</th>
                        <th class="h-12 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Served by</th>
                        <th class="h-12 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Transaction Date</th>
                        <th class="h-12 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $transaction->reference }}</td>
                            <td class="px-4 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $transaction->account_number }}</td>
                            <td class="px-4 py-4 text-sm">
                                @if($transaction->transaction_type === 'withdrawal')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                        {{ ucfirst($transaction->transaction_type) }}
                                    </span>
                                @elseif($transaction->transaction_type === 'share_transfer')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                                        Share Transfer
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">
                                        {{ ucfirst($transaction->transaction_type) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-700 dark:text-slate-300">UGX {{ number_format($transaction->amount, 2) }}</td>
                            <td class="px-4 py-4 text-sm text-slate-700 dark:text-slate-300">
                                <p class="max-w-48 truncate" title="{{ $transaction->narration }}">{{ $transaction->narration ?: '—' }}</p>
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $transaction->performed_by_username ?: '—' }}</td>
                            <td class="px-4 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-4 text-sm">
                                <a href="{{ route('transactions.show', $transaction->id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="h-32 px-4 text-center text-slate-500">No results found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
