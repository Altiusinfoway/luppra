<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Lead;
use App\Models\LeadCall;
use App\Models\LeadChat;
use App\Models\Order;
use App\Models\Quotes;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function sales(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            if ($user->type !== 'Sales') {
                return Utility::return_response(false, "Only Sales dashboard is available on this API.", "", 403);
            }

            $userId = (int) $user->id;
            $tenantConnection = $this->tenantConnectionName();
            $now = Carbon::now();
            $today = Carbon::today();
            $monthStart = $now->copy()->startOfMonth();
            $monthEnd = $now->copy()->endOfMonth();
            $prevMonthStart = $now->copy()->subMonth()->startOfMonth();
            $prevMonthEnd = $now->copy()->subMonth()->endOfMonth();
            $last90Days = $today->copy()->subDays(90);

            $data = [];
            $data['current_year'] = date('Y');
            $data['current_month'] = date('F');
            $data['current_date'] = date('d-m-Y');
            $data['month_val'] = date('Y-m');
            $data['attendance_id'] = 0;
            $data['get_emp'] = Employee::where('user_id', $userId)->first();

            $hasTable = function (string $tableName) use ($tenantConnection): bool {
                return Schema::connection($tenantConnection)->hasTable($tableName);
            };

            $hasColumn = function (string $tableName, string $columnName) use ($tenantConnection): bool {
                return Schema::connection($tenantConnection)->hasTable($tableName)
                    && Schema::connection($tenantConnection)->hasColumn($tableName, $columnName);
            };

            $leadBase = Lead::where('user_id', $userId);
            $data['lead_count_month'] = (clone $leadBase)
                ->whereMonth('date', $now->month)
                ->whereYear('date', $now->year)
                ->count();

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

            $data['today_followups'] = LeadChat::where('created_by', $userId)
                ->whereDate('next_date', $today)
                ->count();

            $data['overdue_followups'] = LeadChat::where('created_by', $userId)
                ->whereDate('next_date', '<', $today)
                ->count();

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
                    $collectionsMonth = (clone $paymentQuery)
                        ->whereBetween('payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                        ->sum('amount');
                } else {
                    $collectionsToday = (clone $paymentQuery)->whereDate('created_at', $today)->sum('amount');
                    $collectionsMonth = (clone $paymentQuery)
                        ->whereBetween('created_at', [$monthStart->toDateString(), $monthEnd->toDateString()])
                        ->sum('amount');
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
            $achievedAmount = (float) (Utility::getSalesEmpTarget($userId, $data['month_val']) ?? 0);
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
                $growthValues[] = ($prevValue !== null && $prevValue > 0)
                    ? round((($value - $prevValue) / $prevValue) * 100, 2)
                    : 0;
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

            $totalMonthOrders = Order::where('user_id', $userId)
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()]);

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
            $previousMonthSales = Order::where('user_id', $userId)
                ->whereBetween('date', [$prevMonthStart->toDateString(), $prevMonthEnd->toDateString()])
                ->sum('grand_total');

            $salesGrowthPct = $previousMonthSales > 0
                ? round((($currentMonthSales - $previousMonthSales) / $previousMonthSales) * 100, 2)
                : 0;

            $data['advanced_kpis'] = [
                'avg_order_value' => $avgOrderValue,
                'repeat_customer_rate' => $repeatCustomerRate,
                'lead_conversion_rate' => $leadToOrderConversion,
                'avg_collection_days' => $avgCollectionDays,
                'dispatch_delay_pct' => 0,
                'sales_growth_pct' => $salesGrowthPct,
            ];

            $data['lead_cur_month_follow_all_list'] = LeadChat::with(['getLeadDetail', 'getLeadStatus', 'getUser'])
                ->whereIn('id', function ($query) use ($today, $userId) {
                    $query->selectRaw('MAX(id)')
                        ->from('lead_chats')
                        ->where('created_by', $userId)
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

            return Utility::return_response(true, "Sales dashboard.", $data, 200);
        } catch (\Throwable $e) {
            return Utility::return_response(false, "Unable to load sales dashboard.", "", 500);
        }
    }

    private function tenantConnectionName(): string
    {
        return config('database.default', 'mysql');
    }
}
