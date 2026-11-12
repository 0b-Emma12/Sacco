@extends('layouts.app')
@section('title', 'Death Claims')

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
        title="Death Claims"
        subtitle="ST01 — claim registration, board approval and settlement of a deceased member's account.">
        <a href="{{ route('members.index') }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2">
            <x-icon name="users" class="w-4 h-4"/>
            Open member register
        </a>
    </x-page-header>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Claims registered" :value="number_format($totals['total'])" meta="All claims on file" icon="file-text" tone="slate"/>
        <x-stat-card label="Pending claims" :value="number_format($totals['pending'])" meta="Awaiting board decision" icon="clock" tone="amber"/>
        <x-stat-card label="Approved" :value="number_format($totals['approved'])" meta="Awaiting settlement" icon="check-circle" tone="purple"/>
        <x-stat-card label="Paid to claimants" :value="'UGX '.number_format($totals['paid'], 2)" meta="Settled member accounts" icon="coins" tone="green"/>
    </div>

    <x-panel title="Claims register" subtitle="Death claims lodged against member accounts, newest first." icon="shield" tone="purple">
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Claim</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Member</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Date of death</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Claimant</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Net payable</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($claims as $claim)
                    @php
                        $claimBadge = $claim->isPaid()
                            ? 'bg-[#38CE3C]/15 text-[#38CE3C] border-[#38CE3C]/20'
                            : ($claim->isApproved()
                                ? 'bg-[#8E32E9]/15 text-[#8E32E9] border-[#8E32E9]/20'
                                : ($claim->status === 'REJECTED'
                                    ? 'bg-[#FF4D6B]/15 text-[#FF4D6B] border-[#FF4D6B]/20'
                                    : 'bg-[#FFDE73]/30 text-amber-700 border-[#FFDE73]/40 dark:text-amber-300'));
                    @endphp
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">DC-{{ str_pad($claim->id, 4, '0', STR_PAD_LEFT) }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm text-slate-700 dark:text-slate-300">{{ $claim->member ? $claim->member->first_name.' '.$claim->member->last_name : '—' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $claim->member->membership_number ?? '' }}</div>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $claim->date_of_death ? $claim->date_of_death->format('d M Y') : '—' }}</td>
                        <td class="px-5 py-4">
                            <div class="text-sm text-slate-700 dark:text-slate-300">{{ $claim->claimant_name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $claim->claimant_relationship }}</div>
                        </td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-slate-800 dark:text-slate-100">{{ number_format($claim->net_payable, 2) }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $claimBadge }}">{{ ucfirst(strtolower($claim->status)) }}</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('member-services.death-claims.show', $claim) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] text-xs font-semibold hover:bg-[#38CE3C]/20 transition">Open</a>
                        </td>
                    </tr>
                    @empty
                        <x-empty-state colspan="7" icon="inbox" title="No death claims registered"
                                       message="Open a member record to lodge a claim against the account.">
                            <a href="{{ route('members.index') }}" class="h-9 px-4 rounded-lg bg-[#38CE3C] text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-[#2cb030] transition">
                                <x-icon name="users" class="w-3.5 h-3.5"/> Open member register
                            </a>
                        </x-empty-state>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($claims->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $claims->links() }}
            </div>
        @endif
    </x-panel>
</div>
@endsection
