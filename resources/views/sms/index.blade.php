@extends('layouts.app')
@section('title', 'SMS Broadcast')
@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-5xl">

    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Communications</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">SMS Broadcast</h1>
        </div>
        <a href="{{ route('sms.create') }}" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#38CE3C] border border-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition shadow-sm">
            <i data-lucide="plus" class="w-4 h-4"></i> New SMS
        </a>
    </div>

    <!-- Empty State / Coming Soon -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-12 dark:bg-slate-900 dark:border-slate-800">
        <div class="flex flex-col items-center justify-center text-center">
            <div class="h-16 w-16 rounded-2xl bg-[#38CE3C]/10 flex items-center justify-center mb-4">
                <i data-lucide="message-square" class="w-8 h-8 text-[#38CE3C]"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-2">SMS Broadcast Module</h3>
            <p class="text-sm text-slate-500 max-w-md mx-auto mb-6">
                Send bulk SMS alerts to SACCO members for loan reminders, transaction confirmations, 
                dividend announcements, and meeting notices.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full max-w-lg">
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl text-center">
                    <i data-lucide="users" class="w-5 h-5 text-[#38CE3C] mx-auto mb-2"></i>
                    <div class="text-xs font-semibold text-slate-600 dark:text-slate-400">Bulk Broadcast</div>
                </div>
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl text-center">
                    <i data-lucide="clock" class="w-5 h-5 text-[#8E32E9] mx-auto mb-2"></i>
                    <div class="text-xs font-semibold text-slate-600 dark:text-slate-400">Scheduled SMS</div>
                </div>
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl text-center">
                    <i data-lucide="bar-chart-2" class="w-5 h-5 text-[#FFDE73] mx-auto mb-2"></i>
                    <div class="text-xs font-semibold text-slate-600 dark:text-slate-400">Delivery Reports</div>
                </div>
            </div>
            <a href="{{ route('sms.create') }}" class="mt-6 px-5 py-2.5 text-sm font-semibold text-white bg-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition shadow-sm">
                Send First SMS
            </a>
        </div>
    </div>

</div>
@endsection
