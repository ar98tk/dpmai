@extends('admin.layout.admin-layout')

@section('title', 'Dashboard')
@section('page-title', 'Home')

@section('content')
    <div class="row g-6 g-xl-9 mb-8">
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-center py-8">
                    <div class="text-muted fs-7 fw-semibold mb-2">Messages Today</div>
                    <div class="fs-2hx fw-bold text-gray-900">{{ number_format($messagesToday) }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-center py-8">
                    <div class="text-muted fs-7 fw-semibold mb-2">Conversations Today</div>
                    <div class="fs-2hx fw-bold text-gray-900">{{ number_format($conversationsToday) }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-center py-8">
                    <div class="text-muted fs-7 fw-semibold mb-2">Total Leads</div>
                    <div class="fs-2hx fw-bold text-gray-900">{{ number_format($totalLeads) }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-center py-8">
                    <div class="text-muted fs-7 fw-semibold mb-2">Active Numbers</div>
                    <div class="fs-2hx fw-bold text-gray-900">{{ number_format($activeNumbers) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-6 g-xl-9 mt-1">
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h3 class="fw-bold m-0">Messages Per Day (Last 7 Days)</h3>
                    </div>
                </div>
                <div class="card-body pt-2 pb-6">
                    <div style="height: 300px;">
                        <canvas id="messages_per_day_chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h3 class="fw-bold m-0">Conversations Per Day (Last 7 Days)</h3>
                    </div>
                </div>
                <div class="card-body pt-2 pb-6">
                    <div style="height: 300px;">
                        <canvas id="conversations_per_day_chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-6 g-xl-9 mt-1">
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h3 class="fw-bold m-0">Token Usage Per Week</h3>
                    </div>
                </div>
                <div class="card-body pt-2 pb-6">
                    <div style="height: 300px;">
                        <canvas id="token_usage_per_week_chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h3 class="fw-bold m-0">Token Spending Per Week ($)</h3>
                    </div>
                </div>
                <div class="card-body pt-2 pb-6">
                    <div style="height: 300px;">
                        <canvas id="token_spending_per_week_chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-6 g-xl-9 mt-1 mb-9">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h3 class="fw-bold m-0">Top Active Numbers</h3>
                    </div>
                </div>
                <div class="card-body py-4">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-4">
                            <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>Number Name</th>
                                <th>Messages Count</th>
                                <th>Tokens Used</th>
                                <th>Spending ($)</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                            @forelse ($topActiveNumbers as $activeNumber)
                                <tr>
                                    <td>{{ $activeNumber->name }}</td>
                                    <td>
                                        <span class="badge badge-light-info fw-bold px-3 py-2">{{ (int) $activeNumber->messages_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-light-primary fw-bold px-3 py-2">{{ number_format((int) $activeNumber->total_tokens) }}</span>
                                    </td>
                                    <td>${{ number_format((float) $activeNumber->spending_usd, 6) }}</td>
                                    <td>
                                        @if ($activeNumber->status === 'connected')
                                            <span class="badge badge-light-success fw-bold px-3 py-2">Connected</span>
                                        @elseif ($activeNumber->status === 'disconnected')
                                            <span class="badge badge-light-danger fw-bold px-3 py-2">Disconnected</span>
                                        @else
                                            <span class="badge badge-light-warning fw-bold px-3 py-2">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-8">No activity yet.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
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
            var messagesChartPayload = @json($messagesChart);
            var conversationsChartPayload = @json($conversationsChart);
            var tokenUsageChartPayload = @json($tokenUsageChart);
            var tokenSpendingChartPayload = @json($tokenSpendingChart);

            function renderLineChart(canvasId, payload, lineColor) {
                var canvas = document.getElementById(canvasId);
                if (!canvas || typeof Chart === 'undefined') {
                    return;
                }

                new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: payload.labels || [],
                        datasets: [{
                            data: payload.data || [],
                            borderColor: lineColor,
                            backgroundColor: lineColor.replace('1)', '0.12)'),
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
            }

            function renderMoneyLineChart(canvasId, payload, lineColor) {
                var canvas = document.getElementById(canvasId);
                if (!canvas || typeof Chart === 'undefined') {
                    return;
                }

                new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: payload.labels || [],
                        datasets: [{
                            data: payload.data || [],
                            borderColor: lineColor,
                            backgroundColor: lineColor.replace('1)', '0.12)'),
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
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return '$' + Number(context.raw || 0).toFixed(6);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function (value) {
                                        return '$' + Number(value).toFixed(4);
                                    }
                                }
                            }
                        }
                    }
                });
            }

            function renderBarChart(canvasId, payload, color) {
                var canvas = document.getElementById(canvasId);
                if (!canvas || typeof Chart === 'undefined') {
                    return;
                }

                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: payload.labels || [],
                        datasets: [{
                            data: payload.data || [],
                            backgroundColor: color.replace('1)', '0.65)'),
                            borderColor: color,
                            borderWidth: 1.5,
                            borderRadius: 6
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
            }

            function renderMoneyBarChart(canvasId, payload, color) {
                var canvas = document.getElementById(canvasId);
                if (!canvas || typeof Chart === 'undefined') {
                    return;
                }

                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: payload.labels || [],
                        datasets: [{
                            data: payload.data || [],
                            backgroundColor: color.replace('1)', '0.65)'),
                            borderColor: color,
                            borderWidth: 1.5,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return '$' + Number(context.raw || 0).toFixed(6);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function (value) {
                                        return '$' + Number(value).toFixed(4);
                                    }
                                }
                            }
                        }
                    }
                });
            }

            renderLineChart('messages_per_day_chart', messagesChartPayload, 'rgba(54, 153, 255, 1)');
            renderLineChart('conversations_per_day_chart', conversationsChartPayload, 'rgba(80, 205, 137, 1)');
            renderBarChart('token_usage_per_week_chart', tokenUsageChartPayload, 'rgba(114, 57, 234, 1)');
            renderMoneyBarChart('token_spending_per_week_chart', tokenSpendingChartPayload, 'rgba(241, 65, 108, 1)');
        });
    </script>
@endpush
