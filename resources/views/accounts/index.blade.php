@extends('layouts.app')
@section('title', 'Accounts')

@section('content')
<div class="mx-auto w-full max-w-7xl flex flex-col gap-6">
    
    @if(session('success'))
        <div class="p-4 text-sm text-[#38CE3C] font-semibold rounded-xl bg-[#38CE3C]/10 border border-[#38CE3C]/20" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <x-page-header
        eyebrow="Module"
        title="Accounts"
        subtitle="Every member savings, share and fixed deposit account.">
        <a href="{{ route('accounts.create') }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold shadow-sm inline-flex items-center gap-2 transition">
            <x-icon name="plus" class="w-4 h-4"/> New account
        </a>
    </x-page-header>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Accounts" :value="number_format($summary['total'])" meta="Matching the current filter" icon="wallet" tone="blue"/>
        <x-stat-card label="Total balance" :value="'UGX '.number_format($summary['balance'], 2)" meta="Across all listed accounts" icon="coins" tone="purple"/>
        <x-stat-card label="Active accounts" :value="number_format($summary['active'])" meta="Open for transactions" icon="check-circle" tone="green"/>
        <x-stat-card label="Inactive accounts" :value="number_format($summary['inactive'])" meta="Closed or dormant" icon="x-circle" tone="coral"/>
    </div>

    <x-panel title="Member accounts" subtitle="Newest accounts first, fifteen per page." icon="wallet">
        <x-slot name="actions">
            <form method="GET" action="{{ route('accounts.index') }}" class="flex flex-wrap items-center gap-2 m-0">
                <div class="relative">
                    <x-icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                    <input type="search" name="q" value="{{ request('q') }}"
                           placeholder="Search account or member..."
                           class="h-10 w-full sm:w-64 pl-9 pr-3 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">
                </div>
                <button type="submit" class="h-10 px-4 rounded-xl bg-[#181824] text-white text-sm font-semibold hover:bg-slate-700 transition dark:bg-white dark:text-slate-900">Search</button>
                @if(request('q'))
                    <a href="{{ route('accounts.index') }}" class="h-10 px-3 inline-flex items-center rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition dark:border-slate-700 dark:text-slate-300">Clear</a>
                @endif
            </form>
        </x-slot>

        <div class="w-full overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Account no</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Member</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Product</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Balance</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($accounts as $account)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4">
                            <a href="{{ route('accounts.show', $account->id) }}" class="text-sm font-semibold text-[#38CE3C] hover:underline">
                                {{ $account->account_number }}
                            </a>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold uppercase">
                                    {{ substr($account->member->first_name ?? 'U', 0, 1) }}{{ substr($account->member->last_name ?? '', 0, 1) }}
                                </div>
                                <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $account->member->first_name ?? 'Unknown' }} {{ $account->member->last_name ?? '' }}
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $account->product->name ?? 'N/A' }}</td>
                        <td class="px-5 py-4 text-sm font-semibold text-slate-700 dark:text-slate-200">UGX {{ number_format($account->balance, 2) }}</td>
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-md 
                                {{ $account->is_active ? 'bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20' : 'bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20' }}">
                                {{ $account->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('accounts.show', $account->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] hover:bg-[#38CE3C]/20 transition" title="View Account Details">
                                <x-icon name="eye" class="w-3.5 h-3.5"/>
                                <span>View</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                        <x-empty-state colspan="6" icon="wallet" title="No accounts match this filter"
                                       message="Try a different search term, or open the first member account.">
                            <a href="{{ route('accounts.create') }}" class="h-9 px-4 rounded-lg bg-[#38CE3C] text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-[#2cb030] transition">
                                <x-icon name="plus" class="w-3.5 h-3.5"/> New account
                            </a>
                        </x-empty-state>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($accounts->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $accounts->links() }}
        </div>
        @endif
    </x-panel>
</div>
@endsection
