@extends('layouts.print')
@section('document_title', 'Withholding Tax Certificate')
@section('content')
    @php
        $year = $dividend->distribution->financial_year ?? '—';
        $gross = (float) $dividend->total_payout;
        $wht = (float) $dividend->withholding_tax;
        $net = (float) $dividend->net_payout;
        $whtRate = (float) ($dividend->wht_rate ?? \App\Models\Setting::get('sys_wht_rate', 15));
        $memberName = trim($member->first_name.' '.$member->last_name);
    @endphp
    <h1 class="doc-heading">Withholding Tax Certificate</h1>
    <p class="muted">Financial year {{ $year }} &nbsp;·&nbsp; Issued {{ now()->format('d M Y') }}</p>

    <h2 class="section">Payer (SACCO)</h2>
    <table class="kv">
        <tr><td class="label">Name</td><td class="value">{{ $orgName }}</td></tr>
        <tr><td class="label">TIN</td><td class="value">{{ \App\Models\Setting::get('org_tin', '') ?: '—' }}</td></tr>
        <tr><td class="label">UMRA Reg. No.</td><td class="value">{{ \App\Models\Setting::get('org_umra_reg_no', '') ?: '—' }}</td></tr>
        <tr><td class="label">Address</td><td class="value">{{ $orgAddress ?: '—' }}</td></tr>
    </table>

    <h2 class="section">Payee (Member)</h2>
    <table class="kv">
        <tr><td class="label">Name</td><td class="value">{{ $memberName }}</td></tr>
        <tr><td class="label">Membership No.</td><td class="value">{{ $member->membership_number }}</td></tr>
        <tr><td class="label">National ID (NIN)</td><td class="value">{{ $member->national_id ?: '—' }}</td></tr>
        <tr><td class="label">TIN</td><td class="value">{{ $member->kra_pin ?: '—' }}</td></tr>
    </table>

    <h2 class="section">Withholding tax deducted</h2>
    <table class="doc-table">
        <thead>
            <tr><th>Description</th><th class="right">Rate</th><th class="right">Gross (UGX)</th><th class="right">Tax withheld (UGX)</th><th class="right">Net paid (UGX)</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>Dividend on share capital, FY {{ $year }}</td>
                <td class="right">{{ number_format($whtRate, 2) }}%</td>
                <td class="right">{{ number_format($gross, 2) }}</td>
                <td class="right">{{ number_format($wht, 2) }}</td>
                <td class="right">{{ number_format($net, 2) }}</td>
            </tr>
            <tr class="total">
                <td colspan="2">Total</td>
                <td class="right">{{ number_format($gross, 2) }}</td>
                <td class="right">{{ number_format($wht, 2) }}</td>
                <td class="right">{{ number_format($net, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="amount-box">
        <div class="amount">UGX {{ number_format($wht, 2) }}</div>
        <div class="small right">Tax withheld and remitted to URA</div>
    </div>
    <p class="amount-words">Amount in words: {{ \App\Services\NumberToWords::shillings($wht) }}</p>

    <div class="notice">
        This certificate is issued for the purposes of section 123 of the Income Tax Act (as amended) and confirms tax
        withheld at source from the dividend paid to the member named above. The tax has been remitted to the Uganda
        Revenue Authority under the SACCO's TIN shown on this certificate.
    </div>

    <div class="seal">Official SACCO stamp</div>

    @include('partials.print.signatures', ['signatures' => [
        ['name' => \App\Models\Setting::get('org_manager', ''), 'title' => 'Manager'],
        ['name' => \App\Models\Setting::get('org_treasurer', ''), 'title' => 'Treasurer'],
    ]])
@endsection
