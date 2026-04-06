<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        $now = Carbon::now();

        $subscriptions = Subscription::query()
            ->with([
                'business:id,name,status',
                'plan:id,name,price,max_instances,daily_token_limit,monthly_token_limit',
            ])
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

        return view('admin.subscriptions.index', [
            'subscriptions' => $subscriptions,
        ]);
    }

    public function invoice(Subscription $subscription)
    {
        $subscription->load([
            'business',
            'plan',
        ]);

        $logoDataUri = $this->getLogoDataUri();

        $html = view('admin.subscriptions.invoice', [
            'subscription' => $subscription,
            'business' => $subscription->business,
            'plan' => $subscription->plan,
            'logoDataUri' => $logoDataUri,
            'generatedAt' => now(),
        ])->render();

        $fileName = 'subscription-invoice-'.$subscription->id.'.html';

        return Response::make($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
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

    private function getLogoDataUri(): ?string
    {
        $logoPath = public_path('dpm-logo.png');

        if (! is_file($logoPath)) {
            return null;
        }

        $contents = file_get_contents($logoPath);
        if ($contents === false) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode($contents);
    }
}
