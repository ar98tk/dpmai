<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\WhatsAppInstance;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        $tokenRate = (float) config('billing.token_rate', 0.000002);

        $selectedMonth = $this->resolveMonth((string) $request->query('month', ''));
        $rangeStart = $selectedMonth->startOfMonth();
        $rangeEnd = $rangeStart->addMonth();

        $businessId = $this->resolvePositiveInt($request->query('business_id'));
        $instanceId = $this->resolvePositiveInt($request->query('instance_id'));

        $businesses = Business::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        $instances = WhatsAppInstance::query()
            ->select(['id', 'business_id', 'name'])
            ->when($businessId, fn ($query) => $query->where('business_id', $businessId))
            ->orderBy('name')
            ->get();

        if ($instanceId && ! $instances->contains('id', $instanceId)) {
            $instanceId = null;
        }

        $selectedInstance = $instanceId
            ? $instances->firstWhere('id', $instanceId)
            : null;

        $baseQuery = DB::table('messages as m')
            ->join('conversations as c', 'c.id', '=', 'm.conversation_id')
            ->join('whatsapp_instances as wi', 'wi.id', '=', 'c.instance_id')
            ->join('businesses as b', 'b.id', '=', 'c.business_id')
            ->where('m.created_at', '>=', $rangeStart->toDateTimeString())
            ->where('m.created_at', '<', $rangeEnd->toDateTimeString())
            ->when($businessId, fn ($query) => $query->where('c.business_id', $businessId))
            ->when($instanceId, fn ($query) => $query->where('c.instance_id', $instanceId));

        $summary = (clone $baseQuery)
            ->selectRaw(
                '
                COALESCE(SUM(m.total_tokens), 0) as total_tokens_used,
                COUNT(m.id) as total_messages,
                COUNT(DISTINCT c.id) as total_conversations
                '
            )
            ->first();

        $totalTokensUsed = (int) ($summary->total_tokens_used ?? 0);
        $totalMessages = (int) ($summary->total_messages ?? 0);
        $totalConversations = (int) ($summary->total_conversations ?? 0);
        $estimatedCost = round($totalTokensUsed * $tokenRate, 6);

        $groupByInstance = $instanceId !== null || $businessId !== null;

        $detailedUsage = $groupByInstance
            ? (clone $baseQuery)
                ->selectRaw(
                    '
                    wi.name as name,
                    COALESCE(SUM(m.total_tokens), 0) as total_tokens,
                    COUNT(m.id) as messages_count,
                    (COALESCE(SUM(m.total_tokens), 0) * ?) as estimated_cost
                    ',
                    [$tokenRate]
                )
                ->groupBy('wi.id', 'wi.name')
                ->orderByDesc('total_tokens')
                ->get()
            : (clone $baseQuery)
                ->selectRaw(
                    '
                    b.name as name,
                    COALESCE(SUM(m.total_tokens), 0) as total_tokens,
                    COUNT(m.id) as messages_count,
                    (COALESCE(SUM(m.total_tokens), 0) * ?) as estimated_cost
                    ',
                    [$tokenRate]
                )
                ->groupBy('b.id', 'b.name')
                ->orderByDesc('total_tokens')
                ->get();

        $tokensPerDayRows = (clone $baseQuery)
            ->selectRaw('DATE(m.created_at) as usage_date, COALESCE(SUM(m.total_tokens), 0) as total_tokens')
            ->groupByRaw('DATE(m.created_at)')
            ->orderByRaw('DATE(m.created_at) ASC')
            ->get();

        $tokensPerDayChart = [
            'labels' => $tokensPerDayRows->pluck('usage_date')->map(fn ($date) => (string) $date)->values()->all(),
            'data' => $tokensPerDayRows->pluck('total_tokens')->map(fn ($tokens) => (int) $tokens)->values()->all(),
        ];

        $instanceUsageSummary = null;
        $phoneUsageRows = collect();

        if ($instanceId !== null) {
            $instanceUsageSummaryRow = (clone $baseQuery)
                ->selectRaw(
                    '
                    COALESCE(SUM(m.total_tokens), 0) as total_tokens,
                    COUNT(m.id) as messages_count,
                    (COALESCE(SUM(m.total_tokens), 0) * ?) as estimated_cost
                    ',
                    [$tokenRate]
                )
                ->first();

            $instanceUsageSummary = [
                'total_tokens' => (int) ($instanceUsageSummaryRow->total_tokens ?? 0),
                'messages_count' => (int) ($instanceUsageSummaryRow->messages_count ?? 0),
                'estimated_cost' => round((float) ($instanceUsageSummaryRow->estimated_cost ?? 0), 6),
            ];

            $phoneUsageRows = (clone $baseQuery)
                ->selectRaw(
                    '
                    c.phone as phone,
                    COALESCE(SUM(m.total_tokens), 0) as total_tokens,
                    COUNT(m.id) as messages_count,
                    (COALESCE(SUM(m.total_tokens), 0) * ?) as estimated_cost
                    ',
                    [$tokenRate]
                )
                ->groupBy('c.phone')
                ->orderByDesc('total_tokens')
                ->get();
        }

        return view('admin.billing.index', [
            'businesses' => $businesses,
            'instances' => $instances,
            'selectedInstance' => $selectedInstance,
            'selectedBusinessId' => $businessId,
            'selectedInstanceId' => $instanceId,
            'selectedMonth' => $rangeStart->format('Y-m'),
            'totalTokensUsed' => $totalTokensUsed,
            'estimatedCost' => $estimatedCost,
            'totalMessages' => $totalMessages,
            'totalConversations' => $totalConversations,
            'detailedUsage' => $detailedUsage,
            'tokensPerDayChart' => $tokensPerDayChart,
            'groupByInstance' => $groupByInstance,
            'instanceUsageSummary' => $instanceUsageSummary,
            'phoneUsageRows' => $phoneUsageRows,
        ]);
    }

    private function resolveMonth(string $rawMonth): CarbonImmutable
    {
        if (preg_match('/^\d{4}-\d{2}$/', $rawMonth) === 1) {
            try {
                return CarbonImmutable::createFromFormat('Y-m', $rawMonth)->startOfMonth();
            } catch (\Throwable $exception) {
            }
        }

        return CarbonImmutable::now()->startOfMonth();
    }

    private function resolvePositiveInt(mixed $value): ?int
    {
        $normalized = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $normalized === false ? null : (int) $normalized;
    }
}
