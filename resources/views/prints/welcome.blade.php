@extends('layouts.print')
@section('document_title', 'Membership Welcome Pack')
@section('content')
    <h1 class="doc-heading">Welcome to {{ $orgName }}</h1>
    <p class="muted">Your membership has been approved. This pack confirms your details and summarises the rules that govern your membership.</p>

    <h2 class="section">Member particulars</h2>
    <div class="two-col">
        <div>
            <table class="kv">
                <tr><td class="label">Member name</td><td class="value">{{ $member->salutation }} {{ $member->first_name }} {{ $member->last_name }}</td></tr>
                <tr><td class="label">Membership number</td><td class="value">{{ $member->membership_number }}</td></tr>
                <tr><td class="label">National ID (NIN)</td><td class="value">{{ $member->national_id ?: '—' }}</td></tr>
                <tr><td class="label">Date joined</td><td class="value">{{ optional($member->created_at)->format('d M Y') }}</td></tr>
            </table>
        </div>
        <div>
            <table class="kv">
                <tr><td class="label">Phone</td><td class="value">{{ $member->phone_number ?: '—' }}</td></tr>
                <tr><td class="label">Email</td><td class="value">{{ $member->email ?: '—' }}</td></tr>
                <tr><td class="label">Common bond</td><td class="value">{{ ucwords(str_replace('_', ' ', (string) $member->common_bond_type)) }}{{ $member->common_bond_value ? ' — '.$member->common_bond_value : '' }}</td></tr>
                <tr><td class="label">Share capital</td><td class="value">UGX {{ number_format($capital['share_balance'], 2) }}</td></tr>
                <tr><td class="label">Savings balance</td><td class="value">UGX {{ number_format($capital['savings_balance'], 2) }}</td></tr>
            </table>
        </div>
    </div>

    <h2 class="section">Shareholding</h2>
    <table class="doc-table">
        <thead>
            <tr><th>Share price</th><th class="right">Shares held</th><th class="right">Share value</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>UGX {{ number_format($sharePrice, 2) }}</td>
                <td class="right">{{ number_format($capital['share_count'], 4) }}</td>
                <td class="right">UGX {{ number_format($capital['share_balance'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <h2 class="section">Rules summary</h2>
    @if($policies->isNotEmpty())
        <ol class="small">
            @foreach($policies as $policy)
                <li><strong>{{ $policy->name }}</strong> (v{{ $policy->version }}{{ $policy->effective_date ? ', effective '.$policy->effective_date->format('d M Y') : '' }}):
                    {{ $policy->summary }}</li>
            @endforeach
        </ol>
    @else
        <p class="muted small">No approved policy summaries are recorded yet. The approved by-laws held at the SACCO office apply.</p>
    @endif

    <h2 class="section">Emergency contacts</h2>
    <table class="kv">
        <tr><td class="label">SACCO office</td><td class="value">{{ $orgPhone ?: '—' }}</td></tr>
        <tr><td class="label">Email</td><td class="value">{{ $orgEmail ?: '—' }}</td></tr>
        <tr><td class="label">Address</td><td class="value">{{ $orgAddress ?: '—' }}</td></tr>
    </table>

    @include('partials.print.signatures', ['signatures' => [
        ['name' => \App\Models\Setting::get('org_chairperson', ''), 'title' => 'Chairperson'],
        ['name' => \App\Models\Setting::get('org_secretary', ''), 'title' => 'Secretary'],
        ['name' => '', 'title' => 'Member acknowledgement'],
    ]])
@endsection
