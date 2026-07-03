<?php

namespace App\Http\Controllers;

use App\Models\CustomerPriceHistory;
use App\Models\Entity;
use App\Models\Lead;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStage;
use App\Models\Products;
use App\Models\Utility;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{
    private function writeInvoiceActivity(string $action, string $eventKey, Order $invoice, string $description, array $properties = []): void
    {
        ActivityLogger::writeFor('invoices', $action, $invoice, null, [
            'event_key' => $eventKey,
            'description' => $description,
            'properties' => $properties,
        ]);
    }

    private function invoiceStatusName(?int $status): string
    {
        return match ((int) $status) {
            2 => 'Sent',
            default => 'Pending',
        };
    }

    public function layoutSettings()
    {
        if (!Auth::user()->can('manage company settings')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $selectedLayout = Utility::getInvoiceLayout();
        $sampleOrder = Auth::user()->type == 'Sales'
            ? Order::where('user_id', Auth::id())->latest('id')->first()
            : Order::where('created_by', Auth::user()->creatorId())->latest('id')->first();
        $settingsActivityTimeline = ActivityLogger::activityForModule('settings', 10, [
            'event_key' => 'settings.invoice_updated',
            'subject' => 'settings',
            'subject_id' => (int) Auth::user()->creatorId(),
        ], 'invoice_settings_activities_page');

        return view('invoice.settings', [
            'selected_layout' => $selectedLayout,
            'sample_order_id' => $sampleOrder?->id,
            'settingsActivityTimeline' => $settingsActivityTimeline,
        ]);
    }

    public function layoutSettingsUpdate(Request $request)
    {
        if (!Auth::user()->can('manage company settings')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $request->validate([
            'invoice_layout' => 'required|in:layout_1,layout_2,layout_3,layout_4',
        ]);

        $creatorId = Auth::user()->creatorId();
        $previousLayout = DB::connection()->table('settings')
            ->where('name', 'invoice_layout')
            ->where('created_by', $creatorId)
            ->value('value');

        DB::connection()->table('settings')->updateOrInsert(
            ['name' => 'invoice_layout', 'created_by' => $creatorId],
            ['value' => $request->invoice_layout]
        );

        if ((string) ($previousLayout ?? '') !== (string) $request->invoice_layout) {
            ActivityLogger::writeFor('settings', 'update', 'settings', (int) $creatorId, [
                'event_key' => 'settings.invoice_updated',
                'description' => 'Invoice layout updated.',
                'properties' => [
                    'changes' => ActivityLogger::diff(
                        ['invoice_layout' => $previousLayout],
                        ['invoice_layout' => $request->invoice_layout]
                    ),
                ],
            ]);
        }

        return redirect()->route('setting.invoice.view')->with('success', 'Invoice layout updated successfully.');
    }

    public function index(Request $request)
    {
        // $orders = Order::where('created_by', '=', Auth::user()->creatorId());
        // $list_data['orders'] = $orders->get();

        $list_data['order_status_list'] = OrderStage::pluck('name', 'id');

        if ($request->ajax()) {
            try {
                if(\Auth::user()->type == 'Sales')
                {
                    $orders = Order::with(['getCustomer', 'Orderstatus'])->select('id', 'order_number', 'customer_id', 'date', 'payment_status', 'grand_total', 'status')
                    ->where('user_id',\Auth::user()->id);
                }
                else
                {
                     $orders = Order::with(['getCustomer', 'Orderstatus'])->select('id', 'order_number', 'customer_id', 'date', 'payment_status', 'grand_total', 'status')
                    ->where('created_by', '=', Auth::user()->creatorId());
                }


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

                $data = $orders->orderBy('id', 'desc')->get();
                return DataTables::of($data)
                ->addIndexColumn()
                    ->editColumn('order_number', function ($row) {
                        return str_replace('ORDER-', 'INVOICE-', $row->order_number);
                    })
                    ->addColumn('date', function ($row) {
                        return Utility::getDateFormated($row->date);
                    })
                    // ->addColumn('order_status', function ($row) {
                    //     return $row->Orderstatus ? $row->Orderstatus->name : '-';
                    // })
                    ->addColumn('customer_name', function ($row) {
                        return $row->getCustomer ? $row->getCustomer->name : '-';
                    })
                    ->addColumn('payment_status', function($row){


                        if($row->payment_status == 'unpaid'){

                            return '<h5><button type="button" class="btn  badge bg-danger">'.$row->payment_status.'</button></h5>
                                    <h5><button type="button" class="btn  badge bg-primary" data-size="xl"
                                            data-url="'. route('orders.collect-payment',[$row->id]) .'"
                                            data-ajax-popup="true"
                                            data-bs-original-title="View Purchase Order">Collect Payment</button></h5>';
                        }

                        return '<h5><button type="button" class="btn  badge bg-success">'.$row->payment_status.'</button></h5>';

                    })
                    ->addColumn('action', function ($row) {
                        $url = route('invoices.view', $row->id);
                        return '<a href="' . $url . '" class="text-primary d-inline-block"><i class="ri-eye-fill fs-16"></i></a>

                        <a  href="javascript:void(0);"
                                    class="text-primary d-inline-block"
                                    data-size="lg"
                                    data-url="' . route('orders.invoice.file', $row->id) . '"
                                    data-ajax-popup="true"
                                    data-bs-original-title="Invoice Options"><i class="ri-file-text-line fs-16"></i></a>
                        ';
                    })
                    ->rawColumns(['date', 'order_status', 'action', 'customer_name', 'payment_status'])
                    ->make(true);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }

        return view('invoice.index', $list_data);
    }

    public function create($customer_id = null, $lead_id = null)
    {
        if (Auth::user()->can('create invoice')) {
            $client_type = ['regular' => 'Regular'];

            // $leads = Entity::where('type', 'customer')
            //     ->get()
            //     ->mapWithKeys(function ($lead) {
            //         return [$lead->id => $lead->id . ' - ' . $lead->name];
            //     });

             if(\Auth::user()->type == 'Sales')
            {
                // $lead_assign_all = Lead::where('user_id',\Auth::user()->id)->distinct()->pluck('customer_id');
                $leads = Entity::GetCustomer()->where('user_id',\Auth::user()->id)->selectRaw("id, CONCAT(company_name, ' - ', name) AS display_name")
                ->pluck('display_name', 'id');
            }
            else
            {
                $leads = Entity::where('type', 'customer')->selectRaw("id, CONCAT(company_name, ' - ', name) AS display_name")
                ->pluck('display_name', 'id');
            }



            $transport_list = Entity::IsTransport()->toArray();
            $product_list = Products::get();

            $lead = null;
            if ($lead_id) {
                $lead = Lead::with('product')->find($lead_id);
            }


            return view('invoice.create')->with([
                'leads' => $leads,
                'client_type' => $client_type,
                'transport_list' => $transport_list,
                'lead_id' => $lead_id,
                'product_list' => $product_list,
                'lead' => $lead,
                'new_customer_id' => $customer_id,
                'new_lead_id' => $lead_id
            ]);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function store(Request $request)
    {

        $input = $request->all();


        $save_next_btn = $request->input('save');
        $products = $request->input('products');


         if ($request->gst_no) {

              $request->validate([
                'gst_no' => [
                    'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
                ]
            ], [
                'gst_no' => 'Invalid GST number format.'
            ]);
            $check_exist_gst = Entity::where('gst_no', $request->gst_no)->where('id', '!=', $input['lead_id'])->where('type', 'customer')->exists();

            if ($check_exist_gst) {
               return redirect()->back()
                ->withInput()
                ->withErrors(['gst_no' => 'Invalid GST number']);
            }
        }

        //gst_no update in entity section
        // $leadId = Lead::where('id', $input['lead_id'])->first();

        $customerId = Entity::where('id', $input['lead_id'])->first();
        if ($customerId  || $request['gst_no'] || $request['adhar_no'] || $input['udhaym_no'] || $request['company_name']) {
            $customer = Entity::isCustomer()->where('id', $customerId['id'])->first();
            if (isset($request->gst_no)) {
                $isAvailGST = Entity::where('id', '!=', $customer['id'])->where('gst_no', $input['gst_no'])->first();
                if ($isAvailGST) {
                    // return redirect()->back()->with(['error'=>'GST No must be unique.']);
                }
            }

            if ($customer) {
                $updateData = [];

                if (isset($input['gst_no'])) {
                    $updateData['gst_no'] = $input['gst_no'];
                }
                if (isset($input['adhar_no'])) {
                    $updateData['company_adhar_no'] = $input['adhar_no'];
                }
                if (isset($input['udhaym_no'])) {
                    $updateData['company_udhyam_no'] = $input['udhaym_no'];
                }
                if (isset($input['company_name'])) {
                    $updateData['company_name'] = $input['company_name'];
                }

                if (!empty($updateData)) {
                    $customer->update($updateData);
                }
            }
        }


        $qt['customer_type'] = $input['customer_type'];
        $qt['lead_id']      = $request->new_lead_id ?? null; //$input['lead_id'];
        $qt['date'] = $input['date'];
        $qt['transport_id'] = $input['transport_id'];
        $qt['gst'] = $input['tax']; //gst value not percentage
        $qt['grand_total'] = (float) $input['total_amt'];

        if ($input['customer_type'] == 'regular') {
            $qt['is_advance_payment'] = 0;
            $qt['payment_after_days'] = $input['payment_after_days'];
        } else {
            $qt['is_advance_payment'] = 1;
            $qt['advance_payment'] = $input['advance_payment'];
        }

        $qt['created_by'] = Auth::user()->creatorId();
        $qt['where_from'] = 'Customer';
        $qt['customer_id'] = $customerId['id'];
        $qt['tax_detail_json'] = $input['tax_json_data'];
        $qt['total_tax_sum'] = $input['tax_rate_sum']; // not used bcz product based gst add
        $qt['user_id'] = Auth::user()->id; //$leadId->user_id;

        $quote_id = Order::create($qt);

        foreach ($products['id'] as $index => $productId) {

            $product_id = $products['id'][$index];
            $qty = (float) $products['qty'][$index];
            $mrp = (float) $products['mrp'][$index];
            $units = (float) $products['units'][$index];
            $dealer_price = (float) $products['price'][$index];
            $discount = (float) ($products['discount'][$index] ?? 0);
            $product_total = (float) $products['product_total'][$index];
            $short_note = $products['short_notes'][$index];
            $product_gst = (float) ($products['gst_value'][$index] ?? 0);
            // QuoteProducts::create([
            //     'quote_id' => $quote_id['id'],
            //     'product_id' => $product_id,
            //     'qty' => $qty,
            //     'unit_id' => $units,
            //     'mrp' => $mrp,
            //     'discount' => $discount,
            //     'price' => $dealer_price,
            //     'total' => $product_total,
            //     'created_by' => \Auth::user()->creatorId(),
            //     'short_notes' => $short_note,
            // ]);
            OrderProduct::create([
                'order_id' => $quote_id['id'],
                'product_id' => $product_id,
                'qty' => $qty,
                'unit_id' => $units,
                'mrp' => $mrp,
                'discount' => $discount,
                'price' => $dealer_price,
                'total' => $product_total,
                'created_by' => Auth::user()->creatorId(),
                'short_notes' => $short_note,
                'tax'=> $product_gst ?? 0
            ]);

            //customer price history
            $check_cust_price_avl = CustomerPriceHistory::where('customer_id', $customerId['id'])->where('product_id', $product_id)->first();
            if ($check_cust_price_avl) {
                $check_cust_price_avl->update(['price' => $dealer_price, 'discount' => $discount]);
            } else {
                $cust_prc_his['customer_id'] = $customerId['id'];
                $cust_prc_his['product_id'] = $product_id;
                $cust_prc_his['price'] = $dealer_price;
                $cust_prc_his['discount'] = $discount;
                CustomerPriceHistory::create($cust_prc_his);
            }
        }

        if (isset($save_next_btn) && $save_next_btn === "save_send") {
            $quote_id->update(['status' => 2]);
            // $file = Utility::quote_pdf_generate_store($quote_id['id'], 'quote_pdf');
            // if ($file) {
            //     return response()->download($file);
            // }
        } else {
            $quote_id->update(['status' => 1]);
        }

        $quote_id->refresh();
        $this->writeInvoiceActivity(
            'create',
            'invoice.created',
            $quote_id,
            (int) $quote_id->status === 2 ? 'Invoice created and marked as sent.' : 'Invoice created.',
            [
                'customer_id' => $quote_id->customer_id,
                'status' => $quote_id->status,
                'status_name' => $this->invoiceStatusName((int) $quote_id->status),
                'grand_total' => $quote_id->grand_total,
                'product_count' => count($products['id'] ?? []),
            ]
        );

        return redirect()->route('invoices.index')->with(['success' => 'Invoice has been added successfully']);
    }

    public function view(Request $request, $id)
    {
        $data['order'] = Order::with('Orderstatus')->where('id', $id)->first();
        $data['order_status_list'] = OrderStage::pluck('name', 'id');
        $data['activityTimeline'] = ActivityLogger::activityForRecord($data['order'], null, 12, 'invoice_activities_page');
        return view('invoice.view', $data);
    }
}
