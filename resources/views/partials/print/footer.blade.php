@php
    $generatedAt = $print->created_at;
    $printedBy = $print->printed_by_name ?: 'System';
@endphp
<footer class="doc-footer">
    <div class="foot-meta">
        <div><strong>Document ID:</strong> {{ $print->document_id }}
            @if($print->is_reprint)
                <span class="muted">(reprint{{ isset($print->meta['original_document_id']) ? ' of '.$print->meta['original_document_id'] : '' }})</span>
            @endif
        </div>
        <div><strong>Generated:</strong> {{ $generatedAt->format('d M Y H:i') }}</div>
        <div><strong>Printed by:</strong> {{ $printedBy }}@if($print->printed_by_id) (User #{{ $print->printed_by_id }})@endif</div>
        <div><strong>Status:</strong> {{ $print->watermark }}</div>
        <div class="doc-disclaimer">
            {{ filled($docDisclaimer ?? null) ? $docDisclaimer : 'This is a computer-generated document. Valid only with an official SACCO stamp and authorised signature.' }}
        </div>
    </div>
    <div class="foot-qr">
        {!! \App\Services\PrintDocumentService::qrSvg($print) !!}
        <div>Scan to verify</div>
    </div>
</footer>
