@extends('layouts.app')
@section('title', 'SACCO Financial Overview')
@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-7xl print-hidden">
      
    <!-- PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">
                Period: YTD {{ date('Y') }} | Currency: UGX | Last Updated: {{ date('M d, Y') }}
            </div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">SACCO Financial Overview & Asset Dashboard</h1>
        </div>
        <div class="flex items-center gap-3">
            <!-- Dropdown for Export Options -->
            <div class="relative group" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded hover:bg-slate-50 transition print-hidden dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
                    <i data-lucide="download" class="w-4 h-4"></i> Export <i data-lucide="chevron-down" class="w-3 h-3"></i>
                </button>
                <div x-show="open" style="display: none;" class="absolute right-0 mt-2 w-40 bg-white border border-slate-200 rounded-lg shadow-lg z-50 overflow-hidden dark:bg-slate-800 dark:border-slate-700">
                    <a href="{{ route('dashboard.export') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700 transition">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Export to CSV
                    </a>
                    <button onclick="window.print()" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700 transition text-left">
                        <i data-lucide="file-text" class="w-4 h-4"></i> Export to PDF
                    </button>
                </div>
            </div>
            
            <a href="{{ route('reports.index') }}" class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-white bg-sacco border border-sacco rounded hover:bg-sacco-dark transition print-hidden">
                <i data-lucide="bar-chart-2" class="w-4 h-4"></i> View Reports
            </a>
        </div>
    </div>

    <!-- 1. TOP KPI TILES -->
    <div class="grid gap-4 grid-cols-2 lg:grid-cols-5">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 dark:bg-slate-900 dark:border-slate-800 flex flex-col">
            <div class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">Total Assets</div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 mt-1">UGX {{ number_format($totalAssets) }}</h2>
            <p class="text-[10px] text-slate-500 mt-auto pt-2">Total value owned</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 dark:bg-slate-900 dark:border-slate-800 flex flex-col border-l-4 border-l-emerald-500">
            <div class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">Total Member Savings</div>
            <h2 class="text-lg font-bold text-emerald-600 mt-1">UGX {{ number_format($totalMemberSavings) }}</h2>
            <p class="text-[10px] text-slate-500 mt-auto pt-2">Liquid deposits</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 dark:bg-slate-900 dark:border-slate-800 flex flex-col border-l-4 border-l-sacco">
            <div class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">Total Share Capital</div>
            <h2 class="text-lg font-bold text-sacco mt-1">UGX {{ number_format($totalShareCapital) }}</h2>
            <p class="text-[10px] text-slate-500 mt-auto pt-2">Member equity/ownership</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 dark:bg-slate-900 dark:border-slate-800 flex flex-col">
            <div class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">Total Interest (YTD)</div>
            <h2 class="text-lg font-bold text-indigo-600 mt-1">UGX {{ number_format($interestIncome) }}</h2>
            <p class="text-[10px] text-slate-500 mt-auto pt-2">Income from loans</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 dark:bg-slate-900 dark:border-slate-800 flex flex-col">
            <div class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">Active Members</div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 mt-1">{{ number_format($activeMembers) }}</h2>
            <p class="text-[10px] text-slate-500 mt-auto pt-2">With active accounts</p>
        </div>
    </div>
    
    <div class="grid gap-6 lg:grid-cols-2">
        <!-- 2. ASSET BREAKDOWN -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden dark:bg-slate-900 dark:border-slate-800 flex flex-col">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Asset Breakdown (Where Money Is)</h3>
            </div>
            <div class="p-0 flex-1 overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead>
                        <tr class="text-slate-500 text-xs uppercase tracking-wider bg-slate-50 dark:bg-slate-900/50">
                            <th class="px-4 py-3 font-semibold">Category</th>
                            <th class="px-4 py-3 font-semibold text-right">Amount (UGX)</th>
                            <th class="px-4 py-3 font-semibold text-right">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3"><div class="font-medium">Gross Loan Portfolio</div><div class="text-[10px] text-slate-400 whitespace-normal">Total principal disbursed</div></td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format($grossLoanPortfolio) }}</td>
                            <td class="px-4 py-3 text-right">{{ $totalAssets > 0 ? round(($grossLoanPortfolio/$totalAssets)*100, 1) : 0 }}%</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3"><div class="font-medium">Cash & Bank Balances</div><div class="text-[10px] text-slate-400 whitespace-normal">Bank & mobile money</div></td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format($cashAndBank) }}</td>
                            <td class="px-4 py-3 text-right">{{ $totalAssets > 0 ? round(($cashAndBank/$totalAssets)*100, 1) : 0 }}%</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3"><div class="font-medium">Investments & Securities</div><div class="text-[10px] text-slate-400 whitespace-normal">Fixed deposits, bonds</div></td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format($investments) }}</td>
                            <td class="px-4 py-3 text-right">{{ $totalAssets > 0 ? round(($investments/$totalAssets)*100, 1) : 0 }}%</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3"><div class="font-medium">Fixed Assets</div><div class="text-[10px] text-slate-400 whitespace-normal">Equipment, property</div></td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format($fixedAssets) }}</td>
                            <td class="px-4 py-3 text-right">{{ $totalAssets > 0 ? round(($fixedAssets/$totalAssets)*100, 1) : 0 }}%</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3"><div class="font-medium">Accrued Interest</div><div class="text-[10px] text-slate-400 whitespace-normal">Due but not paid</div></td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format($accruedInterest) }}</td>
                            <td class="px-4 py-3 text-right">{{ $totalAssets > 0 ? round(($accruedInterest/$totalAssets)*100, 1) : 0 }}%</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-slate-900/50 border-t-2 border-slate-200 dark:border-slate-700">
                        <tr>
                            <td class="px-4 py-3 font-bold text-slate-700 dark:text-slate-300">TOTAL ASSETS</td>
                            <td class="px-4 py-3 text-right font-bold text-slate-800 dark:text-slate-100">{{ number_format($totalAssets) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-slate-800 dark:text-slate-100">100%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- 3. LIABILITIES & EQUITY -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden dark:bg-slate-900 dark:border-slate-800 flex flex-col">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Liabilities & Equity (Source of Money)</h3>
            </div>
            <div class="p-0 flex-1 overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead>
                        <tr class="text-slate-500 text-xs uppercase tracking-wider bg-slate-50 dark:bg-slate-900/50">
                            <th class="px-4 py-3 font-semibold">Category</th>
                            <th class="px-4 py-3 font-semibold text-right">Amount (UGX)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr class="hover:bg-slate-50/50 bg-emerald-50/30 dark:bg-emerald-900/10">
                            <td class="px-4 py-3"><div class="font-medium text-emerald-700 dark:text-emerald-400">Total Member Savings</div><div class="text-[10px] text-slate-400 whitespace-normal">Ordinary & Fixed deposits</div></td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-700 dark:text-emerald-400">{{ number_format($totalMemberSavings) }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50 bg-emerald-50/30 dark:bg-emerald-900/10">
                            <td class="px-4 py-3"><div class="font-medium text-sacco-dark dark:text-sacco">Total Share Capital</div><div class="text-[10px] text-slate-400 whitespace-normal">Non-withdrawable shares</div></td>
                            <td class="px-4 py-3 text-right font-bold text-sacco-dark dark:text-sacco">{{ number_format($totalShareCapital) }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3"><div class="font-medium">Statutory Reserve Fund</div><div class="text-[10px] text-slate-400 whitespace-normal">UMRA mandated reserve</div></td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format($statutoryReserve) }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3"><div class="font-medium">Retained Earnings</div><div class="text-[10px] text-slate-400 whitespace-normal">Profits kept for growth</div></td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format($retainedEarnings) }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3"><div class="font-medium">External Borrowings</div><div class="text-[10px] text-slate-400 whitespace-normal">Loans from commercial banks</div></td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format($externalBorrowings) }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3"><div class="font-medium">Accounts Payable</div><div class="text-[10px] text-slate-400 whitespace-normal">Unpaid dividends, taxes</div></td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format($accountsPayable) }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-slate-900/50 border-t-2 border-slate-200 dark:border-slate-700">
                        <tr>
                            <td class="px-4 py-3 font-bold text-slate-700 dark:text-slate-300">TOTAL LIABILITIES & EQUITY</td>
                            <td class="px-4 py-3 text-right font-bold text-slate-800 dark:text-slate-100">{{ number_format($totalMemberSavings + $totalShareCapital + $statutoryReserve + $retainedEarnings + $externalBorrowings + $accountsPayable) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-12">
        <!-- 4. INCOME & REVENUE STATEMENT -->
        <div class="lg:col-span-8 bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden dark:bg-slate-900 dark:border-slate-800">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-950/50">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Income & Revenue Statement (YTD)</h3>
            </div>
            <div class="p-5">
                <div class="grid sm:grid-cols-2 gap-x-8 gap-y-2">
                    <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800/50 text-sm">
                        <span class="text-slate-600 dark:text-slate-300">Interest from Loans</span>
                        <span class="font-medium">UGX {{ number_format($interestIncome) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800/50 text-sm">
                        <span class="text-slate-600 dark:text-slate-300">Fees & Penalties</span>
                        <span class="font-medium">UGX {{ number_format($feesIncome) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800/50 text-sm">
                        <span class="text-slate-600 dark:text-slate-300">Investment Income</span>
                        <span class="font-medium">UGX {{ number_format($investmentIncome) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800/50 text-sm">
                        <span class="text-slate-600 dark:text-slate-300">Other Income</span>
                        <span class="font-medium">UGX {{ number_format($otherIncome) }}</span>
                    </div>
                </div>
                
                <div class="flex justify-between py-3 mt-2 bg-slate-50 dark:bg-slate-900/50 px-4 rounded-lg font-bold text-sm">
                    <span>Gross Total Income</span>
                    <span class="text-indigo-600 dark:text-indigo-400">UGX {{ number_format($grossIncome) }}</span>
                </div>

                <div class="mt-6 mb-2 text-xs font-bold text-slate-400 uppercase tracking-wider">Less Expenses</div>
                <div class="grid sm:grid-cols-2 gap-x-8 gap-y-2">
                    <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800/50 text-sm">
                        <span class="text-slate-600 dark:text-slate-300">Interest Paid on Savings</span>
                        <span class="font-medium text-rose-600">- UGX {{ number_format($interestPaid) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800/50 text-sm">
                        <span class="text-slate-600 dark:text-slate-300">Operational Expenses</span>
                        <span class="font-medium text-rose-600">- UGX {{ number_format($operationalExpenses) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800/50 text-sm">
                        <span class="text-slate-600 dark:text-slate-300">Loan Loss Provisioning</span>
                        <span class="font-medium text-rose-600">- UGX {{ number_format($loanLossProvision) }}</span>
                    </div>
                </div>

                <div class="flex justify-between py-4 mt-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/30 px-4 rounded-lg">
                    <span class="font-bold text-slate-800 dark:text-slate-200">Net Surplus (Profit)</span>
                    <span class="font-bold text-lg text-emerald-600">UGX {{ number_format($netSurplus) }}</span>
                </div>
                <p class="text-[10px] text-slate-500 text-center mt-2">This surplus will be distributed as Dividends on Shares at the AGM.</p>
            </div>
        </div>

        <!-- 5. PORTFOLIO QUALITY & RISK (UMRA) -->
        <div class="lg:col-span-4 bg-slate-900 text-white rounded-xl shadow-sm border border-slate-800 overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-800 bg-slate-950/50">
                <h3 class="text-base font-bold text-slate-100">UMRA Risk Compliance</h3>
                <p class="text-xs text-slate-400 mt-1">Portfolio Quality Metrics</p>
            </div>
            <div class="p-5 flex-1 flex flex-col justify-center gap-6">
                
                <div>
                    <div class="flex justify-between items-end mb-1">
                        <span class="text-sm font-medium text-slate-300">Portfolio at Risk (PAR > 30)</span>
                        <span class="font-bold text-lg {{ $parRatio > 5 ? 'text-rose-400' : 'text-emerald-400' }}">{{ $parRatio }}%</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $parRatio > 5 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ min($parRatio * 10, 100) }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">Target: < 5%</p>
                </div>

                <div>
                    <div class="flex justify-between items-end mb-1">
                        <span class="text-sm font-medium text-slate-300">Liquidity Ratio</span>
                        <span class="font-bold text-lg {{ $liquidityRatio < 15 ? 'text-rose-400' : 'text-emerald-400' }}">{{ number_format($liquidityRatio, 1) }}%</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $liquidityRatio < 15 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ min($liquidityRatio, 100) }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">UMRA Minimum: 15%</p>
                </div>

                <div>
                    <div class="flex justify-between items-end mb-1">
                        <span class="text-sm font-medium text-slate-300">Capital Adequacy Ratio</span>
                        <span class="font-bold text-lg {{ $capitalAdequacy < 10 ? 'text-rose-400' : 'text-emerald-400' }}">{{ number_format($capitalAdequacy, 1) }}%</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $capitalAdequacy < 10 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ min($capitalAdequacy * 2, 100) }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">UMRA Minimum: 10%</p>
                </div>

                <div class="mt-auto pt-4 border-t border-slate-800 text-sm">
                    <div class="flex justify-between py-1">
                        <span class="text-slate-400">Non-Performing Loans</span>
                        <span class="font-medium text-rose-300">UGX {{ number_format($nplAmount) }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-400">Provision Coverage</span>
                        <span class="font-medium text-emerald-300">{{ $provisionCoverage }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CLASSIC PDF PRINT LAYOUT -->
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

    .pdf-section-label {
      font-weight: bold;
      margin-top: 10pt;
      margin-bottom: 4pt;
    }

    .pdf-row {
      display: flex;
      justify-content: space-between;
      padding: 1.5pt 0;
    }
    .pdf-row.indent { padding-left: 20pt; }
    .pdf-row.indent2 { padding-left: 36pt; }

    .pdf-row .amount {
      min-width: 80pt;
      text-align: right;
      white-space: nowrap;
    }
    .pdf-row.underline .amount { border-bottom: 1pt solid #000; }
    .pdf-row.bold { font-weight: bold; }
    .pdf-row.double-underline .amount {
      border-bottom: 3pt double #000;
      font-weight: bold;
    }

    .pdf-spacer { height: 10pt; }
    .pdf-spacer-sm { height: 4pt; }
    .pdf-divider { border-top: 1pt solid #ccc; margin: 8pt 0; }

    .pdf-two-col {
      display: flex;
      gap: 20pt;
    }
    .pdf-two-col > div { flex: 1; }

    .pdf-col-title {
      text-align: center;
      font-weight: bold;
      text-decoration: underline;
      margin-bottom: 8pt;
    }

    .pdf-total-bar {
      display: flex;
      justify-content: space-between;
      font-weight: bold;
      border-top: 1pt solid #000;
      padding-top: 4pt;
      margin-top: 4pt;
    }
    .pdf-total-bar .amount { text-align: right; }
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
    <div class="pdf-row indent">
      <span>Interest Income from Loans</span>
      <span class="amount">{{ number_format($interestIncome) }}</span>
    </div>
    <div class="pdf-row indent underline">
      <span>Fees &amp; Penalties</span>
      <span class="amount">{{ number_format($feesIncome) }}</span>
    </div>
    <div class="pdf-row bold">
      <span>Gross Total Income</span>
      <span class="amount">UGX {{ number_format($grossIncome) }}</span>
    </div>

    <div class="pdf-spacer"></div>

    <div class="pdf-section-label">Operating Expenses:</div>
    <div class="pdf-row indent">
      <span>Interest Paid on Member Savings</span>
      <span class="amount">{{ number_format($interestPaid) }}</span>
    </div>
    <div class="pdf-row indent">
      <span>Operational Expenses</span>
      <span class="amount">{{ number_format($operationalExpenses) }}</span>
    </div>
    <div class="pdf-row indent underline">
      <span>Loan Loss Provisioning</span>
      <span class="amount">{{ number_format($loanLossProvision) }}</span>
    </div>
    <div class="pdf-row">
      <span>Total Operating Expenses</span>
      <span class="amount">{{ number_format($interestPaid + $operationalExpenses + $loanLossProvision) }}</span>
    </div>

    <div class="pdf-spacer"></div>
    <div class="pdf-divider"></div>

    <div class="pdf-row double-underline">
      <span>NET SURPLUS (PROFIT)</span>
      <span class="amount">UGX {{ number_format($netSurplus) }}</span>
    </div>

    <div class="pdf-spacer"></div>
    <div style="font-size:8pt; margin-top:12pt; color:#444;">
      This surplus is subject to appropriation at the Annual General Meeting (AGM) as Dividends on Shares and Interest Rebates on Savings.
    </div>
  </div>

  <!-- PAGE 2: BALANCE SHEET -->
  <div class="pdf-page">
    <div class="pdf-center">
      <div class="pdf-title">OPEN SACCO</div>
      <div class="pdf-subtitle">Balance Sheet</div>
      <div class="pdf-meta">As at {{ date('F j, Y') }}</div>
    </div>

    <div class="pdf-two-col">

      <!-- LEFT: ASSETS -->
      <div>
        <div class="pdf-col-title">Assets</div>

        <div class="pdf-section-label">Current Assets:</div>
        <div class="pdf-row indent">
          <span>Cash &amp; Bank Balances</span>
          <span class="amount">{{ number_format($cashAndBank) }}</span>
        </div>
        <div class="pdf-row indent underline">
          <span>Accrued Interest Receivable</span>
          <span class="amount">{{ number_format($accruedInterest) }}</span>
        </div>
        <div class="pdf-row">
          <span>Total Current Assets</span>
          <span class="amount">{{ number_format($cashAndBank + $accruedInterest) }}</span>
        </div>

        <div class="pdf-spacer"></div>

        <div class="pdf-section-label">Non-Current Assets:</div>
        <div class="pdf-row indent">
          <span>Gross Loan Portfolio</span>
          <span class="amount">{{ number_format($grossLoanPortfolio) }}</span>
        </div>
        <div class="pdf-row indent underline">
          <span>Less: Loan Loss Provision</span>
          <span class="amount">({{ number_format($loanLossProvision) }})</span>
        </div>
        <div class="pdf-row">
          <span>Net Loan Portfolio</span>
          <span class="amount">{{ number_format($grossLoanPortfolio - $loanLossProvision) }}</span>
        </div>

        <div class="pdf-spacer"></div>
        <div class="pdf-divider"></div>

        <div class="pdf-total-bar">
          <span>TOTAL ASSETS</span>
          <span class="amount">UGX {{ number_format($totalAssets - $loanLossProvision) }}</span>
        </div>
      </div>

      <!-- RIGHT: LIABILITIES & EQUITY -->
      <div>
        <div class="pdf-col-title">Liabilities &amp; Equity</div>

        <div class="pdf-section-label">Liabilities:</div>
        <div class="pdf-row indent">
          <span>Total Member Savings</span>
          <span class="amount">{{ number_format($totalMemberSavings) }}</span>
        </div>
        <div class="pdf-row indent underline">
          <span>Accounts Payable</span>
          <span class="amount">0</span>
        </div>
        <div class="pdf-row">
          <span>Total Liabilities</span>
          <span class="amount">{{ number_format($totalMemberSavings) }}</span>
        </div>

        <div class="pdf-spacer"></div>

        <div class="pdf-section-label">Owners' Equity:</div>
        <div class="pdf-row indent">
          <span>Total Share Capital</span>
          <span class="amount">{{ number_format($totalShareCapital) }}</span>
        </div>
        <div class="pdf-row indent underline">
          <span>Retained Earnings</span>
          <span class="amount">{{ number_format($retainedEarnings) }}</span>
        </div>
        <div class="pdf-row">
          <span>Total Equity</span>
          <span class="amount">{{ number_format($totalShareCapital + $retainedEarnings) }}</span>
        </div>

        <div class="pdf-spacer"></div>
        <div class="pdf-divider"></div>

        <div class="pdf-total-bar">
          <span>TOTAL LIAB. &amp; EQUITY</span>
          <span class="amount">UGX {{ number_format($totalMemberSavings + $totalShareCapital + $retainedEarnings) }}</span>
        </div>
      </div>
    </div>

    <div class="pdf-spacer"></div>
    <div style="font-size: 8pt; color:#444; margin-top: 24pt; border-top: 0.5pt solid #ccc; padding-top: 6pt; display: flex; justify-content: space-between;">
      <span>Prepared by: ________________________</span>
      <span>Approved by: ________________________</span>
      <span>Date: {{ date('d/m/Y') }}</span>
    </div>
  </div>

</div>
@endsection
