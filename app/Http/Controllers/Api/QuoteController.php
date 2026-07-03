<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\CustomerPriceHistory;
use App\Models\Entity;
use App\Models\Lead;
use App\Models\Order;
use App\Models\OrderActivity;
use App\Models\OrderProduct;
use App\Models\Products;
use App\Models\QuoteProducts;
use App\Models\Quotes;
use App\Models\Units;
use App\Models\User;
use App\Models\UserLead;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\ActivityLogger;

class QuoteController extends Controller
{
    private function writeQuoteActivity(string $action, string $eventKey, Quotes $quote, string $description, array $properties = []): void
    {
        ActivityLogger::writeFor('quotes', $action, $quote, null, [
            'event_key' => $eventKey,
            'description' => $description,
            'properties' => $properties,
        ]);
    }

    public function quote_list(Request $request)
    {
        try {
            $user = $this->authenticatedUser();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            Log::info('------ start quote_list ------');
            Log::info('Request :-', $request->all());

            $quotes = $this->scopedQuoteQuery($user)
                ->with($this->quoteRelations())
                ->orderByDesc('id')
                ->get();

            $data = $quotes->map(fn(Quotes $quote) => $this->transformQuote($quote))->values();

            Log::info('------ end quote_list ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "Quote list.", $data, 200);
        } catch (\Throwable $e) {
            Log::info('quotes-list error ', [$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
        }
    }

    public function quote_detail(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'quote_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = $this->authenticatedUser();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            $quote = $this->findAccessibleQuote($user, (int) $request->quote_id, true);
            if (!$quote) {
                return Utility::return_response(false, "Quote not found.", "", 404);
            }

            return Utility::return_response(true, "Quote detail.", $this->transformQuote($quote), 200);
        } catch (\Throwable $e) {
            Log::info('quote_detail error ', [$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
        }
    }

    // public function add_quote(Request $request)
    // {
    //     try {
    //         $user = $this->authenticatedUser();
    //         if (!$user) {
    //             return Utility::return_response(false, "User not authenticated.", "", 401);
    //         }

    //          $validator = Validator::make($request->all(), [

    //             'customer_id' => 'required|exists:entities,id',
    //             'customer_type' => 'required|string',
    //             'date' => 'required|date',
    //             'payment_after_days' => 'required|numeric',
    //             'grand_total' => 'required|numeric',
    //             // 'total_tax_sum' => 'required|numeric',
    //             'tax_detail_json' => 'required',
    //             'product_json_list' => 'required',
    //             'lead_id' => 'nullable|exists:leads,id',
    //         ]);

    //         if ($validator->fails()) {
    //             return Utility::return_response(false, $validator->errors()->first(), "", 422);
    //         }


    //         Log::info('------ start add_quote ------');
    //         Log::info('Request :-', $request->all());

    //         $input = $request->all();

    //         try{


    //         if ($request->gst_no) {

    //           $request->validate([
    //             'gst_no' => [
    //                 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
    //             ]
    //         ], [
    //             'gst_no' => 'Invalid GST number format.'
    //         ]);
    //         $check_exist_gst = Entity::where('gst_no', $request->gst_no)->where('id', '!=', $request['lead_id'])->where('type', 'customer')->exists();

    //         if ($check_exist_gst) {
    //             return Utility::return_response(false, "Invalid GST number .", "", 404);

    //         }
    //     }

    //     //gst_no update in entity section
    //     $customerId = Entity::where('id', $request['customer_id'])->first();
    //     if ($customerId  || $request['gst_no'] || $request['adhar_no'] || $request['udhaym_no'] || $request['company_name']) {
    //         $customer = Entity::isCustomer()->where('id', $customerId['id'])->first();
    //         if (isset($request->gst_no)) {
    //             $isAvailGST = Entity::where('id', '!=', $customer['id'])->where('gst_no', $request['gst_no'])->first();
    //             if ($isAvailGST) {
    //                 // return redirect()->back()->with(['error'=>'GST No must be unique.']);
    //             }
    //         }

    //         if ($customer) {
    //             $updateData = [];

    //             if (isset($input['gst_no'])) {
    //                 $updateData['gst_no'] = $input['gst_no'];
    //             }
    //             if (isset($input['adhar_no'])) {
    //                 $updateData['company_adhar_no'] = $input['adhar_no'];
    //             }
    //             if (isset($input['udhaym_no'])) {
    //                 $updateData['company_udhyam_no'] = $input['udhaym_no'];
    //             }
    //             if (isset($input['company_name'])) {
    //                 $updateData['company_name'] = $input['company_name'];
    //             }

    //             if (!empty($updateData)) {
    //                 $customer->update($updateData);
    //             }
    //         }
    //     }

    //     if($request->has('lead_id') && $request->lead_id != null){

    //         $lead_data = Lead::find($request->lead_id);
    //     }

    //     $qt['customer_type'] = $request['customer_type'];
    //     $qt['lead_id']      = $request->lead_id ?? null; //$input['lead_id'];
    //     $qt['date'] = $request['date'];
    //     $qt['transport_id'] = $request['transport_id'];
    //     $qt['gst'] = $request['gst']; //gst value not percentage
    //     $qt['grand_total'] = (float) $request['grand_total'];

    //     if ($request['customer_type'] == 'regular') {
    //         $qt['is_advance_payment'] = 0;
    //         $qt['payment_after_days'] = $request['payment_after_days'];
    //     } else {
    //         $qt['is_advance_payment'] = 1;
    //         $qt['advance_payment'] = $request['advance_payment'];
    //     }



    //     $qt['created_by'] = $user->creatorId();
    //     $taxDetail = $request->input('tax_detail_json');

    //      $qt['tax_detail_json'] = is_array($taxDetail)
    //     ? json_encode($taxDetail)
    //     : $taxDetail;

    //     $qt['where_from'] = $request['lead_id'] ? 'Lead' : 'Customer';
    //     $qt['customer_id'] = $customerId['id'];
    //     // $qt['tax_detail_json'] =   json_encode($request->tax_detail_json,true) ?? null;
    //     $qt['total_tax_sum'] = $request['total_tax_sum'] ?? 0; // not used bcz product based gst add
    //     $qt['user_id'] = isset($lead_data) ? $lead_data->user_id : $user->id;  //$leadId->user_id;

    //     $quote_id = Quotes::create($qt);

    //     $all_products = $request->input('product_json_list');

    //     if (is_string($all_products)) {
    //         $all_products = json_decode($all_products, true);
    //     }

    //     foreach ($all_products as $index => $productId) {

    //         $product_rcd = Products::where('id',$productId['product_id'])->first();
    //         QuoteProducts::create([
    //             'quote_id'     => $quote_id['id'],
    //             'product_id'   => (int) $productId['product_id'],
    //             'qty'          => (float) $productId['qty'],
    //             'unit_id'      => (int) $productId['unit_id'],
    //             'mrp'          => (float) ($product_rcd['price'] ?? 0),
    //             'discount'     => (float) ($productId['discount'] ?? 0),
    //             'price'        => (float) $productId['price'],
    //             'total'        => (float) ($productId['total'] ?? 0),
    //             'created_by'   => $user->creatorId(),
    //             'short_notes'  => $productId['short_notes'] ?? null,
    //             'tax'          => ($productId['tax'] ?? 0),
    //         ]);

    //         //customer price history
    //         $check_cust_price_avl = CustomerPriceHistory::where('customer_id', $customerId['id'])->where('product_id', $productId['product_id'])->first();
    //         if ($check_cust_price_avl) {
    //             $check_cust_price_avl->update(['price' =>  $productId['price'], 'discount' => $productId['discount']]);
    //         } else {
    //             $cust_prc_his['customer_id'] = $customerId['id'];
    //             $cust_prc_his['product_id'] = $$productId['product_id'];
    //             $cust_prc_his['price'] =  $productId['price'];
    //             $cust_prc_his['discount'] = $productId['discount'];
    //             CustomerPriceHistory::create($cust_prc_his);
    //         }
    //     }


    //     $quote_id->update(['status' => 1]);

    //     $this->writeQuoteActivity(
    //         'create',
    //         'quote.created',
    //         $quote_id,
    //         (int) $quote_id->status === 1 ? 'Quotation created.' : 'Quotation created.',
    //         [
    //             'customer_id' => $quote_id->customer_id,
    //             'lead_id' => $quote_id->lead_id,
    //             'status' => $quote_id->status,
    //             'status_name' => $this->quoteStatusName((int) $quote_id->status),
    //             'grand_total' => $quote_id->grand_total,
    //             'product_count' => count($products['id'] ?? []),
    //         ]
    //     );

    //       DB::commit();


    //         }
    //          catch(\Throwable $th)
    //          {
    //                DB::rollback();

    //         throw $th;
    //          }

    //         Log::info('------ end add_quote ------');
    //         Log::info('------------------------------------------------------------------------------');

    //         return Utility::return_response(true, "Quote has been added successfully.", $this->transformQuote($quote_id), 200);
    //     } catch (\InvalidArgumentException $e) {
    //         return Utility::return_response(false, $e->getMessage(), "", 422);
    //     } catch (\Throwable $e) {
    //         Log::info('add_quote error ', [$e->getMessage()]);
    //         return Utility::return_response(false, $e->getMessage(), "", 500);
    //     }
    // }

    public function add_quote(Request $request)
    {
        try {
            $user = $this->authenticatedUser();

            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:entities,id',
                'customer_type' => 'required|string',
                'date' => 'required|date',
                'payment_after_days' => 'required|numeric',
                'grand_total' => 'required|numeric',
                'tax_detail_json' => 'required',
                'product_json_list' => 'required',
                'lead_id' => 'nullable|exists:leads,id',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            Log::info('------ start add_quote ------');
            Log::info('Request :-', $request->all());

            $input = $request->all();

            DB::beginTransaction();

            try {
                if ($request->filled('gst_no')) {
                    $request->validate([
                        'gst_no' => [
                            'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
                        ],
                    ], [
                        'gst_no.regex' => 'Invalid GST number format.',
                    ]);

                    $check_exist_gst = Entity::where('gst_no', $request->gst_no)
                        ->where('id', '!=', $request->customer_id)
                        ->where('type', 'customer')
                        ->exists();

                    if ($check_exist_gst) {
                        DB::rollBack();
                        return Utility::return_response(false, "Invalid GST number .", "", 404);
                    }
                }

                $customerId = Entity::where('id', $request->customer_id)->first();

                if (!$customerId) {
                    DB::rollBack();
                    return Utility::return_response(false, "Customer not found.", "", 404);
                }

                if (
                    $customerId ||
                    $request->filled('gst_no') ||
                    $request->filled('adhar_no') ||
                    $request->filled('udhaym_no') ||
                    $request->filled('company_name')
                ) {
                    $customer = Entity::isCustomer()->where('id', $customerId->id)->first();

                    if (!$customer) {
                        DB::rollBack();
                        return Utility::return_response(false, "Customer not found.", "", 404);
                    }

                    if ($request->filled('gst_no')) {
                        $isAvailGST = Entity::where('id', '!=', $customer->id)
                            ->where('gst_no', $request->gst_no)
                            ->first();

                        if ($isAvailGST) {
                            DB::rollBack();
                            return Utility::return_response(false, "GST No must be unique.", "", 422);
                        }
                    }

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

                $lead_data = null;

                if ($request->filled('lead_id')) {
                    $lead_data = Lead::find($request->lead_id);
                }

                $taxDetail = $request->input('tax_detail_json');

                $qt = [];
                $qt['customer_type'] = $request->customer_type;
                $qt['lead_id'] = $request->lead_id ?? null;
                $qt['date'] = $request->date;
                $qt['transport_id'] = $request->transport_id;
                $qt['gst'] = $request->gst ?? 0;
                $qt['grand_total'] = (float) $request->grand_total;

                if ($request->customer_type == 'regular') {
                    $qt['is_advance_payment'] = 0;
                    $qt['payment_after_days'] = $request->payment_after_days;
                } else {
                    $qt['is_advance_payment'] = 1;
                    $qt['advance_payment'] = $request->advance_payment ?? 0;
                }

                $qt['created_by'] = $user->creatorId();
                $qt['tax_detail_json'] = is_array($taxDetail)
                    ? json_encode($taxDetail)
                    : $taxDetail;

                $qt['where_from'] = $request->filled('lead_id') ? 'Lead' : 'Customer';
                $qt['customer_id'] = $customerId->id;
                $qt['total_tax_sum'] = $request->total_tax_sum ?? 0;
                $qt['user_id'] = $lead_data ? $lead_data->user_id : $user->id;

                $quote_id = Quotes::create($qt);

                $all_products = $request->input('product_json_list');

                if (is_string($all_products)) {
                    $all_products = json_decode($all_products, true);
                }

                if (!is_array($all_products) || empty($all_products)) {
                    DB::rollBack();
                    return Utility::return_response(false, "Invalid product list.", "", 422);
                }

                foreach ($all_products as $productId) {
                    $product_rcd = Products::where('id', $productId['product_id'])->first();

                    if (!$product_rcd) {
                        DB::rollBack();
                        return Utility::return_response(false, "Product not found.", "", 404);
                    }

                    QuoteProducts::create([
                        'quote_id'     => $quote_id->id,
                        'product_id'   => (int) $productId['product_id'],
                        'qty'          => (float) $productId['qty'],
                        'unit_id'      => (int) $productId['unit_id'],
                        'mrp'          => (float) ($product_rcd->price ?? 0),
                        'discount'     => (float) ($productId['discount'] ?? 0),
                        'price'        => (float) $productId['price'],
                        'total'        => (float) ($productId['total'] ?? 0),
                        'created_by'   => $user->creatorId(),
                        'short_notes'  => $productId['short_notes'] ?? null,
                        'tax'          => (float) ($productId['tax'] ?? 0),
                    ]);

                    $check_cust_price_avl = CustomerPriceHistory::where('customer_id', $customerId->id)
                        ->where('product_id', $productId['product_id'])
                        ->first();

                    if ($check_cust_price_avl) {
                        $check_cust_price_avl->update([
                            'price' => $productId['price'],
                            'discount' => $productId['discount'],
                        ]);
                    } else {
                        CustomerPriceHistory::create([
                            'customer_id' => $customerId->id,
                            'product_id'  => $productId['product_id'],
                            'price'       => $productId['price'],
                            'discount'    => $productId['discount'],
                        ]);
                    }
                }

                $quote_id->update(['status' => 1]);

                $this->writeQuoteActivity(
                    'create',
                    'quote.created',
                    $quote_id,
                    (int) $quote_id->status === 1 ? 'Quotation created.' : 'Quotation created.',
                    [
                        'customer_id' => $quote_id->customer_id,
                        'lead_id' => $quote_id->lead_id,
                        'status' => $quote_id->status,
                        'status_name' => $this->quoteStatusName((int) $quote_id->status),
                        'grand_total' => $quote_id->grand_total,
                        'product_count' => count($all_products),
                    ]
                );

                DB::commit();

                Log::info('------ end add_quote ------');
                Log::info('------------------------------------------------------------------------------');

                Utility::quote_pdf_generate_store($quote_id->id, 'quote_pdf');
                $quote_id->refresh()->load($this->quoteRelations());

                return Utility::return_response(
                    true,
                    "Quote has been added successfully.",
                    $this->transformQuote($quote_id),
                    200
                );
            } catch (\Throwable $th) {
                DB::rollBack();
                throw $th;
            }
        } catch (\InvalidArgumentException $e) {
            return Utility::return_response(false, $e->getMessage(), "", 422);
        } catch (\Throwable $e) {
            Log::info('add_quote error ', [$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
        }
    }

    public function edit_quote(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:quotes,id',
                'grand_total' => 'required|numeric',
                'gst' => 'required|numeric',

                'tax_detail_json' => 'required',
                'product_json_list' => 'required',
                'lead_id' => 'nullable|exists:leads,id',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = $this->authenticatedUser();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            Log::info('------ start edit_quote ------');
            Log::info('Request :-', $request->all());

            DB::beginTransaction();

            $quote_id = Quotes::find($request->id);
            $input = $request->all();
            $quoteBefore = $this->quoteActivitySnapshot($quote_id);

            // GST VALIDATION
            if ($request->gst_no) {
                $request->validate([
                    'gst_no' => [
                        'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
                    ]
                ]);

                $check_exist_gst = Entity::where('gst_no', $request->gst_no)
                    ->where('id', '!=', $quote_id->customer_id)
                    ->where('type', 'customer')
                    ->exists();

                if ($check_exist_gst) {
                    DB::rollBack(); // ✅ SAFE
                    return Utility::return_response(false, "This GST number already exists for another customer.", "", 422);
                }
            }

            $customer = Entity::isCustomer()->where('id', $quote_id->customer_id)->first();

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


            // UPDATE QUOTE
            $qt['date'] = $input['date'] ?? $quote_id->date;
            $qt['transport_id'] = $input['transport_id'];
            $qt['gst'] = $input['gst'];
            $qt['grand_total'] = (float) $input['grand_total'];

            if ($quote_id['customer_type'] == 'regular') {
                $qt['is_advance_payment'] = 0;
                $qt['payment_after_days'] = $input['payment_after_days'];
            } else {
                $qt['is_advance_payment'] = 1;
                $qt['advance_payment'] = $input['advance_payment'];
            }

            $qt['created_by'] = $user->creatorId();

            $taxDetail = $request->input('tax_detail_json');
            $qt['tax_detail_json'] = is_array($taxDetail) ? json_encode($taxDetail) : $taxDetail;

            $qt['total_tax_sum'] = $input['total_tax_sum'] ?? 0;

            $quote_id->update($qt);

            // PRODUCTS
            $all_products = $request->input('product_json_list');

            if (is_string($all_products)) {
                $all_products = json_decode($all_products, true);
            }

            foreach ($all_products as $productId) {

                $product_rcd = Products::where('id', $productId['product_id'])->first();

                if (!$product_rcd) {
                    DB::rollBack();
                    return Utility::return_response(false, "Product not found.", "", 422);
                }

                $product_id = $product_rcd['id'];
                $submittedProductIds[] = $product_id;

                $qty = $productId['qty'] ?? 0;
                $mrp = $product_rcd['price'] ?? 0;
                $units = $productId['unit_id'] ?? null;
                $dealer_price = $productId['price'] ?? 0;
                $discount = $productId['discount'] ?? 0;
                $product_total = $productId['total'] ?? 0;
                $short_note = $productId['short_notes'] ?? null;
                $product_gst = $productId['tax'] ?? 0;

                $quoteProduct = QuoteProducts::where('quote_id', $quote_id['id'])
                    ->where('product_id', $product_id)
                    ->first();

                if ($quoteProduct) {
                    $quoteProduct->update([
                        'qty' => $qty,
                        'unit_id' => $units,
                        'mrp' => $mrp,
                        'discount' => $discount,
                        'price' => $dealer_price,
                        'total' => $product_total,
                        'created_by' => $user->creatorId(),
                        'short_notes' => $short_note,
                        'tax' => $product_gst,
                    ]);
                } else {
                    QuoteProducts::create([
                        'quote_id' => $quote_id['id'],
                        'product_id' => $product_id,
                        'qty' => $qty,
                        'unit_id' => $units,
                        'mrp' => $mrp,
                        'discount' => $discount,
                        'price' => $dealer_price,
                        'total' => $product_total,
                        'created_by' => $user->creatorId(),
                        'short_notes' => $short_note,
                        'tax' => $product_gst,
                    ]);
                }

                //customer price history
                $check_cust_price_avl = CustomerPriceHistory::where('customer_id', $customer['id'])->where('product_id', $product_id)->first();
                if ($check_cust_price_avl) {
                    $check_cust_price_avl->update(['price' => $dealer_price, 'discount' => $discount]);
                } else {
                    $cust_prc_his['customer_id'] = $customer['id'];
                    $cust_prc_his['product_id'] = $product_id;
                    $cust_prc_his['price'] = $dealer_price;
                    $cust_prc_his['discount'] = $discount;
                    CustomerPriceHistory::create($cust_prc_his);
                }
            }

            // delete removed products after foreach
            $existingProductIds = QuoteProducts::where('quote_id', $quote_id->id)
                ->pluck('product_id')
                ->toArray();

            $productIdsToDelete = array_diff($existingProductIds, $submittedProductIds);

            if (!empty($productIdsToDelete)) {
                QuoteProducts::where('quote_id', $quote_id->id)
                    ->whereIn('product_id', $productIdsToDelete)
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

            Utility::quote_pdf_generate_store($quote_id->id, 'quote_pdf');
            $quote_id->refresh()->load($this->quoteRelations());

            DB::commit();

            Log::info('------ end edit_quote ------');

            return Utility::return_response(true, "quote has been updated successfully.", $this->transformQuote($quote_id), 200);
        } catch (\InvalidArgumentException $e) {
            Log::info('edit_quote ', [$e->getMessage()]);

            return Utility::return_response(false, $e->getMessage(), "", 422);
        } catch (\Throwable $e) {

            DB::rollBack(); // ✅ FINAL SAFETY

            Log::info('edit_quote error ', [$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
        }
    }

    public function customer_price_history_product_list(Request $request)
    {
        try {
            Log::info('------ start customer_price_history_product_list ------');
            Log::info('Request :-', $request->all());

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:entities,id',
                'product_id' => 'required|exists:products,id',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = $this->authenticatedUser();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            $customer = $this->findAccessibleCustomer($user, (int) $request->customer_id);
            if (!$customer) {
                return Utility::return_response(false, "Customer not found.", "", 404);
            }

            $product = Products::with([
                'getUnit:id,name',
                'getUnitType:id,name',
            ])->find((int) $request->product_id);

            if (!$product) {
                return Utility::return_response(false, "Product not found.", "", 404);
            }

            $priceHistory = CustomerPriceHistory::with([
                'product:id,name,image,sku_code',
            ])->where('customer_id', $customer->id)
                ->where('product_id', $product->id)
                ->first();

            $qty = 1;
            $dealerPrice = (float) ($priceHistory?->price ?? $product->price);
            $discount = (float) ($priceHistory?->discount ?? 0);
            $baseTotal = $dealerPrice * $qty;
            $discountAmt = ($baseTotal * $discount) / 100;
            $lineTotal = round($baseTotal - $discountAmt, 2);

            $response = [
                'product_id' => $product->id,
                'name' => $priceHistory?->product?->name ?? $product->name,
                'image' => $priceHistory?->product?->image ?? $product->image,
                'qty' => $qty,
                'unit_type' => $product->unit_type,
                'get_unit_type' => [
                    'id' => $product->getUnitType?->id,
                    'name' => $product->getUnitType?->name,
                ],
                'unit' => $product->unit,
                'get_unit' => [
                    'id' => $product->getUnit?->id,
                    'name' => $product->getUnit?->name,
                ],
                'dealer_price' => number_format($dealerPrice, 2, '.', ''),
                'discount' => number_format($discount, 2, '.', ''),
                'line_total' => number_format($lineTotal, 2, '.', ''),
            ];

            Log::info('------ end customer_price_history_product_list ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "customer previous product list.", $response, 200);
        } catch (\Throwable $e) {
            Log::info('customer_price_history_product_list error ', [$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
        }
    }

    public function customer_gst_list(Request $request)
    {
        try {
            Log::info('------ start customer_gst_list ------');
            Log::info('Request :-', $request->all());

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:entities,id',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = $this->authenticatedUser();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            $customer = $this->findAccessibleCustomer($user, (int) $request->customer_id);
            if (!$customer) {
                return Utility::return_response(false, "Customer not found.", "", 404);
            }


            $taxDetail = $this->buildCustomerGstPayload($customer->id);



            Log::info('------ end customer_gst_list ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "customer gst list.", $taxDetail, 200);
        } catch (\Throwable $e) {
            Log::info('customer_gst_list error ', [$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
        }
    }

    public function generate_pdf(Request $request)
    {
        try {
            Log::info('------ start generate_pdf ------');
            Log::info('Request :-', $request->all());

            $validator = Validator::make($request->all(), [
                'quote_id' => 'required|exists:quotes,id',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = $this->authenticatedUser();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            $quote = $this->findAccessibleQuote($user, (int) $request->quote_id);
            if (!$quote) {
                return Utility::return_response(false, "Quote not found.", "", 404);
            }

            Utility::quote_pdf_generate_store($quote->id, 'quote_pdf');
            $quote->refresh();

            $data = [
                'quote_invoice' => $quote->quote_invoice
                    ? asset('storage/uploads/quote_pdf/' . $quote->quote_invoice)
                    : '',
            ];

            Log::info('------ end generate_pdf ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "quote invoice generate successfully.", $data, 200);
        } catch (\Throwable $e) {
            Log::info('generate_pdf error ', [$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
        }
    }

    public function quote_final(Request $request)
    {
        try {
            Log::info('------ start quote_final ------');
            Log::info('Request :-', $request->all());

            $validator = Validator::make($request->all(), [
                'quote_id' => 'required|exists:quotes,id',
                'billing_country' => 'required',
                'billing_state' => 'required',
                'billing_city' => 'required',
                'billing_zipcode' => 'required',
                'billing_address_line_1' => 'required',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $sameAsAbove = (int) $request->input('is_same_above', 1) === 1;
            if (!$sameAsAbove) {
                $shippingValidator = Validator::make($request->all(), [
                    'shipping_country' => 'required',
                    'shipping_state' => 'required',
                    'shipping_city' => 'required',
                    'shipping_zipcode' => 'required',
                    'shipping_address_line_1' => 'required',
                ]);

                if ($shippingValidator->fails()) {
                    return Utility::return_response(false, $shippingValidator->errors()->first(), "", 422);
                }
            }

            $user = $this->authenticatedUser();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            $quote = $this->findAccessibleQuote($user, (int) $request->quote_id);
            if (!$quote) {
                return Utility::return_response(false, "Quote not found.", "", 404);
            }

            if ((int) $quote->status === 3 || (int) $quote->is_final === 1) {
                return Utility::return_response(false, "this quoate already final.", "", 422);
            }

            $quoteBeforeStatus = (int) $quote->status;
            $quoteBeforeIsFinal = (int) $quote->is_final;

            $customer = Entity::find((int) $quote->customer_id);
            if (!$customer) {
                return Utility::return_response(false, "Customer not found.", "", 404);
            }

            DB::connection($this->tenantConnectionName())->transaction(function () use ($request, $user, $quote, $customer, $sameAsAbove, $quoteBeforeStatus, $quoteBeforeIsFinal) {
                $this->syncCustomerContactDetails($customer, $request);
                $this->syncQuoteAddresses($customer, $request, $sameAsAbove);

                $quote->update([
                    'status' => 3,
                    'is_final' => 1,
                ]);

                $order = Order::create([
                    'customer_type' => $quote->customer_type,
                    'customer_id' => $customer->id,
                    'date' => $quote->date,
                    'status' => Utility::getOrderStatus('Order Placed'),
                    'transport_id' => $quote->transport_id,
                    'tax_detail_json' => $quote->tax_detail_json,
                    'total_tax_sum' => $quote->total_tax_sum,
                    'gst' => $quote->gst,
                    'grand_total' => $quote->grand_total,
                    'is_advance_payment' => $quote->is_advance_payment,
                    'payment_after_days' => $quote->payment_after_days,
                    'remaining_payment' => $quote->grand_total,
                    'advance_payment' => $quote->advance_payment,
                    'is_final' => 1,
                    'notes' => $quote->notes,
                    'quote_invoice' => $quote->quote_invoice,
                    'created_by' => $user->creatorId(),
                    'user_id' => $quote->user_id,
                ]);

                OrderActivity::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'action' => 'Order Created',
                    'message' => 'An order has been placed.',
                ]);

                ActivityLogger::writeFor('orders', 'create', $order, null, [
                    'event_key' => 'order.created',
                    'reference' => $quote,
                    'description' => 'Order created from quotation.',
                    'properties' => [
                        'quote_id' => $quote->id,
                        'quote_code' => $quote->code,
                        'customer_id' => $order->customer_id,
                        'status_id' => $order->status,
                        'grand_total' => $order->grand_total,
                    ],
                ]);

                $this->writeQuoteActivity('change_status', 'quote.status_changed', $quote, 'Quotation finalized and converted to order.', [
                    'changes' => ActivityLogger::diff(
                        [
                            'status' => (string) $quoteBeforeStatus,
                            'status_name' => $this->quoteStatusName($quoteBeforeStatus),
                            'is_final' => (string) $quoteBeforeIsFinal,
                        ],
                        [
                            'status' => (string) $quote->status,
                            'status_name' => $this->quoteStatusName((int) $quote->status),
                            'is_final' => (string) $quote->is_final,
                        ]
                    ),
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);

                $quoteProducts = QuoteProducts::where('quote_id', $quote->id)->get();
                foreach ($quoteProducts as $quoteProduct) {
                    OrderProduct::create([
                        'order_id' => $order->id,
                        'product_id' => $quoteProduct->product_id,
                        'short_notes' => $quoteProduct->short_notes,
                        'qty' => $quoteProduct->qty,
                        'unit_id' => $quoteProduct->unit_id,
                        'price' => $quoteProduct->price,
                        'discount' => $quoteProduct->discount,
                        'total' => $quoteProduct->total,
                        'created_by' => $user->creatorId(),
                        'tax' => $quoteProduct->tax,
                    ]);
                }

                $this->updateCustomerDueAmount($customer, $order, $request);

                if (!empty($quote->lead_id)) {
                    Quotes::where('id', '!=', $quote->id)
                        ->where('lead_id', $quote->lead_id)
                        ->update(['created_by' => 0]);
                }
            });

            Log::info('------ end quote_final ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "quotation final successfully.", "", 200);
        } catch (\Throwable $e) {
            Log::info('quote_final error ', [$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
        }
    }

    public function get_customer_lead_product(Request $request)
    {
        try {
            Log::info('------ start get_customer_lead_product ------');
            Log::info('Request :-', $request->all());

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:entities,id',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = $this->authenticatedUser();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            $customer = $this->findAccessibleCustomer($user, (int) $request->customer_id);
            if (!$customer) {
                return Utility::return_response(false, "Customer not found.", "", 404);
            }

            $lead = $this->scopedLeadQuery($user)
                ->with([
                    'product' => function ($query) {
                        $query->select('products.id', 'products.name', 'products.image')
                            ->withPivot(['price', 'qty', 'unit_id']);
                    },
                ])
                ->where('customer_id', $customer->id)
                ->orderByDesc('id')
                ->first();

            if (!$lead) {
                return Utility::return_response(false, "Lead not found for this customer.", "", 404);
            }

            $products = [];
            foreach ($lead->product as $product) {
                $customerPrice = CustomerPriceHistory::where('customer_id', $lead->customer_id)
                    ->where('product_id', $product->id)
                    ->first();

                $dealerPrice = (float) ($customerPrice?->price ?? $product->pivot->price);
                $discount = (float) ($customerPrice?->discount ?? 0);
                $qty = (float) ($product->pivot->qty > 0 ? $product->pivot->qty : 1);

                $baseTotal = $dealerPrice * $qty;
                $discountAmt = ($baseTotal * $discount) / 100;
                $lineTotal = round($baseTotal - $discountAmt, 2);

                $productRecord = Products::with(['getUnitType:id,name', 'getUnit:id,name'])
                    ->find($product->id);

                $products[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->image ?? "",
                    'qty' => $qty,
                    'unit' => $product->pivot->unit_id,
                    'get_unit' => $productRecord?->getUnit,
                    'unit_type' => $productRecord?->unit_type ?? "",
                    'get_unit_type' => $productRecord?->getUnitType,
                    'dealer_price' => number_format($dealerPrice, 2, '.', ''),
                    'discount' => number_format($discount, 2, '.', ''),
                    'line_total' => number_format($lineTotal, 2, '.', ''),
                ];
            }

            $response = [
                'lead_id' => $lead->id,
                'customer_id' => $lead->customer_id,
                'products' => $products,
            ];

            Log::info('------ end get_customer_lead_product ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "Customer According Lead product list.", $response, 200);
        } catch (\Throwable $e) {
            Log::info('get_customer_lead_product error ', [$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
        }
    }

    private function authenticatedUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    private function scopedQuoteQuery(User $user)
    {
        $query = Quotes::query()->where('created_by', $user->creatorId());

        if ($user->type === 'company') {
            return $query;
        }

        $assignedLeadIds = UserLead::where('user_id', $user->id)->pluck('lead_id');

        return $query->where(function ($quoteQuery) use ($user, $assignedLeadIds) {
            $quoteQuery->where('user_id', $user->id);

            if ($assignedLeadIds->isNotEmpty()) {
                $quoteQuery->orWhereIn('lead_id', $assignedLeadIds);
            }
        });
    }

    private function scopedLeadQuery(User $user)
    {
        $query = Lead::query()->where('created_by', $user->creatorId());

        if ($user->type === 'company') {
            return $query;
        }

        $assignedLeadIds = UserLead::where('user_id', $user->id)->pluck('lead_id');

        return $query->where(function ($leadQuery) use ($user, $assignedLeadIds) {
            $leadQuery->where('user_id', $user->id);

            if ($assignedLeadIds->isNotEmpty()) {
                $leadQuery->orWhereIn('id', $assignedLeadIds);
            }

            $leadQuery->orWhereNull('user_id');
        });
    }

    private function accessibleCustomerQuery(User $user)
    {
        $query = Entity::query()
            ->where('type', 'customer')
            ->where('created_by', $user->creatorId());

        if ($user->type === 'company') {
            return $query;
        }

        return $query->where(function ($customerQuery) use ($user) {
            $customerQuery->where('user_id', $user->id)
                ->orWhereNull('user_id');
        });
    }

    private function findAccessibleCustomer(User $user, int $customerId): ?Entity
    {
        return $this->accessibleCustomerQuery($user)->where('id', $customerId)->first();
    }

    private function findAccessibleLead(User $user, int $leadId): ?Lead
    {
        return $this->scopedLeadQuery($user)->where('id', $leadId)->first();
    }

    private function findAccessibleQuote(User $user, int $quoteId, bool $withRelations = false): ?Quotes
    {
        $query = $this->scopedQuoteQuery($user)->where('id', $quoteId);

        if ($withRelations) {
            $query->with($this->quoteRelations());
        }

        return $query->first();
    }

    private function quoteRelations(): array
    {
        return [
            'lead:id,name,customer_id,user_id',
            'customer:id,name,email,company_name,gst_no,company_adhar_no,company_udhyam_no,billing_address_id,shipping_address_id',
            'customer.getBillingAddress:id,address_line_1,address_line_2,city,state,country,zipcode',
            'customer.getShippingAddress:id,address_line_1,address_line_2,city,state,country,zipcode',
            'get_user:id,name',
            'get_transport:id,name',
            'quoteProducts' => function ($query) {
                $query->select('id', 'quote_id', 'product_id', 'short_notes', 'qty', 'unit_id', 'discount', 'price', 'total', 'mrp', 'tax')
                    ->with(['getProduct:id,name,image,sku_code,unit,unit_type']);
            },
        ];
    }

    private function transformQuote(Quotes $quote): array
    {
        $data = $quote->toArray();
        $data['date_original'] = $quote->date;
        $data['date'] = $quote->date ? Utility::getDateFormated($quote->date) : '';
        $data['status_name'] = $this->quoteStatusName((int) $quote->status);
        $data['tax_detail_json'] = $this->decodeTaxDetailJson($quote->tax_detail_json);
        $data['quote_invoice_url'] = $quote->quote_invoice
            ? asset('storage/uploads/quote_pdf/' . $quote->quote_invoice)
            : '';
        // $data['quote_invoice_path'] = $quote->quote_invoice
        //     ? storage_path('uploads/quote_pdf/' . $quote->quote_invoice)
        //     : '';

        return $data;
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

    private function quoteStatusName(int $status): string
    {
        return match ($status) {
            0, 1 => 'Pending',
            2 => 'Send',
            3 => 'Final',
            4 => 'Rejected',
            default => '',
        };
    }

    private function decodeTaxDetailJson($taxDetail): array
    {
        if (is_array($taxDetail)) {
            return $taxDetail;
        }

        if (!is_string($taxDetail) || trim($taxDetail) === '') {
            return [];
        }

        $decoded = json_decode($taxDetail, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeTaxDetailJson($taxDetail): string
    {
        if (is_array($taxDetail)) {
            return json_encode($taxDetail, JSON_UNESCAPED_UNICODE);
        }

        if (is_string($taxDetail)) {
            $decoded = json_decode($taxDetail, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }

            return $taxDetail;
        }

        return json_encode([], JSON_UNESCAPED_UNICODE);
    }

    private function isAdminQuotePayload(Request $request): bool
    {
        return is_array($request->input('products'))
            || $request->has('tax_json_data')
            || $request->has('total_amt')
            || $request->has('new_lead_id');
    }

    private function addQuoteFromAdminPayload(Request $request, User $user): Quotes
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required|exists:entities,id',
            'new_lead_id' => 'nullable|exists:leads,id',
            'customer_type' => 'required|in:regular,debitClient',
            'date' => 'required|date',
            'transport_id' => 'nullable|exists:entities,id',
            'company_name' => 'nullable|string|max:120',
            'gst_no' => 'nullable|string|max:15',
            'adhar_no' => 'nullable|digits:12',
            'udhaym_no' => 'nullable|string|max:20',
            'payment_after_days' => 'required_if:customer_type,regular|nullable|integer|min:0|max:365',
            'advance_payment' => 'required_if:customer_type,debitClient|nullable|numeric|min:0',
            'products' => 'required|array',
            'products.id' => 'required|array|min:1',
            'products.id.*' => 'required|exists:products,id',
            'products.qty' => 'required|array',
            'products.qty.*' => 'required|numeric|gt:0',
            'products.units' => 'required|array',
            'products.units.*' => 'required|exists:units,id',
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
            'tax_rate_sum' => 'nullable|numeric|min:0',
            'tax_json_data' => 'nullable',
        ], [
            'lead_id.required' => 'Please select a customer.',
            'products.id.min' => 'Please add at least one product.',
            'payment_after_days.required_if' => 'Payment After Days is required for regular customer.',
            'advance_payment.required_if' => 'Advance Payment is required for debit client.',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        if ($request->customer_type !== 'regular' && (float) $request->input('advance_payment', 0) > (float) $request->total_amt) {
            throw new \InvalidArgumentException('Advance Payment cannot be greater than Total Amount.');
        }

        $customer = $this->findAccessibleCustomer($user, (int) $request->lead_id);
        if (!$customer) {
            throw new \InvalidArgumentException('Customer not found.');
        }

        $lead = null;
        if ($request->filled('new_lead_id')) {
            $lead = $this->findAccessibleLead($user, (int) $request->new_lead_id);
            if (!$lead) {
                throw new \InvalidArgumentException('Lead not found.');
            }

            if ((int) $lead->customer_id !== (int) $customer->id) {
                throw new \InvalidArgumentException('Lead does not belong to the selected customer.');
            }
        }

        $productItems = $this->parseAndValidateAdminProductList($request->input('products', []));
        $this->validateAdminQuoteTotals($request, $productItems);

        return DB::connection($this->tenantConnectionName())->transaction(function () use ($request, $user, $customer, $lead, $productItems) {
            $this->syncCustomerLegalDetails($customer, $request);

            $quote = Quotes::create([
                'customer_type' => $request->customer_type,
                'lead_id' => $lead?->id,
                'date' => $request->date,
                'status' => $request->input('save') === 'save_send' ? 2 : 1,
                'transport_id' => $request->input('transport_id') ?: null,
                'gst' => (float) $request->input('tax', 0),
                'grand_total' => (float) $request->total_amt,
                'is_advance_payment' => $request->customer_type === 'regular' ? 0 : 1,
                'payment_after_days' => $request->customer_type === 'regular' ? (int) $request->payment_after_days : null,
                'advance_payment' => $request->customer_type === 'regular' ? 0 : (float) $request->input('advance_payment', 0),
                'is_final' => 0,
                'created_by' => $user->creatorId(),
                'notes' => $request->input('notes'),
                'customer_id' => $customer->id,
                'where_from' => $lead ? 'Lead' : 'Customer',
                'tax_detail_json' => $this->validatedTaxDetailJson($request->input('tax_json_data', [])),
                'total_tax_sum' => (float) $request->input('tax_rate_sum', 0),
                'user_id' => $lead?->user_id ?: $user->id,
            ]);

            $this->syncQuoteProducts($quote, $customer, $productItems, $user, false);

            return $quote->load($this->quoteRelations());
        });
    }

    private function validatedTaxDetailJson($taxDetail): string
    {
        if (is_array($taxDetail)) {
            return json_encode($taxDetail, JSON_UNESCAPED_UNICODE);
        }

        if ($taxDetail === null || $taxDetail === '') {
            return json_encode([], JSON_UNESCAPED_UNICODE);
        }

        if (!is_string($taxDetail)) {
            throw new \InvalidArgumentException('Invalid tax_json_data format.');
        }

        $decoded = json_decode($taxDetail, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            throw new \InvalidArgumentException('Invalid tax_json_data format.');
        }

        return json_encode($decoded, JSON_UNESCAPED_UNICODE);
    }

    private function parseAndValidateProductList($productPayload): Collection
    {
        if (is_string($productPayload)) {
            $productItems = json_decode($productPayload, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
            }
        } elseif (is_array($productPayload)) {
            $productItems = $productPayload;
        } else {
            $productItems = [];
        }

        if (empty($productItems) || !is_array($productItems)) {
            throw new \InvalidArgumentException('Invalid or empty product list');
        }

        $normalizedItems = collect();

        foreach ($productItems as $index => $item) {
            $requiredFields = ['product_id', 'short_notes', 'qty', 'unit_id', 'price', 'total'];
            foreach ($requiredFields as $field) {
                if (!array_key_exists($field, $item)) {
                    throw new \InvalidArgumentException("Missing field '{$field}' at index " . ($index + 1));
                }
            }

            if (!is_numeric($item['qty']) || (float) $item['qty'] <= 0) {
                throw new \InvalidArgumentException("qty must be greater than 0 at index " . ($index + 1));
            }

            if (!is_numeric($item['price']) || (float) $item['price'] < 0) {
                throw new \InvalidArgumentException("price must be 0 or greater at index " . ($index + 1));
            }

            if (isset($item['discount']) && (!is_numeric($item['discount']) || (float) $item['discount'] < 0 || (float) $item['discount'] > 100)) {
                throw new \InvalidArgumentException("discount must be between 0 and 100 at index " . ($index + 1));
            }

            if (!is_numeric($item['total']) || (float) $item['total'] < 0) {
                throw new \InvalidArgumentException("total must be 0 or greater at index " . ($index + 1));
            }

            [$product, $unit] = $this->validateProductAndUnit((int) $item['product_id'], (int) $item['unit_id']);

            $normalizedItems->push([
                'product' => $product,
                'product_id' => (int) $item['product_id'],
                'short_notes' => $item['short_notes'],
                'qty' => (float) $item['qty'],
                'unit_id' => (int) $item['unit_id'],
                'mrp' => (float) ($item['mrp'] ?? $product->price ?? 0),
                'price' => (float) $item['price'],
                'discount' => (float) ($item['discount'] ?? 0),
                'tax' => (float) ($item['tax'] ?? $item['gst_value'] ?? 0),
                'total' => (float) $item['total'],
            ]);
        }

        return $normalizedItems;
    }

    private function parseAndValidateAdminProductList(array $products): Collection
    {
        $productIds = array_values($products['id'] ?? []);
        $qtyList = array_values($products['qty'] ?? []);
        $unitList = array_values($products['units'] ?? []);
        $mrpList = array_values($products['mrp'] ?? []);
        $priceList = array_values($products['price'] ?? []);
        $discountList = array_values($products['discount'] ?? []);
        $gstValueList = array_values($products['gst_value'] ?? []);
        $productTotalList = array_values($products['product_total'] ?? []);
        $shortNotesList = array_values($products['short_notes'] ?? []);

        $lineCount = count($productIds);
        if ($lineCount === 0) {
            throw new \InvalidArgumentException('Please add at least one product.');
        }

        $requiredLists = [
            'qty' => $qtyList,
            'units' => $unitList,
            'price' => $priceList,
            'product_total' => $productTotalList,
        ];

        foreach ($requiredLists as $field => $list) {
            if (count($list) !== $lineCount) {
                throw new \InvalidArgumentException("Invalid products.{$field} data.");
            }
        }

        if (count(array_unique(array_map('intval', $productIds))) !== $lineCount) {
            throw new \InvalidArgumentException('Duplicate products are not allowed in one quote.');
        }

        $normalizedItems = collect();

        for ($index = 0; $index < $lineCount; $index++) {
            $productId = (int) $productIds[$index];
            $unitId = (int) $unitList[$index];
            [$product] = $this->validateProductAndUnit($productId, $unitId);

            $qty = (float) $qtyList[$index];
            $mrp = isset($mrpList[$index]) && $mrpList[$index] !== '' ? (float) $mrpList[$index] : (float) ($product->price ?? 0);
            $price = (float) $priceList[$index];
            $discount = isset($discountList[$index]) && $discountList[$index] !== '' ? (float) $discountList[$index] : 0.0;
            $gst = isset($gstValueList[$index]) && $gstValueList[$index] !== '' ? (float) $gstValueList[$index] : 0.0;
            $submittedTotal = (float) $productTotalList[$index];
            $shortNotes = (string) ($shortNotesList[$index] ?? '');

            if ($qty <= 0) {
                throw new \InvalidArgumentException("Product row " . ($index + 1) . ": qty must be greater than 0.");
            }

            if ($price < 0) {
                throw new \InvalidArgumentException("Product row " . ($index + 1) . ": dealer price must be 0 or greater.");
            }

            if ($discount < 0 || $discount > 100) {
                throw new \InvalidArgumentException("Product row " . ($index + 1) . ": discount must be between 0 and 100.");
            }

            if ($gst < 0) {
                throw new \InvalidArgumentException("Product row " . ($index + 1) . ": GST must be 0 or greater.");
            }

            if ($submittedTotal < 0) {
                throw new \InvalidArgumentException("Product row " . ($index + 1) . ": total must be 0 or greater.");
            }

            $discountAmountPerUnit = ($price * $discount) / 100;
            $subtotal = $qty * ($price - $discountAmountPerUnit);
            $taxAmount = ($subtotal * $gst) / 100;
            $calculatedTotal = round($subtotal + $taxAmount, 2);

            if (abs($calculatedTotal - round($submittedTotal, 2)) > 0.05) {
                throw new \InvalidArgumentException("Product row " . ($index + 1) . ": total mismatch. Please recalculate the quote.");
            }

            $normalizedItems->push([
                'product' => $product,
                'product_id' => $productId,
                'short_notes' => $shortNotes,
                'qty' => $qty,
                'unit_id' => $unitId,
                'mrp' => $mrp,
                'price' => $price,
                'discount' => $discount,
                'tax' => $gst,
                'tax_amount' => round($taxAmount, 2),
                'total' => round($submittedTotal, 2),
            ]);
        }

        return $normalizedItems;
    }

    private function validateAdminQuoteTotals(Request $request, Collection $productItems): void
    {
        $calculatedTax = round((float) $productItems->sum('tax_amount'), 2);
        $calculatedGrandTotal = round((float) $productItems->sum('total'), 2);
        $submittedTax = round((float) $request->input('tax', 0), 2);
        $submittedGrandTotal = round((float) $request->input('total_amt', 0), 2);

        if (abs($calculatedTax - $submittedTax) > 0.05) {
            throw new \InvalidArgumentException('Tax Value mismatch. Please recalculate the quote.');
        }

        if (abs($calculatedGrandTotal - $submittedGrandTotal) > 0.05) {
            throw new \InvalidArgumentException('Total Amount mismatch. Please recalculate the quote.');
        }
    }

    private function validateProductAndUnit(int $productId, int $unitId): array
    {
        $product = Products::find($productId);
        if (!$product) {
            throw new \InvalidArgumentException("product_id = {$productId} product not found.");
        }

        $unit = Units::find($unitId);
        if (!$unit) {
            throw new \InvalidArgumentException("unit_id = {$unitId} unit not found.");
        }

        $unitCheck = Units::where('id', $unitId)
            ->where('type_id', $product->unit_type)
            ->first();

        if (!$unitCheck) {
            throw new \InvalidArgumentException("unit_id = {$unitId} invalid unit.");
        }

        return [$product, $unit];
    }

    private function normalizeCustomerType(string $customerType): ?string
    {
        $normalized = strtolower(trim($customerType));

        return match ($normalized) {
            'regular' => 'regular',
            'debitclient', 'debit_client', 'debit client' => 'debitClient',
            default => null,
        };
    }

    private function syncQuoteProducts(Quotes $quote, Entity $customer, Collection $productItems, User $user, bool $replaceExisting): void
    {
        if ($replaceExisting) {
            $requestProductIds = $productItems->pluck('product_id')->all();
            QuoteProducts::where('quote_id', $quote->id)
                ->whereNotIn('product_id', $requestProductIds)
                ->delete();
        }

        foreach ($productItems as $item) {
            $payload = [
                'qty' => $item['qty'],
                'unit_id' => $item['unit_id'],
                'mrp' => (float) ($item['mrp'] ?? $item['product']->price ?? 0),
                'price' => $item['price'],
                'discount' => $item['discount'],
                'total' => $item['total'],
                'short_notes' => $item['short_notes'],
                'tax' => (float) ($item['tax'] ?? 0),
                'created_by' => $user->creatorId(),
            ];

            if ($replaceExisting) {
                QuoteProducts::updateOrCreate(
                    [
                        'quote_id' => $quote->id,
                        'product_id' => $item['product_id'],
                    ],
                    $payload
                );
            } else {
                QuoteProducts::create([
                    'quote_id' => $quote->id,
                    'product_id' => $item['product_id'],
                    ...$payload,
                ]);
            }

            CustomerPriceHistory::updateOrCreate(
                [
                    'customer_id' => $customer->id,
                    'product_id' => $item['product_id'],
                ],
                [
                    'price' => $item['price'],
                    'discount' => $item['discount'],
                ]
            );
        }
    }

    private function syncCustomerLegalDetails(Entity $customer, Request $request): void
    {
        $gstNumber = $request->input('gst_no');
        $adharNumber = $request->input('company_adhar_no', $request->input('adhar_no'));
        $udhyamNumber = $request->input('company_udhyam_no', $request->input('udhaym_no'));
        $companyName = $request->input('company_name');

        if (!empty($gstNumber)) {
            $validator = Validator::make(['gst_no' => $gstNumber], [
                'gst_no' => [
                    'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
                ],
            ]);

            if ($validator->fails()) {
                throw new \InvalidArgumentException($validator->errors()->first());
            }

            $exists = Entity::where('gst_no', $gstNumber)
                ->where('id', '!=', $customer->id)
                ->where('type', 'customer')
                ->exists();

            if ($exists) {
                throw new \InvalidArgumentException('This GST number already exists for another customer');
            }
        }

        $updateData = [];
        if ($request->has('gst_no')) {
            $updateData['gst_no'] = $gstNumber;
        }
        if ($request->has('company_adhar_no') || $request->has('adhar_no')) {
            $updateData['company_adhar_no'] = $adharNumber;
        }
        if ($request->has('company_udhyam_no') || $request->has('udhaym_no')) {
            $updateData['company_udhyam_no'] = $udhyamNumber;
        }
        if ($request->has('company_name')) {
            $updateData['company_name'] = $companyName;
        }

        if (!empty($updateData)) {
            $customer->update($updateData);
        }
    }

    private function syncCustomerContactDetails(Entity $customer, Request $request): void
    {
        $updateData = [];

        if ($request->filled('email')) {
            $updateData['email'] = $request->email;
        }

        if ($request->filled('gst_no')) {
            $updateData['gst_no'] = $request->gst_no;
        }

        if (!empty($updateData)) {
            $customer->update($updateData);
        }
    }

    private function syncQuoteAddresses(Entity $customer, Request $request, bool $sameAsAbove): void
    {
        $billing = [
            'name' => $customer->name,
            'email' => $customer->email,
            'country' => $request->billing_country,
            'state' => $request->billing_state,
            'city' => $request->billing_city,
            'zipcode' => $request->billing_zipcode,
            'address_line_1' => $request->billing_address_line_1,
            'address_line_2' => $request->billing_address_line_2,
        ];

        $billingAddress = $customer->billing_address_id ? Address::find($customer->billing_address_id) : null;
        if ($billingAddress) {
            $billingAddress->update($billing);
        } else {
            $billingAddress = Address::create($billing);
            $customer->update(['billing_address_id' => $billingAddress->id]);
        }

        $shipping = $sameAsAbove ? $billing : [
            'name' => $customer->name,
            'email' => $customer->email,
            'country' => $request->shipping_country,
            'state' => $request->shipping_state,
            'city' => $request->shipping_city,
            'zipcode' => $request->shipping_zipcode,
            'address_line_1' => $request->shipping_address_line_1,
            'address_line_2' => $request->shipping_address_line_2,
        ];

        $shippingAddress = $customer->shipping_address_id ? Address::find($customer->shipping_address_id) : null;
        if ($shippingAddress) {
            $shippingAddress->update($shipping);
        } else {
            $shippingAddress = Address::create($shipping);
            $customer->update(['shipping_address_id' => $shippingAddress->id]);
        }
    }

    private function updateCustomerDueAmount(Entity $customer, Order $order, Request $request): void
    {
        if ((int) $order->is_advance_payment === 1) {
            $paidAmount = (float) $customer->paid_amount + (float) $request->input('amount', 0);
            $remainingAmount = max((float) $order->grand_total - (float) $request->input('amount', 0), 0);
            $dueAmount = max((float) $customer->due_amount + $remainingAmount, 0);

            $customer->update([
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
            ]);

            return;
        }

        $customer->update([
            'due_amount' => max((float) $customer->due_amount + (float) $order->grand_total, 0),
        ]);
    }

    private function tenantConnectionName(): string
    {
        return (new Quotes())->getConnectionName() ?: config('database.default', 'mysql');
    }

    public function check_cust_address($customer_id) //$lead_id
    {
        return response()->json($this->buildCustomerGstPayload($customer_id));
    }

    private function buildCustomerGstPayload($customer_id): array
    {
        // $lead = Lead::find($lead_id);
        $customer_id =  Entity::where('id', $customer_id)->first(); // $lead->customer_id
        $static_gst_json = [
            'CGST' => 0,
            'SGST' => 0,
            'IGST' => 0,
            'GST'  => 1,
        ];

        if ($customer_id) {
            $address_rcd = Address::where('id', $customer_id->billing_address_id)->first();

            if ($address_rcd) {
                if (empty($address_rcd->country) && empty($address_rcd->state)) //|| empty($address_rcd->city)
                {
                    $tax_detail = $static_gst_json;
                    $gst_all_list = Utility::gstNameList($tax_detail);

                    return [
                        'success' => true,
                        'tax_data' => $tax_detail,
                        'gst_list' => $gst_all_list,
                    ];
                }
                $tax_detail = Utility::getTaxValue($address_rcd->id);

                $gst_all_list = Utility::gstNameList($tax_detail);

                return [
                    'success' => true,
                    'tax_data' => $tax_detail,
                    'gst_list' => $gst_all_list,
                ];
            } else {

                $tax_detail = $static_gst_json;
                $gst_all_list = Utility::gstNameList($tax_detail);

                return [
                    'success' => true,
                    'tax_data' => $tax_detail,
                    'gst_list' => $gst_all_list,
                ];
            }
        } else {
            $tax_detail = $static_gst_json;
            $gst_all_list = Utility::gstNameList($tax_detail);

            return [
                'success' => false,
                'tax_data' => $tax_detail,
                'gst_list' => $gst_all_list,
            ];
        }
    }
}
