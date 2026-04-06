<!DOCTYPE html>
<html lang="en">
<head>
    @include('admin.layout.components.head')
</head>
<body
    id="kt_app_body"
    data-kt-app-header-fixed="true"
    class="app-default"
>
<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
        @include('admin.layout.components.top-navbar')

        <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
            <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                <div class="d-flex flex-column flex-column-fluid">
                    <div id="kt_app_content" class="app-content flex-column-fluid">
                        <div id="kt_app_content_container" class="app-container container-xxl">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-6 pb-4">
                                <h1 class="text-gray-900 fw-bold fs-3 m-0">@yield('page-title', 'Dashboard')</h1>
                                @yield('page-actions')
                            </div>

                            @include('admin.layout.components.alerts')
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.layout.components.scripts')
</body>
</html>
