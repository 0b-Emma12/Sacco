@extends('layouts.app')
@section('title', 'Share Transfers')

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
        title="Share Transfers"
        subtitle="ST02 — transfer of non-withdrawable share capital between members.">
        <a href="{{ route('member-services.share-transfers.create') }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold shadow-sm inline-flex items-center gap-2 transition">
            <x-icon name="plus-circle" class="w-4 h-4"/>
            New Transfer
        </a>
    </x-page-header>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Transfers" :value="number_format($totals['total'])" meta="Mandates on record" icon="arrow-left-right" tone="slate"/>
        <x-stat-card label="Pending transfers" :value="number_format($totals['pending'])" meta="Awaiting board approval" icon="clock" tone="amber"/>
        <x-stat-card label="Completed" :value="number_format($totals['approved'])" meta="Shares already moved" icon="check-circle" tone="green"/>
        <x-stat-card label="Value transferred" :value="'UGX '.number_format($totals['value'], 2)" meta="Approved share value" icon="coins" tone="purple"/>
    </div>

    <x-panel title="Transfers register" subtitle="Share capital movements between members, newest first." icon="arrow-left-right" tone="purple">
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Transfer</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">From</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">To</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Shares</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Value (UGX)</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($transfers as $transfer)
                    @php
                        $transferBadge = $transfer->isApproved()
                            ? 'bg-[#38CE3C]/15 text-[#38CE3C] border-[#38CE3C]/20'
                            : ($transfer->status === 'REJECTED'
                                ? 'bg-[#FF4D6B]/15 text-[#FF4D6B] border-[#FF4D6B]/20'
                                : 'bg-[#FFDE73]/30 text-amber-700 border-[#FFDE73]/40 dark:text-amber-300');
                    @endphp
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">ST-{{ str_pad($transfer->id, 4, '0', STR_PAD_LEFT) }}</div>
                            <div class="text-[11px] text-slate-400">#{{ $transfer->id }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm text-slate-700 dark:text-slate-300">{{ $transfer->transferor->first_name ?? '—' }} {{ $transfer->transferor->last_name ?? '' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $transfer->transferor->membership_number ?? '' }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm text-slate-700 dark:text-slate-300">{{ $transfer->transferee->first_name ?? '—' }} {{ $transfer->transferee->last_name ?? '' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $transfer->transferee->membership_number ?? '' }}</div>
                        </td>
                        <td class="px-5 py-4 text-right text-sm text-slate-700 dark:text-slate-300">{{ number_format($transfer->shares_count, 4) }}</td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-slate-800 dark:text-slate-100">{{ number_format($transfer->share_value, 2) }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $transferBadge }}">{{ ucfirst(strtolower($transfer->status)) }}</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('member-services.share-transfers.show', $transfer) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] text-xs font-semibold hover:bg-[#38CE3C]/20 transition">Open</a>
                        </td>
                    </tr>
                    @empty
                        <x-empty-state colspan="7" icon="inbox" title="No share transfers recorded yet"
                                       message="Lodge a transfer to move non-withdrawable share capital between members.">
                            <a href="{{ route('member-services.share-transfers.create') }}" class="h-9 px-4 rounded-lg bg-[#38CE3C] text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-[#2cb030] transition">
                                <x-icon name="plus" class="w-3.5 h-3.5"/> New Transfer
                            </a>
                        </x-empty-state>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $transfers->links() }}
            </div>
        @endif
    </x-panel>
</div>
@endsection
