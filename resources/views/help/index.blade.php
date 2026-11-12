@extends('layouts.app')
@section('title', 'Help & Knowledge Base')
@section('content')
@php
    $topics = [
        ['key' => 'members', 'title' => 'Member Management', 'description' => 'Register members, manage profiles, track exit procedures and next-of-kin details.', 'route' => 'members.index', 'icon' => 'users', 'tone' => 'green'],
        ['key' => 'loans', 'title' => 'Loan Processing', 'description' => 'Apply for loans, manage guarantors and follow repayment schedules.', 'route' => 'loans.index', 'icon' => 'credit-card', 'tone' => 'purple'],
        ['key' => 'accounting', 'title' => 'General Ledger', 'description' => 'Post double-entry journal entries, view the trial balance and balance sheet.', 'route' => 'accounting.index', 'icon' => 'book-open', 'tone' => 'amber'],
        ['key' => 'expenses', 'title' => 'Expenses & Approval', 'description' => 'Record operating expenses and manage the approval workflow by category.', 'route' => 'expenses.index', 'icon' => 'receipt', 'tone' => 'coral'],
        ['key' => 'reports', 'title' => 'Reports & Analytics', 'description' => 'Generate financial reports, filter by date range and export CSV data.', 'route' => 'reports.index', 'icon' => 'pie-chart', 'tone' => 'green'],
        ['key' => 'settings', 'title' => 'System Settings', 'description' => 'Configure organisation details, currency, fiscal year and notifications.', 'route' => 'settings.index', 'icon' => 'settings', 'tone' => 'purple'],
    ];
    $tones = [
        'green' => 'bg-[#38CE3C]/10 text-[#38CE3C]',
        'purple' => 'bg-[#8E32E9]/10 text-[#8E32E9]',
        'amber' => 'bg-[#FFDE73]/30 text-amber-600 dark:text-amber-300',
        'coral' => 'bg-[#FF4D6B]/10 text-[#FF4D6B]',
    ];
@endphp
<div class="mx-auto w-full max-w-6xl flex flex-col gap-6">

    <x-page-header
        eyebrow="System"
        title="Help & Knowledge Base"
        subtitle="Short guides to the modules that run this SACCO's records and money.">
        <a href="{{ route('dashboard') }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold inline-flex items-center gap-2 hover:bg-slate-50 transition dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200">
            <x-icon name="layout-dashboard" class="w-4 h-4"/> Dashboard
        </a>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($topics as $topic)
            <a href="{{ route($topic['route']) }}" class="group bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 dark:bg-[#181824] dark:border-[#272738] hover:shadow-md transition flex flex-col">
                <div class="flex items-center justify-between">
                    <div class="h-10 w-10 rounded-xl {{ $tones[$topic['tone']] ?? $tones['green'] }} flex items-center justify-center">
                        <x-icon :name="$topic['icon']" class="w-5 h-5"/>
                    </div>
                    <x-icon name="arrow-right" class="w-4 h-4 text-slate-300 group-hover:text-[#38CE3C] transition"/>
                </div>
                <h3 class="font-bold text-slate-800 dark:text-slate-100 mt-4">{{ $topic['title'] }}</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $topic['description'] }}</p>
                <span class="mt-4 text-[11px] font-bold uppercase tracking-wider text-[#38CE3C]">Open module</span>
            </a>
        @endforeach
    </div>

    <x-panel title="System information" subtitle="Runtime details for this installation." icon="info" tone="slate">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-5">
            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Organisation</div>
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $orgName }}</div>
            </div>
            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Framework</div>
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Laravel {{ app()->version() }}</div>
            </div>
            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Database</div>
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ strtoupper(config('database.default')) }}</div>
            </div>
            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Environment</div>
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ ucfirst(config('app.env')) }}</div>
            </div>
        </div>
    </x-panel>

</div>
@endsection
