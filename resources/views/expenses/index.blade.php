@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
<div class="mx-auto w-full max-w-7xl flex flex-col gap-6">
    
    <x-page-header
        eyebrow="Management"
        title="Expenses"
        subtitle="Operating expenses awaiting approval or already posted to the ledger.">
        <a href="{{ route('expenses.create') }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold shadow-sm inline-flex items-center gap-2 transition">
            <x-icon name="plus" class="w-4 h-4"/> Record expense
        </a>
    </x-page-header>

    @if(session('success'))
        <div class="p-4 text-sm text-[#38CE3C] font-semibold rounded-xl bg-[#38CE3C]/10 border border-[#38CE3C]/20" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Expenses" :value="number_format($summary['count'])" meta="Matching the current filter" icon="receipt" tone="blue"/>
        <x-stat-card label="Total value" :value="'UGX '.number_format($summary['total'], 2)" meta="All recorded expenses" icon="coins" tone="purple"/>
        <x-stat-card label="Approved" :value="'UGX '.number_format($summary['approved'], 2)" meta="Posted to the ledger" icon="check-circle" tone="green"/>
        <x-stat-card label="Pending" :value="number_format($summary['pending'])" meta="Awaiting management approval" icon="clock" tone="amber"/>
    </div>

    <x-panel title="Expense register" subtitle="Newest expenses first, fifteen per page." icon="receipt">
        <x-slot name="actions">
            <form method="GET" action="{{ route('expenses.index') }}" class="flex flex-wrap items-center gap-2 m-0">
                <div class="relative">
                    <x-icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                    <input type="search" name="q" value="{{ request('q') }}"
                           placeholder="Search description or category..."
                           class="h-10 w-full sm:w-64 pl-9 pr-3 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">
                </div>
                <button type="submit" class="h-10 px-4 rounded-xl bg-[#181824] text-white text-sm font-semibold hover:bg-slate-700 transition dark:bg-white dark:text-slate-900">Search</button>
                @if(request('q'))
                    <a href="{{ route('expenses.index') }}" class="h-10 px-3 inline-flex items-center rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition dark:border-slate-700 dark:text-slate-300">Clear</a>
                @endif
            </form>
        </x-slot>

        <div class="w-full overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Expense ID</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Description</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Category</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Posted by</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($expenses as $expense)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4">
                            <a href="{{ route('expenses.show', $expense->id) }}" class="text-sm font-semibold text-[#38CE3C] hover:underline">
                                #{{ str_pad($expense->id, 4, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-800 dark:text-slate-200">
                            {{ $expense->description }}
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $expense->category }}</td>
                        <td class="px-5 py-4 text-sm font-semibold text-slate-800 dark:text-slate-200">
                            UGX {{ number_format($expense->amount, 2) }}
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-400">
                            {{ $expense->date ? $expense->date->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-400">
                            {{ $expense->created_by_name }}
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $st = strtolower($expense->status);
                                $badgeClass = $st === 'approved'
                                    ? 'bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20'
                                    : ($st === 'rejected'
                                        ? 'bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20'
                                        : 'bg-[#FFDE73]/30 text-amber-700 dark:text-amber-300 border border-amber-300/30');
                            @endphp
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-md {{ $badgeClass }}">
                                {{ ucfirst($expense->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('expenses.show', $expense->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] text-xs font-semibold hover:bg-[#38CE3C]/20 transition" title="View">View</a>
                            <a href="#delete-expense-{{ $expense->id }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition dark:bg-slate-800 dark:text-slate-300" title="Delete">Delete</a>
                        </td>
                    </tr>
                    @empty
                        <x-empty-state colspan="8" icon="receipt" title="No expenses match this filter"
                                       message="Try a different search term, or record the first operating expense.">
                            <a href="{{ route('expenses.create') }}" class="h-9 px-4 rounded-lg bg-[#38CE3C] text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-[#2cb030] transition">
                                <x-icon name="plus" class="w-3.5 h-3.5"/> Record expense
                            </a>
                        </x-empty-state>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $expenses->links() }}
            </div>
        @endif
    </x-panel>
</div>

@foreach($expenses as $expense)
<div id="delete-expense-{{ $expense->id }}" class="modal">
    <a href="#" class="modal-overlay" aria-label="Close"></a>
    <div class="modal-card bg-white dark:bg-[#181824] rounded-2xl shadow-xl p-6 border border-slate-200/80 dark:border-[#272738]">
        <div class="flex items-center gap-3 mb-4">
            <span class="h-10 w-10 rounded-xl bg-[#FF4D6B]/10 text-[#FF4D6B] flex items-center justify-center">
                <x-icon name="alert-triangle" class="w-5 h-5"/>
            </span>
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Delete expense?</h3>
                <p class="text-xs text-slate-500">This action cannot be undone.</p>
            </div>
        </div>
        <p class="text-xs text-slate-500 mb-6">
            #{{ str_pad($expense->id, 4, '0', STR_PAD_LEFT) }} — {{ $expense->description }} (UGX {{ number_format($expense->amount, 2) }})
        </p>
        <div class="flex justify-end gap-3">
            <a href="#" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">Cancel</a>
            <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#FF4D6B] rounded-xl hover:bg-[#e63e5c] transition">Delete Expense</button>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
