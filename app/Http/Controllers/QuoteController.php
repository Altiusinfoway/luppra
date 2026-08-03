<?php

namespace App\Http\Controllers;

use Spatie\Permission\PermissionRegistrar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use ZipArchive;
use PDF;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Utility;
use App\Models\Quotes;
use App\Models\Lead;
use App\Models\Role;
use App\Models\Entity;
use App\Models\QuoteProducts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Address;
use App\Models\Payments;
use League\CommonMark\Extension\SmartPunct\Quote;
use App\Models\Products;
use App\Models\CustomerPhone;
use App\Models\UserLead;
use App\Models\LeadStage;
use App\Models\BankDetail;
use App\Models\OrderPayment;
use App\Models\OrderActivity;
use App\Models\CustomerPriceHistory;
use App\Models\MarketplaceListing;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\TermsAndConditionService;
use Illuminate\Support\Facades\Schema;


class QuoteController extends Controller
{
    private function resolveMarketplaceListing(?string $listingId, int $productId): ?MarketplaceListing
    {
        if (empty($listingId)) {
            return null;
        }

        if (!Schema::hasTable('marketplace_listings')) {
            return null;
        }

        return MarketplaceListing::where('id', (int) $listingId)
            ->where('product_id', $productId)
            ->firstOrFail();
    }

    private function writeQuoteActivity(string $action, string $eventKey, Quotes $quote, string $description, array $properties = []): void
    {
        ActivityLogger::writeFor('quotes', $action, $quote, null, [
            'event_key' => $eventKey,
            'description' => $description,
            'properties' => $properties,
        ]);
    }

    private function quoteStatusName(?int $status): string
    {
        return match ((int) $status) {
            2 => 'Sent',
            3 => 'Finalized',
            default => 'Pending',
        };
    }

