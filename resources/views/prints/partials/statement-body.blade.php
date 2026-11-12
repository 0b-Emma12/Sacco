@php
    $memberName = trim(($member->salutation ?? '').' '.$member->first_name.' '.$member->last_name);
@endphp
<h1 class="doc-heading">Member Account Statement</h1>
<p class="muted">
    {{ $memberName }} &nbsp;·&nbsp; Membership No. {{ $member->membership_number }}
    &nbsp;·&nbsp; Period {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}
</p>

<div class="two-col">
    <div>
        <h2 class="section">Member particulars</h2>
        <table class="kv">
            <tr><td class="label">National ID (NIN)</td><td class="value">{{ $member->national_id ?: '—' }}</td></tr>
            <tr><td class="label">Phone</td><td class="value">{{ $member->phone_number ?: '—' }}</td></tr>
            <tr><td class="label">Date joined</td><td class="value">{{ optional($member->date_joined ?? $member->created_at)->format('d M Y') }}</td></tr>
        </table>
    </div>
    <div>
        <h2 class="section">Position at {{ $to->format('d M Y') }}</h2>
        <table class="kv">
            <tr><td class="label">Savings balance</td><td class="value">UGX {{ number_format($capital['savings_balance'], 2) }}</td></tr>
            <tr><td class="label">Share capital</td><td class="value">UGX {{ number_format($capital['share_balance'], 2) }}</td></tr>
            <tr><td class="label">Loan lien on savings</td><td class="value">UGX {{ number_format($capital['lien_amount'], 2) }}</td></tr>
            <tr><td class="label">Guarantee liability</td><td class="value">UGX {{ number_format($capital['guarantee_liability'], 2) }}</td></tr>
            <tr><td class="label">Available savings</td><td class="value">UGX {{ number_format($capital['available_savings'], 2) }}</td></tr>
        </table>
    </div>
</div>

<h2 class="section">Savings transactions</h2>
<table class="doc-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Reference</th>
            <th>Description</th>
            <th class="right">Debit (UGX)</th>
            <th class="right">Credit (UGX)</th>
            <th class="right">Balance (UGX)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $from->format('d M Y') }}</td>
            <td>—</td>
            <td class="muted">Opening balance brought forward</td>
            <td class="right">—</td>
            <td class="right">—</td>
            <td class="right">{{ number_format($opening, 2) }}</td>
        </tr>
        @forelse($rows as $row)
            <tr>
                <td>{{ $row['date']->format('d M Y') }}</td>
                <td>{{ $row['reference'] }}</td>
                <td>{{ $row['description'] }}</td>
                <td class="right">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}</td>
                <td class="right">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}</td>
                <td class="right">{{ number_format($row['balance'], 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="muted center">No transactions were recorded in this period.</td>
            </tr>
        @endforelse
        <tr class="total">
            <td colspan="3">Totals for the period</td>
            <td class="right">{{ number_format($totalDebit, 2) }}</td>
            <td class="right">{{ number_format($totalCredit, 2) }}</td>
            <td class="right">{{ number_format($closing, 2) }}</td>
        </tr>
    </tbody>
</table>
<p class="small muted">Closing savings balance carried forward at {{ $to->format('d M Y') }}: <strong>UGX {{ number_format($closing, 2) }}</strong></p>

<h2 class="section">Active loans</h2>
@if($activeLoans->isNotEmpty())
    <table class="doc-table">
        <thead>
            <tr><th>Loan</th><th>Disbursed</th><th>Status</th><th class="right">Outstanding (UGX)</th></tr>
        </thead>
        <tbody>
            @foreach($activeLoans as $entry)
                <tr>
                    <td>APP-{{ str_pad($entry['loan']->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ optional($entry['loan']->disbursed_at)->format('d M Y') ?: '—' }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $entry['loan']->status)) }}</td>
                    <td class="right">{{ number_format($entry['outstanding'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p class="small muted">No active loans.</p>
@endif

<p class="small muted" style="margin-top:18px;">
    Any discrepancy on this statement must be reported to the SACCO office in writing within 30 days of the statement date.
</p>
