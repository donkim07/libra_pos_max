<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Order #{{ $saleOrder->id }}</title>
    <style>
        @media print {
            body {
                margin: 0;
            }

            .no-print {
                display: none;
            }
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
            font-size: 20px;
        }

        .receipt-info {
            margin-bottom: 10px;
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
            padding: 6px 0;
            text-align: left;
        }

        table td {
            padding: 4px 2px;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            border-top: 2px solid #000;
            padding-top: 8px;
            margin-top: 8px;
        }

        .totals div {
            display: flex;
            justify-content: space-between;
            margin: 4px 0;
        }

        .grand-total {
            font-weight: bold;
            font-size: 14px;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 11px;
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
        <button onclick="window.print()">🖨️ Print Order</button>
    </div>

    <div class="receipt-header">
        <h1>{{ env('APP_NAME') }}</h1>
        <img class="" src="{{ asset('images/logo.png') }}" alt="Logo">
        <p>SALE ORDER</p>
    </div>

    <div class="receipt-info">
        <div>
            <span>Order #:</span>
            <span><strong>{{ $saleOrder->receipt_number ?? $saleOrder->id }}</strong></span>
        </div>
        <div>
            <span>Date:</span>
            <span>{{ optional($saleOrder->order_date)->format('M j, Y') }}</span>
        </div>
        <div>
            <span>Expected Delivery:</span>
            <span>{{ optional($saleOrder->expected_delivery_date)->format('M j, Y') }}</span>
        </div>
        <div>
            <span>Customer:</span>
            <span>{{ $saleOrder->customer?->name ?? 'Walk-in Customer' }}</span>
        </div>
        <div>
            <span>Cashier:</span>
            <span>{{ $saleOrder->creator?->name ?? 'System' }}</span>
        </div>
        <div>
            <span>Status:</span>
            <span>{{ strtoupper($saleOrder->status) }} / {{ strtoupper($saleOrder->payment_status) }} /
                {{ strtoupper($saleOrder->delivery_status) }}</span>
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
            @foreach ($saleOrder->saleOrderItems as $item)
                <tr>
                    <td>{{ $item->item?->name ?? 'Item' }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->price, 0) }}</td>
                    <td class="text-right">{{ number_format($item->total, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $subTotal = $saleOrder->saleOrderItems->sum('total');
        $paid = (float) $saleOrder->paid_amount;
        $balance = max($subTotal - $paid, 0);
    @endphp

    <div class="totals">
        <div>
            <span>Subtotal:</span>
            <span>TSH {{ number_format($subTotal, 0) }}</span>
        </div>
        <div>
            <span>Paid:</span>
            <span>TSH {{ number_format($paid, 0) }}</span>
        </div>
        @if ($balance > 0)
            <div>
                <span>Balance Due:</span>
                <span>TSH {{ number_format($balance, 0) }}</span>
            </div>
        @endif
        <div class="grand-total">
            <span>TOTAL:</span>
            <span>TSH {{ number_format($subTotal, 0) }}</span>
        </div>
    </div>

    <div class="receipt-footer">
        <p>Thank you for your order!</p>
        <p>{{ env('APP_NAME') ?? 'Store Name' }}</p>
        <p style="margin-top: 10px;">Order Status: <strong>{{ strtoupper($saleOrder->status) }}</strong></p>
    </div>
</body>

</html>
