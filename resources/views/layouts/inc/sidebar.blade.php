{{-- <!-- ========== App Menu ========== -->
@php
    $website_nm = \App\Models\Utility::getWebsiteName();
    $website_short_nm = \App\Models\Utility::getWebsiteShortName();
    $brandLogo = asset('public/build/assets/images/engage-logo.png');
@endphp
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ route('dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <h2 class="text-light py-2">
                    KK
                </h2>
                <!-- <img src="assets/images/logo-sm.png" alt="" height="22"> -->
            </span>
            <span class="logo-lg">
                <h2 class="text-black py-2">KK Products</h2>
                <!-- <img src="assets/images/logo-dark.png" alt="" height="17"> -->
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ route('dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                <h2 class="text-light py-2">{{ $website_short_nm ?? '' }} </h2>
                <!-- <img src="assets/images/logo-sm.png" alt="" height="22"> -->
            </span>
            <span class="logo-lg">
                <h2 class="text-light py-2">{{ $website_nm ?? '' }}</h2>
                <!-- <img src="assets/images/logo-light.png" alt="" height="17"> -->
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>

            @php
                $isCustomerRoute = request()->routeIs('customers.*');
                $isVendorRoute = request()->routeIs('vendors.*');
                $isTransportRoute = request()->routeIs('transports.*');
                $isEntityRoute = $isCustomerRoute || $isVendorRoute || $isTransportRoute;

                $isDashboardRoute = request()->routeIs('dashboard');

                $isUserRoute = request()->routeIs('users.*');
                $isRoleRoute = request()->routeIs('roles.*');
                $isUserMangRoute = $isUserRoute || $isRoleRoute;
            @endphp

            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span>Main Menu</span></li>
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}"
                        class="nav-link menu-link {{ $isDashboardRoute ? 'active' : '' }}">
                        <i class="ri-dashboard-2-line"></i> <span>Dashboards</span>
                    </a>
                </li>


                @can('manage user')
                    <li class="nav-item {{ $isUserMangRoute ? 'active' : '' }}">
                        <a class="nav-link menu-link" href="#user-management-setion" data-bs-toggle="collapse"
                            role="button" aria-expanded="false" aria-controls="user-management-setion">
                            <i class="ri-account-circle-line"></i> <span>{{ __('User Management') }}</span>
                        </a>
                        <div class="collapse menu-dropdown {{ $isUserMangRoute ? 'show' : '' }}"
                            id="user-management-setion">
                            <ul class="nav nav-sm flex-column">
                                @can('manage role')
                                    <li class="nav-item">
                                        <a href="{{ route('roles.index') }}"
                                            class="nav-link {{ $isRoleRoute ? 'active' : '' }}">{{ __('Assign roles') }}</a>
                                    </li>
                                @endcan

                                <li class="nav-item">
                                    <a href="{{ route('users') }}"
                                        class="nav-link {{ $isUserRoute ? 'active' : '' }}">{{ __('User Operation') }}</a>
                                </li>

                            </ul>
                        </div>
                    </li>
                @endcan

                @if (\Auth::user()->can('manage customer') || \Auth::user()->can('manage vender') || \Auth::user()->can('manage transport'))
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $isEntityRoute ? 'active' : '' }}" href="#entity-setion"
                            data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $isEntityRoute ? 'true' : 'false' }}" aria-controls="entity-setion">
                            <i class="ri-contacts-book-2-line"></i> <span>Customer Network</span>
                        </a>
                        <div class="collapse menu-dropdown {{ $isEntityRoute ? 'show' : '' }}" id="entity-setion">
                            <ul class="nav nav-sm flex-column">
                                @can('manage customer')
                                    <li class="nav-item">
                                        <a href="{{ route('customers.index') }}"
                                            class="nav-link {{ $isCustomerRoute ? 'active' : '' }}">Customers</a>
                                    </li>
                                @endcan
                                @can('manage vender')
                                    <li class="nav-item">
                                        <a href="{{ route('vendors.index') }}"
                                            class="nav-link {{ $isVendorRoute ? 'active' : '' }}">Vendor</a>
                                    </li>
                                @endcan
                                @can('manage transport')
                                    <li class="nav-item">
                                        <a href="{{ route('transports.index') }}"
                                            class="nav-link {{ $isTransportRoute ? 'active' : '' }}">Transport Partners</a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endif


                @if (\Auth::user()->can('manage product & service'))
                    <li class="nav-item">
                        <a href="{{ route('products.index') }}" class="nav-link menu-link">
                            <i class="ri-stack-line"></i> <span>Products</span>
                        </a>
                    </li>
                @endif

                @php
                    $isEmployeeRoute = request()->routeIs('employees.*');
                    $isDepartmentRoute = request()->routeIs('departments.*');
                    $isDesignationRoute = request()->routeIs('designations.*');
                    $isHolidayRoute = request()->routeIs('holidays.*');
                    $isLeaveRoute = request()->routeIs('leaves.*');
                    $isAttendanceRoute = request()->routeIs('attendances.*');
                    $isAttendanceReportRoute = request()->routeIs('attendances.report');
                    $isSalesTargetRoute = request()->routeIs('sales-targets.*');
                    $isPayRollRoute = request()->routeIs('payrolls.*');
                    $isWorkingHoursRoute = request()->routeIs('working-hours.*');
                    $isLeaveRuleRoute = request()->routeIs('leave-rules.*');

                    //sales-emp-target
                    $isSalesEmplTargetRoute = request()->routeIs('sales-employee-targets.*');

                    $isLeaveTypeRoute = request()->routeIs('leave-types.*');

                    $isHrRoute =
                        $isEmployeeRoute ||
                        $isDepartmentRoute ||
                        $isDesignationRoute ||
                        $isHolidayRoute ||
                        $isLeaveRoute ||
                        $isAttendanceRoute ||
                        $isSalesTargetRoute ||
                        $isPayRollRoute ||
                        $isWorkingHoursRoute ||
                        $isLeaveRuleRoute ||
                        $isSalesEmplTargetRoute || $isLeaveTypeRoute;

                    $isOrderRoute = request()->routeIs('orders.*');

                    $isLeadRoute = request()->routeIs('leads.*');
                    $isNewLeadRoute = request()->routeIs('leads.new_lead_list');
                    $isNewAssignRoute = request()->routeIs('leads.new_assign_lead');

                    $isQuoteRoute = request()->routeIs('quotes.*');
                    $isPaymentRoute = request()->routeIs('payments.*');
                    $isSpankoRoute = request()->routeIs('spanko.*');

                    //follow-up
                    $currentSlug = request()->route('slug');
                    $isFollowUpRoute = request()->routeIs('follow-ups.*');
                    $isFollowUpManagementRoute = $isFollowUpRoute;



                    $isSalesRoute =
                        $isLeadRoute ||
                        $isQuoteRoute ||
                        $isPaymentRoute ||
                        $isSpankoRoute ||
                        $isNewLeadRoute ||
                        $isNewAssignRoute ||
                        $isFollowUpManagementRoute;

                    $isLeadData = $isLeadRoute || $isNewLeadRoute || $isNewAssignRoute;

                @endphp

                @if (\Auth::user()->can('manage employee') || \Auth::user()->can('manage department') || \Auth::user()->can('manage designation') || \Auth::user()->can('manage attendance') || \Auth::user()->can('manage attendance report') || \Auth::user()->can('manage leave') || \Auth::user()->can('manage holiday'))
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $isHrRoute ? 'active' : '' }}" href="#hr-setion"
                            data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $isHrRoute ? 'true' : 'false' }}" aria-controls="hr-setion">
                            <i class="ri-apps-2-line"></i> <span>HR</span>
                        </a>
                        <div class="collapse menu-dropdown {{ $isHrRoute ? 'show' : '' }}" id="hr-setion">
                            <ul class="nav nav-sm flex-column">
                                @can('manage employee')
                                    <li class="nav-item">
                                        <a href="{{ route('employees.index') }}"
                                            class="nav-link {{ $isEmployeeRoute ? 'active' : '' }}">Employees</a>
                                    </li>
                                @endcan
                                @can('manage sales target')
                                    <li class="nav-item">
                                        <a href="{{ route('sales-targets.index') }}"
                                            class="nav-link {{ $isSalesTargetRoute ? 'active' : '' }}">Sales Target</a>
                                    </li>
                                @endcan
                                @can('manage department')
                                    <li class="nav-item">
                                        <a href="{{ route('departments.index') }}"
                                            class="nav-link {{ $isDepartmentRoute ? 'active' : '' }}">Departments</a>
                                    </li>
                                @endcan
                                @can('manage designation')
                                    <li class="nav-item">
                                        <a href="{{ route('designations.index') }}"
                                            class="nav-link {{ $isDesignationRoute ? 'active' : '' }}">Designations</a>
                                    </li>
                                @endcan
                                @can('manage attendance')
                                    <li class="nav-item">
                                        <a href="{{ route('attendances.index') }}"
                                            class="nav-link {{ $isAttendanceRoute ? 'active' : '' }}">Attendance</a>
                                    </li>
                                @endcan
                                @can('manage attendance report')
                                    <li class="nav-item">
                                        <a href="{{ route('attendances.report') }}"
                                            class="nav-link {{ $isAttendanceReportRoute ? 'active' : '' }}">Attendance
                                            Report</a>
                                    </li>
                                @endcan

                                @can('manage payroll')
                                    <li class="nav-item">
                                        <a href="{{ route('payrolls.index') }}"
                                            class="nav-link {{ $isPayRollRoute ? 'active' : '' }}">Payroll</a>
                                    </li>
                                @endcan

                                @can('manage leave')
                                    <li class="nav-item">
                                        <a href="{{ route('leaves.index') }}"
                                            class="nav-link {{ $isLeaveRoute ? 'active' : '' }}">Leave</a>
                                    </li>
                                @endcan
                                @can('manage holiday')
                                    <li class="nav-item">
                                        <a href="{{ route('holidays.index') }}"
                                            class="nav-link {{ $isHolidayRoute ? 'active' : '' }}">Holiday</a>
                                    </li>
                                @endcan

                                @can('manage working hours')
                                    <li class="nav-item">
                                        <a href="{{ route('working-hours.index') }}"
                                            class="nav-link {{ $isWorkingHoursRoute ? 'active' : '' }}">Working Hours</a>
                                    </li>
                                @endcan

                                @can('manage leave rule')
                                    <li class="nav-item">
                                        <a href="{{ route('leave-rules.edit', 1) }}"
                                            class="nav-link {{ $isLeaveRuleRoute ? 'active' : '' }}">Leave Rule</a>
                                    </li>
                                @endcan

                                @can('manage leave type')
                                    <li class="nav-item">
                                        <a href="{{ route('leave-types.index') }}" class="nav-link {{ $isLeaveTypeRoute ? 'active' : '' }}">Leave Type</a>
                                    </li>
                                @endcan


                                @can('manage sales_employee_target')
                                <li class="nav-item">
                                    <a href="{{ route('sales-employee-targets.index', 'all_months') }}"
                                        class="nav-link {{ $isSalesEmplTargetRoute ? 'active' : '' }}">Target</a>
                                </li>
                                @endcan



                            </ul>
                        </div>
                    </li>
                @endif


                @if (\Auth::user()->can('manage lead') || \Auth::user()->can('manage quote') || \Auth::user()->can('manage payment') || \Auth::user()->can('manage spanko'))
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $isSalesRoute ? 'active' : '' }}" href="#sales-section"
                            data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $isSalesRoute ? 'true' : 'false' }}" aria-controls="sales-section">
                            <i class="ri-apps-2-line"></i> <span>Sales</span>
                        </a>

                        <div class="collapse menu-dropdown {{ $isSalesRoute ? 'show' : '' }}" id="sales-section">
                            <ul class="nav nav-sm flex-column">

                                @can('manage quote')
                                    <li class="nav-item">
                                        <a href="{{ route('quotes.index') }}"
                                            class="nav-link {{ $isQuoteRoute ? 'active' : '' }}">Quote</a>
                                    </li>
                                @endcan

                                @can('manage payment')
                                    <li class="nav-item">
                                        <a href="{{ route('payments.index') }}"
                                            class="nav-link {{ $isPaymentRoute ? 'active' : '' }}">Payments</a>
                                    </li>
                                @endcan

                                @can('manage spanko')
                                    <li class="nav-item">
                                        <a href="{{ route('spanko.index') }}"
                                            class="nav-link {{ $isSpankoRoute ? 'active' : '' }}">Spanko</a>
                                    </li>
                                @endcan

                                @can('manage sales target')
                                    <li class="nav-item">
                                        <a href="{{ route('sales-targets.index') }}"
                                            class="nav-link {{ $isSalesTargetRoute ? 'active' : '' }}">Sales Target</a>
                                    </li>
                                @endcan

                                @can('manage sales_employee_target')
                                    <li class="nav-item">
                                        <a href="{{ route('sales-employee-targets.index', 'all_months') }}"
                                            class="nav-link {{ $isTargetReportRoute ? 'active' : '' }}">Targets Report</a>
                                    </li>
                                @endcan


                                <li class="nav-item">
                                    <a class="nav-link menu-link {{ $isLeadData ? 'active' : '' }}"
                                        href="#lead-setting-new" data-bs-toggle="collapse" role="button"
                                        aria-expanded="false" aria-controls="lead-setting-new">
                                        <span>Leads</span>
                                    </a>
                                    <div class="collapse menu-dropdown {{ $isLeadData ? 'show' : '' }}"
                                        id="lead-setting-new">
                                        <ul class="nav nav-sm flex-column">

                                            @can('manage lead')
                                                <li class="nav-item">
                                                    <a href="{{ route('leads.list') }}"
                                                        class="nav-link {{ $isLeadRoute ? 'active' : '' }}">All Leads</a>
                                                </li>
                                            @endcan

                                            @can('manage lead')
                                                <li class="nav-item">
                                                    <a href="{{ route('leads.new_lead_list') }}"
                                                        class="nav-link {{ $isNewLeadRoute ? 'active' : '' }}">New
                                                        Leads</a>
                                                </li>
                                            @endcan

                                            @can('manage lead')
                                                <li class="nav-item">
                                                    <a href="{{ route('leads.new_assign_lead') }}"
                                                        class="nav-link {{ $isNewAssignRoute ? 'active' : '' }}">New
                                                        Assign</a>
                                                </li>
                                            @endcan

                                        </ul>
                                    </div>
                                </li>

                                <!-- ------------- Lead followup --------------- -->
                                @can('manage follow-up')
                                <li class="nav-item">
                                    <a class="nav-link menu-link {{ $isFollowUpManagementRoute ? 'active' : '' }}"
                                        href="#lead-setting-new" data-bs-toggle="collapse" role="button"
                                        aria-expanded="{{ $isFollowUpManagementRoute ? 'true' : 'false' }}"
                                        aria-controls="lead-setting-new">
                                        <span>Follow Up</span>
                                    </a>
                                    <div class="collapse menu-dropdown {{ $isFollowUpManagementRoute ? 'show' : '' }}"
                                        id="lead-setting-new">
                                        <ul class="nav nav-sm flex-column">

                                            <li class="nav-item">
                                                <a href="{{ route('follow-ups.follow_up_lead', 'upcomming') }}"
                                                    class="nav-link {{ $currentSlug === 'upcomming' ? 'active' : '' }}">
                                                    Upcoming Follow-up
                                                </a>
                                            </li>

                                            <li class="nav-item">
                                                <a href="{{ route('follow-ups.follow_up_lead', 'expired') }}"
                                                    class="nav-link {{ $currentSlug === 'expired' ? 'active' : '' }}">
                                                    Expired Follow-up
                                                </a>
                                            </li>

                                            <li class="nav-item">
                                                <a href="{{ route('follow-ups.follow_up_lead', 'notinterested') }}"
                                                    class="nav-link {{ $currentSlug === 'notinterested' ? 'active' : '' }}">
                                                    Not Interested
                                                </a>
                                            </li>

                                            <li class="nav-item">
                                                <a href="{{ route('follow-ups.follow_up_lead', 'all') }}"
                                                    class="nav-link {{ $currentSlug === 'all' ? 'active' : '' }}">
                                                    Follow-up Report
                                                </a>
                                            </li>

                                        </ul>
                                    </div>
                                </li>
                                @endcan
                                <!-- ------------- Lead followup --------------- -->


                            </ul>
                        </div>
                    </li>
                @endif


                @can('manage order')
                    <li class="nav-item">
                        <a href="{{ route('orders.index') }}"
                            class="nav-link {{ $isOrderRoute ? 'active' : '' }} menu-link">
                            <i class="ri-stack-line"></i> <span>Order</span>
                        </a>
                    </li>
                @endcan

                @can('manage account')
                    <li class="nav-item">
                        <a href="javascript:void(0);" class="nav-link menu-link">
                            <i class="ri-stack-line"></i> <span>Account</span>
                        </a>
                    </li>
                @endcan

                @php
                    $isAdvertisementRoute = request()->routeIs('advertisements.*');
                @endphp

                @can('manage advertisement')
                    <li class="nav-item">
                        <a href="{{ route('advertisements.index') }}"
                            class="nav-link {{ $isAdvertisementRoute ? 'active' : '' }} menu-link">
                            <i class="ri-stack-line"></i> <span>Advertisement</span>
                        </a>
                    </li>
                @endcan

                @can('manage company settings')
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#setting" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="setting">
                            <i class="ri-apps-2-line"></i> <span>Setting</span>
                        </a>
                        <div class="collapse menu-dropdown" id="setting">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('setting.lead.index') }}" class="nav-link">Lead</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('setting.taxes') }}" class="nav-link">GST Management</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('setting.order.index') }}" class="nav-link">Order</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('setting.invoice.view') }}" class="nav-link">Invoice View</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('setting.whatsapp-bot.index') }}" class="nav-link">WhatsApp AI Bot</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endcan

            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div> --}}


