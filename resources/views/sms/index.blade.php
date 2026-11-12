@extends('layouts.app')
@section('title', 'SMS Broadcast')
@section('content')
<div class="mx-auto w-full max-w-6xl flex flex-col gap-6">

    @if(session('success'))
        <div class="p-4 text-sm text-[#38CE3C] font-semibold rounded-xl bg-[#38CE3C]/10 border border-[#38CE3C]/20" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <x-page-header
        eyebrow="Communications"
        title="SMS Broadcast"
        subtitle="Bulk alerts to members for loan reminders, transaction confirmations and meeting notices.">
        <a href="{{ route('sms.create') }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold transition inline-flex items-center gap-2 shadow-sm">
            <x-icon name="plus" class="w-4 h-4"/>
            New SMS
        </a>
    </x-page-header>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-3">
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 dark:bg-[#181824] dark:border-[#272738]">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">Bulk broadcast</span>
                <span class="p-2 rounded-xl bg-[#38CE3C]/10 text-[#38CE3C]"><x-icon name="users" class="w-4 h-4"/></span>
            </div>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">Reach member groups with a single message.</p>
        </div>
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 dark:bg-[#181824] dark:border-[#272738]">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">Scheduled SMS</span>
                <span class="p-2 rounded-xl bg-[#8E32E9]/10 text-[#8E32E9]"><x-icon name="clock" class="w-4 h-4"/></span>
            </div>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">Queue reminders for a future date and time.</p>
        </div>
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 dark:bg-[#181824] dark:border-[#272738]">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">Delivery reports</span>
                <span class="p-2 rounded-xl bg-[#FFDE73]/30 text-amber-600 dark:text-amber-300"><x-icon name="bar-chart-2" class="w-4 h-4"/></span>
            </div>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">Track which messages reached their recipients.</p>
        </div>
    </div>

    <x-panel title="Message register" subtitle="Sent and scheduled broadcasts appear here." icon="message-square">
        <x-empty-state icon="inbox" title="No SMS broadcasts recorded yet"
                       message="Compose the first message to alert members about savings, loans and meetings.">
            <a href="{{ route('sms.create') }}" class="h-9 px-4 rounded-lg bg-[#38CE3C] text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-[#2cb030] transition">
                <x-icon name="send" class="w-3.5 h-3.5"/> Compose SMS
            </a>
        </x-empty-state>
    </x-panel>
</div>
@endsection
