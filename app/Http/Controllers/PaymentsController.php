<?php

namespace App\Http\Controllers;

use Spatie\Permission\PermissionRegistrar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Models\Payments;
use App\Models\Entity;
use App\Models\BankDetail;
use App\Models\Utility;

class PaymentsController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!\Auth::user()->can('manage payment')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        if ($request->ajax()) {
            try {

            if(\Auth::user()->type == 'Sales')
            {

                $payments = Payments::where('created_by',\Auth::user()->id)->select(
                    'payments.id',
                    'payments.payment_date',
                    'payments.amount',
                    'payments.transaction_id',
                    'payments.payment_method',
                    'payments.payment_type',
                    'payments.description',
                );
            }
            else
            {
                $payments = Payments::select(
                    'payments.id',
                    'payments.payment_date',
                    'payments.amount',
                    'payments.transaction_id',
                    'payments.payment_method',
                    'payments.payment_type',
                    'payments.description'
                );


            }


                /* SEARCH */
                if ($request->filled('search')) {
                    $search = $request->search;
                    $payments->where(function ($q) use ($search) {
                        $q->where('payments.transaction_id', 'like', "%$search%")
                            ->orWhere('payments.id', 'like', "%$search%");
                    });
                }

                /* DATE RANGE */
                if ($request->filled('dateRange')) {
                    $dateRange = $request->dateRange;

                    if (str_contains($dateRange, ' to ')) {
                        [$start, $end] = explode(' to ', $dateRange);
                        $payments->whereBetween('payment_date', [
                            \Carbon\Carbon::createFromFormat('d M, Y', trim($start))->startOfDay(),
                            \Carbon\Carbon::createFromFormat('d M, Y', trim($end))->endOfDay()
                        ]);
                    } else {
                        $payments->whereDate(
                            'payment_date',
                            \Carbon\Carbon::createFromFormat('d M, Y', trim($dateRange))
                        );
                    }
                }

                /* PAYMENT METHOD */
                if ($request->filled('paymentMethod')) {
                    $payments->where('payment_method', $request->paymentMethod);
                }

                /* TOTALS */
                $creditTotal = (clone $payments)->where('payment_type', 'credit')->sum('amount');
                $debitTotal  = (clone $payments)->where('payment_type', 'debit')->sum('amount');
                $grandTotal  = $payments->sum('amount');

                return DataTables::of($payments->orderBy('id', 'desc')->get())
                    ->addIndexColumn()
                    ->addColumn('paymentsDebit', function ($row) {
                        return $row->payment_type === 'debit'
                            ? '<span class="text-danger">' . number_format($row->amount, 2) . '</span>'
                            : '-';
                    })

                    ->addColumn('paymentsCredit', function ($row) {
                        return $row->payment_type === 'credit'
                            ? '<span class="text-success">' . number_format($row->amount, 2) . '</span>'
                            : '-';
                    })

                    ->addColumn('total_payments', function ($row) {
                        return '<strong>' . number_format($row->amount, 2) . '</strong>';
                    })

                    ->addColumn('paymentDate', function ($row) {
                        return Utility::getDateFormated($row->payment_date);
                    })

                    ->rawColumns(['paymentsDebit', 'paymentsCredit', 'total_payments'])

                    ->with([
                        'credit_total' => $creditTotal,
                        'debit_total'  => $debitTotal,
                        'grand_total'  => $grandTotal
                    ])

                    ->make(true);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }

