<style>
    @page { size: A4 portrait; margin: 14mm; }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }
    body {
        font-family: "Segoe UI", Arial, Helvetica, sans-serif;
        font-size: 11.5px;
        line-height: 1.45;
        color: #1e293b;
        background: #fff;
    }
    .sheet, .page {
        position: relative;
        z-index: 1;
        width: 210mm;
        min-height: 280mm;
        margin: 0 auto 32px;
        padding: 16mm 15mm;
        background: #fff;
    }
    .page { min-height: 297mm; margin-bottom: 24px; }
    .page:last-child { page-break-after: auto; break-after: auto; }
    .watermark {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-32deg);
        font-size: 130px;
        font-weight: 800;
        letter-spacing: 12px;
        color: rgba(15, 23, 42, 0.075);
        z-index: 0;
        pointer-events: none;
        user-select: none;
        white-space: nowrap;
    }
    .watermark.inline {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-32deg);
    }
    .doc-header {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        border-bottom: 3px solid #16a34a;
        padding-bottom: 10px;
        margin-bottom: 16px;
    }
    .doc-brand { display: flex; gap: 12px; align-items: center; }
    .doc-logo { max-height: 54px; max-width: 120px; object-fit: contain; }
    .doc-org { font-size: 18px; font-weight: 800; letter-spacing: 0.4px; color: #0f172a; }
    .doc-brand div div { font-size: 10.5px; color: #475569; }
    .doc-title { text-align: right; min-width: 38%; }
    .doc-title .doctype {
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #0f172a;
    }
    .doc-title .docid {
        margin-top: 4px;
        font-family: "Courier New", monospace;
        font-size: 11px;
        font-weight: 700;
        color: #16a34a;
    }
    .doc-title .status {
        margin-top: 3px;
        display: inline-block;
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: 1px;
        padding: 2px 8px;
        border: 1px solid #94a3b8;
        border-radius: 999px;
        color: #475569;
    }
    .doc-title .status.original { border-color: #16a34a; color: #15803d; }
    .doc-title .status.copy { border-color: #b45309; color: #b45309; }
    .doc-title .status.draft { border-color: #64748b; color: #64748b; }
    h1.doc-heading { font-size: 16px; margin: 0 0 4px; color: #0f172a; }
    h2.section {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: #64748b;
        margin: 18px 0 6px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 3px;
    }
    p { margin: 0 0 8px; }
    .muted { color: #64748b; }
    .small { font-size: 9.5px; }
    .right { text-align: right; }
    .center { text-align: center; }
    .strong { font-weight: 700; }
    table.doc-table { width: 100%; border-collapse: collapse; margin: 4px 0 10px; }
    table.doc-table th {
        text-align: left;
        font-size: 9.5px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #475569;
        background: #f1f5f9;
        border-bottom: 1px solid #cbd5e1;
        padding: 6px 7px;
    }
    table.doc-table td { padding: 5px 7px; border-bottom: 1px solid #e8edf3; vertical-align: top; }
    table.doc-table thead { display: table-header-group; }
    table.doc-table tr { page-break-inside: avoid; break-inside: avoid; }
    table.doc-table tr.total td { font-weight: 700; border-top: 2px solid #94a3b8; border-bottom: none; }
    table.kv { width: 100%; border-collapse: collapse; }
    table.kv td { padding: 3px 0; vertical-align: top; }
    table.kv td.label { color: #64748b; width: 42%; }
    table.kv td.value { font-weight: 600; text-align: right; }
    .amount-box {
        border: 2px solid #0f172a;
        border-radius: 6px;
        padding: 8px 12px;
        margin: 12px 0;
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 12px;
    }
    .amount-box .amount { font-size: 19px; font-weight: 800; }
    .amount-words { font-style: italic; color: #334155; }
    .two-col { display: flex; gap: 24px; }
    .two-col > div { flex: 1; }
    .notice {
        border-left: 4px solid #16a34a;
        background: #f0fdf4;
        padding: 8px 12px;
        margin: 10px 0;
        font-size: 10.5px;
        color: #166534;
    }
    .sig-grid {
        display: flex;
        gap: 22px;
        margin-top: 34px;
    }
    .sig-grid > div { flex: 1; }
    .sig-line { border-top: 1px solid #64748b; margin-top: 34px; padding-top: 4px; }
    .sig-name { font-weight: 700; font-size: 10.5px; min-height: 14px; }
    .sig-title { font-size: 9.5px; color: #64748b; text-transform: uppercase; letter-spacing: 0.6px; }
    .seal {
        margin-top: 26px;
        width: 110px;
        height: 60px;
        border: 1px dashed #94a3b8;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-size: 8.5px;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .doc-footer {
        margin-top: 22px;
        border-top: 1px solid #cbd5e1;
        padding-top: 8px;
        display: flex;
        gap: 14px;
        align-items: flex-start;
        font-size: 9px;
        color: #64748b;
    }
    .doc-footer .foot-meta { flex: 1; }
    .doc-footer .foot-meta div { margin-bottom: 2px; }
    .doc-footer .foot-qr { text-align: center; width: 92px; }
    .doc-footer .foot-qr svg { width: 76px; height: 76px; display: block; margin: 0 auto 2px; }
    .doc-disclaimer { margin-top: 6px; font-style: italic; font-size: 8.8px; }
    .page-break { page-break-after: always; break-after: page; }
    @media print {
        body { background: #fff; }
        .no-print { display: none !important; }
        .sheet { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
        .page {
            width: auto;
            min-height: 262mm;
            margin: 0;
            padding: 0;
            box-shadow: none;
            page-break-after: always;
            break-after: page;
        }
        .page:last-child { page-break-after: auto; break-after: auto; }
        .watermark { position: fixed; }
        .watermark.inline { position: absolute; }
    }
</style>
