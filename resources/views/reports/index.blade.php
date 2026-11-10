@extends('layouts.app')
@section('title', 'Financial Reports')
@section('content')
<div class="mx-auto w-full max-w-6xl flex flex-col gap-6">
  
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
      <div>
          <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Analytics</div>
          <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">Financial Reports</h1>
      </div>
      <div>
          <button onclick="window.print()" class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded hover:bg-slate-50 transition print-hidden dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
              <i data-lucide="printer" class="w-4 h-4"></i> Print Report
          </button>
      </div>
  </div>

  <!-- Filter Form -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 dark:bg-slate-900 dark:border-slate-800 print-hidden">
      <form method="GET" action="{{ route('reports.index') }}" class="flex flex-col sm:flex-row gap-4 items-end">
          <div class="flex-1 w-full">
              <label class="block text-xs font-bold text-slate-500 mb-1">Start Date</label>
              <input type="date" name="start_date" value="{{ $startDate }}" class="w-full h-10 px-3 rounded-lg border border-slate-200 focus:ring-2 focus:ring-sacco outline-none text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">
          </div>
          <div class="flex-1 w-full">
              <label class="block text-xs font-bold text-slate-500 mb-1">End Date</label>
              <input type="date" name="end_date" value="{{ $endDate }}" class="w-full h-10 px-3 rounded-lg border border-slate-200 focus:ring-2 focus:ring-sacco outline-none text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">
          </div>
          <button type="submit" class="h-10 px-6 rounded-lg bg-sacco hover:bg-sacco-dark text-white font-medium text-sm transition flex items-center gap-2 w-full sm:w-auto justify-center">
              <i data-lucide="filter" class="w-4 h-4"></i> Filter
          </button>
      </form>
  </div>

  <!-- Report Period Heading (visible on print) -->
  <div class="hidden print:block mb-4 text-center">
      <h2 class="text-xl font-bold">Financial Report</h2>
      <p class="text-sm">Period: {{ $startDate }} to {{ $endDate }}</p>
  </div>

  <!-- KPIs -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 dark:bg-slate-900 dark:border-slate-800">
          <div class="text-xs font-bold tracking-wider text-slate-400 uppercase">Total Deposits</div>
          <h3 class="text-2xl font-bold text-emerald-600 mt-2">UGX {{ number_format($totalDeposits) }}</h3>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 dark:bg-slate-900 dark:border-slate-800">
          <div class="text-xs font-bold tracking-wider text-slate-400 uppercase">Total Withdrawals</div>
          <h3 class="text-2xl font-bold text-rose-600 mt-2">UGX {{ number_format($totalWithdrawals) }}</h3>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 dark:bg-slate-900 dark:border-slate-800">
          <div class="text-xs font-bold tracking-wider text-slate-400 uppercase">Net Cash Flow</div>
          <h3 class="text-2xl font-bold {{ $netCashFlow >= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-2">
              UGX {{ number_format($netCashFlow) }}
          </h3>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 dark:bg-slate-900 dark:border-slate-800">
          <div class="text-xs font-bold tracking-wider text-slate-400 uppercase">Loans Disbursed</div>
          <h3 class="text-2xl font-bold text-indigo-600 mt-2">UGX {{ number_format($totalLoansDisbursed) }}</h3>
      </div>
  </div>

  <!-- Transactions Table -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden dark:bg-slate-900 dark:border-slate-800">
      <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
          <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Transaction Ledger</h3>
          <button class="text-sacco text-sm font-medium hover:underline print-hidden" onclick="exportTableToCSV('transactions_report.csv')">Export CSV</button>
      </div>
      <div class="overflow-x-auto w-full">
          <table class="w-full text-left text-sm whitespace-nowrap">
              <thead>
                  <tr class="bg-slate-50 dark:bg-slate-950/50 text-slate-500 text-xs uppercase tracking-wider">
                      <th class="px-6 py-4 font-semibold">Date</th>
                      <th class="px-6 py-4 font-semibold">Ref</th>
                      <th class="px-6 py-4 font-semibold">Account</th>
                      <th class="px-6 py-4 font-semibold">Type</th>
                      <th class="px-6 py-4 font-semibold text-right">Amount</th>
                  </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                  @forelse($transactions as $tx)
                  <tr class="hover:bg-slate-50/50 transition">
                      <td class="px-6 py-4 text-slate-600">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                      <td class="px-6 py-4 font-medium">{{ $tx->reference }}</td>
                      <td class="px-6 py-4">{{ $tx->account_number }}</td>
                      <td class="px-6 py-4">
                          @if($tx->transaction_type === 'deposit')
                              <span class="px-2 py-1 text-xs font-medium rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Deposit</span>
                          @else
                              <span class="px-2 py-1 text-xs font-medium rounded-full bg-rose-50 text-rose-700 border border-rose-200">Withdrawal</span>
                          @endif
                      </td>
                      <td class="px-6 py-4 font-semibold text-right {{ $tx->transaction_type === 'deposit' ? 'text-emerald-600' : 'text-rose-600' }}">
                          {{ $tx->transaction_type === 'deposit' ? '+' : '-' }} UGX {{ number_format($tx->amount) }}
                      </td>
                  </tr>
                  @empty
                  <tr>
                      <td colspan="5" class="px-6 py-8 text-center text-slate-500">No transactions recorded in this period.</td>
                  </tr>
                  @endforelse
              </tbody>
          </table>
      </div>
      @if($transactions->hasPages())
      <div class="p-4 border-t border-slate-100 dark:border-slate-800 print-hidden">
          {{ $transactions->links() }}
      </div>
      @endif
  </div>
</div>
@endsection
