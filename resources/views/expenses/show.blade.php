@extends('layouts.app')
@section('title', 'Expense Details')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-4xl">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Expenses</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">Expense Details</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('expenses.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition">← Back to Expenses</a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-[#38CE3C]/10 border border-[#38CE3C]/20 text-[#38CE3C] px-4 py-3 rounded-xl font-semibold text-sm" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 overflow-hidden">
        <div class="p-6 md:p-8">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">#{{ str_pad($expense->id, 4, '0', STR_PAD_LEFT) }}</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ $expense->description }}</p>
                </div>
                <div>
                    @php
                        $st = strtolower($expense->status);
                        $badgeClass = $st === 'approved'
                            ? 'bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20'
                            : ($st === 'rejected'
                                ? 'bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20'
                                : 'bg-[#FFDE73]/30 text-amber-700 dark:text-amber-300 border border-amber-300/30');
                    @endphp
                    <span class="px-3 py-1.5 text-sm font-semibold rounded-xl {{ $badgeClass }}">
                        {{ ucfirst($expense->status) }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 border-t border-slate-100 dark:border-slate-800 pt-6">
                <div>
                    <div class="mb-4">
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Amount</span>
                        <span class="text-lg font-bold text-slate-800 dark:text-slate-200">UGX {{ number_format($expense->amount, 2) }}</span>
                    </div>
                    <div class="mb-4">
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Category</span>
                        <span class="text-slate-700 dark:text-slate-300">{{ $expense->category ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Date</span>
                        <span class="text-slate-700 dark:text-slate-300">{{ $expense->date ? $expense->date->format('F d, Y') : 'N/A' }}</span>
                    </div>
                </div>

                <div>
                    <div class="mb-4">
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Posted By</span>
                        <span class="text-slate-700 dark:text-slate-300">{{ $expense->created_by_name }}</span>
                    </div>
                    <div class="mb-4">
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Approved By</span>
                        <span class="text-slate-700 dark:text-slate-300">{{ $expense->approver ? $expense->approver->username : 'Pending Approval' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Created At</span>
                        <span class="text-slate-700 dark:text-slate-300">{{ $expense->created_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-800 pt-6 flex flex-wrap justify-end gap-3">
                @if($expense->status === 'pending')
                <form action="{{ route('expenses.update', $expense->id) }}" method="POST" class="inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="description" value="{{ $expense->description }}">
                    <input type="hidden" name="amount" value="{{ $expense->amount }}">
                    <input type="hidden" name="category" value="{{ $expense->category }}">
                    <input type="hidden" name="status" value="rejected">
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-[#FF4D6B] bg-[#FF4D6B]/10 border border-[#FF4D6B]/20 rounded-xl hover:bg-[#FF4D6B]/20 transition">Reject</button>
                </form>

                <form action="{{ route('expenses.update', $expense->id) }}" method="POST" class="inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="description" value="{{ $expense->description }}">
                    <input type="hidden" name="amount" value="{{ $expense->amount }}">
                    <input type="hidden" name="category" value="{{ $expense->category }}">
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition shadow-sm">Approve</button>
                </form>
                @endif
                <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-[#FF4D6B] hover:text-[#FF4D6B]/80 transition">Delete Expense</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
