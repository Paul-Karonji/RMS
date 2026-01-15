@extends('emails.layout.master')

@section('content')
    <h2>Welcome to {{ config('app.name') }}!</h2>
    
    <p>Hello {{ $user->name }},</p>
    
    <p>Your account has been successfully created for <strong>{{ $company->name }}</strong>.</p>
    
    <div class="info-box">
        <p><strong>Login Credentials:</strong></p>
        <p>Email: {{ $user->email }}</p>
        <p>Temporary Password: <strong>{{ $temporaryPassword }}</strong></p>
    </div>
    
    <div class="warning-box">
        <p><strong>⚠️ Important:</strong> You must change your password on first login for security reasons.</p>
    </div>
    
    <a href="{{ $loginUrl }}" class="button">Login Now</a>
    
    <div class="divider"></div>
    
    <p>If you have any questions, please don't hesitate to contact our support team.</p>
@endsection
