<header id="page-topbar">
    @php
        $default_img = \App\Models\Utility::defaultImage();
        $brandLogo = asset('public/build/assets/images/engage-logo.png');
        $currentTenant = app()->bound('currentTenant') ? app('currentTenant') : null;
        $isSuperAdmin = \Auth::check() && \Auth::user()->type === 'super admin';
        $tenantOptions = $isSuperAdmin
            ? \App\Models\Tenant::query()->orderBy('name')->get(['id', 'name', 'slug'])
            : collect();
    @endphp
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="{{ route('dashboard') }}" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{ $brandLogo }}" alt="Engage Net" height="38">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ $brandLogo }}" alt="Engage Net" height="42">
                        </span>
                    </a>

                    <a href="{{ route('dashboard') }}" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{ $brandLogo }}" alt="Engage Net" height="38">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ $brandLogo }}" alt="Engage Net" height="42">
                        </span>
                    </a>
                </div>

                <button type="button"
                    class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none"
                    id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>

                <!-- App Search-->
                <form class="app-search d-none d-md-block">
                    <div class="position-relative">
                        <input type="text" class="form-control" placeholder="Search..." autocomplete="off"
                            id="search-options" value="">
                        <span class="mdi mdi-magnify search-widget-icon"></span>
                        <span class="mdi mdi-close-circle search-widget-icon search-widget-icon-close d-none"
                            id="search-close-options"></span>
                    </div>
                    <div class="dropdown-menu dropdown-menu-lg" id="search-dropdown">
                        <div data-simplebar style="max-height: 320px;">
                            <!-- item-->
                            <div class="dropdown-header">
                                <h6 class="text-overflow text-muted mb-0 text-uppercase">Recent Searches</h6>
                            </div>

                            <div class="dropdown-item bg-transparent text-wrap">
                                <a href="#" class="btn btn-soft-secondary btn-sm rounded-pill">how to setup <i
                                        class="mdi mdi-magnify ms-1"></i></a>
                                <a href="#" class="btn btn-soft-secondary btn-sm rounded-pill">buttons <i
                                        class="mdi mdi-magnify ms-1"></i></a>
                            </div>
                            <!-- item-->
                            <div class="dropdown-header mt-2">
                                <h6 class="text-overflow text-muted mb-1 text-uppercase">Pages</h6>
                            </div>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <i class="ri-bubble-chart-line align-middle fs-18 text-muted me-2"></i>
                                <span>Analytics Dashboard</span>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <i class="ri-lifebuoy-line align-middle fs-18 text-muted me-2"></i>
                                <span>Help Center</span>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <i class="ri-user-settings-line align-middle fs-18 text-muted me-2"></i>
                                <span>My account settings</span>
                            </a>

                            <!-- item-->
                            <div class="dropdown-header mt-2">
                                <h6 class="text-overflow text-muted mb-2 text-uppercase">Members</h6>
                            </div>

                            <div class="notification-list">
                                <!-- item -->
                                <a href="javascript:void(0);" class="dropdown-item notify-item py-2">
                                    <div class="d-flex">
                                        <img src="assets/images/users/avatar-2.jpg"
                                            class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                        <div class="flex-grow-1">
                                            <h6 class="m-0">Angela Bernier</h6>
                                            <span class="fs-11 mb-0 text-muted">Manager</span>
                                        </div>
                                    </div>
                                </a>
                                <!-- item -->
                                <a href="javascript:void(0);" class="dropdown-item notify-item py-2">
                                    <div class="d-flex">
                                        <img src="assets/images/users/avatar-3.jpg"
                                            class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                        <div class="flex-grow-1">
                                            <h6 class="m-0">David Grasso</h6>
                                            <span class="fs-11 mb-0 text-muted">Web Designer</span>
                                        </div>
                                    </div>
                                </a>
                                <!-- item -->
                                <a href="javascript:void(0);" class="dropdown-item notify-item py-2">
                                    <div class="d-flex">
                                        <img src="assets/images/users/avatar-5.jpg"
                                            class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                        <div class="flex-grow-1">
                                            <h6 class="m-0">Mike Bunch</h6>
                                            <span class="fs-11 mb-0 text-muted">React Developer</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="text-center pt-3 pb-1">
                            <a href="#" class="btn btn-primary btn-sm">View All Results <i
                                    class="ri-arrow-right-line ms-1"></i></a>
                        </div>
                    </div>
                </form>
            </div>

            @php
                $settingsConnection = app()->bound('currentTenant') ? 'tenant' : 'landlord';
                $isError = DB::connection($settingsConnection)->table('settings')
                            ->where('name', 'facebook_token_is_error')
                            ->where('created_by', auth()->id())
                            ->value('value');
            @endphp

            @if($isError == '1')
                <div style="background-color: #f8d7da; color: #721c24; padding: 2px; border: 1px solid #f5c6cb; border-radius: 4px;margin:20px 20px 20px 20px;">
                    <strong>⚠️ Connection Expired:</strong> Your Facebook connection token has expired for security reasons. Please click the button below to re-authenticate.
                    <a href="{{ route('facebooks.create') }}" class="btn btn-info btn-sm" style="margin-left: 15px;">Reconnect Facebook</a>
                </div>
            @endif

            <div class="d-flex align-items-center">
                @if ($currentTenant)
                    <span class="badge rounded-pill bg-success-subtle text-success me-2 d-none d-md-inline">
                        Tenant: {{ $currentTenant->name }} ({{ $currentTenant->slug }})
                    </span>
                @endif

                @if ($isSuperAdmin)
                    <form method="POST" action="{{ route('setting.tenancy.switch-context') }}" class="d-none d-md-flex align-items-center me-2">
                        @csrf
                        <select name="tenant_id" class="form-select form-select-sm me-2" style="min-width: 220px;" onchange="this.form.submit()">
                            <option value="">Switch Tenant Context</option>
                            @foreach ($tenantOptions as $tenantOption)
                                <option value="{{ $tenantOption->id }}" {{ (int) optional($currentTenant)->id === (int) $tenantOption->id ? 'selected' : '' }}>
                                    {{ $tenantOption->name }} ({{ $tenantOption->slug }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                    @if ($currentTenant)
                        <form method="POST" action="{{ route('setting.tenancy.clear-context') }}" class="d-none d-md-inline me-2">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Clear Tenant</button>
                        </form>
                    @endif
                @endif

                <div class="dropdown d-md-none topbar-head-dropdown header-item">
                    <button type="button"
                        class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle"
                        id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <i class="bx bx-search fs-22"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                        aria-labelledby="page-header-search-dropdown">
                        <form class="p-3">
                            <div class="form-group m-0">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search ..."
                                        aria-label="Recipient's username">
                                    <button class="btn btn-primary" type="submit"><i
                                            class="mdi mdi-magnify"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>


                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button"
                        class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle"
                        data-toggle="fullscreen">
                        <i class='bx bx-fullscreen fs-22'></i>
                    </button>
                </div>
                <div class="dropdown topbar-head-dropdown ms-1 header-item">
                    <button type="button"
                        class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-category-alt fs-22"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-xl p-0 dropdown-menu-end" style="">
                        <div class="p-3 border-top-0 border-start-0 border-end-0 border-dashed border">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0 fw-semibold fs-15">Quick Link</h6>
                                </div>
                            </div>
                        </div>

                        <div class="p-2">
                            <div class="row g-0">
                                @can('manage lead')
                                    <div class="col-4">
                                        <a class="dropdown-icon-item" href="{{ route('leads.list') }}">
                                            <i class="ri-stack-line fs-22"></i>
                                            <span>Lead</span>
                                        </a>
                                    </div>
                                @endcan
                                @can('manage quote')
                                    <div class="col-4">
                                        <a class="dropdown-icon-item" href="{{ route('quotes.index') }}">
                                            <i class="ri-stack-line fs-22"></i>
                                            <span>quatetion</span>
                                        </a>
                                    </div>
                                @endcan
                                @can('manage order')
                                    <div class="col-4">
                                        <a class="dropdown-icon-item" href="{{ route('orders.index') }}">
                                            <i class="ri-stack-line fs-22"></i>
                                            <span>Order</span>
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user"
                                src="{{ \Auth::user()->avatar ? \Auth::user()->avatar : $default_img }}"
                                alt="Header Avatar">
                            <span class="text-start ms-xl-2">
                                <span
                                    class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ \Auth::user()->name ?? '' }}</span>

                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <h6 class="dropdown-header">Welcome {{ \Auth::user()->name ?? '' }}</h6>

                        <a class="dropdown-item" href="{{ route('user_profile.edit', \Auth::user()->id) }}">
                            <i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle">Profile</span>
                        </a>

                        @if (\Auth::user()->type == 'company')
                            <a class="dropdown-item" href="{{ route('settings.edit', \Auth::user()->id) }}">
                                <i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i>
                                <span class="align-middle">Settings</span>
                            </a>
                        @endif
                        <a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                                            document.getElementById('logout-form').submit();">
                            <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle" data-key="t-logout">{{ __('Logout') }}</span>
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

@include('layouts.inc.sidebar')
