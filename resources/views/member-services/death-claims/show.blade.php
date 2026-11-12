@extends('layouts.app')
@section('title', 'Death Claim')

@section('content')
@php
    $badge = $claim->isPaid()
        ? 'bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20'
        : ($claim->isApproved()
            ? 'bg-sky-100 text-sky-700 border border-sky-200'
            : ($claim->status === 'REJECTED'
                ? 'bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20'
                : 'bg-[#FFDE73]/30 text-amber-700 border border-amber-300/30'));
@endphp

<div class="mx-auto w-full max-w-6xl flex flex-col gap-6">
    <x-page-header
        eyebrow="Member Services / ST01"
        title="Death Claim DC-{{ str_pad($claim->id, 4, '0', STR_PAD_LEFT) }}"
        subtitle="{{ $claim->member->first_name }} {{ $claim->member->last_name }} — {{ $claim->member->membership_number }}">
        <span class="px-3 py-1.5 text-sm font-semibold rounded-xl border {{ $badge }}">{{ ucfirst(strtolower($claim->status)) }}</span>
        <a href="{{ route('member-services.death-claims.index') }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2 no-print">
            <x-icon name="arrow-left" class="w-4 h-4"/> All claims
        </a>
    </x-page-header>

    @if (session('success'))
        <div class="p-4 text-sm text-[#38CE3C] font-semibold bg-[#38CE3C]/10 border border-[#38CE3C]/20 rounded-xl no-print" role="alert">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="p-4 text-sm text-[#FF4D6B] bg-[#FF4D6B]/10 border border-[#FF4D6B]/20 rounded-xl no-print" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Net payable" :value="'UGX '.number_format($claim->net_payable, 2)" meta="After loans and guarantees" icon="coins" tone="green"/>
        <x-stat-card label="Savings balance" :value="'UGX '.number_format($claim->savings_balance, 2)" meta="Account 2000" icon="wallet" tone="blue"/>
        <x-stat-card label="Share capital" :value="'UGX '.number_format($claim->share_balance, 2)" meta="Account 3000" icon="landmark" tone="purple"/>
        <x-stat-card label="Loan offset" :value="'UGX '.number_format($claim->loan_outstanding, 2)" meta="Recovered from the estate" icon="credit-card" tone="coral"/>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 flex flex-col gap-6">
            <x-panel title="Claim particulars" subtitle="Claimant, certificate and decision trail." icon="file-text" tone="purple">
                <div class="p-5 sm:p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5 text-sm">
                        <div><dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Date of death</dt><dd class="text-slate-700 dark:text-slate-300">{{ $claim->date_of_death ? $claim->date_of_death->format('d M Y') : '—' }}</dd></div>
                        <div><dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Death certificate</dt><dd class="text-slate-700 dark:text-slate-300">{{ $claim->death_certificate_number }}</dd></div>
                        <div><dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Claimant</dt><dd class="text-slate-700 dark:text-slate-300">{{ $claim->claimant_name }}</dd></div>
                        <div><dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Claimant NIN</dt><dd class="text-slate-700 dark:text-slate-300">{{ $claim->claimant_nin }}</dd></div>
                        <div><dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Relationship</dt><dd class="text-slate-700 dark:text-slate-300">{{ $claim->claimant_relationship }}</dd></div>
                        <div><dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Claimant phone</dt><dd class="text-slate-700 dark:text-slate-300">{{ $claim->claimant_phone }}</dd></div>
                        <div><dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Registered by</dt><dd class="text-slate-700 dark:text-slate-300">{{ $claim->requested_by }}</dd></div>
                        <div><dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Approved by</dt><dd class="text-slate-700 dark:text-slate-300">{{ $claim->approved_by ?: '—' }}{{ $claim->approved_at ? ' · '.$claim->approved_at->format('d M Y H:i') : '' }}</dd></div>
                    </dl>

                    @if ($claim->decision_notes)
                        <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Board / management notes</div>
                            <p class="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $claim->decision_notes }}</p>
                        </div>
                    @endif
                </div>
            </x-panel>

            <x-panel title="Documents on file" subtitle="Statutory evidence required before settlement." icon="check-circle" tone="green">
                <div class="p-5 sm:p-6">
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                        @foreach ($documents as $key => $label)
                            @php $received = in_array($key, $claim->checkedDocuments(), true); @endphp
                            <li class="flex items-center gap-2 {{ $received ? 'text-slate-700 dark:text-slate-300' : 'text-slate-400' }}">
                                <x-icon name="{{ $received ? 'check' : 'x' }}" class="w-4 h-4 {{ $received ? 'text-[#38CE3C]' : 'text-slate-300' }}"/>
                                {{ $label }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </x-panel>

            @if ($journalEntry)
            <x-panel title="Settlement posting" subtitle="{{ $journalEntry->reference }}" icon="receipt" tone="blue">
                <div class="p-5 sm:p-6">
                    <div class="w-full overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <th class="h-10 px-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Account</th>
                                    <th class="h-10 px-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Debit</th>
                                    <th class="h-10 px-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Credit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($journalEntry->lineItems as $line)
                                <tr>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $line->account_code }} — {{ $line->account_name }}</td>
                                    <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300">{{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}</td>
                                    <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300">{{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-panel>
            @endif
        </div>

        <div class="flex flex-col gap-6">
            <x-panel title="Entitlement" subtitle="How the claim value is built." icon="wallet" tone="green">
                <div class="p-5">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Savings (2000)</dt><dd class="font-semibold text-slate-800 dark:text-slate-100">{{ number_format($claim->savings_balance, 2) }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Share capital (3000)</dt><dd class="font-semibold text-slate-800 dark:text-slate-100">{{ number_format($claim->share_balance, 2) }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Loan offset (1200)</dt><dd class="font-semibold text-[#FF4D6B]">({{ number_format($claim->loan_outstanding, 2) }})</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Guarantee retention (2300)</dt><dd class="font-semibold text-[#FF4D6B]">({{ number_format($claim->guarantee_liability, 2) }})</dd></div>
                        <div class="flex justify-between gap-4 border-t border-slate-100 dark:border-slate-800 pt-2">
                            <dt class="font-semibold text-slate-700 dark:text-slate-200">Net payable to claimant</dt>
                            <dd class="font-bold text-[#38CE3C]">UGX {{ number_format($claim->net_payable, 2) }}</dd>
                        </div>
                    </dl>
                    @if ($claim->isPaid())
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-sm space-y-1">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Method</dt><dd class="text-slate-800 dark:text-slate-100">{{ str_replace('_', ' ', $claim->payment_method) }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Reference</dt><dd class="text-slate-800 dark:text-slate-100">{{ $claim->payment_reference ?: '—' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Paid on</dt><dd class="text-slate-800 dark:text-slate-100">{{ $claim->payment_date ? $claim->payment_date->format('d M Y') : '—' }}</dd></div>
                    </div>
                    @endif
                </div>
            </x-panel>

            @if ($claim->isPending() && Auth::user()->canApproveLoans())
            <x-panel title="Board decision" subtitle="Approve the claim for settlement." icon="check-circle" tone="purple">
                <div class="p-5 no-print">
                    <form action="{{ route('member-services.death-claims.approve', $claim) }}" method="POST" class="space-y-3">
                        @csrf
                        <textarea name="decision_notes" rows="3" maxlength="2000" placeholder="Resolution / board notes" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] dark:bg-slate-900 dark:text-slate-100"></textarea>
                        <button type="submit" class="w-full h-10 px-4 text-sm font-semibold text-white bg-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition">Approve Claim</button>
                    </form>
                </div>
            </x-panel>
            @endif

            @if ($claim->isApproved() && Auth::user()->canDisburseLoans())
            <x-panel title="Record settlement" subtitle="Pays the claimant and closes the member account." icon="send" tone="green">
                <div class="p-5 no-print">
                    <form action="{{ route('member-services.death-claims.settle', $claim) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Payment method</label>
                            <select name="payment_method" class="w-full h-10 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100" required>
                                <option value="CASH">Cash</option>
                                <option value="BANK_TRANSFER">Bank transfer</option>
                                <option value="MOBILE_MONEY">Mobile money</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Payment reference</label>
                            <input type="text" name="payment_reference" maxlength="80" class="w-full h-10 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Payment date</label>
                            <input type="date" name="payment_date" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" class="w-full h-10 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100" required>
                        </div>
                        <button type="submit" class="w-full h-10 px-4 text-sm font-semibold text-white bg-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition">Settle &amp; Close Account</button>
                        <p class="text-[11px] text-slate-400">Posting: Dr 2000 savings, Dr 3000 shares, Dr 5300 any shortfall / Cr 1200 loans, Cr 2300 guarantees retained, Cr 1000 cash paid.</p>
                    </form>
                </div>
            </x-panel>
            @endif

            @if ($claim->isPaid())
            <a href="{{ route('prints.death-claims.settlement', $claim) }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] text-white text-sm font-semibold hover:bg-[#2cb030] transition inline-flex items-center justify-center gap-2 no-print">
                <x-icon name="printer" class="w-4 h-4"/> Print Settlement Letter
            </a>
            @endif
        </div>
    </div>
</div>

@if ($claim->isPaid())
    <div class="mx-auto w-full max-w-6xl mt-6 bg-white rounded-2xl shadow-xs border border-slate-200/80 dark:bg-[#181824] dark:border-[#272738] px-5 py-5 sm:px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl bg-[#38CE3C]/10 text-[#38CE3C] flex items-center justify-center shrink-0">
                <x-icon name="receipt" class="w-4 h-4"/>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Settlement letter</h2>
                <p class="text-xs text-slate-500 mt-1">Issued on the secure print register with a document ID, verification QR code and signature blocks.</p>
            </div>
        </div>
        <a href="{{ route('prints.death-claims.settlement', $claim) }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold shadow-sm inline-flex items-center justify-center gap-2 transition">Open settlement letter</a>
    </div>
@endif
@endsection
