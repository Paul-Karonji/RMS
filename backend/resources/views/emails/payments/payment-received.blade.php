@extends('emails.layout.master')

@section('content')
    <h2>Payment Received</h2>
    
    <p>Hello {{ $tenant->name }},</p>
    
    <p>We have successfully received your payment. Thank you!</p>
    
    <div class="success-box">
        <table>
            <tr>
                <th>Transaction ID</th>
                <td>{{ $payment->transaction_id }}</td>
            </tr>
            <tr>
                <th>Amount Paid</th>
                <td><strong>KES {{ number_format($payment->amount, 2) }}</strong></td>
            </tr>
            <tr>
                <th>Payment Method</th>
                <td>{{ ucfirst($payment->payment_method) }}</td>
            </tr>
            <tr>
                <th>Payment Date</th>
                <td>{{ $payment->payment_date }}</td>
            </tr>
            <tr>
                <th>Unit</th>
                <td>{{ $unit->unit_number }}</td>
            </tr>
        </table>
    </div>
    
    <a href="{{ $receiptUrl }}" class="button">Download Receipt</a>
    
    <p>This payment has been applied to your account. You can view your payment history in your dashboard.</p>
@endsection
