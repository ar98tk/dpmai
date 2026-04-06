@extends('admin.layout.admin-layout')

@section('title', 'Billing & Usage')
@section('page-title', 'Billing & Usage')

@section('content')
    <div class="card mb-7">
        <div class="card-body py-6">
            <form method="GET" action="{{ route('admin.billing.index') }}">
                <div class="row g-5 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Select Business</label>
                        <select name="business_id" class="form-select form-select-solid" data-control="select2" data-hide-search="true">
                            <option value="">All Businesses</option>
                            @foreach ($businesses as $business)
                                <option value="{{ $business->id }}" {{ (int) $selectedBusinessId === (int) $business->id ? 'selected' : '' }}>
                                    {{ $business->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Select Instance (Optional)</label>
                        <select name="instance_id" class="form-select form-select-solid" data-control="select2" data-hide-search="true">
                            <option value="">All Instances</option>
                            @foreach ($instances as $instance)
                                <option value="{{ $instance->id }}" {{ (int) $selectedInstanceId === (int) $instance->id ? 'selected' : '' }}>
                                    {{ $instance->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Select Month</label>
                        <input type="month" name="month" value="{{ $selectedMonth }}" class="form-control form-control-solid" />
                    </div>

                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-primary">Apply</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-6 g-xl-9 mb-7">
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body py-8">
                    <div class="text-muted fs-7 fw-semibold mb-2">Total Tokens Used</div>
                    <div class="fs-2hx fw-bold text-gray-900">{{ number_format($totalTokensUsed) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body py-8">
                    <div class="text-muted fs-7 fw-semibold mb-2">Estimated Cost</div>
                    <div class="fs-2hx fw-bold text-gray-900">${{ number_format($estimatedCost, 6) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body py-8">
                    <div class="text-muted fs-7 fw-semibold mb-2">Total Messages</div>
                    <div class="fs-2hx fw-bold text-gray-900">{{ number_format($totalMessages) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body py-8">
                    <div class="text-muted fs-7 fw-semibold mb-2">Total Conversations</div>
                    <div class="fs-2hx fw-bold text-gray-900">{{ number_format($totalConversations) }}</div>
                </div>
            </div>
        </div>
    </div>

    @if ($selectedInstanceId && $instanceUsageSummary)
        <div class="row g-6 g-xl-9 mb-7">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body py-8">
                        <div class="text-muted fs-7 fw-semibold mb-2">Instance Tokens Used ({{ $selectedInstance?->name }})</div>
                        <div class="fs-2hx fw-bold text-gray-900">{{ number_format((int) $instanceUsageSummary['total_tokens']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body py-8">
                        <div class="text-muted fs-7 fw-semibold mb-2">Instance Messages Count</div>
                        <div class="fs-2hx fw-bold text-gray-900">{{ number_format((int) $instanceUsageSummary['messages_count']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body py-8">
                        <div class="text-muted fs-7 fw-semibold mb-2">Instance Cost</div>
                        <div class="fs-2hx fw-bold text-gray-900">${{ number_format((float) $instanceUsageSummary['estimated_cost'], 6) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-6 g-xl-9 mb-7">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <h3 class="fw-bold m-0">Phone Usage (Selected Instance)</h3>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-4">
                                <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Phone Number</th>
                                    <th>Tokens Used</th>
                                    <th>Messages Count</th>
                                    <th>Cost</th>
                                </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-semibold">
                                @forelse ($phoneUsageRows as $phoneUsage)
                                    <tr>
                                        <td>{{ $phoneUsage->phone }}</td>
                                        <td>{{ number_format((int) $phoneUsage->total_tokens) }}</td>
                                        <td>{{ number_format((int) $phoneUsage->messages_count) }}</td>
                                        <td>${{ number_format((float) $phoneUsage->estimated_cost, 6) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-8">No phone usage data for this instance and period.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-6 g-xl-9 mb-7">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h3 class="fw-bold m-0">Detailed Usage</h3>
                    </div>
                </div>
                <div class="card-body py-4">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-4">
                            <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>Name ({{ $groupByInstance ? 'Instance' : 'Business' }})</th>
                                <th>Total Tokens</th>
                                <th>Messages Count</th>
                                <th>Estimated Cost</th>
                            </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                            @forelse ($detailedUsage as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ number_format((int) $item->total_tokens) }}</td>
                                    <td>{{ number_format((int) $item->messages_count) }}</td>
                                    <td>${{ number_format((float) $item->estimated_cost, 6) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-8">No usage data for this period.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-6 g-xl-9 mb-9">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h3 class="fw-bold m-0">Tokens Usage Per Day</h3>
                    </div>
                </div>
                <div class="card-body pt-2 pb-6">
                    <div style="height: 320px;">
                        <canvas id="billing_tokens_chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var chartPayload = @json($tokensPerDayChart);
            var canvas = document.getElementById('billing_tokens_chart');

            if (!canvas || typeof Chart === 'undefined') {
                return;
            }

            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: chartPayload.labels || [],
                    datasets: [{
                        data: chartPayload.data || [],
                        borderColor: 'rgba(54, 153, 255, 1)',
                        backgroundColor: 'rgba(54, 153, 255, 0.12)',
                        borderWidth: 2,
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            });
        });
    </script>
@endpush
