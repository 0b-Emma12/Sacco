@extends('layouts.app')
@section('title', $meeting->title)

@section('content')
@php
    $isAgm = $meeting->type === 'AGM';
    $typeLabel = \App\Models\GovernanceMeeting::TYPES[$meeting->type] ?? $meeting->type;
@endphp
<div class="flex flex-col gap-6 mx-auto w-full max-w-5xl">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{{ $isAgm ? 'G03 · AGM' : 'G01 · Board' }}</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">{{ $meeting->title }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $typeLabel }} · {{ $meeting->meeting_date->format('d M Y') }}{{ $meeting->venue ? ' · '.$meeting->venue : '' }}</p>
        </div>
        <a href="{{ route('governance.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Back to Governance</a>
    </div>

    @if (session('success'))
        <div class="p-4 text-sm text-[#38CE3C] font-semibold bg-[#38CE3C]/10 border border-[#38CE3C]/20 rounded-xl">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="p-4 text-sm text-[#FF4D6B] bg-[#FF4D6B]/10 border border-[#FF4D6B]/20 rounded-xl">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 p-6">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-4">Record</h3>
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Agenda</dt>
                        <dd class="mt-1 whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $meeting->agenda ?: 'Not recorded.' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Attendance</dt>
                        <dd class="mt-1 whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $meeting->attendance ?: 'Not recorded.' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Absent with apology</dt>
                        <dd class="mt-1 whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $meeting->absent_with_apology ?: 'None recorded.' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Resolutions</dt>
                        <dd class="mt-1 whitespace-pre-line {{ $meeting->resolutions ? 'text-slate-700 dark:text-slate-300' : 'text-amber-600 font-medium' }}">{{ $meeting->resolutions ?: 'No resolutions recorded — required before approval.' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Action items</dt>
                        <dd class="mt-1 whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $meeting->action_items ?: 'None recorded.' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 p-6">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4">Status</h3>
                <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-semibold {{ $meeting->isApproved() ? 'bg-[#38CE3C]/15 text-[#38CE3C]' : 'bg-[#FFDE73]/30 text-amber-700' }}">
                    {{ $meeting->isApproved() ? 'Approved' : 'Draft' }}
                </span>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Type</dt>
                        <dd class="font-medium text-slate-700 dark:text-slate-300">{{ $typeLabel }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Quorum</dt>
                        <dd class="font-medium {{ $meeting->quorum_met ? 'text-[#38CE3C]' : 'text-[#FF4D6B]' }}">{{ $meeting->quorum_met ? 'Met' : 'Not met' }}</dd>
                    </div>
                    @if ($isAgm)
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Attendance</dt>
                        <dd class="font-medium text-slate-700 dark:text-slate-300">{{ (int) $meeting->members_present }} / {{ (int) $meeting->members_total }} members</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Dividend on shares</dt>
                        <dd class="font-medium text-slate-700 dark:text-slate-300">{{ $meeting->dividend_on_shares !== null ? $meeting->dividend_on_shares.'%' : '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Interest on savings</dt>
                        <dd class="font-medium text-slate-700 dark:text-slate-300">{{ $meeting->interest_on_savings !== null ? $meeting->interest_on_savings.'%' : '—' }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Prepared by</dt>
                        <dd class="font-medium text-slate-700 dark:text-slate-300">{{ $meeting->prepared_by ?: '—' }}</dd>
                    </div>
                    @if ($meeting->isApproved())
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Approved by</dt>
                        <dd class="font-medium text-slate-700 dark:text-slate-300">{{ $meeting->approved_by ?: '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Approved at</dt>
                        <dd class="font-medium text-slate-700 dark:text-slate-300">{{ optional($meeting->approved_at)->format('d M Y H:i') ?: '—' }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            @if (! $meeting->isApproved() && Auth::user()->canApproveLoans())
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 p-6">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-1">Approve minutes</h3>
                <p class="text-xs text-slate-500 mb-4">Approval locks the record. Resolutions must be present.</p>
                <form action="{{ route('governance.meetings.approve', $meeting) }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="text" name="approved_by" value="{{ old('approved_by', Auth::user()->username ?? Auth::user()->email) }}" maxlength="100" placeholder="Approved by" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">
                    <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-sacco rounded hover:bg-sacco-dark transition">Approve &amp; Lock</button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
