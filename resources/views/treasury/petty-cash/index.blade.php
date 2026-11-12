@extends('layouts.app')
@section('title', 'Petty Cash')

@section('content')
<div class="mx-auto w-full max-w-7xl flex flex-col gap-6">
    @if (session('success'))
        <div class="p-4 text-sm text-[#38CE3C] font-semibold rounded-xl bg-[#38CE3C]/10 border border-[#38CE3C]/20" role="alert">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="p-4 text-sm text-[#FF4D6B] rounded-xl bg-[#FF4D6B]/10 border border-[#FF4D6B]/20" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <x-page-header
        eyebrow="Treasury"
        title="Petty Cash"
        subtitle="F05 — imprest float, vouchers and replenishments.">
        <a href="{{ route('treasury.petty-cash.create') }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold shadow-sm inline-flex items-center gap-2 transition">
            <x-icon name="plus-circle" class="w-4 h-4"/>
            New Voucher
        </a>
    </x-page-header>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Float balance (1400)" :value="'UGX '.number_format($floatBalance, 2)" meta="Imprest float available" icon="wallet" tone="green"/>
        <x-stat-card label="Pending vouchers" :value="'UGX '.number_format($pendingTotal, 2)" :meta="$pendingCount.' awaiting approval'" icon="clock" tone="amber"/>
        <x-stat-card label="Approved spend" :value="'UGX '.number_format($approvedTotal, 2)" meta="Posted to the ledger" icon="check-circle" tone="purple"/>
        <x-stat-card label="Replenishments" :value="number_format($replenishmentCount)" meta="Bank top-ups on record" icon="refresh-cw" tone="blue"/>
    </div>

    @if (Auth::user()->canApproveLoans())
    <details class="dropdown bg-white rounded-2xl shadow-xs border border-slate-200/80 dark:bg-[#181824] dark:border-[#272738]">
        <summary class="p-5 flex items-center justify-between gap-3 cursor-pointer">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center shrink-0">
                    <x-icon name="refresh-cw" class="w-4 h-4"/>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Replenish float from bank account</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Move funds from the bank account into imprest 1400.</p>
                </div>
            </div>
            <x-icon name="chevron-down" class="w-4 h-4 text-slate-400"/>
        </summary>
        <form action="{{ route('treasury.petty-cash.replenish') }}" method="POST" class="px-5 pb-5 grid grid-cols-1 md:grid-cols-4 gap-4 border-t border-slate-100 dark:border-slate-800 pt-5">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Date</label>
                <input type="date" name="date" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" class="w-full h-10 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] dark:bg-slate-900 dark:text-slate-100">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Amount (UGX)</label>
                <input type="number" step="0.01" min="0.01" name="amount" class="w-full h-10 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] dark:bg-slate-900 dark:text-slate-100" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Cheque / reference</label>
                <input type="text" name="reference" maxlength="100" class="w-full h-10 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] dark:bg-slate-900 dark:text-slate-100">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full h-10 px-4 text-sm font-semibold text-white bg-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition">Replenish</button>
            </div>
        </form>
    </details>
    @endif

    <x-panel title="Vouchers" subtitle="Claims against the imprest float, newest first." icon="receipt" tone="amber">
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Voucher</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Paid to</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Purpose</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Amount</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Receipt</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($vouchers as $voucher)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors align-top">
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $voucher->voucher_number }}</div>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $voucher->date->format('d M Y') }}</td>
                        <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $voucher->paid_to }}</td>
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                            <p class="max-w-56 whitespace-normal">{{ $voucher->purpose }}</p>
                        </td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-slate-800 dark:text-slate-100">{{ number_format($voucher->amount, 2) }}</td>
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $voucher->receipt_attached ? 'Yes' : 'No' }}</td>
                        <td class="px-5 py-4">
                            @php
                                $voucherBadge = $voucher->isApproved()
                                    ? 'bg-[#38CE3C]/15 text-[#38CE3C] border-[#38CE3C]/20'
                                    : ($voucher->status === 'REJECTED'
                                        ? 'bg-[#FF4D6B]/15 text-[#FF4D6B] border-[#FF4D6B]/20'
                                        : 'bg-[#FFDE73]/30 text-amber-700 border-[#FFDE73]/40 dark:text-amber-300');
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $voucherBadge }}">{{ ucfirst(strtolower($voucher->status)) }}</span>
                            @if ($voucher->isApproved())
                                <div class="text-[11px] text-slate-400 mt-1">{{ $voucher->approved_by }}</div>
                            @endif
                            @if ($voucher->decision_notes)
                                <div class="text-[11px] text-slate-500 whitespace-normal max-w-xs mt-1">{{ $voucher->decision_notes }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if ($voucher->isPending() && Auth::user()->canApproveLoans())
                            <div class="flex flex-col items-end gap-2">
                                <form action="{{ route('treasury.petty-cash.approve', $voucher) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#38CE3C] text-white text-xs font-semibold hover:bg-[#2cb030] transition">Approve</button>
                                </form>
                                <details class="dropdown">
                                    <summary class="text-xs font-semibold text-[#FF4D6B] cursor-pointer">Reject</summary>
                                    <div class="mt-2 w-48">
                                        <form action="{{ route('treasury.petty-cash.reject', $voucher) }}" method="POST" class="flex flex-col gap-2">
                                            @csrf
                                            <input type="text" name="decision_notes" placeholder="Reason" class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1.5 text-xs dark:bg-slate-900 dark:text-slate-100" required>
                                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-[#FF4D6B] rounded-lg">Confirm</button>
                                        </form>
                                    </div>
                                </details>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                        <x-empty-state colspan="8" icon="inbox" title="No petty cash vouchers recorded yet"
                                       message="Raise the first voucher to draw from the imprest float.">
                            <a href="{{ route('treasury.petty-cash.create') }}" class="h-9 px-4 rounded-lg bg-[#38CE3C] text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-[#2cb030] transition">
                                <x-icon name="plus" class="w-3.5 h-3.5"/> New Voucher
                            </a>
                        </x-empty-state>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vouchers->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $vouchers->links() }}
            </div>
        @endif
    </x-panel>

    <x-panel title="Recent replenishments" subtitle="Latest transfers from the bank into the float." icon="refresh-cw" tone="blue">
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Reference</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Amount</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Replenished by</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($replenishments as $replenishment)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $replenishment->date->format('d M Y') }}</td>
                        <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $replenishment->reference ?: '—' }}</td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-slate-800 dark:text-slate-100">{{ number_format($replenishment->amount, 2) }}</td>
                        <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $replenishment->replenished_by }}</td>
                    </tr>
                    @empty
                        <x-empty-state colspan="4" icon="inbox" title="No replenishments recorded" message="Top-ups from the bank account will be listed here."/>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-panel>
</div>
@endsection
