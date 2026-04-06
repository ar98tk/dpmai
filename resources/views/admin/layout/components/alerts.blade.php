@if (session('success'))
    <div class="app-alert app-alert-success mb-6">
        <span class="app-alert-icon">
            <i class="ki-outline ki-check-circle fs-2"></i>
        </span>
        <div class="app-alert-content">
            <h5 class="app-alert-title">Success</h5>
            <p class="app-alert-text mb-0">{{ session('success') }}</p>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="app-alert app-alert-danger mb-6">
        <span class="app-alert-icon">
            <i class="ki-outline ki-cross-circle fs-2"></i>
        </span>
        <div class="app-alert-content">
            <h5 class="app-alert-title">Error</h5>
            <p class="app-alert-text mb-0">{{ session('error') }}</p>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="app-alert app-alert-danger mb-6">
        <span class="app-alert-icon">
            <i class="ki-outline ki-information-5 fs-2"></i>
        </span>
        <div class="app-alert-content">
            <h5 class="app-alert-title">Please check the following</h5>
            <ul class="mb-0 ps-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            </ul>
        </div>
    </div>
@endif
