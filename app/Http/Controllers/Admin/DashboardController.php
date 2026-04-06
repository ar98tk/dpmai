<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Lead;
use App\Models\Message;
use App\Models\WhatsAppInstance;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const INPUT_TOKEN_PRICE_PER_MILLION = 0.15;
    private const OUTPUT_TOKEN_PRICE_PER_MILLION = 0.60;

    public function index(): View
    {
        $today = Carbon::today();
        $startDate = Carbon::today()->subDays(6)->toDateString();
        $endDate = Carbon::today()->toDateString();

        $messagesToday = Message::query()
            ->whereDate('created_at', $today)
            ->count();

        $conversationsToday = Conversation::query()
            ->whereDate('updated_at', $today)
            ->count();

        $totalLeads = Lead::query()->count();

        $activeNumbers = WhatsAppInstance::query()
            ->where('status', 'connected')
            ->count();

        $messagesRows = DB::select(
            '
            WITH RECURSIVE days AS (
                SELECT DATE(?) AS day
                UNION ALL
                SELECT DATE_ADD(day, INTERVAL 1 DAY)
                FROM days
                WHERE day < DATE(?)
            )
            SELECT DATE_FORMAT(days.day, "%Y-%m-%d") AS label,
                   COALESCE(m.total, 0) AS total
            FROM days
            LEFT JOIN (
                SELECT DATE(created_at) AS day, COUNT(*) AS total
                FROM messages
                WHERE DATE(created_at) BETWEEN DATE(?) AND DATE(?)
                GROUP BY DATE(created_at)
            ) m ON m.day = days.day
            ORDER BY days.day ASC
            ',
            [$startDate, $endDate, $startDate, $endDate]
        );

        $conversationsRows = DB::select(
            '
            WITH RECURSIVE days AS (
                SELECT DATE(?) AS day
                UNION ALL
                SELECT DATE_ADD(day, INTERVAL 1 DAY)
                FROM days
                WHERE day < DATE(?)
            )
            SELECT DATE_FORMAT(days.day, "%Y-%m-%d") AS label,
                   COALESCE(c.total, 0) AS total
            FROM days
            LEFT JOIN (
                SELECT DATE(updated_at) AS day, COUNT(*) AS total
                FROM conversations
                WHERE DATE(updated_at) BETWEEN DATE(?) AND DATE(?)
                GROUP BY DATE(updated_at)
            ) c ON c.day = days.day
            ORDER BY days.day ASC
            ',
            [$startDate, $endDate, $startDate, $endDate]
        );

        $tokenRows = DB::select(
            '
            WITH RECURSIVE days AS (
                SELECT DATE(?) AS day
                UNION ALL
                SELECT DATE_ADD(day, INTERVAL 1 DAY)
                FROM days
                WHERE day < DATE(?)
            )
            SELECT DATE_FORMAT(days.day, "%Y-%m-%d") AS label,
                   COALESCE(t.total_tokens, 0) AS total_tokens,
                   COALESCE(
                       ((COALESCE(t.prompt_tokens, 0) / 1000000.0) * ?)
                       + ((COALESCE(t.completion_tokens, 0) / 1000000.0) * ?),
                       0
                   ) AS spending_usd
            FROM days
            LEFT JOIN (
                SELECT DATE(created_at) AS day,
                       SUM(COALESCE(total_tokens, 0)) AS total_tokens,
                       SUM(COALESCE(prompt_tokens, 0)) AS prompt_tokens,
                       SUM(COALESCE(completion_tokens, 0)) AS completion_tokens
                FROM messages
                WHERE DATE(created_at) BETWEEN DATE(?) AND DATE(?)
                GROUP BY DATE(created_at)
            ) t ON t.day = days.day
            ORDER BY days.day ASC
            '
            ,
            [
                $startDate,
                $endDate,
                self::INPUT_TOKEN_PRICE_PER_MILLION,
                self::OUTPUT_TOKEN_PRICE_PER_MILLION,
                $startDate,
                $endDate,
            ]
        );

        $messagesChart = [
            'labels' => collect($messagesRows)->pluck('label')->values()->all(),
            'data' => collect($messagesRows)->pluck('total')->map(fn ($count) => (int) $count)->values()->all(),
        ];

        $conversationsChart = [
            'labels' => collect($conversationsRows)->pluck('label')->values()->all(),
            'data' => collect($conversationsRows)->pluck('total')->map(fn ($count) => (int) $count)->values()->all(),
        ];

        $tokenUsageChart = [
            'labels' => collect($tokenRows)->pluck('label')->values()->all(),
            'data' => collect($tokenRows)->pluck('total_tokens')->map(fn ($count) => (int) $count)->values()->all(),
        ];

        $tokenSpendingChart = [
            'labels' => collect($tokenRows)->pluck('label')->values()->all(),
            'data' => collect($tokenRows)->pluck('spending_usd')->map(fn ($value) => round((float) $value, 6))->values()->all(),
        ];

        $messageCountsSubquery = DB::table('messages as m')
            ->join('conversations as c', 'c.id', '=', 'm.conversation_id')
            ->selectRaw(
                '
                c.instance_id,
                COUNT(*) as messages_count,
                SUM(COALESCE(m.total_tokens, 0)) as total_tokens,
                SUM(COALESCE(m.prompt_tokens, 0)) as prompt_tokens,
                SUM(COALESCE(m.completion_tokens, 0)) as completion_tokens
                '
            )
            ->groupBy('c.instance_id');

        $topActiveNumbers = DB::table('whatsapp_instances as wi')
            ->leftJoinSub($messageCountsSubquery, 'mc', function ($join): void {
                $join->on('mc.instance_id', '=', 'wi.id');
            })
            ->selectRaw(
                '
                wi.id,
                wi.name,
                wi.status,
                COALESCE(mc.messages_count, 0) as messages_count,
                COALESCE(mc.total_tokens, 0) as total_tokens,
                (
                    ((COALESCE(mc.prompt_tokens, 0) / 1000000.0) * ?)
                    + ((COALESCE(mc.completion_tokens, 0) / 1000000.0) * ?)
                ) as spending_usd
                ',
                [self::INPUT_TOKEN_PRICE_PER_MILLION, self::OUTPUT_TOKEN_PRICE_PER_MILLION]
            )
            ->orderByDesc('messages_count')
            ->limit(5)
            ->get();

        return view('admin.home', [
            'messagesToday' => $messagesToday,
            'conversationsToday' => $conversationsToday,
            'totalLeads' => $totalLeads,
            'activeNumbers' => $activeNumbers,
            'messagesChart' => $messagesChart,
            'conversationsChart' => $conversationsChart,
            'tokenUsageChart' => $tokenUsageChart,
            'tokenSpendingChart' => $tokenSpendingChart,
            'topActiveNumbers' => $topActiveNumbers,
        ]);
    }
}
