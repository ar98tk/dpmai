<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\WhatsAppInstance;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusinessController extends Controller
{
    private const FREE_DAILY_TOKEN_LIMIT = 1000;
    private const FREE_MONTHLY_TOKEN_LIMIT = 5000;

    public function index(): View
    {
        $businesses = Business::query()
            ->withCount('whatsappInstances')
            ->latest()
            ->get();

        return view('admin.businesses.index', compact('businesses'));
    }

    public function create(): View
    {
        $plans = Plan::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.businesses.create', [
            'plans' => $plans,
        ]);
    }

    public function show(Business $business): View
    {
        $activeSubscription = $business->getActiveSubscription();
        $activePlan = $activeSubscription ? $activeSubscription->plan : null;

        $instancesCount = WhatsAppInstance::query()
            ->where('business_id', $business->id)
            ->count();

        $maxInstances = (int) ($activePlan->max_instances ?? 0);
        $instancesLimitReached = $activeSubscription !== null && $instancesCount >= $maxInstances;

        $dailyTokensUsed = (int) DB::table('messages')
            ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
            ->where('conversations.business_id', $business->id)
            ->where('messages.created_at', '>=', now()->startOfDay())
            ->sum('messages.total_tokens');

        $monthlyTokensUsed = (int) DB::table('messages')
            ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
            ->where('conversations.business_id', $business->id)
            ->where('messages.created_at', '>=', now()->startOfMonth())
            ->sum('messages.total_tokens');

        [$dailyLimit, $monthlyLimit] = $this->resolveTokenLimits($activePlan);
        $dailyLimitReached = $dailyLimit > 0 && $dailyTokensUsed >= $dailyLimit;
        $monthlyLimitReached = $monthlyLimit > 0 && $monthlyTokensUsed >= $monthlyLimit;
        $aiLimitReached = $dailyLimitReached || $monthlyLimitReached;

        if ((int) $business->daily_tokens_used !== $dailyTokensUsed || (int) $business->monthly_tokens_used !== $monthlyTokensUsed) {
            $business->forceFill([
                'daily_tokens_used' => $dailyTokensUsed,
                'monthly_tokens_used' => $monthlyTokensUsed,
            ])->saveQuietly();
        }

        $plans = Plan::query()
            ->orderBy('name')
            ->get(['id', 'name', 'daily_token_limit', 'monthly_token_limit', 'max_instances']);

        $instances = WhatsAppInstance::query()
            ->where('business_id', $business->id)
            ->withCount('leads')
            ->latest()
            ->get();

        $now = Carbon::now();

        $subscriptions = Subscription::query()
            ->where('business_id', $business->id)
            ->with('plan:id,name,price')
            ->latest('id')
            ->get()
            ->map(function (Subscription $subscription) use ($now) {
                $isExpiredByDate = $subscription->end_date !== null && $subscription->end_date->lte($now);

                $effectiveStatus = $subscription->status;
                if ($effectiveStatus === 'active' && $isExpiredByDate) {
                    $effectiveStatus = 'expired';
                }

                $remainingText = '-';
                if ($subscription->end_date) {
                    $remainingText = $this->formatRemainingTime($subscription->end_date, $now);
                }

                $subscription->setAttribute('effective_status', $effectiveStatus);
                $subscription->setAttribute('remaining_text', $remainingText);

                return $subscription;
            });

        return view('admin.businesses.show', [
            'business' => $business,
            'plans' => $plans,
            'instances' => $instances,
            'subscriptions' => $subscriptions,
            'activeSubscription' => $activeSubscription,
            'activePlan' => $activePlan,
            'instancesCount' => $instancesCount,
            'maxInstances' => $maxInstances,
            'instancesLimitReached' => $instancesLimitReached,
            'dailyTokensUsed' => $dailyTokensUsed,
            'monthlyTokensUsed' => $monthlyTokensUsed,
            'dailyTokenLimit' => $dailyLimit,
            'monthlyTokenLimit' => $monthlyLimit,
            'aiLimitReached' => $aiLimitReached,
            'aiLimitReachedMessage' => 'AI paused: daily or monthly token limit reached for this business.',
        ]);
    }

    public function store(Request $request, SubscriptionService $subscriptionService): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:businesses,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,suspended'],
            'plan_id' => ['required', 'exists:plans,id'],
            'plan_expiry_date' => ['nullable', 'date', 'after:now'],
        ]);

        DB::transaction(function () use ($data, $subscriptionService) {
            $business = Business::query()->create(Arr::except($data, ['plan_id', 'plan_expiry_date']));
            $subscriptionService->createForBusiness(
                $business,
                (int) $data['plan_id'],
                $data['plan_expiry_date'] ?? null
            );
        });

        return redirect()
            ->route('admin.businesses.index')
            ->with('success', 'Business created successfully.');
    }

    public function update(Request $request, Business $business, SubscriptionService $subscriptionService): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('businesses', 'email')->ignore($business->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,suspended'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'plan_expiry_date' => ['nullable', 'date', 'after:now'],
        ]);

        DB::transaction(function () use ($business, $data, $subscriptionService): void {
            $businessPayload = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'],
            ];

            if (! empty($data['password'])) {
                $businessPayload['password'] = $data['password'];
            }

            $business->update($businessPayload);

            if (array_key_exists('plan_id', $data)) {
                if ($data['plan_id']) {
                    $activeSubscription = $business->getActiveSubscription();
                    $targetPlanId = (int) $data['plan_id'];
                    $targetEndDate = isset($data['plan_expiry_date'])
                        ? Carbon::parse($data['plan_expiry_date'])
                        : null;

                    if ($activeSubscription && (int) $activeSubscription->plan_id === $targetPlanId) {
                        if ($targetEndDate) {
                            $activeSubscription->update([
                                'end_date' => $targetEndDate,
                            ]);
                        }
                    } else {
                        $subscriptionService->createForBusiness($business, $targetPlanId, $targetEndDate);
                    }
                } else {
                    Subscription::query()
                        ->where('business_id', $business->id)
                        ->where('status', 'active')
                        ->update([
                            'status' => 'expired',
                        ]);
                }
            }
        });

        return redirect()
            ->route('admin.businesses.show', $business)
            ->with('success', 'Business updated successfully.');
    }

    private function formatRemainingTime(Carbon $endDate, Carbon $now): string
    {
        $seconds = $now->diffInSeconds($endDate, false);

        if ($seconds <= 0) {
            return 'Expired';
        }

        $days = intdiv($seconds, 86400);
        $seconds %= 86400;

        $hours = intdiv($seconds, 3600);
        $seconds %= 3600;

        $minutes = intdiv($seconds, 60);

        $parts = [];

        if ($days > 0) {
            $parts[] = $days.' '.($days === 1 ? 'Day' : 'Days');
        }

        if ($hours > 0 || $days > 0) {
            $parts[] = $hours.' '.($hours === 1 ? 'Hour' : 'Hours');
        }

        if ($minutes > 0 || empty($parts)) {
            $parts[] = $minutes.' '.($minutes === 1 ? 'Minute' : 'Minutes');
        }

        return implode(' ', $parts);
    }

    private function resolveTokenLimits(?Plan $plan): array
    {
        if (! $plan) {
            return [self::FREE_DAILY_TOKEN_LIMIT, self::FREE_MONTHLY_TOKEN_LIMIT];
        }

        return [
            (int) $plan->daily_token_limit,
            (int) $plan->monthly_token_limit,
        ];
    }
}
