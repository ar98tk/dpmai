@extends('business.auth.layout', [
    'title' => 'Register',
    'pill' => 'Start Free Trial',
    'heading' => 'Create Business Account',
    'subtitle' => 'Start on the free plan instantly, or choose a paid plan and continue to Stripe checkout.',
    'sideTitle' => 'Launch In Minutes',
    'sideText' => 'Connect WhatsApp, set your AI behavior, and start converting conversations automatically.',
])

@php
    $oldPlanId = old('plan_id');
    $selectedPlan = $oldPlanId !== null ? (int) $oldPlanId : ($selectedPlanId ?? null);
@endphp

@section('content')
    <form method="POST" action="{{ route('register.submit') }}" class="form">
        @csrf

        <div class="field">
            <label for="name">Business Name</label>
            <input
                id="name"
                name="name"
                type="text"
                class="input"
                value="{{ old('name') }}"
                required
                autocomplete="organization"
                placeholder="Your business name"
            >
        </div>

        <div class="field-row">
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
                <label for="phone">Phone (Optional)</label>
                <input
                    id="phone"
                    name="phone"
                    type="text"
                    class="input"
                    value="{{ old('phone') }}"
                    autocomplete="tel"
                    placeholder="+1 000 000 0000"
                >
            </div>
        </div>

        <div class="field-row">
            <div class="field">
                <label for="password">Password</label>
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

        <div class="field">
            <label for="plan_id">Plan</label>
            <select id="plan_id" name="plan_id" class="select">
                <option value="" {{ $selectedPlan ? '' : 'selected' }}>Free Plan (No payment required)</option>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}" {{ $selectedPlan === $plan->id ? 'selected' : '' }}>
                        {{ $plan->name }} - ${{ number_format((float) $plan->price, 2) }}/month
                    </option>
                @endforeach
            </select>
        </div>

        <p class="helper">
            If you choose a paid plan, after submitting this form you will be redirected to Stripe to complete payment.
        </p>

        <button type="submit" class="submit">Create Account</button>
    </form>

    <p class="helper">
        Already have an account? <a href="{{ route('login') }}">Login</a>
    </p>
@endsection
