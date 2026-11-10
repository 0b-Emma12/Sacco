@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-7xl print-hidden">

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
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200/90 rounded-xl shadow-xs hover:bg-slate-50 transition dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Export</span>
                    <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400"></i>
                </button>
                <div x-show="open" 
                     @click.away="open = false" 
                     style="display: none;" 
                     class="absolute right-0 mt-2 w-44 bg-white border border-slate-100 rounded-xl shadow-xl z-50 overflow-hidden dark:bg-[#181824] dark:border-[#272738] p-1">
                    <a href="{{ route('dashboard.export') }}" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 rounded-lg hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800 transition">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-[#38CE3C]"></i> Export CSV
                    </a>
                    <button onclick="window.print()" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 rounded-lg hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800 transition text-left">
                        <i data-lucide="printer" class="w-4 h-4 text-[#8E32E9]"></i> Print Financial PDF
                    </button>
                </div>
            </div>

            <a href="{{ route('reports.index') }}" 
               class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-[#38CE3C] rounded-xl shadow-sm shadow-[#38CE3C]/25 hover:bg-[#2cb030] transition">
                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
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
                    <i data-lucide="landmark" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    UGX {{ number_format($totalAssets) }}
                </div>
                <div class="flex items-center gap-1.5 mt-1 text-[11px] font-medium text-[#38CE3C]">
                    <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                    <span>Solvent Portfolio</span>
                </div>
            </div>
        </div>

        <!-- CARD 2: Member Savings (Purple #8E32E9) -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 sm:p-5 dark:bg-[#181824] dark:border-[#272738] flex flex-col justify-between hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">Member Savings</span>
                <span class="p-2 rounded-xl bg-[#8E32E9]/10 text-[#8E32E9]">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
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
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
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
                    <i data-lucide="coins" class="w-4 h-4"></i>
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
                    <i data-lucide="users" class="w-4 h-4"></i>
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
        
        <!-- Performance Indicator Multi-bar Chart (Stellar left card, 7 columns) -->
        <div class="lg:col-span-7 bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 sm:p-6 dark:bg-[#181824] dark:border-[#272738] flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Performance Indicator</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Monthly transaction volume, loan activity & repayments</p>
                </div>
                <!-- Time range buttons (Stellar reference: 7d, 2w, 1m, 3m, 6m) -->
                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                    <button class="px-2.5 py-1 rounded-lg text-slate-500 hover:text-slate-800 dark:hover:text-white transition">7d</button>
                    <button class="px-2.5 py-1 rounded-lg text-slate-500 hover:text-slate-800 dark:hover:text-white transition">2w</button>
                    <button class="px-2.5 py-1 rounded-lg bg-white shadow-xs text-slate-800 dark:bg-[#181824] dark:text-white font-bold">1m</button>
                    <button class="px-2.5 py-1 rounded-lg text-slate-500 hover:text-slate-800 dark:hover:text-white transition">3m</button>
                    <button class="px-2.5 py-1 rounded-lg text-slate-500 hover:text-slate-800 dark:hover:text-white transition">6m</button>
                </div>
            </div>

            <!-- Legend Pills (Stellar Reference: Complaints, Task Done, Attends) -->
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

            <!-- Chart.js Canvas -->
            <div class="h-64 sm:h-72 w-full mt-4">
                <canvas id="performanceChart"></canvas>
            </div>
        </div>

        <!-- Sessions By Channel / Asset Allocation Doughnut Chart (Stellar right card, 5 columns) -->
        <div class="lg:col-span-5 bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 sm:p-6 dark:bg-[#181824] dark:border-[#272738] flex flex-col justify-between">
            <div class="pb-3 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Asset & Capital Allocation</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Composition of SACCO liquidity and equity</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C]">
                    Balanced
                </span>
            </div>

            <!-- Doughnut with Center Lead Number (Stellar Reference) -->
            <div class="relative h-60 w-full flex items-center justify-center my-3">
                <canvas id="sessionsChannelChart"></canvas>
                <!-- Central Text Badge -->
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-2xl font-extrabold text-slate-800 dark:text-white tracking-tight">
                        {{ $totalAssets > 1000000 ? round($totalAssets / 1000000, 1) . 'M' : number_format($totalAssets) }}
                    </span>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Portfolio</span>
                </div>
            </div>

            <!-- Stellar Style Bottom Legend Tags -->
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
        
        <!-- Header Banner (Stellar Reference: Orange/Amber Gradient Header with subtitle) -->
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

        <!-- Action Buttons Horizontal Row (Stellar Reference: Add Client, Create Quote, Enter Payment, Create Invoice) -->
        <div class="grid grid-cols-2 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-slate-100 dark:divide-slate-800 text-sm">
            
            <!-- Action 1: Add Member -->
            <a href="{{ route('members.create') }}" 
               class="group flex items-center justify-center gap-3 p-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#38CE3C]/15 text-[#38CE3C] group-hover:scale-105 transition">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
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
                    <i data-lucide="file-plus-2" class="w-4 h-4"></i>
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
                    <i data-lucide="arrow-down-circle" class="w-4 h-4"></i>
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
                    <i data-lucide="arrow-up-circle" class="w-4 h-4"></i>
                </div>
                <div class="text-left">
                    <p class="font-semibold text-slate-800 dark:text-slate-200 text-xs">Record Withdrawal</p>
                    <p class="text-[11px] text-slate-400">Liquid payout & collateral check</p>
                </div>
            </a>

        </div>

    </div>

    <!-- ==================== 4. STELLAR DATA PRESENTATION TABLE ==================== -->
    <!-- Styled directly like "Products Inventory" in the Stellar reference -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 dark:bg-[#181824] dark:border-[#272738] overflow-hidden">
        
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Financial Ledger & Recent Transactions</h3>
                <p class="text-xs text-slate-400 mt-0.5">Latest account transactions and member ledger events</p>
            </div>
            <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-[#8E32E9] hover:text-[#7822d6] transition flex items-center gap-1">
                <span>View all Transactions</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
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
                                        <span class="p-1 rounded-md bg-[#38CE3C]/10 text-[#38CE3C]"><i data-lucide="arrow-down-left" class="w-3.5 h-3.5"></i></span>
                                        <span class="font-medium">Deposit</span>
                                    @elseif($tx->transaction_type === 'withdrawal')
                                        <span class="p-1 rounded-md bg-[#FF4D6B]/10 text-[#FF4D6B]"><i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i></span>
                                        <span class="font-medium">Withdrawal</span>
                                    @elseif($tx->transaction_type === 'share_transfer')
                                        <span class="p-1 rounded-md bg-[#8E32E9]/10 text-[#8E32E9]"><i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i></span>
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
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                <p class="font-medium">No recent transactions recorded yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Stellar Style Pagination Bar -->
        <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div>
                Showing 1 to {{ count($recentTransactions) }} of {{ count($recentTransactions) }} entries
            </div>
            <!-- Stellar reference: < 1 2 3 > with purple button for active page -->
            <div class="flex items-center gap-1">
                <button class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-400">
                    &lt;
                </button>
                <button class="px-3 py-1.5 rounded-lg font-bold text-white bg-[#8E32E9] shadow-xs">
                    1
                </button>
                <button class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-600 dark:text-slate-300">
                    2
                </button>
                <button class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-600 dark:text-slate-300">
                    3
                </button>
                <button class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-400">
                    &gt;
                </button>
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

<!-- ==================== CHART.JS SCRIPT INITIALIZATION ==================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Performance Indicator Bar Chart (Stellar Style: Jan - Dec)
    const ctxPerformance = document.getElementById('performanceChart');
    if (ctxPerformance) {
        new Chart(ctxPerformance, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                    {
                        label: 'Deposits',
                        data: [65, 59, 80, 81, 56, 55, 70, 75, 82, 90, 85, 95],
                        backgroundColor: '#00d2d3',
                        borderRadius: 4,
                        barPercentage: 0.35,
                    },
                    {
                        label: 'Loans Disbursed',
                        data: [45, 49, 60, 71, 46, 35, 50, 60, 68, 72, 65, 80],
                        backgroundColor: '#8E32E9',
                        borderRadius: 4,
                        barPercentage: 0.35,
                    },
                    {
                        label: 'Repayments',
                        data: [28, 48, 40, 19, 86, 27, 40, 45, 50, 62, 58, 70],
                        backgroundColor: '#FF4D6B',
                        borderRadius: 4,
                        barPercentage: 0.35,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#181824',
                        titleColor: '#fff',
                        bodyColor: '#a6a8b7',
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', size: 11 }, color: '#94a3b8' }
                    },
                    y: {
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: { font: { family: 'Inter', size: 11 }, color: '#94a3b8' }
                    }
                }
            }
        });
    }

    // 2. Asset & Channel Allocation Doughnut Chart (Stellar Ring Style)
    const ctxChannel = document.getElementById('sessionsChannelChart');
    if (ctxChannel) {
        const grossLoan = {{ $grossLoanPortfolio > 0 ? $grossLoanPortfolio : 45 }};
        const savings = {{ $totalMemberSavings > 0 ? $totalMemberSavings : 35 }};
        const cash = {{ $cashAndBank > 0 ? $cashAndBank : 15 }};
        const shares = {{ $totalShareCapital > 0 ? $totalShareCapital : 10 }};

        new Chart(ctxChannel, {
            type: 'doughnut',
            data: {
                labels: ['Gross Loans', 'Savings Pool', 'Cash & Bank', 'Share Capital'],
                datasets: [{
                    data: [grossLoan, savings, cash, shares],
                    backgroundColor: [
                        '#FF4D6B', // Coral
                        '#38CE3C', // Green
                        '#FFDE73', // Yellow
                        '#8E32E9', // Purple
                    ],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '76%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#181824',
                        titleColor: '#fff',
                        bodyColor: '#a6a8b7',
                        padding: 10,
                        cornerRadius: 8,
                    }
                }
            }
        });
    }
});
</script>

