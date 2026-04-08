@extends('business.auth.layout', [
    'title' => 'Verify Email',
    'pill' => 'Email Verification',
    'heading' => 'Verify Your Email Address',
    'subtitle' => 'Before continuing, please confirm your email using the link we just sent to your inbox.',
    'sideTitle' => 'One More Step',
    'sideText' => 'Verification keeps your business account secure and enables password recovery and notifications.',
])

@section('content')
    @if (session('status') === 'verification-link-sent')
        <div class="flash success">A new verification email has been sent to your inbox.</div>
    @endif

    <div class="form">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="submit">Resend Verification Email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="muted-btn">Logout</button>
        </form>
    </div>
@endsection
