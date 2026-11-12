@extends('layouts.app')
@section('title', 'Help Topic')
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
    $topic = collect($topics)->firstWhere('key', (string) $id);
    $title = $topic['title'] ?? 'Help topic #' . $id;
    $related = collect($topics)->where('key', '!=', (string) $id)->take(4);
@endphp
<div class="mx-auto w-full max-w-6xl flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div class="flex items-start gap-3">
            <a href="{{ route('help.index') }}" class="mt-1 p-2 bg-white rounded-xl border border-slate-200 text-slate-500 hover:text-[#38CE3C] hover:border-[#38CE3C]/30 hover:bg-[#38CE3C]/5 transition dark:bg-[#181824] dark:border-[#272738]" title="Back to help">
                <x-icon name="arrow-left" class="w-4 h-4"/>
            </a>
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Knowledge base</div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">{{ $title }}</h1>
                <p class="text-sm text-slate-500 mt-1">Guidance for using this module in {{ $orgName }}.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('help.index') }}" class="h-10 px-4 inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200">
                <x-icon name="book-open" class="w-4 h-4"/> All guides
            </a>
        </div>
    </div>

    @if($topic)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 flex flex-col gap-6">
                <x-panel title="Overview" subtitle="What this module covers." icon="{{ $topic['icon'] }}" tone="{{ $topic['tone'] }}">
                    <div class="p-5 sm:p-6">
                        <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">{{ $topic['description'] }}</p>
                        <p class="text-sm text-slate-500 mt-3">Every record you create here is stored against {{ $orgName }} and follows the access rules attached to your role.</p>
                    </div>
                </x-panel>

                <x-panel title="Where to find it" subtitle="Jump straight into the module." icon="arrow-right" tone="green">
                    <div class="p-5">
                        <a href="{{ route($topic['route']) }}" class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                            <span class="flex items-center gap-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200">
                                <span class="h-8 w-8 rounded-lg {{ $tones[$topic['tone']] ?? $tones['green'] }} flex items-center justify-center">
                                    <x-icon :name="$topic['icon']" class="w-4 h-4"/>
                                </span>
                                Open {{ $topic['title'] }}
                            </span>
                            <x-icon name="arrow-right" class="w-4 h-4 text-slate-400"/>
                        </a>
                    </div>
                </x-panel>
            </div>

            <div class="flex flex-col gap-6">
                <x-panel title="Related guides" subtitle="Other modules you may need." icon="book-open" tone="slate">
                    <div class="p-5 space-y-2">
                        @foreach($related as $item)
                            <a href="{{ route('help.show', $item['key']) }}" class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                                <span class="flex items-center gap-2.5 text-sm font-medium text-slate-700 dark:text-slate-200">
                                    <x-icon :name="$item['icon']" class="w-4 h-4 text-slate-400"/> {{ $item['title'] }}
                                </span>
                                <x-icon name="arrow-right" class="w-4 h-4 text-slate-400"/>
                            </a>
                        @endforeach
                    </div>
                </x-panel>
            </div>
        </div>
    @else
        <x-panel title="Help topic" subtitle="This guide could not be matched." icon="help-circle" tone="amber">
            <x-empty-state icon="help-circle" title="No guide found for this topic"
                           message="Return to the knowledge base to browse the available module guides.">
                <a href="{{ route('help.index') }}" class="h-9 px-4 rounded-lg bg-[#38CE3C] text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-[#2cb030] transition">
                    <x-icon name="arrow-left" class="w-3.5 h-3.5"/> Back to help
                </a>
            </x-empty-state>
        </x-panel>
    @endif
</div>
@endsection
