@extends('layouts.app')
@section('title', $title ?? 'Defaulted Instalments')

@section('content')
<div class="mx-auto w-full max-w-7xl flex flex-col gap-6">
    <x-page-header
        eyebrow="Loans"
        title="{{ $title ?? 'Defaulted Instalments' }}"
        subtitle="Loans in arrears and the automated recovery queue.">
        <a href="{{ route('loans.index') }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2 transition">
            <x-icon name="arrow-left" class="w-4 h-4"/> Back to loans
        </a>
    </x-page-header>

    <x-panel title="Defaulted instalments" subtitle="Recovery tracking for loans that have fallen into arrears." icon="alert-triangle" tone="coral">
        <x-empty-state icon="alert-triangle" title="No defaulted instalments"
                       message="Defaulted loans and automated recovery protocols will be listed here once the arrears engine flags an instalment.">
        </x-empty-state>
    </x-panel>
</div>
@endsection
