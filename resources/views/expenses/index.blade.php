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
            <a href="{{ route('expenses.create') }}" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#38CE3C] border border-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition shadow-sm">
                <i data-lucide="plus" class="w-4 h-4"></i> Record Expense
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-[#38CE3C]/10 text-[#38CE3C] p-4 rounded-xl text-sm border border-[#38CE3C]/20 font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <!-- DATA TABLE -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden dark:bg-slate-900 dark:border-slate-800">
        <div class="p-6 flex items-center justify-between border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">All Expenses</h3>
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-slate-400"></i>
                <input type="text" placeholder="Search description..." class="pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200">
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
                        <td class="px-6 py-4 font-medium text-[#38CE3C] hover:underline cursor-pointer">
                            <a href="{{ route('expenses.show', $expense->id) }}">
                                #{{ str_pad($expense->id, 4, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-slate-800 dark:text-slate-200">
                            {{ $expense->description }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $expense->category }}</td>
                        <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-200">
                            UGX {{ number_format($expense->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                            {{ $expense->date ? $expense->date->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                            {{ $expense->created_by_name }}
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $st = strtolower($expense->status);
                                $badgeClass = $st === 'approved'
                                    ? 'bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20'
                                    : ($st === 'rejected'
                                        ? 'bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20'
                                        : 'bg-[#FFDE73]/30 text-amber-700 dark:text-amber-300 border border-amber-300/30');
                            @endphp
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-md {{ $badgeClass }}">
                                {{ ucfirst($expense->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right flex justify-end gap-2">
                            <a href="{{ route('expenses.show', $expense->id) }}" class="text-slate-400 hover:text-[#38CE3C] transition" title="View"><i data-lucide="eye" class="w-4 h-4"></i></a>
                            <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-[#FF4D6B] transition" title="Delete"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="h-14 w-14 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mb-3 dark:bg-slate-800/50">
                                    <i data-lucide="receipt" class="w-7 h-7"></i>
                                </div>
                                <h4 class="text-slate-700 dark:text-slate-200 font-bold mb-1">No expenses recorded yet</h4>
                                <p class="text-slate-500 text-xs">Start by recording an operating expense.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
