<?php

namespace App\Http\Controllers;

use Spatie\Permission\PermissionRegistrar;
use Illuminate\Http\Request;
use App\Models\Payments;
use App\Models\Order;
use App\Models\OrderStage;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Utility;
use PDF;
use Exception;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\BankDetail;
use App\Models\Entity;
use App\Models\OrderPayment;
use App\Models\CustomerPhone;
use App\Services\ActivityLogger;
use App\Services\TermsAndConditionService;

class OrdersController extends Controller
{
    private function writeOrderActivity(string $action, string $eventKey, Order $order, string $description, array $properties = []): void
    {
        ActivityLogger::writeFor('orders', $action, $order, null, [
            'event_key' => $eventKey,
            'description' => $description,
            'properties' => $properties,
        ]);
    }

    private function resolveOrderStageName(?int $stageId): ?string
    {
        if (!$stageId) {
            return null;
        }

        return OrderStage::where('id', $stageId)->value('name');
    }

    private function resolveOrderStatusEventKey(?string $stageName): string
    {
        $normalized = strtolower(trim((string) $stageName));

        if (str_contains($normalized, 'cancel')) {
            return 'order.cancelled';
        }

        if (str_contains($normalized, 'confirm')) {
            return 'order.confirmed';
        }

        return 'order.status_changed';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $orders = Order::where('created_by', '=', \Auth::user()->creatorId());
        $list_data['orders'] = $orders->get();
        $list_data['order_status_list'] = OrderStage::pluck('name','id');

        if ($request->ajax())
        {
            try
            {
                if(\Auth::user()->type == 'Sales')
                {
                    $orders = Order::with(['getCustomer','Orderstatus'])->select('id','order_number','customer_id','date','payment_status','grand_total','status')
                                        ->where('user_id',\Auth::user()->id);
                }
                else
                {
                     $orders = Order::with(['getCustomer','Orderstatus'])->select('id','order_number','customer_id','date','payment_status','grand_total','status')
                                        ->where('created_by', '=', \Auth::user()->creatorId());
                }

                // $orders = Order::with(['getCustomer','Orderstatus'])->select('id','order_number','customer_id','date','payment_status','grand_total','status')
                //                         ->where('created_by', '=', \Auth::user()->creatorId());

                if ($request->has('search') && $request->search != "") {
                    $search = $request->input('search');


                    $orders->where(function ($query) use ($search) {

                        $query->orWhere('order_number', 'like', '%' . $search . '%')
                            ->orWhere('grand_total', 'like', '%' . $search . '%')
                            ->orWhereHas('getCustomer', function ($q) use ($search) {
                                    $q->where('name', 'like', '%' . $search . '%');
                            });
                    });
                }

                if ($request->has('dateRange') && $request->dateRange != "") {
                    $dateRange = $request->input('dateRange');

                    if (strpos($dateRange, ' to ') !== false) {
                        [$start, $end] = explode(' to ', $dateRange);

                        $startDate = \Carbon\Carbon::createFromFormat('d M, Y', trim($start))->startOfDay();
                        $endDate = \Carbon\Carbon::createFromFormat('d M, Y', trim($end))->endOfDay();
                        $orders->whereBetween('date', [$startDate, $endDate]);
                    } else {
                        $date = \Carbon\Carbon::createFromFormat('d M, Y', trim($dateRange))->toDateString();
                        $orders->whereDate('date', $date);
                    }

                }

                if ($request->has('paymentMethod') && $request->paymentMethod != "") {
                    $paymentMethod = $request->input('paymentMethod');
                    $orders->where('payment_status', $paymentMethod);
                }

                if ($request->has('order_statusMethod') && $request->order_statusMethod != "") {
                    $order_statusMethod = $request->input('order_statusMethod');
                    $orders->where('status', $order_statusMethod);
                }

                $data = $orders->orderBy('id','desc')->get();
                return DataTables::of($data)
                 ->addIndexColumn()
                    ->addColumn('date', function($row){
                        return Utility::getDateFormated($row->date);
                    })
                    ->addColumn('order_status', function($row){
                        return $row->Orderstatus ? $row->Orderstatus->name : '-';
                    })
                    ->addColumn('customer_name', function($row){
                        return $row->getCustomer ? $row->getCustomer->name : '-';
                    })
                    // ->addColumn('payment_status', function($row){


                    //     if($row->payment_status == 'unpaid'){

                    //         return '<h5><button type="button" class="btn  badge bg-danger">'.$row->payment_status.'</button></h5>
                    //                 <h5><button type="button" class="btn  badge bg-primary" data-size="xl"
                    //                         data-url="'. route('orders.collect-payment',[$row->id]) .'"
                    //                         data-ajax-popup="true"
                    //                         data-bs-original-title="View Purchase Order">Collect Payment</button></h5>';
                    //     }

                    //     return '<h5><button type="button" class="btn  badge bg-success">'.$row->payment_status.'</button></h5>';

                    // })
                    ->addColumn('action', function($row){
                        $url = route('orders.view', $row->id);
                        return '<a href="'.$url.'" class="text-primary d-inline-block"><i class="ri-eye-fill fs-16"></i></a>';
                    })
                    ->rawColumns(['date','order_status','action','customer_name','payment_status'])
                    ->make(true);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);

            }
        }

