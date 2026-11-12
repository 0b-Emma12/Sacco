@extends('layouts.app')
@section('title', 'Bank Reconciliation')

@section('content')
<div class="mx-auto w-full max-w-7xl flex flex-col gap-6">
    @if (session('success'))
        <div class="p-4 text-sm text-[#38CE3C] font-semibold rounded-xl bg-[#38CE3C]/10 border border-[#38CE3C]/20" role="alert">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="p-4 text-sm text-[#FF4D6B] rounded-xl bg-[#FF4D6B]/10 border border-[#FF4D6B]/20" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <x-page-header
        eyebrow="Treasury"
        title="Bank Reconciliation"
        subtitle="F04 — reconcile bank statements with the general ledger.">
        <a href="{{ route('treasury.bank-reconciliations.create') }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold shadow-sm inline-flex items-center gap-2 transition">
            <x-icon name="plus-circle" class="w-4 h-4"/>
            New Reconciliation
        </a>
    </x-page-header>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Ledger cash at bank (1000)" :value="'UGX '.number_format($ledgerCash, 2)" meta="Book balance in the ledger" icon="landmark" tone="green"/>
        <x-stat-card label="Reconciliations" :value="number_format($counts['total'])" meta="Statements on record" icon="file-text" tone="purple"/>
        <x-stat-card label="Drafts" :value="number_format($counts['draft'])" meta="Awaiting review" icon="clock" tone="amber"/>
        <x-stat-card label="Reconciled" :value="number_format($counts['reconciled'])" meta="Locked after review" icon="check-circle" tone="blue"/>
    </div>

    <x-panel title="Reconciliation register" subtitle="Bank statements matched against the ledger, newest period first." icon="landmark" tone="blue">
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap">
                <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Bank / Account</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Period</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Adjusted bank</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Adjusted book</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Difference</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($reconciliations as $reconciliation)
                    @php
                        $recBadge = $reconciliation->isReconciled()
                            ? 'bg-[#38CE3C]/15 text-[#38CE3C] border-[#38CE3C]/20'
                            : 'bg-[#FFDE73]/30 text-amber-700 border-[#FFDE73]/40 dark:text-amber-300';
                    @endphp
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $reconciliation->bank_name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $reconciliation->account_number }}</div>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $reconciliation->period_from->format('d M Y') }} – {{ $reconciliation->period_to->format('d M Y') }}</td>
                        <td class="px-5 py-4 text-right text-sm text-slate-700 dark:text-slate-300">{{ number_format($reconciliation->adjusted_bank_balance, 2) }}</td>
                        <td class="px-5 py-4 text-right text-sm text-slate-700 dark:text-slate-300">{{ number_format($reconciliation->adjusted_book_balance, 2) }}</td>
                        <td class="px-5 py-4 text-right text-sm font-semibold {{ $reconciliation->isBalanced() ? 'text-[#38CE3C]' : 'text-[#FF4D6B]' }}">{{ number_format($reconciliation->difference, 2) }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $recBadge }}">{{ ucfirst(strtolower($reconciliation->status)) }}</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('treasury.bank-reconciliations.show', $reconciliation) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] text-xs font-semibold hover:bg-[#38CE3C]/20 transition">Open</a>
                        </td>
                    </tr>
                    @empty
                        <x-empty-state colspan="7" icon="inbox" title="No bank reconciliations recorded yet"
                                       message="Prepare the first statement reconciliation to compare bank and book balances.">
                            <a href="{{ route('treasury.bank-reconciliations.create') }}" class="h-9 px-4 rounded-lg bg-[#38CE3C] text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-[#2cb030] transition">
                                <x-icon name="plus" class="w-3.5 h-3.5"/> New Reconciliation
                            </a>
                        </x-empty-state>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reconciliations->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $reconciliations->links() }}
            </div>
        @endif
    </x-panel>
</div>
@endsection