<!-- ==================== CLASSIC PDF PRINT LAYOUT (PRESERVED) ==================== -->
<style>
  @media screen { .pdf-only { display: none; } }
  @media print {
    @page { size: A4 portrait; margin: 18mm 20mm; }
    body, html { margin: 0; padding: 0; background: #fff; }
    .pdf-only {
      display: block !important;
      width: 170mm;
      font-family: Georgia, 'Times New Roman', serif;
      font-size: 10pt;
      color: #000;
      background: #fff;
      line-height: 1.5;
    }
    .pdf-page { page-break-after: always; }
    .pdf-page:last-child { page-break-after: avoid; }
    .pdf-center { text-align: center; }
    .pdf-title { font-size: 14pt; font-weight: bold; margin-bottom: 2pt; }
    .pdf-subtitle { font-size: 11pt; font-weight: bold; margin-bottom: 2pt; }
    .pdf-meta { font-size: 9pt; margin-bottom: 16pt; }
    .pdf-section-label { font-weight: bold; margin-top: 10pt; margin-bottom: 4pt; }
    .pdf-row { display: flex; justify-content: space-between; padding: 1.5pt 0; }
    .pdf-row.indent { padding-left: 20pt; }
    .pdf-row .amount { min-width: 80pt; text-align: right; white-space: nowrap; }
    .pdf-row.underline .amount { border-bottom: 1pt solid #000; }
    .pdf-row.bold { font-weight: bold; }
    .pdf-row.double-underline .amount { border-bottom: 3pt double #000; font-weight: bold; }
    .pdf-spacer { height: 10pt; }
    .pdf-divider { border-top: 1pt solid #ccc; margin: 8pt 0; }
    .pdf-two-col { display: flex; gap: 20pt; }
    .pdf-two-col > div { flex: 1; }
    .pdf-col-title { text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 8pt; }
    .pdf-total-bar { display: flex; justify-content: space-between; font-weight: bold; border-top: 1pt solid #000; padding-top: 4pt; margin-top: 4pt; }
  }
</style>

<div class="pdf-only">
  <!-- PAGE 1: INCOME STATEMENT -->
  <div class="pdf-page">
    <div class="pdf-center">
      <div class="pdf-title">OPEN SACCO</div>
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
      <div class="pdf-title">OPEN SACCO</div>
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
        <div class="pdf-row indent underline"><span>Accounts Payable</span><span class="amount">0</span></div>
        <div class="pdf-row"><span>Total Liabilities</span><span class="amount">{{ number_format($totalMemberSavings) }}</span></div>
        <div class="pdf-spacer"></div>
        <div class="pdf-section-label">Owners' Equity:</div>
        <div class="pdf-row indent"><span>Total Share Capital</span><span class="amount">{{ number_format($totalShareCapital) }}</span></div>
        <div class="pdf-row indent underline"><span>Retained Earnings</span><span class="amount">{{ number_format($retainedEarnings) }}</span></div>
        <div class="pdf-row"><span>Total Equity</span><span class="amount">{{ number_format($totalShareCapital + $retainedEarnings) }}</span></div>
        <div class="pdf-divider"></div>
        <div class="pdf-total-bar"><span>TOTAL LIAB. &amp; EQUITY</span><span class="amount">UGX {{ number_format($totalMemberSavings + $totalShareCapital + $retainedEarnings) }}</span></div>
      </div>
    </div>
  </div>
</div>
@endsection
