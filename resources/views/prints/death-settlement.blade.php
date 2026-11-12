@extends('layouts.print')
@section('document_title', 'Death Benefit Settlement Letter')
@section('content')
    @php
        $memberName = trim($claim->member->first_name.' '.$claim->member->last_name);
    @endphp
    <h1 class="doc-heading">Death Benefit Settlement Letter</h1>
    <p class="muted">Reference DC-{{ str_pad($claim->id, 4, '0', STR_PAD_LEFT) }} &nbsp;·&nbsp; {{ $claim->payment_date ? $claim->payment_date->format('d M Y') : '' }}</p>

    <p style="margin-top:12px;">
        To: <strong>{{ $claim->claimant_name }}</strong> ({{ $claim->claimant_relationship }}), NIN {{ $claim->claimant_nin }}<br>
        @if($claim->claimant_phone)Phone: {{ $claim->claimant_phone }}@endif
    </p>

    <p>
        Following the death of member <strong>{{ $memberName }}</strong>
        (Membership No. {{ $claim->member->membership_number }}) on
        {{ $claim->date_of_death ? $claim->date_of_death->format('d M Y') : '' }}, and upon verification of death
        certificate {{ $claim->death_certificate_number }}, the society has settled the member's account as follows:
    </p>

    <table class="doc-table">
        <tbody>
            <tr><td>Savings balance</td><td class="right">UGX {{ number_format((float) $claim->savings_balance, 2) }}</td></tr>
            <tr><td>Share capital</td><td class="right">UGX {{ number_format((float) $claim->share_balance, 2) }}</td></tr>
            <tr><td>Less: loan balance</td><td class="right">({{ number_format((float) $claim->loan_outstanding, 2) }})</td></tr>
            <tr><td>Less: guarantees retained</td><td class="right">({{ number_format((float) $claim->guarantee_liability, 2) }})</td></tr>
            <tr class="total"><td>Net amount paid</td><td class="right">UGX {{ number_format((float) $claim->net_payable, 2) }}</td></tr>
        </tbody>
    </table>

    <div class="amount-box">
        <div class="amount">UGX {{ number_format((float) $claim->net_payable, 2) }}</div>
        <div class="small right">Net amount paid to claimant</div>
    </div>
    <p class="amount-words">Amount in words: {{ \App\Services\NumberToWords::shillings((float) $claim->net_payable) }}</p>

    <p class="small muted">
        Paid by {{ ucwords(str_replace('_', ' ', (string) $claim->payment_method)) }}{{ $claim->payment_reference ? ' under reference '.$claim->payment_reference : '' }}.
        Guarantee retentions are held until the guaranteed loans are settled, after which any balance will be released
        to the estate or lawful claimant. This settlement is final and closes the member's account.
    </p>

    <div class="seal">Official SACCO stamp</div>

    @include('partials.print.signatures', ['signatures' => [
        ['name' => $claim->approved_by ?: \App\Models\Setting::get('org_manager', ''), 'title' => 'Approved by'],
        ['name' => $claim->claimant_name, 'title' => 'Claimant signature'],
    ]])
@endsection
