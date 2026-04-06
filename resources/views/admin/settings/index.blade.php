@extends('admin.layout.admin-layout')

@section('title', 'System Settings')
@section('page-title', 'System Settings')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-9">
            <div class="card">
                <div class="card-body py-10 px-10">
                    <form method="POST" action="{{ route('admin.settings.store') }}">
                        @csrf

                        <div class="mb-8">
                            <label class="form-label">OpenAI API Key</label>
                            <input
                                type="text"
                                name="openai_api_key"
                                value="{{ old('openai_api_key', $openAiApiKey) }}"
                                class="form-control form-control-solid"
                                autocomplete="off"
                            />
                        </div>

                        <div class="mb-8">
                            <label class="form-label required">Cost per Token</label>
                            <input
                                type="number"
                                name="cost_per_token"
                                step="0.00000001"
                                min="0"
                                value="{{ old('cost_per_token', $costPerToken) }}"
                                class="form-control form-control-solid"
                                required
                            />
                        </div>

                        <div class="mb-8">
                            <label class="form-label required">Default Daily Token Limit</label>
                            <input
                                type="number"
                                name="default_daily_token_limit"
                                min="0"
                                value="{{ old('default_daily_token_limit', $defaultDailyTokenLimit) }}"
                                class="form-control form-control-solid"
                                required
                            />
                        </div>

                        <div class="mb-10">
                            <label class="form-label required">Default Monthly Token Limit</label>
                            <input
                                type="number"
                                name="default_monthly_token_limit"
                                min="0"
                                value="{{ old('default_monthly_token_limit', $defaultMonthlyTokenLimit) }}"
                                class="form-control form-control-solid"
                                required
                            />
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
