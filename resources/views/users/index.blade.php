@extends('layouts.app')
@section('title', 'Staff Management')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-7xl">
    
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Management</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">Staff / Users</h1>
        </div>
        <div class="flex items-center gap-3">
            <button class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-white bg-sacco border border-sacco rounded hover:bg-sacco-dark transition">
                <i data-lucide="user-plus" class="w-4 h-4"></i> Create User
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden dark:bg-slate-900 dark:border-slate-800">
        <div class="p-6 flex items-center justify-between border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">System Users</h3>
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-slate-400"></i>
                <input type="text" placeholder="Search username..." class="pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200">
            </div>
        </div>
        
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/50 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Image</th>
                        <th class="px-6 py-4 font-semibold">User Name</th>
                        <th class="px-6 py-4 font-semibold">Email</th>
                        <th class="px-6 py-4 font-semibold">Role</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                        <td class="px-6 py-4">
                            @if($user->profile_image)
                                <img src="{{ $user->profile_image }}" alt="{{ $user->username }}" class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold">
                                    {{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-200">{{ $user->username }}</td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $user->email }}</td>
                        <td class="px-6 py-4">
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
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                {{ $roleDisplay }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button class="text-slate-400 hover:text-sacco"><i data-lucide="edit" class="w-4 h-4"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">No users found in the database.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
