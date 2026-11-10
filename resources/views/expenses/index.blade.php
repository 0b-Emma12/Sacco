@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-7xl">
    
    <!-- PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Management</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">Expenses</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('expenses.create') }}" class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-white bg-sacco border border-sacco rounded hover:bg-sacco-dark transition">
                <i data-lucide="plus" class="w-4 h-4"></i> Record Expense
            </a>
        </div>
    </div>

    <!-- DATA TABLE -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden dark:bg-slate-900 dark:border-slate-800">
        <div class="p-6 flex items-center justify-between border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">All Expenses</h3>
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-slate-400"></i>
                <input type="text" placeholder="Search description..." class="pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200">
            </div>
        </div>
        
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/50 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Expense ID</th>
                        <th class="px-6 py-4 font-semibold">Description</th>
                        <th class="px-6 py-4 font-semibold">Category</th>
                        <th class="px-6 py-4 font-semibold">Amount</th>
                        <th class="px-6 py-4 font-semibold">Date</th>
                        <th class="px-6 py-4 font-semibold">Posted By</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($expenses as $expense)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                        <td class="px-6 py-4 font-medium text-sacco hover:underline cursor-pointer">
                            <a href="{{ route('expenses.show', $expense->id) }}">
                                #{{ str_pad($expense->id, 4, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-slate-800 dark:text-slate-200">
                            {{ $expense->description }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $expense->category }}</td>
                        <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-200">
                            {{ number_format($expense->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                            {{ $expense->date ? $expense->date->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                            {{ $expense->created_by_name }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                {{ strtolower($expense->status) === 'approved' ? 'bg-emerald-100 text-emerald-700' : (strtolower($expense->status) === 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                {{ ucfirst($expense->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right flex justify-end gap-2">
                            <a href="{{ route('expenses.show', $expense->id) }}" class="text-slate-400 hover:text-sacco"><i data-lucide="eye" class="w-4 h-4"></i></a>
                            <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-rose-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-slate-500">No expenses recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
