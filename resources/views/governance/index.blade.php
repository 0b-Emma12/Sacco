@extends('layouts.app')
@section('title', 'Governance')

@section('content')
<div class="mx-auto w-full max-w-7xl flex flex-col gap-6">
    @if (session('success'))
        <div class="p-4 text-sm text-[#38CE3C] font-semibold bg-[#38CE3C]/10 border border-[#38CE3C]/20 rounded-xl" role="alert">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="p-4 text-sm text-[#FF4D6B] bg-[#FF4D6B]/10 border border-[#FF4D6B]/20 rounded-xl" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <x-page-header
        eyebrow="Governance"
        title="Minutes & Policies"
        subtitle="G01 board minutes, G03 annual general meeting and G04 policy approvals.">
        <a href="{{ route('governance.meetings.create', 'BOARD') }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold transition inline-flex items-center gap-2 shadow-sm">
            <x-icon name="plus" class="w-4 h-4"/> New board minutes
        </a>
        <a href="{{ route('governance.meetings.create', 'AGM') }}" class="h-10 px-4 rounded-xl bg-[#8E32E9] hover:bg-[#7822d6] text-white text-sm font-semibold transition inline-flex items-center gap-2 shadow-sm">
            <x-icon name="plus" class="w-4 h-4"/> New AGM minutes
        </a>
        <a href="{{ route('governance.policies.create') }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold inline-flex items-center gap-2 hover:bg-slate-50 transition dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200">
            <x-icon name="file-plus-2" class="w-4 h-4"/> New policy
        </a>
    </x-page-header>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Board meetings" :value="number_format($stats['board'])" meta="Minutes on record" icon="building" tone="blue"/>
        <x-stat-card label="AGMs" :value="number_format($stats['agm'])" meta="Annual general meetings" icon="users" tone="purple"/>
        <x-stat-card label="Minutes awaiting approval" :value="number_format($stats['pending_meetings'])" meta="Draft minutes to approve" icon="clock" tone="amber"/>
        <x-stat-card label="Policies awaiting approval" :value="number_format($stats['pending_policies'])" meta="Draft policies to approve" icon="file-text" tone="coral"/>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-panel title="Meeting minutes" subtitle="Recorded board and annual general meetings." icon="building">
            <div class="w-full overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Meeting</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($meetings as $meeting)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $meeting->title }}</div>
                                    <div class="text-[11px] text-slate-400">{{ \App\Models\GovernanceMeeting::TYPES[$meeting->type] ?? $meeting->type }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $meeting->meeting_date->format('d M Y') }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $meeting->isApproved() ? 'bg-[#38CE3C]/15 text-[#38CE3C] border-[#38CE3C]/20' : 'bg-[#FFDE73]/30 text-amber-700 border-[#FFDE73]/40 dark:text-amber-300' }}">
                                        {{ ucfirst(strtolower($meeting->status)) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('governance.meetings.show', $meeting) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] text-xs font-semibold hover:bg-[#38CE3C]/20 transition">Open</a>
                                </td>
                            </tr>
                        @empty
                            <x-empty-state colspan="4" icon="building" title="No minutes recorded yet"
                                           message="Record the first board or AGM minutes to start the governance file.">
                                <a href="{{ route('governance.meetings.create', 'BOARD') }}" class="h-9 px-4 rounded-lg bg-[#38CE3C] text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-[#2cb030] transition">
                                    <x-icon name="plus" class="w-3.5 h-3.5"/> New board minutes
                                </a>
                            </x-empty-state>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($meetings->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">{{ $meetings->links() }}</div>
            @endif
        </x-panel>

        <x-panel title="Policies" subtitle="Policy versions approved by the board." icon="file-text" tone="purple">
            <div class="w-full overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Policy</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Version</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($policies as $policy)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $policy->name }}</div>
                                    <div class="text-[11px] text-slate-400">Effective {{ optional($policy->effective_date)->format('d M Y') ?: 'not set' }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $policy->version }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $policy->isApproved() ? 'bg-[#38CE3C]/15 text-[#38CE3C] border-[#38CE3C]/20' : 'bg-[#FFDE73]/30 text-amber-700 border-[#FFDE73]/40 dark:text-amber-300' }}">
                                        {{ ucfirst(strtolower($policy->status)) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('governance.policies.show', $policy) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#8E32E9]/10 text-[#8E32E9] text-xs font-semibold hover:bg-[#8E32E9]/20 transition">Open</a>
                                </td>
                            </tr>
                        @empty
                            <x-empty-state colspan="4" icon="file-text" title="No policies recorded yet"
                                           message="Record the first policy version for board approval.">
                                <a href="{{ route('governance.policies.create') }}" class="h-9 px-4 rounded-lg bg-[#8E32E9] text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-[#7822d6] transition">
                                    <x-icon name="file-plus-2" class="w-3.5 h-3.5"/> New policy
                                </a>
                            </x-empty-state>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($policies->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">{{ $policies->links() }}</div>
            @endif
        </x-panel>
    </div>
</div>
@endsection
