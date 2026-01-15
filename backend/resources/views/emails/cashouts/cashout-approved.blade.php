@extends('emails.layout.master')

@section('content')
    <h2>Cashout Request Approved</h2>
    
    <p>Hello {{ $company->name }},</p>
    
    <p>Your cashout request has been approved and is being processed.</p>
    
    <div class="success-box">
        <table>
            <tr>
                <th>Request ID</th>
                <td>{{ $cashout->id }}</td>
            </tr>
            <tr>
                <th>Requested Amount</th>
                <td>KES {{ number_format($cashout->amount, 2) }}</td>
            </tr>
            <tr>
                <th>Platform Fee (3%)</th>
                <td>KES {{ number_format($cashout->platform_fee, 2) }}</td>
            </tr>
            <tr>
                <th>Net Amount</th>
                <td><strong>KES {{ number_format($cashout->net_amount, 2) }}</strong></td>
            </tr>
            <tr>
                <th>Payment Method</th>
                <td>{{ ucfirst($cashout->payment_method) }}</td>
            </tr>
        </table>
    </div>
    
    <p>The funds will be transferred to your account within 1-3 business days.</p>
    
    <a href="{{ $dashboardUrl }}" class="button">View Dashboard</a>
@endsection
