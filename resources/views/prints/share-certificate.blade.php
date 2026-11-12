@extends('layouts.print')
@section('document_title', 'Official Share Certificate')
@section('content')
    <div class="center" style="margin-top: 8px;">
        <h1 class="doc-heading" style="font-size: 20px; letter-spacing: 1px;">CERTIFICATE OF SHAREHOLDING</h1>
        <p class="muted">Certificate No. <span class="strong">{{ $certificateNumber }}</span></p>
    </div>

    <p style="margin-top: 18px;">This is to certify that</p>
    <p class="center strong" style="font-size: 17px; margin: 10px 0;">{{ $member->salutation }} {{ $member->first_name }} {{ $member->last_name }}</p>
    <p class="center small">National ID (NIN): {{ $member->national_id ?: '—' }} &nbsp;·&nbsp; Membership No: {{ $member->membership_number }}</p>

    <p style="margin-top: 16px;">is the registered holder of</p>

    <div class="amount-box">
        <div>
            <div class="amount">{{ number_format($capital['share_count'], 4) }} shares</div>
            <div class="small muted">at UGX {{ number_format($sharePrice, 2) }} per share</div>
        </div>
        <div class="right">
            <div class="amount">UGX {{ number_format($capital['share_balance'], 2) }}</div>
            <div class="small muted">Total share value</div>
        </div>
    </div>

    <p class="amount-words">Amount in words: {{ \App\Services\NumberToWords::shillings((float) $capital['share_balance']) }}</p>

    <p>in <strong>{{ $orgName }}</strong>{{ $orgUmraRegNo ? ' (UMRA Reg. No. '.$orgUmraRegNo.')' : '' }}, subject to the by-laws of the society. These shares are non-withdrawable while the member remains active.</p>

    <table class="kv" style="margin-top: 14px;">
        <tr><td class="label">Date of issue</td><td class="value">{{ now()->format('d M Y') }}</td></tr>
        <tr><td class="label">Member since</td><td class="value">{{ optional($member->created_at)->format('d M Y') }}</td></tr>
    </table>

    @include('partials.print.signatures', ['signatures' => [
        ['name' => \App\Models\Setting::get('org_chairperson', ''), 'title' => 'Chairperson'],
        ['name' => \App\Models\Setting::get('org_secretary', ''), 'title' => 'Secretary'],
        ['name' => \App\Models\Setting::get('org_treasurer', ''), 'title' => 'Treasurer'],
    ]])

    <div class="seal">Official SACCO seal</div>
@endsection
