@extends('layouts.app')
@section('title', 'SMS Broadcast Details')
@section('content')
<div class="mx-auto w-full max-w-5xl flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div class="flex items-start gap-3">
            <a href="{{ route('sms.index') }}" class="mt-1 p-2 bg-white rounded-xl border border-slate-200 text-slate-500 hover:text-[#38CE3C] hover:border-[#38CE3C]/30 hover:bg-[#38CE3C]/5 transition dark:bg-[#181824] dark:border-[#272738]" title="Back to SMS broadcasts">
                <x-icon name="arrow-left" class="w-4 h-4"/>
            </a>
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">SMS broadcast</div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Message #{{ $id }}</h1>
                <p class="text-sm text-slate-500 mt-1">Delivery record for this broadcast reference.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('sms.index') }}" class="h-10 px-4 inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200">
                <x-icon name="message-square" class="w-4 h-4"/> Message register
            </a>
            <a href="{{ route('sms.create') }}" class="h-10 px-4 inline-flex items-center gap-2 rounded-xl bg-[#38CE3C] text-white text-sm font-semibold shadow-sm hover:bg-[#2cb030] transition">
                <x-icon name="plus" class="w-4 h-4"/> New SMS
            </a>
        </div>
    </div>

    <x-panel title="Delivery details" subtitle="Outcome recorded for this SMS reference." icon="message-square" tone="purple">
        <x-empty-state icon="clock" title="No message record available"
                       message="This module does not store individual SMS records yet, so no delivery details can be shown for this reference.">
            <a href="{{ route('sms.create') }}" class="h-9 px-4 rounded-lg bg-[#38CE3C] text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-[#2cb030] transition">
                <x-icon name="send" class="w-3.5 h-3.5"/> Compose SMS
            </a>
        </x-empty-state>
    </x-panel>
</div>
@endsection
