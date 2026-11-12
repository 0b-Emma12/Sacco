@extends('layouts.app')
@section('title', 'Edit Expense')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-3xl">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Expenses</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">Edit Expense #{{ str_pad($expense->id, 4, '0', STR_PAD_LEFT) }}</h1>
        </div>
        <a href="{{ route('expenses.show', $expense->id) }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Back to expense</a>
    </div>

    @if ($errors->any())
        <div class="bg-[#FF4D6B]/10 text-[#FF4D6B] border border-[#FF4D6B]/20 p-4 rounded-xl text-sm font-semibold">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 p-6 md:p-8">
        @if ($expense->status === 'approved')
            <p class="text-sm text-slate-600 dark:text-slate-300">This expense is approved and posted to the ledger, so it can no longer be edited. Reverse the journal entry from the General Ledger if a correction is required.</p>
        @else
            <form action="{{ route('expenses.update', $expense->id) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <input type="text" name="description" value="{{ old('description', $expense->description) }}" maxlength="255" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Amount (UGX)</label>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $expense->amount) }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Category</label>
                        <input type="text" name="category" value="{{ old('category', $expense->category) }}" maxlength="255" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('expenses.show', $expense->id) }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Cancel</a>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition shadow-sm">Save Changes</button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
