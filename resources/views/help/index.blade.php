@extends('layouts.app')
@section('title', 'Help & Knowledge Base')
@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-5xl">

    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">System</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">Help & Knowledge Base</h1>
        </div>
    </div>

    <!-- Quick Help Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 dark:bg-slate-900 dark:border-slate-800 hover:shadow-md transition group">
            <div class="h-10 w-10 rounded-xl bg-[#38CE3C]/10 flex items-center justify-center mb-4 group-hover:bg-[#38CE3C]/20 transition">
                <i data-lucide="users" class="w-5 h-5 text-[#38CE3C]"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-1">Member Management</h3>
            <p class="text-xs text-slate-500">Register members, manage profiles, track exit procedures and NOK details.</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 dark:bg-slate-900 dark:border-slate-800 hover:shadow-md transition group">
            <div class="h-10 w-10 rounded-xl bg-[#8E32E9]/10 flex items-center justify-center mb-4 group-hover:bg-[#8E32E9]/20 transition">
                <i data-lucide="credit-card" class="w-5 h-5 text-[#8E32E9]"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-1">Loan Processing</h3>
            <p class="text-xs text-slate-500">Apply for loans, manage guarantors, track repayment schedules.</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 dark:bg-slate-900 dark:border-slate-800 hover:shadow-md transition group">
            <div class="h-10 w-10 rounded-xl bg-[#FFDE73]/30 flex items-center justify-center mb-4 group-hover:bg-[#FFDE73]/50 transition">
                <i data-lucide="book-open" class="w-5 h-5 text-amber-600"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-1">General Ledger</h3>
            <p class="text-xs text-slate-500">Post double-entry journal entries, view trial balance and balance sheet.</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 dark:bg-slate-900 dark:border-slate-800 hover:shadow-md transition group">
            <div class="h-10 w-10 rounded-xl bg-[#FF4D6B]/10 flex items-center justify-center mb-4 group-hover:bg-[#FF4D6B]/20 transition">
                <i data-lucide="receipt" class="w-5 h-5 text-[#FF4D6B]"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-1">Expenses & Approval</h3>
            <p class="text-xs text-slate-500">Record operating expenses, manage approval workflow by category.</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 dark:bg-slate-900 dark:border-slate-800 hover:shadow-md transition group">
            <div class="h-10 w-10 rounded-xl bg-[#38CE3C]/10 flex items-center justify-center mb-4 group-hover:bg-[#38CE3C]/20 transition">
                <i data-lucide="pie-chart" class="w-5 h-5 text-[#38CE3C]"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-1">Reports & Analytics</h3>
            <p class="text-xs text-slate-500">Generate financial reports, filter by date range, export CSV data.</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 dark:bg-slate-900 dark:border-slate-800 hover:shadow-md transition group">
            <div class="h-10 w-10 rounded-xl bg-[#8E32E9]/10 flex items-center justify-center mb-4 group-hover:bg-[#8E32E9]/20 transition">
                <i data-lucide="settings" class="w-5 h-5 text-[#8E32E9]"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-1">System Settings</h3>
            <p class="text-xs text-slate-500">Configure organization details, currency, fiscal year and notifications.</p>
        </div>
    </div>

    <!-- System Info -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 dark:bg-slate-900 dark:border-slate-800">
        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-4">System Information</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Application</div>
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Open SACCO v2.0</div>
            </div>
            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Framework</div>
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Laravel 9.x</div>
            </div>
            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Database</div>
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">SQLite</div>
            </div>
            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Design System</div>
                <div class="text-sm font-semibold text-[#38CE3C]">Stellar Admin</div>
            </div>
        </div>
    </div>

</div>
@endsection
