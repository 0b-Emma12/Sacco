@extends('layouts.app')

@section('title', 'Members')

@section('content')
<div class="mx-auto w-full max-w-7xl flex flex-col gap-6">
    
    @if(session('success'))
        <div class="p-4 text-sm text-[#38CE3C] font-semibold rounded-xl bg-[#38CE3C]/10 border border-[#38CE3C]/20" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <x-page-header
        eyebrow="Directory"
        title="Members"
        subtitle="Member register with savings, share capital and KYC status.">
        <a href="{{ route('prints.statements') }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2 transition">
            <x-icon name="printer" class="w-4 h-4"/> Print statements
        </a>
        <a href="{{ route('members.export', ['q' => request('q')]) }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2 transition">
            <x-icon name="download" class="w-4 h-4"/> Export Excel
        </a>
        <a href="{{ route('members.create') }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold shadow-sm inline-flex items-center gap-2 transition">
            <x-icon name="plus" class="w-4 h-4"/> Add member
        </a>
    </x-page-header>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Members" :value="number_format($summary['total'])" meta="Matching the current filter" icon="users" tone="blue"/>
        <x-stat-card label="Active members" :value="number_format($summary['active'])" meta="Status marked active" icon="user" tone="green"/>
        <x-stat-card label="Total savings" :value="'UGX '.number_format($summary['savings'], 2)" meta="Sum of member savings balances" icon="wallet" tone="purple"/>
        <x-stat-card label="Share capital" :value="'UGX '.number_format($summary['shares'], 2)" meta="Non-withdrawable equity" icon="coins" tone="amber"/>
    </div>

    <x-panel title="Member directory" subtitle="Newest registrations first, fifteen per page." icon="users">
        <x-slot name="actions">
            <form method="GET" action="{{ route('members.index') }}" class="flex flex-wrap items-center gap-2 m-0">
                <div class="relative">
                    <x-icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                    <input type="search" name="q" value="{{ request('q') }}"
                           placeholder="Search name, ID, member no..."
                           class="h-10 w-full sm:w-64 pl-9 pr-3 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">
                </div>
                <button type="submit" class="h-10 px-4 rounded-xl bg-[#181824] text-white text-sm font-semibold hover:bg-slate-700 transition dark:bg-white dark:text-slate-900">Search</button>
                @if(request('q'))
                    <a href="{{ route('members.index') }}" class="h-10 px-3 inline-flex items-center rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition dark:border-slate-700 dark:text-slate-300">Clear</a>
                @endif
            </form>
        </x-slot>

        <div class="w-full overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Member no</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Name</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">National ID</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Phone</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($members as $member)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4">
                            <a href="{{ route('members.show', $member->id) }}" class="text-sm font-semibold text-[#38CE3C] hover:underline">
                                {{ $member->membership_number }}
                            </a>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold uppercase">
                                    {{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}
                                </div>
                                <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $member->first_name }} {{ $member->last_name }}
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $member->national_id }}</td>
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $member->phone_number }}</td>
                        <td class="px-5 py-4">
                            @php
                                $st = strtolower($member->status);
                            @endphp
                            @if($st === 'active')
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20">
                                    Active
                                </span>
                            @elseif(in_array($st, ['exited', 'suspended', 'closed']))
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20">
                                    {{ ucfirst($member->status) }}
                                </span>
                            @elseif(in_array($st, ['pending', 'incubating']))
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-[#FFDE73]/30 text-amber-700 dark:text-amber-300 border border-amber-300/30">
                                    {{ ucfirst($member->status) }}
                                </span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    {{ ucfirst($member->status) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('members.show', $member->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] hover:bg-[#38CE3C]/20 transition">
                                <x-icon name="eye" class="w-3.5 h-3.5"/>
                                <span>View</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                        <x-empty-state colspan="6" icon="users" title="No members match this filter"
                                       message="Try a different search term, or register the first member of the SACCO.">
                            <a href="{{ route('members.create') }}" class="h-9 px-4 rounded-lg bg-[#38CE3C] text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-[#2cb030] transition">
                                <x-icon name="plus" class="w-3.5 h-3.5"/> Add member
                            </a>
                        </x-empty-state>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination (if applicable) -->
        @if($members->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $members->links() }}
        </div>
        @endif
    </x-panel>
</div>
@endsection
