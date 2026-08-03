<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSRF Token -->

    @php
        $website_nm = \App\Models\Utility::getWebsiteName();
        $website_img = \App\Models\Utility::websiteLogo();
         $default_img = \App\Models\Utility::defaultImage();
    @endphp

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>  {{ $website_nm ?? '' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />



    <script src="//cdn.jsdelivr.net/gh/alpinejs/alpine@v2.3.5/dist/alpine.min.js" defer></script>


    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ !empty($website_img) ? $website_img : $default_img }}">
    <!-- jsvectormap css -->
    <link href="{{ asset('public/build/assets/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <!--Swiper slider css-->
    <link href="{{ asset('public/build/assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('public/build/assets/libs/dragula/dragula.min.css') }}" />
    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    <!-- Layout config Js -->
    <script src="{{ asset('public/build/assets/js/layout.js') }}"></script>
    <!-- Bootstrap Css -->
    {{-- <link href="{{ asset('public/build/assets/css/bootstrap-rtl.min.css') }}" rel="stylesheet" type="text/css" /> --}}
     <link href="{{ asset('public/build/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Icons Css -->
    <link href="{{ asset('public/build/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    {{-- <link href="{{ asset('public/build/assets/css/app-rtl.min.css') }}" rel="stylesheet" type="text/css" />--}}
    <link href="{{ asset('public/build/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- custom Css-->
    {{-- <link href="{{ asset('public/build/assets/css/custom-rtl.min.css') }}" rel="stylesheet" type="text/css" /> --}}
    <link href="{{ asset('public/build/assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />

    <link rel='stylesheet' href='//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css'>
    <link rel="stylesheet" href="{{ asset('public/build/assets/css/sticky-notes.css') }}">
    <!-- Jquery cdn -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <script>
        (function () {
            var isProductsPage = typeof window !== 'undefined' && /\/products(?:[\/?]|$)/.test(window.location.pathname);
            var originalAlert = window.alert;

            function isLegacyProductDataTableWarning(message) {
                return typeof message === 'string'
                    && message.indexOf('DataTables warning') !== -1
                    && message.indexOf('productList') !== -1;
            }

            window.alert = function (message) {
                if (isLegacyProductDataTableWarning(message)) {
                    console.warn('Suppressed legacy productList DataTables alert:', message);
                    return;
                }

                return originalAlert.apply(window, arguments);
            };

            if (!isProductsPage) {
                return;
            }

            function suppressDataTableErrors() {
                if (window.jQuery && $.fn && $.fn.dataTable) {
                    $.fn.dataTable.ext.errMode = 'none';
                }
            }

            window.addEventListener('error', function (event) {
                if (isLegacyProductDataTableWarning(event.message || '')) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    return false;
                }
            }, true);

            document.addEventListener('DOMContentLoaded', function () {
                suppressDataTableErrors();
            });

            var suppressorTicks = 0;
            var suppressor = window.setInterval(function () {
                suppressDataTableErrors();
                suppressorTicks++;

                if (suppressorTicks >= 20) {
                    window.clearInterval(suppressor);
                }
            }, 250);
        })();
    </script>

    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">


    <style>
        :root {
            --ui-bg: #f5f7fb;
            --ui-bg-soft: #eef2f8;
            --ui-surface: rgba(255, 255, 255, 0.92);
            --ui-surface-strong: #ffffff;
            --ui-border: #e2e8f0;
            --ui-border-strong: #d6e0ec;
            --ui-text: #0f172a;
            --ui-muted: #64748b;
            --ui-accent: #0f766e;
            --ui-accent-soft: #e6fffb;
            --ui-accent-2: #2563eb;
            --ui-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            --ui-shadow-soft: 0 10px 24px rgba(15, 23, 42, 0.05);
            --ui-radius: 22px;
            --ui-radius-md: 16px;
            --ui-radius-sm: 12px;
            --modern-sidebar-width: 286px;
            --modern-sidebar-gap: 14px;
            --modern-shell-offset: calc(var(--modern-sidebar-width) + var(--modern-sidebar-gap) * 2);
        }

        html,
        body {
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.08), transparent 24%),
                var(--ui-bg) !important;
            color: var(--ui-text);
            font-family: 'Instrument Sans', sans-serif;
        }

        h1, h2, h3, h4, h5, h6,
        .card-title,
        .navbar-brand-box .logo-lg,
        .user-name-text {
            font-family: 'Manrope', sans-serif;
        }

        #layout-wrapper,
        .main-content {
            background: transparent !important;
        }

        .layout-width,
        .container-fluid {
            max-width: 1500px;
        }

        #page-topbar {
            background: rgba(255, 255, 255, 0.82) !important;
            backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.04);
        }

        .navbar-header {
            min-height: 78px;
            gap: 1rem;
        }

        .main-content .page-content {
            padding: calc(70px + 1.4rem) 0 1.8rem;
        }

        .breadcrumb {
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid var(--ui-border);
            border-radius: 999px;
            padding: 0.5rem 0.85rem;
            gap: 0.35rem;
        }

        .breadcrumb-item,
        .breadcrumb-item a {
            color: var(--ui-muted);
            font-size: 0.83rem;
            font-weight: 600;
            text-decoration: none;
        }

        .card {
            background: var(--ui-surface) !important;
            border: 1px solid rgba(255, 255, 255, 0.75) !important;
            border-radius: var(--ui-radius) !important;
            box-shadow: var(--ui-shadow-soft);
            overflow: hidden;
        }

        .card-header,
        .card-footer {
            background: transparent !important;
            border-color: rgba(226, 232, 240, 0.8) !important;
        }

        .card-header {
            padding: 1.05rem 1.2rem !important;
        }

        .card-body {
            padding: 1.2rem !important;
        }

        .card-title {
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--ui-text);
        }

        .app-menu.navbar-menu {
            background: rgba(255, 255, 255, 0.84) !important;
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 14px 0 34px rgba(15, 23, 42, 0.04);
        }

        @media (min-width: 768px) {
            .app-menu.navbar-menu {
                width: var(--modern-sidebar-width) !important;
            }

            #page-topbar {
                left: var(--modern-shell-offset) !important;
                right: 0;
                width: auto !important;
            }

            .main-content {
                margin-left: var(--modern-shell-offset) !important;
            }

            .footer {
                left: var(--modern-shell-offset) !important;
            }
        }

        @media (max-width: 767.98px) {
            #page-topbar {
                left: 0 !important;
                width: 100% !important;
            }

            .main-content,
            .footer {
                margin-left: 0 !important;
                left: 0 !important;
            }
        }

        .navbar-brand-box {
            background: transparent !important;
            border-bottom: 1px solid rgba(226, 232, 240, 0.7);
        }

        #scrollbar .container-fluid {
            padding: 1rem 0.85rem 1.5rem;
        }

        .navbar-nav .menu-title {
            margin: 0.4rem 0 0.65rem;
            padding: 0 0.65rem;
        }

        .navbar-nav .menu-title span {
            color: var(--ui-muted);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .navbar-nav .nav-link.menu-link,
        .navbar-nav .menu-dropdown .nav-link {
            border-radius: 14px !important;
            color: var(--ui-text) !important;
            font-weight: 700;
            transition: background-color .18s ease, color .18s ease, transform .18s ease;
        }

        .navbar-nav .nav-link.menu-link {
            margin-bottom: 0.28rem;
            padding: 0.82rem 0.9rem !important;
        }

        .navbar-nav .menu-dropdown {
            margin: 0.25rem 0 0.65rem;
            padding: 0.45rem;
            border-radius: 18px;
            background: rgba(248, 250, 252, 0.82);
            border: 1px solid rgba(226, 232, 240, 0.82);
        }

        .navbar-nav .menu-dropdown .nav-link {
            font-size: 0.92rem;
            margin: 0.14rem 0;
            padding: 0.72rem 0.85rem !important;
        }

        .navbar-nav .nav-link.menu-link:hover,
        .navbar-nav .nav-link.menu-link.active,
        .navbar-nav .menu-dropdown .nav-link:hover,
        .navbar-nav .menu-dropdown .nav-link.active {
            background: linear-gradient(135deg, rgba(15, 118, 110, 0.14) 0%, rgba(37, 99, 235, 0.14) 100%) !important;
            color: #0f172a !important;
            transform: translateX(2px);
        }

        .navbar-nav .nav-link.menu-link i {
            color: #2563eb;
        }

        .text-muted {
            color: var(--ui-muted) !important;
        }

        .btn {
            border-radius: 14px !important;
            font-weight: 700 !important;
            padding: 0.7rem 1rem;
            box-shadow: none !important;
        }

        .btn-sm {
            border-radius: 12px !important;
            padding: 0.56rem 0.9rem !important;
        }

        .btn-primary,
        .bg-primary {
            background: linear-gradient(135deg, #0f766e 0%, #2563eb 100%) !important;
            border-color: transparent !important;
        }

        .btn-success {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%) !important;
            border-color: transparent !important;
        }

        .btn-info,
        .btn-soft-secondary,
        .btn-light {
            background: rgba(255, 255, 255, 0.86) !important;
            border: 1px solid var(--ui-border) !important;
            color: var(--ui-text) !important;
        }

        .btn-info:hover,
        .btn-soft-secondary:hover,
        .btn-light:hover {
            background: #ffffff !important;
            border-color: var(--ui-border-strong) !important;
        }

        .badge {
            border-radius: 999px;
            padding: 0.45rem 0.7rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .alert {
            border: 1px solid transparent;
            border-radius: 18px;
            padding: 0.95rem 1rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        }

        .alert-success {
            background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%) !important;
            border-color: #bbf7d0 !important;
            color: #166534 !important;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fff1f2 100%) !important;
            border-color: #fecaca !important;
            color: #b91c1c !important;
        }

        .alert-warning {
            background: linear-gradient(135deg, #fff7ed 0%, #fffbeb 100%) !important;
            border-color: #fed7aa !important;
            color: #b45309 !important;
        }

        .alert-info {
            background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%) !important;
            border-color: #bfdbfe !important;
            color: #1d4ed8 !important;
        }

        .bg-primary-subtle {
            background: #dbeafe !important;
        }

        .text-primary {
            color: #1d4ed8 !important;
        }

        .form-control,
        .form-select,
        div.dataTables_wrapper div.dataTables_filter input,
        .dataTables_length select {
            border-radius: 14px !important;
            border: 1px solid var(--ui-border) !important;
            background: rgba(255, 255, 255, 0.92) !important;
            color: var(--ui-text) !important;
            min-height: 44px;
            box-shadow: none !important;
        }

        .form-control:focus,
        .form-select:focus,
        div.dataTables_wrapper div.dataTables_filter input:focus,
        .dataTables_length select:focus {
            border-color: rgba(37, 99, 235, 0.35) !important;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.08) !important;
        }

        .table {
            color: var(--ui-text);
            margin-bottom: 0;
        }

        .table > :not(caption) > * > * {
            background: rgba(255, 255, 255, 0.8);
            border-bottom-color: #edf2f7;
            padding: 1rem 0.95rem;
        }

        .table > thead > tr > th {
            font-size: 0.76rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--ui-muted);
            background: rgba(248, 250, 252, 0.96);
            border-top: 0;
        }

        .table-striped > tbody > tr:nth-of-type(odd) > * {
            --bs-table-accent-bg: rgba(248, 250, 252, 0.86);
            color: inherit;
        }

        .table-responsive,
        .dataTables_wrapper {
            border-radius: 18px;
        }

        .nav-tabs,
        .nav-pills {
            gap: 0.5rem;
            border-bottom: 0 !important;
        }

        .nav-tabs .nav-link,
        .nav-pills .nav-link,
        .nav-tabs-custom .nav-link,
        .card-header-tabs .nav-link,
        .custom-nav .nav-link {
            border: 1px solid var(--ui-border) !important;
            border-radius: 999px !important;
            background: rgba(255, 255, 255, 0.86) !important;
            color: var(--ui-muted) !important;
            font-weight: 700;
            padding: 0.68rem 1rem;
            transition: background-color .18s ease, color .18s ease, border-color .18s ease, transform .18s ease;
        }

        .nav-tabs .nav-link:hover,
        .nav-pills .nav-link:hover,
        .nav-tabs-custom .nav-link:hover,
        .card-header-tabs .nav-link:hover,
        .custom-nav .nav-link:hover,
        .nav-tabs .nav-link.active,
        .nav-pills .nav-link.active,
        .nav-tabs-custom .nav-link.active,
        .card-header-tabs .nav-link.active,
        .custom-nav .nav-link.active {
            background: linear-gradient(135deg, rgba(15, 118, 110, 0.12) 0%, rgba(37, 99, 235, 0.14) 100%) !important;
            border-color: rgba(37, 99, 235, 0.2) !important;
            color: var(--ui-text) !important;
            transform: translateY(-1px);
        }

        .table-hover > tbody > tr:hover > * {
            --bs-table-accent-bg: #eef6ff;
            color: var(--ui-text);
        }

        .dataTables_wrapper .row {
            --bs-gutter-y: 0.9rem;
            align-items: center;
        }

        .dataTables_wrapper .row:first-child,
        .dataTables_wrapper .row:last-child {
            margin-left: 0;
            margin-right: 0;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            padding: 0.2rem 0;
        }

        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            color: var(--ui-muted);
            font-size: 0.86rem;
            font-weight: 700;
        }

        .dataTables_wrapper .dataTables_filter input {
            min-width: 240px;
            margin-left: 0.6rem !important;
        }

        .dataTables_wrapper .dataTables_info {
            color: var(--ui-muted) !important;
            font-size: 0.88rem;
            font-weight: 600;
        }

        .dataTables_wrapper .dataTables_paginate {
            display: flex;
            justify-content: flex-end;
        }

        .dataTables_wrapper .pagination {
            gap: 0.45rem;
            margin-bottom: 0;
        }

        .dataTables_wrapper .page-item .page-link {
            border: 1px solid var(--ui-border);
            border-radius: 12px !important;
            min-width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--ui-text);
            background: rgba(255, 255, 255, 0.94);
            font-weight: 700;
            box-shadow: none !important;
        }

        .dataTables_wrapper .page-item.active .page-link {
            background: linear-gradient(135deg, #0f766e 0%, #2563eb 100%);
            border-color: transparent;
            color: #ffffff;
        }

        .dataTables_wrapper .page-item.disabled .page-link {
            color: #94a3b8;
            background: rgba(248, 250, 252, 0.94);
        }

        .dropdown-menu {
            border: 1px solid var(--ui-border) !important;
            border-radius: 16px !important;
            box-shadow: var(--ui-shadow) !important;
            padding: 0.55rem;
        }

        .dropdown-item {
            border-radius: 10px;
            font-weight: 600;
            padding: 0.72rem 0.85rem;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background: #f8fafc;
        }

        .app-search .form-control {
            min-width: 320px;
            padding-left: 2.8rem;
            background: rgba(248, 250, 252, 0.92) !important;
        }

        .topbar-heading-label {
            color: var(--ui-muted);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .topbar-heading-title {
            color: var(--ui-text);
            font-family: 'Manrope', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .search-widget-icon {
            color: var(--ui-muted);
        }

        .btn-topbar,
        .topbar-user .btn {
            background: rgba(248, 250, 252, 0.94) !important;
            border: 1px solid var(--ui-border) !important;
            color: var(--ui-text) !important;
        }

        .header-profile-user {
            border: 2px solid rgba(37, 99, 235, 0.14);
        }

        .search-member-avatar {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            object-fit: cover;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
        }

        .dropdown-icon-item {
            border-radius: 14px;
            padding: 0.95rem 0.45rem;
            transition: background-color .18s ease, transform .18s ease;
        }

        .dropdown-icon-item:hover,
        .dropdown-icon-item:focus {
            background: #f8fafc;
            transform: translateY(-1px);
        }

        .topbar-alert {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin: 1rem 0 0;
            padding: 0.9rem 1rem;
            border: 1px solid #fecaca;
            border-radius: 16px;
            background: linear-gradient(135deg, #fff1f2 0%, #fff7ed 100%);
            color: #9f1239;
            box-shadow: 0 12px 24px rgba(244, 63, 94, 0.08);
        }

        .accordion-item {
            border: 1px solid rgba(226, 232, 240, 0.85) !important;
            border-radius: 18px !important;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.88);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        }

        .accordion-button {
            background: rgba(248, 250, 252, 0.88) !important;
            color: var(--ui-text) !important;
            font-weight: 700;
            box-shadow: none !important;
        }

        .accordion-button:not(.collapsed) {
            background: linear-gradient(135deg, rgba(15, 118, 110, 0.08) 0%, rgba(37, 99, 235, 0.1) 100%) !important;
            color: var(--ui-text) !important;
        }

        .accordion-button:focus {
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.08) !important;
        }

        .list-group-item {
            border-color: rgba(226, 232, 240, 0.85) !important;
            background: rgba(255, 255, 255, 0.88) !important;
        }

        .footer {
            background: transparent;
            border-top: 1px solid rgba(226, 232, 240, 0.6);
            color: var(--ui-muted);
        }

        .modal-content {
            border: 1px solid rgba(226, 232, 240, 0.85) !important;
            border-radius: 24px !important;
            box-shadow: 0 24px 54px rgba(15, 23, 42, 0.16) !important;
            overflow: hidden;
        }

        .modal-header,
        .modal-footer {
            border-color: rgba(226, 232, 240, 0.8) !important;
        }

        @media (max-width: 991.98px) {
            .app-search .form-control {
                min-width: 220px;
            }

            .topbar-alert {
                flex-direction: column;
                align-items: flex-start;
            }

            .main-content .page-content {
                padding-top: calc(70px + 1rem);
            }
        }
   </style>


    @yield('page-css')
</head>
