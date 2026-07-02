<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\OrderPayment;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\EmployeeSalaryDetail;
use App\Models\UserLogin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales_outstanding_report(Request $request)
    {
        if ($request->input('summary') == '1') {
            $query = Order::where('created_by', \Auth::user()->creatorId())
                ->where('payment_status', 'unpaid');

            if ($request->start_date != "" && $request->end_date != "") {
                $query->whereBetween('date', [$request->start_date, $request->end_date]);
            }

            $orders = $query->with('customer')->orderBy('id', 'desc')->get();
            $rows = collect($orders)->map(function ($order) {
                $orderPayment = OrderPayment::where('order_id', $order->id)->sum('amount');
                $outstanding = (float) $order->grand_total - (float) $orderPayment;
                return [
                    'customer' => $order->customer->name ?? 'Unknown',
                    'due' => max($outstanding, 0),
                ];
            })->filter(fn($row) => $row['due'] > 0);

            $top = $rows->groupBy('customer')->map(fn($group) => (float) $group->sum('due'))
                ->sortDesc()
                ->take(8);

            return response()->json([
                'kpis' => [
                    ['label' => 'Total Outstanding', 'value' => $this->money($rows->sum('due'))],
                    ['label' => 'Unpaid Orders', 'value' => number_format($rows->count())],
                    ['label' => 'Customers with Due', 'value' => number_format($rows->pluck('customer')->unique()->count())],
                ],
                'chart' => [
                    'title' => 'Top Outstanding Customers',
                    'labels' => $top->keys()->values(),
                    'values' => $top->values(),
                ],
            ]);
        }

        if ($request->ajax()) {
            try {

                $take_order = [];

                $query = Order::where('created_by', \Auth::user()->creatorId())
                            ->where('payment_status', 'unpaid');

                // DATE FILTER
                if ($request->start_date != "" && $request->end_date != "") {
                    $query->whereBetween('date', [$request->start_date, $request->end_date]);
                }

                $total_unpaid_order = $query->orderBy('id', 'desc')->get();

                foreach ($total_unpaid_order as $order) {

                    $order_payment = OrderPayment::where('order_id', $order->id)->sum('amount');
                    $outstanding_amt = $order->grand_total - $order_payment;

                    if ($outstanding_amt > 0) {
                        $take_order[] = [
                            'order_no'      => $order->order_number,
                            'customer_name' => $order->customer->name ?? '',
                            'order_date'    => $order->date,
                            'due_amount'    => $outstanding_amt,
                        ];
                    }
                }

                return DataTables::of($take_order)
                    ->addIndexColumn()
                    ->make(true);

            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: '.$e->getMessage()
                ], 500);
            }
        }

        return view('report.sales_outstanding');
    }

    public function total_sale(Request $request)
    {
        if ($request->input('summary') == '1') {
            $query = $this->scopedOrdersQuery()->where('payment_status', 'paid');
            if ($request->start_date != "" && $request->end_date != "") {
                $query->whereBetween('date', [$request->start_date, $request->end_date]);
            }

            $orders = $query->get();
            $trend = $orders->groupBy(function ($row) {
                return Carbon::parse($row->date)->format('d M');
            })->map(fn($group) => (float) $group->sum('grand_total'))->take(10);

            $total = (float) $orders->sum('grand_total');
            $count = (int) $orders->count();

            return response()->json([
                'kpis' => [
                    ['label' => 'Total Sales', 'value' => $this->money($total)],
                    ['label' => 'Paid Orders', 'value' => number_format($count)],
                    ['label' => 'Avg Order Value', 'value' => $this->money($count > 0 ? $total / $count : 0)],
                ],
                'chart' => [
                    'title' => 'Sales Trend',
                    'labels' => $trend->keys()->values(),
                    'values' => $trend->values(),
                ],
            ]);
        }

        if ($request->ajax())
        {
            try {

                $query = Order::with('getCustomer')
                            ->where('payment_status', 'paid');

                if (\Auth::user()->type == 'Sales') {
                    $query->where('user_id', \Auth::id());
                } else {
                    $query->where('created_by', \Auth::user()->creatorId());
                }

                if ($request->start_date != "" && $request->end_date != "") {
                    $query->whereBetween('date', [$request->start_date, $request->end_date]);
                }

                $orders = $query->orderBy('id', 'desc')->get();

                return DataTables::of($orders)
                    ->addIndexColumn()

                    ->addColumn('customer_name', function($row) {
                        return $row->getCustomer->name ?? '';
                    })

                    // EXAMPLE: TOTAL AMOUNT DISPLAY
                    ->addColumn('total_amount', function($row) {
                        return $row->grand_total;
                    })

                    ->make(true);

            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: '.$e->getMessage()
                ], 500);
            }
        }
        return view('report.total_sale');
    }

    public function customer_sales(Request $request)
    {
        if ($request->input('summary') == '1') {
            $query = $this->scopedOrdersQuery()
                ->selectRaw('customer_id, SUM(grand_total) as total_amount')
                ->with('getCustomer')
                ->where('payment_status', 'paid')
                ->groupBy('customer_id');

            if ($request->start_date != "" && $request->end_date != "") {
                $query->whereBetween('date', [$request->start_date, $request->end_date]);
            }

            $data = $query->get();
            $top = $data->sortByDesc('total_amount')->take(8);

            return response()->json([
                'kpis' => [
                    ['label' => 'Customers Billed', 'value' => number_format($data->count())],
                    ['label' => 'Total Customer Sales', 'value' => $this->money($data->sum('total_amount'))],
                    ['label' => 'Top Customer', 'value' => $top->first()?->getCustomer?->name ?? '-'],
                ],
                'chart' => [
                    'title' => 'Top Customers by Revenue',
                    'labels' => $top->map(fn($row) => $row->getCustomer->name ?? 'Unknown')->values(),
                    'values' => $top->map(fn($row) => (float) $row->total_amount)->values(),
                ],
            ]);
        }

        if ($request->ajax())
        {
            try {

                $query = Order::selectRaw('customer_id, SUM(grand_total) as total_amount')
                            ->with('getCustomer')
                            ->where('payment_status', 'paid')
                            ->groupBy('customer_id');

                if (\Auth::user()->type == 'Sales') {
                    $query->where('user_id', \Auth::id());
                } else {
                    $query->where('created_by', \Auth::user()->creatorId());
                }

                if ($request->start_date != "" && $request->end_date != "") {
                    $query->whereBetween('date', [$request->start_date, $request->end_date]);
                }

                $data = $query->orderBy('id', 'desc')->get();

                return DataTables::of($data)
                    ->addIndexColumn()

                    ->addColumn('customer_name', function($row) {
                        return $row->getCustomer->name ?? '';
                    })

                    ->addColumn('total_amount', function($row) {
                        return number_format($row->total_amount, 2);
                    })

                    ->rawColumns(['customer_name'])
                    ->make(true);

            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: '.$e->getMessage()
                ], 500);
            }
        }

        return view('report.customer_according_sales');
    }

     public function customer_type(Request $request)
    {
        if ($request->input('summary') == '1') {
            $query = $this->scopedOrdersQuery()->selectRaw(
                'customer_id, COUNT(id) as order_count, SUM(grand_total) as total_amount'
            )
            ->where('payment_status', 'paid')
            ->groupBy('customer_id');

            if ($request->start_date != "" && $request->end_date != "") {
                $query->whereBetween('date', [$request->start_date, $request->end_date]);
            }

            $rows = $query->get();
            $newCustomers = $rows->filter(fn($row) => (int) $row->order_count === 1);
            $oldCustomers = $rows->filter(fn($row) => (int) $row->order_count > 1);

            return response()->json([
                'kpis' => [
                    ['label' => 'Total Customers', 'value' => number_format($rows->count())],
                    ['label' => 'New Customers', 'value' => number_format($newCustomers->count())],
                    ['label' => 'Old Customers', 'value' => number_format($oldCustomers->count())],
                ],
                'chart' => [
                    'title' => 'Customer Mix',
                    'labels' => ['New', 'Old'],
                    'values' => [$newCustomers->count(), $oldCustomers->count()],
                ],
            ]);
        }

        if ($request->ajax())
        {

            try {

                $query = Order::selectRaw(
                            'customer_id,
                            COUNT(id) as order_count,
                            SUM(grand_total) as total_amount'
                        )
                        ->with('getCustomer')
                        ->where('payment_status', 'paid')
                        ->groupBy('customer_id');

                if (\Auth::user()->type == 'Sales') {
                    $query->where('user_id', \Auth::id());
                } else {
                    $query->where('created_by', \Auth::user()->creatorId());
                }

                if ($request->start_date != "" && $request->end_date != "") {
                    $query->whereBetween('date', [$request->start_date, $request->end_date]);
                }

                // Filter: New / Old
                if ($request->customer_type == "new") {
                    $query->havingRaw('COUNT(id) = 1');
                }

                if ($request->customer_type == "old") {
                    $query->havingRaw('COUNT(id) > 1');
                }

                $data = $query->orderBy('id', 'desc')->get();

                return DataTables::of($data)
                    ->addIndexColumn()

                    ->addColumn('customer_name', function ($row) {
                        return $row->getCustomer->name ?? '';
                    })

                    ->addColumn('customer_type', function ($row) {
                        return ($row->order_count > 1) ? 'Old Customer' : 'New Customer';
                    })

                    ->addColumn('total_amount', function ($row) {
                        return number_format($row->total_amount, 2);
                    })

                    ->rawColumns(['customer_name'])
                    ->make(true);

            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }


        return view('report.customer_new_old');
    }


    public function income_expense_report(Request $request)
    {
        if ($request->input('summary') == '1') {
            $total_income = $this->scopedOrdersQuery()->where('payment_status', 'paid')->sum('grand_total');
            $total_expense = $this->scopedEmployeeSalaryQuery()->where('payment_status', 'paid')->sum('final_salary');
            $net_profit = $total_income - $total_expense;

            return response()->json([
                'kpis' => [
                    ['label' => 'Income', 'value' => $this->money($total_income)],
                    ['label' => 'Expense', 'value' => $this->money($total_expense)],
                    ['label' => 'Net Profit', 'value' => $this->money($net_profit)],
                ],
                'chart' => [
                    'title' => 'Income vs Expense',
                    'labels' => ['Income', 'Expense'],
                    'values' => [(float) $total_income, (float) $total_expense],
                ],
                'net_profit' => (float) $net_profit,
            ]);
        }

        $total_income = $this->scopedOrdersQuery()->where('payment_status', 'paid')->sum('grand_total');
        $total_expense = $this->scopedEmployeeSalaryQuery()->where('payment_status', 'paid')->sum('final_salary');

        $net_profit = $total_income - $total_expense;

        return view('report.income_expense_report', compact(
            'total_income',
            'total_expense',
            'net_profit'
        ));
    }

    public function user_login_report(Request $request)
    {
        if ($request->input('summary') == '1') {
            $query = UserLogin::query()->whereHas('user', function ($q) {
                if (\Auth::user()->type == 'Sales') {
                    $q->where('id', \Auth::id());
                } else {
                    $q->where('created_by', \Auth::user()->creatorId());
                }
            });
            if ($request->user_id != "") {
                $query->where('user_id', $request->user_id);
            }
            if ($request->start_date != "" && $request->end_date != "") {
                $query->whereBetween('login_date_time', [$request->start_date, $request->end_date]);
            }

            $rows = $query->get();
            $webCount = $rows->where('is_web_app_login', 1)->count();
            $appCount = $rows->where('is_web_app_login', 0)->count();
            $activeSessions = $rows->whereNull('logout_date_time')->count();

            return response()->json([
                'kpis' => [
                    ['label' => 'Total Logins', 'value' => number_format($rows->count())],
                    ['label' => 'Web Logins', 'value' => number_format($webCount)],
                    ['label' => 'App Logins', 'value' => number_format($appCount)],
                    ['label' => 'Active Sessions', 'value' => number_format($activeSessions)],
                ],
                'chart' => [
                    'title' => 'Web vs App Login',
                    'labels' => ['Web', 'App'],
                    'values' => [$webCount, $appCount],
                ],
            ]);
        }

        if ($request->ajax()) {

        try {

            $query = UserLogin::with('user')->whereHas('user', function ($q) {
                if (\Auth::user()->type == 'Sales') {
                    $q->where('id', \Auth::id());
                } else {
                    $q->where('created_by', \Auth::user()->creatorId());
                }
            });

            // USER FILTER
            if ($request->user_id != "") {
                $query->where('user_id', $request->user_id);
            }

            // DATE FILTER
            if ($request->start_date != "" && $request->end_date != "") {
                $query->whereBetween('login_date_time', [$request->start_date, $request->end_date]);
            }

            $data = $query->orderBy('id', 'desc')->get();

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('user_name', function($row){
                    return $row->user->name ?? '';
                })
                ->addColumn('login_time', function($row){
                    return date("d-m-Y h:i A", strtotime($row->login_date_time));
                })
                ->addColumn('logout_time', function($row){
                    if ($row->logout_date_time) {
                        return date("d-m-Y h:i A", strtotime($row->logout_date_time));
                    }
                    return '<span class="badge bg-danger">Not Logged Out</span>';
                })
                ->addColumn('is_web_app_detail', function($row){
                    if ($row->is_web_app_login == 0)
                    {
                        return "App";
                    }
                    else
                    {
                        return "Web";
                    }
                    return '<span class="badge bg-danger">Not Logged Out</span>';
                })
                ->rawColumns(['logout_time'])
                ->make(true);

        } catch (\Exception $e) {
            return response()->json([
                "error" => "Server Error: ".$e->getMessage()
            ], 500);
        }
    }

    $users = \App\Models\User::Isdeleted()
        ->when(\Auth::user()->type == 'Sales', function ($q) {
            $q->where('id', \Auth::id());
        }, function ($q) {
            $q->where('created_by', \Auth::user()->creatorId());
        })
        ->select("id", "name")
        ->get();
    return view('report.user_login', compact('users'));
    }

    public function sales_analytics(Request $request)
    {
        if ($request->ajax()) {
            [$periodType, $startDate, $endDate, $periodLabel] = $this->resolvePeriod($request);

            $ordersQuery = $this->scopedOrdersQuery()->whereBetween('date', [$startDate, $endDate]);
            $orders = (clone $ordersQuery)->with('getCustomer')->orderByDesc('id')->get();
            $userNames = User::whereIn('id', $orders->pluck('user_id')->filter()->unique()->all())
                ->pluck('name', 'id');

            $totalSales = (float) $orders->sum('grand_total');
            $totalOrders = (int) $orders->count();
            $paidSales = (float) $orders->where('payment_status', 'paid')->sum('grand_total');
            $avgOrderValue = $totalOrders > 0 ? ($totalSales / $totalOrders) : 0;

            $trendRows = $this->salesTrendRows($periodType, $startDate, $endDate);

            $tableRows = $orders->map(function ($order) use ($userNames) {
                return [
                    'order_no' => (string) $order->order_number,
                    'date' => (string) Carbon::parse($order->date)->format('d M Y'),
                    'customer' => (string) ($order->getCustomer->name ?? '-'),
                    'sales_person' => (string) ($userNames[$order->user_id] ?? '-'),
                    'payment_status' => ucfirst((string) $order->payment_status),
                    'amount' => $this->money($order->grand_total),
                ];
            })->values()->all();

            return response()->json([
                'title' => 'Sales Analytics',
                'period_label' => $periodLabel,
                'kpis' => [
                    ['label' => 'Total Sales', 'value' => $this->money($totalSales)],
                    ['label' => 'Total Orders', 'value' => number_format($totalOrders)],
                    ['label' => 'Collections', 'value' => $this->money($paidSales)],
                    ['label' => 'Avg Order Value', 'value' => $this->money($avgOrderValue)],
                ],
                'chart' => [
                    'type' => 'line',
                    'title' => 'Sales Trend',
                    'labels' => array_values(array_column($trendRows, 'label')),
                    'values' => array_values(array_column($trendRows, 'total')),
                ],
                'table' => [
                    'columns' => [
                        ['key' => 'order_no', 'label' => 'Order No'],
                        ['key' => 'date', 'label' => 'Date'],
                        ['key' => 'customer', 'label' => 'Customer'],
                        ['key' => 'sales_person', 'label' => 'Sales Person'],
                        ['key' => 'payment_status', 'label' => 'Payment'],
                        ['key' => 'amount', 'label' => 'Amount'],
                    ],
                    'rows' => $tableRows,
                ],
            ]);
        }

        return view('report.analytics_dashboard', [
            'reportTitle' => 'Sales Analytics',
            'reportSubtitle' => 'Yearly / Monthly / Weekly',
            'reportEndpoint' => route('reports.sales_analytics'),
        ]);
    }

    public function sales_person_performance(Request $request)
    {
        if ($request->ajax()) {
            [$periodType, $startDate, $endDate, $periodLabel] = $this->resolvePeriod($request);

            $baseOrders = $this->scopedOrdersQuery()->whereBetween('date', [$startDate, $endDate]);

            $rows = (clone $baseOrders)
                ->select(
                    'orders.user_id',
                    DB::raw('COUNT(orders.id) as total_orders'),
                    DB::raw('SUM(orders.grand_total) as total_sales'),
                    DB::raw("SUM(CASE WHEN orders.payment_status = 'paid' THEN orders.grand_total ELSE 0 END) as collections")
                )
                ->groupBy('orders.user_id')
                ->orderByDesc('total_sales')
                ->get();
            $userNames = User::whereIn('id', $rows->pluck('user_id')->filter()->unique()->all())->pluck('name', 'id');

            $mappedRows = $rows->map(function ($row) use ($userNames) {
                $sales = (float) ($row->total_sales ?? 0);
                $orders = (int) ($row->total_orders ?? 0);
                $collections = (float) ($row->collections ?? 0);

                return [
                    'sales_person' => (string) ($userNames[$row->user_id] ?? 'Unassigned'),
                    'total_orders' => number_format($orders),
                    'total_sales' => $this->money($sales),
                    'collections' => $this->money($collections),
                    'avg_order' => $this->money($orders > 0 ? ($sales / $orders) : 0),
                ];
            })->values();

            $totalSales = (float) $rows->sum('total_sales');
            $totalOrders = (int) $rows->sum('total_orders');
            $activeUsers = (int) $rows->filter(fn($row) => (float) $row->total_sales > 0)->count();
            $topPerformer = $mappedRows->first()['sales_person'] ?? '-';

            return response()->json([
                'title' => 'Sales Person Performance',
                'period_label' => $periodLabel,
                'kpis' => [
                    ['label' => 'Team Sales', 'value' => $this->money($totalSales)],
                    ['label' => 'Team Orders', 'value' => number_format($totalOrders)],
                    ['label' => 'Active Sales Persons', 'value' => number_format($activeUsers)],
                    ['label' => 'Top Performer', 'value' => $topPerformer],
                ],
                'chart' => [
                    'type' => 'bar',
                    'title' => 'Sales by Sales Person',
                    'labels' => $mappedRows->pluck('sales_person')->all(),
                    'values' => $rows->pluck('total_sales')->map(fn($val) => (float) $val)->all(),
                ],
                'table' => [
                    'columns' => [
                        ['key' => 'sales_person', 'label' => 'Sales Person'],
                        ['key' => 'total_orders', 'label' => 'Orders'],
                        ['key' => 'total_sales', 'label' => 'Sales'],
                        ['key' => 'collections', 'label' => 'Collections'],
                        ['key' => 'avg_order', 'label' => 'Avg Order Value'],
                    ],
                    'rows' => $mappedRows->all(),
                ],
            ]);
        }

        return view('report.analytics_dashboard', [
            'reportTitle' => 'Sales Person Performance Analysis',
            'reportSubtitle' => 'Yearly / Monthly / Weekly',
            'reportEndpoint' => route('reports.sales_person_performance'),
        ]);
    }

    public function product_sales_analysis(Request $request)
    {
        if ($request->ajax()) {
            [$periodType, $startDate, $endDate, $periodLabel] = $this->resolvePeriod($request);

            $productsQuery = OrderProduct::query()
                ->join('orders', 'orders.id', '=', 'order_products.order_id')
                ->leftJoin('products', 'products.id', '=', 'order_products.product_id')
                ->whereBetween('orders.date', [$startDate, $endDate]);

            if (\Auth::user()->type == 'Sales') {
                $productsQuery->where('orders.user_id', \Auth::id());
            } else {
                $productsQuery->where('orders.created_by', \Auth::user()->creatorId());
            }

            $rows = $productsQuery
                ->select(
                    'order_products.product_id',
                    DB::raw("COALESCE(products.name, CONCAT('Product #', order_products.product_id)) as product_name"),
                    DB::raw('SUM(order_products.qty) as qty_sold'),
                    DB::raw('SUM(order_products.total) as revenue')
                )
                ->groupBy('order_products.product_id', 'products.name')
                ->orderByDesc('revenue')
                ->get();

            $topChartRows = $rows->take(10);

            $tableRows = $rows->values()->map(function ($row, $index) {
                $qty = (float) ($row->qty_sold ?? 0);
                $revenue = (float) ($row->revenue ?? 0);

                return [
                    'rank' => (string) ($index + 1),
                    'product' => (string) $row->product_name,
                    'qty_sold' => number_format($qty, 2),
                    'revenue' => $this->money($revenue),
                    'avg_price' => $this->money($qty > 0 ? ($revenue / $qty) : 0),
                ];
            })->all();

            $totalRevenue = (float) $rows->sum('revenue');
            $totalQty = (float) $rows->sum('qty_sold');
            $distinctProducts = (int) $rows->count();
            $topProduct = (string) ($rows->first()->product_name ?? '-');

            return response()->json([
                'title' => 'Product Sales Analysis',
                'period_label' => $periodLabel,
                'kpis' => [
                    ['label' => 'Total Product Revenue', 'value' => $this->money($totalRevenue)],
                    ['label' => 'Total Quantity Sold', 'value' => number_format($totalQty, 2)],
                    ['label' => 'Products Sold', 'value' => number_format($distinctProducts)],
                    ['label' => 'Top Product', 'value' => $topProduct],
                ],
                'chart' => [
                    'type' => 'bar',
                    'title' => 'Top Products by Revenue',
                    'labels' => $topChartRows->pluck('product_name')->all(),
                    'values' => $topChartRows->pluck('revenue')->map(fn($val) => (float) $val)->all(),
                ],
                'table' => [
                    'columns' => [
                        ['key' => 'rank', 'label' => '#'],
                        ['key' => 'product', 'label' => 'Product'],
                        ['key' => 'qty_sold', 'label' => 'Qty Sold'],
                        ['key' => 'revenue', 'label' => 'Revenue'],
                        ['key' => 'avg_price', 'label' => 'Avg Unit Price'],
                    ],
                    'rows' => $tableRows,
                ],
            ]);
        }

        return view('report.analytics_dashboard', [
            'reportTitle' => 'Products Sales Analysis',
            'reportSubtitle' => 'Yearly / Monthly / Weekly',
            'reportEndpoint' => route('reports.product_sales_analysis'),
        ]);
    }

    private function scopedOrdersQuery()
    {
        $query = Order::query();

        if (\Auth::user()->type == 'Sales') {
            $query->where('user_id', \Auth::id());
        } else {
            $query->where('created_by', \Auth::user()->creatorId());
        }

        return $query;
    }

    private function scopedEmployeeSalaryQuery()
    {
        $query = EmployeeSalaryDetail::query();
        $query->whereHas('getEmployee.getUser', function ($q) {
            if (\Auth::user()->type == 'Sales') {
                $q->where('id', \Auth::id());
            } else {
                $q->where('created_by', \Auth::user()->creatorId());
            }
        });

        return $query;
    }

    private function resolvePeriod(Request $request): array
    {
        $periodType = strtolower((string) $request->input('period', 'monthly'));
        if (!in_array($periodType, ['yearly', 'monthly', 'weekly'], true)) {
            $periodType = 'monthly';
        }

        $referenceDate = $request->filled('reference_date')
            ? Carbon::parse($request->input('reference_date'))
            : Carbon::now();

        if ($periodType === 'yearly') {
            $start = $referenceDate->copy()->startOfYear();
            $end = $referenceDate->copy()->endOfYear();
            $label = 'Year ' . $referenceDate->format('Y');
        } elseif ($periodType === 'weekly') {
            $start = $referenceDate->copy()->startOfWeek(Carbon::MONDAY);
            $end = $referenceDate->copy()->endOfWeek(Carbon::SUNDAY);
            $label = $start->format('d M Y') . ' - ' . $end->format('d M Y');
        } else {
            $start = $referenceDate->copy()->startOfMonth();
            $end = $referenceDate->copy()->endOfMonth();
            $label = $referenceDate->format('F Y');
        }

        return [$periodType, $start->toDateString(), $end->toDateString(), $label];
    }

    private function salesTrendRows(string $periodType, string $startDate, string $endDate): array
    {
        $query = $this->scopedOrdersQuery()->whereBetween('date', [$startDate, $endDate]);

        if ($periodType === 'yearly') {
            $rows = $query
                ->select(DB::raw('MONTH(date) as sort_idx'), DB::raw("DATE_FORMAT(date, '%b') as label"), DB::raw('SUM(grand_total) as total'))
                ->groupBy('sort_idx', 'label')
                ->orderBy('sort_idx')
                ->get();
        } elseif ($periodType === 'weekly') {
            $rows = $query
                ->select(DB::raw('WEEKDAY(date) as sort_idx'), DB::raw("DATE_FORMAT(date, '%a') as label"), DB::raw('SUM(grand_total) as total'))
                ->groupBy('sort_idx', 'label')
                ->orderBy('sort_idx')
                ->get();
        } else {
            $rows = $query
                ->select(DB::raw('DATE(date) as sort_idx'), DB::raw("DATE_FORMAT(date, '%d %b') as label"), DB::raw('SUM(grand_total) as total'))
                ->groupBy('sort_idx', 'label')
                ->orderBy('sort_idx')
                ->get();
        }

        return $rows->map(function ($row) {
            return [
                'label' => (string) $row->label,
                'total' => (float) $row->total,
            ];
        })->all();
    }

    private function money($value): string
    {
        return 'Rs. ' . number_format((float) $value, 2);
    }

}
