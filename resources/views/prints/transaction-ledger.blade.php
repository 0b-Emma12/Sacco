@extends('layouts.print')
@section('document_title', 'Transaction Ledger Extract')
@section('content')
    @php
        $labels = [
            'deposit' => 'Deposits',
            'withdrawal' => 'Withdrawals',
            'share_transfer' => 'Share transfers',
            'loan_disbursement' => 'Disbursements',
            'loan_repayment' => 'Repayments',
            'dividend' => 'Dividends',
            'fee' => 'Fees',
        ];
    @endphp

    <h1 class="doc-heading">Transaction Ledger Extract</h1>
    <p class="muted">
        @if($filters['search'] !== '')
            Search: &ldquo;{{ $filters['search'] }}&rdquo; &nbsp;·&nbsp;
        @endif
        @if($filters['type'] !== '')
            Type: {{ $labels[$filters['type']] ?? ucfirst(str_replace('_', ' ', $filters['type'])) }} &nbsp;·&nbsp;
        @endif
        {{ number_format($totals['count']) }} {{ \Illuminate\Support\Str::plural('posting', $totals['count']) }} &nbsp;·&nbsp;
        Extracted {{ now()->format('d M Y H:i') }}
    </p>

    <table class="kv" style="margin-top:10px;">
        <tr><td class="label">Total inflow</td><td class="value">UGX {{ number_format($totals['inflow'], 2) }}</td></tr>
        <tr><td class="label">Total outflow</td><td class="value">UGX {{ number_format($totals['outflow'], 2) }}</td></tr>
        <tr><td class="label">Net movement</td><td class="value">UGX {{ number_format($totals['net'], 2) }}</td></tr>
    </table>

    <table class="doc-table">
        <thead>
            <tr>
                <th style="width:11%">Posted</th>
                <th style="width:13%">Reference</th>
                <th>Account / member</th>
                <th style="width:11%">Type</th>
                <th class="right" style="width:14%">Amount (UGX)</th>
                <th>Narration</th>
                <th style="width:12%">Served by</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                @php
                    $member = optional($transaction->account)->member;
                    $isCredit = in_array($transaction->transaction_type, ['deposit', 'dividend', 'loan_disbursement'], true);
                @endphp
                <tr>
                    <td>{{ $transaction->created_at->format('d M Y') }}<br><span class="muted small">{{ $transaction->created_at->format('H:i') }}</span></td>
                    <td>
                        {{ $transaction->reference }}
                        @if($transaction->is_reversal)
                            <br><span class="small" style="color:#b91c1c;">Reversal</span>
                        @endif
                    </td>
                    <td>
                        {{ $transaction->account_number }}
                        @if($member)
                            <br><span class="muted small">{{ $member->first_name }} {{ $member->last_name }}</span>
                        @endif
                    </td>
                    <td>{{ $labels[$transaction->transaction_type] ?? ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}</td>
                    <td class="right">{{ $isCredit ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($transaction->narration ?: '—', 70) }}</td>
                    <td>{{ $transaction->performed_by_username ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center muted" style="padding:18px;">No postings match the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if($transactions->isNotEmpty())
            <tfoot>
                <tr class="total">
                    <td colspan="4">Net movement &mdash; {{ number_format($totals['count']) }} {{ \Illuminate\Support\Str::plural('posting', $totals['count']) }}</td>
                    <td class="right">{{ number_format($totals['net'], 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <p class="small muted">
        This extract lists the postings that matched the filters shown above at the time it was generated. It is for
        internal reconciliation and audit use.
    </p>
@endsection
