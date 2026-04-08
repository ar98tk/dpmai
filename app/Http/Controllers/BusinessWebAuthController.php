<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Plan;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BusinessWebAuthController extends Controller
{
    private const REGISTRATION_SESSION_KEY = 'business.registration.pending';
    private SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function showLogin(): View
    {
        return view('business.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('business')->attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Invalid credentials.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        /** @var Business $business */
        $business = Auth::guard('business')->user();

        if ($business->status !== 'active') {
            Auth::guard('business')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Your account is suspended. Please contact support.'])
                ->onlyInput('email');
        }

        if (! $business->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return redirect()->intended(route('home'));
    }

    public function showRegister(Request $request): View
    {
        $plans = Plan::query()
            ->orderBy('price')
            ->get(['id', 'name', 'price', 'max_instances', 'daily_token_limit', 'monthly_token_limit', 'features']);

        $requestedPlanId = $request->integer('plan');
        $selectedPlanId = $plans->contains('id', $requestedPlanId) ? $requestedPlanId : null;

        return view('business.auth.register', [
            'plans' => $plans,
            'selectedPlanId' => $selectedPlanId,
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:businesses,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'plan_id' => ['nullable', 'integer', 'exists:plans,id'],
        ]);

        $selectedPlan = null;

        if (! empty($validated['plan_id'])) {
            $selectedPlan = Plan::query()->find((int) $validated['plan_id']);
        }

        if ($selectedPlan && (float) $selectedPlan->price > 0) {
            return $this->startPaidRegistration($request, $validated, $selectedPlan);
        }

        $business = $this->createBusinessAndSubscription($validated, $selectedPlan);

        Auth::guard('business')->login($business);
        $request->session()->regenerate();

        $business->sendEmailVerificationNotification();

        return redirect()
            ->route('verification.notice')
            ->with('status', 'verification-link-sent');
    }

    public function completePaidRegistration(Request $request): RedirectResponse
    {
        $pending = $request->session()->get(self::REGISTRATION_SESSION_KEY);
        $sessionId = (string) $request->query('session_id', '');

        if (! is_array($pending) || $sessionId === '') {
            return redirect()
                ->route('register')
                ->withErrors(['plan_id' => 'Your checkout session expired. Please try again.']);
        }

        if (($pending['checkout_session_id'] ?? null) !== $sessionId) {
            return redirect()
                ->route('register')
                ->withErrors(['plan_id' => 'Invalid checkout session. Please try again.']);
        }

        $checkoutSession = $this->fetchStripeCheckoutSession($sessionId);

        if (! $checkoutSession || ($checkoutSession['payment_status'] ?? null) !== 'paid') {
            return redirect()
                ->route('register')
                ->withErrors(['plan_id' => 'Payment was not completed. Please try again.']);
        }

        $planId = (int) ($pending['plan_id'] ?? 0);
        $plan = Plan::query()->find($planId);

        if (! $plan || (float) $plan->price <= 0) {
            $request->session()->forget(self::REGISTRATION_SESSION_KEY);

            return redirect()
                ->route('register')
                ->withErrors(['plan_id' => 'Selected plan is no longer available.']);
        }

        if (Business::query()->where('email', $pending['email'])->exists()) {
            $request->session()->forget(self::REGISTRATION_SESSION_KEY);

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'This email is already registered. Please login.']);
        }

        $business = $this->createBusinessAndSubscription([
            'name' => $pending['name'],
            'email' => $pending['email'],
            'phone' => $pending['phone'] ?? null,
            'password' => $pending['password_hash'],
        ], $plan);

        $request->session()->forget(self::REGISTRATION_SESSION_KEY);

        Auth::guard('business')->login($business);
        $request->session()->regenerate();

        $business->sendEmailVerificationNotification();

        return redirect()
            ->route('verification.notice')
            ->with('status', 'verification-link-sent');
    }

    public function showEmailVerificationNotice(Request $request)
    {
        /** @var Business $business */
        $business = $request->user('business');

        if ($business->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        return view('business.auth.verify-email');
    }

    public function verifyEmail(Request $request, string $id, string $hash): RedirectResponse
    {
        /** @var Business $business */
        $business = $request->user('business');

        if ((string) $business->getKey() !== $id || ! hash_equals((string) $hash, sha1($business->getEmailForVerification()))) {
            abort(403);
        }

        if (! $business->hasVerifiedEmail()) {
            $business->markEmailAsVerified();
        }

        return redirect()
            ->route('home')
            ->with('status', 'email-verified');
    }

    public function sendEmailVerificationNotification(Request $request): RedirectResponse
    {
        /** @var Business $business */
        $business = $request->user('business');

        if ($business->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        $business->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    public function showForgotPassword(): View
    {
        return view('business.auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::broker('businesses')->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()
            ->withErrors(['email' => __($status)])
            ->onlyInput('email');
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('business.auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::broker('businesses')->reset(
            $validated,
            function (Business $business, string $password): void {
                $business->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('business')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function startPaidRegistration(Request $request, array $validated, Plan $plan): RedirectResponse
    {
        $checkout = $this->createStripeCheckoutSession($validated, $plan);

        if (! $checkout) {
            return back()
                ->withErrors(['plan_id' => 'Stripe checkout is currently unavailable. Please try again later.'])
                ->withInput();
        }

        $request->session()->put(self::REGISTRATION_SESSION_KEY, [
            'checkout_session_id' => $checkout['id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password_hash' => Hash::make($validated['password']),
            'plan_id' => (int) $plan->id,
        ]);

        return redirect()->away($checkout['url']);
    }

    private function createStripeCheckoutSession(array $validated, Plan $plan): ?array
    {
        $stripeSecret = (string) config('services.stripe.secret', '');

        if ($stripeSecret === '') {
            return null;
        }

        $amount = (int) round(((float) $plan->price) * 100);
        if ($amount <= 0) {
            return null;
        }

        $response = Http::asForm()
            ->withBasicAuth($stripeSecret, '')
            ->timeout(20)
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'payment',
                'success_url' => route('register.checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('register', ['plan' => $plan->id]),
                'customer_email' => $validated['email'],
                'line_items[0][price_data][currency]' => 'usd',
                'line_items[0][price_data][unit_amount]' => $amount,
                'line_items[0][price_data][product_data][name]' => $plan->name.' Plan',
                'line_items[0][quantity]' => 1,
                'metadata[plan_id]' => (string) $plan->id,
                'metadata[email]' => $validated['email'],
            ]);

        if ($response->failed()) {
            return null;
        }

        $payload = $response->json();

        if (! is_array($payload) || empty($payload['id']) || empty($payload['url'])) {
            return null;
        }

        return [
            'id' => (string) $payload['id'],
            'url' => (string) $payload['url'],
        ];
    }

    private function fetchStripeCheckoutSession(string $sessionId): ?array
    {
        $stripeSecret = (string) config('services.stripe.secret', '');
        if ($stripeSecret === '') {
            return null;
        }

        $response = Http::withBasicAuth($stripeSecret, '')
            ->timeout(20)
            ->get('https://api.stripe.com/v1/checkout/sessions/'.$sessionId);

        if ($response->failed()) {
            return null;
        }

        $payload = $response->json();

        return is_array($payload) ? $payload : null;
    }

    private function createBusinessAndSubscription(array $validated, ?Plan $selectedPlan): Business
    {
        return DB::transaction(function () use ($validated, $selectedPlan): Business {
            $business = Business::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'phone' => $validated['phone'] ?? null,
                'status' => 'active',
            ]);

            if ($selectedPlan) {
                $this->subscriptionService->createForBusiness($business, (int) $selectedPlan->id);
            }

            return $business;
        });
    }
}
