@extends('layouts.app')

@section('title', 'Members')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-7xl">
    
    <!-- PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Directory</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">Members</h1>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="exportTableToCSV('members.csv')" class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded hover:bg-slate-50 transition">
                <i data-lucide="download" class="w-4 h-4"></i> Export Excel
            </button>
            <a href="{{ route('members.create') }}" class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-white bg-sacco border border-sacco rounded hover:bg-sacco-dark transition">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Member
            </a>
        </div>
    </div>

    <!-- DATA TABLE -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden dark:bg-slate-900 dark:border-slate-800">
        <div class="p-6 flex items-center justify-between border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">All Members</h3>
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-slate-400"></i>
                <input type="text" placeholder="Search members..." class="pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200">
            </div>
        </div>
        
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/50 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Member No</th>
                        <th class="px-6 py-4 font-semibold">Name</th>
                        <th class="px-6 py-4 font-semibold">National ID</th>
                        <th class="px-6 py-4 font-semibold">Phone</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($members as $member)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                        <td class="px-6 py-4 font-medium text-sacco hover:underline cursor-pointer">
                            <a href="{{ route('members.show', $member->id) }}">
                                {{ $member->membership_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold uppercase">
                                {{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}
                            </div>
                            <div class="font-semibold text-slate-800 dark:text-slate-200">
                                {{ $member->first_name }} {{ $member->last_name }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $member->national_id }}</td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $member->phone_number }}</td>
                        <td class="px-6 py-4">
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
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('members.show', $member->id) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 hover:bg-[#38CE3C]/15 hover:text-[#38CE3C] transition dark:bg-slate-800 dark:text-slate-300">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>View</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">No members found in the database.</td>
                    </tr>
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
    </div>
</div>
@endsection
