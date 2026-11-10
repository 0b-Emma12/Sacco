@extends('layouts.app')
@section('title', $title ?? 'Defaulted Instalments')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-7xl">
    
    <!-- PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Loans</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">{{ $title ?? 'Defaulted Instalments' }}</h1>
        </div>
    </div>

    <!-- DATA TABLE -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 text-center dark:bg-slate-900 dark:border-slate-800">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#FF4D6B]/15 text-[#FF4D6B] mb-4 dark:bg-[#FF4D6B]/20">
            <i data-lucide="alert-triangle" class="w-8 h-8"></i>
        </div>
        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-2">No Defaulted Instalments</h3>
        <p class="text-sm text-slate-500 max-w-md mx-auto">
            This module is currently under construction. Defaulted loans and automated recovery protocols will be listed here.
        </p>
    </div>
</div>
@endsection