        $list_data['paymentMethods'] = Payments::getPaymentMethods();
        return view('payments.index', $list_data);
    }


    public function create(Request $request)
    {

        if (\Auth::user()->can('create payment')) {
            /* --- old code
            $paymentTypes = Payments::getPaymentTypes();
            $paymentMethods = Payments::getPaymentMethods();

            // Get Unpaid order list ::
            $orders = \App\Models\Order::select('orders.id', 'orders.order_number', 'entities.name as customer_name')
            ->join('entities', 'entities.id', '=', 'orders.customer_id')
            ->unpaid()
            //->limit(20)
            ->get()
            ->mapWithKeys(function ($order) {
                $label = $order->order_number . ' (' . ($order->customer_name ?? 'No Name') . ')';
                return [$order->id => $label];
            });
            compact('paymentTypes', 'paymentMethods', 'orders','bank_detail_list')
            */

            $bank_detail_list = BankDetail::all();
            $account_transaction_type = Payments::getAccountTransactionTypes();
            $paymentMethods = Payments::getPaymentMethods();


            return view('payments.create', compact('bank_detail_list', 'account_transaction_type', 'paymentMethods'));
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    /**
     * Store a new payment record.
     *
     * Handles the creation of a new payment, including validation,
     * saving payment details, and updating order remaining payment.
     *
     * @param Request $request The HTTP request containing payment details
     * @return \Illuminate\Http\RedirectResponse Redirects back with success/error message
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('create payment')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'account_transaction_type' => 'required|in:' . implode(',', array_keys(Payments::getAccountTransactionTypes())),
                    'entity_id' => 'required',
                    'amount' => 'required|gt:0',
                    'bank_detail_id' => 'required',
                    'payment_method' => 'required|in:' . implode(',', array_keys(Payments::getPaymentMethods())),
                    'transaction_id' => 'required_unless:payment_method,cash',
                    'payment_date' => 'required',

                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                if ($request->ajax()) {

                    return response()->json([
                        'errors' => $messages,
                    ], 422);
                }
                return redirect()->back()->with('error', $messages->first());
            }


            try {

                DB::beginTransaction();

                $payment                = new Payments();

                $payment->created_by        = \Auth::user()->id;
                $payment->payment_method    = $request->payment_method ?? '';
                $payment->payment_status    = 'paid';
                $payment->description       = $request->description ?? '';
                $payment->payment_date      = Utility::getDBDateFormated($request->payment_date);
                $payment->payment_type      = $request->account_transaction_type ?? '';
                $payment->amount            = $request->amount ?? '';
                $payment->bank_detail_id    = $request->bank_detail_id ?? '';
                $payment->payee_type        = "entity";
                $payment->payee_id          = $request->entity_id ?? null;
                $payment->cheque_no         = $payment->payment_method == 'cheque' ? $request->transaction_id : null;
                $payment->transaction_id    = $payment->payment_method != 'cheque' ? $request->transaction_id : null;


                if ($request->hasFile('attachment')) {

                    $filenameWithExt = $request->file('attachment')->getClientOriginalName();

                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('attachment')->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                    $dir = 'uploads/attachment/';
                    $attachment_path = $dir . $fileNameToStore;
                    if (\File::exists($attachment_path)) {
                        \File::delete($attachment_path);
                    }

                    $url = '';
                    $path = Utility::upload_file($request, 'attachment', $fileNameToStore, $dir, []);

                    if ($path['flag'] == 1) {
                        $url = $path['url'];
                    } else {

                        return response()->json(['message' => __($path['msg'])]);
                    }

                    $attachment  = !empty($request->attachment) ? $fileNameToStore : null;

                    $payment->attachment = $attachment;
                }
                $payment->save();

                $entity_rcd = Entity::where('id', $request->entity_id)->first();
                if ($entity_rcd) {
                    $dueAmount = $entity_rcd->due_amount - $payment->amount;
                    $paidAmount = $entity_rcd->paid_amount + $payment->amount;

                    $due_amount_final    = $dueAmount > 0 ? $dueAmount : 0;
                    $paid_amount_final   = $paidAmount;
                    // $entity_rcd->save();
                    $entity_rcd->update(['due_amount' => $due_amount_final, 'paid_amount' => $paid_amount_final]);
                }

                DB::commit();

                if ($request->ajax()) {

                    return response()->json([
                        'success' => 'true',
                        'message' => "Payment successfully created."
                    ]);
                }

                return redirect()->route('payments.index')->with('success', 'Payment successfully created.');
            } catch (\Exception $e) {

                DB::rollBack();
                throw $e;
            }
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function payment_credit(Request $request)
    {
        if (\Auth::user()->can('manage payment')) {
            //$payments = Payments::where('payment_type','credit')->where('created_by', '=', \Auth::user()->creatorId());
            //$list_data['payments'] = $payments->get();

            if ($request->ajax()) {

                try {

                 if(\Auth::user()->type == 'Sales')
                {
                    $payments = Payments::where('payment_type', 'credit')->select('payments.id', 'payments.payment_date', 'payments.amount', 'payments.transaction_id', 'payments.payment_method', 'payments.description')
                        ->where('payments.created_by',\Auth::user()->id);
                }
                else
                {
                    $payments = Payments::where('payment_type', 'credit')->select('payments.id', 'payments.payment_date', 'payments.amount', 'payments.transaction_id', 'payments.payment_method', 'payments.description')
                        ;
                }



                    if ($request->has('search') && $request->search != "") {
                        $search = $request->input('search');
                        $payments->where(function ($query) use ($search) {

                            $query->where('payments.transaction_id', 'like', '%' . $search . '%')
                                ->orWhere('payments.id', 'like', '%' . $search);
                        });
                    }

                    if ($request->has('dateRange') && $request->dateRange != "") {
                        $dateRange = $request->input('dateRange');

                        if (strpos($dateRange, ' to ') !== false) {
                            [$start, $end] = explode(' to ', $dateRange);

                            $startDate = \Carbon\Carbon::createFromFormat('d M, Y', trim($start))->startOfDay();
                            $endDate = \Carbon\Carbon::createFromFormat('d M, Y', trim($end))->endOfDay();
                            $payments->whereBetween('payment_date', [$startDate, $endDate]);
                        } else {
                            $date = \Carbon\Carbon::createFromFormat('d M, Y', trim($dateRange))->toDateString();
                            $payments->whereDate('payment_date', $date);
                        }
                    }


                    if ($request->has('paymentMethod') && $request->paymentMethod != "") {
                        $paymentMethod = $request->input('paymentMethod');

                        $payments->where('payment_method', $paymentMethod);
                    }

                    $grandTotal = $payments->sum('amount');

                    $data = $payments->orderBy('id', 'desc')->get();

                    return DataTables::of($data)
                         ->addIndexColumn()
                        ->addColumn('paymentDate', function ($row) {

                            return Utility::getDateFormated($row->payment_date);
                        })
                        ->addColumn('amount_raw', function ($row) {
                            return $row->amount;  // Add raw numeric amount
                        })
                        ->rawColumns(['paymentDate', 'amount_raw'])
                        ->with([
                            'grand_total' => $grandTotal
                        ])
                        ->setRowClass('main-row')
                        ->make(true);
                } catch (\Exception $e) {

                    return response()->json([
                        'error' => 'Server Error: ' . $e->getMessage()
                    ], 500);
                }
            }
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $list_data['paymentMethods'] = Payments::getPaymentMethods();
        return view('payments.index', $list_data);
    }


    public function payment_debit(Request $request)
    {
        if (\Auth::user()->can('manage payment')) {
            if ($request->ajax()) {

                try {

                    if(\Auth::user()->type == 'Sales')
                    {

                        $payments = Payments::where('payment_type', 'debit')->select('payments.id', 'payments.payment_date', 'payments.amount', 'payments.transaction_id', 'payments.payment_method', 'payments.description')
                            ->where('payments.created_by', '=', \Auth::user()->id);
                    }
                    else
                    {
                         $payments = Payments::where('payment_type', 'debit')->select('payments.id', 'payments.payment_date', 'payments.amount', 'payments.transaction_id', 'payments.payment_method', 'payments.description');

                    }


                    if ($request->has('search') && $request->search != "") {
                        $search = $request->input('search');
                        $payments->where(function ($query) use ($search) {

                            $query->where('payments.transaction_id', 'like', '%' . $search . '%')
                                ->orWhere('payments.id', 'like', '%' . $search);
                        });
                    }

                    if ($request->has('dateRange') && $request->dateRange != "") {
                        $dateRange = $request->input('dateRange');

                        if (strpos($dateRange, ' to ') !== false) {
                            [$start, $end] = explode(' to ', $dateRange);

                            $startDate = \Carbon\Carbon::createFromFormat('d M, Y', trim($start))->startOfDay();
                            $endDate = \Carbon\Carbon::createFromFormat('d M, Y', trim($end))->endOfDay();
                            $payments->whereBetween('payment_date', [$startDate, $endDate]);
                        } else {
                            $date = \Carbon\Carbon::createFromFormat('d M, Y', trim($dateRange))->toDateString();
                            $payments->whereDate('payment_date', $date);
                        }
                    }


                    if ($request->has('paymentMethod') && $request->paymentMethod != "") {
                        $paymentMethod = $request->input('paymentMethod');

                        $payments->where('payment_method', $paymentMethod);
                    }

                    $grandTotal = $payments->sum('amount');

                    $data = $payments->orderBy('id', 'desc')->get();



                    return DataTables::of($data)
                         ->addIndexColumn()
                        ->addColumn('paymentDate', function ($row) {

                            return Utility::getDateFormated($row->payment_date);
                        })
                        ->addColumn('amount_raw', function ($row) {
                            return $row->amount;  // Add raw numeric amount
                        })
                        ->rawColumns(['paymentDate', 'amount_raw'])
                        ->with([
                            'grand_total' => $grandTotal
                        ])
                        ->setRowClass('main-row')
                        ->make(true);
                } catch (\Exception $e) {

                    return response()->json([
                        'error' => 'Server Error: ' . $e->getMessage()
                    ], 500);
                }
            }
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $list_data['paymentMethods'] = Payments::getPaymentMethods();
        return view('payments.index', $list_data);
    }


    public function getDropdownData(Request $request)
    {
        $type = $request->get('type');

        if ($type === 'credit') {
            if(\Auth::user()->type == 'Sales')
            {
                $data = Entity::where('type', 'customer')->where('user_id',\Auth::user()->id)->pluck('name', 'id');
            }
            else
            {
                $data = Entity::where('type', 'customer')->pluck('name', 'id');
            }

        } else {

            $data = Entity::where(function ($q) {
                $q->where('type', 'transport')
                    ->orWhere('type', 'vendor');
            })->pluck('name', 'id');
        }

        return response()->json($data);
    }

    public function getEntityDueAmount(Request $request)
    {
        $entityId = $request->get('id');
        $entity = Entity::find($entityId);

        return response()->json([
            'due_amount' => $entity->due_amount ?? 0
        ]);
    }
}
