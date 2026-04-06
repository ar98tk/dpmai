@extends('admin.layout.admin-layout')

@section('title', 'Create Business')
@section('page-title', 'Create Business')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-7 col-lg-8">
            <div class="card">
                <div class="card-body py-10 px-10">
                    <form method="POST" action="{{ route('admin.businesses.store') }}">
                        @csrf

                        <div class="mb-8">
                            <label class="form-label required">Business Name</label>
                            <input
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                class="form-control form-control-solid"
                                required
                            />
                        </div>

                        <div class="mb-8">
                            <label class="form-label required">Email</label>
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                class="form-control form-control-solid"
                                required
                            />
                        </div>

                        <div class="mb-8">
                            <label class="form-label required">Password</label>
                            <input
                                type="password"
                                name="password"
                                class="form-control form-control-solid"
                                minlength="8"
                                required
                            />
                        </div>

                        <div class="mb-8">
                            <label class="form-label">Phone</label>
                            <input
                                type="text"
                                name="phone"
                                value="{{ old('phone') }}"
                                class="form-control form-control-solid"
                            />
                        </div>

                        <div class="mb-8">
                            <label class="form-label required">Plan</label>
                            <select name="plan_id" class="form-select form-select-solid" required>
                                <option value="" disabled {{ old('plan_id') ? '' : 'selected' }}>Select plan</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" {{ (string) old('plan_id') === (string) $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-10">
                            <label class="form-label">Plan Expiry Day</label>
                            <input
                                type="datetime-local"
                                name="plan_expiry_date"
                                value="{{ old('plan_expiry_date', now()->addDays(30)->format('Y-m-d\\TH:i')) }}"
                                class="form-control form-control-solid"
                            />
                        </div>

                        <div class="mb-10">
                            <label class="form-label required">Status</label>
                            <select name="status" class="form-select form-select-solid" required>
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>active</option>
                                <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>suspended</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end gap-3">
                            <a href="{{ url()->previous() }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Business</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
