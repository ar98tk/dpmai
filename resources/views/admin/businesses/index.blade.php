@extends('admin.layout.admin-layout')

@section('title', 'Businesses')
@section('page-title', 'Businesses')
@section('page-actions')
    <a href="{{ route('admin.businesses.create') }}" class="btn btn-primary">
        <i class="ki-outline ki-plus fs-2"></i>Create Business
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body py-4">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Numbers</th>
                        <th>Plan Expiry Day</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    @forelse ($businesses as $business)
                        @php
                            $activeSubscription = $business->getActiveSubscription();
                            $activePlan = $activeSubscription ? $activeSubscription->plan : null;
                        @endphp
                        <tr>
                            <td>{{ $business->name }}</td>
                            <td>{{ $business->email ?: '-' }}</td>
                            <td>{{ $business->phone ?: '-' }}</td>
                            <td>
                                @if ($activePlan)
                                    <span class="badge badge-light-primary fw-bold px-4 py-3">{{ $activePlan->name }}</span>
                                @else
                                    <span class="badge badge-light-info fw-bold px-4 py-3">Free Plan</span>
                                @endif
                            </td>
                            <td>
                                @if ($business->status === 'active')
                                    <span class="badge badge-light-success fw-bold px-4 py-3">Active</span>
                                @else
                                    <span class="badge badge-light-danger fw-bold px-4 py-3">Suspended</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-light-info fw-bold px-3 py-2">{{ (int) $business->whatsapp_instances_count }}</span>
                            </td>
                            <td>
                                {{ $activeSubscription ? optional($activeSubscription->end_date)->format('Y-m-d H:i') : '-' }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.businesses.show', $business) }}" class="btn btn-sm btn-light-primary">
                                    <i class="ki-outline ki-eye fs-6 me-1"></i>Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-10">No businesses found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
