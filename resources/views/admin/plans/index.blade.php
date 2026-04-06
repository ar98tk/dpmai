@extends('admin.layout.admin-layout')

@section('title', 'Plans')
@section('page-title', 'Plans')
@section('page-actions')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#plan_create_modal">
        <i class="ki-outline ki-plus fs-2"></i>Add Plan
    </button>
@endsection

@section('content')
    <div class="card">
        <div class="card-body py-4">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-4">
                    <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>Name</th>
                        <th>Price</th>
                        <th>Max Instances</th>
                        <th>Daily Limit</th>
                        <th>Monthly Limit</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-700 fw-semibold">
                    @forelse ($plans as $plan)
                        <tr>
                            <td>{{ $plan->name }}</td>
                            <td>${{ number_format((float) $plan->price, 2) }}</td>
                            <td>{{ number_format((int) $plan->max_instances) }}</td>
                            <td>{{ number_format((int) $plan->daily_token_limit) }}</td>
                            <td>{{ number_format((int) $plan->monthly_token_limit) }}</td>
                            <td class="text-end">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-light-primary me-2 js-edit-plan-btn"
                                    data-plan-id="{{ $plan->id }}"
                                    data-plan-name="{{ $plan->name }}"
                                    data-plan-price="{{ $plan->price }}"
                                    data-plan-max-instances="{{ $plan->max_instances }}"
                                    data-plan-daily-token-limit="{{ $plan->daily_token_limit }}"
                                    data-plan-monthly-token-limit="{{ $plan->monthly_token_limit }}"
                                    data-plan-features='@json($plan->features)'
                                >
                                    <i class="ki-outline ki-notepad-edit fs-6 me-1"></i>Edit
                                </button>

                                <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" class="d-inline" onsubmit="return confirm('Delete this plan?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light-danger">
                                        <i class="ki-outline ki-trash fs-6 me-1"></i>Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-8">No plans found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="plan_create_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-700px">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.plans.store') }}">
                    @csrf

                    <div class="modal-header">
                        <h2 class="fw-bold">Add Plan</h2>
                        <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </button>
                    </div>

                    <div class="modal-body py-8 px-lg-10">
                        <div class="row g-5">
                            <div class="col-md-6">
                                <label class="form-label required">Name</label>
                                <input type="text" name="name" class="form-control form-control-solid" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Price</label>
                                <input type="number" name="price" step="0.01" min="0" class="form-control form-control-solid" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Max Instances</label>
                                <input type="number" name="max_instances" min="0" class="form-control form-control-solid" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Daily Token Limit</label>
                                <input type="number" name="daily_token_limit" min="0" class="form-control form-control-solid" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Monthly Token Limit</label>
                                <input type="number" name="monthly_token_limit" min="0" class="form-control form-control-solid" required />
                            </div>
                            <div class="col-12">
                                <label class="form-label">Features (JSON or one item per line)</label>
                                <textarea name="features" rows="4" class="form-control form-control-solid"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="plan_edit_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-700px">
            <div class="modal-content">
                <form id="plan_edit_form" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h2 class="fw-bold">Edit Plan</h2>
                        <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </button>
                    </div>

                    <div class="modal-body py-8 px-lg-10">
                        <div class="row g-5">
                            <div class="col-md-6">
                                <label class="form-label required">Name</label>
                                <input type="text" id="edit_plan_name" name="name" class="form-control form-control-solid" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Price</label>
                                <input type="number" id="edit_plan_price" name="price" step="0.01" min="0" class="form-control form-control-solid" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Max Instances</label>
                                <input type="number" id="edit_plan_max_instances" name="max_instances" min="0" class="form-control form-control-solid" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Daily Token Limit</label>
                                <input type="number" id="edit_plan_daily_token_limit" name="daily_token_limit" min="0" class="form-control form-control-solid" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Monthly Token Limit</label>
                                <input type="number" id="edit_plan_monthly_token_limit" name="monthly_token_limit" min="0" class="form-control form-control-solid" required />
                            </div>
                            <div class="col-12">
                                <label class="form-label">Features (JSON or one item per line)</label>
                                <textarea id="edit_plan_features" name="features" rows="4" class="form-control form-control-solid"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var editModalElement = document.getElementById('plan_edit_modal');
            if (typeof bootstrap === 'undefined' || !editModalElement) {
                return;
            }

            var editModal = bootstrap.Modal.getOrCreateInstance(editModalElement);
            var editForm = document.getElementById('plan_edit_form');
            var editName = document.getElementById('edit_plan_name');
            var editPrice = document.getElementById('edit_plan_price');
            var editMaxInstances = document.getElementById('edit_plan_max_instances');
            var editDailyLimit = document.getElementById('edit_plan_daily_token_limit');
            var editMonthlyLimit = document.getElementById('edit_plan_monthly_token_limit');
            var editFeatures = document.getElementById('edit_plan_features');
            var updateUrlTemplate = @json(route('admin.plans.update', ['plan' => '__PLAN__']));

            document.querySelectorAll('.js-edit-plan-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    var planId = button.getAttribute('data-plan-id');
                    var featuresRaw = button.getAttribute('data-plan-features');
                    var featuresValue = '';

                    try {
                        var parsed = JSON.parse(featuresRaw || 'null');
                        if (Array.isArray(parsed)) {
                            featuresValue = parsed.join('\n');
                        } else if (parsed && typeof parsed === 'object') {
                            featuresValue = JSON.stringify(parsed, null, 2);
                        } else {
                            featuresValue = parsed ? String(parsed) : '';
                        }
                    } catch (error) {
                        featuresValue = '';
                    }

                    editForm.action = updateUrlTemplate.replace('__PLAN__', planId);
                    editName.value = button.getAttribute('data-plan-name') || '';
                    editPrice.value = button.getAttribute('data-plan-price') || 0;
                    editMaxInstances.value = button.getAttribute('data-plan-max-instances') || 0;
                    editDailyLimit.value = button.getAttribute('data-plan-daily-token-limit') || 0;
                    editMonthlyLimit.value = button.getAttribute('data-plan-monthly-token-limit') || 0;
                    editFeatures.value = featuresValue;

                    editModal.show();
                });
            });
        });
    </script>
@endpush
