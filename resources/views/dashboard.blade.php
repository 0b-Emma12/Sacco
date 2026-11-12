@extends('layouts.app')
@section('title', 'Admin Dashboard')
@php
    $perfMonths = array_column($monthlyPerformance, 'month');
    $perfDeposits = array_column($monthlyPerformance, 'deposits');
    $perfLoans = array_column($monthlyPerformance, 'loans');
    $perfRepayments = array_column($monthlyPerformance, 'repayments');
    $perfPeak = max(1, max($perfDeposits), max($perfLoans), max($perfRepayments));
    $perfHeight = fn ($value) => round(($value / $perfPeak) * 100, 1);

    $allocationTotal = max(1, $grossLoanPortfolio + $totalMemberSavings + $cashAndBank + $totalShareCapital);
    $allocLoan = round(($grossLoanPortfolio / $allocationTotal) * 100, 2);
    $allocSavings = round($allocLoan + ($totalMemberSavings / $allocationTotal) * 100, 2);
    $allocCash = round($allocSavings + ($cashAndBank / $allocationTotal) * 100, 2);
@endphp
@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-7xl">

    <!-- ==================== HEADER & TOP BAR ==================== -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#38CE3C]/10 text-[#38CE3C] border border-[#38CE3C]/20">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#38CE3C]"></span>
                    Live System Status
                </span>
                <span class="text-xs text-slate-400">YTD {{ date('Y') }} &bull; Currency: UGX &bull; {{ date('M d, Y') }}</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight mt-1">Financial & Management Overview</h1>
        </div>

        <div class="flex items-center gap-3">
            <!-- Dropdown for Export Options -->
            <details class="dropdown">
                <summary class="flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200/90 rounded-xl shadow-xs hover:bg-slate-50 transition dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200">
                    <x-icon name="download" class="w-3.5 h-3.5 text-slate-500"/>
                    <span>Export</span>
                    <x-icon name="chevron-down" class="w-3 h-3 text-slate-400"/>
                </summary>
                <div class="dropdown-panel w-44 bg-white border border-slate-100 rounded-xl shadow-xl overflow-hidden dark:bg-[#181824] dark:border-[#272738] p-1">
                    <a href="{{ route('dashboard.export') }}" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 rounded-lg hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800 transition">
                        <x-icon name="file-spreadsheet" class="w-4 h-4 text-[#38CE3C]"/> Export CSV
                    </a>
                    <a href="{{ route('dashboard', ['print' => 1]) }}" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 rounded-lg hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800 transition">
                        <x-icon name="printer" class="w-4 h-4 text-[#8E32E9]"/> Print Financial PDF
                    </a>
                </div>
            </details>

            <a href="{{ route('reports.index') }}" 
               class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-[#38CE3C] rounded-xl shadow-sm shadow-[#38CE3C]/25 hover:bg-[#2cb030] transition">
                <x-icon name="file-text" class="w-3.5 h-3.5"/>
                <span>Audit Reports</span>
            </a>
        </div>
    </div>

    <!-- ==================== 1. TOP SUMMARY KPI CARDS (STELLAR PALETTE) ==================== -->
    <div class="grid gap-4 grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
        
        <!-- CARD 1: Total Assets (Primary Green) -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 sm:p-5 dark:bg-[#181824] dark:border-[#272738] flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">Total Assets</span>
                <span class="p-2 rounded-xl bg-[#38CE3C]/10 text-[#38CE3C]">
                    <x-icon name="landmark" class="w-4 h-4"/>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    UGX {{ number_format($totalAssets) }}
                </div>
                <div class="flex items-center gap-1.5 mt-1 text-[11px] font-medium text-[#38CE3C]">
                    <x-icon name="trending-up" class="w-3.5 h-3.5"/>
                    <span>Solvent Portfolio</span>
                </div>
            </div>
        </div>

        <!-- CARD 2: Member Savings (Purple #8E32E9) -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 sm:p-5 dark:bg-[#181824] dark:border-[#272738] flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">Member Savings</span>
                <span class="p-2 rounded-xl bg-[#8E32E9]/10 text-[#8E32E9]">
                    <x-icon name="wallet" class="w-4 h-4"/>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    UGX {{ number_format($totalMemberSavings) }}
                </div>
                <div class="flex items-center gap-1.5 mt-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#8E32E9]"></span>
                    <span>Liquid deposits pool</span>
                </div>
            </div>
        </div>

        <!-- CARD 3: Gross Loan Portfolio (Coral #FF4D6B) -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 sm:p-5 dark:bg-[#181824] dark:border-[#272738] flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">Loan Portfolio</span>
                <span class="p-2 rounded-xl bg-[#FF4D6B]/10 text-[#FF4D6B]">
                    <x-icon name="credit-card" class="w-4 h-4"/>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    UGX {{ number_format($grossLoanPortfolio) }}
                </div>
                <div class="flex items-center gap-1.5 mt-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#FF4D6B]"></span>
                    <span>Disbursed credit</span>
                </div>
            </div>
        </div>

        <!-- CARD 4: Cash & Bank Balances (Yellow #FFDE73) -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 sm:p-5 dark:bg-[#181824] dark:border-[#272738] flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">Cash & Bank</span>
                <span class="p-2 rounded-xl bg-[#FFDE73]/30 text-amber-600 dark:text-amber-300">
                    <x-icon name="coins" class="w-4 h-4"/>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    UGX {{ number_format($cashAndBank) }}
                </div>
                <div class="flex items-center gap-1.5 mt-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                    <span>Branch vault & bank</span>
                </div>
            </div>
        </div>

        <!-- CARD 5: Active Members & Equity -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 sm:p-5 dark:bg-[#181824] dark:border-[#272738] flex flex-col justify-between col-span-2 md:col-span-1 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">Active Members</span>
                <span class="p-2 rounded-xl bg-blue-500/10 text-blue-500">
                    <x-icon name="users" class="w-4 h-4"/>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ number_format($activeMembers) }} <span class="text-xs font-normal text-slate-400">/ {{ number_format($totalMembersCount ?? $activeMembers) }}</span>
                </div>
                <div class="flex items-center gap-1.5 mt-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                    <span>Shares: UGX {{ number_format($totalShareCapital) }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- ==================== 2. STELLAR CHARTS ROW ==================== -->
    <div class="grid gap-6 lg:grid-cols-12">
        
        <!-- Performance Indicator Multi-bar Chart (7 columns) -->
        <div class="lg:col-span-7 bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 sm:p-6 dark:bg-[#181824] dark:border-[#272738] flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Performance Indicator</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Monthly transaction volume, loan activity & repayments</p>
                </div>
                <!-- Reporting period covered by the chart -->
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">FY {{ date('Y') }} &middot; Monthly</span>
            </div>

            <!-- Legend Pills -->
            <div class="flex flex-wrap items-center gap-4 pt-4 text-xs font-medium text-slate-600 dark:text-slate-300">
                <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-[#00d2d3]"></span>
                    <span>Deposits & Savings</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-[#8E32E9]"></span>
                    <span>Loans Disbursed</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-[#FF4D6B]"></span>
                    <span>Repayments & Yield</span>
                </div>
            </div>

            <!-- Server-rendered bar chart (CSS only, no canvas/JS) -->
            <div class="h-64 sm:h-72 w-full mt-4">
                <div class="bar-chart border-b border-slate-100 dark:border-slate-800">
                    @foreach($perfMonths as $mIndex => $month)
                        <div class="bar-group" title="{{ $month }} — Deposits: UGX {{ number_format($perfDeposits[$mIndex]) }} · Loans: UGX {{ number_format($perfLoans[$mIndex]) }} · Repayments: UGX {{ number_format($perfRepayments[$mIndex]) }}">
                            <span class="bar" style="height: {{ $perfHeight($perfDeposits[$mIndex]) }}%; background-color: #00d2d3;"></span>
                            <span class="bar" style="height: {{ $perfHeight($perfLoans[$mIndex]) }}%; background-color: #8E32E9;"></span>
                            <span class="bar" style="height: {{ $perfHeight($perfRepayments[$mIndex]) }}%; background-color: #FF4D6B;"></span>
                            <span class="bar-label">{{ $month }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Asset Allocation Doughnut (5 columns) -->
        <div class="lg:col-span-5 bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 sm:p-6 dark:bg-[#181824] dark:border-[#272738] flex flex-col justify-between">
            <div class="pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Asset & Capital Allocation</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Composition of SACCO liquidity and equity</p>
                </div>
            </div>

            <!-- CSS conic-gradient doughnut with center lead number -->
            <div class="h-60 w-full flex items-center justify-center my-3">
                <div class="donut" style="background: conic-gradient(#FF4D6B 0% {{ $allocLoan }}%, #38CE3C {{ $allocLoan }}% {{ $allocSavings }}%, #FFDE73 {{ $allocSavings }}% {{ $allocCash }}%, #8E32E9 {{ $allocCash }}% 100%);">
                    <div class="donut-label">
                        <span class="text-2xl font-extrabold text-slate-800 dark:text-white tracking-tight">
                            {{ $totalAssets > 1000000 ? round($totalAssets / 1000000, 1) . 'M' : number_format($totalAssets) }}
                        </span>
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Portfolio</span>
                    </div>
                </div>
            </div>

            <!-- Bottom Legend Tags -->
            <div class="grid grid-cols-2 gap-2 pt-3 border-t border-slate-100 dark:border-slate-800 text-xs font-medium">
                <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-900/50">
                    <span class="h-3 w-3 rounded-md bg-[#FF4D6B] shrink-0"></span>
                    <div class="truncate">
                        <p class="text-[10px] text-slate-400">Loan Portfolio</p>
                        <p class="font-bold text-slate-700 dark:text-slate-200">{{ $totalAssets > 0 ? round(($grossLoanPortfolio / $totalAssets) * 100) : 0 }}%</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-900/50">
                    <span class="h-3 w-3 rounded-md bg-[#38CE3C] shrink-0"></span>
                    <div class="truncate">
                        <p class="text-[10px] text-slate-400">Savings Pool</p>
                        <p class="font-bold text-slate-700 dark:text-slate-200">{{ $totalAssets > 0 ? round(($totalMemberSavings / $totalAssets) * 100) : 0 }}%</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-900/50">
                    <span class="h-3 w-3 rounded-md bg-[#FFDE73] shrink-0"></span>
                    <div class="truncate">
                        <p class="text-[10px] text-slate-400">Cash & Bank</p>
                        <p class="font-bold text-slate-700 dark:text-slate-200">{{ $totalAssets > 0 ? round(($cashAndBank / $totalAssets) * 100) : 0 }}%</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-900/50">
                    <span class="h-3 w-3 rounded-md bg-[#8E32E9] shrink-0"></span>
                    <div class="truncate">
                        <p class="text-[10px] text-slate-400">Share Capital</p>
                        <p class="font-bold text-slate-700 dark:text-slate-200">{{ $totalAssets > 0 ? round(($totalShareCapital / $totalAssets) * 100) : 0 }}%</p>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- ==================== 3. STELLAR QUICK ACTIONS SECTION ==================== -->
    <div class="rounded-2xl shadow-xs overflow-hidden border border-slate-200/80 dark:border-[#272738] bg-white dark:bg-[#181824]">
        
        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-[#FF6B35] via-[#FF7E47] to-[#FFA024] px-5 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-white shadow-sm">
            <div class="flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/20 backdrop-blur-xs font-bold text-sm">
                    ⚡
                </span>
                <h3 class="font-bold text-sm tracking-wide">Quick Actions</h3>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-white/90">
                <span>Instant financial transactions & member lifecycle operations</span>
                <span>💡</span>
            </div>
        </div>

        <!-- Action Buttons Horizontal Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-slate-100 dark:divide-slate-800 text-sm">
            
            <!-- Action 1: Add Member -->
            <a href="{{ route('members.create') }}" 
               class="group flex items-center justify-center gap-3 p-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#38CE3C]/15 text-[#38CE3C] group-hover:scale-105 transition">
                    <x-icon name="user-plus" class="w-4 h-4"/>
                </div>
                <div class="text-left">
                    <p class="font-semibold text-slate-800 dark:text-slate-200 text-xs">Add New Member</p>
                    <p class="text-[11px] text-slate-400">KYC & Bio Registration</p>
                </div>
            </a>

            <!-- Action 2: Apply Loan -->
            <a href="{{ route('loans.create') }}" 
               class="group flex items-center justify-center gap-3 p-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#8E32E9]/15 text-[#8E32E9] group-hover:scale-105 transition">
                    <x-icon name="file-plus-2" class="w-4 h-4"/>
                </div>
                <div class="text-left">
                    <p class="font-semibold text-slate-800 dark:text-slate-200 text-xs">Apply / Disburse Loan</p>
                    <p class="text-[11px] text-slate-400">Guarantor pledge matrix</p>
                </div>
            </a>

            <!-- Action 3: Record Deposit -->
            <a href="{{ route('transactions.create', ['type' => 'deposit']) }}" 
               class="group flex items-center justify-center gap-3 p-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#38CE3C]/15 text-[#38CE3C] group-hover:scale-105 transition">
                    <x-icon name="arrow-down-circle" class="w-4 h-4"/>
                </div>
                <div class="text-left">
                    <p class="font-semibold text-slate-800 dark:text-slate-200 text-xs">Record Deposit</p>
                    <p class="text-[11px] text-slate-400">Savings & Shares influx</p>
                </div>
            </a>

            <!-- Action 4: Record Withdrawal -->
            <a href="{{ route('transactions.create', ['type' => 'withdrawal']) }}" 
               class="group flex items-center justify-center gap-3 p-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#FF4D6B]/15 text-[#FF4D6B] group-hover:scale-105 transition">
                    <x-icon name="arrow-up-circle" class="w-4 h-4"/>
                </div>
                <div class="text-left">
                    <p class="font-semibold text-slate-800 dark:text-slate-200 text-xs">Record Withdrawal</p>
                    <p class="text-[11px] text-slate-400">Liquid payout & collateral check</p>
                </div>
            </a>

        </div>

    </div>

    <!-- ==================== 4. RECENT TRANSACTIONS TABLE ==================== -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 dark:bg-[#181824] dark:border-[#272738] overflow-hidden">
        
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Financial Ledger & Recent Transactions</h3>
                <p class="text-xs text-slate-400 mt-0.5">Latest account transactions and member ledger events</p>
            </div>
            <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-[#8E32E9] hover:text-[#7822d6] transition flex items-center gap-1">
                <span>View all Transactions</span>
                <x-icon name="arrow-right" class="w-3.5 h-3.5"/>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs whitespace-nowrap">
                <thead>
                    <tr class="text-slate-400 uppercase tracking-wider bg-slate-50/75 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-5 py-3 font-semibold">Account / Member</th>
                        <th class="px-5 py-3 font-semibold">Amount</th>
                        <th class="px-5 py-3 font-semibold">Channel / Type</th>
                        <th class="px-5 py-3 font-semibold">Created At</th>
                        <th class="px-5 py-3 font-semibold">Reference</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                    @forelse($recentTransactions as $tx)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-xs text-slate-700 dark:text-slate-200 border border-slate-200/60">
                                        {{ substr($tx->account_number ?? 'AC', 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 dark:text-slate-100">{{ $tx->account_number }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $tx->performed_by_username ?? 'System Operator' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 font-bold text-slate-800 dark:text-slate-100">
                                UGX {{ number_format($tx->amount, 2) }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    @if($tx->transaction_type === 'deposit')
                                        <span class="p-1 rounded-md bg-[#38CE3C]/10 text-[#38CE3C]"><x-icon name="arrow-down-left" class="w-3.5 h-3.5"/></span>
                                        <span class="font-medium">Deposit</span>
                                    @elseif($tx->transaction_type === 'withdrawal')
                                        <span class="p-1 rounded-md bg-[#FF4D6B]/10 text-[#FF4D6B]"><x-icon name="arrow-up-right" class="w-3.5 h-3.5"/></span>
                                        <span class="font-medium">Withdrawal</span>
                                    @elseif($tx->transaction_type === 'share_transfer')
                                        <span class="p-1 rounded-md bg-[#8E32E9]/10 text-[#8E32E9]"><x-icon name="refresh-cw" class="w-3.5 h-3.5"/></span>
                                        <span class="font-medium">Share Capital</span>
                                    @else
                                        <span class="font-medium">{{ ucfirst($tx->transaction_type) }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">
                                {{ $tx->created_at ? $tx->created_at->format('d M Y, h:i A') : 'N/A' }}
                            </td>
                            <td class="px-5 py-3.5 font-mono text-[11px] text-slate-500">
                                {{ $tx->reference }}
                            </td>
                            <td class="px-5 py-3.5">
                                @if($tx->transaction_type === 'deposit')
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-[#38CE3C] text-white shadow-xs">
                                        Completed
                                    </span>
                                @elseif($tx->transaction_type === 'withdrawal')
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-[#FF4D6B] text-white shadow-xs">
                                        Debited
                                    </span>
                                @elseif($tx->transaction_type === 'share_transfer')
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-[#8E32E9] text-white shadow-xs">
                                        Locked Equity
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-[#FFDE73] text-slate-800 shadow-xs">
                                        Processed
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                <x-icon name="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"/>
                                <p class="font-medium">No recent transactions recorded yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Ledger Footer -->
        <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div>
                Showing {{ $recentTransactions->count() > 0 ? 1 : 0 }} to {{ count($recentTransactions) }} of {{ number_format($totalTransactionsCount) }} entries
            </div>
            <div class="flex items-center gap-1">
                <a href="{{ route('transactions.index') }}" class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-400">
                    &lt;
                </a>
                <a href="{{ route('transactions.index') }}" class="px-3 py-1.5 rounded-lg font-bold text-white bg-[#8E32E9] shadow-xs">
                    1
                </a>
                <a href="{{ route('transactions.index') }}" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-600 dark:text-slate-300">
                    All
                </a>
                <a href="{{ route('transactions.index') }}" class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-400">
                    &gt;
                </a>
            </div>
        </div>

    </div>

    <!-- ==================== 5. FINANCIAL AUDIT & RISK MODULES ==================== -->
    <div class="grid gap-6 lg:grid-cols-12">
        
        <!-- Income & Surplus Statement (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 sm:p-6 dark:bg-[#181824] dark:border-[#272738]">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Income & Operational Statement</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Year-to-date statutory revenue and surplus generation</p>
                </div>
                <span class="text-xs font-bold text-[#38CE3C] px-3 py-1 rounded-full bg-[#38CE3C]/10 border border-[#38CE3C]/20">
                    Surplus: UGX {{ number_format($netSurplus) }}
                </span>
            </div>

            <div class="grid sm:grid-cols-2 gap-4 mt-4 text-xs">
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 space-y-2">
                    <span class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Operating Revenues</span>
                    <div class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-800">
                        <span class="text-slate-600 dark:text-slate-300">Interest on Loans</span>
                        <span class="font-bold">UGX {{ number_format($interestIncome) }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-800">
                        <span class="text-slate-600 dark:text-slate-300">Fees & Penalties</span>
                        <span class="font-bold">UGX {{ number_format($feesIncome) }}</span>
                    </div>
                    <div class="flex justify-between py-1 font-bold text-slate-800 dark:text-white">
                        <span>Total Gross Revenue</span>
                        <span class="text-[#38CE3C]">UGX {{ number_format($grossIncome) }}</span>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 space-y-2">
                    <span class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Expenses & Provisions</span>
                    <div class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-800">
                        <span class="text-slate-600 dark:text-slate-300">Operational Expenses</span>
                        <span class="font-bold text-[#FF4D6B]">- UGX {{ number_format($operationalExpenses) }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-800">
                        <span class="text-slate-600 dark:text-slate-300">Loan Loss Provisioning</span>
                        <span class="font-bold text-[#FF4D6B]">- UGX {{ number_format($loanLossProvision) }}</span>
                    </div>
                    <div class="flex justify-between py-1 font-bold text-slate-800 dark:text-white">
                        <span>Total Costs Deducted</span>
                        <span class="text-[#FF4D6B]">- UGX {{ number_format($operationalExpenses + $loanLossProvision) }}</span>
                    </div>
                </div>
            </div>

            <!-- Net Surplus Banner -->
            <div class="mt-4 p-4 rounded-xl bg-gradient-to-r from-[#38CE3C]/10 via-[#38CE3C]/5 to-transparent border border-[#38CE3C]/20 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-100">Annual Net Profit Available for Dividend Distribution</p>
                    <p class="text-[11px] text-slate-400">Subject to AGM statutory appropriation and retained earnings</p>
                </div>
                <div class="text-lg font-black text-[#38CE3C]">
                    UGX {{ number_format($netSurplus) }}
                </div>
            </div>
        </div>

        <!-- UMRA Risk & Capital Compliance (4 cols, Dark Card) -->
        <div class="lg:col-span-4 bg-[#181824] text-white rounded-2xl shadow-xs border border-[#272738] p-5 sm:p-6 flex flex-col justify-between">
            <div class="pb-3 border-b border-slate-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-100">UMRA Risk Compliance</h3>
                    <span class="text-[10px] font-bold text-[#38CE3C] uppercase px-2 py-0.5 rounded bg-[#38CE3C]/15 border border-[#38CE3C]/30">Tier 4 Act</span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">Prudential regulatory ratios</p>
            </div>

            <div class="space-y-4 my-4">
                
                <!-- Ratio 1: PAR -->
                <div>
                    <div class="flex justify-between items-center text-xs mb-1">
                        <span class="text-slate-300 font-medium">Portfolio at Risk (PAR &gt; 30)</span>
                        <span class="font-bold {{ $parRatio > 5 ? 'text-[#FF4D6B]' : 'text-[#38CE3C]' }}">{{ $parRatio }}%</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $parRatio > 5 ? 'bg-[#FF4D6B]' : 'bg-[#38CE3C]' }}" style="width: {{ min($parRatio * 10, 100) }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">Regulator benchmark: &lt; 5.0%</p>
                </div>

                <!-- Ratio 2: Liquidity Ratio -->
                <div>
                    <div class="flex justify-between items-center text-xs mb-1">
                        <span class="text-slate-300 font-medium">Liquidity Ratio</span>
                        <span class="font-bold {{ $liquidityRatio < 15 ? 'text-[#FF4D6B]' : 'text-[#38CE3C]' }}">{{ number_format($liquidityRatio, 1) }}%</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $liquidityRatio < 15 ? 'bg-[#FF4D6B]' : 'bg-[#38CE3C]' }}" style="width: {{ min($liquidityRatio, 100) }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">UMRA minimum: 15.0%</p>
                </div>

                <!-- Ratio 3: Capital Adequacy -->
                <div>
                    <div class="flex justify-between items-center text-xs mb-1">
                        <span class="text-slate-300 font-medium">Capital Adequacy Ratio</span>
                        <span class="font-bold {{ $capitalAdequacy < 10 ? 'text-[#FF4D6B]' : 'text-[#38CE3C]' }}">{{ number_format($capitalAdequacy, 1) }}%</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $capitalAdequacy < 10 ? 'bg-[#FF4D6B]' : 'bg-[#38CE3C]' }}" style="width: {{ min($capitalAdequacy * 2, 100) }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">UMRA minimum: 10.0%</p>
                </div>

            </div>

            <div class="pt-3 border-t border-slate-800 flex justify-between items-center text-xs text-slate-400">
                <span>Non-Performing Loans</span>
                <span class="font-bold text-[#FF4D6B]">UGX {{ number_format($nplAmount) }}</span>
            </div>
        </div>

    </div>

</div>

<!-- ==================== CLASSIC PDF PRINT LAYOUT (PRESERVED) ==================== -->
<div class="pdf-only">
  <!-- PAGE 1: INCOME STATEMENT -->
  <div class="pdf-page">
    <div class="pdf-center">
      <div class="pdf-title">{{ strtoupper($orgName) }}</div>
      <div class="pdf-subtitle">Income Statement</div>
      <div class="pdf-meta">For the Year Ended {{ date('F j, Y') }}</div>
    </div>
    <div class="pdf-section-label">Revenue:</div>
    <div class="pdf-row indent"><span>Interest Income from Loans</span><span class="amount">{{ number_format($interestIncome) }}</span></div>
    <div class="pdf-row indent underline"><span>Fees &amp; Penalties</span><span class="amount">{{ number_format($feesIncome) }}</span></div>
    <div class="pdf-row bold"><span>Gross Total Income</span><span class="amount">UGX {{ number_format($grossIncome) }}</span></div>
    <div class="pdf-spacer"></div>
    <div class="pdf-section-label">Operating Expenses:</div>
    <div class="pdf-row indent"><span>Interest Paid on Member Savings</span><span class="amount">{{ number_format($interestPaid) }}</span></div>
    <div class="pdf-row indent"><span>Operational Expenses</span><span class="amount">{{ number_format($operationalExpenses) }}</span></div>
    <div class="pdf-row indent underline"><span>Loan Loss Provisioning</span><span class="amount">{{ number_format($loanLossProvision) }}</span></div>
    <div class="pdf-row"><span>Total Operating Expenses</span><span class="amount">{{ number_format($interestPaid + $operationalExpenses + $loanLossProvision) }}</span></div>
    <div class="pdf-spacer"></div>
    <div class="pdf-divider"></div>
    <div class="pdf-row double-underline"><span>NET SURPLUS (PROFIT)</span><span class="amount">UGX {{ number_format($netSurplus) }}</span></div>
  </div>

  <!-- PAGE 2: BALANCE SHEET -->
  <div class="pdf-page">
    <div class="pdf-center">
      <div class="pdf-title">{{ strtoupper($orgName) }}</div>
      <div class="pdf-subtitle">Balance Sheet</div>
      <div class="pdf-meta">As at {{ date('F j, Y') }}</div>
    </div>
    <div class="pdf-two-col">
      <div>
        <div class="pdf-col-title">Assets</div>
        <div class="pdf-section-label">Current Assets:</div>
        <div class="pdf-row indent"><span>Cash &amp; Bank Balances</span><span class="amount">{{ number_format($cashAndBank) }}</span></div>
        <div class="pdf-row indent underline"><span>Accrued Interest Receivable</span><span class="amount">{{ number_format($accruedInterest) }}</span></div>
        <div class="pdf-row"><span>Total Current Assets</span><span class="amount">{{ number_format($cashAndBank + $accruedInterest) }}</span></div>
        <div class="pdf-spacer"></div>
        <div class="pdf-section-label">Non-Current Assets:</div>
        <div class="pdf-row indent"><span>Gross Loan Portfolio</span><span class="amount">{{ number_format($grossLoanPortfolio) }}</span></div>
        <div class="pdf-row indent underline"><span>Less: Loan Loss Provision</span><span class="amount">({{ number_format($loanLossProvision) }})</span></div>
        <div class="pdf-row"><span>Net Loan Portfolio</span><span class="amount">{{ number_format($grossLoanPortfolio - $loanLossProvision) }}</span></div>
        <div class="pdf-divider"></div>
        <div class="pdf-total-bar"><span>TOTAL ASSETS</span><span class="amount">UGX {{ number_format($totalAssets - $loanLossProvision) }}</span></div>
      </div>
      <div>
        <div class="pdf-col-title">Liabilities &amp; Equity</div>
        <div class="pdf-section-label">Liabilities:</div>
        <div class="pdf-row indent"><span>Total Member Savings</span><span class="amount">{{ number_format($totalMemberSavings) }}</span></div>
        <div class="pdf-row indent underline"><span>Accounts Payable</span><span class="amount">{{ number_format($accountsPayable) }}</span></div>
        <div class="pdf-row"><span>Total Liabilities</span><span class="amount">{{ number_format($totalMemberSavings + $accountsPayable) }}</span></div>
        <div class="pdf-spacer"></div>
        <div class="pdf-section-label">Owners' Equity:</div>
        <div class="pdf-row indent"><span>Total Share Capital</span><span class="amount">{{ number_format($totalShareCapital) }}</span></div>
        <div class="pdf-row indent underline"><span>Retained Earnings</span><span class="amount">{{ number_format($retainedEarnings) }}</span></div>
        <div class="pdf-row"><span>Total Equity</span><span class="amount">{{ number_format($totalShareCapital + $retainedEarnings) }}</span></div>
        <div class="pdf-divider"></div>
        <div class="pdf-total-bar"><span>TOTAL LIAB. &amp; EQUITY</span><span class="amount">UGX {{ number_format($totalMemberSavings + $accountsPayable + $totalShareCapital + $retainedEarnings) }}</span></div>
      </div>
    </div>
  </div>
</div>
@endsection
