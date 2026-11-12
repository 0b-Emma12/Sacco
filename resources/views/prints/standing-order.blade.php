@extends('layouts.print')
@section('document_title', 'Standing Order Mandate (S05)')
@section('content')
    @php
        $memberName = trim(($standingOrder->member->first_name ?? '').' '.($standingOrder->member->last_name ?? ''));
        $status = ucfirst(strtolower($standingOrder->status));
    @endphp
    <h1 class="doc-heading">Standing Order Mandate (S05)</h1>
    <p class="muted">Mandate No. {{ $standingOrder->mandate_reference }} &nbsp;·&nbsp; Status: {{ $status }}</p>

    <p style="margin-top:12px;">
        I, <strong>{{ $memberName }}</strong> (Membership No. {{ $standingOrder->member->membership_number ?? '' }}),
        authorise <strong>{{ $orgName }}</strong> to collect
        <strong>UGX {{ number_format((float) $standingOrder->amount, 2) }}</strong>
        <strong>{{ strtolower($standingOrder->frequencyLabel()) }}</strong> from
        {{ strtolower($standingOrder->sourceLabel()) }}
        @if($standingOrder->payer_details) through <strong>{{ $standingOrder->payer_details }}</strong>@endif
        and apply it to {{ strtolower($standingOrder->destinationLabel()) }}, commencing
        {{ $standingOrder->start_date ? $standingOrder->start_date->format('d M Y') : '—' }}@if($standingOrder->end_date) and ending {{ $standingOrder->end_date->format('d M Y') }}@endif.
    </p>

    <h2 class="section">Mandate particulars</h2>
    <table class="kv">
        <tr><td class="label">Destination</td><td class="value">{{ $standingOrder->destinationLabel() }}</td></tr>
        <tr><td class="label">Amount per cycle</td><td class="value">UGX {{ number_format((float) $standingOrder->amount, 2) }}</td></tr>
        <tr><td class="label">Frequency</td><td class="value">{{ $standingOrder->frequencyLabel() }}</td></tr>
        <tr><td class="label">Source of funds</td><td class="value">{{ $standingOrder->sourceLabel() }}</td></tr>
        <tr><td class="label">Payer reference</td><td class="value">{{ $standingOrder->payer_reference ?: '—' }}</td></tr>
        <tr><td class="label">Next collection date</td><td class="value">{{ $standingOrder->next_run_date ? $standingOrder->next_run_date->format('d M Y') : '—' }}</td></tr>
        <tr><td class="label">Runs completed</td><td class="value">{{ (int) $standingOrder->runs_completed }}</td></tr>
        <tr><td class="label">Requested by</td><td class="value">{{ $standingOrder->requested_by ?: '—' }}</td></tr>
        <tr><td class="label">Authorised by</td><td class="value">{{ $standingOrder->approved_by ?: '—' }}{{ $standingOrder->approved_at ? ' · '.$standingOrder->approved_at->format('d M Y') : '' }}</td></tr>
    </table>

    @if($standingOrder->member_instructions)
        <h2 class="section">Member instructions</h2>
        <p>{{ $standingOrder->member_instructions }}</p>
    @endif

    <p class="small muted" style="margin-top:16px;">
        This mandate remains in force until cancelled in writing by the member or by the society. The member may suspend
        the mandate at any time; collection resumes from the next due date after reinstatement. A copy of this authority
        is retained in the member's file.
    </p>

    <div class="seal">Official SACCO stamp</div>

    @include('partials.print.signatures', ['signatures' => [
        ['name' => $memberName, 'title' => 'Member signature'],
        ['name' => '', 'title' => 'Witness'],
        ['name' => $standingOrder->approved_by ?: \App\Models\Setting::get('org_manager', ''), 'title' => 'Authorised signatory'],
    ]])
@endsection
