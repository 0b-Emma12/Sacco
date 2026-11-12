@extends('layouts.app')
@section('title', 'User Details')
@section('content')
@php
    $roleDisplay = $user->role_label;
    $roleClass = match($user->role) {
        'AD' => 'bg-[#FF4D6B]/15 text-[#FF4D6B] border-[#FF4D6B]/20',
        'MA' => 'bg-[#8E32E9]/15 text-[#8E32E9] border-[#8E32E9]/20',
        'LO' => 'bg-[#FFDE73]/30 text-amber-700 border-[#FFDE73]/40 dark:text-amber-300',
        'AC' => 'bg-[#38CE3C]/15 text-[#38CE3C] border-[#38CE3C]/20',
        default => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'
    };
    $permissions = [
        ['label' => 'Approve loans', 'granted' => $user->canApproveLoans()],
        ['label' => 'Disburse loans', 'granted' => $user->canDisburseLoans()],
        ['label' => 'Appraise loans', 'granted' => $user->canAppraiseLoans()],
        ['label' => 'Verify member KYC', 'granted' => $user->canVerifyKyc()],
    ];
@endphp
<div class="mx-auto w-full max-w-6xl flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div class="flex items-start gap-3">
            <a href="{{ route('users.index') }}" class="mt-1 p-2 bg-white rounded-xl border border-slate-200 text-slate-500 hover:text-[#38CE3C] hover:border-[#38CE3C]/30 hover:bg-[#38CE3C]/5 transition dark:bg-[#181824] dark:border-[#272738]" title="Back to users">
                <x-icon name="arrow-left" class="w-4 h-4"/>
            </a>
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Staff account</div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">{{ $user->username ?: $user->email }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ $roleDisplay }} &middot; Joined {{ $user->created_at ? $user->created_at->format('d M Y') : '—' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('users.index') }}" class="h-10 px-4 inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200">
                <x-icon name="users" class="w-4 h-4"/> All users
            </a>
            <a href="{{ route('users.create') }}" class="h-10 px-4 inline-flex items-center gap-2 rounded-xl bg-[#38CE3C] text-white text-sm font-semibold shadow-sm hover:bg-[#2cb030] transition">
                <x-icon name="user-plus" class="w-4 h-4"/> New user
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 flex flex-col gap-6">
            <x-panel title="Account particulars" subtitle="Registration details stored for this system user." icon="user">
                <dl class="grid gap-x-6 gap-y-5 p-5 sm:p-6 sm:grid-cols-2 text-sm">
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">User ID</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-100">#{{ $user->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Username</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-100">{{ $user->username ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Email address</dt>
                        <dd class="text-slate-800 dark:text-slate-100">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Role</dt>
                        <dd>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $roleClass }}">
                                {{ $roleDisplay }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Account created</dt>
                        <dd class="text-slate-800 dark:text-slate-100">{{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Email verified</dt>
                        <dd class="text-slate-800 dark:text-slate-100">{{ $user->email_verified_at ? $user->email_verified_at->format('d M Y, H:i') : 'Not verified' }}</dd>
                    </div>
                </dl>
            </x-panel>

            <x-panel title="Access permissions" subtitle="Actions this role is allowed to perform." icon="shield" tone="purple">
                <div class="p-5 sm:p-6 grid gap-3 sm:grid-cols-2">
                    @foreach($permissions as $permission)
                        <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50">
                            <span class="flex items-center gap-2.5 text-sm font-medium text-slate-700 dark:text-slate-200">
                                <x-icon name="{{ $permission['granted'] ? 'check-circle' : 'x-circle' }}" class="w-4 h-4 {{ $permission['granted'] ? 'text-[#38CE3C]' : 'text-slate-400' }}"/>
                                {{ $permission['label'] }}
                            </span>
                            <span class="text-[11px] font-bold uppercase tracking-wider {{ $permission['granted'] ? 'text-[#38CE3C]' : 'text-slate-400' }}">
                                {{ $permission['granted'] ? 'Allowed' : 'No' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </x-panel>
        </div>

        <div class="flex flex-col gap-6">
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 text-center dark:bg-[#181824] dark:border-[#272738]">
                @if($user->profile_image)
                    <img src="{{ $user->profile_image }}" alt="{{ $user->username }}" class="h-20 w-20 mx-auto rounded-full object-cover border border-[#38CE3C]/30">
                @else
                    <div class="flex h-20 w-20 mx-auto items-center justify-center rounded-full bg-[#38CE3C]/15 text-[#38CE3C] font-bold text-2xl">
                        {{ strtoupper(substr($user->username ?? $user->email, 0, 1)) }}
                    </div>
                @endif
                <div class="mt-4 text-base font-bold text-slate-800 dark:text-slate-100">{{ $user->username ?: 'Unnamed user' }}</div>
                <div class="text-xs text-slate-500 mt-0.5 break-all">{{ $user->email }}</div>
                <div class="mt-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $roleClass }}">
                        {{ $roleDisplay }}
                    </span>
                </div>
            </div>

            <x-panel title="User directory" subtitle="Return to the staff register." icon="users" tone="slate">
                <div class="p-5 space-y-2">
                    <a href="{{ route('users.index') }}" class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                        <span class="flex items-center gap-2.5 text-sm font-medium text-slate-700 dark:text-slate-200">
                            <x-icon name="users" class="w-4 h-4 text-[#38CE3C]"/> All system users
                        </span>
                        <x-icon name="arrow-right" class="w-4 h-4 text-slate-400"/>
                    </a>
                    <a href="{{ route('users.create') }}" class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                        <span class="flex items-center gap-2.5 text-sm font-medium text-slate-700 dark:text-slate-200">
                            <x-icon name="user-plus" class="w-4 h-4 text-[#8E32E9]"/> Create another user
                        </span>
                        <x-icon name="arrow-right" class="w-4 h-4 text-slate-400"/>
                    </a>
                </div>
            </x-panel>
        </div>
    </div>
</div>
@endsection
