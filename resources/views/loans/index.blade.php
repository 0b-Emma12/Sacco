@extends('layouts.app')
@section('title', 'Loans')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-7xl">
    
    <!-- PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Module</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">Loans</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('loans.create') }}" class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-white bg-sacco border border-sacco rounded hover:bg-sacco-dark transition">
                <i data-lucide="plus" class="w-4 h-4"></i> New Loan Application
            </a>
        </div>
    </div>

    <!-- DATA TABLE -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden dark:bg-slate-900 dark:border-slate-800">
        <div class="p-6 flex items-center justify-between border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Loan Applications</h3>
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-slate-400"></i>
                <input type="text" placeholder="Search loans..." class="pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200">
            </div>
        </div>
        
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/50 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Application</th>
                        <th class="px-6 py-4 font-semibold">Member</th>
                        <th class="px-6 py-4 font-semibold">Principal</th>
                        <th class="px-6 py-4 font-semibold">Rate</th>
                        <th class="px-6 py-4 font-semibold">Term</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($loans as $loan)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                        <td class="px-6 py-4 font-medium text-sacco hover:underline cursor-pointer">
                            <a href="{{ route('loans.show', $loan->id) }}">
                                APP-{{ str_pad($loan->id, 4, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-6 py-4 flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold uppercase">
                                {{ substr($loan->member->first_name ?? 'U', 0, 1) }}{{ substr($loan->member->last_name ?? '', 0, 1) }}
                            </div>
                            <div class="font-semibold text-slate-800 dark:text-slate-200">
                                {{ $loan->member->first_name ?? 'Unknown' }} {{ $loan->member->last_name ?? '' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200">UGX {{ number_format($loan->principal_amount, 2) }}</td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $loan->interest_rate }}%</td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $loan->term_months }} Months</td>
                        <td class="px-6 py-4">
                            @php
                                $statusClass = match($loan->status) {
                                    'approved', 'disbursed' => 'bg-emerald-100 text-emerald-700',
                                    'rejected', 'defaulted' => 'bg-rose-100 text-rose-700',
                                    'under_review' => 'bg-amber-100 text-amber-700',
                                    default => 'bg-slate-100 text-slate-700'
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClass }}">
                                {{ ucfirst(str_replace('_', ' ', $loan->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('loans.show', $loan->id) }}" class="text-slate-400 hover:text-sacco"><i data-lucide="eye" class="w-4 h-4 inline"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-500">No loans found in the database.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($loans->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $loans->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
