@extends('admin.layout.admin-layout')

@section('title', 'Business Details')
@section('page-title', 'Business Details')

@section('content')
    @php
        $plan = $activePlan;
        $dailyUsed = (int) $dailyTokensUsed;
        $dailyLimit = (int) $dailyTokenLimit;
        $dailyPercent = $dailyLimit > 0 ? min(100, (int) round(($dailyUsed / $dailyLimit) * 100)) : 0;
        $dailyColor = $dailyPercent > 90 ? 'danger' : ($dailyPercent >= 70 ? 'warning' : 'success');

        $monthlyUsed = (int) $monthlyTokensUsed;
        $monthlyLimit = (int) $monthlyTokenLimit;
        $monthlyPercent = $monthlyLimit > 0 ? min(100, (int) round(($monthlyUsed / $monthlyLimit) * 100)) : 0;
        $monthlyColor = $monthlyPercent > 90 ? 'danger' : ($monthlyPercent >= 70 ? 'warning' : 'success');
    @endphp

    <div class="card mb-7">
        <div class="card-body py-7">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold text-gray-900 mb-2">{{ $business->name }}</h2>
                    @if ($plan)
                        <span class="badge badge-light-primary fw-bold px-4 py-3">Plan: {{ $plan->name }}</span>
                    @else
                        <span class="badge badge-light-info fw-bold px-4 py-3">Plan: Free</span>
                    @endif
                    <div class="mt-3 text-muted fw-semibold fs-7">
                        Subscription End Date:
                        @if ($activeSubscription)
                            <span class="text-gray-800">{{ optional($activeSubscription->end_date)->format('Y-m-d H:i') }}</span>
                        @else
                            <span class="text-gray-600">Free plan (no active paid subscription)</span>
                        @endif
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    @if ($business->status === 'active')
                        <span class="badge badge-light-success fw-bold px-4 py-3">Active</span>
                    @else
                        <span class="badge badge-light-danger fw-bold px-4 py-3">Suspended</span>
                    @endif

                    <button type="button" class="btn btn-light-primary" data-bs-toggle="modal" data-bs-target="#edit_business_modal">
                        <i class="ki-outline ki-notepad-edit fs-2"></i>Edit Business
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-7">
        <div class="card-body py-7">
            <h3 class="fw-bold text-gray-900 mb-6">Token Usage</h3>

            <div class="row g-6">
                <div class="col-md-6">
                    <div class="text-muted fw-semibold mb-2">Daily Usage</div>
                    <div class="mb-2 text-gray-700 fs-7">
                        {{ number_format($dailyUsed) }} / {{ number_format($dailyLimit) }} tokens
                    </div>
                    <div class="progress h-8px">
                        <div
                            class="progress-bar bg-{{ $dailyColor }}"
                            role="progressbar"
                            style="width: {{ $dailyPercent }}%;"
                            aria-valuenow="{{ $dailyPercent }}"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        ></div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted fw-semibold mb-2">Monthly Usage</div>
                    <div class="mb-2 text-gray-700 fs-7">
                        {{ number_format($monthlyUsed) }} / {{ number_format($monthlyLimit) }} tokens
                    </div>
                    <div class="progress h-8px">
                        <div
                            class="progress-bar bg-{{ $monthlyColor }}"
                            role="progressbar"
                            style="width: {{ $monthlyPercent }}%;"
                            aria-valuenow="{{ $monthlyPercent }}"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-9">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="fw-bold m-0">WhatsApp Numbers</h3>
            </div>
            <div class="card-toolbar">
                <button
                    id="open_add_number_modal_btn"
                    type="button"
                    class="btn btn-primary"
                    data-has-plan="{{ $activeSubscription ? '1' : '0' }}"
                    data-max-instances="{{ $maxInstances }}"
                    {{ $instancesLimitReached ? 'disabled' : '' }}
                >
                    <i class="ki-outline ki-plus fs-2"></i>Add Number
                </button>
            </div>
        </div>
        <div class="card-body py-4">
            @if ($instancesLimitReached)
                <div class="alert alert-warning d-flex align-items-center p-5 mb-6">
                    <i class="ki-outline ki-information-5 fs-2hx text-warning me-4"></i>
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-gray-900">Plan instances limit reached.</span>
                        <span class="text-muted">This business has {{ $instancesCount }} / {{ $maxInstances }} numbers.</span>
                    </div>
                </div>
            @endif

            @if ($aiLimitReached)
                <div class="alert alert-danger d-flex align-items-center p-5 mb-6">
                    <i class="ki-outline ki-shield-cross fs-2hx text-danger me-4"></i>
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-gray-900">AI is paused for this business.</span>
                        <span class="text-muted">Daily or monthly token limit reached. AI toggles are disabled until usage resets or plan limits increase.</span>
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="instances_table">
                    <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Leads</th>
                        <th>Status</th>
                        <th>AI Enabled</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold" id="instances_table_body">
                    @foreach ($instances as $instance)
                        <tr id="instance-row-{{ $instance->id }}" data-instance-id="{{ $instance->id }}">
                            <td class="instance-name">{{ $instance->name }}</td>
                            <td class="instance-phone">{{ $instance->phone_number ?: '-' }}</td>
                            <td class="instance-leads-count">
                                <span class="badge badge-light-info fw-bold px-3 py-2">{{ (int) $instance->leads_count }}</span>
                            </td>
                            <td class="instance-status">
                                @if ($instance->status === 'connected')
                                    <span class="badge badge-light-success fw-bold px-4 py-3">Connected</span>
                                @elseif ($instance->status === 'disconnected')
                                    <span class="badge badge-light-danger fw-bold px-4 py-3">Disconnected</span>
                                @else
                                    <span class="badge badge-light-warning fw-bold px-4 py-3">Pending</span>
                                @endif
                            </td>
                            <td class="instance-ai">
                                <div class="d-flex flex-column gap-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <label class="form-check form-switch form-check-custom form-check-solid m-0">
                                            <input
                                                class="form-check-input js-ai-toggle"
                                                type="checkbox"
                                                data-instance-id="{{ $instance->id }}"
                                                {{ $instance->ai_enabled ? 'checked' : '' }}
                                                {{ $aiLimitReached ? 'disabled' : '' }}
                                            />
                                        </label>
                                        <span class="instance-ai-label badge {{ $instance->ai_enabled ? 'badge-light-success' : 'badge-light-secondary' }} fw-bold px-3 py-2">
                                            {{ $instance->ai_enabled ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </div>
                                    @if ($aiLimitReached)
                                        <div class="instance-ai-limit-message text-danger fs-8 fw-semibold">
                                            {{ $aiLimitReachedMessage }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end">
                                <a
                                    href="{{ route('admin.instances.leads', $instance) }}"
                                    class="btn btn-sm btn-light-info me-2"
                                >
                                    <i class="ki-outline ki-profile-circle fs-6 me-1"></i>Leads
                                </a>

                                <a
                                    href="{{ route('admin.instances.edit', $instance) }}"
                                    class="btn btn-sm btn-light-primary me-2"
                                >
                                    <i class="ki-outline ki-notepad-edit fs-6 me-1"></i>Edit
                                </a>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-light-warning me-2 js-reconnect-instance {{ $instance->status === 'disconnected' ? '' : 'd-none' }}"
                                    data-instance-id="{{ $instance->id }}"
                                    data-instance-name="{{ $instance->name }}"
                                >
                                    <i class="ki-outline ki-arrows-circle fs-6 me-1"></i>Reconnect
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-light-danger js-delete-instance"
                                    data-instance-id="{{ $instance->id }}"
                                >
                                    <i class="ki-outline ki-trash fs-6 me-1"></i>Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach

                    <tr id="instances_empty_row" class="{{ $instances->isEmpty() ? '' : 'd-none' }}">
                        <td colspan="6" class="text-center text-muted py-12">No numbers connected yet</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-9">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="fw-bold m-0">Subscriptions</h3>
            </div>
        </div>
        <div class="card-body py-4">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>Expiry Date</th>
                        <th>Days Left</th>
                        <th>Price</th>
                        <th class="text-end">Action</th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-700 fw-semibold">
                    @forelse ($subscriptions as $subscription)
                        @php
                            $effectiveStatus = (string) ($subscription->effective_status ?? $subscription->status);
                        @endphp
                        <tr>
                            <td>{{ $subscription->plan?->name ?? '-' }}</td>
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
                            <td>${{ number_format((float) ($subscription->plan?->price ?? 0), 2) }}</td>
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
                            <td colspan="7" class="text-center text-muted py-10">No subscriptions found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit_business_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-600px">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.businesses.update', $business) }}">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h2 class="fw-bold">Edit Business</h2>
                        <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </button>
                    </div>

                    <div class="modal-body py-10 px-lg-10">
                        <div class="mb-7">
                            <label class="form-label required">Business Name</label>
                            <input type="text" name="name" value="{{ old('name', $business->name) }}" class="form-control form-control-solid" required />
                        </div>

                        <div class="mb-7">
                            <label class="form-label required">Email</label>
                            <input type="email" name="email" value="{{ old('email', $business->email) }}" class="form-control form-control-solid" required />
                        </div>

                        <div class="mb-7">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control form-control-solid" minlength="8" />
                            <div class="text-muted fs-8 mt-2">Leave empty to keep current password.</div>
                        </div>

                        <div class="mb-7">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $business->phone) }}" class="form-control form-control-solid" />
                        </div>

                        <div class="mb-7">
                            <label class="form-label required">Status</label>
                            <select name="status" class="form-select form-select-solid" required>
                                <option value="active" {{ old('status', $business->status) === 'active' ? 'selected' : '' }}>active</option>
                                <option value="suspended" {{ old('status', $business->status) === 'suspended' ? 'selected' : '' }}>suspended</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Plan</label>
                            <select name="plan_id" class="form-select form-select-solid">
                                <option value="">Free Plan (No Subscription)</option>
                                @foreach ($plans as $item)
                                    <option
                                        value="{{ $item->id }}"
                                        {{ (string) old('plan_id', $activeSubscription?->plan_id) === (string) $item->id ? 'selected' : '' }}
                                    >
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mt-7">
                            <label class="form-label">Plan Expiry Day</label>
                            <input
                                type="datetime-local"
                                name="plan_expiry_date"
                                value="{{ old('plan_expiry_date', $activeSubscription ? optional($activeSubscription->end_date)->format('Y-m-d\\TH:i') : '') }}"
                                class="form-control form-control-solid"
                            />
                            <div class="text-muted fs-8 mt-2">
                                Current: {{ $activeSubscription ? optional($activeSubscription->end_date)->format('Y-m-d H:i') : 'No active subscription' }}
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="instance_connect_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <div class="modal-content">
                <form id="instance_connect_form" method="POST">
                    @csrf
                    <input type="hidden" name="business_id" value="{{ $business->id }}">
                    <input type="hidden" id="connect_instance_id" value="">

                    <div class="modal-header">
                        <h2 class="fw-bold" id="instance_connect_modal_title">Add Number</h2>
                        <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </button>
                    </div>

                    <div class="modal-body py-10 px-lg-10">
                        <div class="mb-7" id="connect_instance_name_wrapper">
                            <label class="form-label required">Number Name</label>
                            <input id="connect_instance_name" type="text" name="name" class="form-control form-control-solid" required />
                        </div>

                        <div class="mb-7" id="connect_phone_number_wrapper">
                            <label class="form-label required">Phone Number</label>
                            <input id="connect_phone_number" type="text" name="phone_number" class="form-control form-control-solid" required />
                        </div>

                        <div id="instance_connect_error" class="alert alert-danger d-none mb-7"></div>

                        <div id="instance_connect_loader" class="d-none text-center py-8">
                            <div class="spinner-border text-primary mb-4" role="status"></div>
                            <div class="text-muted">Fetching QR code...</div>
                        </div>

                        <div id="instance_qr_wrapper" class="d-none text-center">
                            <div class="border rounded p-6 bg-light">
                                <img id="instance_qr_image" src="" alt="QR Code" class="mw-100" style="max-height: 260px;" />
                            </div>
                            <div id="instance_connection_status_text" class="mt-4 text-muted fw-semibold">Waiting for scan...</div>
                        </div>
                    </div>

                    <div class="modal-footer flex-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button id="instance_connect_submit_btn" type="submit" class="btn btn-primary">Create &amp; Connect</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="position-fixed top-0 end-0 p-5" style="z-index: 1300;">
        <div id="instance_success_toast" class="modern-toast" role="status" aria-live="polite" aria-atomic="true">
            <div class="modern-toast__icon">
                <i class="ki-outline ki-check-circle fs-2 text-success"></i>
            </div>
            <div class="modern-toast__content">
                <div class="modern-toast__title">Success</div>
                <div class="modern-toast__message" id="instance_success_toast_body">Connected</div>
            </div>
            <button type="button" id="instance_success_toast_close" class="modern-toast__close" aria-label="Close">
                <i class="ki-outline ki-cross fs-4"></i>
            </button>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .modern-toast {
            width: min(380px, calc(100vw - 2rem));
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.9rem 1rem;
            border-radius: 14px;
            border: 1px solid rgba(80, 203, 125, 0.45);
            background: linear-gradient(145deg, rgba(18, 26, 21, 0.95), rgba(11, 21, 16, 0.92));
            box-shadow:
                0 14px 34px rgba(0, 0, 0, 0.28),
                0 0 0 1px rgba(255, 255, 255, 0.03),
                0 0 24px rgba(80, 203, 125, 0.16);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: #ecfff2;
            opacity: 0;
            transform: translateY(-14px) scale(0.97);
            pointer-events: none;
            transition: opacity 0.22s ease, transform 0.22s ease;
        }

        .modern-toast.show {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }

        .modern-toast__icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(80, 203, 125, 0.12);
            border: 1px solid rgba(80, 203, 125, 0.28);
            flex: 0 0 auto;
        }

        .modern-toast__content {
            flex: 1 1 auto;
            min-width: 0;
        }

        .modern-toast__title {
            font-weight: 700;
            font-size: 0.86rem;
            color: #d7ffe3;
            line-height: 1.2;
            margin-bottom: 2px;
        }

        .modern-toast__message {
            font-size: 0.81rem;
            color: #b9f5cc;
            line-height: 1.35;
            word-break: break-word;
        }

        .modern-toast__close {
            border: 0;
            background: transparent;
            color: #b8e6c6;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.18s ease, color 0.18s ease;
        }

        .modern-toast__close:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof bootstrap === 'undefined') {
                return;
            }

            var endpoints = {
                store: '{{ route('admin.instances.store') }}',
                qr: '/instances/{id}/qr',
                status: '/instances/{id}/status',
                update: '/instances/{id}',
                destroy: '/instances/{id}'
            };

            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var tableBody = document.getElementById('instances_table_body');
            var emptyRow = document.getElementById('instances_empty_row');
            var addNumberBtn = document.getElementById('open_add_number_modal_btn');

            var connectModalElement = document.getElementById('instance_connect_modal');
            var connectModal = bootstrap.Modal.getOrCreateInstance(connectModalElement);
            var connectForm = document.getElementById('instance_connect_form');
            var connectModalTitle = document.getElementById('instance_connect_modal_title');
            var connectNameWrapper = document.getElementById('connect_instance_name_wrapper');
            var connectNameInput = document.getElementById('connect_instance_name');
            var connectPhoneWrapper = document.getElementById('connect_phone_number_wrapper');
            var connectPhoneInput = document.getElementById('connect_phone_number');
            var connectInstanceIdInput = document.getElementById('connect_instance_id');
            var connectError = document.getElementById('instance_connect_error');
            var connectLoader = document.getElementById('instance_connect_loader');
            var qrWrapper = document.getElementById('instance_qr_wrapper');
            var qrImage = document.getElementById('instance_qr_image');
            var connectionStatusText = document.getElementById('instance_connection_status_text');
            var connectSubmitBtn = document.getElementById('instance_connect_submit_btn');

            var toastElement = document.getElementById('instance_success_toast');
            var toastBody = document.getElementById('instance_success_toast_body');
            var toastCloseButton = document.getElementById('instance_success_toast_close');
            var toastTimer = null;

            var connectMode = 'create';
            var connectPollTimer = null;
            var tablePollTimer = null;
            var aiLimitReached = {{ $aiLimitReached ? 'true' : 'false' }};
            var aiLimitMessage = @json($aiLimitReachedMessage);

            function endpoint(urlPattern, id) {
                return urlPattern.replace('{id}', String(id));
            }

            function showToast(message) {
                toastBody.textContent = message;
                toastElement.classList.add('show');

                if (toastTimer) {
                    clearTimeout(toastTimer);
                }

                toastTimer = setTimeout(function () {
                    toastElement.classList.remove('show');
                }, 2500);
            }

            toastCloseButton.addEventListener('click', function () {
                toastElement.classList.remove('show');
                if (toastTimer) {
                    clearTimeout(toastTimer);
                    toastTimer = null;
                }
            });

            function setConnectError(message) {
                connectError.textContent = message || 'Something went wrong.';
                connectError.classList.remove('d-none');
            }

            function clearConnectError() {
                connectError.textContent = '';
                connectError.classList.add('d-none');
            }

            function setConnectLoading(loading, text) {
                connectSubmitBtn.disabled = loading;
                connectSubmitBtn.textContent = text || 'Create & Connect';
            }

            function setQrLoading(loading) {
                connectLoader.classList.toggle('d-none', !loading);
            }

            function clearConnectPolling() {
                if (connectPollTimer) {
                    clearInterval(connectPollTimer);
                    connectPollTimer = null;
                }
            }

            function normalizeStatus(status) {
                var value = (status || '').toLowerCase();
                if (value === 'connected' || value === 'open') {
                    return 'connected';
                }
                if (value === 'disconnected' || value === 'close' || value === 'closed') {
                    return 'disconnected';
                }
                return 'pending';
            }

            function statusBadgeHtml(status) {
                var value = normalizeStatus(status);
                if (value === 'connected') {
                    return '<span class="badge badge-light-success fw-bold px-4 py-3">Connected<\/span>';
                }
                if (value === 'disconnected') {
                    return '<span class="badge badge-light-danger fw-bold px-4 py-3">Disconnected<\/span>';
                }
                return '<span class="badge badge-light-warning fw-bold px-4 py-3">Pending<\/span>';
            }

            function aiToggleHtml(instance) {
                var enabled = !!instance.ai_enabled;
                var disabledAttr = aiLimitReached ? 'disabled' : '';
                var limitMessageHtml = aiLimitReached
                    ? '<div class="instance-ai-limit-message text-danger fs-8 fw-semibold">' + aiLimitMessage + '<\/div>'
                    : '';

                return ''
                    + '<div class="d-flex flex-column gap-2">'
                    + '  <div class="d-flex align-items-center gap-3">'
                    + '      <label class="form-check form-switch form-check-custom form-check-solid m-0">'
                    + '          <input class="form-check-input js-ai-toggle" type="checkbox" data-instance-id="' + instance.id + '" ' + (enabled ? 'checked' : '') + ' ' + disabledAttr + ' />'
                    + '      </label>'
                    + '      <span class="instance-ai-label badge ' + (enabled ? 'badge-light-success' : 'badge-light-secondary') + ' fw-bold px-3 py-2">'
                    +            (enabled ? 'Enabled' : 'Disabled')
                    + '      </span>'
                    + '  </div>'
                    +      limitMessageHtml
                    + '</div>';
            }

            function reconnectButtonClass(status) {
                return normalizeStatus(status) === 'disconnected' ? '' : 'd-none';
            }

            function toggleEmptyState() {
                var hasRows = tableBody.querySelectorAll('tr[data-instance-id]').length > 0;
                emptyRow.classList.toggle('d-none', hasRows);
            }

            function updateAddNumberButtonState() {
                if (!addNumberBtn) {
                    return;
                }

                var hasPlan = addNumberBtn.getAttribute('data-has-plan') === '1';
                var maxInstances = parseInt(addNumberBtn.getAttribute('data-max-instances') || '0', 10);
                var currentInstances = tableBody.querySelectorAll('tr[data-instance-id]').length;

                if (!hasPlan) {
                    addNumberBtn.disabled = false;
                    return;
                }

                addNumberBtn.disabled = !Number.isNaN(maxInstances) && currentInstances >= maxInstances;
            }

            function rowForInstanceId(instanceId) {
                return document.getElementById('instance-row-' + instanceId);
            }

            function updateRowStatus(instanceId, status) {
                var row = rowForInstanceId(instanceId);
                if (!row) {
                    return;
                }

                var normalized = normalizeStatus(status);
                var statusCell = row.querySelector('.instance-status');
                var reconnectBtn = row.querySelector('.js-reconnect-instance');

                statusCell.innerHTML = statusBadgeHtml(normalized);
                reconnectBtn.classList.toggle('d-none', reconnectButtonClass(normalized) === 'd-none');
            }

            function addOrUpdateRow(instance) {
                var row = rowForInstanceId(instance.id);
                if (row) {
                    row.querySelector('.instance-name').textContent = instance.name;
                    row.querySelector('.instance-phone').textContent = instance.phone_number || '-';
                    if (typeof instance.leads_count !== 'undefined') {
                        var leadsBadge = row.querySelector('.instance-leads-count span');
                        if (leadsBadge) {
                            leadsBadge.textContent = String(instance.leads_count || 0);
                        }
                    }
                    row.querySelector('.instance-ai').innerHTML = aiToggleHtml(instance);
                    updateRowStatus(instance.id, instance.status || 'pending');
                    return;
                }

                var tr = document.createElement('tr');
                tr.id = 'instance-row-' + instance.id;
                tr.setAttribute('data-instance-id', instance.id);
                tr.innerHTML = ''
                    + '<td class="instance-name">' + instance.name + '<\/td>'
                    + '<td class="instance-phone">' + (instance.phone_number || '-') + '<\/td>'
                    + '<td class="instance-leads-count"><span class="badge badge-light-info fw-bold px-3 py-2">0<\/span><\/td>'
                    + '<td class="instance-status">' + statusBadgeHtml(instance.status || 'pending') + '<\/td>'
                    + '<td class="instance-ai">' + aiToggleHtml(instance) + '<\/td>'
                    + '<td class="text-end">'
                    + '  <a href="/instances/' + instance.id + '/leads" class="btn btn-sm btn-light-info me-2">'
                    + '      <i class="ki-outline ki-profile-circle fs-6 me-1"></i>Leads'
                    + '  </a>'
                    + '  <a href="/instances/' + instance.id + '/edit" class="btn btn-sm btn-light-primary me-2">'
                    + '      <i class="ki-outline ki-notepad-edit fs-6 me-1"></i>Edit'
                    + '  </a>'
                    + '  <button type="button" class="btn btn-sm btn-light-warning me-2 js-reconnect-instance ' + reconnectButtonClass(instance.status || 'pending') + '" data-instance-id="' + instance.id + '" data-instance-name="' + instance.name + '">'
                    + '      <i class="ki-outline ki-arrows-circle fs-6 me-1"></i>Reconnect'
                    + '  </button>'
                    + '  <button type="button" class="btn btn-sm btn-light-danger js-delete-instance" data-instance-id="' + instance.id + '">'
                    + '      <i class="ki-outline ki-trash fs-6 me-1"></i>Delete'
                    + '  </button>'
                    + '<\/td>';

                tableBody.insertBefore(tr, emptyRow);
                toggleEmptyState();
                updateAddNumberButtonState();
            }

            async function requestJson(url, options) {
                var response = await fetch(url, options || {});
                var data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Request failed.');
                }

                return data;
            }

            async function fetchAndApplyStatus(instanceId) {
                var payload = await requestJson(endpoint(endpoints.status, instanceId), {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                });

                updateRowStatus(instanceId, payload.status || 'pending');
                return payload.status || 'pending';
            }

            function startTablePolling() {
                if (tablePollTimer) {
                    clearInterval(tablePollTimer);
                }

                tablePollTimer = setInterval(function () {
                    var rows = tableBody.querySelectorAll('tr[data-instance-id]');
                    rows.forEach(function (row) {
                        var instanceId = row.getAttribute('data-instance-id');
                        fetchAndApplyStatus(instanceId).catch(function () {});
                    });
                }, 5000);
            }

            function resetConnectModal() {
                clearConnectPolling();
                connectMode = 'create';
                connectModalTitle.textContent = 'Add Number';
                connectNameWrapper.classList.remove('d-none');
                connectNameInput.required = true;
                connectNameInput.value = '';
                connectPhoneWrapper.classList.remove('d-none');
                connectPhoneInput.required = true;
                connectPhoneInput.value = '';
                connectInstanceIdInput.value = '';
                connectSubmitBtn.classList.remove('d-none');
                clearConnectError();
                setQrLoading(false);
                qrWrapper.classList.add('d-none');
                qrImage.src = '';
                connectionStatusText.textContent = 'Waiting for scan...';
                setConnectLoading(false, 'Create & Connect');
            }

            function openCreateModal() {
                resetConnectModal();
                connectModal.show();
            }

            async function fetchQrAndStartPolling(instanceId) {
                clearConnectError();
                setQrLoading(true);
                qrWrapper.classList.add('d-none');
                connectionStatusText.textContent = 'Waiting for scan...';

                var qrPayload = await requestJson(endpoint(endpoints.qr, instanceId), {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                });

                var qrValue = qrPayload.data && qrPayload.data.qr ? qrPayload.data.qr : '';
                if (!qrValue) {
                    throw new Error('QR code not available.');
                }

                qrImage.src = qrValue.startsWith('data:image') ? qrValue : ('data:image/png;base64,' + qrValue);
                setQrLoading(false);
                qrWrapper.classList.remove('d-none');

                clearConnectPolling();
                connectPollTimer = setInterval(async function () {
                    try {
                        var status = await fetchAndApplyStatus(instanceId);
                        connectionStatusText.textContent = 'Status: ' + status;

                        if (status === 'connected') {
                            clearConnectPolling();
                            showToast('Instance connected successfully.');
                            setTimeout(function () {
                                connectModal.hide();
                            }, 700);
                        }
                    } catch (error) {
                        clearConnectPolling();
                        setConnectError(error.message);
                    }
                }, 3000);
            }

            addNumberBtn.addEventListener('click', function (event) {
                if (addNumberBtn.disabled) {
                    event.preventDefault();
                    return;
                }

                openCreateModal();
            });

            connectForm.addEventListener('submit', async function (event) {
                event.preventDefault();

                if (connectMode !== 'create') {
                    return;
                }

                clearConnectError();
                setConnectLoading(true, 'Please wait...');

                try {
                    var payload = await requestJson(endpoints.store, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            business_id: connectForm.querySelector('input[name="business_id"]').value,
                            name: connectNameInput.value,
                            phone_number: connectPhoneInput.value
                        })
                    });

                    var redirectUrl = payload.redirect_url || ('/instances/' + payload.data.id + '/edit');
                    window.location.href = redirectUrl;
                } catch (error) {
                    setConnectLoading(false, 'Create & Connect');
                    setConnectError(error.message);
                }
            });

            tableBody.addEventListener('click', async function (event) {
                var reconnectButton = event.target.closest('.js-reconnect-instance');
                if (reconnectButton) {
                    var instanceId = reconnectButton.getAttribute('data-instance-id');
                    var originalHtml = reconnectButton.innerHTML;
                    reconnectButton.disabled = true;
                    reconnectButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Reconnect';

                    try {
                        resetConnectModal();
                        connectMode = 'reconnect';
                        connectModalTitle.textContent = 'Reconnect Number';
                        connectNameWrapper.classList.add('d-none');
                        connectNameInput.required = false;
                        connectPhoneWrapper.classList.add('d-none');
                        connectPhoneInput.required = false;
                        connectSubmitBtn.classList.add('d-none');
                        connectInstanceIdInput.value = instanceId;
                        connectModal.show();

                        await fetchQrAndStartPolling(instanceId);
                    } catch (error) {
                        setConnectError(error.message);
                    } finally {
                        reconnectButton.disabled = false;
                        reconnectButton.innerHTML = originalHtml;
                    }

                    return;
                }

                var deleteButton = event.target.closest('.js-delete-instance');
                if (deleteButton) {
                    var deleteId = deleteButton.getAttribute('data-instance-id');
                    if (!window.confirm('Are you sure you want to delete this instance?')) {
                        return;
                    }

                    var originalDeleteHtml = deleteButton.innerHTML;
                    deleteButton.disabled = true;
                    deleteButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Delete';

                    try {
                        await requestJson(endpoint(endpoints.destroy, deleteId), {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });

                        var deleteRow = rowForInstanceId(deleteId);
                        if (deleteRow) {
                            deleteRow.remove();
                        }
                        toggleEmptyState();
                        updateAddNumberButtonState();
                        showToast('Instance deleted successfully.');
                    } catch (error) {
                        alert(error.message);
                    } finally {
                        deleteButton.disabled = false;
                        deleteButton.innerHTML = originalDeleteHtml;
                    }
                }
            });

            tableBody.addEventListener('change', async function (event) {
                var aiToggle = event.target.closest('.js-ai-toggle');
                if (!aiToggle) {
                    return;
                }

                if (aiLimitReached) {
                    aiToggle.checked = !aiToggle.checked;
                    showToast(aiLimitMessage);
                    return;
                }

                var instanceId = aiToggle.getAttribute('data-instance-id');
                var row = rowForInstanceId(instanceId);
                var label = row ? row.querySelector('.instance-ai-label') : null;
                var targetValue = aiToggle.checked ? 1 : 0;
                var previousValue = aiToggle.checked ? 0 : 1;

                aiToggle.disabled = true;

                try {
                    var payload = await requestJson(endpoint(endpoints.update, instanceId), {
                        method: 'PUT',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            ai_enabled: targetValue
                        })
                    });

                    addOrUpdateRow(payload.data);
                    showToast('AI setting updated.');
                } catch (error) {
                    aiToggle.checked = previousValue === 1;
                    if (label) {
                        label.textContent = previousValue === 1 ? 'Enabled' : 'Disabled';
                        label.classList.toggle('badge-light-success', previousValue === 1);
                        label.classList.toggle('badge-light-secondary', previousValue !== 1);
                    }
                    alert(error.message);
                } finally {
                    aiToggle.disabled = aiLimitReached;
                }
            });

            connectModalElement.addEventListener('hidden.bs.modal', function () {
                connectSubmitBtn.classList.remove('d-none');
                resetConnectModal();
            });

            startTablePolling();
            updateAddNumberButtonState();
        });
    </script>
@endpush
