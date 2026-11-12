@extends('layouts.print')
@section('document_title', 'Payment Voucher')
@section('content')
    @php
        $bankName = \App\Models\Setting::get('org_bank_name', '');
        $bankAccount = \App\Models\Setting::get('org_bank_account', '');
    @endphp
    <h1 class="doc-heading">Payment Voucher</h1>
    <p class="muted">Voucher No. PV-{{ str_pad($expense->id, 5, '0', STR_PAD_LEFT) }} &nbsp;·&nbsp; {{ $expense->date ? $expense->date->format('d M Y') : '' }}</p>

    <table class="kv" style="margin-top:12px;">
        <tr><td class="label">Payee / description</td><td class="value">{{ $expense->description }}</td></tr>
        <tr><td class="label">Expense category</td><td class="value">{{ $expense->category ?: '—' }}</td></tr>
        <tr><td class="label">Payment method</td><td class="value">
            @if($bankName || $bankAccount)
                Bank — {{ $bankName }}{{ $bankAccount ? ' ('.$bankAccount.')' : '' }}
            @else
                Cash
            @endif
        </td></tr>
        <tr><td class="label">Prepared by</td><td class="value">{{ $expense->created_by_name }}</td></tr>
        <tr><td class="label">Approved by</td><td class="value">{{ $expense->approver ? $expense->approver->username : '—' }}</td></tr>
    </table>

    <div class="amount-box">
        <div class="amount">UGX {{ number_format((float) $expense->amount, 2) }}</div>
        <div class="small right">Amount payable</div>
    </div>
    <p class="amount-words">Amount in words: {{ \App\Services\NumberToWords::shillings((float) $expense->amount) }}</p>

    <p class="small muted">
        This voucher authorises payment of the amount stated above. The expenditure has been approved in accordance with
        the SACCO's financial policy and is to be posted to the operational expense account in the general ledger.
    </p>

    <div class="seal">Official SACCO stamp</div>

    @include('partials.print.signatures', ['signatures' => [
        ['name' => $expense->created_by_name, 'title' => 'Prepared by'],
        ['name' => $expense->approver ? $expense->approver->username : '', 'title' => 'Approved by'],
        ['name' => '', 'title' => 'Received by'],
    ]])
@endsection
