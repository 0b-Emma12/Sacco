@extends('layouts.app')
@section('title', 'Standing Order')

@section('content')
@php
    $badge = match ($standingOrder->status) {
        'ACTIVE' => 'bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20',
        'PENDING' => 'bg-[#FFDE73]/30 text-amber-700 border border-amber-300/30',
        'SUSPENDED' => 'bg-amber-100 text-amber-800 border border-amber-300/40',
        'CANCELLED' => 'bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20',
        default => 'bg-slate-100 text-slate-600 border border-slate-200 dark:border-slate-700',
    };
@endphp

<div class="mx-auto w-full max-w-6xl flex flex-col gap-6">
    <x-page-header
        eyebrow="Member Services / S05"
        title="{{ $standingOrder->mandate_reference }}"
        subtitle="{{ trim(($standingOrder->member->first_name ?? '').' '.($standingOrder->member->last_name ?? '')) }} · {{ $standingOrder->destinationLabel() }}"
    >
        <span class="px-3 py-1.5 text-sm font-semibold rounded-xl border {{ $badge }}">{{ ucfirst(strtolower($standingOrder->status)) }}</span>
        <a href="{{ route('member-services.standing-orders.index') }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2 no-print">
            <x-icon name="arrow-left" class="w-4 h-4"/> All mandates
        </a>
    </x-page-header>

    @if (session('success'))
        <div class="p-4 text-sm text-[#38CE3C] font-semibold bg-[#38CE3C]/10 border border-[#38CE3C]/20 rounded-xl no-print">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="p-4 text-sm text-[#FF4D6B] bg-[#FF4D6B]/10 border border-[#FF4D6B]/20 rounded-xl no-print">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Amount per cycle" :value="'UGX '.number_format($standingOrder->amount, 2)" :meta="$standingOrder->frequencyLabel()" icon="coins" tone="green"/>
        <x-stat-card label="Next run" :value="$standingOrder->next_run_date->format('d M Y')" :meta="'Start '.$standingOrder->start_date->format('d M Y')" icon="clock" tone="blue"/>
        <x-stat-card label="Cycles posted" :value="$standingOrder->runs_completed" :meta="$standingOrder->last_run_date ? 'Last '.$standingOrder->last_run_date->format('d M Y') : 'Not yet collected'" icon="refresh-cw" tone="purple"/>
        <x-stat-card label="Destination" :value="$standingOrder->destinationLabel()" :meta="$standingOrder->destination_type === 'loan' ? 'Loan repayment' : 'Savings deposit'" icon="landmark" tone="slate"/>
    </div>

    <x-panel title="Mandate parties" subtitle="Member, collection route and linked account or loan." icon="users" tone="purple">
        <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Member</div>
                <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $standingOrder->member->first_name ?? '—' }} {{ $standingOrder->member->last_name ?? '' }}</div>
                <div class="text-xs text-slate-500">{{ $standingOrder->member->membership_number ?? '' }}</div>
                @if ($standingOrder->account)
                    <div class="text-xs text-slate-500 mt-2">Credits {{ $standingOrder->account->account_number }} (balance UGX {{ number_format($standingOrder->account->balance, 2) }})</div>
                @endif
                @if ($standingOrder->loan)
                    <div class="text-xs text-slate-500 mt-2">Repays Loan #{{ $standingOrder->loan->id }} · outstanding UGX {{ number_format($standingOrder->loan->outstanding_balance, 2) }}</div>
                @endif
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Collection</div>
                <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $standingOrder->sourceLabel() }}</div>
                <div class="text-xs text-slate-500 mt-2">Payer: {{ $standingOrder->payer_details ?: '—' }}</div>
                <div class="text-xs text-slate-500">Reference: {{ $standingOrder->payer_reference ?: '—' }}</div>
                <div class="text-xs text-slate-500">End date: {{ $standingOrder->end_date ? $standingOrder->end_date->format('d M Y') : 'Open-ended' }}</div>
            </div>
        </div>
    </x-panel>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-panel title="Mandate particulars" subtitle="Request trail, schedule and member instructions." icon="file-text" tone="blue">
                <div class="p-5 sm:p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5 text-sm">
                        <div><dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Requested by</dt><dd class="text-slate-700 dark:text-slate-300">{{ $standingOrder->requested_by }}</dd></div>
                        <div><dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Authorised by</dt><dd class="text-slate-700 dark:text-slate-300">{{ $standingOrder->approved_by ?: '—' }}{{ $standingOrder->approved_at ? ' · '.$standingOrder->approved_at->format('d M Y H:i') : '' }}</dd></div>
                    </dl>

                    @if ($standingOrder->member_instructions)
                        <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Member instructions</div>
                            <p class="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $standingOrder->member_instructions }}</p>
                        </div>
                    @endif
                    @if ($standingOrder->decision_notes)
                        <div class="mt-4">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Decision notes</div>
                            <p class="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $standingOrder->decision_notes }}</p>
                        </div>
                    @endif
                </div>
            </x-panel>
        </div>

        <div>
            <x-panel title="Mandate status" subtitle="Schedule and authorisation trail." icon="shield" tone="slate">
                <div class="p-5">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Frequency</dt><dd class="text-right font-medium text-slate-800 dark:text-slate-100">{{ $standingOrder->frequencyLabel() }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Start date</dt><dd class="text-right font-medium text-slate-800 dark:text-slate-100">{{ $standingOrder->start_date->format('d M Y') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">End date</dt><dd class="text-right font-medium text-slate-800 dark:text-slate-100">{{ $standingOrder->end_date ? $standingOrder->end_date->format('d M Y') : 'Open-ended' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Next run</dt><dd class="text-right font-medium text-slate-800 dark:text-slate-100">{{ $standingOrder->next_run_date->format('d M Y') }}</dd></div>
                    </dl>
                    <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <a href="{{ route('prints.standing-orders.mandate', $standingOrder) }}" class="w-full h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center justify-center gap-2 no-print">
                            <x-icon name="printer" class="w-4 h-4"/> Print mandate
                        </a>
                    </div>
                </div>
            </x-panel>
        </div>
    </div>

    @if (Auth::user()->canApproveLoans() || Auth::user()->canDisburseLoans())
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 no-print">
        <x-panel title="Mandate controls" subtitle="Authorise the mandate or post the next due cycle." icon="shield" tone="green">
            <div class="p-5 space-y-5">
                @if ($standingOrder->isPending() && Auth::user()->canApproveLoans())
                    <form action="{{ route('member-services.standing-orders.approve', $standingOrder) }}" method="POST" class="space-y-3">
                        @csrf
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Authorise mandate</label>
                        <textarea name="decision_notes" rows="2" maxlength="2000" placeholder="Notes (optional)" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100"></textarea>
                        <button type="submit" class="w-full h-10 px-4 text-sm font-semibold text-white bg-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition">Authorise</button>
                    </form>
                @endif

                @if ($standingOrder->isActive() && Auth::user()->canDisburseLoans())
                    <form action="{{ route('member-services.standing-orders.post-due', $standingOrder) }}" method="POST" class="space-y-3">
                        @csrf
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Collect due cycle</label>
                        <p class="text-xs text-slate-500">Posts the next due cycle now. The daily scheduler does this automatically.</p>
                        <button type="submit" class="w-full h-10 px-4 text-sm font-semibold text-white bg-slate-700 rounded-xl hover:bg-slate-800 transition">Post Due Cycle</button>
                    </form>
                @endif

                @if (! ($standingOrder->isPending() && Auth::user()->canApproveLoans()) && ! ($standingOrder->isActive() && Auth::user()->canDisburseLoans()))
                    <p class="text-sm text-slate-500">No collection actions are available for this mandate in its current state.</p>
                @endif
            </div>
        </x-panel>

        <x-panel title="Suspend or cancel" subtitle="Lifecycle actions require a recorded reason." icon="alert-triangle" tone="amber">
            <div class="p-5 space-y-5">
                @if (Auth::user()->canApproveLoans() && ($standingOrder->isActive() || $standingOrder->isSuspended()))
                    @if ($standingOrder->isActive())
                        <form action="{{ route('member-services.standing-orders.suspend', $standingOrder) }}" method="POST" class="space-y-3">
                            @csrf
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Suspend mandate</label>
                            <textarea name="decision_notes" rows="2" maxlength="2000" placeholder="Reason (required)" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100" required></textarea>
                            <button type="submit" class="w-full h-10 px-4 text-sm font-semibold text-white bg-amber-600 rounded-xl hover:bg-amber-700 transition">Suspend</button>
                        </form>
                    @else
                        <form action="{{ route('member-services.standing-orders.resume', $standingOrder) }}" method="POST" class="space-y-3">
                            @csrf
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Resume mandate</label>
                            <p class="text-xs text-slate-500">Resuming restarts collection from the next due date, not from the suspension date.</p>
                            <button type="submit" class="w-full h-10 px-4 text-sm font-semibold text-white bg-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition">Resume</button>
                        </form>
                    @endif

                    <form action="{{ route('member-services.standing-orders.cancel', $standingOrder) }}" method="POST" class="space-y-3 pt-5 border-t border-slate-100 dark:border-slate-800">
                        @csrf
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Cancel mandate</label>
                        <textarea name="decision_notes" rows="2" maxlength="2000" placeholder="Reason (required)" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100" required></textarea>
                        <button type="submit" class="w-full h-10 px-4 text-sm font-semibold text-white bg-[#FF4D6B] rounded-xl hover:bg-[#e63e5c] transition">Cancel Mandate</button>
                    </form>
                @else
                    <p class="text-sm text-slate-500">Lifecycle actions are unavailable for this mandate in its current state or for your role.</p>
                @endif
            </div>
        </x-panel>
    </div>
    @endif

    <x-panel title="Collection history" subtitle="Processed cycles, postings and journal links." icon="clock" tone="blue" class="no-print">
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-950/50 text-slate-500">
                        <th class="h-11 px-5 text-[11px] font-semibold uppercase tracking-wider">Due date</th>
                        <th class="h-11 px-5 text-[11px] font-semibold uppercase tracking-wider text-right">Amount (UGX)</th>
                        <th class="h-11 px-5 text-[11px] font-semibold uppercase tracking-wider">Status</th>
                        <th class="h-11 px-5 text-[11px] font-semibold uppercase tracking-wider">Transaction</th>
                        <th class="h-11 px-5 text-[11px] font-semibold uppercase tracking-wider">Journal</th>
                        <th class="h-11 px-5 text-[11px] font-semibold uppercase tracking-wider">Note</th>
                        <th class="h-11 px-5 text-[11px] font-semibold uppercase tracking-wider">Processed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($runs as $run)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-4 font-medium text-slate-700 dark:text-slate-200">{{ $run->due_date->format('d M Y') }}</td>
                        <td class="px-5 py-4 text-right font-semibold text-slate-800 dark:text-slate-100">{{ number_format($run->amount, 2) }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $run->isPosted() ? 'bg-[#38CE3C]/15 text-[#38CE3C]' : 'bg-[#FF4D6B]/15 text-[#FF4D6B]' }}">{{ ucfirst(strtolower($run->status)) }}</span>
                        </td>
                        <td class="px-5 py-4 font-mono text-xs text-slate-500">{{ $run->transaction_reference ?: '—' }}</td>
                        <td class="px-5 py-4 font-mono text-xs text-slate-500">{{ $run->journal_reference ?: '—' }}</td>
                        <td class="px-5 py-4 text-xs text-slate-500 whitespace-normal max-w-xs">{{ $run->note ?: '—' }}</td>
                        <td class="px-5 py-4 text-xs text-slate-500">{{ $run->processed_at ? $run->processed_at->format('d M Y H:i') : '—' }}</td>
                    </tr>
                    @empty
                    <x-empty-state colspan="7" icon="inbox" title="No cycles processed yet" message="Cycles appear here once the scheduler or an officer posts them."/>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($runs->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">{{ $runs->links() }}</div>
        @endif
    </x-panel>
    <x-panel title="Standing order mandate (S05)" :subtitle="'Mandate '.$standingOrder->mandate_reference.' is issued on the secure print register with signature and verification blocks.'" icon="file-text" tone="slate">
        <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <p class="text-sm text-slate-500">Print the mandate for signature when the member signs up for this collection instruction.</p>
            <a href="{{ route('prints.standing-orders.mandate', $standingOrder) }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold shadow-sm inline-flex items-center gap-2">
                <x-icon name="printer" class="w-4 h-4"/> Open mandate
            </a>
        </div>
    </x-panel>
</div>
@endsection
