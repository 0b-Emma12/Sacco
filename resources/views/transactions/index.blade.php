@extends('layouts.app')
@section('title', 'Transactions')
@section('content')
<div class="mx-auto w-full max-w-7xl flex flex-col gap-6">
    @if(session('success'))
        <div class="p-4 text-sm text-[#38CE3C] font-semibold rounded-xl bg-[#38CE3C]/10 border border-[#38CE3C]/20" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Module</div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Transactions</h1>
            <p class="text-sm text-slate-500 mt-1">Manage and view all transaction records.</p>
        </div>
        
        <a href="{{ route('transactions.create') }}" class="h-11 px-6 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white font-semibold transition flex items-center gap-2 shadow-sm">
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
                    class="w-full h-11 pl-10 pr-4 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"
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
                            <td class="px-4 py-4 text-sm text-slate-700 dark:text-slate-300 font-medium">{{ $transaction->reference }}</td>
                            <td class="px-4 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $transaction->account_number }}</td>
                            <td class="px-4 py-4 text-sm">
                                @if($transaction->transaction_type === 'withdrawal')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20">
                                        {{ ucfirst($transaction->transaction_type) }}
                                    </span>
                                @elseif($transaction->transaction_type === 'share_transfer')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-[#8E32E9]/15 text-[#8E32E9] border border-[#8E32E9]/20">
                                        Share Transfer
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20">
                                        {{ ucfirst($transaction->transaction_type) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm font-semibold text-slate-700 dark:text-slate-300">UGX {{ number_format($transaction->amount, 2) }}</td>
                            <td class="px-4 py-4 text-sm text-slate-700 dark:text-slate-300">
                                <p class="max-w-48 truncate" title="{{ $transaction->narration }}">{{ $transaction->narration ?: '—' }}</p>
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $transaction->performed_by_username ?: '—' }}</td>
                            <td class="px-4 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-4 text-sm">
                                <a href="{{ route('transactions.show', $transaction->id) }}" class="text-sm font-semibold text-[#38CE3C] hover:text-[#2cb030] hover:underline">View</a>
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