        return view('order.index',$list_data);
    }

    public function view(Request $request,$id)
    {
         $data['order'] = Order::with('Orderstatus')->where('id',$id)->first();
         $data['order_status_list'] = OrderStage::pluck('name','id');
         $data['order_stages'] = OrderStage::orderBy('order')->get();
         $data['activityTimeline'] = ActivityLogger::activityForRecord($data['order'], null, 12, 'order_activities_page');
         return view('order.view',$data);
    }

    // Return only unpaid order list
    public function unpaidOrders(Request $request)
    {
        $search = $request->get('search');

        $orders = Order::select('orders.id', 'orders.order_number', 'entities.name as customer_name')
        ->join('entities', 'entities.id', '=', 'orders.customer_id')
        ->unpaid()
        ->when($search, function ($query, $search) {
            $query->where('orders.order_number', 'like', "%{$search}%")
                ->orWhere('entities.name', 'like', "%{$search}%");
        })
        ->limit(20)
        ->get()
        ->map(fn($order) => [
            'value' => $order->id,
            'label' => $order->order_number . ' (' . ($order->customer_name ?? 'No Name') . ')'
        ]);

        return response()->json($orders);
    }

    // Return order details :
    public function orderDetails(Request $request, $order){
        $order = $this->resolveOrderFromRoute($order);

        return response()->json([
            'id' => $order->id,
            'grandTotal' => $order->grand_total,
            'remainingPayment' => $order->remaining_payment,
        ]);

    }

     public function invoice(Request $report, $order)
    {
	          $order = $this->resolveOrderFromRoute($order);
              $invoiceTerms = app(TermsAndConditionService::class)
                  ->getConfiguredInvoiceTerms(app()->bound('currentTenant') ? 'tenant' : 'landlord');
	          $data['order'] = $order;
	            $data['order_products'] = $order->orderProducts;
            $data['bank_detail'] = BankDetail::first();
            $data['qrCode'] = '';
            $data['print_option'] = 'original';
            $data['for_pdf'] = false;
             $data['invoiceNumber'] = $order->bill_number ?? str_replace('ORDER','INV',$order->order_number);

            // $invoiceNumber = str_replace('/','-',$order->bill_number ?? str_replace('ORDER','INV',$order->order_number));

	              $data['check_discount_allow'] = Utility::isDiscountAllowed();
                  $data['invoice_terms'] = $invoiceTerms;

            //   return view('order.invoice', $data);


        $pdf = PDF::loadView('order.invoice', $data);

        // return $pdf->stream('invoice-INV-2023-001.pdf');

        return $pdf->download('invoice-'.$data['invoiceNumber'].'.pdf');


        /* ------ old file
        $dueDate = ($order->is_advance_payment) ? $order->date : date('Y-m-d', strtotime($order->date . ' + ' . $order->payment_after_days . ' days'));

        // Order Items :
        $items = [];
        if($order->orderProducts){

            foreach($order->orderProducts as $item){

                $items[] = [
                    'description' => $item->product->name,
                    'quantity' => $item->qty,
                    'unit_price' => $item->price,
                    'unit' => Utility::getUnitName($item->unit_id),
                    'discount' => $item->discount,
                    'tax' => $item->tax,
                    'total' => $item->total
                ];

            }
        }

        $customerCompany = $order->getCustomer;
        $companyBilling = optional($customerCompany)->getBillingAddress;
        $customer = optional($order->getCustomer);

        $data = [
            'invoiceNumber' =>  $order->bill_number ?? str_replace('ORDER','INV',$order->order_number),
            'invoiceDate' =>  Utility::getDateFormated($order->date),
            'dueDate' =>  Utility::getDateFormated($dueDate),
            'clientName' => optional($customerCompany)->company_name  ?? optional($customer)->name,
            'clientAddress' => optional($companyBilling)->address_line_1 .','. optional($companyBilling)->address_line_2,
            'clientCity' => optional($companyBilling)->get_city->name,
            'clientState' => optional($companyBilling)->get_state->name,
            'clientZip' => optional($companyBilling)->zipcode,
            'clientEmail' => optional($customerCompany)->email,
            'clientPhone' => optional($customerCompany)->phone,
            'status' => ucfirst($order->payment_status),
            //'paymentMethod' => 'Bank Transfer',
            'terms' => $order->payment_after_days,
            'items' => $items,
            'subtotal' => $order->grand_total,
            'taxRate' => $order->total_tax_sum,
            'taxAmount' => $order->gst,
            'discount' => 0,
            'total' => $order->grand_total,
            'notes' => 'Thank you for your business. Please make payment within 30 days.',
            'order_record'=> $order,
            'lr_number'=>$order->lr_number,
            'no_article'=>$order->no_article,
            'transport_charge'=>$order->transport_charge,
            'check_discount_allow'=>Utility::isDiscountAllowed(),
        ];

            // return view('order.invoice', $data);


        $pdf = PDF::loadView('order.invoice', $data);

        // return $pdf->stream('invoice-INV-2023-001.pdf');

        return $pdf->download('invoice-'.$data['invoiceNumber'].'.pdf');
        */

    }

	     public function addBillNumber(Request $request, $order)
	     {
	        $order = $this->resolveOrderFromRoute($order);
            $before = [
                'bill_number' => $order->bill_number,
                'lr_number' => $order->lr_number,
                'no_article' => $order->no_article,
                'transport_charge' => $order->transport_charge,
            ];
	        $order->bill_number = $request->bill_number;
	        $order->lr_number = $request->lr_number;
	        $order->no_article = $request->no_article;
	        $order->transport_charge = $request->transport_charge ?? 0.00;
	        $order->save();

            $after = [
                'bill_number' => $order->bill_number,
                'lr_number' => $order->lr_number,
                'no_article' => $order->no_article,
                'transport_charge' => $order->transport_charge,
            ];
            $changes = ActivityLogger::diff($before, $after);
            if (!empty($changes)) {
                $this->writeOrderActivity(
                    'update',
                    'order.updated',
                    $order,
                    'Order invoice details updated.',
                    ['changes' => $changes]
                );
            }

	        return response()->json([
            'success' => 'Bill number has been updated successfully.',
            'url' => route('orders.invoice',$order['id'])
        ], 200);

    }

     // Collect Payment of order.
    public function collectPayment(Request $request, $order){
        $order = $this->resolveOrderFromRoute($order);

        if(\Auth::user()->can('create payment'))
        {

            if($order){

                $bank_detail_list = BankDetail::all();
                $account_transaction_type = Payments::getAccountTransactionTypes();
                $paymentMethods = Payments::getPaymentMethods();
                $customer = Entity::select('id','name')->IsCustomer()->get();
                $requestData  = $request->all();

                return view('order.create-payments',compact('bank_detail_list','account_transaction_type','paymentMethods','customer','order') );
            }
        }
        else
        {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }

    }

    public function storecollectedPayment(Request $request, $order){
        $order = $this->resolveOrderFromRoute($order);

        $request->validate([
            'account_transaction_type'=>'required|in:' . implode(',', array_keys(Payments::getAccountTransactionTypes())),
            'entity_id'=>'required',
            'amount'=>'required|gt:0',
            'bank_detail_id' => 'required',
            'payment_method' => 'required|in:' . implode(',', array_keys(Payments::getPaymentMethods())),
            'transaction_id' => 'required_unless:payment_method,cash',
            'payment_date' => 'required',
        ]);

	        if($order){
                $previousPaymentStatus = (string) ($order->payment_status ?? '');
                $previousRemainingPayment = (float) ($order->remaining_payment ?? $order->grand_total ?? 0);
	            if ((int) $request->entity_id !== (int) $order->customer_id) {
                return response()->json([
                    'errors' => [
                        'entity_id' => ['Selected customer does not match this invoice customer.'],
                    ],
                ], 422);
            }

            $remainingAmount = max((float) ($order->remaining_payment ?? $order->grand_total), 0);
            if ((float) $request->amount > $remainingAmount) {
                return response()->json([
                    'errors' => [
                        'amount' => ['Amount cannot be greater than remaining invoice amount.'],
                    ],
                ], 422);
            }

            // create payment.
            $payment                = new Payments();

            $payment->created_by        = \Auth::user()->id;
            $payment->payment_method    = $request->payment_method ?? '';
            $payment->payment_status    = 'paid';
            $payment->description       = $request->description ?? '';
            $payment->payment_date      = Utility::getDBDateFormated($request->payment_date);
            $payment->payment_type      = $request->account_transaction_type ?? '';
            $payment->amount            = $request->amount ?? '';
            $payment->bank_detail_id    = $request->bank_detail_id ?? null;
            $payment->payee_type        = "entity";
            $payment->payee_id          = $request->entity_id ?? null;
            $payment->cheque_no         = $payment->payment_method == 'cheque' ? $request->transaction_id : null;
            $payment->transaction_id    = $payment->payment_method != 'cheque' ? $request->transaction_id : null;

            if($request->hasFile('attachment'))
            {

                $filenameWithExt = $request->file('attachment')->getClientOriginalName();

                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('attachment')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $dir = 'uploads/attachment/';
                $attachment_path = $dir . $fileNameToStore;
                if (\File::exists($attachment_path)) {
                    \File::delete($attachment_path);
                }

                $url    = '';
                $path   = Utility::upload_file($request,'attachment',$fileNameToStore,$dir,[]);

                if($path['flag'] == 1){

                    $url = $path['url'];

                } else {

                    return response()->json(['message' => __($path['msg'])]);

                }

                $attachment  = !empty($request->attachment) ? $fileNameToStore : null;

                $payment->attachment = $attachment;
            }
            $payment->save();

            // Settled customer due amount.
            $entity_rcd = Entity::where('id',$request->entity_id)->first();
            if($entity_rcd)
            {
                $dueAmount = $entity_rcd->due_amount - $payment->amount;
                $paidAmount = $entity_rcd->paid_amount + $payment->amount;

                $entity_rcd->due_amount    = $dueAmount > 0 ? $dueAmount : 0;
                $entity_rcd->paid_amount   = $paidAmount;
                $entity_rcd->save();
            }

            // Order Payments.
            $order_payment = new OrderPayment();
            $order_payment->order_id = $order->id;
            $order_payment->payment_id = $payment->id;
            $order_payment->payment_status = $payment->payment_status;
            $order_payment->amount = $payment->amount;
            $order_payment->save();

            // Calculate remainig amount.
            $paidAmount = OrderPayment::where('order_id', $order->id)
            ->where('payment_status', 'paid')
            ->sum('amount');

            if($paidAmount >= $order->grand_total){

                $order->payment_status = 'paid';
                $order->remaining_payment = 0;

            } else {

                $order->payment_status = 'unpaid';
                $order->remaining_payment = round($order->grand_total - $paidAmount,2);

            }

	            $order->save();

                if (
                    $previousPaymentStatus !== (string) ($order->payment_status ?? '')
                    || $previousRemainingPayment !== (float) ($order->remaining_payment ?? 0)
                ) {
                    $this->writeOrderActivity(
                        'change_status',
                        'order.payment_status_changed',
                        $order,
                        'Order payment status updated.',
                        [
                            'before' => [
                                'payment_status' => $previousPaymentStatus,
                                'remaining_payment' => $previousRemainingPayment,
                            ],
                            'after' => [
                                'payment_status' => $order->payment_status,
                                'remaining_payment' => (float) ($order->remaining_payment ?? 0),
                            ],
                            'payment' => [
                                'amount' => (float) $payment->amount,
                                'payment_id' => $payment->id,
                                'payment_method' => $payment->payment_method,
                            ],
                        ]
                    );
                }

	            return response()->json(['message' => 'Order payment status updated successfully!']);

        }

        return response()->json(['message' => 'Something went wrong!'],422);

    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'order_id'  => 'required|exists:tenant.orders,id',
            'status_id' => 'required|exists:tenant.order_stages,id',
        ]);

	        $order = Order::find($request->order_id);
            $previousStageId = (int) ($order->status ?? 0);
	        $order->status = $request->status_id;
	        $order->save();

            if ($previousStageId !== (int) $order->status) {
                $currentStageName = $this->resolveOrderStageName((int) $order->status);

                $this->writeOrderActivity(
                    'change_status',
                    $this->resolveOrderStatusEventKey($currentStageName),
                    $order,
                    'Order status changed.',
                    [
                        'before' => [
                            'status_id' => $previousStageId,
                            'status_name' => $this->resolveOrderStageName($previousStageId),
                        ],
                        'after' => [
                            'status_id' => (int) $order->status,
                            'status_name' => $currentStageName,
                        ],
                    ]
                );
            }

	        return response()->json(['success' => true]);
	    }


    public function invoiceOptions(Request $request, $order)
    {
        $order = $this->resolveOrderFromRoute($order);
        Utility::order_pdf_generate_store($order->id,'order_pdf');
        return view('order.invoice-options', compact('order'));

    }

    public function previewInvoice(Request $request, $order)
    {
        $order = $this->resolveOrderFromRoute($order);

        $printOptions = empty(request()->query())
                        ? ['original' => 1]
                        : request()->query();

        $invoices = '';

        $printCount = count($printOptions);
        $print = 0;
	        $invoiceLayout = Utility::getInvoiceLayout();
	        $invoiceTemplate = Utility::resolveOrderInvoiceTemplate(false);
	        $invoiceTerms = app(TermsAndConditionService::class)
	            ->getConfiguredInvoiceTerms(app()->bound('currentTenant') ? 'tenant' : 'landlord');

        $device = \App\Models\Device::where('user_id', \Auth::user()->id)->first();
        $cust_phone = CustomerPhone::where('customer_id', $order->customer_id)
                                ->where('is_whatsapp', 1)
                                ->first();

        // \Log::info('order device ',[$device]);
        // \Log::info('ORDER ',[$order]);
        $whatsapp_msg = $cust_phone ? $cust_phone->phone : null;
        $whatsappAttachment = $this->buildWhatsappInvoiceAttachment($order, $printOptions);
        //  \Log::info('ORDER whatsapp_msg ',[$whatsapp_msg]);

        foreach(array_keys($printOptions) as $val)
        {
            $print++;
            $data['order'] = $order;
            $data['order_products'] = $order->orderProducts;
            $data['bank_detail'] = BankDetail::first();
            $data['qrCode'] = '';
            $data['print_option'] = $val;
            $data['for_pdf'] = false;
            $data['invoice_layout'] = $invoiceLayout;

            // $invoiceNumber = str_replace('/','-',$order->bill_number ?? str_replace('ORDER','INV',$order->order_number));

	              $data['check_discount_allow'] = Utility::isDiscountAllowed();
                  $data['invoice_terms'] = $invoiceTerms;
	            $invoices .= view($invoiceTemplate, $data)->render();

            if($printCount != $print){

                $invoices .='<div class="page-break"></div>';
            }
        }


        return view('order.invoice-render', ['order' => $order, 'invoices' => $invoices, 'print_options' => $printOptions,
        'invoice_layout' => $invoiceLayout,
        'whatsapp_msg' => $whatsapp_msg,'device'=> $device,
        'whatsapp_attachment_url' => $whatsappAttachment['url'] ?? '',
        'whatsapp_attachment_name' => $whatsappAttachment['name'] ?? '']);

    }

      // Download Invoice
    public function invoice_new(Request $request, $order)
    {
        $order = $this->resolveOrderFromRoute($order);

        $printOptions = empty(request()->query())
        ? ['original' => 1]
        : request()->query();

	        $invoiceNumber = str_replace('/', '-', $order->bill_number ?? str_replace('ORDER', 'INV', $order->order_number));
	        $invoiceLayout = Utility::getInvoiceLayout();
	        $invoiceTemplate = Utility::resolveOrderInvoiceTemplate(true);
	        $invoiceTerms = app(TermsAndConditionService::class)
	            ->getConfiguredInvoiceTerms(app()->bound('currentTenant') ? 'tenant' : 'landlord');


        $full_path = storage_path('temp');

        if (!File::exists($full_path)) {
            File::makeDirectory($full_path, 0775, true, true);
        }

        $zipFile = "invoice-".$invoiceNumber.".zip";
        $zipPath = storage_path("temp/$zipFile");


        // Ensure temp directory exists
        // Storage::makeDirectory('temp');

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {

            foreach (array_keys($printOptions) as $val) {

                $data = [
                    'order' => $order,
                    'order_products' => $order->orderProducts,
                    'bank_detail' => BankDetail::first(),
                    'qrCode' => '',
                    'print_option' => $val,
	                    'for_pdf' => true,
	                    'invoice_layout' => $invoiceLayout,
	                    'check_discount_allow'=>Utility::isDiscountAllowed(),
	                    'invoice_terms' => $invoiceTerms,
	                ];

                $pdf = PDF::loadView($invoiceTemplate, $data);

                // Output PDF file contents
                if(count($printOptions) > 1)
                {
                    // Add to ZIP
                    $content = $pdf->output();
                    $zip->addFromString("{$val}-invoice-".$invoiceNumber.".pdf", $content);
                }
                else
                {
                    return $pdf->download("{$val}-invoice-".$invoiceNumber.".pdf");
                }

            }

            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);

    }

    private function resolveOrderFromRoute($routeValue): Order
    {
        $user = \Auth::user();
        $value = trim((string) $routeValue);

        $query = Order::query();

        if ($user && $user->type === 'Sales') {
            $query->where('user_id', $user->id);
        } elseif ($user) {
            $query->where('created_by', $user->creatorId());
        }

        if (is_numeric($value)) {
            return (clone $query)->whereKey((int) $value)->firstOrFail();
        }

        $normalizedOrderNumber = str_replace('INVOICE-', 'ORDER-', $value);

        return (clone $query)
            ->where(function ($q) use ($value, $normalizedOrderNumber) {
                $q->where('order_number', $value)
                  ->orWhere('order_number', $normalizedOrderNumber);
            })
            ->firstOrFail();
    }

    private function buildWhatsappInvoiceAttachment(Order $order, array $printOptions): array
    {
        $invoiceNumber = str_replace('/', '-', $order->bill_number ?? str_replace('ORDER', 'INV', $order->order_number));

        if (count($printOptions) <= 1) {
            if (empty($order->order_invoice) || !File::exists(storage_path('uploads/order_pdf/' . $order->order_invoice))) {
                Utility::order_pdf_generate_store($order->id, 'order_pdf');
                $order->refresh();
            }

            return [
                'url' => !empty($order->order_invoice) ? asset('storage/uploads/order_pdf/' . $order->order_invoice) : '',
                'name' => 'invoice-' . $invoiceNumber . '.pdf',
            ];
        }

        $folderPath = storage_path('uploads/order_pdf');
        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0775, true, true);
        }

	        $invoiceTemplate = Utility::resolveOrderInvoiceTemplate(true);
	        $invoiceLayout = Utility::getInvoiceLayout();
	        $invoiceTerms = app(TermsAndConditionService::class)
	            ->getConfiguredInvoiceTerms(app()->bound('currentTenant') ? 'tenant' : 'landlord');
	        $pdfFile = 'invoice-' . $invoiceNumber . '-whatsapp-' . time() . '.pdf';
        $pdfPath = $folderPath . DIRECTORY_SEPARATOR . $pdfFile;
        $invoices = '';
        $printCount = count($printOptions);
        $printIndex = 0;

        foreach (array_keys($printOptions) as $val) {
            $printIndex++;
            $data = [
                'order' => $order,
                'order_products' => $order->orderProducts,
                'bank_detail' => BankDetail::first(),
                'qrCode' => '',
                'print_option' => $val,
                'for_pdf' => true,
	                'invoice_layout' => $invoiceLayout,
	                'check_discount_allow' => Utility::isDiscountAllowed(),
	                'invoice_terms' => $invoiceTerms,
	            ];

            $invoices .= view($invoiceTemplate, $data)->render();
            if ($printIndex !== $printCount) {
                $invoices .= '<div class="page-break"></div>';
            }
        }

        PDF::loadHTML($invoices)->save($pdfPath);

        return [
            'url' => asset('storage/uploads/order_pdf/' . $pdfFile),
            'name' => 'invoice-' . $invoiceNumber . '.pdf',
        ];
    }


}
