<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'openAiApiKey' => (string) getSetting('openai_api_key', ''),
            'costPerToken' => (string) getSetting('cost_per_token', '0.000002'),
            'defaultDailyTokenLimit' => (string) getSetting('default_daily_token_limit', '0'),
            'defaultMonthlyTokenLimit' => (string) getSetting('default_monthly_token_limit', '0'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'openai_api_key' => ['nullable', 'string', 'max:500'],
            'cost_per_token' => ['required', 'numeric', 'min:0'],
            'default_daily_token_limit' => ['required', 'integer', 'min:0'],
            'default_monthly_token_limit' => ['required', 'integer', 'min:0'],
        ]);

        $settingsPayload = [
            'openai_api_key' => (string) ($validated['openai_api_key'] ?? ''),
            'cost_per_token' => (string) $validated['cost_per_token'],
            'default_daily_token_limit' => (string) $validated['default_daily_token_limit'],
            'default_monthly_token_limit' => (string) $validated['default_monthly_token_limit'],
        ];

        foreach ($settingsPayload as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );

            Cache::forget('setting:'.$key);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'System settings updated successfully.');
    }
}
