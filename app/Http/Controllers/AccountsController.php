<?php

namespace App\Http\Controllers;

use App\Models\BankDetail;
use App\Models\Entity;
use App\Models\Order;
use App\Models\Payments;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountsController extends Controller
{
    public function index()
    {
        if (!$this->canAccessAccounts()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $paymentsQuery = $this->basePaymentsQuery();
        $ordersQuery = $this->baseOrdersQuery();
        $customerQuery = $this->baseEntityQuery('customer');
        $vendorQuery = $this->baseEntityQuery('vendor');

        $totalInvoiced = (float) (clone $ordersQuery)->sum('grand_total');
        $totalOutstandingInvoices = (float) (clone $ordersQuery)->where('payment_status', 'unpaid')->sum('remaining_payment');
        $totalCollected = (float) (clone $paymentsQuery)->where('payment_type', 'credit')->sum('amount');
        $totalPaidOut = (float) (clone $paymentsQuery)->where('payment_type', 'debit')->sum('amount');

        $receivableTotal = (float) (clone $customerQuery)->sum('due_amount');
        $payableTotal = (float) (clone $vendorQuery)->sum('due_amount');
        $netCashflow = $totalCollected - $totalPaidOut;

        $overdueAmount = (float) (clone $ordersQuery)
            ->where('payment_status', 'unpaid')
            ->whereDate('date', '<', Carbon::now()->subDays(30)->toDateString())
            ->sum('remaining_payment');

        $recentTransactions = $this->mapPaymentRows((clone $paymentsQuery)
            ->orderByDesc('id')
            ->limit(12)
            ->get());

        $topReceivables = (clone $customerQuery)
            ->where('due_amount', '>', 0)
            ->orderByDesc('due_amount')
            ->limit(8)
            ->get(['id', 'name', 'company_name', 'due_amount', 'paid_amount']);

        $topPayables = (clone $vendorQuery)
            ->where('due_amount', '>', 0)
            ->orderByDesc('due_amount')
            ->limit(8)
            ->get(['id', 'name', 'company_name', 'due_amount', 'paid_amount']);

        $monthLabels = [];
        $creditSeries = [];
        $debitSeries = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthLabels[] = $month->format('M Y');

            $monthQuery = (clone $paymentsQuery)
                ->whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month);

            $creditSeries[] = (float) (clone $monthQuery)->where('payment_type', 'credit')->sum('amount');
            $debitSeries[] = (float) (clone $monthQuery)->where('payment_type', 'debit')->sum('amount');
        }

        return view('accounts.index', [
            'kpis' => [
                'total_invoiced' => $totalInvoiced,
                'total_collected' => $totalCollected,
                'total_paid_out' => $totalPaidOut,
                'net_cashflow' => $netCashflow,
                'receivable_total' => $receivableTotal,
                'payable_total' => $payableTotal,
                'invoice_outstanding' => $totalOutstandingInvoices,
                'overdue_amount' => $overdueAmount,
            ],
            'recent_transactions' => $recentTransactions,
            'top_receivables' => $topReceivables,
            'top_payables' => $topPayables,
            'cashflow_labels' => $monthLabels,
            'cashflow_credit' => $creditSeries,
            'cashflow_debit' => $debitSeries,
        ]);
    }

    public function customers(Request $request)
    {
        if (!$this->canAccessAccounts()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $search = trim((string) $request->input('search', ''));
        $dueFilter = (string) $request->input('due_filter', 'all');
        $paymentFilter = (string) $request->input('payment_filter', 'all');
        $sortBy = (string) $request->input('sort_by', 'receivable_desc');
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $customers = $this->baseEntityQuery('customer')
            ->select('id', 'name', 'company_name', 'due_amount', 'paid_amount')
            ->orderByDesc('id')
            ->get();

        $orderStats = (clone $this->baseOrdersQuery())
            ->select('customer_id', DB::raw('SUM(grand_total) as total_invoiced'), DB::raw('SUM(remaining_payment) as invoice_due'))
            ->groupBy('customer_id')
            ->pluck('total_invoiced', 'customer_id');

        $paymentBase = $this->basePaymentsQuery();
        $payeeColumn = $this->resolvePaymentEntityColumn();
        $creditByCustomer = collect();
        if ($payeeColumn !== null) {
            $creditByCustomer = (clone $paymentBase)
                ->where('payment_type', 'credit')
                ->whereNotNull($payeeColumn)
                ->select($payeeColumn, DB::raw('SUM(amount) as total_collected'))
                ->groupBy($payeeColumn)
                ->pluck('total_collected', $payeeColumn);
        }

        $lastPaymentByCustomer = collect();
        if ($payeeColumn !== null) {
            $lastPaymentByCustomer = (clone $paymentBase)
                ->where('payment_type', 'credit')
                ->whereNotNull($payeeColumn)
                ->select($payeeColumn, DB::raw('MAX(payment_date) as last_payment_date'))
                ->groupBy($payeeColumn)
                ->pluck('last_payment_date', $payeeColumn);
        }

        $rows = $customers->map(function ($customer) use ($orderStats, $creditByCustomer, $lastPaymentByCustomer) {
            $customerId = (int) $customer->id;
            return [
                'id' => $customerId,
                'name' => $customer->company_name ?: $customer->name,
                'total_invoiced' => (float) ($orderStats[$customerId] ?? 0),
                'total_collected' => (float) ($creditByCustomer[$customerId] ?? 0),
                'receivable' => (float) ($customer->due_amount ?? 0),
                'paid' => (float) ($customer->paid_amount ?? 0),
                'last_payment_date' => $lastPaymentByCustomer[$customerId] ?? null,
            ];
        });

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $rows = $rows->filter(function ($row) use ($needle) {
                return str_contains(mb_strtolower((string) ($row['name'] ?? '')), $needle);
            })->values();
        }

        if ($dueFilter === 'due') {
            $rows = $rows->filter(fn ($row) => (float) ($row['receivable'] ?? 0) > 0)->values();
        } elseif ($dueFilter === 'clear') {
            $rows = $rows->filter(fn ($row) => (float) ($row['receivable'] ?? 0) <= 0)->values();
        }

        if ($paymentFilter === 'no_payment') {
            $rows = $rows->filter(fn ($row) => empty($row['last_payment_date']))->values();
        } elseif ($paymentFilter === 'last_30_days') {
            $cutoff = Carbon::now()->subDays(30)->startOfDay();
            $rows = $rows->filter(function ($row) use ($cutoff) {
                if (empty($row['last_payment_date'])) {
                    return false;
                }
                try {
                    return Carbon::parse($row['last_payment_date'])->gte($cutoff);
                } catch (\Throwable $e) {
                    return false;
                }
            })->values();
        }

        if ($sortBy === 'name_asc') {
            $rows = $rows->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();
        } elseif ($sortBy === 'invoiced_desc') {
            $rows = $rows->sortByDesc('total_invoiced')->values();
        } elseif ($sortBy === 'collected_desc') {
            $rows = $rows->sortByDesc('total_collected')->values();
        } else {
            $rows = $rows->sortByDesc('receivable')->values();
        }

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $total = $rows->count();
        $pagedRows = $rows->forPage($currentPage, $perPage)->values();

        $rowsPagination = new LengthAwarePaginator(
            $pagedRows,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('accounts.customers', [
            'rows' => $rowsPagination,
            'filters' => [
                'search' => $search,
                'due_filter' => $dueFilter,
                'payment_filter' => $paymentFilter,
                'sort_by' => $sortBy,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function customerLedger($customerId)
    {
        if (!$this->canAccessAccounts()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $customer = $this->baseEntityQuery('customer')->where('id', $customerId)->first();
        abort_if(!$customer, 404);

        $orders = (clone $this->baseOrdersQuery())
            ->where('customer_id', $customer->id)
            ->orderByDesc('date')
            ->get(['id', 'order_number', 'date', 'grand_total', 'remaining_payment', 'payment_status']);

        $paymentRows = collect();
        $payeeColumn = $this->resolvePaymentEntityColumn();
        if ($payeeColumn !== null) {
            $paymentRows = (clone $this->basePaymentsQuery())
                ->where($payeeColumn, $customer->id)
                ->orderByDesc('payment_date')
                ->orderByDesc('id')
                ->limit(200)
                ->get();
        }

        $paymentRows = $this->mapPaymentRows($paymentRows);
        $bankMap = BankDetail::query()->pluck('bank_name', 'id');
        $paymentRows = $paymentRows->map(function ($row) use ($bankMap) {
            $row['bank_name'] = !empty($row['bank_detail_id']) ? (string) ($bankMap[$row['bank_detail_id']] ?? '-') : '-';
            return $row;
        });

        return view('accounts.customer_ledger', [
            'customer' => $customer,
            'orders' => $orders,
            'payment_rows' => $paymentRows,
        ]);
    }

    private function canAccessAccounts(): bool
    {
        return Auth::user()->can('manage payment')
            || Auth::user()->can('manage report')
            || Auth::user()->can('manage finance report')
            || Auth::user()->can('manage bank detail');
    }

    private function resolvePaymentEntityColumn(): ?string
    {
        if (Schema::hasColumn('payments', 'payee_id')) {
            return 'payee_id';
        }
        if (Schema::hasColumn('payments', 'entity_id')) {
            return 'entity_id';
        }
        return null;
    }

    private function basePaymentsQuery()
    {
        $user = Auth::user();
        $creatorId = method_exists($user, 'creatorId') ? $user->creatorId() : $user->id;
        $isSales = (string) $user->type === 'Sales';

        $query = Payments::query();
        if (Schema::hasColumn('payments', 'created_by')) {
            if ($isSales) {
                $query->where('created_by', $user->id);
            } else {
                $query->where('created_by', $creatorId);
            }
        }

        if (Schema::hasColumn('payments', 'payee_type')) {
            $query->where(function ($q) {
                $q->whereNull('payee_type')
                    ->orWhere('payee_type', 'entity');
            });
        }

        return $query;
    }

    private function baseOrdersQuery()
    {
        $user = Auth::user();
        $creatorId = method_exists($user, 'creatorId') ? $user->creatorId() : $user->id;
        $isSales = (string) $user->type === 'Sales';

        $query = Order::query();
        if ($isSales && Schema::hasColumn('orders', 'user_id')) {
            $query->where('user_id', $user->id);
        } elseif (Schema::hasColumn('orders', 'created_by')) {
            $query->where('created_by', $creatorId);
        }
        return $query;
    }

    private function baseEntityQuery(string $type)
    {
        $user = Auth::user();
        $creatorId = method_exists($user, 'creatorId') ? $user->creatorId() : $user->id;
        $isSales = (string) $user->type === 'Sales';

        $query = $type === 'customer'
            ? Entity::realCustomers()
            : Entity::query()->where('type', $type);
        if ($isSales && Schema::hasColumn('entities', 'user_id')) {
            $query->where('user_id', $user->id);
        } elseif (Schema::hasColumn('entities', 'created_by')) {
            $query->where('created_by', $creatorId);
        }
        return $query;
    }

    private function mapPaymentRows($rows)
    {
        $rows = collect($rows);
        $payeeColumn = $this->resolvePaymentEntityColumn();

        $entityIds = $rows->pluck($payeeColumn)->filter()->unique()->values()->all();
        $entityMap = Entity::query()->whereIn('id', $entityIds)->pluck('name', 'id');

        return $rows->map(function ($row) use ($payeeColumn, $entityMap) {
            $entityId = $payeeColumn ? ($row->{$payeeColumn} ?? null) : null;
            $entityName = !empty($entityId) ? (string) ($entityMap[$entityId] ?? '-') : '-';

            return [
                'id' => $row->id,
                'payment_date' => $row->payment_date,
                'amount' => (float) ($row->amount ?? 0),
                'payment_type' => (string) ($row->payment_type ?? ''),
                'payment_method' => (string) ($row->payment_method ?? ''),
                'transaction_id' => (string) ($row->transaction_id ?? ''),
                'entity_name' => $entityName,
                'description' => (string) ($row->description ?? ''),
                'bank_detail_id' => $row->bank_detail_id ?? null,
            ];
        });
    }
}
