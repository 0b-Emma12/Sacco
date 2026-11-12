@php
    $year = $dividend->distribution->financial_year ?? '—';
    $gross = (float) $dividend->total_payout;
    $wht = (float) $dividend->withholding_tax;
    $net = (float) $dividend->net_payout;
    $whtRate = (float) ($dividend->wht_rate ?? \App\Models\Setting::get('sys_wht_rate', 15));
@endphp
<h1 class="doc-heading">Dividend &amp; Interest Statement</h1>
<p class="muted">Financial year {{ $year }} &nbsp;·&nbsp; Membership No. {{ $member->membership_number }}</p>

<div class="two-col">
    <div>
        <h2 class="section">Member</h2>
        <table class="kv">
            <tr><td class="label">Name</td><td class="value">{{ $member->first_name }} {{ $member->last_name }}</td></tr>
            <tr><td class="label">National ID (NIN)</td><td class="value">{{ $member->national_id ?: '—' }}</td></tr>
            <tr><td class="label">Phone</td><td class="value">{{ $member->phone_number ?: '—' }}</td></tr>
        </table>
    </div>
    <div>
        <h2 class="section">Declared rates</h2>
        <table class="kv">
            <tr><td class="label">Dividend on shares</td><td class="value">{{ number_format((float) ($dividend->distribution->dividend_on_shares_pct ?? 0), 2) }}%</td></tr>
            <tr><td class="label">Interest on savings</td><td class="value">{{ number_format((float) ($dividend->distribution->interest_on_savings_pct ?? 0), 2) }}%</td></tr>
            <tr><td class="label">Withholding tax rate</td><td class="value">{{ number_format($whtRate, 2) }}%</td></tr>
        </table>
    </div>
</div>

<h2 class="section">Entitlement</h2>
<table class="doc-table">
    <thead>
        <tr><th>Item</th><th class="right">Average balance (UGX)</th><th class="right">Rate</th><th class="right">Amount (UGX)</th></tr>
    </thead>
    <tbody>
        <tr>
            <td>Dividend on share capital</td>
            <td class="right">{{ number_format((float) $dividend->avg_share_balance, 2) }}</td>
            <td class="right">{{ number_format((float) ($dividend->distribution->dividend_on_shares_pct ?? 0), 2) }}%</td>
            <td class="right">{{ number_format((float) $dividend->dividend_earned, 2) }}</td>
        </tr>
        <tr>
            <td>Interest on savings</td>
            <td class="right">{{ number_format((float) $dividend->avg_savings_balance, 2) }}</td>
            <td class="right">{{ number_format((float) ($dividend->distribution->interest_on_savings_pct ?? 0), 2) }}%</td>
            <td class="right">{{ number_format((float) $dividend->interest_earned, 2) }}</td>
        </tr>
        <tr class="total">
            <td colspan="3">Gross entitlement</td>
            <td class="right">{{ number_format($gross, 2) }}</td>
        </tr>
        <tr>
            <td colspan="3">Less: withholding tax ({{ number_format($whtRate, 2) }}% on dividend)</td>
            <td class="right">({{ number_format($wht, 2) }})</td>
        </tr>
        <tr class="total">
            <td colspan="3">Net payout</td>
            <td class="right">{{ number_format($net, 2) }}</td>
        </tr>
    </tbody>
</table>

<div class="amount-box">
    <div class="amount">UGX {{ number_format($net, 2) }}</div>
    <div class="small right">Net amount payable</div>
</div>
<p class="amount-words">Amount in words: {{ \App\Services\NumberToWords::shillings($net) }}</p>

<h2 class="section">Payment</h2>
<table class="kv">
    <tr><td class="label">Method</td><td class="value">{{ $dividend->payout_method ? ucwords(str_replace('_', ' ', $dividend->payout_method)) : 'Credit to savings account' }}</td></tr>
    <tr><td class="label">Status</td><td class="value">{{ ucfirst($dividend->payout_status ?? 'pending') }}</td></tr>
    <tr><td class="label">Payment date</td><td class="value">{{ $dividend->payout_date ? \Carbon\Carbon::parse($dividend->payout_date)->format('d M Y') : '—' }}</td></tr>
    <tr><td class="label">Payment reference</td><td class="value">{{ $dividend->payout_reference ?: '—' }}</td></tr>
</table>

<p class="small muted" style="margin-top:16px;">
    The withholding tax shown above is deducted from the dividend on shares at the statutory rate and remitted to the
    Uganda Revenue Authority. Interest on savings is not subject to withholding tax. A separate WHT certificate is
    available for this deduction.
</p>
