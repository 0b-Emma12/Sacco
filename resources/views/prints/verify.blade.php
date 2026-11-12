<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Document Verification — {{ $orgName }}</title>
    @include('partials.print.styles')
    <style>
        .verify { max-width: 620px; margin: 48px auto; background: #fff; border: 1px solid #dbe1e8; border-radius: 14px; padding: 28px 30px; box-shadow: 0 6px 24px rgba(15,23,42,.08); }
        .verify h1 { font-size: 20px; margin: 0 0 4px; }
        .banner { border-radius: 10px; padding: 14px 16px; font-weight: 700; margin: 16px 0 20px; }
        .banner.valid { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }
        .banner.invalid { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
        .banner small { display: block; font-weight: 500; margin-top: 4px; color: inherit; opacity: .85; }
        .verify table.kv td.label { width: 46%; }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <a class="btn secondary" href="{{ url('/') }}">Go to {{ $orgName }}</a>
    </div>

    <div class="verify">
        <div class="doc-org">{{ strtoupper($orgName) }}</div>
        <div class="small muted">{{ $orgAddress ?: '' }}</div>
        <h1 style="margin-top:14px;">Document authenticity check</h1>

        @if($log)
            @php
                $typeLabel = \App\Services\PrintDocumentService::TYPES[$log->document_type]['label'] ?? $log->document_type;
                $issuedAt = $log->created_at;
                $isCopy = $log->watermark === 'COPY';
            @endphp

            <div class="banner valid">
                This document is genuine and was issued by {{ $orgName }}.
                <small>The details below are held in the SACCO's document register.</small>
            </div>

            <table class="kv">
                <tr><td class="label">Document type</td><td class="value">{{ $typeLabel }}</td></tr>
                <tr><td class="label">Document ID</td><td class="value">{{ $log->document_id }}</td></tr>
                <tr><td class="label">Issue status</td><td class="value">{{ $isCopy ? 'Valid reprint of an issued document' : 'Original issue' }}</td></tr>
                <tr><td class="label">Date issued</td><td class="value">{{ $issuedAt->format('d M Y H:i') }}</td></tr>
            </table>

            <p class="small muted" style="margin-top:18px;">
                For privacy, financial amounts and member details are not shown on this public page. If the document in
                your hand does not match the document ID and type above, treat it as invalid and contact the SACCO office.
            </p>
        @else
            <div class="banner invalid">
                This document could not be verified.
                <small>The document ID and verification code do not match any document issued by this SACCO.</small>
            </div>

            <p class="small muted">
                Check that you entered the address exactly as printed, or scan the QR code on the document again. If the
                problem persists, contact the SACCO office.
            </p>
        @endif

        <table class="kv" style="margin-top:18px;">
            <tr><td class="label">SACCO office</td><td class="value">{{ $orgPhone ?: '—' }}</td></tr>
            <tr><td class="label">Email</td><td class="value">{{ $orgEmail ?: '—' }}</td></tr>
            <tr><td class="label">UMRA Reg. No.</td><td class="value">{{ \App\Models\Setting::get('org_umra_reg_no', '') ?: '—' }}</td></tr>
        </table>
    </div>
</body>
</html>
