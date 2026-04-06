<?php

namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Command;

class ResetMonthlyTokenUsageCommand extends Command
{
    protected $signature = 'tokens:reset-monthly';

    protected $description = 'Reset monthly token usage counters for all businesses';

    public function handle(): int
    {
        $updatedRows = Business::query()
            ->where('monthly_tokens_used', '>', 0)
            ->update([
                'monthly_tokens_used' => 0,
            ]);

        $this->info("Monthly token usage reset for {$updatedRows} businesses.");

        return self::SUCCESS;
    }
}
