@extends('layouts.print')
@section('document_title', 'Loan Clearance Certificate')
@section('content')
    @php
        $loanRef = 'APP-'.str_pad($loan->id, 4, '0', STR_PAD_LEFT);
        $memberName = trim($loan->member->first_name.' '.$loan->member->last_name);
        $totalPaid = (float) $loan->repayments->sum('amount_paid');
    @endphp
    <h1 class="doc-heading">Certificate of Loan Clearance</h1>
    <p class="muted">Loan reference {{ $loanRef }} &nbsp;·&nbsp; Issued {{ now()->format('d M Y') }}</p>

    <div class="two-col">
        <div>
            <h2 class="section">Borrower</h2>
            <table class="kv">
                <tr><td class="label">Name</td><td class="value">{{ $memberName }}</td></tr>
                <tr><td class="label">Membership No.</td><td class="value">{{ $loan->member->membership_number }}</td></tr>
                <tr><td class="label">National ID (NIN)</td><td class="value">{{ $loan->member->national_id ?: '—' }}</td></tr>
            </table>
        </div>
        <div>
            <h2 class="section">Loan particulars</h2>
            <table class="kv">
                <tr><td class="label">Product</td><td class="value">{{ $loan->product->name ?? '—' }}</td></tr>
                <tr><td class="label">Principal</td><td class="value">UGX {{ number_format((float) $loan->principal_amount, 2) }}</td></tr>
                <tr><td class="label">Disbursed</td><td class="value">{{ optional($loan->disbursed_at)->format('d M Y') ?: '—' }}</td></tr>
                <tr><td class="label">Maturity</td><td class="value">{{ $loan->maturity_date ? \Carbon\Carbon::parse($loan->maturity_date)->format('d M Y') : '—' }}</td></tr>
            </table>
        </div>
    </div>

    <h2 class="section">Settlement</h2>
    <table class="doc-table">
        <tbody>
            <tr><td>Total amount repayable</td><td class="right">UGX {{ number_format((float) $loan->total_repayable, 2) }}</td></tr>
            <tr><td>Total amount paid by borrower</td><td class="right">UGX {{ number_format($totalPaid, 2) }}</td></tr>
            <tr><td>Final payment date</td><td class="right">{{ $lastPayment ? \Carbon\Carbon::parse($lastPayment->created_at)->format('d M Y') : '—' }}</td></tr>
            <tr class="total"><td>Outstanding balance</td><td class="right">UGX 0.00</td></tr>
        </tbody>
    </table>

    <div class="notice">
        This is to certify that the borrower named above has fully repaid the loan referenced {{ $loanRef }} together with
        all interest and charges due. The loan account is closed and the borrower has no further liability in respect of
        this facility. Any security, lien or pledge held against this loan has been released.
    </div>

    <div class="seal">Official SACCO stamp</div>

    @include('partials.print.signatures', ['signatures' => [
        ['name' => \App\Models\Setting::get('org_manager', ''), 'title' => 'Manager'],
        ['name' => \App\Models\Setting::get('org_treasurer', ''), 'title' => 'Treasurer'],
    ]])
@endsection