    private function quoteActivitySnapshot(Quotes $quote): array
    {
        $quote->refresh();

        return [
            'date' => (string) ($quote->date ?? ''),
            'transport_id' => (string) ($quote->transport_id ?? ''),
            'gst' => (string) ($quote->gst ?? ''),
            'grand_total' => (string) ($quote->grand_total ?? ''),
            'customer_type' => (string) ($quote->customer_type ?? ''),
            'is_advance_payment' => (string) ($quote->is_advance_payment ?? ''),
            'payment_after_days' => (string) ($quote->payment_after_days ?? ''),
            'advance_payment' => (string) ($quote->advance_payment ?? ''),
            'status' => (string) ($quote->status ?? ''),
            'status_name' => $this->quoteStatusName((int) ($quote->status ?? 0)),
            'is_final' => (string) ($quote->is_final ?? ''),
            'notes' => (string) ($quote->notes ?? ''),
            'product_count' => (string) QuoteProducts::where('quote_id', $quote->id)->count(),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $lead_id = null)
    {

        if (\Auth::user()->can('manage quote')) {
            if(\Auth::user()->type == 'Sales')
            {
                $data['lead_list'] = Lead::where('user_id',\Auth::user()->id)->get();
            }
            else
            {
                $data['lead_list'] = Lead::all();
            }

            $data['lead_id'] = $lead_id;
            if ($request->ajax()) {
                try {
                    if (\Auth::user()->type != 'company')
                    {
                        //  $lead_id_all = Lead::where('user_id',\Auth::user()->id)->pluck('id');
                        $query = Quotes::where('user_id', \Auth::user()->id)->where('created_by', '=', \Auth::user()->creatorId())->select('id', 'lead_id', 'date', 'grand_total', 'advance_payment', 'status', 'code', 'customer_id');
                    } else {
                        $query = Quotes::where('created_by', '=', \Auth::user()->creatorId())->select('id', 'lead_id', 'date', 'grand_total', 'advance_payment', 'status', 'code', 'customer_id');
                    }


                    if ($request->lead_filter) {
                        $query->where('lead_id', $request->lead_filter);
                    }

                    if ($request->status_filter) {
                        $query->where('status', $request->status_filter);
                    }

                    if (!empty($request->start_date_filter)) {
                        $query->whereDate('date', '>=', $request->start_date_filter);
                    }

                    if (!empty($request->end_date_filter)) {
                        $query->whereDate('date', '<=', $request->end_date_filter);
                    }

                    $device = \App\Models\Device::where('user_id', \Auth::user()->id)->first();
                    $data = $query->orderBy('id', 'desc')->get();

                    return DataTables::of($data)
                        ->addIndexColumn()
                        ->addColumn('lead_id', function ($row) {
                            // return $row->customer->name ?? '';
                            return '<h5>' . $row->customer->name ?? '' . '</h5>';
                        })
                        ->addColumn('status', function ($row) {
                            if ($row->status == 1 || $row->status == 0) {
                                return '<span class="badge bg-danger me-1">Pending</span>';
                            } elseif ($row->status == 2) {
                                return '<span class="badge bg-primary me-1">Send</span>';
                            } elseif ($row->status == 3) {
                                return '<span class="badge bg-success me-1">Final</span>';
                            } else {
                                return '';
                            }
                        })
                        ->addColumn('cust_phone', function ($row) {
                            $get_phone = CustomerPhone::where('customer_id', $row->customer_id)->where('is_primary', 1)->first();
                            $get_whatsapp = CustomerPhone::where('customer_id', $row->customer_id)->where('is_whatsapp', 1)->first();
                            $div_wp = $div_phone = '';
                            if ($get_phone) {
                                // return $get_phone->phone ?? '';
                                $div_phone = '

                                <li class="list-inline-item avatar-xs">
                                <a href="tel:' . ($get_phone?->phone ?? '') . '" class="avatar-title bg-danger-subtle text-danger fs-16 rounded">
                                <i class="ri-phone-line"></i>
                                </a>
                                </li>';
                            }

                            if ($get_whatsapp) {
                                $div_wp = '<li class="list-inline-item avatar-xs">
                                            <a href="javascript:void(0);" class="avatar-title bg-success-subtle text-success fs-16 rounded open-whatsapp-modal" data-customer_id="' . $row->id . '"
                                                data-phone="' . $get_whatsapp->phone . '">
                                                <i class="ri-whatsapp-line"></i>
                                            </a>
                                        </li>';
                            }

                            return '<ul class="list-inline mb-0 text-center">' . $div_phone . $div_wp . '</ul>';
                        })

                        ->addColumn('date', function ($row) {
                            return Utility::getDateFormated($row->date);
                        })
                        ->addColumn('quotation_approve', function ($row) {
                            $aprUrl = route("quotes.edit_status", $row->id);
                            $isChecked = ($row->status == 3) ? 'checked disabled' : '';

                            return '
                            <div class="d-flex justify-content-center">
                            <div class="form-check form-switch">
                                        <input
                                            class="form-check-input form-switch-md"
                                            type="checkbox"
                                            role="switch"
                                            id="flexSwitchCheckDefault_' . $row->id . '"
                                            data-url="' . $aprUrl . '"
                                            onchange="openStatusEditPage(this)"
                                            ' . $isChecked . '>
                                    </div>
                                    </div>
                                    ';
                        })

                        ->addColumn('action', function ($quote) use ($device) {
                            $whatsapp_msg = CustomerPhone::where('customer_id', $quote->customer_id)
                                ->where('is_whatsapp', 1)
                                ->first();

                            $actions = '';
                            if (auth()->user()->can('edit quote') || auth()->user()->can('delete quote')) {
                                $actions .= '<div class="dropdown d-inline-block">
                                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-more-fill align-middle"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">';

                                if (auth()->user()->can('edit quote')) {
                                    $actions .= '<li>
                                     <a href="' . route('quotes.edit', $quote->id) . '" class="dropdown-item edit-item-btn">
                                        <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                    </a>

                                    </li>';
                                }

                                if (auth()->user()->can('delete quote')) {
                                    if($quote->status != 3)
                                    {
                                        $actions .= '<li>
                                            <a class="dropdown-item remove-item-btn"
                                            data-delete-popup="true"
                                            data-bs-original-title="You are about to delete a Quotes ?"
                                            data-bs-original-description="Deleting your Quotes will remove all of your information from our database."
                                            data-url="' . route('quotes.delete', [$quote->id]) . '"
                                            data-method="DELETE"
                                            data-cb="afterDelete"
                                            href="javascript:void(0)">
                                            <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                            </a>
                                        </li>';
                                    }
                                }

                                $actions .= '<li>
                                     <a href="javascript:void(0);"
                                    class="dropdown-item"
                                    data-size="lg"
                                    data-url="' .  route('quotes.invoice.file', $quote->id) . '"
                                    data-ajax-popup="true"
                                    data-bs-original-title="Quotation PDF">
                                        <i class="ri-file-pdf-fill align-bottom me-2 text-muted"></i> Pdf
                                    </a>
                                </li>';

                                if ($whatsapp_msg && $device) {
                                    $actions .= '<li>
                                    <a href="javascript:void(0)"
                                    class="dropdown-item open-whatsapp-modal"
                                    data-customer_id="' . $quote->customer_id . '"
                                    data-phone="' . $whatsapp_msg->phone . '">
                                    <i class="ri-whatsapp-fill align-bottom me-2 text-muted"></i> WhatsApp
                                    </a>
                                </li>';
                                }

                                $actions .= '</ul></div>';
                            }
                            return $actions;
                        })
                        ->rawColumns(['lead_id', 'quotation_approve', 'status', 'cust_phone', 'action'])
                        ->setRowClass('main-row')
                        ->make(true);
                } catch (\Exception $e) {

                    return response()->json([
                        'error' => 'Server Error: ' . $e->getMessage()
                    ], 500);
                }
            }



            return view('quotes.index', $data);
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($customer_id = null, $lead_id = null)
    {
        if (\Auth::user()->can('create quote')) {
            $client_type = ['regular' => 'Regular']; // 'debitClient' => 'Debit Client'

            /* lead dropdown proper working dk
            $leads = Lead::leftjoin('lead_products', 'leads.id', '=', 'lead_products.lead_id')
                ->select('leads.name', 'leads.id','leads.customer_id');

            if (\Auth::user()->type != 'company') {
                $leads = $leads->where('leads.user_id', \Auth::user()->id);
            }
            $leads = $leads->get()->pluck('name', 'id');
             */

            //temp code must remove
            //  $leads = $leads->get()->mapWithKeys(function ($lead) {
            //     return [$lead->id => $lead->name . ' - ' . ($lead->customer_id ?? 'N/A')];
            // });

            //----------------


            //customer-list

            if(\Auth::user()->type == 'Sales')
            {
                // $lead_assign_all = Lead::where('user_id',\Auth::user()->id)->distinct()->pluck('customer_id');
                // $leads = Entity::where('type', 'customer')->whereIn('id',$lead_assign_all)->pluck('name', 'id');

               $leads = Entity::GetCustomer()
                ->where('user_id', auth()->id())
                ->selectRaw("id, CONCAT(company_name, ' - ', name) AS display_name")
                ->pluck('display_name', 'id');
            }
            else
            {
                $leads = Entity::where('type', 'customer')->selectRaw("id, CONCAT(company_name, ' - ', name) AS display_name")->pluck('display_name', 'id');
            }


            //temp must remove
            // $leads = Entity::where('type', 'customer')
            //     ->get()
            //     ->mapWithKeys(function ($lead) {
            //         return [$lead->id => $lead->id . ' - ' . $lead->name];
            //     });

            $transport_list = Entity::IsTransport()->toArray();
            $product_list = Products::with('getGstSlabMaster')->get();
            if (Schema::hasTable('marketplace_listings')) {
                $product_list->load('marketplaceListings');
            }

            $lead = null;
            if ($lead_id) {
                $lead = Lead::with('product')->find($lead_id);
            }

            if(!empty($customer_id))
            {
                $customer_rcd = Entity::where('id',$customer_id)->first();
            }


            return view('quotes.create')->with([
                'leads' => $leads,
                'client_type' => $client_type,
                'transport_list' => $transport_list,
                'lead_id' => $lead_id,
                'product_list' => $product_list,
                'lead' => $lead,
                'new_customer_id' => $customer_id,
                'new_lead_id' => $lead_id,
                'lead_billing_id' => $customer_rcd?->billing_address_id ?? null, // customer->billing-adr
                'lead_shipping_id' => $customer_rcd?->shipping_address_id ?? null //customer->shipping-adr
            ]);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $input = $request->all();

        $save_next_btn = $request->input('save');
        $products = $request->input('products', []);

        $request->validate([
            'lead_id' => 'required|exists:entities,id',
            'customer_type' => 'required|in:regular,debitClient',
            'date' => 'required|date',
            'transport_id' => 'nullable|exists:entities,id',
            'company_name' => 'nullable|string|max:120',
            'adhar_no' => 'nullable',
            'udhaym_no' => 'nullable',
            'payment_after_days' => 'required_if:customer_type,regular|nullable|integer|min:0|max:365',
            'advance_payment' => 'required_if:customer_type,debitClient|nullable|numeric|min:0',
            'products' => 'required|array',
            'products.id' => 'required|array|min:1',
            'products.id.*' => 'required|exists:products,id',
            'products.qty' => 'required|array',
            'products.qty.*' => 'required|numeric|gt:0',
            'products.price' => 'required|array',
            'products.price.*' => 'required|numeric|min:0',
            'products.discount' => 'nullable|array',
            'products.discount.*' => 'nullable|numeric|min:0|max:100',
            'products.gst_value' => 'nullable|array',
            'products.gst_value.*' => 'nullable|numeric|min:0',
            'products.product_total' => 'required|array',
            'products.product_total.*' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total_amt' => 'required|numeric|gt:0',
        ], [
            'lead_id.required' => 'Please select a customer.',
            'products.id.min' => 'Please add at least one product.',
            'payment_after_days.required_if' => 'Payment After Days is required for regular customer.',
            'advance_payment.required_if' => 'Advance Payment is required for debit client.',
        ]);

        if ($request->customer_type !== 'regular' && (float) $request->advance_payment > (float) $request->total_amt) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['advance_payment' => 'Advance Payment cannot be greater than Total Amount.']);
        }

        // \Log::info('quote create  ',[\Auth::user()->id]);

        //gst check
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

        $customerId = Entity::where('id', $request->input('lead_id'))->where('type', 'customer')->first();
        if (!$customerId) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['lead_id' => 'Selected customer was not found.']);
        }

        if ($request->filled('gst_no') || $request->filled('adhar_no') || $request->filled('udhaym_no') || $request->filled('company_name')) {
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

        if($request->has('new_lead_id') && $request->new_lead_id != null){

            $lead_data = Lead::find($request->new_lead_id);
        }

        $qt['customer_type'] = $input['customer_type'];
        $qt['lead_id']      = $request->new_lead_id ?? null; //$input['lead_id'];
        $qt['date'] = $input['date'];
        $qt['transport_id'] = $request->input('transport_id') ?: null;
        $qt['gst'] = (float) $request->input('tax', 0); //gst value not percentage
        $qt['grand_total'] = (float) $input['total_amt'];

        if ($input['customer_type'] == 'regular') {
            $qt['is_advance_payment'] = 0;
            $qt['payment_after_days'] = $request->input('payment_after_days');
        } else {
            $qt['is_advance_payment'] = 1;
            $qt['advance_payment'] = (float) $request->input('advance_payment', 0);
        }

        $qt['created_by'] = \Auth::user()->creatorId();
        // \Log::info('quote create created_by ',[\Auth::user()->creatorId()]);

        $qt['where_from'] = $request->filled('new_lead_id') ? 'Lead' : 'Customer';
        $qt['customer_id'] = $customerId['id'];
        $qt['tax_detail_json'] = $request->input('tax_json_data', '{}');
        $qt['total_tax_sum'] = (float) $request->input('tax_rate_sum', 0); // not used bcz product based gst add
        $qt['user_id'] = isset($lead_data) ? $lead_data->user_id : \Auth::user()->id;  //$leadId->user_id;

        $quote_id = Quotes::create($qt);

        foreach ($products['id'] as $index => $productId) {

            $product_id = $products['id'][$index];
            $marketplaceListing = $this->resolveMarketplaceListing($products['listing_id'][$index] ?? null, (int) $product_id);
            $qty = (float) $products['qty'][$index];
            $product = Products::find($product_id);
            $mrp = (float) ($products['mrp'][$index] ?? $marketplaceListing?->mrp ?? $product?->price ?? 0);
            $units = $products['units'][$index] ?? null;
            $dealer_price = (float) ($products['price'][$index] ?? $marketplaceListing?->selling_price ?? 0);
            $discount = (float) ($products['discount'][$index] ?? 0);
            $product_total = (float) $products['product_total'][$index];
            $short_note = $products['short_notes'][$index] ?? null;
            $product_gst = (float) ($products['gst_value'][$index] ?? 0);
            QuoteProducts::create([
                'quote_id' => $quote_id['id'],
                'product_id' => $product_id,
                'marketplace_listing_id' => $marketplaceListing?->id,
                'qty' => $qty,
                'unit_id' => $units,
                'mrp' => $mrp,
                'discount' => $discount,
                'price' => $dealer_price,
                'total' => $product_total,
                'created_by' => \Auth::user()->creatorId(),
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
        $this->writeQuoteActivity(
            'create',
            'quote.created',
            $quote_id,
            (int) $quote_id->status === 2 ? 'Quotation created and marked as sent.' : 'Quotation created.',
            [
                'customer_id' => $quote_id->customer_id,
                'lead_id' => $quote_id->lead_id,
                'status' => $quote_id->status,
                'status_name' => $this->quoteStatusName((int) $quote_id->status),
                'grand_total' => $quote_id->grand_total,
                'product_count' => count($products['id'] ?? []),
            ]
        );

        return redirect()->route('quotes.index')->with(['success' => 'Quote has been added successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $quote_id =  Quotes::find($id);
        $activityTimeline = ActivityLogger::activityForRecord($quote_id, null, 12, 'quote_activities_page');
        $client_type = ['regular' => 'Regular', 'debitClient' => 'Debit Client'];
        // $leads = Lead::IsNotConverted()->get()->pluck('name', 'id');

        //customer
        $leads = Entity::where('type', 'customer') ->selectRaw("id, CONCAT(company_name, ' - ', name) AS display_name")
                ->pluck('display_name', 'id');

        $transport_list = Entity::IsTransport()->toArray();

        $product_list = Products::with('getGstSlabMaster')->InHouse()->get();
        if (Schema::hasTable('marketplace_listings')) {
            $product_list->load('marketplaceListings');
        }
        $cust_gst = Entity::where('id', $quote_id->customer_id)->first();

        return view('quotes.edit')->with(['leads' => $leads, 'client_type' => $client_type, 'transport_list' => $transport_list, 'quote_id' => $quote_id, 'product_list' => $product_list, 'cust_gst' => $cust_gst->gst_no ?? '', 'activityTimeline' => $activityTimeline]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $input = $request->all();
        $save_next_btn = $request->input('save');

        $request->validate([
            'date' => 'required|date',
            'transport_id' => 'nullable|exists:entities,id',
            'company_name' => 'nullable|string|max:120',
            'adhar_no' => 'nullable',
            'udhaym_no' => 'nullable',
            'payment_after_days' => 'nullable|integer|min:0|max:365',
            'advance_payment' => 'nullable|numeric|min:0',
            'products' => 'required|array',
            'products.id' => 'required|array|min:1',
            'products.id.*' => 'required|exists:products,id',
            'products.qty' => 'required|array',
            'products.qty.*' => 'required|numeric|gt:0',
            'products.price' => 'required|array',
            'products.price.*' => 'required|numeric|min:0',
            'products.discount' => 'nullable|array',
            'products.discount.*' => 'nullable|numeric|min:0|max:100',
            'products.gst_value' => 'nullable|array',
            'products.gst_value.*' => 'nullable|numeric|min:0',
            'products.product_total' => 'required|array',
            'products.product_total.*' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total_amt' => 'required|numeric|gt:0',
        ], [
            'products.id.min' => 'Please add at least one product.',
        ]);

        $quote_id =  Quotes::findOrFail($id);
        $quoteBefore = $this->quoteActivitySnapshot($quote_id);
        // \Log::info('quote edit ',[\Auth::user()->id]);

        //gst check
        if ($request->gst_no) {

              $request->validate([
                'gst_no' => [
                    'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
                ]
            ], [
                'gst_no' => 'Invalid GST number format.'
            ]);
            $check_exist_gst = Entity::where('gst_no', $request->gst_no)->where('id', '!=', $quote_id->customer_id)->where('type', 'customer')->exists();

            if ($check_exist_gst) {
                return redirect()->back()->with(['error' => 'This GST number already exists for another customer.']);
            }
        }

        $products = $request->input('products', []);

        $qt['date'] = $input['date'];
        $qt['transport_id'] = $request->input('transport_id') ?: null;
        $qt['gst'] = (float) $request->input('tax', 0);//gst value not percentage
        $qt['grand_total'] = (float) $input['total_amt'];

        //gst_no update in entity section
        $customerId = Entity::where('id', $quote_id->customer_id)->first();
        if (!$customerId) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['lead_id' => 'Quote customer was not found.']);
        }

        if (
            $customerId
            && !empty($customerId)
            && (
                !empty($request->gst_no)
                || !empty($request->adhar_no)
                || !empty($request->udhaym_no) || !empty($request->company_name)
            )
        ) {
            $customer = Entity::isCustomer()->where('id', $customerId['id'])->first();
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

        if ($quote_id['customer_type'] == 'regular') {
            if (!$request->filled('payment_after_days')) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['payment_after_days' => 'Payment After Days is required for regular customer.']);
            }

            $qt['is_advance_payment'] = 0;
            $qt['payment_after_days'] = $request->input('payment_after_days');
            $qt['advance_payment'] = null;
        } else {
            if (!$request->filled('advance_payment')) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['advance_payment' => 'Advance Payment is required for debit client.']);
            }

            if ((float) $request->input('advance_payment', 0) > (float) $request->input('total_amt', 0)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['advance_payment' => 'Advance Payment cannot be greater than Total Amount.']);
            }

            $qt['is_advance_payment'] = 1;
            $qt['advance_payment'] = (float) $request->input('advance_payment', 0);
            $qt['payment_after_days'] = null;
        }

        $qt['created_by'] = \Auth::user()->creatorId();
        // \Log::info('quote edit created_by ',[\Auth::user()->creatorId()]);

        // $qt['transport_id'] = $input['transport_id'];
        // $qt['date'] = $input['date'];
        $qt['tax_detail_json'] = $request->input('tax_json_data', '{}');
        $qt['total_tax_sum'] = (float) $request->input('tax_rate_sum', 0); // not used bcz product based gst add
        $qt['gst'] = (float) $request->input('tax', 0);
        $qt['grand_total'] = (float) $input['total_amt'];

        $quote_id->update($qt);

        $submittedQuoteIds = [];
        foreach ($products['id'] as $index => $productId) {
            $prod_prc = Products::where('id', $products['id'][$index])->first();
            $marketplaceListing = $this->resolveMarketplaceListing($products['listing_id'][$index] ?? null, (int) $products['id'][$index]);
            $product_id    = isset($products['id'][$index]) ? (int) $products['id'][$index] : 0;
            $qty           = isset($products['qty'][$index]) ? (int) $products['qty'][$index] : 0;
            $mrp           = (float) ($products['mrp'][$index] ?? $marketplaceListing?->mrp ?? ($prod_prc['price'] ?? 0));
            $units         = isset($products['units'][$index]) && $products['units'][$index] != 0  ? (int)$products['units'][$index] : null;
            $dealer_price  = isset($products['price'][$index]) ? (float) $products['price'][$index] : (float) ($marketplaceListing?->selling_price ?? 0);
            $discount      = isset($products['discount'][$index]) ? (float) $products['discount'][$index] : 0;
            $product_total = isset($products['product_total'][$index]) ? (float) $products['product_total'][$index] : 0;
            $short_note    = isset($products['short_notes'][$index]) ? $products['short_notes'][$index] : null;
            $product_gst = (float) ($products['gst_value'][$index] ?? 0);

            $quoteProductId = $products['quote_ids'][$index] ?? null;
            $quoteProduct = !empty($quoteProductId)
                ? QuoteProducts::where('quote_id', $quote_id['id'])->where('id', (int) $quoteProductId)->first()
                : null;

            if ($quoteProduct) {

                $quoteProduct->update([
                    'quote_id' => $quote_id['id'],
                    'product_id' => $product_id,
                    'marketplace_listing_id' => $marketplaceListing?->id,
                    'qty' => $qty,
                    'unit_id' => $units,
                    'mrp' => $mrp,
                    'discount' => $discount,
                    'price' => $dealer_price,
                    'total' => $product_total,
                    'created_by' => \Auth::user()->creatorId(),
                    'short_notes' => $short_note,
                    'tax'=> $product_gst ?? 0

                ]);
                $submittedQuoteIds[] = $quoteProduct->id;
            } else {
                $createdQuoteProduct = QuoteProducts::create([
                    'quote_id' => $quote_id['id'],
                    'product_id' => $product_id,
                    'marketplace_listing_id' => $marketplaceListing?->id,
                    'qty' => $qty,
                    'unit_id' => $units,
                    'mrp' => $mrp,
                    'discount' => $discount,
                    'price' => $dealer_price,
                    'total' => $product_total,
                    'created_by' => \Auth::user()->creatorId(),
                    'short_notes' => $short_note,
                    'tax'=> $product_gst ?? 0

                ]);
                $submittedQuoteIds[] = $createdQuoteProduct->id;
            }

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

        if (!empty($submittedQuoteIds)) {
            QuoteProducts::where('quote_id', $quote_id->id)
                ->whereNotIn('id', $submittedQuoteIds)
                ->delete();
        }

        $quoteAfter = $this->quoteActivitySnapshot($quote_id);
        $quoteChanges = ActivityLogger::diff($quoteBefore, $quoteAfter);
        if (!empty($quoteChanges)) {
            $this->writeQuoteActivity('update', 'quote.updated', $quote_id, 'Quotation updated.', [
                'changes' => $quoteChanges,
                'customer_id' => $quote_id->customer_id,
                'lead_id' => $quote_id->lead_id,
            ]);
        }

        // if (isset($save_next_btn) && $save_next_btn == "save_send") {
        //     $file = Utility::quote_pdf_generate_store($id, 'quote_pdf');
        //     if ($file) {
        //         return response()->download($file);
        //     }
        // }

        return redirect()->route('quotes.index')->with(['success' => 'Quote has been updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        $quote_product_all = QuoteProducts::where('quote_id', $id)->get();
        if (!empty($quote_product_all)) {
            foreach ($quote_product_all as $q_product) {
                $q_product->delete();
            }
        }
        $q_id = Quotes::find($id);

        $folder_path = storage_path('uploads/quote_pdf');
        $image_path = $folder_path . '/' . $q_id['quote_invoice'];
        if (File::exists($image_path)) {
            File::delete($image_path);
        }

        $q_id->delete();

        return response()->json([
            'success' => 'Quotes has been deleted successfully.'
        ], 200);

        return redirect()->route('quotes.index');
    }

    public function pdf(Request $request, $id)
    {
        $file = Utility::quote_pdf_generate_store($id, 'quote_pdf');

        if ($file) {
            return response()->download($file);
        }
    }

    public function edit_status(Request $request, $id)
    {
        $data['quote_id'] = $id;
        $quoteId = Quotes::where('id', $id)->first();
        $data['customer_data'] = Entity::where('id', $quoteId->customer_id)->first();
        $data['quote_data'] = $quoteId;
        $address_data = [];

        $shipping_address_id = $data['customer_data']->shipping_address_id ?? null;
        $billing_address_id = $data['customer_data']->billing_address_id ?? null;



        $address_data[] = $billing_address_id
            ? Address::find($billing_address_id)
            : (object)[
                'country' => '',
                'state' => '',
                'city' => '',
                'zipcode' => '',
                'address_line_1' => '',
                'address_line_2' => '',
            ];

        $address_data[] = $shipping_address_id
            ? Address::find($shipping_address_id)
            : (object)[
                'country' => '',
                'state' => '',
                'city' => '',
                'zipcode' => '',
                'address_line_1' => '',
                'address_line_2' => '',
            ];

        $data['address_list'] = $address_data;
        $data['bank_detail_list'] = BankDetail::all();
        $data['account_transaction_type'] = Payments::getAccountTransactionTypes();
        $data['customer_list'] = Entity::GetCustomer()->pluck('name', 'id');
        $data['paymentMethods'] = Payments::getPaymentMethods();




        return view('quotes.edit_status', $data);
    }

    // Quote Convert into order. ( Quote is final and deal is done. )
    public function status_update(Request $request, $id)
    {
        $validatedData = $request->validate([
            // 'name' => 'required',
            // 'email' => 'required|email',
            // 'phone' => 'required|digits:10',
            // 'gst_no' => [
            //     'required',
            //     'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'
            // ],
            // 'avatar'=>'required|file|mimes:jpg,jpeg,png|max:2048',
            // 'address_line_1'=>'required',
            // 'address_line_2'=>'required',
            // 'city'=>'required',
            // 'zipcode'=>'required',
            // 'state'=>'required',
            // 'country'=>'required',

        ]);

        $input = $request->all();

        $quote_id = Quotes::findOrFail($id);
        $quoteBeforeStatus = (int) $quote_id->status;
        $quoteBeforeIsFinal = (int) $quote_id->is_final;

        // \Log::info('quote final ',[\Auth::user()->id]);

        $lead_id = Lead::where('id', $quote_id['lead_id'])->first();

        $customer_rcd = Entity::where('id', $quote_id->customer_id)->first();

        if ($request->email) {
            $customer_rcd->update(['email' => $request->email]);
        }
        if ($request->gst_no) {
            $customer_rcd->update(['gst_no' => $request->gst_no]);
        }

        // if($quote_id->company_id == null)
        // {
        //      return response()->json([
        //         'error'=>'yes',
        //         'message_success' => 'Company not found. ',
        //     ]);
        // }
        // else
        // {
        //     $company_id = Company::where('id',$quote_id->company_id)->first();
        //     if(!$company_id)
        //     {
        //         return response()->json([
        //         'error'=>'yes',
        //         'message_success' => 'Company not found. ',
        //         ]);
        //     }
        // }

        try {

            DB::beginTransaction();

            $isSameAsAbove = $request->has('is_check') && $request->is_check == 1;
            $addressInputs = $request->only([
                'address_id',
                'country',
                'state',
                'city',
                'zipcode',
                'address_line_1',
                'address_line_2'
            ]);

            $address_ids = [];


            if (isset($request['is_check']) && $request['is_check'] == 1) {
                // billing & shipping adr same
                foreach ($addressInputs['country'] as $i => $country_id) {
                    if ($isSameAsAbove && $i > 0) {
                        break;
                    }

                    $data = [
                        'country'        => $country_id,
                        'state'          => $addressInputs['state'][$i],
                        'city'           => $addressInputs['city'][$i],
                        'zipcode'        => $addressInputs['zipcode'][$i],
                        'address_line_1' => $addressInputs['address_line_1'][$i],
                        'address_line_2' => $addressInputs['address_line_2'][$i],
                    ];

                    if (!empty($addressInputs['address_id'][$i])) {
                        Address::where('id', $addressInputs['address_id'][$i])->update($data);
                        $address_ids[] = $addressInputs['address_id'][$i];
                    } else {
                        $new_bill = Address::create($data);
                        $new_ship = Address::create($data);
                        $address_ids[] = $new_bill->id;
                        $address_ids[] = $new_ship->id;


                        $customer_rcd->update([
                            'billing_address_id'  => $new_bill->id,
                            'shipping_address_id' => $new_ship->id,
                        ]);
                    }
                }
            } else {
                //shipping & billing address diff
                foreach ($addressInputs['country'] as $i => $country_id) {
                    $data = [
                        'country'        => $country_id,
                        'state'          => $addressInputs['state'][$i],
                        'city'           => $addressInputs['city'][$i],
                        'zipcode'        => $addressInputs['zipcode'][$i],
                        'address_line_1' => $addressInputs['address_line_1'][$i],
                        'address_line_2' => $addressInputs['address_line_2'][$i],
                    ];

                    if (!empty($addressInputs['address_id'][$i])) {
                        Address::where('id', $addressInputs['address_id'][$i])->update($data);
                        $address_ids[] = $addressInputs['address_id'][$i];
                    } else {
                        $new = Address::create($data);
                        $address_ids[] = $new->id;

                        if ($i == 0) {
                            $customer_rcd->update([
                                'shipping_address_id' => $new->id,
                            ]);
                        } else {
                            $customer_rcd->update([
                                'billing_address_id'  => $new->id,
                            ]);
                        }
                    }
                }
            }

            // $image = "";
            // if($request->hasFile('avatar'))
            // {
            //     $filenameWithExt = $request->file('avatar')->getClientOriginalName();
            //     $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            //     $extension       = $request->file('avatar')->getClientOriginalExtension();
            //     $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            //     $dir = 'uploads/customer_avatar/';
            //     $image_path = $dir . $fileNameToStore;
            //     if (\File::exists($image_path)) {
            //         \File::delete($image_path);
            //     }
            //     $url = '';
            //     $path = Utility::upload_file($request,'avatar',$fileNameToStore,$dir,[]);

            //     if($path['flag'] == 1){
            //         $url = $path['url'];
            //     }else{
            //         return redirect()->back()->with('error', __($path['msg']));
            //     }
            //     $image  = !empty($request->avatar) ? $fileNameToStore : '';
            // }
            // $cust['avatar']= $image;

            /* $amt = 0;

            if (!empty($quote_id['advance_payment'])) {
                $amt = $quote_id['grand_total'] - $quote_id['advance_payment'];
                $cust['due_amount'] = ($cust['due_amount'] ?? 0) + $amt;
                $cust['paid_amount'] = ($cust['paid_amount'] ?? 0) + $quote_id['advance_payment'];
            } else {
                $cust['due_amount'] = ($cust['due_amount'] ?? 0) + $quote_id['grand_total'];
            } */



            // $customer_rcd->update($cust);

            $quote_id->update(['status' => 3, 'is_final' => 1]);


            $ord['customer_type'] = $quote_id['customer_type'];
            $ord['customer_id'] = $customer_rcd['id'];
            $ord['date'] = $quote_id['date'];
            $ord['status'] = Utility::getOrderStatus('Order Placed');
            $ord['transport_id'] = $quote_id['transport_id'];
            $ord['tax_detail_json'] = $quote_id['tax_detail_json'];
            $ord['total_tax_sum'] = $quote_id['total_tax_sum'];
            $ord['gst'] = $quote_id['gst'];
            $ord['grand_total'] = $quote_id['grand_total'];
            $ord['is_advance_payment'] = $quote_id['is_advance_payment'];
            $ord['payment_after_days'] = $quote_id['payment_after_days'];
            $ord['remaining_payment'] = $quote_id['grand_total'];
            $ord['advance_payment'] = $quote_id['advance_payment'];
            $ord['is_final'] = 1;
            $ord['notes'] = $quote_id['notes'];
            $ord['quote_invoice'] = $quote_id['quote_invoice'];
            $ord['created_by'] = \Auth::user()->creatorId();
            $ord['user_id'] = $quote_id['user_id'];
            // $ord['company_id'] =$quote_id['company_id'];


            $order_id = Order::create($ord);

            // Add Order Activity
            OrderActivity::create([
                'user_id' => \Auth::user()->id,
                'order_id' => $order_id['id'],
                'action' => "Order Created",
                'message' => "An order has been placed.",
            ]);

            ActivityLogger::writeFor('orders', 'create', $order_id, null, [
                'event_key' => 'order.created',
                'reference' => $quote_id,
                'description' => 'Order created from quotation.',
                'properties' => [
                    'quote_id' => $quote_id->id,
                    'quote_code' => $quote_id->code,
                    'customer_id' => $order_id->customer_id,
                    'status_id' => $order_id->status,
                    'grand_total' => $order_id->grand_total,
                ],
            ]);

            $this->writeQuoteActivity('change_status', 'quote.status_changed', $quote_id, 'Quotation finalized and converted to order.', [
                'changes' => ActivityLogger::diff(
                    [
                        'status' => (string) $quoteBeforeStatus,
                        'status_name' => $this->quoteStatusName($quoteBeforeStatus),
                        'is_final' => (string) $quoteBeforeIsFinal,
                    ],
                    [
                        'status' => (string) $quote_id->status,
                        'status_name' => $this->quoteStatusName((int) $quote_id->status),
                        'is_final' => (string) $quote_id->is_final,
                    ]
                ),
                'order_id' => $order_id->id,
                'order_number' => $order_id->order_number,
            ]);


            $quote_product = QuoteProducts::where('quote_id', $quote_id['id'])->get();
            $product_data = [];
            if ($quote_product) {
                foreach ($quote_product as $quo_pro) {
                    $ord_prod['order_id'] = $order_id['id'];
                    $ord_prod['product_id'] = $quo_pro['product_id'];
                    $ord_prod['marketplace_listing_id'] = $quo_pro['marketplace_listing_id'];
                    $ord_prod['short_notes'] = $quo_pro['short_notes'];
                    $ord_prod['qty'] = $quo_pro['qty'];
                    $ord_prod['unit_id'] = $quo_pro['unit_id'];
                    $ord_prod['price'] = $quo_pro['price'];
                    $ord_prod['discount'] = $quo_pro['discount'];
                    $ord_prod['total'] = $quo_pro['total'];
                    $ord_prod['created_by'] = \Auth::user()->creatorId();
                    $ord_prod['tax'] = $quo_pro['tax'];

                    OrderProduct::create($ord_prod);

                    //store data in product_data
                    $pro_data[] = [
                        'product_id' => $quo_pro['product_id'],
                        'qty' => $quo_pro['qty'],
                        'unit_id' => $quo_pro['unit_id']
                    ];
                }
            }

            if ($quote_id['is_advance_payment'] == 1) {
                $amount = $quote_id['grand_total'] - $quote_id['advance_payment'];
                $order_id->update(['remaining_payment' => number_format($amount, 2)]);

                //----- payment
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
                $payment->payee_id          = $order_id->customer_id ?? null;
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

                $payment_id = $payment->id;

                //------------ Order Payment
                $or_payment['order_id'] = $order_id->id;
                $or_payment['payment_id'] = $payment_id;
                $or_payment['amount'] = $request->amount;
                $or_payment['payment_status'] = $payment->payment_status;

                OrderPayment::create($or_payment);
            }


            $entity = Entity::find($order_id['customer_id']);
            if ($entity) {
                if ($quote_id['is_advance_payment'] == 1) {
                    $paidAmount = $entity->paid_amount + $request->amount;
                    $remaining_amount = max($order_id['grand_total'] - $request->amount, 0);
                    $final_due_amt = max($entity->due_amount + $remaining_amount, 0);
                    $entity->update([
                        'paid_amount' => $paidAmount,
                        'due_amount'  => $final_due_amt,
                    ]);
                } else {
                    $dueAmount = $entity->due_amount + $order_id['grand_total'];
                    $final_amount = max($dueAmount, 0);

                    $entity->update([
                        'due_amount' => $final_amount,
                    ]);
                }
            }
            /*
            $entity_rcd = Entity::where('id',$order_id['customer_id'])->first();
            if($entity_rcd)
            {
                if($quote_id['is_advance_payment'] == 1)
                {
                    $paidAmount = round($entity_rcd->paid_amount + $request->amount);
                    $remaining_amount = $order_id['grand_total'] - $request->amount;
                    $final_due_amt= round($entity_rcd->due_amount +$remaining_amount);
                    $entity_rcd->update(['paid_amount'=>$paidAmount,'due_amount'=>$final_due_amt]);

                }
                else
                {
                    $dueAmount = round($entity_rcd->due_amount + $order_id['grand_total']);
                    $final_amount    = $dueAmount > 0 ? $dueAmount : 0;
                    // $entity_rcd->save();
                    $entity_rcd->update(['due_amount'=>$final_amount]);
                }

            }*/

            // Keep previous quotes visible in quote list after approval.

            DB::commit();

            return response()->json([
                'success' => 'true',
                'redirect_url'   => route('quotes.index'),
                'message_success' => 'Quotes status has been updated successfully ',
            ]);
        } catch (\Throwable $th) {

            DB::rollback();

            throw $th;

            return response()->json([
                'redirect_url' => route('quotes.index', ['message_error' => 'Something went wrong. Please try again.']),
            ]);
        }

        /* old code

        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required|digits:10',
            // 'gst_no' => [
            //     'required',
            //     'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'
            // ],
            'avatar' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'address_line_1' => 'required',
            'address_line_2' => 'required',
            'city' => 'required',
            'zipcode' => 'required',
            'state' => 'required',
            'country' => 'required',
        ]);

        $input = $request->all();

        $quote_id = Quotes::where('id', $id)->first();
        $lead_id = Lead::where('id', $quote_id['lead_id'])->first();


        $cust_contact_exist = Entity::isCustomer()->where('contact', $request['phone'])->where('id', '!=', $lead_id['customer_id'])->first();
        if ($cust_contact_exist) {
            return response()->json([
                'errors' => [
                    'phone' => ['This phone number already exists for another customer.']
                ]
            ], 422);
        }


        try {

            DB::beginTransaction();

            $customer_adr = Entity::where('id', $quote_id['customer_id'])->first();

            //already address available then update
            $ship_adr  = $request->ship_id;
            $bill_adr = $request->bill_id;
            if ($bill_adr) {

                $billing_data = Address::where('id', $bill_adr)->first();


                $billing_inp['name'] = $request['name'];
                $billing_inp['email'] = $request['email'];
                $billing_inp['phone'] = $request['phone'];
                $billing_inp['address_line_1'] = $request['address_line_1'];
                $billing_inp['address_line_2'] = $request['address_line_2'];
                $billing_inp['city'] = $request['city'];
                $billing_inp['zipcode'] = $request['zipcode'];
                $billing_inp['state'] = $request['state'];
                $billing_inp['country'] = $request['country'];
                $billing_data->update($billing_inp);
            }

            if ($ship_adr) {
                $shipping_data = Address::where('id', $ship_adr)->first();
                $shipping_inp['name'] = $request['name'];
                $shipping_inp['email'] = $request['email'];
                $shipping_inp['phone'] = $request['phone'];
                $shipping_inp['address_line_1'] = $request['address_line_1_sp'];
                $shipping_inp['address_line_2'] = $request['address_line_2_sp'];
                $shipping_inp['city'] = $request['city_sp'];
                $shipping_inp['zipcode'] = $request['zipcode_sp'];
                $shipping_inp['state'] = $request['state_sp'];
                $shipping_inp['country'] = $request['country_sp'];
                $shipping_data->update($shipping_inp);
            }

            if (isset($request->is_check) && $request->is_check == 1 && $ship_adr && $bill_adr) {

                $billing_data = Address::where('id', $bill_adr)->first();


                $billing_inp['name'] = $request['name'];
                $billing_inp['email'] = $request['email'];
                $billing_inp['phone'] = $request['phone'];
                $billing_inp['address_line_1'] = $request['address_line_1'];
                $billing_inp['address_line_2'] = $request['address_line_2'];
                $billing_inp['city'] = $request['city'];
                $billing_inp['zipcode'] = $request['zipcode'];
                $billing_inp['state'] = $request['state'];
                $billing_inp['country'] = $request['country'];
                $billing_data->update($billing_inp);

                $shipping_data = Address::where('id', $ship_adr)->first();
                $shipping_inp['name'] = $request['name'];
                $shipping_inp['email'] = $request['email'];
                $shipping_inp['phone'] = $request['phone'];
                $shipping_inp['address_line_1'] = $request['address_line_1_sp'];
                $shipping_inp['address_line_2'] = $request['address_line_2_sp'];
                $shipping_inp['city'] = $request['city_sp'];
                $shipping_inp['zipcode'] = $request['zipcode_sp'];
                $shipping_inp['state'] = $request['state_sp'];
                $shipping_inp['country'] = $request['country_sp'];
                $shipping_data->update($shipping_inp);
            }


            if (isset($request->is_check) && $request->is_check == 1 && $ship_adr == null && $bill_adr == null) {

                $billing_inp['name'] = $request['name'];
                $billing_inp['email'] = $request['email'];
                $billing_inp['phone'] = $request['phone'];
                $billing_inp['address_line_1'] = $request['address_line_1'];
                $billing_inp['address_line_2'] = $request['address_line_2'];
                $billing_inp['city'] = $request['city'];
                $billing_inp['zipcode'] = $request['zipcode'];
                $billing_inp['state'] = $request['state'];
                $billing_inp['country'] = $request['country'];
                $billing_new_adr =  Address::create($billing_inp);
                $customer_adr->update(['billing_address_id' => $billing_new_adr->id, 'shipping_address_id' => $billing_new_adr->id]);
            }

            if ($request->is_check == null && $ship_adr == null && $bill_adr == null) {
                $billing_inp['name'] = $request['name'];
                $billing_inp['email'] = $request['email'];
                $billing_inp['phone'] = $request['phone'];
                $billing_inp['address_line_1'] = $request['address_line_1'];
                $billing_inp['address_line_2'] = $request['address_line_2'];
                $billing_inp['city'] = $request['city'];
                $billing_inp['zipcode'] = $request['zipcode'];
                $billing_inp['state'] = $request['state'];
                $billing_inp['country'] = $request['country'];
                $billing_new_adr =  Address::create($billing_inp);

                $shipping_inp['name'] = $request['name'];
                $shipping_inp['email'] = $request['email'];
                $shipping_inp['phone'] = $request['phone'];
                $shipping_inp['address_line_1'] = $request['address_line_1_sp'];
                $shipping_inp['address_line_2'] = $request['address_line_2_sp'];
                $shipping_inp['city'] = $request['city_sp'];
                $shipping_inp['zipcode'] = $request['zipcode_sp'];
                $shipping_inp['state'] = $request['state_sp'];
                $shipping_inp['country'] = $request['country_sp'];
                $shipping_new_adr =  Address::create($shipping_inp);

                $customer_adr->update(['billing_address_id' => $billing_new_adr->id, 'shipping_address_id' => $shipping_new_adr->id]);
            }

            $image = "";
            if ($request->hasFile('avatar')) {
                $filenameWithExt = $request->file('avatar')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('avatar')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $dir = 'uploads/customer_avatar/';
                $image_path = $dir . $fileNameToStore;
                if (\File::exists($image_path)) {
                    \File::delete($image_path);
                }
                $url = '';
                $path = Utility::upload_file($request, 'avatar', $fileNameToStore, $dir, []);

                if ($path['flag'] == 1) {
                    $url = $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }
                $image  = !empty($request->avatar) ? $fileNameToStore : '';
            }
            $cust['avatar'] = $image;
            $customer_adr->update($cust);

            $quote_id->update(['status' => 3, 'is_final' => 1]);

            $ord['customer_type'] = $quote_id['customer_type'];
            $ord['customer_id'] = $quote_id['customer_id'];
            $ord['date'] = $quote_id['date'];
            $ord['status'] = Utility::getOrderStatus('dispatch');
            $ord['transport_id'] = $quote_id['transport_id'];
            $ord['gst'] = $quote_id['gst'];
            $ord['grand_total'] = $quote_id['grand_total'];
            $ord['is_advance_payment'] = $quote_id['is_advance_payment'];
            $ord['payment_after_days'] = $quote_id['payment_after_days'];
            $ord['advance_payment'] = $quote_id['advance_payment'];
            $ord['is_final'] = 1;
            $ord['notes'] = $quote_id['notes'];
            $ord['quote_invoice'] = $quote_id['quote_invoice'];
            $ord['created_by'] = \Auth::user()->creatorId();

            $order_id = Order::create($ord);

            ActivityLogger::writeFor('orders', 'create', $order_id, null, [
                'event_key' => 'order.created',
                'reference' => $quote_id,
                'description' => 'Order created from quotation.',
                'properties' => [
                    'quote_id' => $quote_id->id,
                    'quote_code' => $quote_id->code,
                    'customer_id' => $order_id->customer_id,
                    'status_id' => $order_id->status,
                    'grand_total' => $order_id->grand_total,
                ],
            ]);

            $quote_product = QuoteProducts::where('quote_id', $quote_id['id'])->get();
            $product_data = [];
            if ($quote_product) {
                foreach ($quote_product as $quo_pro) {
                    $ord_prod['order_id'] = $order_id['id'];
                    $ord_prod['product_id'] = $quo_pro['product_id'];
                    $ord_prod['marketplace_listing_id'] = $quo_pro['marketplace_listing_id'];
                    $ord_prod['qty'] = $quo_pro['qty'];
                    $ord_prod['price'] = $quo_pro['price'];
                    $ord_prod['discount'] = $quo_pro['discount'];
                    $ord_prod['total'] = $quo_pro['total'];
                    $ord_prod['created_by'] = \Auth::user()->creatorId();
                    OrderProduct::create($ord_prod);

                    //store data in product_data
                    $pro_data[] = [
                        'product_id' => $quo_pro['product_id'],
                        'qty' => $quo_pro['qty'],
                    ];
                }
            }

            if ($quote_id['is_advance_payment'] == 1) {
                $pay['order_id'] = $order_id['id'];
                $pay['amount'] = $order_id['grand_total'];
                $pay['payment_date'] = $quote_id['date'];
                $pay['transaction_id'] = 'cash';
                $pay['payment_method'] = 'cash';
                $pay['description'] = 'desc';
                $pay['payment_status'] = 'paid';
                $pay['payment_type'] = 'order';
                $pay['created_by'] = \Auth::user()->creatorId();

                Payments::create($pay);
            }

            // duplicate lead remove
            $duplicate = Quotes::where('id', '!=', $quote_id['id'])->where('lead_id', $quote_id['lead_id'])->get();
            if ($duplicate->isNotEmpty()) {
                foreach ($duplicate as $dp) {
                    $qid = Quotes::where('id', $dp['id'])->first();
                    $qid->update(['created_by' => 0]);
                }
            }

            //update status of converted
            $lead_id->update(['is_converted' => 1]);
            ActivityLogger::writeFor('leads', 'convert', $lead_id, null, [
                'event_key' => 'lead.converted',
                'reference' => $order_id,
                'description' => 'Lead converted to order.',
                'properties' => [
                    'order_id' => $order_id->id,
                    'order_number' => $order_id->order_number,
                ],
            ]);

            DB::commit();

            return response()->json([
                'redirect_url' => route('quotes.index', ['message_success' => 'Quotes status has been updated successfully  ']),
            ]);
        } catch (\Throwable $th) {

            DB::rollback();

            throw $th;

            return response()->json([
                'redirect_url' => route('quotes.index', ['message_error' => 'Something went wrong. Please try again.']),
            ]);

            //throw $th;

        }
        */
    }

    public function customer_store(Request $request)
    {
        $tenantConnection = 'tenant';


        $validated = $request->validate([
            'name' => ['required'],
            // 'rate' => 'required|numeric',
            // 'description' => 'required',
            'email' => 'nullable|email',
            'is_active' => 'nullable',

            // Phones
            'phones' => 'required|array|min:1',
            'phones.*.phone' => 'required|numeric',
            'phones.*.phone_type' => 'required|in:primary,secondary',


            // Billing
            'companies.*.billing_country' => 'nullable|string',
            'companies.*.billing_state'   => 'nullable|string',
            'companies.*.billing_city'    => 'nullable|string',
            'companies.*.billing_zipcode' => 'nullable|string',
            'companies.*.billing_address_line_1' => 'nullable|string',
            'companies.*.billing_address_line_2' => 'nullable|string',

            // Shipping
            'companies.*.shipping_country' => 'nullable|string',
            'companies.*.shipping_state'   => 'nullable|string',
            'companies.*.shipping_city'    => 'nullable|string',
            'companies.*.shipping_zipcode' => 'nullable|string',
            'companies.*.shipping_address_line_1' => 'nullable|string',
            'companies.*.shipping_address_line_2' => 'nullable|string',
            'gst_no' => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[0-9A-Z]{1}Z[0-9A-Z]{1}$/',

        ]);

        $input = $request->all();

        if($request->gst_no)
        {
            $check_exist_gst = Entity::on($tenantConnection)->where('gst_no',$request->gst_no)->where('type','customer')->exists();

            if($check_exist_gst)
            {
                return response()->json([
                        'error'   => true,
                        'message' => $request->gst_no . ' Customer Company GST No already exists.'
                    ], 200);
            }
        }

         //check phones exists
        foreach ($request->phones as $key => $phoneData) {
            if ($phoneData['phone_type'] == 'primary') {
                $phoneData['phone_type'] = 1;
            }

            $cust_check = CustomerPhone::on($tenantConnection)->where('phone', $phoneData['phone'])
                ->where('is_primary', 1)
                ->first();

            if ($cust_check) {
                $get_cust = Entity::on($tenantConnection)->where('id',$cust_check->customer_id)->first();
                if(!$get_cust)
                {
                    return response()->json([
                        'success' => false,
                        'message' => 'customer not found'
                    ], 422);
                }

                if(\Auth::user()->type != 'company')
                {
                    if($get_cust->user_id == null){

                        $get_cust->user_id =  \Auth::user()->id;
                        $get_cust->save();
                    }

                    $get_user = User::where('id',$get_cust->user_id)->first();
                    if(!$get_user)
                    {
                        return response()->json([
                            'success' => false,
                            'message' => 'Sales person not found.'
                        ], 422);
                    }

                    if($get_cust->user_id != \Auth::user()->id){

                        return response()->json([
                            'error' => 'yes',
                            'message' => $get_cust->name . ' Customer already exists. And Managed by ' . $get_user->name
                        ], 200);
                    }

                }


                return response()->json([
                    'error' => 'yes',
                    'message' => $cust_check->phone . ' Customer phone already exists '
                ], 200);
            }
        }

        //check phones exists
        // foreach ($request->phones as $key => $phoneData) {
        //     if ($phoneData['phone_type'] == 'primary') {
        //         $phoneData['phone_type'] = 1;
        //     }

        //     $cust_check = CustomerPhone::where('phone', $phoneData['phone'])
        //         ->where('is_primary', 1)
        //         ->first();

        //     if ($cust_check) {
        //         $get_cust = Entity::where('id',$cust_check->customer_id)->first();
        //         if(!$get_cust)
        //         {
        //             return response()->json([
        //                 'success' => false,
        //                 'message' => 'customer not found'
        //             ], 422);
        //         }

        //         $get_user = User::where('id',$get_cust->user_id)->first();
        //         if(!$get_user)
        //         {
        //             return response()->json([
        //                 'success' => false,
        //                 'message' => 'user not found'
        //             ], 422);
        //         }

        //         return response()->json([
        //             'success' => false,
        //             'message' => $cust_check->phone . ' Customer phone already exists '.$get_user->name
        //         ], 422);
        //     }
        // }

        //entity
        $input['type'] = 'customer';
        $input['created_by'] = \Auth::user()->creatorId();

        if(\Auth::user()->type == 'company')
        {
            $input['user_id'] = null;
        }
        else
        {
            $input['user_id'] = \Auth::user()->id;
        }

        $cust = Entity::on($tenantConnection)->create($input);

        //customer phones
        foreach ($request->phones as $key => $phoneData) {

            $cust_phone_rcd = CustomerPhone::on($tenantConnection)->create([
                'customer_id'  => $cust->id,
                'phone'        => $phoneData['phone'] ?? null,
                'is_primary'   => (isset($phoneData['phone_type']) && $phoneData['phone_type'] === 'primary') ? 1 : 0,
                'is_secondary' => (isset($phoneData['phone_type']) && $phoneData['phone_type'] === 'secondary') ? 1 : 0,
                'is_whatsapp'  => isset($phoneData['is_whatsapp']) ? 1 : 0,
            ]);

            if ($key == 0) {
                $cust_phone = $phoneData['phone'] ?? null;
            }
        }


        foreach ($validated['companies'] as $companyData) {
            //company
            // $company = Company::create([
            //     'company_name'   => $companyData['comp_name'] ?? $cust->name,
            //     'email'  => $companyData['comp_email'] ?? $cust->email,
            //     'phone'  => $companyData['comp_phone'] ?? $cust_phone,
            //     'gst_no' => $companyData['comp_gst_no'] ?? null,
            //     'adhar_no' => $companyData['comp_adhar_no'] ?? null,
            //     'udhyam_no' => $companyData['comp_udhyam_no'] ?? null,
            //     'customer_id' => $cust->id,
            // ]);

            //address
            $billingAddress = Address::on($tenantConnection)->create([
                'name'           => $request->name ?? null,
                'email'          => $request->email ?? null,
                'phone'          => $cust_phone ?? null,
                'country'        => $companyData['billing_country'],
                'state'          => $companyData['billing_state'],
                'city'           => $companyData['billing_city'],
                'zipcode'        => $companyData['billing_zipcode'],
                'address_line_1' => $companyData['billing_address_line_1'],
                'address_line_2' => $companyData['billing_address_line_2'] ?? null,
            ]);

            //address
            $shippingAddress = Address::on($tenantConnection)->create([
                'name'           => $request->name ?? null,
                'email'          => $request->email ?? null,
                'phone'          => $cust_phone ?? null,
                'country'        => $companyData['shipping_country'] ??  $companyData['billing_country'],
                'state'          => $companyData['shipping_state'] ??  $companyData['billing_state'],
                'city'           => $companyData['shipping_city'] ?? $companyData['billing_city'],
                'zipcode'        => $companyData['shipping_zipcode'] ?? $companyData['billing_zipcode'],
                'address_line_1' => $companyData['shipping_address_line_1'] ?? $companyData['billing_address_line_1'],
                'address_line_2' => $companyData['shipping_address_line_2'] ?? $companyData['billing_address_line_2'],
            ]);

            $cust->update([
                'billing_address_id'  => $billingAddress->id,
                'shipping_address_id' => $shippingAddress->id,
            ]);
        }

        $stage = LeadStage::on($tenantConnection)->where('name', 'new')->first();

        //lead
        // $lead_data['customer_id'] = $cust->id;
        // $lead_data['name'] = $cust->name;
        // $lead_data['email'] = $cust->email;
        // $lead_data['phone'] = $cust->contact ?? null;
        // $lead_data['gst_no'] = $cust->gst_no ?? null;
        // $lead_data['user_id'] = \Auth::user()->id;
        // $lead_data['stage_id'] = $stage->id; //new
        // $lead_data['created_by'] = \Auth::user()->creatorId();
        // $lead_data['date'] = date('Y-m-d');

        // $lead_id = Lead::create($lead_data);


        //user-leads
        // UserLead::create(['user_id' => Auth::user()->id, 'lead_id' => $lead_id->id]);

        // lead-activity
        // $date = date('Y-m-d H:i:s');
        // Utility::add_lead_activity($lead_id->id, Auth::user()->id, 'add lead detail', $date, 'add');

        return response()->json([
            'success' => 'Customer has been added successfully.',
            'redirect_url' => route('quotes.create', [$cust->id]),
        ]);
    }

    public function check_cust_address(Request $request, $customer_id) //$lead_id
    {
        // $lead = Lead::find($lead_id);
        $customer_id =  Entity::where('id', $customer_id)->first(); // $lead->customer_id
        $static_gst_json = [
                'CGST' => 0,
                'SGST' => 0,
                'IGST' => 0,
                'GST'  => 1,
            ];

        if ($customer_id)
        {
            $address_rcd = Address::where('id', $customer_id->billing_address_id)->first();

            if ($address_rcd)
            {
                if(empty($address_rcd->country) && empty($address_rcd->state) ) //|| empty($address_rcd->city)
                {
                     $tax_detail = $static_gst_json;
                    $gst_all_list = Utility::gstNameList($tax_detail);

                    return response()->json([
                        'success' => true,
                        'tax_data' => $tax_detail,
                        'gst_list' =>$gst_all_list,
                    ]);

                      return response()->json([
                        'success' => false,
                        'tax_data' => 'address blank',
                    ]);
                }
                $tax_detail = Utility::getTaxValue($address_rcd->id);

                $gst_all_list = Utility::gstNameList($tax_detail);

                return response()->json([
                    'success' => true,
                    'tax_data' => $tax_detail,
                    'gst_list' =>$gst_all_list,
                ]);
            } else {

                $tax_detail = $static_gst_json;
                $gst_all_list = Utility::gstNameList($tax_detail);

                return response()->json([
                    'success' => true,
                    'tax_data' => $tax_detail,
                    'gst_list' =>$gst_all_list,
                ]);
            }
        }
        else
        {
            $tax_detail = $static_gst_json;
            $gst_all_list = Utility::gstNameList($tax_detail);

            return response()->json([
                'success' => false,
               'tax_data' => $tax_detail,
                'gst_list' =>$gst_all_list,
            ]);
        }
    }

    public function get_customer_price_history(Request $request, $customer_id, $product_id)
    {
        // $lead = Lead::find($lead_id);
        if (!$customer_id) {
            return response()->json(['success' => false, 'message' => 'Customer not found']);
        }

        $cust_price = CustomerPriceHistory::where('customer_id', $customer_id)
            ->where('product_id', $product_id)
            ->first();

        // \Log::info(" cust price ", [$cust_price]);
        return response()->json([
            'success' => true,
            'price' => $cust_price->price ?? 0.00,
            'discount' => $cust_price->discount ?? 0,
        ]);
    }

    public function invoiceOptions(Request $request, $quote)
    {
        $quote = $this->resolveQuoteFromRoute($quote);
        Utility::quote_pdf_generate_store($quote->id, 'quote_pdf');
        return view('quotes.invoice-options', compact('quote'));
    }

    public function previewInvoice(Request $request, $quote)
    {
        $quote = $this->resolveQuoteFromRoute($quote);
        $quotationTerms = app(TermsAndConditionService::class)
            ->getQuotationTerms(config('database.default', 'mysql'));

        $printOptions = empty(request()->query())
            ? ['original' => 1]
            : request()->query();

        $invoices = '';

        $printCount = count($printOptions);
        $print = 0;

        $device = \App\Models\Device::where('user_id', \Auth::user()->id)->first();
        $cust_phone = CustomerPhone::where('customer_id', $quote->customer_id)
            ->where('is_whatsapp', 1)
            ->first();
        $whatsapp_msg = $cust_phone ? $cust_phone->phone : null;

        foreach (array_keys($printOptions) as $val) {
            $print++;
            $data['quote_id'] = $quote;
            $data['quote_products'] = $quote->quoteProducts;
            $data['bank_detail'] = BankDetail::first();
            $data['qrCode'] = '';
            $data['print_option'] = $val;
            $data['for_pdf'] = false;

            // $invoiceNumber = str_replace('/','-',$order->bill_number ?? str_replace('ORDER','INV',$order->order_number));

            $data['check_discount_allow'] = Utility::isDiscountAllowed();
            $data['quotation_terms'] = $quotationTerms;
            $invoices .= view('quotes.invoice-view', $data)->render();

            if ($printCount != $print) {

                $invoices .= '<div class="page-break"></div>';
            }
        }


        return view('quotes.invoice-render', ['quote' => $quote, 'invoices' => $invoices, 'print_options' =>
        $printOptions, 'whatsapp_msg' => $whatsapp_msg, 'device' => $device]);
    }

    // Download Invoice
    public function invoice_new(Request $request, $quote)
    {
        $quote = $this->resolveQuoteFromRoute($quote);
        $quotationTerms = app(TermsAndConditionService::class)
            ->getQuotationTerms(config('database.default', 'mysql'));
        $printOptions = empty($request->query())
            ? ['original' => 1]
            : $request->query();

        $invoices = '';

        $printCount = count($printOptions);
        $print = 0;

        $company_name = '';
        if ($quote->customer_id) {
            $company_detail = Entity::where('id', $quote->customer_id)->first();
            $company_name = $company_detail->company_name;
        }

        foreach (array_keys($printOptions) as $val) {
            $print++;

            $data = [
                'quote_id' => $quote,
                'quote_products' => $quote->quoteProducts,
                'bank_detail' => BankDetail::first(),
                'qrCode' => '',
                'print_option' => $val,
                'for_pdf' => true,
                'check_discount_allow' => Utility::isDiscountAllowed(),
                'quotation_terms' => $quotationTerms,
            ];

            $invoices .= view('quotes.quotation', $data)->render();

            if ($printCount !== $print) {
                $invoices .= '<div class="page-break"></div>';
            }
        }

        $pdf = PDF::loadHTML($invoices);

        return $pdf->download("{$company_name}-{$quote->code}.pdf");

        /* duplicate proper working
        $printOptions = empty(request()->query())
        ? ['original' => 1]
        : request()->query();

        $invoiceNumber =  $quote->code;


        $full_path = storage_path('quote_temp');

        if (!File::exists($full_path)) {
            File::makeDirectory($full_path, 0775, true, true);
        }

        $zipFile = "Quote-".$invoiceNumber.".zip";
        $zipPath = storage_path("quote_temp/$zipFile");

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {

            foreach (array_keys($printOptions) as $val) {

                $data = [
                    'quote_id' => $quote,
                    'quote_products' => $quote->quoteProducts,
                    'bank_detail' => BankDetail::first(),
                    'qrCode' => '',
                    'print_option' => $val,
                    'for_pdf' => true,
                    'check_discount_allow'=>Utility::isDiscountAllowed(),
                ];

                $pdf = PDF::loadView('quotes.quotation', $data);

                // Output PDF file contents
                $content = $pdf->output();

                // Add to ZIP
                $zip->addFromString("{$val}-quote-".$invoiceNumber.".pdf", $content);
            }

            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
        */
    }

    private function resolveQuoteFromRoute($routeValue): Quotes
    {
        $user = \Auth::user();
        $value = trim((string) $routeValue);

        $query = Quotes::query();

        if ($user && $user->type === 'Sales') {
            $query->where('user_id', $user->id);
        } elseif ($user) {
            $query->where('created_by', $user->creatorId());
        }

        if (is_numeric($value)) {
            return (clone $query)->whereKey((int) $value)->firstOrFail();
        }

        $normalizedQuoteCode = str_replace('INVOICE-', 'QUOTE-', $value);

        return (clone $query)
            ->where(function ($q) use ($value, $normalizedQuoteCode) {
                $q->where('code', $value)
                    ->orWhere('code', $normalizedQuoteCode);
            })
            ->firstOrFail();
    }
}
