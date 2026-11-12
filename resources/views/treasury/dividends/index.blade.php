@extends('layouts.app')
@section('title', 'Dividend Distribution')

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
        title="Dividend Distribution"
        subtitle="F06 — compute the annual surplus split, obtain board approval and post payouts.">
        <form action="{{ route('treasury.dividends.store') }}" method="POST" class="flex items-end gap-2 m-0">
            @csrf
            <div>
                <label class="block text-[11px] font-medium text-slate-500 mb-1">Financial year</label>
                <input type="number" name="financial_year" min="2000" max="{{ now()->year }}" value="{{ old('financial_year', now()->year - 1) }}" class="h-10 w-28 rounded-xl bg-slate-50 border border-slate-200 px-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100" required>
            </div>
            <button type="submit" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold shadow-sm inline-flex items-center gap-2 transition">
                <x-icon name="refresh-cw" class="w-4 h-4"/>
                Compute
            </button>
        </form>
    </x-page-header>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Distributions" :value="number_format($aggregates['count'])" meta="Financial years on record" icon="pie-chart" tone="purple"/>
        <x-stat-card label="Net surplus" :value="'UGX '.number_format($aggregates['netSurplus'], 2)" meta="Across all computed splits" icon="trending-up" tone="green"/>
        <x-stat-card label="Dividend pool" :value="'UGX '.number_format($aggregates['dividendPool'], 2)" meta="Allocated on share capital" icon="wallet" tone="blue"/>
        <x-stat-card label="Paid out" :value="'UGX '.number_format($aggregates['paid'], 2)" meta="Credited to member savings" icon="coins" tone="amber"/>
    </div>

    <x-panel title="Distribution register" subtitle="Annual surplus splits computed from the ledger." icon="pie-chart" tone="purple">
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Financial year</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Net surplus</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Dividend pool</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Savings interest pool</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Paid out</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($distributions as $distribution)
                    @php
                        $status = strtoupper((string) $distribution->status);
                        $badge = $status === 'COMPLETED'
                            ? 'bg-[#38CE3C]/15 text-[#38CE3C] border-[#38CE3C]/20'
                            : ($status === 'APPROVED'
                                ? 'bg-[#8E32E9]/15 text-[#8E32E9] border-[#8E32E9]/20'
                                : 'bg-[#FFDE73]/30 text-amber-700 border-[#FFDE73]/40 dark:text-amber-300');
                    @endphp
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">FY {{ $distribution->financial_year }}</div>
                            <div class="text-[11px] text-slate-400">#{{ $distribution->id }}</div>
                        </td>
                        <td class="px-5 py-4 text-right text-sm text-slate-700 dark:text-slate-300">{{ number_format($distribution->net_surplus, 2) }}</td>
                        <td class="px-5 py-4 text-right text-sm text-slate-700 dark:text-slate-300">{{ number_format($distribution->dividend_pool, 2) }}</td>
                        <td class="px-5 py-4 text-right text-sm text-slate-700 dark:text-slate-300">{{ number_format($distribution->savings_interest_pool, 2) }}</td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-slate-800 dark:text-slate-100">{{ number_format($totals[$distribution->id] ?? 0, 2) }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $badge }}">{{ ucfirst(strtolower($distribution->status)) }}</span>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('treasury.dividends.show', $distribution) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] text-xs font-semibold hover:bg-[#38CE3C]/20 transition">Open</a>
                            <a href="{{ route('prints.dividends.bulk', $distribution) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition dark:bg-slate-800 dark:text-slate-300">Statements</a>
                        </td>
                    </tr>
                    @empty
                        <x-empty-state colspan="7" icon="inbox" title="No surplus distribution has been computed yet"
                                       message="Compute a financial year above and the split will be drawn from the ledger.">
                            <span class="text-xs text-slate-400">Use the Compute form in the header.</span>
                        </x-empty-state>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($distributions->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $distributions->links() }}
            </div>
        @endif
    </x-panel>
</div>
@endsection
