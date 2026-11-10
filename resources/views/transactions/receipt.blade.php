<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $transaction->reference }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background-color: #f7f7f7;
            margin: 0;
            padding: 20px;
        }
        .receipt-container {
            width: 58mm; /* standard thermal printer width */
            background-color: #fff;
            margin: 0 auto;
            padding: 5mm;
            box-sizing: border-box;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mt-2 { margin-top: 5px; }
        .mt-4 { margin-top: 10px; }
        .mb-2 { margin-bottom: 5px; }
        .mb-4 { margin-bottom: 10px; }
        .flex { display: flex; justify-content: space-between; }
        .dashed-line {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .header {
            font-size: 16px;
            font-weight: bold;
        }
        .btn-print {
            display: block;
            width: 58mm;
            margin: 0 auto 20px auto;
            padding: 10px;
            background: #1ABB9C;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-family: sans-serif;
            font-weight: bold;
        }
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
    <button class="btn-print no-print" onclick="window.print()">Print Thermal Receipt</button>
    
    <div class="receipt-container">
        <div class="text-center mb-4">
            <div class="header">OPEN SACCO</div>
            <div class="mt-2">Tel: +256 700 000000</div>
            <div>www.opensacco.com</div>
        </div>

        <div class="text-center font-bold mt-4 mb-2">
            *** TRANSACTION RECEIPT ***
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

        <div class="mt-4 mb-4">
            <div class="flex header">
                <span>AMOUNT:</span>
                <span>{{ number_format($transaction->amount, 0) }}</span>
            </div>
        </div>

        <div class="dashed-line"></div>

        <div class="mt-2 mb-4 text-center">
            <div>{{ $transaction->narration ?: 'CASH' }}</div>
        </div>

        <div class="text-center mt-4">
            Thank you for banking with us!
            <br>
            <br>
            <br>
            ...........................<br>
            Customer Signature
        </div>
        
        <!-- Extra space at the bottom for paper cutting -->
        <br><br>
    </div>
</body>
</html>
