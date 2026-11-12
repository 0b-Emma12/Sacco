@extends('layouts.app')
@section('title', 'Share Transfer')

@section('content')
@php
    $badge = $shareTransfer->isApproved()
        ? 'bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20'
        : ($shareTransfer->status === 'REJECTED'
            ? 'bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20'
            : 'bg-[#FFDE73]/30 text-amber-700 border border-amber-300/30');
@endphp

<div class="mx-auto w-full max-w-6xl flex flex-col gap-6">
    <x-page-header
        eyebrow="Member Services / ST02"
        title="Share Transfer ST-{{ str_pad($shareTransfer->id, 4, '0', STR_PAD_LEFT) }}"
        subtitle="{{ $shareTransfer->transferor->first_name }} {{ $shareTransfer->transferor->last_name }} → {{ $shareTransfer->transferee->first_name }} {{ $shareTransfer->transferee->last_name }}">
        <span class="px-3 py-1.5 text-sm font-semibold rounded-xl border {{ $badge }}">{{ ucfirst(strtolower($shareTransfer->status)) }}</span>
        <a href="{{ route('member-services.share-transfers.index') }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2 no-print">
            <x-icon name="arrow-left" class="w-4 h-4"/> All transfers
        </a>
    </x-page-header>

    @if (session('success'))
        <div class="p-4 text-sm text-[#38CE3C] font-semibold bg-[#38CE3C]/10 border border-[#38CE3C]/20 rounded-xl no-print" role="alert">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="p-4 text-sm text-[#FF4D6B] bg-[#FF4D6B]/10 border border-[#FF4D6B]/20 rounded-xl no-print" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Shares transferred" :value="number_format($shareTransfer->shares_count, 4)" meta="Non-withdrawable equity" icon="arrow-left-right" tone="purple"/>
        <x-stat-card label="Transfer value" :value="'UGX '.number_format($shareTransfer->share_value, 2)" meta="Valued at the share price" icon="coins" tone="green"/>
        <x-stat-card label="Consideration" :value="$shareTransfer->consideration !== null ? 'UGX '.number_format($shareTransfer->consideration, 2) : '—'" meta="Amount paid between members" icon="wallet" tone="blue"/>
        <x-stat-card label="Transferor capital" :value="'UGX '.number_format($shareTransfer->transferor->share_capital, 2)" meta="Current share capital held" icon="landmark" tone="slate"/>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 flex flex-col gap-6">
            <x-panel title="Transfer parties" subtitle="Who is moving the shares and who receives them." icon="users" tone="purple">
                <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Transferor</div>
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $shareTransfer->transferor->first_name }} {{ $shareTransfer->transferor->last_name }}</div>
                        <div class="text-xs text-slate-500">{{ $shareTransfer->transferor->membership_number }}</div>
                        <div class="text-xs text-slate-500 mt-2">Share capital: UGX {{ number_format($shareTransfer->transferor->share_capital, 2) }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Transferee</div>
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $shareTransfer->transferee->first_name }} {{ $shareTransfer->transferee->last_name }}</div>
                        <div class="text-xs text-slate-500">{{ $shareTransfer->transferee->membership_number }}</div>
                        <div class="text-xs text-slate-500 mt-2">Share capital: UGX {{ number_format($shareTransfer->transferee->share_capital, 2) }}</div>
                    </div>
                </div>
            </x-panel>

            <x-panel title="Transfer particulars" subtitle="Value, consideration and recorded reason." icon="file-text" tone="blue">
                <div class="p-5 sm:p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5 text-sm">
                        <div><dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Shares</dt><dd class="font-semibold text-slate-800 dark:text-slate-200">{{ number_format($shareTransfer->shares_count, 4) }}</dd></div>
                        <div><dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Value</dt><dd class="font-semibold text-slate-800 dark:text-slate-200">UGX {{ number_format($shareTransfer->share_value, 2) }}</dd></div>
                        <div><dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Consideration</dt><dd class="text-slate-700 dark:text-slate-300">{{ $shareTransfer->consideration !== null ? 'UGX '.number_format($shareTransfer->consideration, 2) : '—' }}</dd></div>
                        <div><dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Transfer date</dt><dd class="text-slate-700 dark:text-slate-300">{{ $shareTransfer->transfer_date ? $shareTransfer->transfer_date->format('d M Y') : '—' }}</dd></div>
                    </dl>

                    @if ($shareTransfer->reason)
                        <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Reason</div>
                            <p class="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $shareTransfer->reason }}</p>
                        </div>
                    @endif
                    @if ($shareTransfer->decision_notes)
                        <div class="mt-4">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Decision notes</div>
                            <p class="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $shareTransfer->decision_notes }}</p>
                        </div>
                    @endif
                </div>
            </x-panel>
        </div>

        <div class="flex flex-col gap-6">
            <x-panel title="Decision" subtitle="Approval trail and certificate." icon="shield" tone="slate">
                <div class="p-5">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Requested by</dt><dd class="text-right font-medium text-slate-800 dark:text-slate-100">{{ $shareTransfer->requested_by }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Decided by</dt><dd class="text-right font-medium text-slate-800 dark:text-slate-100">{{ $shareTransfer->approved_by ?: '—' }}{{ $shareTransfer->approved_at ? ' · '.$shareTransfer->approved_at->format('d M Y H:i') : '' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Certificate</dt><dd class="text-right font-medium text-slate-800 dark:text-slate-100">{{ $shareTransfer->certificate_reference ?: '—' }}</dd></div>
                    </dl>

                    @if ($shareTransfer->isPending() && Auth::user()->canApproveLoans())
                    <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-4 no-print">
                        <form action="{{ route('member-services.share-transfers.approve', $shareTransfer) }}" method="POST" class="space-y-3">
                            @csrf
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Approve transfer</label>
                            <input type="date" name="transfer_date" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" class="w-full h-10 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100">
                            <textarea name="decision_notes" rows="2" maxlength="2000" placeholder="Notes" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100"></textarea>
                            <button type="submit" class="w-full h-10 px-4 text-sm font-semibold text-white bg-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition">Approve &amp; Move Shares</button>
                        </form>

                        <form action="{{ route('member-services.share-transfers.reject', $shareTransfer) }}" method="POST" class="space-y-3 border-t border-slate-100 dark:border-slate-800 pt-4">
                            @csrf
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Reject transfer</label>
                            <textarea name="decision_notes" rows="3" maxlength="2000" placeholder="Reason for rejection (required)" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100" required></textarea>
                            <button type="submit" class="w-full h-10 px-4 text-sm font-semibold text-white bg-[#FF4D6B] rounded-xl hover:bg-[#e63e5c] transition">Reject</button>
                        </form>
                    </div>
                    @endif

                    @if ($shareTransfer->isApproved())
                        <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 no-print">
                            <a href="{{ route('prints.share-transfers.certificate', $shareTransfer) }}" class="w-full h-10 px-4 rounded-xl bg-[#38CE3C] text-white text-sm font-semibold hover:bg-[#2cb030] transition inline-flex items-center justify-center gap-2">
                                <x-icon name="printer" class="w-4 h-4"/> Print Transfer Certificate
                            </a>
                        </div>
                    @endif
                </div>
            </x-panel>
        </div>
    </div>
</div>

@if ($shareTransfer->isApproved())
    <div class="mx-auto w-full max-w-6xl bg-white rounded-2xl shadow-xs border border-slate-200/80 dark:bg-[#181824] dark:border-[#272738] px-5 py-5 sm:px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-6">
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl bg-[#8E32E9]/10 text-[#8E32E9] flex items-center justify-center shrink-0">
                <x-icon name="receipt" class="w-4 h-4"/>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Share transfer certificate</h2>
                <p class="text-xs text-slate-500 mt-1">Certificate {{ $shareTransfer->certificate_reference }}, issued on the secure print register with a verification QR code.</p>
            </div>
        </div>
        <a href="{{ route('prints.share-transfers.certificate', $shareTransfer) }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold shadow-sm inline-flex items-center justify-center gap-2 transition">Open certificate</a>
    </div>
@endif
@endsection
