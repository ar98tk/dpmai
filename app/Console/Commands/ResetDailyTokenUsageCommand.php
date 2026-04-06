<?php

namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Command;

class ResetDailyTokenUsageCommand extends Command
{
    protected $signature = 'tokens:reset-daily';

    protected $description = 'Reset daily token usage counters for all businesses';

    public function handle(): int
    {
        $updatedRows = Business::query()
            ->where('daily_tokens_used', '>', 0)
            ->update([
                'daily_tokens_used' => 0,
            ]);

        $this->info("Daily token usage reset for {$updatedRows} businesses.");

        return self::SUCCESS;
    }
}
