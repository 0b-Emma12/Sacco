@extends('layouts.app')
@section('title', 'Financial Reports')
@section('content')
<div class="mx-auto w-full max-w-6xl flex flex-col gap-6">

  <x-page-header
      eyebrow="Analytics"
      title="Financial Reports"
      subtitle="Deposits, withdrawals, cash flow and lending for the selected reporting period.">
      <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2 transition no-print">
          <x-icon name="printer" class="w-4 h-4"/> Print report
      </a>
  </x-page-header>

  <!-- Filter Form -->
  <x-panel title="Report parameters" subtitle="Choose the date range included in every figure below." icon="filter" class="no-print">
      <form method="GET" action="{{ route('reports.index') }}" class="p-5 flex flex-col sm:flex-row gap-4 items-end">
          <div class="flex-1 w-full">
              <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Start Date</label>
              <input type="date" name="start_date" value="{{ $startDate }}" class="w-full h-10 px-3 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] outline-none text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100 transition">
          </div>
          <div class="flex-1 w-full">
              <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">End Date</label>
              <input type="date" name="end_date" value="{{ $endDate }}" class="w-full h-10 px-3 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] outline-none text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100 transition">
          </div>
          <button type="submit" class="h-10 px-6 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white font-semibold text-sm transition inline-flex items-center gap-2 w-full sm:w-auto justify-center shadow-sm">
              <x-icon name="filter" class="w-4 h-4"/> Filter
          </button>
      </form>
  </x-panel>

  <!-- Report Period Heading (visible on print) -->
  <div class="hidden print:block mb-4 text-center">
      <h2 class="text-xl font-bold">Financial Report</h2>
      <p class="text-sm">Period: {{ $startDate }} to {{ $endDate }}</p>
  </div>

  <!-- KPIs -->
  <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
      <x-stat-card label="Total deposits" :value="'UGX '.number_format($totalDeposits)" meta="Member deposits in the period" icon="arrow-down-to-line" tone="green"/>
      <x-stat-card label="Total withdrawals" :value="'UGX '.number_format($totalWithdrawals)" meta="Payouts in the period" icon="arrow-up-from-line" tone="coral"/>
      <x-stat-card label="Net cash flow" :value="'UGX '.number_format($netCashFlow)" meta="Deposits less withdrawals" :icon="$netCashFlow >= 0 ? 'trending-up' : 'trending-down'" :tone="$netCashFlow >= 0 ? 'green' : 'coral'"/>
      <x-stat-card label="Loans disbursed" :value="'UGX '.number_format($totalLoansDisbursed)" meta="Approved and disbursed credit" icon="credit-card" tone="purple"/>
  </div>

  <!-- Transactions Table -->
  <x-panel title="Transaction ledger" subtitle="Credits and debits posted within the selected period." icon="arrow-left-right">
      <x-slot name="actions">
          <a href="{{ route('reports.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2 transition no-print">
              <x-icon name="download" class="w-4 h-4"/> Export CSV
          </a>
      </x-slot>

      <div class="overflow-x-auto w-full">
          <table class="w-full text-left whitespace-nowrap">
              <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                  <tr class="border-b border-slate-100 dark:border-slate-800">
                      <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                      <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Ref</th>
                      <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Account</th>
                      <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                      <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Amount</th>
                  </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                  @forelse($transactions as $tx)
                  <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                      <td class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $tx->created_at->format('d M Y, H:i') }}</td>
                      <td class="px-5 py-4">
                          <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $tx->reference }}</div>
                          <div class="text-[11px] text-slate-400">#{{ $tx->id }}</div>
                      </td>
                      <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $tx->account_number }}</td>
                      <td class="px-5 py-4">
                          @if($tx->transaction_type === 'deposit')
                              <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border bg-[#38CE3C]/15 text-[#38CE3C] border-[#38CE3C]/20">Deposit</span>
                          @else
                              <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border bg-[#FF4D6B]/15 text-[#FF4D6B] border-[#FF4D6B]/20">Withdrawal</span>
                          @endif
                      </td>
                      <td class="px-5 py-4 text-right text-sm font-bold {{ $tx->transaction_type === 'deposit' ? 'text-[#38CE3C]' : 'text-[#FF4D6B]' }}">
                          {{ $tx->transaction_type === 'deposit' ? '+' : '-' }} UGX {{ number_format($tx->amount) }}
                      </td>
                  </tr>
                  @empty
                  <x-empty-state colspan="5" icon="inbox" title="No transactions in this period"
                                 message="Widen the date range or post a transaction to populate the report."/>
                  @endforelse
              </tbody>
          </table>
      </div>
      @if($transactions->hasPages())
      <div class="p-4 border-t border-slate-100 dark:border-slate-800 no-print">
          {{ $transactions->links() }}
      </div>
      @endif
  </x-panel>
</div>
@endsection
