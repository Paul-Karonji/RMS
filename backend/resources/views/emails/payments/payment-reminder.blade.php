@extends('emails.layout.master')

@section('content')
    <h2>Payment Reminder</h2>
    
    <p>Hello {{ $tenant->name }},</p>
    
    <p>This is a friendly reminder that your rent payment is due soon.</p>
    
    <div class="info-box">
        <table>
            <tr>
                <th>Unit</th>
                <td>{{ $unit->unit_number }}</td>
            </tr>
            <tr>
                <th>Amount Due</th>
                <td><strong>KES {{ number_format($amount, 2) }}</strong></td>
            </tr>
            <tr>
                <th>Due Date</th>
                <td>{{ $dueDate }}</td>
            </tr>
            <tr>
                <th>Days Remaining</th>
                <td>{{ $daysRemaining }} days</td>
            </tr>
        </table>
    </div>
    
    <a href="{{ $paymentUrl }}" class="button">Pay Now</a>
    
    <p>Paying on time helps you avoid late fees and maintains your good standing.</p>
@endsection
