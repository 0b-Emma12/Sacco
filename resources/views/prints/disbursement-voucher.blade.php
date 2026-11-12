@extends('layouts.print')
@section('document_title', 'Loan Disbursement Voucher')
@section('content')
    <h1 class="doc-heading">Loan Disbursement Advice</h1>
    <p class="muted">Loan reference APP-{{ str_pad($loan->id, 4, '0', STR_PAD_LEFT) }} &nbsp;·&nbsp; Disbursed {{ optional($loan->disbursed_at)->format('d M Y') }}</p>

    <div class="two-col" style="margin-top: 10px;">
        <div>
            <h2 class="section">Borrower</h2>
            <table class="kv">
                <tr><td class="label">Name</td><td class="value">{{ $loan->member->first_name }} {{ $loan->member->last_name }}</td></tr>
                <tr><td class="label">Membership No.</td><td class="value">{{ $loan->member->membership_number }}</td></tr>
                <tr><td class="label">National ID</td><td class="value">{{ $loan->member->national_id ?: '—' }}</td></tr>
            </table>
        </div>
        <div>
            <h2 class="section">Loan terms</h2>
            <table class="kv">
                <tr><td class="label">Product</td><td class="value">{{ $loan->product->name ?? '—' }}</td></tr>
                <tr><td class="label">Term</td><td class="value">{{ $loan->term_months }} months</td></tr>
                <tr><td class="label">Interest</td><td class="value">{{ number_format((float) $loan->interest_rate, 2) }}% monthly ({{ $loan->interest_method === 'FLAT' ? 'flat' : 'reducing balance' }})</td></tr>
            </table>
        </div>
    </div>

    <h2 class="section">Amounts</h2>
    <table class="doc-table">
        <tbody>
            <tr><td>Gross principal approved</td><td class="right">UGX {{ number_format((float) $loan->principal_amount, 2) }}</td></tr>
            <tr><td>Less: processing fee</td><td class="right">({{ number_format($processingFee, 2) }})</td></tr>
            <tr><td>Less: insurance fee</td><td class="right">({{ number_format($insuranceFee, 2) }})</td></tr>
            <tr class="total"><td>Net amount disbursed</td><td class="right">UGX {{ number_format($net, 2) }}</td></tr>
        </tbody>
    </table>

    <div class="amount-box">
        <div class="amount">UGX {{ number_format($net, 2) }}</div>
        <div class="small right">Net amount disbursed</div>
    </div>
    <p class="amount-words">Amount in words: {{ \App\Services\NumberToWords::shillings($net) }}</p>

    <h2 class="section">Disbursement</h2>
    <table class="kv">
        <tr><td class="label">Method</td><td class="value">Credit to savings account {{ $account->account_number ?? '—' }}</td></tr>
        <tr><td class="label">Disbursed by</td><td class="value">{{ $loan->disbursed_by ?: '—' }}</td></tr>
        <tr><td class="label">First instalment due</td><td class="value">{{ $firstSchedule ? \Carbon\Carbon::parse($firstSchedule->due_date)->format('d M Y') : '—' }}</td></tr>
        <tr><td class="label">Monthly instalment</td><td class="value">UGX {{ number_format((float) $loan->monthly_installment, 2) }}</td></tr>
        <tr><td class="label">Total repayable</td><td class="value">UGX {{ number_format((float) $loan->total_repayable, 2) }}</td></tr>
        <tr><td class="label">Maturity date</td><td class="value">{{ $loan->maturity_date ? \Carbon\Carbon::parse($loan->maturity_date)->format('d M Y') : '—' }}</td></tr>
    </table>

    @if($loan->schedules->isNotEmpty())
        <h2 class="section">Repayment schedule (first five instalments)</h2>
        <table class="doc-table">
            <thead>
                <tr><th>#</th><th>Due date</th><th class="right">Principal</th><th class="right">Interest</th><th class="right">Total due</th></tr>
            </thead>
            <tbody>
                @foreach($loan->schedules->take(5) as $schedule)
                    <tr>
                        <td>{{ $schedule->installment_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($schedule->due_date)->format('d M Y') }}</td>
                        <td class="right">{{ number_format((float) $schedule->principal_due, 2) }}</td>
                        <td class="right">{{ number_format((float) $schedule->interest_due, 2) }}</td>
                        <td class="right">{{ number_format((float) $schedule->total_due, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @include('partials.print.signatures', ['signatures' => [
        ['name' => $loan->disbursed_by ?: '', 'title' => 'Disbursing officer'],
        ['name' => '', 'title' => 'Member acknowledgement'],
        ['name' => \App\Models\Setting::get('org_manager', ''), 'title' => 'Manager'],
    ]])
@endsection
