@extends('layouts.app')
@section('title', $policy->name)

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-5xl">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">G04 · Policy v{{ $policy->version }}</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">{{ $policy->name }}</h1>
            <p class="text-sm text-slate-500 mt-1">
                {{ $policy->isApproved() ? 'Approved' : 'Draft' }}
                @if ($policy->effective_date) · effective {{ $policy->effective_date->format('d M Y') }} @endif
            </p>
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
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-4">Summary</h3>
                <p class="text-sm whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $policy->summary }}</p>
            </div>
            @if ($policy->change_reason)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 p-6">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-2">Reason for change</h3>
                <p class="text-sm whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $policy->change_reason }}</p>
            </div>
            @endif
            @if ($policy->impact_assessment)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 p-6">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-2">Impact assessment</h3>
                <p class="text-sm whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $policy->impact_assessment }}</p>
            </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 p-6">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4">Status</h3>
                <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-semibold {{ $policy->isApproved() ? 'bg-[#38CE3C]/15 text-[#38CE3C]' : 'bg-[#FFDE73]/30 text-amber-700' }}">
                    {{ $policy->isApproved() ? 'Approved' : 'Draft' }}
                </span>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Version</dt>
                        <dd class="font-medium text-slate-700 dark:text-slate-300">{{ $policy->version }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Effective date</dt>
                        <dd class="font-medium text-slate-700 dark:text-slate-300">{{ optional($policy->effective_date)->format('d M Y') ?: 'Not set' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Prepared by</dt>
                        <dd class="font-medium text-slate-700 dark:text-slate-300">{{ $policy->created_by ?: '—' }}</dd>
                    </div>
                    @if ($policy->isApproved())
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Resolution</dt>
                        <dd class="font-medium text-slate-700 dark:text-slate-300">{{ $policy->resolution_number ?: '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Approved by</dt>
                        <dd class="font-medium text-slate-700 dark:text-slate-300">{{ $policy->approved_by ?: '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Approved at</dt>
                        <dd class="font-medium text-slate-700 dark:text-slate-300">{{ optional($policy->approved_at)->format('d M Y H:i') ?: '—' }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            @if (! $policy->isApproved() && Auth::user()->canApproveLoans())
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 p-6">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-1">Approve policy</h3>
                <p class="text-xs text-slate-500 mb-4">Record the board resolution that adopted this policy.</p>
                <form action="{{ route('governance.policies.approve', $policy) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Resolution number</label>
                        <input type="text" name="resolution_number" value="{{ old('resolution_number') }}" maxlength="100" placeholder="E.g. BR/2026/04" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Effective date</label>
                        <input type="date" name="effective_date" value="{{ old('effective_date', optional($policy->effective_date)->toDateString() ?: now()->toDateString()) }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-sacco rounded hover:bg-sacco-dark transition">Approve Policy</button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
