@extends('admin.layout.admin-layout')

@section('title', 'Subscriptions')
@section('page-title', 'Subscriptions')

@section('content')
    <div class="card">
        <div class="card-body py-4">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>Business</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>Expiry Date</th>
                        <th>Days Left</th>
                        <th>Plan Limits</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-700 fw-semibold">
                    @forelse ($subscriptions as $subscription)
                        @php
                            $effectiveStatus = (string) $subscription->effective_status;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    @if ($subscription->business)
                                        <a href="{{ route('admin.businesses.show', $subscription->business) }}" class="text-gray-900 fw-bold text-hover-primary">
                                            {{ $subscription->business->name }}
                                        </a>
                                    @else
                                        <span class="text-gray-900 fw-bold">-</span>
                                    @endif
                                    <span class="text-muted fs-8">Business Status: {{ ucfirst((string) ($subscription->business?->status ?? 'unknown')) }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-900 fw-bold">{{ $subscription->plan?->name ?? '-' }}</span>
                                    <span class="text-muted fs-8">${{ number_format((float) ($subscription->plan?->price ?? 0), 2) }}</span>
                                </div>
                            </td>
                            <td>
                                @if ($effectiveStatus === 'active')
                                    <span class="badge badge-light-success fw-bold px-4 py-3">Active</span>
                                @elseif ($effectiveStatus === 'canceled')
                                    <span class="badge badge-light-dark fw-bold px-4 py-3">Canceled</span>
                                @else
                                    <span class="badge badge-light-danger fw-bold px-4 py-3">Expired</span>
                                @endif
                            </td>
                            <td>{{ optional($subscription->start_date)->format('Y-m-d H:i') ?: '-' }}</td>
                            <td>{{ optional($subscription->end_date)->format('Y-m-d H:i') ?: '-' }}</td>
                            <td>{{ $subscription->remaining_text ?? '-' }}</td>
                            <td>
                                @if ($subscription->plan)
                                    <div class="text-muted fs-8">
                                        Instances: {{ (int) $subscription->plan->max_instances }}<br>
                                        Daily: {{ number_format((int) $subscription->plan->daily_token_limit) }}<br>
                                        Monthly: {{ number_format((int) $subscription->plan->monthly_token_limit) }}
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">
                                <a
                                    href="{{ route('admin.subscriptions.invoice', $subscription) }}"
                                    class="btn btn-sm btn-light-primary"
                                >
                                    <i class="ki-outline ki-document fs-6 me-1"></i>Export Invoice
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-10">No subscriptions found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
