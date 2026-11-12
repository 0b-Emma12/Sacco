@extends('layouts.app')
@section('title', 'Expense Details')

@section('content')
<div class="mx-auto w-full max-w-7xl flex flex-col gap-6">
    <x-page-header
        eyebrow="Expenses"
        title="Expense #{{ str_pad($expense->id, 4, '0', STR_PAD_LEFT) }}"
        subtitle="{{ $expense->description }}">
        <a href="{{ route('expenses.index') }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2 transition">
            <x-icon name="arrow-left" class="w-4 h-4"/> Back to expenses
        </a>
    </x-page-header>

    @if(session('success'))
        <div class="bg-[#38CE3C]/10 border border-[#38CE3C]/20 text-[#38CE3C] px-4 py-3 rounded-xl font-semibold text-sm" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-[#FF4D6B]/10 text-[#FF4D6B] border border-[#FF4D6B]/20 p-4 rounded-xl text-sm font-semibold">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $st = strtolower($expense->status);
        $badgeClass = $st === 'approved'
            ? 'bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20'
            : ($st === 'rejected'
                ? 'bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20'
                : 'bg-[#FFDE73]/30 text-amber-700 dark:text-amber-300 border border-amber-300/30');
        $statusTone = $st === 'approved' ? 'green' : ($st === 'rejected' ? 'coral' : 'amber');
    @endphp

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Amount" :value="'UGX '.number_format($expense->amount, 2)" meta="Requested expenditure" icon="coins" tone="green"/>
        <x-stat-card label="Status" :value="ucfirst($expense->status)" meta="Approval state" icon="check-circle" :tone="$statusTone"/>
        <x-stat-card label="Expense date" :value="$expense->date ? $expense->date->format('d M Y') : 'N/A'" meta="When the cost was incurred" icon="clock" tone="blue"/>
        <x-stat-card label="Posted by" :value="$expense->created_by_name" meta="Recorded in the register" icon="user" tone="purple"/>
    </div>

    <x-panel title="Expense particulars" subtitle="Amount, category and approval trail." icon="receipt">
        <div class="p-5 sm:p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">#{{ str_pad($expense->id, 4, '0', STR_PAD_LEFT) }}</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ $expense->description }}</p>
                </div>
                <div>
                    <span class="px-3 py-1.5 text-sm font-semibold rounded-xl {{ $badgeClass }}">
                        {{ ucfirst($expense->status) }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 border-t border-slate-100 dark:border-slate-800 pt-6">
                <div>
                    <div class="mb-4">
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Amount</span>
                        <span class="text-lg font-bold text-slate-800 dark:text-slate-200">UGX {{ number_format($expense->amount, 2) }}</span>
                    </div>
                    <div class="mb-4">
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Category</span>
                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $expense->category ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Date</span>
                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $expense->date ? $expense->date->format('F d, Y') : 'N/A' }}</span>
                    </div>
                </div>

                <div>
                    <div class="mb-4">
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Posted by</span>
                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $expense->created_by_name }}</span>
                    </div>
                    <div class="mb-4">
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Approved by</span>
                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $expense->approver ? $expense->approver->username : 'Pending Approval' }}</span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Created at</span>
                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $expense->created_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>

            @if($journalEntry)
            <div class="border-t border-slate-100 dark:border-slate-800 pt-6 mb-6">
                <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">Ledger posting</h3>
                <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-2 bg-slate-50 dark:bg-slate-800/60 text-xs">
                        <span class="font-mono font-semibold text-slate-600 dark:text-slate-300">{{ $journalEntry->reference }}</span>
                        <span class="text-slate-500">{{ $journalEntry->posted_at ? $journalEntry->posted_at->format('M d, Y H:i') : '' }}</span>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <th class="h-10 px-4 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Account</th>
                                <th class="h-10 px-4 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Debit (UGX)</th>
                                <th class="h-10 px-4 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Credit (UGX)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($journalEntry->lineItems as $line)
                            <tr>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">{{ $line->account_code }} — {{ $line->account_name }}</td>
                                <td class="px-4 py-3 text-right text-sm">{{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}</td>
                                <td class="px-4 py-3 text-right text-sm">{{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('accounting.show', $journalEntry->id) }}" class="no-print inline-block mt-3 text-xs font-semibold text-[#38CE3C] hover:underline">Open journal entry</a>
            </div>
            @endif

            <div class="border-t border-slate-100 dark:border-slate-800 pt-6 flex flex-wrap justify-end gap-3 no-print">
                @if($expense->status === 'pending')
                    @if(auth()->user()->canApproveLoans())
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
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition shadow-sm">Approve &amp; Post</button>
                    </form>
                    @else
                    <span class="text-xs text-slate-500">Awaiting approval by the Administrator or Manager.</span>
                    @endif
                @elseif($expense->status === 'approved')
                    <a href="{{ route('prints.expenses.voucher', $expense) }}" class="px-4 py-2 text-sm font-semibold text-white bg-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition shadow-sm">Print Payment Voucher</a>
                @endif
                <a href="{{ route('expenses.edit', $expense->id) }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Edit</a>
                @if($expense->status !== 'approved')
                <a href="#delete-expense" class="px-4 py-2 text-sm font-semibold text-[#FF4D6B] hover:text-[#FF4D6B]/80 transition">Delete Expense</a>
                @endif
            </div>
        </div>
    </x-panel>

    @if($expense->status === 'approved')
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 dark:bg-[#181824] dark:border-[#272738] px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Payment voucher</h2>
                <p class="text-xs text-slate-500 mt-1">Issued on the secure print register with a document ID, verification QR code and signature blocks.</p>
            </div>
            <a href="{{ route('prints.expenses.voucher', $expense) }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold shadow-sm inline-flex items-center gap-2 transition">
                <x-icon name="printer" class="w-4 h-4"/> Open payment voucher
            </a>
        </div>
    @endif
</div>

<div id="delete-expense" class="modal">
    <a href="#" class="modal-overlay" aria-label="Close"></a>
    <div class="modal-card bg-white dark:bg-[#181824] rounded-2xl shadow-xl p-6 border border-slate-200/80 dark:border-[#272738]">
        <div class="flex items-center gap-3 mb-4">
            <span class="h-10 w-10 rounded-xl bg-[#FF4D6B]/10 text-[#FF4D6B] flex items-center justify-center">
                <x-icon name="alert-triangle" class="w-5 h-5"/>
            </span>
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Delete expense?</h3>
                <p class="text-xs text-slate-500">This action cannot be undone.</p>
            </div>
        </div>
        <p class="text-xs text-slate-500 mb-6">
            #{{ str_pad($expense->id, 4, '0', STR_PAD_LEFT) }} — {{ $expense->description }} (UGX {{ number_format($expense->amount, 2) }})
        </p>
        <div class="flex justify-end gap-3">
            <a href="#" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">Cancel</a>
            <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#FF4D6B] rounded-xl hover:bg-[#e63e5c] transition">Delete Expense</button>
            </form>
        </div>
    </div>
</div>
@endsection
