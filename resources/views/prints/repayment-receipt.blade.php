@extends('layouts.print')
@section('document_title', 'Loan Repayment Receipt')
@section('content')
    <h1 class="doc-heading">Loan Repayment Receipt</h1>
    <p class="muted">Loan APP-{{ str_pad($loan->id, 4, '0', STR_PAD_LEFT) }} &nbsp;·&nbsp; {{ optional($repayment->created_at)->format('d M Y H:i') }}</p>

    <div class="two-col" style="margin-top: 10px;">
        <div>
            <table class="kv">
                <tr><td class="label">Member</td><td class="value">{{ $loan->member->first_name }} {{ $loan->member->last_name }}</td></tr>
                <tr><td class="label">Membership No.</td><td class="value">{{ $loan->member->membership_number }}</td></tr>
            </table>
        </div>
        <div>
            <table class="kv">
                <tr><td class="label">Instalment No.</td><td class="value">{{ $repayment->installment_number ?: '—' }}</td></tr>
                <tr><td class="label">Recorded by</td><td class="value">{{ $repayment->recorded_by ?: '—' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="amount-box">
        <div class="amount">UGX {{ number_format((float) $repayment->amount_paid, 2) }}</div>
        <div class="small right">Amount received</div>
    </div>
    <p class="amount-words">Amount in words: {{ \App\Services\NumberToWords::shillings((float) $repayment->amount_paid) }}</p>

    <h2 class="section">Application of payment</h2>
    <table class="doc-table">
        <tbody>
            <tr><td>Principal portion</td><td class="right">UGX {{ number_format((float) $repayment->principal_paid, 2) }}</td></tr>
            <tr><td>Interest portion</td><td class="right">UGX {{ number_format((float) $repayment->interest_paid, 2) }}</td></tr>
            <tr class="total"><td>Total applied</td><td class="right">UGX {{ number_format((float) $repayment->amount_paid, 2) }}</td></tr>
        </tbody>
    </table>

    <table class="kv">
        <tr><td class="label">Outstanding balance after payment</td><td class="value">UGX {{ number_format((float) $repayment->balance_after, 2) }}</td></tr>
        <tr><td class="label">Narration</td><td class="value">{{ $repayment->narration ?: '—' }}</td></tr>
    </table>

    @include('partials.print.signatures', ['signatures' => [
        ['name' => $repayment->recorded_by ?: '', 'title' => 'Cashier / Teller'],
        ['name' => '', 'title' => 'Member signature'],
    ]])
@endsection
