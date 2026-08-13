<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeadCall;
use App\Models\User;
use App\Models\Leave;
use App\Models\Lead;
use App\Models\LeadChat;
use App\Models\LeadSource;
use App\Models\Quotes;
use App\Models\Order;
use App\Models\LeadStage;
use App\Models\WorkingHours;
use App\Models\LocationHistory;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\TenantUsage;
use App\Models\Category;
use App\Support\Tenancy\TenancyManager;
use Illuminate\Support\Facades\Schema;
use League\CommonMark\Extension\SmartPunct\Quote;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    private function buildCategoryFilterOptions($groupedCategories, $parentId = null, string $prefix = ''): array
    {
        $result = [];

        $children = $groupedCategories[$parentId] ?? collect();

        foreach ($children as $category) {
            $result[$category->id] = $prefix . $category->name;

            if (($groupedCategories[$category->id] ?? collect())->count() > 0) {
                $result += $this->buildCategoryFilterOptions($groupedCategories, $category->id, $prefix . '- ');
            }
        }

        return $result;
    }

    private function normalizeDashboardCategoryFilter(Request $request): array
    {
        $raw = $request->query('inventory_category_ids', $request->query('inventory_category_id', []));
        $raw = is_array($raw) ? $raw : [$raw];

        return collect($raw)
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $tenantConnection = $this->tenantConnectionName();
        $data['attendance_id'] =0;
        $data['current_year'] = date('Y');
        $data['current_month'] = date('F');
        $data['current_date'] = date('d-m-Y');

        $dashboard = '';

        switch (\Auth::user()->type) {
            case 'super admin':
                $today = Carbon::today();
                $monthStart = Carbon::now()->startOfMonth();
                $monthEnd = Carbon::now()->endOfMonth();
                $last6Months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i))->push(Carbon::now());

                $data['saas_kpis'] = [
                    'tenants_total' => Tenant::query()->count(),
                    'tenants_active' => Tenant::query()->where('is_active', true)->count(),
                    'plans_total' => Plan::query()->count(),
                    'subs_active' => Subscription::query()->whereIn('status', ['active', 'trialing'])->count(),
                ];

                $requiredTables = (array) config('tenancy.health_required_tables', []);
                $healthSummary = [
                    'db_ok' => 0,
                    'schema_ok' => 0,
                    'subscription_ok' => 0,
                    'whatsapp_limit_breached' => 0,
                ];
                $tenantsForHealth = Tenant::query()->get();
                foreach ($tenantsForHealth as $tenant) {
                    $dbOk = false;
                    $schemaOk = false;
                    try {
                        app(TenancyManager::class)->initialize($tenant);
                        DB::connection()->select('SELECT 1');
                        $dbOk = true;
                        $missingTables = [];
                        foreach ($requiredTables as $table) {
                            if (!Schema::hasTable($table)) {
                                $missingTables[] = $table;
                            }
                        }
                        $schemaOk = empty($missingTables);
                    } catch (\Throwable $e) {
                        $dbOk = false;
                        $schemaOk = false;
                    } finally {
                        app(TenancyManager::class)->end();
                    }

                    if ($dbOk) {
                        $healthSummary['db_ok']++;
                    }
                    if ($schemaOk) {
                        $healthSummary['schema_ok']++;
                    }

                    $subscription = Subscription::query()
                        ->with('plan')
                        ->where('tenant_id', $tenant->id)
                        ->latest('id')
                        ->first();

                    if ($subscription) {
                        $isActiveSub = in_array($subscription->status, ['active', 'trialing'], true)
                            && (!$subscription->ends_at || $subscription->ends_at->toDateString() >= $today->toDateString());
                        if ($isActiveSub) {
                            $healthSummary['subscription_ok']++;
                        }

                        $limit = (int) ($subscription->plan->whatsapp_limit ?? 0);
                        $usage = (int) TenantUsage::query()
                            ->where('tenant_id', $tenant->id)
                            ->where('metric', 'whatsapp_messages_sent')
                            ->where('period_key', now()->format('Y-m'))
                            ->value('value');

                        if ($limit > 0 && $usage > $limit) {
                            $healthSummary['whatsapp_limit_breached']++;
                        }
                    }
                }
                $data['tenant_health_summary'] = $healthSummary;

                $data['expiring_subscriptions'] = Subscription::query()
                    ->with(['tenant', 'plan'])
                    ->whereIn('status', ['active', 'trialing'])
                    ->whereNotNull('ends_at')
                    ->whereBetween('ends_at', [$today->toDateString(), $today->copy()->addDays(15)->toDateString()])
                    ->orderBy('ends_at')
                    ->limit(10)
                    ->get();

                $mrr = 0.0;
                $activeSubs = Subscription::query()->whereIn('status', ['active', 'trialing'])->get();
                foreach ($activeSubs as $sub) {
                    $amount = (float) $sub->amount;
                    if ($amount <= 0) {
                        continue;
                    }
                    $plan = Plan::query()->find($sub->plan_id);
                    $cycle = $plan?->billing_cycle ?? 'monthly';
                    if ($cycle === 'yearly') {
                        $amount = $amount / 12;
                    } elseif ($cycle === 'quarterly') {
                        $amount = $amount / 3;
                    }
                    $mrr += $amount;
                }
                $data['saas_kpis']['estimated_mrr'] = round($mrr, 2);

                $planMix = Subscription::query()
                    ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
                    ->select('plans.name', DB::raw('COUNT(subscriptions.id) as subs_count'))
                    ->groupBy('plans.name')
                    ->orderByDesc('subs_count')
                    ->get();
                $data['plan_mix_labels'] = $planMix->pluck('name')->toArray();
                $data['plan_mix_values'] = $planMix->pluck('subs_count')->map(fn ($v) => (int) $v)->toArray();

                $revenueTrend = [];
                $tenantTrend = [];
                $trendLabels = [];
                foreach ($last6Months as $month) {
                    $trendLabels[] = $month->format('M y');
                    $revenueTrend[] = (float) Subscription::query()
                        ->whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->sum('amount');
                    $tenantTrend[] = (int) Tenant::query()
                        ->whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->count();
                }
                $data['trend_labels'] = $trendLabels;
                $data['revenue_trend_values'] = $revenueTrend;
                $data['tenant_growth_values'] = $tenantTrend;

                $data['recent_tenants'] = Tenant::query()
                    ->latest('id')
                    ->take(10)
                    ->get()
                    ->map(function ($tenant) use ($monthStart) {
                        $sub = Subscription::query()->where('tenant_id', $tenant->id)->latest('id')->first();
                        $usage = (int) TenantUsage::query()
                            ->where('tenant_id', $tenant->id)
                            ->where('metric', 'whatsapp_messages_sent')
                            ->where('period_key', $monthStart->format('Y-m'))
                            ->value('value');

                        return (object) [
                            'id' => $tenant->id,
                            'name' => $tenant->name,
                            'slug' => $tenant->slug,
                            'database' => $tenant->database,
                            'is_active' => $tenant->is_active,
                            'plan' => $sub ? optional(Plan::query()->find($sub->plan_id))->name : null,
                            'subscription_status' => $sub->status ?? null,
                            'subscription_end' => $sub?->ends_at,
                            'whatsapp_used' => $usage,
                            'users_count' => User::query()->where('tenant_id', $tenant->id)->count(),
                        ];
                    });

                $dashboard = 'dashboard.superadmin';
                break;

            case 'company':

                $year = date('Y');
                $selectedInventoryCategoryIds = $this->normalizeDashboardCategoryFilter($request);
                $data['inventory_category_options'] = collect();
                $data['selected_inventory_category_ids'] = $selectedInventoryCategoryIds;

                if (Schema::connection($tenantConnection)->hasTable('categories')) {
                    $allCategories = Category::query()
                        ->select('id', 'parent_id', 'name')
                        ->orderBy('name')
                        ->get();

                    $groupedCategories = $allCategories->groupBy('parent_id');
                    $data['inventory_category_options'] = collect($this->buildCategoryFilterOptions($groupedCategories));
                }

                $rawData = DB::connection($tenantConnection)->table('lead_products')
                    ->join('products', 'products.id', '=', 'lead_products.product_id')
                    ->selectRaw('
                        products.name,
                        MONTH(lead_products.created_at) as month,
                        SUM(lead_products.price * lead_products.qty) as total
                    ')
                    ->whereYear('lead_products.created_at', $year);

                if (!empty($selectedInventoryCategoryIds)) {
                    $rawData->whereIn('products.category_id', $selectedInventoryCategoryIds);
                }

                $rawData = $rawData
                    ->groupBy('products.id', 'products.name', 'month')
                    ->get();

                $productsByMonth = [];
                foreach ($rawData as $row) {
                    if (!isset($productsByMonth[$row->name])) {
                        $productsByMonth[$row->name] = array_fill(1, 12, 0);
                    }
                    $productsByMonth[$row->name][$row->month] = (float) $row->total;
                }

                $chartSeries = [];
                foreach ($productsByMonth as $productName => $totals) {
                    $chartSeries[] = [
                        'name' => $productName,
                        'data' => array_values($totals)
                    ];
                }

                $data['chartSeries'] = $chartSeries;

                $topLeadRaw = DB::connection($tenantConnection)->table('lead_products')
                    ->join('leads', 'leads.id', '=', 'lead_products.lead_id')
                    ->select('leads.user_id',DB::raw('COUNT(DISTINCT leads.id) as lead_count'), DB::raw('SUM(lead_products.price * lead_products.qty) as total_value')         )
                    ->groupBy('leads.user_id')
                    ->orderByDesc('total_value')
                    ->limit(5)
                    ->get();

                $topLeadUserNames = User::whereIn('id', collect($topLeadRaw)->pluck('user_id')->filter()->unique()->all())
                    ->pluck('name', 'id');
                $topLeadUserIds = collect($topLeadRaw)->pluck('user_id')->filter()->unique()->all();
                $userIdsWithLocationHistory = LocationHistory::whereIn('user_id', $topLeadUserIds)
                    ->whereNotNull('user_id')
                    ->distinct()
                    ->pluck('user_id')
                    ->map(static fn ($id) => (int) $id)
                    ->all();

                $data['top_lead_list'] = collect($topLeadRaw)->map(function ($row) use ($topLeadUserNames, $userIdsWithLocationHistory) {
                    $row->name = $topLeadUserNames[$row->user_id] ?? 'Unknown';
                    $row->has_location_history = in_array((int) $row->user_id, $userIdsWithLocationHistory, true);
                    return $row;
                });


                $data['total_lead_count'] = Lead::count();
                $data['lead_cur_month'] = Lead::whereMonth('date', Carbon::now()->month)
                                    ->whereYear('date', Carbon::now()->year)->count();
                $data['today_lead_count'] = Lead::whereDate('date', Carbon::today())->count();

                // $data['lead_call_count']= LeadCall::count();
               $data['lead_cur_month_follow'] = LeadChat::whereMonth('next_date', Carbon::now()->month)
                ->whereYear('next_date', Carbon::now()->year)
                ->count();
                $data['lead_follow_today_count'] = LeadChat::whereDate('next_date', Carbon::today())->count();

                $data['lead_call_cur_month_count']= LeadCall::whereMonth('date_time', Carbon::now()->month)
                                ->whereYear('date_time', Carbon::now()->year)->count();

                // $data['lead_call_today_count'] = LeadCall::whereDate('date_time', Carbon::today())->count();
                $data['quote_total_count']= Quotes::count();
                $data['quote_month_count'] = Quotes::whereMonth('date', Carbon::now()->month)
                    ->whereYear('date', Carbon::now()->year)
                    ->count();
                $data['quote_today_count'] = Quotes::whereDate('date', Carbon::today())->count();


                $data['order_total_count']= Order::count();
                $data['order_month_count'] = Order::whereMonth('date', Carbon::now()->month)
                                        ->whereYear('date', Carbon::now()->year)->count();

                $data['order_today_count'] = Order::whereDate('date', Carbon::today())->count();

                $data['lead_source_list'] =LeadSource::all();

                $data['lead_stage_list'] = LeadStage::orderBy('id')->get();
                $data['counts'] =\App\Models\Lead::select('stage_id', DB::raw('COUNT(*) as cnt'))
                        ->groupBy('stage_id')->pluck('cnt', 'stage_id')->toArray();
                $data['totalLeads'] = array_sum($data['counts']);

                $inventoryQuery = DB::connection($tenantConnection)->table('products');
                if (Schema::connection($tenantConnection)->hasColumn('products', 'created_by')) {
                    $inventoryQuery->where('created_by', \Auth::user()->creatorId());
                }
                if (!empty($selectedInventoryCategoryIds)) {
                    $inventoryQuery->whereIn('category_id', $selectedInventoryCategoryIds);
                }

                $data['inventory_overview'] = [
                    'total_products' => (clone $inventoryQuery)->count(),
                    'active_products' => Schema::connection($tenantConnection)->hasColumn('products', 'is_active')
                        ? (clone $inventoryQuery)->where('is_active', 1)->count()
                        : (clone $inventoryQuery)->count(),
                    'total_available_stock' => (float) ((clone $inventoryQuery)->sum('stock_qty') ?? 0),
                    'category_breakdown' => collect(),
                ];

                if (Schema::connection($tenantConnection)->hasTable('categories')) {
                    $categoryQuery = DB::connection($tenantConnection)->table('products')
                        ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
                        ->select(
                            DB::raw('COALESCE(categories.id, 0) as category_id'),
                            DB::raw("COALESCE(categories.name, 'Uncategorized') as category_name"),
                            DB::raw('COUNT(products.id) as product_count'),
                            DB::raw('SUM(COALESCE(products.stock_qty, 0)) as stock_qty'),
                            DB::raw("SUM(CASE WHEN COALESCE(products.is_active, 1) = 1 THEN 1 ELSE 0 END) as active_product_count")
                        );

                    if (Schema::connection($tenantConnection)->hasColumn('products', 'created_by')) {
                        $categoryQuery->where('products.created_by', \Auth::user()->creatorId());
                    }
                    if (!empty($selectedInventoryCategoryIds)) {
                        $categoryQuery->whereIn('products.category_id', $selectedInventoryCategoryIds);
                    }

                    $data['inventory_overview']['category_breakdown'] = $categoryQuery
                        ->groupBy('categories.id', 'categories.name')
                        ->orderByDesc('stock_qty')
                        ->get();
                }

                //current month 20 follow-up list
                $data['lead_cur_month_follow_all_list'] = LeadChat::with(['getLeadDetail','getLeadStatus','getUser'])
                ->whereMonth('next_date', Carbon::now()->month)
                ->whereYear('next_date', Carbon::now()->year)->orderBy('id','desc')->take(20)->get();



                $dashboard = 'dashboard.company';
                break;

            case 'Employee':
                $dashboard = 'dashboard.employee';
                break;

            case 'accountant':
                $dashboard = 'dashboard.accountant';
                break;

            case 'HRM':

                $allEmpIds = User::whereIn('type', ['Employee', 'Sales','HRM'])->pluck('id')->toArray();
                $presentEmpIds = Attendance::where('date', date('Y-m-d'))
                    ->where('is_present', 1)
                    ->distinct('employee_id')
                    ->pluck('employee_id')
                    ->toArray();

                $emp = Employee::whereIn('user_id',$allEmpIds)->pluck('id')->toArray();

                $absentEmpIds = array_diff($emp, $presentEmpIds);
                $data['total_emp_count'] = count($allEmpIds);
                $data['leave_pending_count'] =Leave::where('status','1')->count();
                $data['present_emp_count'] = count($presentEmpIds);
                $data['absent_emp_count'] = count($absentEmpIds);
                $data['department_acc_emp_list'] = DB::connection($tenantConnection)->table('employees')
                    ->join('departments', 'departments.id', '=', 'employees.department_id')
                    ->select('departments.name as dept_name', DB::raw('COUNT(employees.id) as emp_count'))
                    ->groupBy('departments.name')
                    ->pluck('emp_count', 'dept_name')
                    ->toArray();


                $employee = Attendance::where('date', date('Y-m-d'))
                    ->selectRaw('employee_id, MIN(check_in) as check_in')
                    ->groupBy('employee_id')->with('getEmployee')->get();

                $current_day = date('l');
                $dayList = [
                    1 => 'Monday',
                    2 => 'Tuesday',
                    3 => 'Wednesday',
                    4 => 'Thursday',
                    5 => 'Friday',
                    6 => 'Saturday',
                ];

                $lateComers = [];

                foreach ($dayList as $key => $dlist)
                {
                    if ($dlist == $current_day)
                    {
                        $cur_day_numb = $key;
                        $current_working_hrs = WorkingHours::where('day', $cur_day_numb)->first();

                        if ($current_working_hrs)
                        {
                            foreach ($employee as $emp) {
                                if ($emp->check_in > $current_working_hrs->start_time) {
                                    $lateComers[] = $emp->getEmployee->name;
                                }
                            }
                        }
                    }
                }

                $data['late_emp_list'] =$lateComers;
                $dashboard = 'dashboard.hrm';
                break;

            case 'Sales':
                $userId = \Auth::user()->id;
                $now = Carbon::now();
                $today = Carbon::today();
                $monthStart = $now->copy()->startOfMonth();
                $monthEnd = $now->copy()->endOfMonth();
                $prevMonthStart = $now->copy()->subMonth()->startOfMonth();
                $prevMonthEnd = $now->copy()->subMonth()->endOfMonth();
                $nextWeek = $today->copy()->addDays(7);
                $last90Days = $today->copy()->subDays(90);

                $data['month_val'] = date('Y-m');
                $data['get_emp'] = Employee::where('user_id', $userId)->first();

                $hasTable = function ($tableName) use ($tenantConnection) {
                    return Schema::connection($tenantConnection)->hasTable($tableName);
                };
                $hasColumn = function ($tableName, $columnName) use ($tenantConnection) {
                    return Schema::connection($tenantConnection)->hasTable($tableName)
                        && Schema::connection($tenantConnection)->hasColumn($tableName, $columnName);
                };

                $leadBase = Lead::where('user_id', $userId);
                $data['lead_count_month'] = (clone $leadBase)->whereMonth('date', $now->month)->whereYear('date', $now->year)->count();
                $data['follow_up'] = LeadChat::where('created_by', $userId)
                    ->whereMonth('next_date', $now->month)
                    ->whereYear('next_date', $now->year)
                    ->distinct('lead_id')
                    ->count();

                $data['lead_stage_list'] = Lead::where('user_id', $userId)
                    ->whereMonth('date', $now->month)
                    ->whereYear('date', $now->year)
                    ->join('lead_stages', 'leads.stage_id', '=', 'lead_stages.id')
                    ->select('lead_stages.name as stage_name', DB::raw('count(*) as total'))
                    ->groupBy('leads.stage_id', 'lead_stages.name')
                    ->get();

                $userLeadIds = Lead::where('user_id', $userId)->pluck('id');
                $data['today_calls'] = LeadCall::whereIn('lead_id', $userLeadIds)
                    ->whereDate('date_time', $today)
                    ->count();

                $data['today_followups'] = LeadChat::where('created_by', $userId)->whereDate('next_date', $today)->count();
                $data['overdue_followups'] = LeadChat::where('created_by', $userId)->whereDate('next_date', '<', $today)->count();

                $todayLeads = Lead::where('user_id', $userId)->whereDate('date', $today)->count();
                $todaySalesAmount = Order::where('user_id', $userId)->whereDate('date', $today)->sum('grand_total');

                $newCustomersToday = 0;
                if ($hasTable('entities') && $hasColumn('entities', 'user_id') && $hasColumn('entities', 'type')) {
                    $newCustomersToday = DB::connection($tenantConnection)->table('entities')
                        ->where('user_id', $userId)
                        ->where('type', 'customer')
                        ->whereDate('created_at', $today)
                        ->count();
                }

                $pendingQuoteCount = Quotes::where('user_id', $userId)->whereIn('status', [0, 1])->count();

                $collectionsToday = 0;
                $collectionsMonth = 0;
                if ($hasTable('payments') && $hasColumn('payments', 'amount')) {
                    $paymentQuery = DB::connection($tenantConnection)->table('payments')->where('payment_status', 'paid');
                    if ($hasColumn('payments', 'created_by')) {
                        $paymentQuery->where('created_by', $userId);
                    }
                    if ($hasColumn('payments', 'payment_date')) {
                        $collectionsToday = (clone $paymentQuery)->whereDate('payment_date', $today)->sum('amount');
                        $collectionsMonth = (clone $paymentQuery)->whereBetween('payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])->sum('amount');
                    } else {
                        $collectionsToday = (clone $paymentQuery)->whereDate('created_at', $today)->sum('amount');
                        $collectionsMonth = (clone $paymentQuery)->whereBetween('created_at', [$monthStart->toDateString(), $monthEnd->toDateString()])->sum('amount');
                    }
                }

                $data['today_kpis'] = [
                    'today_sales_amount' => (float) $todaySalesAmount,
                    'new_leads_today' => (int) $todayLeads,
                    'new_customers_today' => (int) $newCustomersToday,
                    'today_followups' => (int) $data['today_followups'],
                    'pending_quotations' => (int) $pendingQuoteCount,
                    'collections_today' => (float) $collectionsToday,
                ];

                $targetAmount = (float) optional(optional($data['get_emp'])->SalesTarget)->min_target;
                $achievedAmount = (float) (\App\Models\Utility::getSalesEmpTarget($userId, $data['month_val']) ?? 0);
                $remainingTarget = max($targetAmount - $achievedAmount, 0);
                $achievementPercent = $targetAmount > 0 ? round(($achievedAmount / $targetAmount) * 100, 2) : 0;
                $requiredRunRate = $remainingTarget > 0 ? round($remainingTarget / max($monthEnd->diffInDays($today) + 1, 1), 2) : 0;

                $monthPerformance = [];
                $monthPerformanceLabels = [];
                for ($i = 5; $i >= 0; $i--) {
                    $monthDate = $now->copy()->subMonths($i);
                    $monthPerformanceLabels[] = $monthDate->format('M y');
                    $monthPerformance[] = (float) Order::where('user_id', $userId)
                        ->whereMonth('date', $monthDate->month)
                        ->whereYear('date', $monthDate->year)
                        ->sum('grand_total');
                }

                $data['target_summary'] = [
                    'monthly_target' => $targetAmount,
                    'achieved_amount' => $achievedAmount,
                    'remaining_target' => $remainingTarget,
                    'achievement_percent' => $achievementPercent,
                    'required_daily_run_rate' => $requiredRunRate,
                    'monthly_performance_labels' => $monthPerformanceLabels,
                    'monthly_performance_values' => $monthPerformance,
                ];

                $monthlySalesRows = Order::where('user_id', $userId)
                    ->whereYear('date', $now->year)
                    ->selectRaw('MONTH(date) as month_no, SUM(grand_total) as total_sales')
                    ->groupBy('month_no')
                    ->pluck('total_sales', 'month_no')
                    ->toArray();

                $salesTrendValues = [];
                for ($m = 1; $m <= 12; $m++) {
                    $salesTrendValues[] = (float) ($monthlySalesRows[$m] ?? 0);
                }

                $growthLabels = [];
                $growthValues = [];
                $prevValue = null;
                for ($i = 5; $i >= 0; $i--) {
                    $monthDate = $now->copy()->subMonths($i);
                    $value = (float) Order::where('user_id', $userId)
                        ->whereMonth('date', $monthDate->month)
                        ->whereYear('date', $monthDate->year)
                        ->sum('grand_total');
                    $growthLabels[] = $monthDate->format('M');
                    $growthValues[] = ($prevValue !== null && $prevValue > 0) ? round((($value - $prevValue) / $prevValue) * 100, 2) : 0;
                    $prevValue = $value;
                }

                $topProducts = collect();
                if ($hasTable('order_products') && $hasTable('products')) {
                    $topProducts = DB::connection($tenantConnection)->table('order_products')
                        ->join('orders', 'orders.id', '=', 'order_products.order_id')
                        ->join('products', 'products.id', '=', 'order_products.product_id')
                        ->where('orders.user_id', $userId)
                        ->whereBetween('orders.date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                        ->select(
                            'products.name as product_name',
                            DB::raw('SUM(order_products.total) as total_revenue'),
                            DB::raw('SUM(order_products.qty) as sold_qty')
                        )
                        ->groupBy('order_products.product_id', 'products.name')
                        ->orderByDesc('total_revenue')
                        ->limit(5)
                        ->get();
                }

                if ($topProducts->isEmpty() && $hasTable('lead_products')) {
                    $leadGrandTotal = DB::connection($tenantConnection)->table('lead_products')
                        ->join('leads', 'leads.id', '=', 'lead_products.lead_id')
                        ->where('leads.user_id', $userId)
                        ->whereBetween('leads.date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                        ->select(DB::raw('SUM(lead_products.price * lead_products.qty) as total'))
                        ->value('total');

                    if ($leadGrandTotal) {
                        $topProducts = DB::connection($tenantConnection)->table('lead_products')
                            ->join('leads', 'leads.id', '=', 'lead_products.lead_id')
                            ->join('products', 'products.id', '=', 'lead_products.product_id')
                            ->where('leads.user_id', $userId)
                            ->whereBetween('leads.date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                            ->select(
                                'products.name as product_name',
                                DB::raw('SUM(lead_products.price * lead_products.qty) as total_revenue'),
                                DB::raw('SUM(lead_products.qty) as sold_qty'),
                                DB::raw("ROUND((SUM(lead_products.price * lead_products.qty) / {$leadGrandTotal}) * 100, 2) as percentage")
                            )
                            ->groupBy('lead_products.product_id', 'products.name')
                            ->orderByDesc('total_revenue')
                            ->limit(5)
                            ->get();
                    }
                }

                $data['sales_charts'] = [
                    'trend_labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    'trend_values' => $salesTrendValues,
                    'growth_labels' => $growthLabels,
                    'growth_values' => $growthValues,
                    'product_labels' => $topProducts->pluck('product_name')->toArray(),
                    'product_values' => $topProducts->pluck('total_revenue')->map(fn ($v) => (float) $v)->toArray(),
                ];
                $data['top_products_by_user'] = $topProducts;

                $quoteBase = Quotes::where('user_id', $userId);
                $quoteDraft = (clone $quoteBase)->where('status', 0)->count();
                $quotePending = (clone $quoteBase)->where('status', 1)->count();
                $quoteSent = (clone $quoteBase)->where('status', 2)->count();
                $quoteApproved = (clone $quoteBase)->where('status', 3)->count();
                $quoteRejected = (clone $quoteBase)->where('status', 4)->count();

                $monthQuotesSent = Quotes::where('user_id', $userId)
                    ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->whereIn('status', [2, 3])
                    ->count();
                $monthOrderConverted = Order::where('user_id', $userId)
                    ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->count();

                $quotationConversionRate = $monthQuotesSent > 0 ? round(($monthOrderConverted / $monthQuotesSent) * 100, 2) : 0;

                $data['quotation_summary'] = [
                    'draft' => $quoteDraft,
                    'pending' => $quotePending,
                    'sent' => $quoteSent,
                    'approved' => $quoteApproved,
                    'rejected' => $quoteRejected,
                    'conversion_rate' => $quotationConversionRate,
                ];

                $outstandingTotal = (float) Order::where('user_id', $userId)
                    ->where('payment_status', 'unpaid')
                    ->sum(DB::raw('COALESCE(remaining_payment, grand_total)'));

                $overdueAmount = 0.0;
                $upcomingDueAmount = 0.0;
                if ($hasColumn('orders', 'is_advance_payment') && $hasColumn('orders', 'payment_after_days')) {
                    $dueDateExpr = "CASE WHEN is_advance_payment = 1 THEN date ELSE DATE_ADD(date, INTERVAL IFNULL(payment_after_days,0) DAY) END";
                    $overdueAmount = (float) DB::connection($tenantConnection)->table('orders')
                        ->where('user_id', $userId)
                        ->where('payment_status', 'unpaid')
                        ->whereRaw("$dueDateExpr < CURDATE()")
                        ->sum(DB::raw('COALESCE(remaining_payment, grand_total)'));

                    $upcomingDueAmount = (float) DB::connection($tenantConnection)->table('orders')
                        ->where('user_id', $userId)
                        ->where('payment_status', 'unpaid')
                        ->whereRaw("$dueDateExpr BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)")
                        ->sum(DB::raw('COALESCE(remaining_payment, grand_total)'));
                }

                $collectionTarget = $targetAmount > 0 ? round($targetAmount * 0.70, 2) : 0;
                $collectionTargetPct = $collectionTarget > 0 ? round(($collectionsMonth / $collectionTarget) * 100, 2) : 0;
                $data['collection_summary'] = [
                    'total_outstanding' => $outstandingTotal,
                    'overdue_amount' => $overdueAmount,
                    'upcoming_due' => $upcomingDueAmount,
                    'collection_this_month' => (float) $collectionsMonth,
                    'collection_target' => $collectionTarget,
                    'collection_target_pct' => $collectionTargetPct,
                ];

                $totalAssignedCustomers = 0;
                if ($hasTable('entities') && $hasColumn('entities', 'user_id') && $hasColumn('entities', 'type')) {
                    $totalAssignedCustomers = DB::connection($tenantConnection)->table('entities')
                        ->where('user_id', $userId)
                        ->where('type', 'customer')
                        ->count();
                }

                $activeCustomerIds = Order::where('user_id', $userId)
                    ->whereDate('date', '>=', $last90Days->toDateString())
                    ->distinct('customer_id')
                    ->pluck('customer_id')
                    ->toArray();
                $activeCustomers = count($activeCustomerIds);
                $inactiveCustomers = max($totalAssignedCustomers - $activeCustomers, 0);

                $topCustomers = Order::query()
                    ->join('entities', 'entities.id', '=', 'orders.customer_id')
                    ->where('orders.user_id', $userId)
                    ->select(
                        'orders.customer_id',
                        'entities.name as customer_name',
                        DB::raw('SUM(orders.grand_total) as total_revenue'),
                        DB::raw('COUNT(orders.id) as total_orders')
                    )
                    ->groupBy('orders.customer_id', 'entities.name')
                    ->orderByDesc('total_revenue')
                    ->limit(5)
                    ->get();

                $data['customer_insights'] = [
                    'total_assigned_customers' => $totalAssignedCustomers,
                    'active_customers' => $activeCustomers,
                    'inactive_customers' => $inactiveCustomers,
                    'top_customers' => $topCustomers,
                ];

                $meetingCount = 0;
                $visitCount = 0;
                if ($hasTable('lead_activities') && $hasColumn('lead_activities', 'action')) {
                    $meetingCount = DB::connection($tenantConnection)->table('lead_activities')
                        ->where('user_id', $userId)
                        ->whereDate('date_time', $today)
                        ->where('action', 'like', '%meeting%')
                        ->count();
                    $visitCount = DB::connection($tenantConnection)->table('lead_activities')
                        ->where('user_id', $userId)
                        ->whereDate('date_time', $today)
                        ->where('action', 'like', '%visit%')
                        ->count();
                }

                $callsLogged = LeadCall::where('user_id', $userId)->whereDate('date_time', $today)->count();
                $data['activity_tracker'] = [
                    'today_followups' => (int) $data['today_followups'],
                    'overdue_followups' => (int) $data['overdue_followups'],
                    'meetings_scheduled' => (int) $meetingCount,
                    'calls_logged' => (int) $callsLogged,
                    'visits_logged' => (int) $visitCount,
                ];

                $data['inventory_summary'] = [
                    'fast_moving_products' => collect(),
                    'low_stock_alerts' => collect(),
                    'backordered_products' => collect(),
                ];
                if ($hasTable('inventory_package_stocks') && $hasTable('products') && $hasTable('order_products')) {
                    $data['inventory_summary']['fast_moving_products'] = DB::connection($tenantConnection)->table('order_products')
                        ->join('orders', 'orders.id', '=', 'order_products.order_id')
                        ->join('products', 'products.id', '=', 'order_products.product_id')
                        ->leftJoin('inventory_package_stocks', 'inventory_package_stocks.product_id', '=', 'products.id')
                        ->where('orders.user_id', $userId)
                        ->whereDate('orders.date', '>=', $today->copy()->subDays(30)->toDateString())
                        ->select(
                            'products.name as product_name',
                            DB::raw('SUM(order_products.qty) as sold_qty'),
                            DB::raw('MAX(COALESCE(inventory_package_stocks.available_qty,0)) as available_qty')
                        )
                        ->groupBy('products.id', 'products.name')
                        ->orderByDesc('sold_qty')
                        ->limit(5)
                        ->get();

                    $data['inventory_summary']['low_stock_alerts'] = DB::connection($tenantConnection)->table('inventory_package_stocks')
                        ->join('products', 'products.id', '=', 'inventory_package_stocks.product_id')
                        ->whereColumn('inventory_package_stocks.available_qty', '<=', 'inventory_package_stocks.min_qty')
                        ->select(
                            'products.name as product_name',
                            'inventory_package_stocks.available_qty',
                            'inventory_package_stocks.min_qty'
                        )
                        ->orderBy('inventory_package_stocks.available_qty')
                        ->limit(8)
                        ->get();

                    $data['inventory_summary']['backordered_products'] = DB::connection($tenantConnection)->table('order_products')
                        ->join('orders', 'orders.id', '=', 'order_products.order_id')
                        ->join('products', 'products.id', '=', 'order_products.product_id')
                        ->leftJoin('inventory_package_stocks', 'inventory_package_stocks.product_id', '=', 'products.id')
                        ->where('orders.user_id', $userId)
                        ->whereDate('orders.date', '>=', $today->copy()->subDays(30)->toDateString())
                        ->select(
                            'products.name as product_name',
                            DB::raw('SUM(order_products.qty) as demand_qty'),
                            DB::raw('MAX(COALESCE(inventory_package_stocks.available_qty,0)) as available_qty')
                        )
                        ->groupBy('products.id', 'products.name')
                        ->havingRaw('SUM(order_products.qty) > MAX(COALESCE(inventory_package_stocks.available_qty,0))')
                        ->orderByDesc('demand_qty')
                        ->limit(8)
                        ->get();
                }

                $totalMonthOrders = Order::where('user_id', $userId)->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
                $totalMonthOrdersAmount = (clone $totalMonthOrders)->sum('grand_total');
                $totalMonthOrdersCount = (clone $totalMonthOrders)->count();
                $avgOrderValue = $totalMonthOrdersCount > 0 ? round($totalMonthOrdersAmount / $totalMonthOrdersCount, 2) : 0;

                $repeatCustomerCount = Order::where('user_id', $userId)
                    ->select('customer_id', DB::raw('COUNT(*) as total_orders'))
                    ->groupBy('customer_id')
                    ->having('total_orders', '>', 1)
                    ->count();
                $repeatCustomerRate = $totalAssignedCustomers > 0 ? round(($repeatCustomerCount / $totalAssignedCustomers) * 100, 2) : 0;

                $leadToOrderConversion = $data['lead_count_month'] > 0 ? round(($totalMonthOrdersCount / $data['lead_count_month']) * 100, 2) : 0;

                $avgCollectionDays = 0;
                if ($hasTable('order_payments') && $hasTable('payments')) {
                    $avgCollectionDays = (float) DB::connection($tenantConnection)->table('order_payments')
                        ->join('payments', 'payments.id', '=', 'order_payments.payment_id')
                        ->join('orders', 'orders.id', '=', 'order_payments.order_id')
                        ->where('orders.user_id', $userId)
                        ->where('order_payments.payment_status', 'paid')
                        ->selectRaw('AVG(DATEDIFF(payments.payment_date, orders.date)) as avg_days')
                        ->value('avg_days');
                }

                $currentMonthSales = $totalMonthOrdersAmount;
                $previousMonthSales = Order::where('user_id', $userId)->whereBetween('date', [$prevMonthStart->toDateString(), $prevMonthEnd->toDateString()])->sum('grand_total');
                $salesGrowthPct = $previousMonthSales > 0 ? round((($currentMonthSales - $previousMonthSales) / $previousMonthSales) * 100, 2) : 0;

                $data['advanced_kpis'] = [
                    'avg_order_value' => $avgOrderValue,
                    'repeat_customer_rate' => $repeatCustomerRate,
                    'lead_conversion_rate' => $leadToOrderConversion,
                    'avg_collection_days' => $avgCollectionDays,
                    'dispatch_delay_pct' => 0,
                    'sales_growth_pct' => $salesGrowthPct,
                ];

                $data['lead_cur_month_follow_all_list'] = LeadChat::with(['getLeadDetail', 'getLeadStatus', 'getUser'])
                    ->whereIn('id', function ($query) use ($today) {
                        $query->selectRaw('MAX(id)')
                            ->from('lead_chats')
                            ->where('created_by', auth()->id())
                            ->whereDate('next_date', '>=', $today->toDateString())
                            ->groupBy('lead_id');
                    })
                    ->orderBy('id', 'desc')
                    ->take(20)
                    ->get();

                $data['sales_today_comparison'] = [
                    'today_leads' => $todayLeads,
                    'today_sales' => (float) $todaySalesAmount,
                ];

                $dashboard = 'dashboard.sales';
                break;
        }

        if($dashboard == ''){

            return redirect()->back()->with('error', __('Permission Denied.'));

        }

        return view($dashboard,$data);



    }

    private function tenantConnectionName(): string
    {
        return config('database.default', 'mysql');
    }
}