@php
    $isCustomerRoute = request()->routeIs('customers.*');
    $isVendorRoute = request()->routeIs('vendors.*');
    $isTransportRoute = request()->routeIs('transports.*');
    $isEntityRoute = $isCustomerRoute || $isVendorRoute || $isTransportRoute;

    $isDashboardRoute = request()->routeIs('dashboard');

    $isUserRoute = request()->routeIs('users.*');
    $isRoleRoute = request()->routeIs('roles.*');
    $isUserMangRoute = $isUserRoute || $isRoleRoute;

    $isEmployeeRoute = request()->routeIs('employees.*');
    $isDepartmentRoute = request()->routeIs('departments.*');
    $isDesignationRoute = request()->routeIs('designations.*');
    $isHolidayRoute = request()->routeIs('holidays.*');
    $isLeaveRoute = request()->routeIs('leaves.*');
    $isAttendanceRoute = request()->routeIs('attendances.*');
    $isAttendanceReportRoute = request()->routeIs('attendances.report');
    $isSalesTargetRoute = request()->routeIs('sales-targets.*');
    $isTargetReportRoute = request()->routeIs('sales-employee-targets.*');
    $isPayRollRoute = request()->routeIs('payrolls.*');
    $isWorkingHoursRoute = request()->routeIs('working-hours.*');
    $isLeaveRuleRoute = request()->routeIs('leave-rules.*');
    $isLeaveTypeRuleRoute = request()->routeIs('leave-types.*');
    $isSalesEmplTargetRoute = request()->routeIs('sales-employee-targets.*');
    $isHrRoute =
        $isEmployeeRoute ||
        $isDepartmentRoute ||
        $isDesignationRoute ||
        $isHolidayRoute ||
        $isLeaveRoute ||
        $isAttendanceRoute ||
        $isSalesTargetRoute ||
        $isPayRollRoute ||
        $isWorkingHoursRoute ||
        $isLeaveRuleRoute ||
        $isLeaveTypeRuleRoute ||
        $isSalesEmplTargetRoute ||
        $isTargetReportRoute;

    $isOrderRoute = request()->routeIs('orders.*');
    $isInvoiceRoute = request()->routeIs('invoices.*');

    $isLeadRoute = request()->routeIs('leads.*');
    // $isNewLeadRoute = request()->routeIs('leads.new_lead_list');
    // $isNewAssignRoute = request()->routeIs('leads.new_assign_lead');

    $isQuoteRoute = request()->routeIs('quotes.*');
    $isPaymentRoute = request()->routeIs('payments.*');
    $isAccountsRoute = request()->routeIs('accounts.*');
    $isAccountsCustomerRoute = request()->routeIs('accounts.customers');
    $isAccountsLedgerRoute = request()->routeIs('accounts.customers.ledger');
    $isSpankoRoute = request()->routeIs('spanko.*');

    //follow-up
    $currentSlug = request()->route('slug');
    $isFollowUpRoute = request()->routeIs('follow-ups.*');
    $isFollowUpManagementRoute = $isFollowUpRoute;

    $isSalesRoute = $isLeadRoute || $isQuoteRoute || $isPaymentRoute || $isSpankoRoute || $isFollowUpManagementRoute;

    $isLeadData = $isLeadRoute;

    $isDeviceRoute = request()->routeIs('device.*');
    $isBulkMessageRoute = request()->routeIs('bulk-message.*');
    $isProductRoute = request()->routeIs('products.*');
    $isCategoryRoute = request()->routeIs('category.*');

    $website_nm = \App\Models\Utility::getWebsiteName();
    $website_short_nm = \App\Models\Utility::getWebsiteShortName();

    // Regions
    $isRegionsRoute = request()->routeIs('regions.*');
    $isRegionsCountryRoute = request()->routeIs('regions.countries.*');
    $isRegionsStatesRoute = request()->routeIs('regions.states.*');
    $isRegionsCitiesRoute = request()->routeIs('regions.cities.*');

    $uiCanSales = true;
    $uiCanHr = true;
    $uiCanAccounts = true;
    $uiCanWhatsapp = true;
    $uiCanBulkMessage = false;

    if (config('tenancy.enabled', false) && app()->bound('currentTenant') && \Auth::check() && \Auth::user()->type !== 'super admin') {
        $uiCanBulkMessage = true;
        $tenantId = (int) app('currentTenant')->id;
        $subscription = \App\Models\Subscription::query()
            ->with('plan')
            ->where('tenant_id', $tenantId)
            ->latest('id')
            ->first();

        $modules = collect($subscription?->plan?->modules ?? [])
            ->map(fn($m) => strtolower(trim((string) $m)))
            ->filter()
            ->values();

        $allowAllWhenEmpty = (bool) config('tenancy.allow_all_when_plan_modules_empty', true);

        if ($modules->isEmpty()) {
            if (!$allowAllWhenEmpty) {
                $uiCanSales = false;
                $uiCanHr = false;
                $uiCanAccounts = false;
                $uiCanWhatsapp = false;
                $uiCanBulkMessage = false;
            }
        } elseif (!$modules->contains('*')) {
            $uiCanSales = $modules->contains('sales');
            $uiCanHr = $modules->contains('hr');
            $uiCanAccounts = $modules->contains('accounts');
            $uiCanWhatsapp = $modules->contains('whatsapp');
            $uiCanBulkMessage = $modules->contains('bulk_message');
        }
    }

