@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-7xl">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Module</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">{{ $title }}</h1>
        </div>
    </div>
    <div class="bg-white p-12 text-center rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800">
        <i data-lucide="wrench" class="w-16 h-16 mx-auto text-slate-300 mb-4"></i>
        <h2 class="text-xl font-bold text-slate-600 dark:text-slate-300">{{ $title }} UI Under Construction</h2>
        <p class="text-slate-500 mt-2">This route has been mapped successfully. The data table layout will be built next.</p>
    </div>
</div>
@endsection
