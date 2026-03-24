<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->id }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }

        body {
            font-family: 'Courier New', monospace;
            max-width: 120mm;
            margin: 0 auto;
            padding: 10px;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
        img {
            align-items: center;
            max-width: 100px;
        }

        .receipt-header h1 {
            margin: 0;
            font-size: 24px;
        }

        .receipt-info {
            margin-bottom: 15px;
            font-size: 12px;
        }

        .receipt-info div {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 12px;
        }

        table th {
            border-bottom: 1px solid #000;
            padding: 8px 0;
            text-align: left;
        }

        table td {
            padding: 5px 4px;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            border-top: 2px solid #000;
            padding-top: 10px;
            margin-top: 10px;
        }

        .totals div {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }

        .grand-total {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
        }

        .receipt-footer {
            text-align: center;
            margin-top: 20px;
            border-top: 2px dashed #000;
            padding-top: 10px;
            font-size: 11px;
        }

        .print-button {
            margin: 20px auto;
            text-align: center;
        }

        .print-button button {
            padding: 10px 20px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="no-print print-button">
        <button onclick="window.print()">🖨️ Print Receipt</button>
    </div>

    <div class="receipt-header">
        <h1>{{ env('APP_NAME')  }}</h1>
        <img class="" src="{{ asset('images/logo.png') }}">
        <p>SALES RECEIPT</p>
    </div>
    {{-- <div class="receipt-header">
        <h1>{{ $sale->store->name ?? 'Store Name' }}</h1>
        <p>SALES RECEIPT</p>
    </div> --}}

    <div class="receipt-info">
        <div>
            <span>Receipt #:</span>
            <span><strong>{{ $sale->receipt_number }}</strong></span>
        </div>
        <div>
            <span>Date:</span>
            <span>{{ $sale->created_at->format('M j, Y - g:i A') }}</span>
        </div>
        <div>
            <span>Customer:</span>
            <span>{{ $sale->customer->name ?? 'Walk-in Customer' }}</span>
        </div>
        <div>
            <span>Cashier:</span>
            <span>{{ $sale->creator->name ?? 'System' }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $item)
            <tr>
                <td>{{ $item->item->name }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->price, 0) }}</td>
                <td class="text-right">{{ number_format($item->total, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div>
            <span>Subtotal:</span>
            <span>TSH {{ number_format($sale->total + ($sale->discount ?? 0), 0) }}</span>
        </div>
        @if($sale->discount > 0)
        <div>
            <span>Discount:</span>
            <span>- TSH {{ number_format($sale->discount, 0) }}</span>
        </div>
        @endif
        <div class="grand-total">
            <span>TOTAL:</span>
            <span>TSH {{ number_format($sale->total, 0) }}</span>
        </div>
        <div>
            <span>Paid:</span>
            <span>TSH {{ number_format($sale->paid_amount, 0) }}</span>
        </div>
        @if($sale->total - $sale->paid_amount > 0)
        <div>
            <span>Balance Due:</span>
            <span>TSH {{ number_format($sale->total - $sale->paid_amount, 0) }}</span>
        </div>
        @endif
    </div>

    <div class="receipt-footer">
        <p>Thank you for purchasing here!</p>
        <p>{{ env('APP_NAME') ?? 'Store Name' }}</p>
        @if($sale->status)
        <p style="margin-top: 10px;">Status: <strong>{{ strtoupper($sale->status) }}</strong></p>
        @endif
    </div>
</body>
</html>