@endphp

<style>
    .sidebar-modern {
        --sb-shell-bg: linear-gradient(180deg, rgba(255, 255, 255, 0.985) 0%, rgba(249, 250, 252, 0.98) 100%);
        --sb-shell-shadow: 0 18px 34px rgba(15, 23, 42, 0.06);
        --sb-bg: transparent;
        --sb-panel: transparent;
        --sb-text: #1f2937;
        --sb-muted: #8a94a7;
        --sb-subtext: #566173;
        --sb-border: rgba(226, 232, 240, 0.9);
        --sb-active-bg: #f1f3f8;
        --sb-active-text: #111827;
    }

    .sidebar-modern.navbar-menu {
        background: transparent !important;
        border-right: 0;
        box-shadow: none;
        backdrop-filter: none;
        position: fixed;
        top: 0;
        bottom: 0;
        z-index: 1002;
        width: var(--modern-sidebar-width);
        padding: 12px 10px 14px 14px;
        overflow: hidden;
    }

    .sidebar-modern.navbar-menu::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 20px;
        background: var(--sb-shell-bg);
        border: 1px solid rgba(229, 231, 235, 0.95);
        box-shadow: var(--sb-shell-shadow);
        backdrop-filter: blur(14px);
    }

    .sidebar-modern > * {
        position: relative;
        z-index: 1;
    }

    .sidebar-modern .navbar-brand-box {
        background: transparent;
        border-bottom: 0;
        height: auto;
        padding: 0.9rem 0.85rem 0.45rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        position: relative;
        overflow: hidden;
    }

    .sidebar-modern #scrollbar .container-fluid {
        padding: 0.35rem 0.55rem 1rem;
    }

    .sidebar-modern .sidebar-brand-card {
        display: flex !important;
        align-items: center;
        gap: 0.7rem;
        width: 100%;
        min-width: 0;
        margin-right: 0;
        padding: 0.35rem 2.25rem 0.35rem 0.25rem !important;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .sidebar-modern .sidebar-brand-card .logo-sm,
    .sidebar-modern .sidebar-brand-card .logo-lg {
        display: contents;
    }

    .sidebar-modern .brand-logo-wrap {
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #111827;
        border: 0;
    }

    .sidebar-modern .brand-logo-wrap img {
        width: 18px;
        height: 18px;
        object-fit: contain;
    }

    .sidebar-modern .brand-copy {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 0.06rem;
        flex: 1 1 auto;
    }

    .sidebar-modern .brand-eyebrow {
        display: none;
    }

    .sidebar-modern .brand-title {
        color: var(--sb-text);
        font-family: 'Manrope', sans-serif;
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-modern .brand-chevron {
        width: 22px;
        height: 22px;
        flex: 0 0 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        color: #6b7280;
        background: transparent;
        border: 0;
        font-size: 0.95rem;
    }

    .sidebar-modern .navbar-nav .menu-title {
        margin: 0.95rem 0.15rem 0.35rem;
        padding: 0.15rem 0.5rem;
        background: transparent;
        border: 0;
        border-radius: 0;
        color: var(--sb-muted);
        letter-spacing: 0;
        text-transform: none;
        font-size: 0.78rem;
        font-weight: 500;
    }

    .sidebar-modern .navbar-nav .nav-item {
        margin-bottom: 0.08rem;
    }

    .sidebar-modern .navbar-nav .nav-link {
        display: flex;
        align-items: center;
        gap: 0.58rem;
        min-height: 38px;
        position: relative;
        border-radius: 10px;
        color: var(--sb-text) !important;
        padding: 0.48rem 0.7rem 0.48rem 0.65rem;
        transition: all 0.18s ease-in-out;
        font-weight: 500;
        font-size: 0.96rem;
        line-height: 1.4;
        border: 0;
        white-space: normal;
        text-align: left;
    }

    .sidebar-modern .navbar-nav .nav-link:hover,
    .sidebar-modern .navbar-nav .nav-link:focus-visible {
        background: #f7f8fb !important;
        color: var(--sb-active-text) !important;
        transform: none;
    }

    .sidebar-modern .navbar-nav .nav-link:hover i,
    .sidebar-modern .navbar-nav .nav-link:focus-visible i {
        color: #4b5563;
    }

    .sidebar-modern .navbar-nav .menu-link > i {
        width: 18px;
        height: 18px;
        flex: 0 0 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: 0;
        border-radius: 0;
        margin-right: 0;
        font-size: 1rem;
        color: #6b7280;
        transition: all 0.18s ease-in-out;
    }

    .sidebar-modern .navbar-nav .menu-link > span,
    .sidebar-modern .navbar-nav .nav-link > span {
        flex: 1 1 auto;
        min-width: 0;
    }

    .sidebar-modern .navbar-nav .nav-link.active,
    .sidebar-modern .navbar-nav .menu-link.active {
        background: var(--sb-active-bg);
        color: var(--sb-active-text) !important;
        box-shadow: none;
        font-weight: 600;
    }

    .sidebar-modern .navbar-nav .nav-link.active i,
    .sidebar-modern .navbar-nav .menu-link.active i {
        color: #4b5563;
    }

    .sidebar-modern .navbar-nav .menu-link[data-bs-toggle="collapse"]::after {
        color: #9ca3af;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.88rem;
    }

    .sidebar-modern .navbar-nav .menu-link[data-bs-toggle="collapse"] {
        padding-right: 1.8rem;
    }

    .sidebar-modern .navbar-nav .menu-link[data-bs-toggle="collapse"][aria-expanded="true"]::after {
        transform: translateY(-50%) rotate(90deg);
    }

    .sidebar-modern .navbar-nav > .nav-item > .menu-link:not([data-bs-toggle="collapse"])::after {
        display: none !important;
        content: none !important;
    }

    .sidebar-modern .menu-dropdown {
        margin: 0.12rem 0 0.45rem;
        padding: 0.1rem 0 0.05rem 0.45rem;
        border-left: 1px solid #e5e7eb;
        margin-left: 0.95rem;
        background: transparent;
        border-radius: 0;
    }

    .sidebar-modern .menu-dropdown > .nav.nav-sm {
        padding-left: 0.2rem;
    }

    .sidebar-modern .menu-dropdown .nav-link {
        min-height: 31px;
        padding: 0.34rem 0.58rem;
        border-radius: 8px;
        border: 0;
        font-size: 0.92rem;
        font-weight: 400;
        line-height: 1.35;
        color: var(--sb-subtext) !important;
        gap: 0;
        background: transparent;
    }

    .sidebar-modern .menu-dropdown .nav-link.active {
        color: var(--sb-active-text) !important;
        background: #f1f3f8;
        font-weight: 600;
    }

    .sidebar-modern .menu-dropdown .nav-link:hover {
        background: #f7f8fb;
        color: var(--sb-active-text) !important;
    }

    .sidebar-modern .menu-dropdown .nav-link::before {
        display: none;
    }

    :is([data-layout="vertical"], [data-layout="semibox"])[data-sidebar-size="sm-hover"] .sidebar-modern:hover {
        background: linear-gradient(180deg, #eaf2fb 0%, #dde8f4 54%, #d4e1ef 100%) !important;
        box-shadow: 18px 0 36px rgba(15, 23, 42, 0.16);
    }

    :is([data-layout="vertical"], [data-layout="semibox"])[data-sidebar-size="sm-hover"] .sidebar-modern:hover .navbar-nav .menu-link > i,
    :is([data-layout="vertical"], [data-layout="semibox"])[data-sidebar-size="sm-hover"] .sidebar-modern:hover .navbar-nav .nav-link > i {
        flex: 0 0 26px;
        margin-right: 0;
    }

    :is([data-layout="vertical"], [data-layout="semibox"])[data-sidebar-size="sm-hover"] .sidebar-modern:hover .menu-dropdown {
        background: transparent;
        border-left-color: rgba(148, 163, 184, 0.25);
        box-shadow: none;
        position: relative;
        z-index: 2;
    }

    .sidebar-modern .sidebar-background {
        opacity: 0;
    }

    .sidebar-modern .btn-vertical-sm-hover {
        color: #6b7280 !important;
        width: 24px;
        height: 24px;
        border-radius: 999px !important;
        border: 0 !important;
        background: transparent !important;
        position: absolute;
        top: 0.95rem;
        right: 0.75rem;
        z-index: 2;
        padding: 0 !important;
        min-height: 0;
    }

    @media (max-width: 767.98px) {
        .sidebar-modern.navbar-menu {
            padding: 8px;
            width: min(86vw, var(--modern-sidebar-width));
        }

        .sidebar-modern.navbar-menu::before {
            border-radius: 16px;
        }

        .sidebar-modern .navbar-brand-box {
            padding: 0.8rem 0.75rem 0.45rem;
        }

        .sidebar-modern .navbar-nav .nav-link {
            font-size: 0.88rem;
        }
    }
</style>


<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu sidebar-modern">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <a href="{{ route('dashboard') }}" class="logo sidebar-brand-card">
            <span class="logo-sm">
                <span class="brand-logo-wrap">
                    <img src="{{ $brandLogo }}" alt="{{ $website_nm ?? 'Workspace' }}">
                </span>
            </span>
            <span class="logo-lg">
                <span class="brand-copy">
                    <span class="brand-eyebrow">{{ $website_short_nm ?: 'Workspace' }}</span>
                    <span class="brand-title">{{ $website_nm ?? 'Operations Workspace' }}</span>
                </span>
                <span class="brand-chevron">
                    <i class="ri-arrow-down-s-line"></i>
                </span>
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>



            <ul class="navbar-nav" id="navbar-nav">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}"
                        class="nav-link menu-link {{ $isDashboardRoute ? 'active' : '' }}">
                        <i class="ri-dashboard-2-line"></i> <span>Dashboard</span>
                    </a>
                </li>



                @if (
                    $uiCanSales &&
                    (\Auth::user()->can('manage customer') ||
                        \Auth::user()->can('manage vender') ||
                        \Auth::user()->can('manage transport')))
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $isEntityRoute ? 'active' : '' }}" href="#entity-setion"
                            data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $isEntityRoute ? 'true' : 'false' }}" aria-controls="entity-setion">
                            <i class="ri-apps-2-line"></i> <span>Entity</span>
                        </a>
                        <div class="collapse menu-dropdown {{ $isEntityRoute ? 'show' : '' }}" id="entity-setion">
                            <ul class="nav nav-sm flex-column">
                                @can('manage customer')
                                    <li class="nav-item">
                                        <a href="{{ route('customers.index') }}"
                                            class="nav-link {{ $isCustomerRoute ? 'active' : '' }}">Customer</a>
                                    </li>
                                @endcan
                                {{-- @can('manage vender')
                                    <li class="nav-item">
                                        <a href="{{ route('vendors.index') }}"
                                            class="nav-link {{ $isVendorRoute ? 'active' : '' }}">Vendor</a>
                                    </li>
                                @endcan --}}
                                @can('manage transport')
                                    <li class="nav-item">
                                        <a href="{{ route('transports.index') }}"
                                            class="nav-link {{ $isTransportRoute ? 'active' : '' }}">Transport</a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endif

                @if ($uiCanSales && \Auth::user()->can('manage category'))
                    <li class="nav-item">
                        <a href="{{ route('category.index') }}"
                            class="nav-link {{ $isCategoryRoute ? 'active' : '' }} menu-link">
                            <i class="ri-folders-line"></i> <span>Categories</span>
                        </a>
                    </li>
                @endif

                @php
                    $isPartialInventoryRoute = request()->routeIs('products.partial_inventory');
                    $isInventoryActivityRoute = request()->routeIs('products.inventory_activity');
                    $isProductsMenuOpen = $isProductRoute || $isPartialInventoryRoute || $isInventoryActivityRoute;
                @endphp

                @if ($uiCanSales && \Auth::user()->can('manage product & service'))
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $isProductsMenuOpen ? 'active' : '' }}" href="#products-menu"
                            data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $isProductsMenuOpen ? 'true' : 'false' }}" aria-controls="products-menu">
                            <i class="ri-box-3-line"></i> <span>Products</span>
                        </a>
                        <div class="collapse menu-dropdown {{ $isProductsMenuOpen ? 'show' : '' }}" id="products-menu">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('products.index') }}"
                                        class="nav-link {{ $isProductRoute && !$isPartialInventoryRoute ? 'active' : '' }}">
                                        Product List
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('products.partial_inventory') }}"
                                        class="nav-link {{ $isPartialInventoryRoute ? 'active' : '' }}">
                                        Partial Inventory
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('products.inventory_activity') }}"
                                        class="nav-link {{ $isInventoryActivityRoute ? 'active' : '' }}">
                                        Inventory Activity
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                @if($uiCanSales)
                @can('manage quote')
                    <li class="nav-item">
                        <a href="{{ route('quotes.index') }}" class="nav-link menu-link {{ $isQuoteRoute ? 'active' : '' }}"><i
                                class="ri-draft-line"></i><span>Quotations</span>
                        </a>
                    </li>
                @endcan
                @endif

                @if($uiCanSales)
                @can('manage invoice')
                    <li class="nav-item">
                        <a href="{{ route('invoices.index') }}"
                            class="nav-link {{ $isInvoiceRoute ? 'active' : '' }} menu-link">
                            <i class="ri-bill-line"></i> <span>Invoices</span>
                        </a>
                    </li>
                @endcan
                @endif

                @if($uiCanSales)
                @can('manage order')
                    <li class="nav-item">
                        <a href="{{ route('orders.index') }}"
                            class="nav-link {{ $isOrderRoute ? 'active' : '' }} menu-link">
                            <i class="ri-file-list-3-line"></i> <span>Orders</span>
                        </a>
                    </li>
                @endcan
                @endif

                {{-- <li class="nav-item">
                        <a href="{{ route('facebooks.create') }}"
                            class="nav-link {{ $isOrderRoute ? 'active' : '' }} menu-link">
                            <i class="ri-file-list-3-line"></i> <span>Facebook</span>
                        </a>
                </li> --}}


                @if(($uiCanSales || $uiCanWhatsapp))
                @can('manage device')
                    <li class="nav-item">
                        <a href="{{ route('device.index') }}"
                            class="nav-link {{ $isDeviceRoute ? 'active' : '' }} menu-link">
                            <i class="ri-smartphone-line"></i> <span>Devices & WhatsApp</span>
                        </a>
                    </li>
                @endcan
                @endif

                @if($uiCanBulkMessage)
                @can('manage bulk message')
                    <li class="nav-item">
                        <a href="{{ route('bulk-message.index') }}"
                            class="nav-link {{ $isBulkMessageRoute ? 'active' : '' }} menu-link">
                            <i class="ri-megaphone-line"></i> <span>Bulk Message</span>
                        </a>
                    </li>
                @endcan
                @endif

                @if($uiCanSales)
                <li class="menu-title cm-light-bg"><span>CRM & Sales</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link {{ $isLeadData ? 'active' : '' }}" href="#lead-setting-new-1"
                        data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="lead-setting-new">
                        <i class="ri-team-line"></i><span>Lead Pipeline</span>
                    </a>
                    <div class="collapse menu-dropdown {{ $isLeadData ? 'show' : '' }}" id="lead-setting-new-1">
                        <ul class="nav nav-sm flex-column">

                            @php
                                $leadSlug = request()->route('slug') ?? 'all_leads';
                            @endphp

                            @can('manage lead')
                                <li class="nav-item">
                                    <a href="{{ route('leads.list', 'all_leads') }}"
                                        class="nav-link {{ $leadSlug == 'all_leads' ? 'active' : '' }}">
                                        All Leads
                                    </a>
                                </li>
                            @endcan

                            @can('manage lead')
                                <li class="nav-item">
                                    <a href="{{ route('leads.list', 'new_leads') }}"
                                        class="nav-link {{ $leadSlug == 'new_leads' ? 'active' : '' }}">
                                        New Leads
                                    </a>
                                </li>
                            @endcan

                            @can('manage lead')
                                <li class="nav-item">
                                    <a href="{{ route('leads.list', 'new_assigned_leads') }}"
                                        class="nav-link {{ $leadSlug == 'new_assigned_leads' ? 'active' : '' }}">
                                        New Assignments
                                    </a>
                                </li>
                            @endcan

                        </ul>
                    </div>
                </li>

                @can('manage follow-up')
                    <li class="nav-item">
                        <a href="{{ route('follow-ups.list') }}"
                            class="nav-link menu-link {{ $isFollowUpManagementRoute ? 'active' : '' }}"><i
                                class="ri-calendar-check-line"></i><span>Follow-Ups</span></a>
                    </li>
                @endcan


                @can('manage payment')
                    <li class="nav-item">
                        <a href="{{ route('payments.index') }}"
                            class="nav-link menu-link {{ $isPaymentRoute ? 'active' : '' }}"><i
                                class="ri-wallet-3-line"></i><span>Payments</span></a>
                    </li>
                @endcan

                @can('manage spanko')
                    {{-- <li class="nav-item">
                        <a href="{{ route('spanko.index') }}" class="nav-link {{ $isSpankoRoute ? 'active' : '' }}"><i
                                class="ri-stack-line"></i><span>Spanko</span></a>
                    </li> --}}
                @endcan
                @endif

                @if (\Auth::user()->can('manage user') || \Auth::user()->can('manage company settings') || \Auth::user()->type == 'company')
                    <li class="menu-title cm-light-bg"><span>Administration</span></li>
                @endif
                @can('manage user')
                    <li class="nav-item {{ $isUserMangRoute ? 'active' : '' }}">
                        <a class="nav-link menu-link" href="#user-management-setion" data-bs-toggle="collapse"
                            role="button" aria-expanded="false" aria-controls="user-management-setion">
                            <i class="ri-shield-user-line"></i> <span>{{ __('Users & Access') }}</span>
                        </a>
                        <div class="collapse menu-dropdown {{ $isUserMangRoute ? 'show' : '' }}"
                            id="user-management-setion">
                            <ul class="nav nav-sm flex-column">
                                @can('manage role')
                                    <li class="nav-item">
                                        <a href="{{ route('roles.index') }}"
                                            class="nav-link {{ $isRoleRoute ? 'active' : '' }}">{{ __('Roles & Permissions') }}</a>
                                    </li>
                                @endcan

                                <li class="nav-item">
                                    <a href="{{ route('users') }}"
                                        class="nav-link {{ $isUserRoute ? 'active' : '' }}">{{ __('Users') }}</a>
                                </li>

                            </ul>
                        </div>
                    </li>
                @endcan
                @php
                    $isBankDetailRoute = request()->routeIs('bank-account-details.*');
                    $isAccountsMenuOpen = $isAccountsRoute || $isBankDetailRoute || $isAccountsCustomerRoute || $isAccountsLedgerRoute;
                @endphp

                @if($uiCanAccounts)
                    <li class="menu-title cm-light-bg"><span>Finance</span></li>
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $isAccountsMenuOpen ? 'active' : '' }}"
                            href="#accounts-menu" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $isAccountsMenuOpen ? 'true' : 'false' }}"
                            aria-controls="accounts-menu">
                            <i class="ri-bank-card-line"></i> <span>Accounts</span>
                        </a>
                        <div class="collapse menu-dropdown {{ $isAccountsMenuOpen ? 'show' : '' }}" id="accounts-menu">
                            <ul class="nav nav-sm flex-column">
                                @if(\Auth::user()->can('manage payment') || \Auth::user()->can('manage report') || \Auth::user()->can('manage finance report') || \Auth::user()->can('manage bank detail'))
                                    <li class="nav-item">
                                        <a href="{{ route('accounts.index') }}"
                                            class="nav-link {{ $isAccountsRoute && !$isAccountsCustomerRoute && !$isAccountsLedgerRoute ? 'active' : '' }}">
                                            Accounts Dashboard
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('accounts.customers') }}"
                                            class="nav-link {{ $isAccountsCustomerRoute || $isAccountsLedgerRoute ? 'active' : '' }}">
                                            Customer Ledger
                                        </a>
                                    </li>
                                @endif
                                @can('manage bank detail')
                                    <li class="nav-item">
                                        <a href="{{ route('bank-account-details.index') }}"
                                            class="nav-link {{ $isBankDetailRoute ? 'active' : '' }}">
                                            Bank Account
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endif

                @php
                    $isAdvertisementRoute = request()->routeIs('advertisements.*');
                @endphp

                {{-- @can('manage advertisement')
                    <li class="nav-item">
                        <a href="{{ route('advertisements.index') }}"
                            class="nav-link {{ $isAdvertisementRoute ? 'active' : '' }} menu-link">
                            <i class="ri-advertisement-line"></i> <span>Advertisement</span>
                        </a>
                    </li>
                @endcan --}}

                @if (\Auth::check() && \Auth::user()->type === 'super admin')
                    <li class="menu-title cm-light-bg"><span>Platform Control</span></li>
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('setting.invoice-templates.*') || request()->routeIs('setting.razorpay.*') ? 'active' : '' }}"
                            href="#saas-admin-menu" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('setting.invoice-templates.*') || request()->routeIs('setting.razorpay.*') ? 'true' : 'false' }}"
                            aria-controls="saas-admin-menu">
                            <i class="ri-shield-star-line"></i> <span>Admin Settings</span>
                        </a>
                        <div class="collapse menu-dropdown {{ request()->routeIs('setting.invoice-templates.*') || request()->routeIs('setting.razorpay.*') ? 'show' : '' }}"
                            id="saas-admin-menu">
                            <ul class="nav nav-sm flex-column">
                                {{-- <li class="nav-item">
                                    <a href="{{ route('setting.invoice-templates.index') }}"
                                        class="nav-link {{ request()->routeIs('setting.invoice-templates.*') ? 'active' : '' }}">
                                        Invoice Templates
                                    </a>
                                </li> --}}
                                <li class="nav-item">
                                    <a href="{{ route('setting.razorpay.index') }}"
                                        class="nav-link {{ request()->routeIs('setting.razorpay.*') ? 'active' : '' }}">
                                        Razorpay Settings
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('setting.razorpay.transactions') }}"
                                        class="nav-link {{ request()->routeIs('setting.razorpay.transactions') ? 'active' : '' }}">
                                        Razorpay Transactions
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                @can('manage company settings')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('setting.*') || request()->routeIs('activity-logs.*') ? 'active' : '' }}"
                            href="#setting" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('setting.*') || request()->routeIs('activity-logs.*') ? 'true' : 'false' }}"
                            aria-controls="setting">
                            <i class="ri-list-settings-line"></i> <span>Workspace Settings</span>
                        </a>

                        <div class="collapse menu-dropdown {{ request()->routeIs('setting.*') || request()->routeIs('activity-logs.*') ? 'show' : '' }}"
                            id="setting">
                            <ul class="nav nav-sm flex-column">
                                @if(\Auth::check() && \Auth::user()->type !== 'super admin')
                                    <li class="nav-item">
                                        <a href="{{ route('settings.edit', \Auth::id()) }}"
                                            class="nav-link {{ request()->routeIs('settings.edit') ? 'active' : '' }}">
                                            Company Profile
                                        </a>
                                    </li>
                                @endif
                                <li class="nav-item">
                                    <a href="{{ route('setting.lead.index') }}"
                                        class="nav-link {{ request()->routeIs('setting.lead.index') ? 'active' : '' }}">
                                        Lead Settings
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('setting.taxes') }}"
                                        class="nav-link {{ request()->routeIs('setting.taxes') ? 'active' : '' }}">
                                        GST & Taxes
                                    </a>
                                </li>
	                                <li class="nav-item">
	                                    <a href="{{ route('setting.order.index') }}"
	                                        class="nav-link {{ request()->routeIs('setting.order.index') ? 'active' : '' }}">
	                                        Order Settings
	                                    </a>
	                                </li>
	                                <li class="nav-item">
	                                    <a href="{{ route('setting.terms.index') }}"
	                                        class="nav-link {{ request()->routeIs('setting.terms.*') ? 'active' : '' }}">
	                                        Terms & Conditions
	                                    </a>
	                                </li>
	                                {{-- <li class="nav-item">
	                                    <a href="{{ route('setting.invoice.view') }}"
	                                        class="nav-link {{ request()->routeIs('setting.invoice.view') ? 'active' : '' }}">
                                        Invoice Template
                                    </a>
                                </li> --}}
                                @if(\Auth::check() && \Auth::user()->type !== 'super admin')
                                    {{-- <li class="nav-item">
                                        <a href="{{ route('setting.company-invoice-templates.index') }}"
                                            class="nav-link {{ request()->routeIs('setting.company-invoice-templates.*') ? 'active' : '' }}">
                                            New Invoice Templates
                                        </a>
                                    </li> --}}
                                @endif
                                {{-- @if($uiCanWhatsapp)
                                    <li class="nav-item">
                                        <a href="{{ route('setting.whatsapp-bot.index') }}"
                                            class="nav-link {{ request()->routeIs('setting.whatsapp-bot.*') ? 'active' : '' }}">
                                            WhatsApp AI Bot
                                        </a>
                                    </li>
                                @endif --}}
                                @if(\Auth::check() && \Auth::user()->type === 'company')
                                    <li class="nav-item">
                                        <a href="{{ route('activity-logs.index') }}"
                                            class="nav-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                                            Activity Logs
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endcan

                <!-- Regions SECTION -->
                @if(\Auth::user()->type == 'company')
                <li class="nav-item">
                    <a href="#regionsMenu" class="nav-link menu-link {{ $isRegionsRoute ? 'active' : '' }}"
                        data-bs-toggle="collapse" role="button"
                        aria-expanded="{{ $isRegionsRoute ? 'true' : 'false' }}" aria-controls="regionsMenu">
                        <i class="ri-map-pin-2-line"></i> <span>Regions</span>
                    </a>
                    <div class="collapse menu-dropdown {{ $isRegionsRoute ? 'show' : '' }}" id="regionsMenu">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('regions.countries.index') }}"
                                    class="nav-link {{ $isRegionsCountryRoute ? 'active' : '' }}">Countries</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('regions.states.index') }}"
                                    class="nav-link {{ $isRegionsStatesRoute ? 'active' : '' }}">States</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('regions.cities.index') }}"
                                    class="nav-link {{ $isRegionsCitiesRoute ? 'active' : '' }}">Cities</a>
                            </li>

                        </ul>
                    </div>
                </li>
                @endif

                @if((($uiCanSales || $uiCanAccounts)) && \Auth::user()->can('manage finance report'))
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                            href="#finance" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}"
                            aria-controls="finance">
                            <i class="ri-bar-chart-box-line"></i> <span>Reports & Insights</span>
                        </a>

                        <div class="collapse menu-dropdown {{ request()->routeIs('reports.*') ? 'show' : '' }}"
                            id="finance">
                            <ul class="nav nav-sm flex-column">
                                @if($uiCanAccounts)
                                    <li class="nav-item">
                                        <a href="{{ route('reports.sales_outstanding_report') }}"
                                            class="nav-link {{ request()->routeIs('reports.sales_outstanding_report') ? 'active' : '' }}">
                                            Sales Outstanding Report
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reports.income_expense_report') }}"
                                            class="nav-link {{ request()->routeIs('reports.income_expense_report') ? 'active' : '' }}">
                                            Income vs Expense
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reports.user_login_report') }}"
                                            class="nav-link {{ request()->routeIs('reports.user_login_report') ? 'active' : '' }}">
                                            Login Report
                                        </a>
                                    </li>
                                @endif
                                @if($uiCanSales)
                                    <li class="nav-item">
                                        <a href="{{ route('reports.total_sale') }}"
                                            class="nav-link {{ request()->routeIs('reports.total_sale') ? 'active' : '' }}">
                                            Total Sales
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reports.customer_sales') }}"
                                            class="nav-link {{ request()->routeIs('reports.customer_sales') ? 'active' : '' }}">
                                            Customer Sales
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reports.customer_type') }}"
                                            class="nav-link {{ request()->routeIs('reports.customer_type') ? 'active' : '' }}">
                                            New vs Returning Customers
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reports.sales_analytics') }}"
                                            class="nav-link {{ request()->routeIs('reports.sales_analytics') ? 'active' : '' }}">
                                            Sales Analytics
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reports.sales_person_performance') }}"
                                            class="nav-link {{ request()->routeIs('reports.sales_person_performance') ? 'active' : '' }}">
                                            Sales Person Performance
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reports.product_sales_analysis') }}"
                                            class="nav-link {{ request()->routeIs('reports.product_sales_analysis') ? 'active' : '' }}">
                                            Product Sales Analysis
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
