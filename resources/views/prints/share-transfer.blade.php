@extends('layouts.print')
@section('document_title', 'Share Transfer Certificate')
@section('content')
    @php
        $transferorName = trim($shareTransfer->transferor->first_name.' '.$shareTransfer->transferor->last_name);
        $transfereeName = trim($shareTransfer->transferee->first_name.' '.$shareTransfer->transferee->last_name);
        $effective = $shareTransfer->transfer_date ?: $shareTransfer->approved_at;
    @endphp
    <h1 class="doc-heading">Share Transfer Certificate</h1>
    <p class="muted">
        Certificate No. {{ $shareTransfer->certificate_reference ?: 'ST-'.str_pad($shareTransfer->id, 4, '0', STR_PAD_LEFT) }}
        &nbsp;·&nbsp; Effective {{ $effective ? \Carbon\Carbon::parse($effective)->format('d M Y') : '—' }}
    </p>

    <p style="margin-top:12px;">
        This certifies that <strong>{{ number_format((float) $shareTransfer->shares_count, 4) }} shares</strong> valued at
        <strong>UGX {{ number_format((float) $shareTransfer->share_value, 2) }}</strong> were transferred from
        <strong>{{ $transferorName }}</strong> (Membership No. {{ $shareTransfer->transferor->membership_number }}) to
        <strong>{{ $transfereeName }}</strong> (Membership No. {{ $shareTransfer->transferee->membership_number }})
        with effect from {{ $effective ? \Carbon\Carbon::parse($effective)->format('d M Y') : '—' }}.
    </p>

    <h2 class="section">Transfer details</h2>
    <table class="doc-table">
        <thead>
            <tr><th>Particulars</th><th class="right">Transferor</th><th class="right">Transferee</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>Shares held after transfer</td>
                <td class="right">{{ number_format((float) \App\Services\MemberCapitalService::calculate($shareTransfer->transferor)['share_count'], 4) }}</td>
                <td class="right">{{ number_format((float) \App\Services\MemberCapitalService::calculate($shareTransfer->transferee)['share_count'], 4) }}</td>
            </tr>
            <tr>
                <td>Share capital after transfer</td>
                <td class="right">UGX {{ number_format((float) $shareTransfer->transferor->share_capital, 2) }}</td>
                <td class="right">UGX {{ number_format((float) $shareTransfer->transferee->share_capital, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="kv">
        <tr><td class="label">Consideration paid</td><td class="value">{{ $shareTransfer->consideration !== null ? 'UGX '.number_format((float) $shareTransfer->consideration, 2) : 'Nil' }}</td></tr>
        <tr><td class="label">Approved by</td><td class="value">{{ $shareTransfer->approved_by ?: '—' }}{{ $shareTransfer->approved_at ? ' · '.$shareTransfer->approved_at->format('d M Y') : '' }}</td></tr>
    </table>

    @if($shareTransfer->reason)
        <h2 class="section">Reason for transfer</h2>
        <p>{{ $shareTransfer->reason }}</p>
    @endif

    <div class="notice">
        Upon registration of this transfer, the transferee becomes the registered holder of the shares and is entitled to
        all rights attaching to them, including dividends declared after the effective date. The transferor's liability in
        respect of the transferred shares ceases on that date.
    </div>

    <div class="seal">Official SACCO stamp</div>

    @include('partials.print.signatures', ['signatures' => [
        ['name' => $transferorName, 'title' => 'Transferor'],
        ['name' => $transfereeName, 'title' => 'Transferee'],
        ['name' => \App\Models\Setting::get('org_secretary', ''), 'title' => 'Authorised signatory'],
    ]])
@endsection
