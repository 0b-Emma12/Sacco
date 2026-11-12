@extends('layouts.print')
@section('document_title', 'Guarantor Release Letter')
@section('content')
    @php
        $loanRef = 'APP-'.str_pad($loan->id, 4, '0', STR_PAD_LEFT);
        $borrowerName = trim($loan->member->first_name.' '.$loan->member->last_name);
        $releasedAt = $lastPayment ? \Carbon\Carbon::parse($lastPayment->created_at) : now();
    @endphp
    <h1 class="doc-heading">Release of Guarantor</h1>
    <p class="muted">Loan reference {{ $loanRef }} &nbsp;·&nbsp; Released {{ $releasedAt->format('d M Y') }}</p>

    <p>
        To: <strong>All guarantors of loan {{ $loanRef }}</strong><br>
        Re: <strong>Release from guarantee obligations</strong>
    </p>

    <p>
        We refer to the loan facility granted to <strong>{{ $borrowerName }}</strong>
        (Membership No. {{ $loan->member->membership_number }}) on
        {{ optional($loan->disbursed_at)->format('d M Y') ?: '—' }} under the SACCO's loan policy.
    </p>

    <p>
        We confirm that the borrower has fully repaid the loan referenced above, including all principal, interest and
        charges. The loan account is closed with effect from {{ $releasedAt->format('d M Y') }}.
    </p>

    <p>
        Accordingly, all pledge obligations given in respect of this loan are hereby <strong>released and discharged</strong>.
        Any amount held by the SACCO as a guarantee retention, lien or pledge on the guarantors' savings or share capital
        for this loan is released with immediate effect.
    </p>

    <h2 class="section">Guarantors released</h2>
    @if($loan->guarantorPledges->isNotEmpty())
        <table class="doc-table">
            <thead>
                <tr><th>Guarantor</th><th>Membership No.</th><th class="right">Amount pledged (UGX)</th><th class="right">Enforced (UGX)</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach($loan->guarantorPledges as $pledge)
                    <tr>
                        <td>{{ $pledge->guarantor ? trim($pledge->guarantor->first_name.' '.$pledge->guarantor->last_name) : '—' }}</td>
                        <td>{{ $pledge->guarantor->membership_number ?? '—' }}</td>
                        <td class="right">{{ number_format((float) $pledge->pledged_amount, 2) }}</td>
                        <td class="right">{{ number_format((float) $pledge->amount_enforced, 2) }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $pledge->status)) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="small muted">No guarantor pledges were recorded against this loan.</p>
    @endif

    <p class="small muted">
        Guarantors are advised to retain this letter for their records. This release does not affect any other guarantee
        you may have given on any other facility.
    </p>

    @include('partials.print.signatures', ['signatures' => [
        ['name' => \App\Models\Setting::get('org_manager', ''), 'title' => 'Manager'],
        ['name' => \App\Models\Setting::get('org_secretary', ''), 'title' => 'Secretary'],
    ]])
@endsection
