@extends('layouts.app')
@section('title', 'Accounts')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-7xl">
    
    <!-- PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Module</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">Accounts</h1>
        </div>
        <div class="flex items-center gap-3">
            <button class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-white bg-sacco border border-sacco rounded hover:bg-sacco-dark transition">
                <i data-lucide="plus" class="w-4 h-4"></i> New Account
            </button>
        </div>
    </div>

    <!-- DATA TABLE -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden dark:bg-slate-900 dark:border-slate-800">
        <div class="p-6 flex items-center justify-between border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Member Accounts</h3>
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-slate-400"></i>
                <input type="text" placeholder="Search accounts..." class="pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200">
            </div>
        </div>
        
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/50 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Account No</th>
                        <th class="px-6 py-4 font-semibold">Member</th>
                        <th class="px-6 py-4 font-semibold">Product</th>
                        <th class="px-6 py-4 font-semibold">Balance</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($accounts as $account)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                        <td class="px-6 py-4 font-medium text-sacco hover:underline cursor-pointer">
                            <a href="{{ route('accounts.show', $account->id) }}">
                                {{ $account->account_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold uppercase">
                                {{ substr($account->member->first_name ?? 'U', 0, 1) }}{{ substr($account->member->last_name ?? '', 0, 1) }}
                            </div>
                            <div class="font-semibold text-slate-800 dark:text-slate-200">
                                {{ $account->member->first_name ?? 'Unknown' }} {{ $account->member->last_name ?? '' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $account->product->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200">UGX {{ number_format($account->balance, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                {{ $account->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                {{ $account->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('accounts.show', $account->id) }}" class="text-slate-400 hover:text-sacco" title="View Account Details">
                                <i data-lucide="eye" class="w-4 h-4 inline"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">No accounts found in the database.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($accounts->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $accounts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
