@php
    $watermark = $print->watermark ?? 'DRAFT';
    $statusClass = strtolower($watermark);
    $heading = $documentTitle ?? trim($__env->yieldContent('document_title')) ?: 'Document';
@endphp
<header class="doc-header">
    <div class="doc-brand">
        @if(! empty($orgLogoUrl))
            <img src="{{ $orgLogoUrl }}" alt="{{ $orgName }}" class="doc-logo">
        @endif
        <div>
            <div class="doc-org">{{ strtoupper($orgName) }}</div>
            @if(filled($orgAddress))<div>{{ $orgAddress }}</div>@endif
            <div>
                @if(filled($orgPhone))Tel: {{ $orgPhone }}@endif
                @if(filled($orgEmail))&nbsp;· {{ $orgEmail }}@endif
            </div>
            <div>
                @if(filled($orgTin ?? null))TIN: {{ $orgTin }}@endif
                @if(filled($orgUmraRegNo ?? null))&nbsp;· UMRA Reg. No: {{ $orgUmraRegNo }}@endif
            </div>
        </div>
    </div>
    <div class="doc-title">
        <div class="doctype">{{ $heading }}</div>
        <div class="docid">{{ $print->document_id }}</div>
        <span class="status {{ $statusClass }}">{{ $watermark }}</span>
    </div>
</header>
