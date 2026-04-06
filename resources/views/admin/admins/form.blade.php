<div class="row g-6 mb-2">
    <div class="col-md-6">
        <label class="form-label required">Name</label>
        <input
            type="text"
            name="name"
            value="{{ old('name', $admin->name ?? '') }}"
            class="form-control form-control-solid"
            required
        />
    </div>

    <div class="col-md-6">
        <label class="form-label required">Email</label>
        <input
            type="email"
            name="email"
            value="{{ old('email', $admin->email ?? '') }}"
            class="form-control form-control-solid"
            required
        />
    </div>
</div>

<div class="row g-6 mb-8">
    <div class="col-md-6">
        <label class="form-label {{ isset($admin) ? '' : 'required' }}">Password</label>
        <input
            type="password"
            name="password"
            class="form-control form-control-solid"
            {{ isset($admin) ? '' : 'required' }}
        />
        @if (isset($admin))
            <div class="form-text">Leave blank to keep current password.</div>
        @endif
    </div>

    <div class="col-md-6">
        <label class="form-label {{ isset($admin) ? '' : 'required' }}">Confirm Password</label>
        <input
            type="password"
            name="password_confirmation"
            class="form-control form-control-solid"
            {{ isset($admin) ? '' : 'required' }}
        />
    </div>
</div>

<div class="row g-6 mb-8">
    <div class="col-md-6">
        <label class="form-label required">Status</label>
        <select name="is_active" class="form-select form-select-solid" required>
            <option value="1" {{ old('is_active', isset($admin) ? (int) $admin->is_active : 1) === 1 ? 'selected' : '' }}>Active</option>
            <option value="0" {{ old('is_active', isset($admin) ? (int) $admin->is_active : 1) === 0 ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
</div>
