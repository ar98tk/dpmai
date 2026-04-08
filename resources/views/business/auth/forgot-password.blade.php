@extends('business.auth.layout', [
    'title' => 'Forgot Password',
    'pill' => 'Password Recovery',
    'heading' => 'Forgot Your Password?',
    'subtitle' => 'Enter your business account email and we will send you a reset link.',
    'sideTitle' => 'Recover Access Quickly',
    'sideText' => 'Use a strong new password after reset to keep your account protected.',
])

@section('content')
    <form method="POST" action="{{ route('password.email') }}" class="form">
        @csrf

        <div class="field">
            <label for="email">Email Address</label>
            <input
                id="email"
                name="email"
                type="email"
                class="input"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                placeholder="you@example.com"
            >
        </div>

        <button type="submit" class="submit">Send Reset Link</button>
    </form>

    <p class="helper">
        Remembered your password? <a href="{{ route('login') }}">Back to login</a>
    </p>
@endsection
