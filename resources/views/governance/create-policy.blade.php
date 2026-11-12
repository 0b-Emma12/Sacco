@extends('layouts.app')
@section('title', 'New Policy')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-4xl">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Governance</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">New Policy</h1>
            <p class="text-sm text-slate-500 mt-1">G04 — record a policy version for board approval.</p>
        </div>
        <a href="{{ route('governance.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Back to Governance</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 p-6 md:p-8">
        @if ($errors->any())
            <div class="bg-[#FF4D6B]/10 text-[#FF4D6B] border border-[#FF4D6B]/20 p-4 rounded-xl mb-6 text-sm font-semibold">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('governance.policies.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="space-y-4">
                <div class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-md space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Policy name</label>
                            <input type="text" name="name" value="{{ old('name') }}" maxlength="150" placeholder="E.g. Loan Recovery Policy" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Version</label>
                            <input type="text" name="version" value="{{ old('version', '1.0') }}" maxlength="30" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Proposed effective date</label>
                            <input type="date" name="effective_date" value="{{ old('effective_date') }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Policy summary</label>
                        <textarea name="summary" rows="6" maxlength="5000" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>{{ old('summary') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Reason for change</label>
                        <textarea name="change_reason" rows="3" maxlength="2000" placeholder="Regulatory directive, board resolution, risk finding..." class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">{{ old('change_reason') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Impact assessment</label>
                        <textarea name="impact_assessment" rows="3" maxlength="2000" placeholder="Effect on members, operations, capital and systems" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">{{ old('impact_assessment') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="pt-6 flex justify-end gap-3">
                <a href="{{ route('governance.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 rounded hover:bg-slate-200 dark:hover:bg-slate-700 transition">Cancel</a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-sacco rounded hover:bg-sacco-dark transition">Save Draft Policy</button>
            </div>
        </form>
    </div>
</div>
@endsection
