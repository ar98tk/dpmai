@extends('business.auth.layout', [
    'title' => 'Reset Password',
    'pill' => 'Set New Password',
    'heading' => 'Reset Your Password',
    'subtitle' => 'Create a new password for your business account.',
    'sideTitle' => 'Secure Your Account',
    'sideText' => 'Choose a password with at least 8 characters and avoid reusing old passwords.',
])

@section('content')
    <form method="POST" action="{{ route('password.update') }}" class="form">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="field">
            <label for="email">Email Address</label>
            <input
                id="email"
                name="email"
                type="email"
                class="input"
                value="{{ old('email', $email) }}"
                required
                autocomplete="email"
                placeholder="you@example.com"
            >
        </div>

        <div class="field-row">
            <div class="field">
                <label for="password">New Password</label>
                <div class="password-wrap">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="input"
                        required
                        autocomplete="new-password"
                        placeholder="Minimum 8 characters"
                    >
                    <button type="button" class="toggle-pass" data-password-toggle="password">Show</button>
                </div>
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm Password</label>
                <div class="password-wrap">
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        class="input"
                        required
                        autocomplete="new-password"
                        placeholder="Repeat password"
                    >
                    <button type="button" class="toggle-pass" data-password-toggle="password_confirmation">Show</button>
                </div>
            </div>
        </div>

        <button type="submit" class="submit">Reset Password</button>
    </form>

    <p class="helper">
        Back to <a href="{{ route('login') }}">login</a>
    </p>
@endsection
