<div id="kt_app_header" class="app-header align-items-stretch">
    <div class="app-container container-xxl d-flex align-items-stretch justify-content-between" id="kt_app_header_container">
        <div class="d-flex align-items-center gap-8">
            <a href="{{ route('admin.home') }}" class="d-flex align-items-center text-decoration-none">
                <img src="{{ asset('dpm-logo.png') }}" alt="DPM" class="app-brand-logo" />
            </a>

            <div class="app-header-menu align-items-stretch">
                <div class="menu menu-rounded menu-column menu-lg-row fw-semibold fs-6 align-items-stretch my-5 my-lg-0 px-2 px-lg-0">
                    <div class="menu-item me-0 me-lg-2">
                        <a href="{{ route('admin.home') }}" class="menu-link app-top-nav-link {{ request()->routeIs('admin.home') ? 'active' : '' }}">
                            <span class="menu-icon">
                                <i class="ki-outline ki-element-11 fs-3"></i>
                            </span>
                            <span class="menu-title">Home</span>
                        </a>
                    </div>

                    <div class="menu-item me-0 me-lg-2">
                        <a href="{{ route('admin.admins.index') }}" class="menu-link app-top-nav-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                            <span class="menu-icon">
                                <i class="ki-outline ki-users fs-3"></i>
                            </span>
                            <span class="menu-title">Admins</span>
                        </a>
                    </div>

                    <div class="menu-item me-0 me-lg-2">
                        <a href="{{ route('admin.businesses.index') }}" class="menu-link app-top-nav-link {{ request()->routeIs('admin.businesses.*') ? 'active' : '' }}">
                            <span class="menu-icon">
                                <i class="ki-outline ki-shop fs-3"></i>
                            </span>
                            <span class="menu-title">Business</span>
                        </a>
                    </div>

                    <div class="menu-item me-0 me-lg-2">
                        <a href="{{ route('admin.plans.index') }}" class="menu-link app-top-nav-link {{ request()->routeIs('admin.plans.*') ? 'active' : '' }}">
                            <span class="menu-icon">
                                <i class="ki-outline ki-abstract-26 fs-3"></i>
                            </span>
                            <span class="menu-title">Plans</span>
                        </a>
                    </div>

                    <div class="menu-item me-0 me-lg-2">
                        <a href="{{ route('admin.subscriptions.index') }}" class="menu-link app-top-nav-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
                            <span class="menu-icon">
                                <i class="ki-outline ki-calendar-8 fs-3"></i>
                            </span>
                            <span class="menu-title">Subscriptions</span>
                        </a>
                    </div>

                    <div class="menu-item me-0 me-lg-2">
                        <a href="{{ route('admin.billing.index') }}" class="menu-link app-top-nav-link {{ request()->routeIs('admin.billing.*') ? 'active' : '' }}">
                            <span class="menu-icon">
                                <i class="ki-outline ki-credit-cart fs-3"></i>
                            </span>
                            <span class="menu-title">Billing</span>
                        </a>
                    </div>

                    <div class="menu-item me-0 me-lg-2">
                        <a href="{{ route('admin.settings.index') }}" class="menu-link app-top-nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                            <span class="menu-icon">
                                <i class="ki-outline ki-setting-2 fs-3"></i>
                            </span>
                            <span class="menu-title">Settings</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm app-logout-btn">Logout</button>
            </form>
        </div>
    </div>
</div>
