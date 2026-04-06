<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<title>@yield('title', 'Admin Dashboard')</title>

<link rel="shortcut icon" href="{{ asset('assets/media/logos/favicon.ico') }}" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
<link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

<style>
    :root {
        --app-bg-start: #f5f8ff;
        --app-bg-end: #eef3ff;
        --app-nav-text: #5a6480;
        --app-nav-active-bg: #e7efff;
        --app-nav-active-text: #2d4ea2;
        --app-card-border: #e6ecfb;
        --app-primary: #355ec9;
        --app-primary-soft: #e6eeff;
    }

    body.app-default {
        background: linear-gradient(180deg, var(--app-bg-start) 0%, var(--app-bg-end) 100%);
    }

    #kt_app_header {
        background: #ffffff;
        box-shadow: 0 6px 16px rgba(42, 71, 144, 0.08);
        border-bottom: 1px solid #e6ecfb;
    }

    .app-brand-logo {
        max-height: 30px;
        width: auto;
    }

    .app-top-nav-link {
        border-radius: 9px;
        color: var(--app-nav-text) !important;
        transition: all 0.2s ease;
        padding-inline: 12px;
        margin-inline: 2px;
    }

    .app-top-nav-link .menu-icon i {
        color: inherit;
    }

    .app-top-nav-link:hover {
        background: #f1f5ff;
        color: #3e5aa8 !important;
    }

    .app-top-nav-link.active {
        background: var(--app-nav-active-bg);
        color: var(--app-nav-active-text) !important;
        box-shadow: inset 0 0 0 1px #d5e2ff;
    }

    .app-logout-btn {
        background: #fff1f1;
        border: 1px solid #ffd4d4;
        color: #be3030;
    }

    .app-logout-btn:hover {
        background: #ffe3e3;
        color: #9f2424;
    }

    .card {
        border: 1px solid var(--app-card-border);
        box-shadow: 0 8px 20px rgba(57, 94, 187, 0.08);
        border-radius: 14px;
    }

    .btn.btn-primary {
        background-color: var(--app-primary);
        border-color: var(--app-primary);
    }

    .btn.btn-light-primary {
        background-color: var(--app-primary-soft);
        color: var(--app-primary);
    }

    .app-alert {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        background: #ffffff;
        border: 1px solid #e4ebfc;
        border-radius: 12px;
        padding: 14px 16px;
        box-shadow: 0 6px 14px rgba(41, 70, 140, 0.06);
    }

    .app-alert-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .app-alert-content {
        min-width: 0;
    }

    .app-alert-title {
        margin: 0 0 4px 0;
        font-size: 0.95rem;
        font-weight: 700;
    }

    .app-alert-text {
        margin: 0;
        color: #5e6785;
    }

    .app-alert-success {
        border-color: #c5edd5;
        background: #f4fcf7;
    }

    .app-alert-success .app-alert-icon {
        background: #d7f5e3;
        color: #1f9d57;
    }

    .app-alert-success .app-alert-title {
        color: #198a4c;
    }

    .app-alert-danger {
        border-color: #f8cfd4;
        background: #fff7f8;
    }

    .app-alert-danger .app-alert-icon {
        background: #ffe5e8;
        color: #df445a;
    }

    .app-alert-danger .app-alert-title {
        color: #c63649;
    }
</style>

@stack('styles')
