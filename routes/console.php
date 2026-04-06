<?php

use App\Console\Commands\ResetDailyTokenUsageCommand;
use App\Console\Commands\ResetMonthlyTokenUsageCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(ResetDailyTokenUsageCommand::class)->dailyAt('00:00');
Schedule::command(ResetMonthlyTokenUsageCommand::class)->monthlyOn(1, '00:05');
