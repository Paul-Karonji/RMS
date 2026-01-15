@extends('emails.layout.master')

@section('content')
    <h2>Property Approved! 🎉</h2>
    
    <p>Hello {{ $owner->name }},</p>
    
    <p>Great news! Your property has been approved and is now active in the system.</p>
    
    <div class="success-box">
        <p><strong>Property Details:</strong></p>
        <p>Name: {{ $property->property_name }}</p>
        <p>Type: {{ $property->property_type }}</p>
        <p>Location: {{ $property->city }}, {{ $property->county }}</p>
        <p>Total Units: {{ $property->total_units }}</p>
    </div>
    
    <p>You can now:</p>
    <ul style="margin-left: 20px; margin-bottom: 20px;">
        <li>Add units to your property</li>
        <li>Set rental prices</li>
        <li>Start accepting tenants</li>
    </ul>
    
    <a href="{{ $propertyUrl }}" class="button">View Property</a>
@endsection
