<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tenancy Toggle
    |--------------------------------------------------------------------------
    |
    | Keep disabled until tenant databases are provisioned and mapped.
    |
    */
    'enabled' => env('TENANCY_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Auto Switch Default DB Connection
    |--------------------------------------------------------------------------
    |
    | When true, resolved tenant DB becomes default connection for request.
    | Keep false in Phase 1 to avoid breaking existing central DB flows.
    |
    */
    'set_default_connection' => env('TENANCY_SET_DEFAULT_CONNECTION', false),

    /*
    |--------------------------------------------------------------------------
    | Subscription Enforcement
    |--------------------------------------------------------------------------
    |
    | Keep disabled until subscription records are assigned to tenants.
    |
    */
    'enforce_subscription' => env('TENANCY_ENFORCE_SUBSCRIPTION', false),
    'allow_without_subscription' => env('TENANCY_ALLOW_WITHOUT_SUBSCRIPTION', true),
    'enforce_usage_limits' => env('TENANCY_ENFORCE_USAGE_LIMITS', false),
    'enforce_plan_modules' => env('TENANCY_ENFORCE_PLAN_MODULES', false),
    'allow_all_when_plan_modules_empty' => env('TENANCY_ALLOW_ALL_WHEN_PLAN_MODULES_EMPTY', true),
    'enforce_tenant_session_isolation' => env('TENANCY_ENFORCE_TENANT_SESSION_ISOLATION', true),

    /*
    |--------------------------------------------------------------------------
    | Tenant Resolution Inputs
    |--------------------------------------------------------------------------
    */
    'header_tenant_id' => env('TENANCY_HEADER_TENANT_ID', 'X-Tenant-Id'),
    'header_tenant_slug' => env('TENANCY_HEADER_TENANT_SLUG', 'X-Tenant-Slug'),
    'central_hosts' => array_values(array_filter(array_unique(array_map('strtolower', array_map('trim', explode(',', (string) env('TENANCY_CENTRAL_HOSTS', 'localhost,127.0.0.1'))))))),

    /*
    |--------------------------------------------------------------------------
    | Central Route Exemptions
    |--------------------------------------------------------------------------
    |
    | Any matching path/route will skip tenancy initialization.
    | Comma-separated values can be configured via env.
    |
    */
    'exempt_paths' => array_values(array_filter(array_map('trim', explode(',', (string) env('TENANCY_EXEMPT_PATHS', 'up'))))),
    'exempt_route_names' => array_values(array_filter(array_map('trim', explode(',', (string) env('TENANCY_EXEMPT_ROUTE_NAMES', 'login,login.*,logout,password.*'))))),
    'subscription_exempt_paths' => array_values(array_filter(array_map('trim', explode(',', (string) env('TENANCY_SUBSCRIPTION_EXEMPT_PATHS', 'up,login,logout,website*,subscription/plans*,setting/tenancy*'))))),
    'subscription_exempt_route_names' => array_values(array_filter(array_map('trim', explode(',', (string) env('TENANCY_SUBSCRIPTION_EXEMPT_ROUTE_NAMES', 'login,logout,password.*,website.*,subscription.plans,setting.tenancy.*'))))),
    'module_exempt_paths' => array_values(array_filter(array_map('trim', explode(',', (string) env('TENANCY_MODULE_EXEMPT_PATHS', 'up,login,logout,setting/tenancy*,setting/plans*'))))),
    'module_exempt_route_names' => array_values(array_filter(array_map('trim', explode(',', (string) env('TENANCY_MODULE_EXEMPT_ROUTE_NAMES', 'login,logout,password.*,setting.tenancy.*,setting.plans.*'))))),
    'landlord_only_paths' => array_values(array_filter(array_map('trim', explode(',', (string) env('TENANCY_LANDLORD_ONLY_PATHS', 'setting/tenancy*,setting/plans*'))))),
    'landlord_only_route_names' => array_values(array_filter(array_map('trim', explode(',', (string) env('TENANCY_LANDLORD_ONLY_ROUTE_NAMES', 'setting.tenancy.*,setting.plans.*'))))),

    /*
    |--------------------------------------------------------------------------
    | Route-to-Module Mapping
    |--------------------------------------------------------------------------
    */
    'module_route_map' => [
        'sales' => [
            'route_names' => ['leads.*', 'quotes.*', 'orders.*', 'invoices.*', 'payments.*', 'spanko.*', 'follow-ups.*', 'chat.*', 'sent.customtext', 'device.*', 'reports.sales_*', 'reports.total_sale', 'reports.customer_*'],
            'paths' => ['leads*', 'quotes*', 'orders*', 'invoices*', 'payments*', 'spanko*', 'follow-ups*', 'device/chats*', 'send-message*', 'sent-whatsapp-custom-text*', 'reports/sales-*', 'reports/total-sales*', 'reports/customer-*'],
        ],
        'hr' => [
            'route_names' => ['employees.*', 'departments.*', 'designations.*', 'attendances.*', 'leaves.*', 'holidays.*', 'payrolls.*', 'working-hours.*', 'leave-rules.*', 'leave-types.*', 'sales-targets.*', 'sales-employee-targets.*'],
            'paths' => ['employees*', 'departments*', 'designations*', 'attendances*', 'leaves*', 'holidays*', 'payrolls*', 'working-hours*', 'leave-rules*', 'leave-types*', 'sales-targets*', 'sales-employee-targets*'],
        ],
        'accounts' => [
            'route_names' => ['bank-account-details.*', 'reports.income_expense_report', 'reports.sales_outstanding_report', 'reports.user_login_report'],
            'paths' => ['bank-account-details*', 'reports/income-expense-report*', 'reports/sales-outstanding-report*', 'reports/user-login-report*'],
        ],
        'whatsapp' => [
            'route_names' => ['setting.whatsapp-bot.*', 'chat.*', 'sent.customtext', 'device.*'],
            'paths' => ['setting/whatsapp-bot*', 'device/chats*', 'send-message*', 'sent-whatsapp-custom-text*', 'create-session*', 'check-session*', 'logout-session*'],
        ],
        'bulk_message' => [
            'route_names' => ['bulk-message.*'],
            'paths' => ['bulk-message*'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Health Check Tables
    |--------------------------------------------------------------------------
    */
    'health_required_tables' => [
        'users',
        'customer_price_histories',
        'entity_addresses',
        'transports',
        'settings',
        'countries',
        'states',
        'cities',
        'lead_stages',
        'lead_sources',
        'lead_types',
        'products',
        'departments',
        'designations',
        'employees',
        'holidays',
        'leaves',
        'attendances',
        'sales_targets',
        'working_hours',
        'leave_rules',
        'leave_types',
        'employee_salary_details',
        'employee_sales_targets',
        'employee_payment_histories',
        'user_logins',
        'payments',
        'bank_details',
        'location_histories',
        'entities',
        'addresses',
        'customer_phones',
        'order_stages',
        'units',
        'unit_types',
        'categories',
        'gst_slab_masters',
        'leads',
        'user_leads',
        'lead_products',
        'lead_chats',
        'lead_calls',
        'lead_activities',
        'activity_logs',
        'quotes',
        'quote_products',
        'orders',
        'order_products',
        'order_payments',
        'order_activities',
        'jobs',
        'job_batches',
        'password_reset_tokens',
        'personal_access_tokens',
        'sessions',
        'devices',
        'whatsapp_bot_rules',
        'whatsapp_bot_knowledge',
        'third_parties',
        'vendor_products',
        'taxes',
    ],
];
