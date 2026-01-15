@extends('emails.layout.master')

@section('content')
    <h2>Lease Agreement Created</h2>
    
    <p>Hello {{ $tenant->name }},</p>
    
    <p>Your lease agreement has been created and is now active.</p>
    
    <div class="info-box">
        <table>
            <tr>
                <th>Lease Number</th>
                <td>{{ $lease->lease_number }}</td>
            </tr>
            <tr>
                <th>Unit</th>
                <td>{{ $unit->unit_number }}</td>
            </tr>
            <tr>
                <th>Property</th>
                <td>{{ $property->property_name }}</td>
            </tr>
            <tr>
                <th>Monthly Rent</th>
                <td><strong>KES {{ number_format($lease->monthly_rent, 2) }}</strong></td>
            </tr>
            <tr>
                <th>Start Date</th>
                <td>{{ $lease->start_date }}</td>
            </tr>
            <tr>
                <th>End Date</th>
                <td>{{ $lease->end_date }}</td>
            </tr>
            <tr>
                <th>Deposit Paid</th>
                <td>KES {{ number_format($lease->deposit_amount, 2) }}</td>
            </tr>
        </table>
    </div>
    
    <a href="{{ $leaseUrl }}" class="button">View Lease Details</a>
    
    <p>Your first rent payment is due on {{ $firstPaymentDate }}.</p>
@endsection
