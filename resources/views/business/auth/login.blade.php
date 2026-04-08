@extends('business.auth.layout', [
    'title' => 'Login',
    'pill' => 'Business Login',
    'heading' => 'Welcome Back',
    'subtitle' => 'Sign in to manage your business account and keep your AI assistant running.',
    'sideTitle' => 'Fast And Secure Access',
    'sideText' => 'Use your verified business account to continue setup, monitor usage, and manage your plan.',
])

@section('content')
    @if (session('status') === 'email-verified')
        <div class="flash success">Your email address has been verified successfully.</div>
    @endif

    <form method="POST" action="{{ route('login.submit') }}" class="form">
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

        <div class="field">
            <label for="password">Password</label>
            <div class="password-wrap">
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="input"
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                >
                <button type="button" class="toggle-pass" data-password-toggle="password">Show</button>
            </div>
        </div>

        <button type="submit" class="submit">Login</button>
    </form>

    <p class="helper">
        <a href="{{ route('password.request') }}">Forgot password?</a>
        |
        New here? <a href="{{ route('register') }}">Create account</a>
    </p>
@endsection
