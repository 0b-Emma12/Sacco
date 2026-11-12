<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $print->document_id ?: $transaction->reference }}</title>
    <style>
        @page { size: 58mm auto; margin: 2mm; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background-color: #f7f7f7;
            margin: 0;
            padding: 20px;
        }
        .receipt-container {
            position: relative;
            overflow: hidden;
            width: 58mm; /* standard thermal printer width */
            background-color: #fff;
            margin: 0 auto;
            padding: 5mm;
            box-sizing: border-box;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .watermark-thermal {
            position: absolute;
            top: 42%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-32deg);
            font-size: 30px;
            font-weight: bold;
            letter-spacing: 4px;
            color: rgba(0, 0, 0, 0.09);
            pointer-events: none;
            white-space: nowrap;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .small { font-size: 10px; }
        .mt-2 { margin-top: 5px; }
        .mt-4 { margin-top: 10px; }
        .mb-2 { margin-bottom: 5px; }
        .mb-4 { margin-bottom: 10px; }
        .flex { display: flex; justify-content: space-between; gap: 6px; }
        .flex span:last-child { text-align: right; }
        .dashed-line {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .header {
            font-size: 16px;
            font-weight: bold;
        }
        .docid {
            font-size: 9px;
            letter-spacing: .3px;
        }
        .status-pill {
            display: inline-block;
            border: 1px solid #000;
            padding: 0 6px;
            font-size: 9px;
            letter-spacing: 1px;
        }
        .qr svg { width: 54px; height: 54px; display: block; margin: 0 auto; }
        @media print {
            .no-print { display: none !important; }
            body { background-color: #fff; padding: 0; }
            .receipt-container {
                width: 100%; /* Printer driver handles the paper width */
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="watermark-thermal">{{ $print->watermark ?? 'DRAFT' }}</div>

        <div class="text-center mb-4">
            <div class="header">{{ strtoupper($orgName) }}</div>
            @if(filled($orgPhone))
                <div class="mt-2">Tel: {{ $orgPhone }}</div>
            @endif
            @if(filled($orgEmail))
                <div>{{ $orgEmail }}</div>
            @endif
            @if(filled($orgAddress))
                <div>{{ $orgAddress }}</div>
            @endif
            @if(filled($orgTin ?? null))
                <div class="small">TIN: {{ $orgTin }}</div>
            @endif
            @if(filled($orgUmraRegNo ?? null))
                <div class="small">UMRA: {{ $orgUmraRegNo }}</div>
            @endif
        </div>

        <div class="text-center font-bold mt-4 mb-2">
            *** TRANSACTION RECEIPT ***
        </div>
        <div class="text-center docid">
            {{ $print->document_id }}
            &nbsp;<span class="status-pill">{{ $print->watermark ?? 'DRAFT' }}</span>
        </div>

        <div class="dashed-line"></div>
        
        <div class="mt-2">
            <div class="flex">
                <span>Date:</span>
                <span>{{ $transaction->created_at->format('d/m/y H:i') }}</span>
            </div>
            <div class="flex mt-2">
                <span>Receipt:</span>
                <span>{{ $transaction->reference }}</span>
            </div>
            <div class="flex mt-2">
                <span>Cashier:</span>
                <span>{{ substr($transaction->performed_by_username ?: 'System', 0, 10) }}</span>
            </div>
            @if($transaction->payment_method)
                <div class="flex mt-2">
                    <span>Method:</span>
                    <span>{{ strtoupper(str_replace('_', ' ', $transaction->payment_method)) }}</span>
                </div>
            @endif
            @if($transaction->external_reference)
                <div class="flex mt-2">
                    <span>Ref:</span>
                    <span>{{ $transaction->external_reference }}</span>
                </div>
            @endif
        </div>

        <div class="dashed-line"></div>

        <div class="mt-2">
            <div class="flex font-bold">
                <span>Account:</span>
                <span>{{ $transaction->account_number }}</span>
            </div>
            <div class="flex mt-2 font-bold">
                <span>Type:</span>
                <span>{{ strtoupper(str_replace('_', ' ', $transaction->transaction_type)) }}</span>
            </div>
        </div>

        <div class="dashed-line"></div>

        <div class="mt-4 mb-2">
            <div class="flex header">
                <span>AMOUNT:</span>
                <span>{{ number_format($transaction->amount, 0) }}</span>
            </div>
            <div class="small mt-2">{{ $amountInWords }}</div>
        </div>

        <div class="dashed-line"></div>

        <div class="mt-2 mb-2 text-center">
            <div>{{ $transaction->narration ?: 'CASH' }}</div>
        </div>

        @if($member)
            <div class="flex mt-2 font-bold">
                <span>Balance:</span>
                <span>UGX {{ number_format((float) $member->total_savings, 0) }}</span>
            </div>
        @endif

        <div class="dashed-line"></div>

        <div class="text-center mt-4">
            Thank you for banking with us!
            <br><br>
            ...........................<br>
            Customer Signature
        </div>

        <div class="dashed-line"></div>

        <div class="text-center small">
            <div class="qr">{!! \App\Services\PrintDocumentService::qrSvg($print ?? null, 54) !!}</div>
            @if($print->exists)
                <div>Scan to verify</div>
            @else
                <div>Verification code is issued on print</div>
            @endif
            <div class="mt-2">
                {{ filled($docDisclaimer ?? null) ? $docDisclaimer : 'Computer-generated receipt. Valid only with an official SACCO stamp.' }}
            </div>
            <div class="mt-2">
                Printed {{ now()->format('d/m/y H:i') }} by {{ $print->printed_by_name ?: 'preview' }}
            </div>
        </div>
        
        <!-- Extra space at the bottom for paper cutting -->
        <br><br>
    </div>
</body>
</html>
