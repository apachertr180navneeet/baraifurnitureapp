<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quotation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #333;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .section {
            width: 100%;
            margin-bottom: 15px;
        }

        .box {
            border: 1px solid #ccc;
            padding: 10px;
            min-height: 80px;
        }

        .row {
            width: 100%;
            display: table;
        }

        .col-4 {
            width: 33.33%;
            display: table-cell;
            vertical-align: top;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th, table td {
            border: 1px solid #999;
            padding: 6px;
            font-size: 12px;
        }

        table th {
            background: #f2f2f2;
        }

        .totals {
            width: 40%;
            float: right;
            margin-top: 10px;
        }

        .totals td {
            border: none;
            padding: 4px;
        }

        .bank-section, .terms-section {
            margin-top: 30px;
        }

        .footer-clear {
            clear: both;
        }

        .red-text{
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h2 class="text-center">QUOTATION</h2>

    <!-- Header Section -->
    <div class="section row">
        <!-- Company Details -->
        <div class="col-4">
            <div class="box">
                <strong>Company Details</strong><br>
                BARAI FURNITURE (08LQVPS6339A1Z2)<br>
                PLOT NO 3 KHASRA  NO 124/1 GOKUL DHAM SALAWAS JODHPUR 342802 RAJASTHAN<br>
                Phone: 7611980545 / 6375835409 <br>
                Email: baraifurnitureofficial@gmail.com
            </div>
        </div>

        <!-- Logo -->
        <div class="col-4 text-center">
            <div class="box">
                <img src="https://barailife.com/barai-logo.jpg" width="100" height="80">
            </div>
        </div>

        <!-- Party Details -->
        <div class="col-4">
            <div class="box">
                <strong>Party Details</strong><br>
                {{ $user->full_name ?? 'Client Name' }}<br>
                {{ $user->address ?? 'Client Address' }}<br>
                Phone: {{ $user->phone ?? '9999999999' }}
            </div>
        </div>
    </div>

    <!-- Item Table -->
    <table>
        <thead>
            <tr>
                <th width="5%">Sr No</th>
                <th width="25%">Product Name</th>
                <th width="15%">Image</th>
                <th width="10%">Qty</th>
                <th width="15%">Price</th>
                <th width="15%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $sr = 1; @endphp
            @foreach($quotationItems as $row)
            <tr>
                <td class="text-center">{{ $sr++ }}</td>
                <td>{{ $row['item_name'] }}</td>
                <td class="text-center">
                    <img src="{{ $row['item_image'] }}" width="50" height="40">
                </td>
                <td class="text-center">{{ $row['quantity'] }}</td>
                <td class="text-right"> {{ number_format($row['item_price'],2) }}</td>
                <td class="text-right"> {{ number_format($row['amount'],2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <table class="totals">
        <tr>
            <td><strong>Sub Total:</strong></td>
            <td class="text-right"> {{ number_format($totalAmount,2) }}</td>
        </tr>
        <tr>
            <td><strong>GST (18%):</strong></td>
            <td class="text-right"> {{ number_format($totalAmount * 0.18,2) }}</td>
        </tr>
        <tr>
            <td><strong>Grand Total:</strong></td>
            <td class="text-right"><strong> {{ number_format($totalAmount * 1.18,2) }}</strong></td>
        </tr>
    </table>

    <div class="footer-clear"></div>

    <!-- Bank Details -->
    <div class="bank-section">
        <div class="box">
            <strong>Bank Details</strong><br><br>
            Bank Name: State Bank of India<br>
            Account Number: 41784105483<br>
            IFSC Code: SBIN00031375<br>
            Branch: Bhagat Ki kothi, Jodhpur
        </div>
    </div>

    <!-- Terms & Conditions -->
    <div class="terms-section">
        <div class="box">
            <strong>Terms & Conditions</strong><br><br>

            1. 50% payment must be made in advance and the remaining payment must be made before the order is dispatched.<br>

            2. Transportation charges will be extra, and we are not responsible for any delays in transportation.<br>

            <span style="color:red;">
            3. Your order will be dispatched within 7 days of receiving full payment.
            </span><br>

            4. Your order will be processed one day after we receive your advance payment.<br>

            <span style="color:red;">
            5. 10% discount on orders delayed after 7 days.
            </span>
        </div>
    </div>

</body>
</html>