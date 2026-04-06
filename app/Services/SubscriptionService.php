<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function createForBusiness(Business $business, int $planId, Carbon|string|null $endDate = null): Subscription
    {
        return DB::transaction(function () use ($business, $planId, $endDate) {
            $resolvedEndDate = $endDate ? Carbon::parse($endDate) : now()->addDays(30);

            Subscription::query()
                ->where('business_id', $business->id)
                ->where('status', 'active')
                ->update([
                    'status' => 'expired',
                ]);

            return Subscription::query()->create([
                'business_id' => $business->id,
                'plan_id' => $planId,
                'start_date' => now(),
                'end_date' => $resolvedEndDate,
                'status' => 'active',
            ]);
        });
    }
}
