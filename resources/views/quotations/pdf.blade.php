<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quotation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
        }
        .header {
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
        }
        .header p {
            margin: 2px 0;
        }
        .line {
            border-bottom: 1px solid #ccc;
            margin-bottom: 15px;
        }
        .item {
            display: flex;
            margin-bottom: 15px;
            align-items: center;
        }
        .item img {
            width: 100px;
            height: 80px;
            object-fit: cover;
            margin-right: 15px;
            border: 1px solid #ccc;
        }
        .item-details {
            flex: 1;
        }
        .item-details h4 {
            margin: 0 0 5px 0;
        }
        .item-details p {
            margin: 0;
        }
        .item-amount {
            font-weight: bold;
            text-align: right;
            min-width: 80px;
        }
        .totals {
            margin-top: 20px;
            text-align: right;
        }
        .totals p {
            margin: 2px 0;
            font-size: 15px;
        }
        .totals .grand-total {
            font-size: 16px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    @php
        $pdfItems = collect($quotationItems ?? [])->map(function ($row) {
            return (object) $row;
        });
    @endphp

    <div class="header">
        <h2>{{ $user->full_name ?? '' }}</h2>
        <p>{{ $user->phone ?? '' }} | {{ $user->email ?? '' }} | {{ $user->city ?? '' }}</p>
        <div class="line"></div>
    </div>

    @foreach($pdfItems as $row)
        <div class="item">
            <img src="{{ $row->item_image ?? '' }}" width="100" height="80" alt="{{ $row->item_name ?? '' }}">
            <div class="item-details">
                <h4>{{ $row->item_name ?? '' }}</h4>
                <p>Code: {{ $row->item_code ?? '-' }}</p>
                <p>Qty: {{ $row->quantity ?? 0 }}</p>
            </div>
            <div class="item-amount">Rs. {{ number_format((float) ($row->amount ?? 0), 2) }}/-</div>
        </div>
    @endforeach

    <div class="totals">
        <p>Sub Total: Rs. {{ number_format($totalAmount, 2) }}</p>
        <p>Grand Total: <span class="grand-total">Rs. {{ number_format($totalAmount, 2) }}</span></p>
    </div>
</body>
</html>
