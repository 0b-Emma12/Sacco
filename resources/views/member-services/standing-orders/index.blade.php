@extends('layouts.app')
@section('title', 'Standing Orders')

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
        eyebrow="Member Services"
        title="Standing Orders"
        subtitle="S05 — recurring member mandates into savings or loan repayments.">
        <a href="{{ route('member-services.standing-orders.create') }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold shadow-sm inline-flex items-center gap-2 transition">
            <x-icon name="plus-circle" class="w-4 h-4"/>
            New Mandate
        </a>
    </x-page-header>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Active mandates" :value="number_format($totals['active'])" meta="Running on schedule" icon="check-circle" tone="green"/>
        <x-stat-card label="Awaiting authorisation" :value="number_format($totals['pending'])" meta="Pending board approval" icon="clock" tone="amber"/>
        <x-stat-card label="Due today" :value="number_format($totals['due'])" meta="At or past the next run" icon="refresh-cw" tone="blue"/>
        <x-stat-card label="Collected this month" :value="'UGX '.number_format($totals['postedValue'], 2)" :meta="$totals['postedCount'].' posted cycle(s)'" icon="coins" tone="purple"/>
    </div>

    <x-panel title="Mandates register" subtitle="Recurring collection mandates, newest first." icon="refresh-cw" tone="purple">
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Mandate</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Member</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Destination</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Amount (UGX)</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Frequency</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Next run</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($orders as $order)
                    @php
                        $badge = match ($order->status) {
                            'ACTIVE' => 'bg-[#38CE3C]/15 text-[#38CE3C] border-[#38CE3C]/20',
                            'PENDING' => 'bg-[#FFDE73]/30 text-amber-700 border-[#FFDE73]/40 dark:text-amber-300',
                            'SUSPENDED' => 'bg-amber-100 text-amber-800 border-amber-300/40',
                            'CANCELLED' => 'bg-[#FF4D6B]/15 text-[#FF4D6B] border-[#FF4D6B]/20',
                            default => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $order->mandate_reference }}</div>
                            <div class="text-[11px] text-slate-400">#{{ $order->id }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm text-slate-700 dark:text-slate-300">{{ $order->member->first_name ?? '—' }} {{ $order->member->last_name ?? '' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $order->member->membership_number ?? '' }}</div>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $order->destinationLabel() }}</td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-slate-800 dark:text-slate-100">{{ number_format($order->amount, 2) }}</td>
                        <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $order->frequencyLabel() }}</td>
                        <td class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $order->next_run_date->format('d M Y') }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $badge }}">{{ ucfirst(strtolower($order->status)) }}</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('member-services.standing-orders.show', $order) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] text-xs font-semibold hover:bg-[#38CE3C]/20 transition">Open</a>
                        </td>
                    </tr>
                    @empty
                        <x-empty-state colspan="8" icon="inbox" title="No standing order mandates recorded yet"
                                       message="Register a mandate to collect recurring savings or loan repayments automatically.">
                            <a href="{{ route('member-services.standing-orders.create') }}" class="h-9 px-4 rounded-lg bg-[#38CE3C] text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-[#2cb030] transition">
                                <x-icon name="plus" class="w-3.5 h-3.5"/> New Mandate
                            </a>
                        </x-empty-state>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $orders->links() }}
            </div>
        @endif
    </x-panel>

    <div class="text-xs text-slate-500 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl p-4">
        Due cycles are posted automatically by the daily <span class="font-mono">sacco:process-standing-orders</span> run.
        Savings mandates credit account 2000 and debit Cash in Bank (1000); loan mandates apply the repayment through the schedule service so interest and pledges stay consistent.
    </div>
</div>
@endsection
