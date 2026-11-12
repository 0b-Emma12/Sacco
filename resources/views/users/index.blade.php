@extends('layouts.app')
@section('title', 'Staff Management')

@section('content')
<div class="mx-auto w-full max-w-7xl flex flex-col gap-6">
    @if(session('success'))
        <div class="p-4 text-sm text-[#38CE3C] font-semibold rounded-xl bg-[#38CE3C]/10 border border-[#38CE3C]/20" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <x-page-header
        eyebrow="Administration"
        title="Staff & Users"
        subtitle="System accounts, their roles and access to this SACCO's records.">
        <a href="{{ route('users.create') }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold transition inline-flex items-center gap-2 shadow-sm">
            <x-icon name="user-plus" class="w-4 h-4"/>
            Create user
        </a>
    </x-page-header>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total users" :value="number_format($stats['total'])" meta="All system accounts" icon="users" tone="green"/>
        <x-stat-card label="Administrators" :value="number_format($stats['admins'])" meta="Full system access" icon="shield" tone="coral"/>
        <x-stat-card label="Managers" :value="number_format($stats['managers'])" meta="Approval authority" icon="user" tone="purple"/>
        <x-stat-card label="Operations & finance" :value="number_format($stats['operations'])" meta="Day-to-day processing roles" icon="sliders" tone="blue"/>
    </div>

    <x-panel title="System users" subtitle="Newest accounts first, fifteen per page." icon="users">
        <x-slot name="actions">
            <div class="relative">
                <x-icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                <input type="text" placeholder="Search username..."
                       class="h-10 w-full sm:w-64 pl-9 pr-3 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">
            </div>
        </x-slot>
        
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">User</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Email</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Role</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Joined</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($users as $user)
                        @php
                            $roleDisplay = match($user->role) {
                                'AD' => 'Admin',
                                'MA' => 'Manager',
                                'OP' => 'Operation Manager',
                                'FI' => 'Finance Officer',
                                'LO' => 'Loan Officer',
                                'AC' => 'Accountant',
                                default => $user->role
                            };
                            $roleClass = match($user->role) {
                                'AD' => 'bg-[#FF4D6B]/15 text-[#FF4D6B] border-[#FF4D6B]/20',
                                'MA' => 'bg-[#8E32E9]/15 text-[#8E32E9] border-[#8E32E9]/20',
                                'LO' => 'bg-[#FFDE73]/30 text-amber-700 border-[#FFDE73]/40 dark:text-amber-300',
                                'AC' => 'bg-[#38CE3C]/15 text-[#38CE3C] border-[#38CE3C]/20',
                                default => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if($user->profile_image)
                                        <img src="{{ $user->profile_image }}" alt="{{ $user->username }}" class="h-9 w-9 rounded-full object-cover border border-[#38CE3C]/30">
                                    @else
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#38CE3C]/15 text-[#38CE3C] font-bold text-xs">
                                            {{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $user->username ?: '—' }}</div>
                                        <div class="text-[11px] text-slate-400">#{{ $user->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $user->email }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $roleClass }}">
                                    {{ $roleDisplay }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400">
                                {{ $user->created_at ? $user->created_at->format('d M Y') : '—' }}
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('users.show', $user->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] text-xs font-semibold hover:bg-[#38CE3C]/20 transition">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <x-empty-state colspan="5" icon="users" title="No users found"
                                       message="Create a system user to give a staff member access to this SACCO.">
                            <a href="{{ route('users.create') }}" class="h-9 px-4 rounded-lg bg-[#38CE3C] text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-[#2cb030] transition">
                                <x-icon name="user-plus" class="w-3.5 h-3.5"/> Create user
                            </a>
                        </x-empty-state>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $users->links() }}
            </div>
        @endif
    </x-panel>
</div>
@endsection
