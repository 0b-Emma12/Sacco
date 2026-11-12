@extends('layouts.app')
@section('title', 'UMRA Tier 4 Report')

@php
    $parRatio = (float) ($par->par30_ratio ?? 0);
    $liquidityRatio = (float) ($liquidity->liquidity_ratio ?? 0);
    $capitalRatio = (float) ($capital->capital_adequacy_ratio ?? 0);
    $parTone = $parRatio > 5 ? 'coral' : 'green';
    $liquidityTone = $liquidityRatio < 15 ? 'coral' : 'green';
    $capitalTone = $capitalRatio < 10 ? 'coral' : 'green';
@endphp

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-6xl">
    <x-page-header
        eyebrow="Regulatory reporting"
        title="UMRA Tier 4 Prudential Report"
        subtitle="Generated from the general ledger and repayment schedules on {{ now()->format('d M Y H:i') }}">
        <a href="{{ route('reports.index') }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2 transition no-print">
            <x-icon name="arrow-left" class="w-4 h-4"/> Reports
        </a>
        <a href="{{ route('reports.umra.export') }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold shadow-sm inline-flex items-center gap-2 transition no-print">
            <x-icon name="download" class="w-4 h-4"/> Download CSV
        </a>
    </x-page-header>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Gross loan portfolio" :value="'UGX '.number_format((float) ($par->gross_portfolio ?? 0), 2)" :meta="'Arrears: UGX '.number_format((float) ($par->total_arrears ?? 0), 2)" icon="landmark" tone="blue"/>
        <x-stat-card label="PAR above 30 days" :value="number_format($parRatio, 2).'%'" :meta="'UGX '.number_format((float) ($par->par30_amount ?? 0), 2).' at risk'" icon="alert-triangle" :tone="$parTone"/>
        <x-stat-card label="Liquidity ratio" :value="number_format($liquidityRatio, 2).'%'" :meta="'Liquid UGX '.number_format((float) ($liquidity->liquid_assets ?? 0), 2).' / deposits UGX '.number_format((float) ($liquidity->total_deposits ?? 0), 2)" icon="coins" :tone="$liquidityTone"/>
        <x-stat-card label="Capital adequacy" :value="number_format($capitalRatio, 2).'%'" :meta="'Core capital UGX '.number_format((float) ($capital->core_capital ?? 0), 2)" icon="shield" :tone="$capitalTone"/>
    </div>

    <x-panel title="Portfolio Ageing" subtitle="Outstanding loan balances by days in arrears." icon="bar-chart-3" tone="amber">
        <div class="overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap">
                <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">1-30 days</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">31-90 days</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">91-180 days</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">181-365 days</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">365+ days</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4 text-sm font-semibold text-slate-700 dark:text-slate-300">{{ number_format((float) ($ageing->bucket_1_30 ?? 0), 2) }}</td>
                        <td class="px-5 py-4 text-sm font-semibold text-slate-700 dark:text-slate-300">{{ number_format((float) ($ageing->bucket_31_90 ?? 0), 2) }}</td>
                        <td class="px-5 py-4 text-sm font-semibold text-slate-700 dark:text-slate-300">{{ number_format((float) ($ageing->bucket_91_180 ?? 0), 2) }}</td>
                        <td class="px-5 py-4 text-sm font-semibold text-slate-700 dark:text-slate-300">{{ number_format((float) ($ageing->bucket_181_365 ?? 0), 2) }}</td>
                        <td class="px-5 py-4 text-sm font-semibold text-slate-700 dark:text-slate-300">{{ number_format((float) ($ageing->bucket_365_plus ?? 0), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-panel>

    <x-panel title="Loans in Arrears" subtitle="Repayment schedules with an outstanding overdue balance." icon="alert-triangle" tone="coral">
        @if($arrears->isEmpty())
            <x-empty-state icon="check-circle" title="No loans are currently in arrears"
                           message="Every active repayment schedule is up to date."/>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap">
                    <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Member</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Loan</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Principal</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Overdue</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Days</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($arrears as $row)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $row->first_name }} {{ $row->last_name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $row->membership_number }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">#{{ $row->loan_id }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">{{ number_format((float) $row->principal_amount, 2) }}</td>
                                <td class="px-5 py-4 text-sm font-bold text-[#FF4D6B]">{{ number_format((float) $row->overdue_amount, 2) }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">{{ (int) $row->days_overdue }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border bg-[#FF4D6B]/15 text-[#FF4D6B] border-[#FF4D6B]/20 capitalize">{{ str_replace('_', ' ', $row->status) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-panel>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-panel title="Membership & Capital" subtitle="Member counts and capital balances held." icon="users" tone="blue">
            <table class="w-full text-left">
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">Active members</td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-slate-800 dark:text-slate-100">{{ (int) ($membersByStatus['Active'] ?? 0) }}</td>
                    </tr>
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">Dormant members</td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-slate-800 dark:text-slate-100">{{ (int) ($membersByStatus['Dormant'] ?? 0) }}</td>
                    </tr>
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">Exited / closed</td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-slate-800 dark:text-slate-100">{{ (int) ($membersByStatus['Exited'] ?? 0) + (int) ($membersByStatus['Closed'] ?? 0) }}</td>
                    </tr>
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">Total savings</td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-slate-800 dark:text-slate-100">UGX {{ number_format((float) ($capitalTotals->savings ?? 0), 2) }}</td>
                    </tr>
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">Share capital</td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-slate-800 dark:text-slate-100">UGX {{ number_format((float) ($capitalTotals->shares ?? 0), 2) }}</td>
                    </tr>
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">Loan liens</td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-slate-800 dark:text-slate-100">UGX {{ number_format((float) ($capitalTotals->liens ?? 0), 2) }}</td>
                    </tr>
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">Guarantee liabilities</td>
                        <td class="px-5 py-4 text-right text-sm font-bold text-slate-800 dark:text-slate-100">UGX {{ number_format((float) ($capitalTotals->guarantees ?? 0), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </x-panel>

        <x-panel title="Loan Products" subtitle="Configured products and their active loan counts." icon="credit-card" tone="purple">
            @if($loanProducts->isEmpty())
                <x-empty-state icon="credit-card" title="No loan products configured"
                               message="Create a loan product to begin tracking portfolio composition."/>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Product</th>
                                <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Rate</th>
                                <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Active loans</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($loanProducts as $product)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                    <td class="px-5 py-4 text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $product->name }}</td>
                                    <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">{{ number_format((float) $product->interest_rate_monthly, 2) }}% / month</td>
                                    <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $product->loans_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-panel>
    </div>

    <x-panel title="Surplus Distributions" subtitle="Declared statutory surplus appropriations by financial year." icon="coins" tone="green">
        @if($distributions->isEmpty())
            <x-empty-state icon="coins" title="No surplus distribution has been declared"
                           message="Compute the latest financial year to produce a distribution schedule.">
                <span class="font-mono text-[11px] bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-md text-slate-600 dark:text-slate-300">php artisan sacco:distribute-surplus {{ now()->year - 1 }}</span>
            </x-empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap">
                    <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Financial year</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Net surplus</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Reserve</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Dividend pool</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Savings interest</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($distributions as $distribution)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                <td class="px-5 py-4 text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $distribution->financial_year }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">{{ number_format((float) $distribution->net_surplus, 2) }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">{{ number_format((float) $distribution->statutory_reserve_amount, 2) }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">{{ number_format((float) $distribution->dividend_pool, 2) }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">{{ number_format((float) $distribution->savings_interest_pool, 2) }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">{{ $distribution->status }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-panel>
</div>
@endsection
