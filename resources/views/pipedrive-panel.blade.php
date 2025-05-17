<!DOCTYPE html>
<html>
<head>
    <title>Customer Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

    <h3>Transactions for: {{ $email }}</h3>

    @if(isset($data['error']))
        <div class="alert alert-danger">{{ $data['error'] }}</div>
    @else
        <h5>Invoices</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Invoice ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Customer ID</th>
                    <th>Date</th>
                    <th>Receipt</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['invoices'] ?? [] as $invoice)
                    <tr>
                        <td>{{ $invoice['id'] }}</td>
                        <td>${{ number_format($invoice['amount_due'] / 100, 2) }}</td>
                        <td>{{ $invoice['status'] }}</td>
                        <td>{{ $invoice['customer'] }}</td>
                        <td>{{ date('Y-m-d', $invoice['created']) }}</td>
                        <td>
                            @if(isset($invoice['hosted_invoice_url']))
                                <a href="{{ $invoice['hosted_invoice_url'] }}" target="_blank">View</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h5>Charges</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Charge ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Customer ID</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['charges'] ?? [] as $charge)
                    <tr>
                        <td>{{ $charge['id'] }}</td>
                        <td>${{ number_format($charge['amount'] / 100, 2) }}</td>
                        <td>{{ $charge['status'] }}</td>
                        <td>{{ $charge['customer'] }}</td>
                        <td>{{ date('Y-m-d', $charge['created']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</body>
</html>
