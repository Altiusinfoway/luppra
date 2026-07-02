<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\CustomerPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\DB;
use App\Models\Utility;
use App\Models\LeadSource;
use App\Models\LeadStage;
use App\Models\Products;
use App\Models\Lead;
use App\Models\UserLead;
use App\Models\CustomerPriceHistory;
use App\Models\LeadProducts;
use App\Models\Entity;
use App\Models\LeadChat;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{

    public function list_lead(Request $request)
    {
        try {
            Log::info('------ start list_lead ------');
            Log::info('Request :-', $request->all());


            $user = JWTAuth::parseToken()->authenticate();

            $leads = DB::table('leads')
                ->join('entities', 'entities.id', '=', 'leads.customer_id')
                ->leftJoin('addresses', 'addresses.id', '=', 'entities.billing_address_id')
                ->leftJoin('states', 'states.id', '=', 'addresses.state')
                ->leftJoin('cities', 'cities.id', '=', 'addresses.city')
                ->leftJoin('customer_phones', function ($join) {
                    $join->on('customer_phones.customer_id', '=', 'entities.id')
                        ->where('customer_phones.is_primary', 1);
                })
                ->leftJoin('lead_types', 'lead_types.id', '=', 'leads.lead_type_id')
                ->leftJoin('lead_stages', 'lead_stages.id', '=', 'leads.stage_id')
                ->where('leads.user_id', $user->id)
                ->select([
                    'leads.id',
                    'leads.date',
                    'entities.name as customer_name',
                    'entities.email',
                    'states.name as billing_state',
                    'cities.name as billing_city',
                    'customer_phones.phone',
                    'lead_types.id as lead_type_id',
                    'lead_types.name as lead_type',
                    'lead_stages.id as stage_id',
                    'lead_stages.name as stage',
                    'leads.sources'
                ]);



            $data = $leads->orderBy('id', 'desc')->get();

            foreach ($data as $itm) {
                $itm->date = $itm->date ? Utility::getDateFormated($itm->date) : '';

                $itm->sources_list = $itm->sources ? LeadSource::whereIn('id', explode(',', $itm->sources))->select('id', 'name')->get() : [];
            }

            return Utility::return_response(true, "Lead list fetched successfully", $data, 200);

            Log::info('------ end list_lead ------');
            Log::info('------------------------------------------------------------------------------');
        } catch (JWTException $e) {
              \Log::info('lead list error ',[$e->getMessage()]);
            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        }
    }

    public function add_lead(Request $request)
    {


        try {
            $validator = Validator::make($request->all(), [
                'name'         => 'required|string',
                'email'        => 'nullable|email',
                'lead_type_id' => 'required|exists:tenant.lead_types,id',
                'lead_source'  => 'required|exists:tenant.lead_sources,id',
                'phones'       => 'required',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = JWTAuth::parseToken()->authenticate();


            Log::info('------ start add_lead ------');
            Log::info('Request :-', $request->all());

            if($request->lead_source)
            {
                $ids = explode(',', $request->lead_source);

                foreach ($ids as $id)
                {

                    if (!ctype_digit($id)) {
                        return Utility::return_response(false, "Invalid source ID: $id", "", 422);
                    }

                    if (!LeadSource::where('id', $id)->exists()) {
                        return Utility::return_response(false, "Lead source ID $id does not exist", "", 422);
                    }
                }
            }


            //product validation
            //lead_products checks
            $get_product_list = json_decode($request->product_list, true);

            if (!empty($get_product_list) && !is_array($get_product_list)) {
                return Utility::return_response(false, "At least one product is required", "", 422);
            }

            if ($get_product_list)
            {
                foreach ($get_product_list as $index => $prod)
                {
                    $requiredFields = ['product_id', 'price', 'qty'];

                    foreach ($requiredFields as $field)
                    {

                        if (!isset($prod[$field]) || $prod[$field] === null || $prod[$field] === '') {
                            return Utility::return_response(
                                false,
                                "{$field} is required at index " . ($index + 1),
                                "",
                                422
                            );
                        }
                    }

                    if (!is_numeric($prod['price']) || $prod['price'] < 0) {
                        return Utility::return_response(false, "Invalid price at index " . ($index + 1), "", 422);
                    }

                    if (!is_int($prod['qty']) || $prod['qty'] <= 0) {
                        return Utility::return_response(false, "qty must be positive integer at index " . ($index + 1), "", 422);
                    }

                    if ($prod['product_id']) {
                        $check_product = Products::where('id', $prod['product_id'])->first();
                        if (!$check_product) {
                            return Utility::return_response(false, "invalid product at index " . ($index + 1), "", 422);
                        }
                    }
                }
            }


            // Decode phones if passed as JSON string
            $phones = is_string($request->phones) ? json_decode($request->phones, true) : $request->phones;
            if (!is_array($phones)) {
                return Utility::return_response(false, "Invalid phone format", "", 422);
            }
            $request->merge(['phones' => $phones]);



            // ================= LEAD STAGE =================
            $stage = LeadStage::orderBy('order')->first();
            if (!$stage) {
                return Utility::return_response(false, "Please create lead stage first", "", 422);
            }

            DB::beginTransaction();

            // ================= CUSTOMER =================
            if (!empty($request->customer_id) && $request->customer_id != 0) {
                $customer = Entity::where('id', $request->customer_id)->where('type', 'customer')->firstOrFail();
            } else {
                $customer = Entity::create([
                    'name'       => $request->name,
                    'email'      => $request->email,
                    'type'       => 'customer',
                    'created_by' => $user->creatorId(),
                    'company_name'=>$request->name,
                ]);
            }

            $cust_id = $customer->id;

            // ================= ADDRESS =================
            if ($request->billing_country && $request->billing_state && $request->billing_city && $request->billing_zipcode && $request->billing_address_line_1) {
                $addressData = [
                    'country'        => $request->billing_country,
                    'state'          => $request->billing_state,
                    'city'           => $request->billing_city,
                    'zipcode'        => $request->billing_zipcode,
                    'address_line_1' => $request->billing_address_line_1,
                    'address_line_2' => $request->billing_address_line_2 ?? null,
                ];

                // Update or create billing address
                if ($customer->billing_address_id) {
                    $billing = Address::find($customer->billing_address_id);
                    $billing->update($addressData);
                } else {
                    $billing = Address::create($addressData);
                    $customer->billing_address_id = $billing->id;
                }

                // Update or create shipping address
                if ($customer->shipping_address_id) {
                    $shipping = Address::find($customer->shipping_address_id);
                    $shipping->update($addressData);
                } else {
                    $shipping = Address::create($addressData);
                    $customer->shipping_address_id = $shipping->id;
                }

                $customer->save();
            }

            // ================= PHONE VALIDATION =================
            $primaryCount = 0;

            foreach ($request->phones as $index => $phone) {

                if (empty($phone['phone'])) {
                    DB::rollBack();
                    return Utility::return_response(false, "Phone is required at index " . ($index + 1), "", 422);
                }

                if (!preg_match('/^[0-9]{10}$/', $phone['phone'])) {
                    DB::rollBack();
                    return Utility::return_response(false, "Invalid phone at index " . ($index + 1), "", 422);
                }

                foreach (['is_primary', 'is_secondary', 'is_whatsapp'] as $field) {
                    if (!isset($phone[$field]) || !in_array($phone[$field], [0, 1])) {
                        DB::rollBack();
                        return Utility::return_response(false, "Invalid {$field} at index " . ($index + 1), "", 422);
                    }
                }

                if ($phone['is_primary'] == 1 && $phone['is_secondary'] == 1) {
                    DB::rollBack();
                    return Utility::return_response(false, "Primary and Secondary both cannot be 1 at index " . ($index + 1), "", 422);
                }

                if ($phone['is_primary'] == 0 && $phone['is_secondary'] == 0) {
                    DB::rollBack();
                    return Utility::return_response(false, "Set either primary or secondary at index " . ($index + 1), "", 422);
                }

                if ($phone['is_primary'] == 1) {
                    $primaryCount++;
                }

                // Avoid duplicate primary phone across all customers
                $exists = CustomerPhone::where('phone', $phone['phone'])->where('is_primary', 1)->where('customer_id','!=',$cust_id)->first();
                if ($exists) {
                    DB::rollBack();
                    return Utility::return_response(false, $phone['phone'] . " already exists.", "", 422);
                }
            }

            if ($primaryCount > 1) {
                DB::rollBack();
                return Utility::return_response(false, "Only one primary phone allowed", "", 422);
            }

            // ================= SAVE PHONES =================
            foreach ($request->phones as $phone) {
                CustomerPhone::create([
                    'customer_id'  => $cust_id,
                    'phone'        => $phone['phone'],
                    'is_primary'   => $phone['is_primary'],
                    'is_secondary' => $phone['is_secondary'],
                    'is_whatsapp'  => $phone['is_whatsapp'],
                ]);
            }

            // ================= LEAD =================
            $lead = Lead::create([
                'name'         => $customer->name,
                'email'        => $customer->email,
                'user_id'      => $user->type !== 'company' ? $user->id : null,
                'sources'      => $request->lead_source,
                'stage_id'     => $request->stage_id ?? $stage->id,
                'notes'        => $request->description ?? null,
                'created_by'   => $user->creatorId(),
                'date'         => now()->format('Y-m-d'),
                'customer_id'  => $cust_id,
                'lead_type_id' => $request->lead_type_id,
            ]);


            //lead product
            $get_latest_lead = [];

            if (!empty($get_product_list)) {

                $lead_rcd = Lead::findOrFail($lead->id);

                foreach ($get_product_list as $index => $prod)
                {

                    // check existing record
                    $existing = LeadProducts::where('lead_id', $lead_rcd->id)
                        ->where('product_id', $prod['product_id'])
                        ->first();

                    if ($existing) {
                        // UPDATE
                        $existing->update([
                            'price' => $prod['price'],
                            'qty'   => $prod['qty'],
                        ]);

                        $get_latest_lead[] = $existing->id;

                    } else {
                        // INSERT
                        $new = LeadProducts::create([
                            'lead_id'    => $lead_rcd->id,
                            'product_id' => $prod['product_id'],
                            'price'      => $prod['price'],
                            'qty'        => $prod['qty'],
                            'created_by' => $user->creatorId(),
                        ]);

                        $get_latest_lead[] = $new->id;
                    }

                    //update lead product
                    $existingProducts[] = $prod['product_id'];

                    $lead->products = implode(',', array_unique($existingProducts));
                    $lead->save();
                }
            }

            // Assign lead to user
            UserLead::create([
                'user_id' => $user->id,
                'lead_id' => $lead->id,
            ]);

            // Lead activity
            Utility::add_lead_activity(
                $lead->id,
                $user->id,
                'add lead detail',
                now(),
                'add'
            );

            DB::commit();


            $all_data = Lead::where('id', $lead->id)->with([
                'customer' => function ($q) {
                    $q->select('id', 'name', 'email');
                },
                'user' => function ($q) {
                    $q->select('id', 'name');
                },
                'stage' => function ($q) {
                    $q->select('id', 'name');
                }

            ])
                ->select(
                    'id',
                    'customer_id',
                    'name',
                    'user_id',
                    'stage_id',
                    'sources',
                    'lead_type_id',
                    'notes',
                    'lead_id',
                    'date'
                )->first();

            $all_data->append('source_list');

            Log::info('------ end add_lead ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "Lead successfully created", $all_data, 200);
        } catch (\Throwable $e) {
            \Log::info('add lead error ',[$e->getMessage()]);
            DB::rollBack();
            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        }
    }

    public function edit_lead(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'lead_id'      => 'required|exists:tenant.leads,id',
                'name'         => 'required|string',
                'email'        => 'nullable|email',
                'lead_type_id' => 'required|exists:tenant.lead_types,id',
                'lead_source'  => 'required',
                'phones'       => 'required',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = JWTAuth::parseToken()->authenticate();

            Log::info('------ start edit_lead ------');
            Log::info('Request :-', $request->all());

            //lead sources check
            if($request->lead_source)
            {
                $ids = explode(',', $request->lead_source);

                foreach ($ids as $id)
                {

                    if (!ctype_digit($id)) {
                        return Utility::return_response(false, "Invalid source ID: $id", "", 422);
                    }

                    if (!LeadSource::where('id', $id)->exists()) {
                        return Utility::return_response(false, "Lead source ID $id does not exist", "", 422);
                    }
                }
            }

            //lead_products checks
            $get_product_list = json_decode($request->product_list, true);

            if (empty($get_product_list) || !is_array($get_product_list)) {
                return Utility::return_response(false, "At least one product is required", "", 422);
            }

            if ($get_product_list)
            {
                foreach ($get_product_list as $index => $prod)
                {
                    $requiredFields = ['product_id', 'price', 'qty'];

                    foreach ($requiredFields as $field)
                    {

                        if (!isset($prod[$field]) || $prod[$field] === null || $prod[$field] === '') {
                            return Utility::return_response(
                                false,
                                "{$field} is required at index " . ($index + 1),
                                "",
                                422
                            );
                        }
                    }

                    if (!is_numeric($prod['price']) || $prod['price'] < 0) {
                        return Utility::return_response(false, "Invalid price at index " . ($index + 1), "", 422);
                    }

                    if (!is_int($prod['qty']) || $prod['qty'] <= 0) {
                        return Utility::return_response(false, "qty must be positive integer at index " . ($index + 1), "", 422);
                    }

                    if ($prod['product_id']) {
                        $check_product = Products::where('id', $prod['product_id'])->first();
                        if (!$check_product) {
                            return Utility::return_response(false, "invalid product at index " . ($index + 1), "", 422);
                        }
                    }
                }
            }

            //insert data
            $get_latest_lead = [];

            if (!empty($get_product_list)) {

                $lead = Lead::findOrFail($request->lead_id);

                foreach ($get_product_list as $index => $prod)
                {

                    // check existing record
                    $existing = LeadProducts::where('lead_id', $request->lead_id)
                        ->where('product_id', $prod['product_id'])
                        ->first();

                    if ($existing) {
                        // UPDATE
                        $existing->update([
                            'price' => $prod['price'],
                            'qty'   => $prod['qty'],
                        ]);

                        $get_latest_lead[] = $existing->id;

                    } else {
                        // INSERT
                        $new = LeadProducts::create([
                            'lead_id'    => $request->lead_id,
                            'product_id' => $prod['product_id'],
                            'price'      => $prod['price'],
                            'qty'        => $prod['qty'],
                            'created_by' => $user->creatorId(),
                        ]);

                        $get_latest_lead[] = $new->id;
                    }

                    // update lead products list
                    $existingProducts = $lead->products
                        ? explode(',', $lead->products)
                        : [];

                    $existingProducts[] = $prod['product_id'];

                    $lead->products = implode(',', array_unique($existingProducts));
                    $lead->save();
                }
            }



            $phones = is_string($request->phones) ? json_decode($request->phones, true) : $request->phones;
            if (!is_array($phones)) {
                return Utility::return_response(false, "Invalid phone format", "", 422);
            }
            $request->merge(['phones' => $phones]);

            $user = JWTAuth::parseToken()->authenticate();

            $lead = Lead::findOrFail($request->lead_id);

            $customer = Entity::findOrFail($lead->customer_id);
            $cust_id = $customer->id;

            $customer->update([
                'name'  => $request->name,
                'email' => $request->email,
                'lead_type_id'=>$request->lead_type_id,
            ]);

            if ($request->billing_country && $request->billing_state && $request->billing_city && $request->billing_zipcode && $request->billing_address_line_1) {
                $addressData = [
                    'country'        => $request->billing_country,
                    'state'          => $request->billing_state,
                    'city'           => $request->billing_city,
                    'zipcode'        => $request->billing_zipcode,
                    'address_line_1' => $request->billing_address_line_1,
                    'address_line_2' => $request->billing_address_line_2 ?? null,
                ];

                if ($customer->billing_address_id) {
                    Address::find($customer->billing_address_id)->update($addressData);
                } else {
                    $billing = Address::create($addressData);
                    $customer->billing_address_id = $billing->id;
                }

                if ($customer->shipping_address_id) {
                    Address::find($customer->shipping_address_id)->update($addressData);
                } else {
                    $shipping = Address::create($addressData);
                    $customer->shipping_address_id = $shipping->id;
                }

                $customer->save();
            }

            $primaryCount = 0;

            foreach ($request->phones as $index => $phone) {
                if (empty($phone['phone'])) {
                    DB::rollBack();
                    return Utility::return_response(false, "Phone is required at index " . ($index + 1), "", 422);
                }

                if (!preg_match('/^[0-9]{10}$/', $phone['phone'])) {
                    DB::rollBack();
                    return Utility::return_response(false, "Invalid phone at index " . ($index + 1), "", 422);
                }

                foreach (['is_primary', 'is_secondary', 'is_whatsapp'] as $field) {
                    if (!isset($phone[$field]) || !in_array($phone[$field], [0, 1])) {
                        DB::rollBack();
                        return Utility::return_response(false, "Invalid {$field} at index " . ($index + 1), "", 422);
                    }
                }

                if ($phone['is_primary'] == 1 && $phone['is_secondary'] == 1) {
                    DB::rollBack();
                    return Utility::return_response(false, "Primary and Secondary both cannot be 1 at index " . ($index + 1), "", 422);
                }

                if ($phone['is_primary'] == 0 && $phone['is_secondary'] == 0) {
                    DB::rollBack();
                    return Utility::return_response(false, "Set either primary or secondary at index " . ($index + 1), "", 422);
                }

                if ($phone['is_primary'] == 1) {
                    $primaryCount++;
                }

                $exists = CustomerPhone::where('phone', $phone['phone'])
                    ->where('is_primary', 1)
                    ->where('customer_id', '!=', $cust_id)
                    ->first();

                if ($exists) {
                    DB::rollBack();
                    return Utility::return_response(false, $phone['phone'] . " already exists.", "", 422);
                }
            }

            if ($primaryCount > 1) {
                DB::rollBack();
                return Utility::return_response(false, "Only one primary phone allowed", "", 422);
            }

            CustomerPhone::where('customer_id', $cust_id)->delete();
            foreach ($request->phones as $phone) {
                CustomerPhone::create([
                    'customer_id'  => $cust_id,
                    'phone'        => $phone['phone'],
                    'is_primary'   => $phone['is_primary'],
                    'is_secondary' => $phone['is_secondary'],
                    'is_whatsapp'  => $phone['is_whatsapp'],
                ]);
            }

            $lead->update([
                'name'         => $customer->name,
                'email'        => $customer->email,
                'sources'      => $request->lead_source,
                'stage_id'     => $request->stage_id ?? $lead->stage_id,
                'notes'        => $request->description ?? $lead->notes,
                'lead_type_id' => $request->lead_type_id,
                'next_contact_date' => $request->next_contact_date,
            ]);

            //won status
            if ($lead->stage_id == "4") {
                $lead->update(['won_date'=>date('Y-m-d')]);
            }


            //lead Activity
            if ($lead['stage_id'] != $request->stage_id) {
                $date = date('Y-m-d H:i:s');
                Utility::add_lead_activity($lead->id, $user->id, 'update lead stage', $date, 'update');
            }


            DB::commit();




            //get latest data
            $all_data = Lead::where('id', $lead->id)->with([
                'customer' => function ($q) {
                    $q->select('id', 'name', 'email');
                },
                'user' => function ($q) {
                    $q->select('id', 'name');
                },
                'stage' => function ($q) {
                    $q->select('id', 'name');
                }

            ])
                ->select(
                    'id',
                    'customer_id',
                    'name',
                    'user_id',
                    'stage_id',
                    'sources',
                    'lead_type_id',
                    'notes',
                    'lead_id',
                    'date'
                )->first();

            $all_data->append('source_list');

            Log::info('------ end edit_lead ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "Lead successfully updated", $all_data, 200);
        } catch (JWTException $e) {
             \Log::info('edit lead error ',[$e->getMessage()]);
            DB::rollBack();
            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        }
    }

    // =========================== Lead Product ===========================================
    public function leadProductList(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'lead_id' => 'required|exists:tenant.leads,id',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

             $user = JWTAuth::parseToken()->authenticate();

            Log::info('------ start leadProductList ------');
            Log::info('Request :-', $request->all());


            $lead_detail = Lead::with([
                'getLeadProduct'=>function ($q)
                {
                    $q->select('id','lead_id','qty','price','product_id','qty');
                },
                 'getLeadProduct.getProduct'=>function ($q)
                {
                    $q->select('id','name','image','sku_code','unit_type','unit');
                },
                 'getLeadProduct.getProduct.getUnit'=>function ($q)
                {
                    $q->select('id','name','type_id');
                },
                 'getLeadProduct.getProduct.getUnitType'=>function ($q)
                {
                    $q->select('id','name');
                }
                ])->where('id',$request->lead_id)->select('id','name','customer_id')->first();



//             $lead_detail = Lead::with([
//     'getLeadProduct.getProduct.getUnit',
//     'getLeadProduct.getProduct.getUnitType'
// ])->select('id','name','customer_id')
//   ->where('id', $request->lead_id)
//   ->first();

/*
|--------------------------------------------------------------------------
| Overwrite lead product price from customer price history
|--------------------------------------------------------------------------
*/
foreach ($lead_detail->getLeadProduct as $leadProduct) {

    $priceHistory = CustomerPriceHistory::where('customer_id', $lead_detail->customer_id)
        ->where('product_id', $leadProduct->product_id)
        ->orderBy('id', 'desc') // latest price
        ->first();

    if ($priceHistory && $priceHistory->price > 0) {


        $price = (float) $priceHistory->price;
        $discount = (float) ($priceHistory->discount ?? 0);

        // Update runtime values
        $leadProduct->price = $price;
        $leadProduct->discount = $discount;
    }
    else
    {
         $leadProduct->discount = 0.00;
    }

}




            Log::info('------ end leadProductList ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, 'Lead products fetched successfully', $lead_detail, 200);
        } catch (JWTException $e) {
            Log::error("Error leadProductList: ",[$e->getMessage()]);
            return Utility::return_response(false, 'Something went wrong.', "", 500);
        }
    }

    public function addLeadProduct(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'lead_id' => 'required|exists:tenant.leads,id',
                'product_list'  => 'required',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = JWTAuth::parseToken()->authenticate();

            Log::info('------ start addLeadProduct ------');
            Log::info('Request :-', $request->all());


            $get_product_list = json_decode($request->product_list, true);

            if (empty($get_product_list) || !is_array($get_product_list)) {
                return Utility::return_response(false, "At least one product is required", "", 422);
            }

            if ($get_product_list) {
                foreach ($get_product_list as $index => $prod) {
                    $requiredFields = ['product_id', 'price', 'qty'];

                    foreach ($requiredFields as $field) {

                        if (!isset($prod[$field]) || $prod[$field] === null || $prod[$field] === '') {
                            return Utility::return_response(
                                false,
                                "{$field} is required at index " . ($index + 1),
                                "",
                                422
                            );
                        }
                    }

                    if (!is_numeric($prod['price']) || $prod['price'] < 0) {
                        return Utility::return_response(false, "Invalid price at index " . ($index + 1), "", 422);
                    }

                    if (!is_int($prod['qty']) || $prod['qty'] <= 0) {
                        return Utility::return_response(false, "qty must be positive integer at index " . ($index + 1), "", 422);
                    }

                    if ($prod['product_id']) {
                        $check_product = Products::where('id', $prod['product_id'])->first();
                        if (!$check_product) {
                            return Utility::return_response(false, "invalid product at index " . ($index + 1), "", 422);
                        }
                    }
                }
            }

            //insert data
            $get_latest_lead = [];

            if (!empty($get_product_list)) {

                $lead = Lead::findOrFail($request->lead_id);

                foreach ($get_product_list as $index => $prod)
                {

                    // check existing record
                    $existing = LeadProducts::where('lead_id', $request->lead_id)
                        ->where('product_id', $prod['product_id'])
                        ->first();

                    if ($existing) {
                        // UPDATE
                        $existing->update([
                            'price' => $prod['price'],
                            'qty'   => $prod['qty'],
                        ]);

                        $get_latest_lead[] = $existing->id;

                    } else {
                        // INSERT
                        $new = LeadProducts::create([
                            'lead_id'    => $request->lead_id,
                            'product_id' => $prod['product_id'],
                            'price'      => $prod['price'],
                            'qty'        => $prod['qty'],
                            'created_by' => $user->creatorId(),
                        ]);

                        $get_latest_lead[] = $new->id;
                    }

                    // update lead products list
                    $existingProducts = $lead->products
                        ? explode(',', $lead->products)
                        : [];

                    $existingProducts[] = $prod['product_id'];

                    $lead->products = implode(',', array_unique($existingProducts));
                    $lead->save();
                }
            }

            $fetch_data = LeadProducts::with([
                'getProduct'=>function ($q)
                {
                    $q->select('id','name','image');
                },
                'getLead'=> function ($q)
                {
                    $q->select('id','name');
                }
            ])->whereIn('id', $get_latest_lead)->select('id','lead_id','product_id','price','qty')->get();

            Log::info('------ end addLeadProduct ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "Lead Products has been added successfully", $fetch_data, 200);
        } catch (JWTException $e) {
             \Log::info('add lead product error ',[$e->getMessage()]);
            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        }
    }

    public function editLeadProduct(Request $request)
    {
         try {

            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:tenant.lead_products,id',
                'product_id'  => 'required|exists:tenant.products,id',
                'price'=>'numeric',
                'qty'=>'numeric'
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = JWTAuth::parseToken()->authenticate();

            Log::info('------ start editLeadProduct ------');
            Log::info('Request :-', $request->all());

            $check_lead_product = LeadProducts::where('id',$request->id)->where('product_id',$request->product_id)->first();
            if(!$check_lead_product)
            {
                return Utility::return_response(false, "lead product not found", "", 404);
            }

            $l_product['qty']=$request->qty;
            $l_product['price']=$request->price;
            $check_lead_product->update($l_product);

            $fetch_data = LeadProducts::with([
                'getProduct'=>function ($q)
                {
                    $q->select('id','name','image');
                },
                'getLead'=> function ($q)
                {
                    $q->select('id','name');
                }
            ])->where('id',$request->id)->select('id','lead_id','product_id','price','qty')->first();

            Log::info('------ end editLeadProduct ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "Lead Products has been updated successfully", $fetch_data, 200);
        } catch (JWTException $e) {
             \Log::info('edit lead product error ',[$e->getMessage()]);
            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        }

    }

    // =========================== Lead Chat ===============================================
    public function listLeadChat(Request $request)
    {
        try {


            $validator = Validator::make($request->all(), [
                'lead_id' => 'required|exists:tenant.leads,id',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }


            Log::info('------ start listLeadChat ------');
            Log::info('Request :-', $request->all());

            $lead = Lead::select(
                'id',
                'name',
                'email',
                'next_contact_date'
            )
                ->where('id', $request->lead_id)
                ->first();

            $leadChatList = LeadChat::with([
                'getLeadStatus'=>function ($q)
                {
                    $q->select('id','name');
                }
            ])->where('lead_id', $request->lead_id)
                ->select('id', 'chat', 'stage_id','next_date')
                ->orderBy('created_at', 'desc')
                ->get();

            $lead_all = [
                'lead'        => $lead,
                'lead_chats' => $leadChatList,
            ];

            Log::info('------ end listLeadChat ------');
            Log::info('------------------------------------------------------------------------------');


            return Utility::return_response(true, "lead chat list.", $lead_all, 200);
        } catch (JWTException $e) {
             \Log::info('lead chat list  error ',[$e->getMessage()]);
            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        }
    }

    public function addLeadChat(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'lead_id' => 'required|exists:tenant.leads,id',
                'stage_id'=> 'required|exists:tenant.lead_stages,id'
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false,$validator->errors()->first(),"",422);
            }

            $user = JWTAuth::parseToken()->authenticate();

            Log::info('------ start addLeadChat ------');
            Log::info('Request :-',$request->all());

            $cht['lead_id']=$request->lead_id;
            $cht['stage_id'] = $request->stage_id;
            $cht['chat'] = $request->message;
            $cht['next_date'] = $request->next_date;
            $cht['created_by'] = $user->id;
            $new_rcd = LeadChat::create($cht);

             if (!empty($request['next_date']))
            {
                $lead_rcd = Lead::where('id', $request->lead_id)->first();
                if ($lead_rcd)
                {
                    if($request['next_date'])
                    {
                        $lead_rcd->update(['next_contact_date' => $request['next_date']]);
                    }
                }
            }


            $new_data = LeadChat::where('id',$new_rcd->id)->with([
                'getLeadDetail'=>function ($q)
                {
                    $q->select('id','name');
                },
                'getLeadStatus'=> function ($q)
                {
                    $q->select('id','name');
                }
            ])->select('id','lead_id','chat','stage_id','next_date')->first();

            Log::info('------ end addLeadChat ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true,"Lead Chat has been added successfully.",$new_data,200);

        } catch (JWTException $e) {
             \Log::info('lead chat list error ',[$e->getMessage()]);
            return Utility::return_response(false,"Token invalid or not provided.","",500);
        }
    }

    // =========================== Get Lead Sources And Stages ==============================
    public function get_lead_sources(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            Log::info('------ start get_lead_sources ------');
            Log::info('Request :-', $request->all());

            $leadSources = DB::table('lead_sources')
                ->select('id', 'name')
                ->where('created_by', $user->creatorId())
                ->orderBy('name', 'asc')
                ->get();

            Log::info('------ end get_lead_sources ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "Lead source list fetched successfully", $leadSources, 200);
        } catch (JWTException $e) {
            \Log::info('get lead source error ',[$e->getMessage()]);
            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        }
    }

    public function get_lead_stages(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            Log::info('------ start get_lead_stages ------');
            Log::info('Request :-', $request->all());

            $leadSources = DB::table('lead_stages')
                ->select('id', 'name', 'color')
                ->where('created_by', $user->creatorId())
                ->orderBy('name', 'asc')
                ->get();

            Log::info('------ end get_lead_stages ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "Lead stages list fetched successfully", $leadSources, 200);
        } catch (JWTException $e) {
             \Log::info('get lead stage error ',[$e->getMessage()]);
            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        }
    }


    //---------------------------- new --------------------------
    public function lead_detail(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'lead_id' => 'required|exists:tenant.leads,id',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = JWTAuth::parseToken()->authenticate();

            Log::info('------ start lead_detail ------');
            Log::info('Request :-', $request->all());

            if ($user->type == 'Sales') {
                $query = Lead::where('id', $request->lead_id)
                    ->select(
                        'id',
                        'customer_id',
                        'name',
                        'email',
                        'user_id',
                        'stage_id',
                        'sources',
                        'lead_type_id',
                        'products',
                        'notes',
                        'lead_id',
                        'won_date',
                        'date',
                        'next_contact_date'
                    );


                $data =
                    $query->with([
                        'customer' => function ($q) {
                            $q->select(
                                'id',
                                'name',
                                'email',
                                'company_name',
                                'lead_type_id',
                                'gst_no',
                                'company_adhar_no',
                                'company_udhyam_no',
                                'billing_address_id',
                                'shipping_address_id',
                                'rate',
                                'description',
                                'specification'
                            )
                                ->with([
                                    'getBillingAddress' => function ($add) {
                                        $add->select(
                                            'id',
                                            'address_line_1',
                                            'address_line_2',
                                            'country',
                                            'state',
                                            'city',
                                            'zipcode'
                                        )
                                            ->with([
                                                'get_country:id,name',
                                                'get_state:id,name',
                                                'get_city:id,name'
                                            ]);
                                    },

                                    'getShippingAddress' => function ($add) {
                                        $add->select(
                                            'id',
                                            'address_line_1',
                                            'address_line_2',
                                            'country',
                                            'state',
                                            'city',
                                            'zipcode'
                                        )
                                            ->with([
                                                'get_country:id,name',
                                                'get_state:id,name',
                                                'get_city:id,name'
                                            ]);
                                    },
                                ]);
                        },

                        'user' => function ($q) {
                            $q->select('id', 'name');
                        },
                        'product' => function ($q) {
                            $q->select('products.id', 'products.name', 'products.image', 'products.sku_code', 'products.price')
                                ->withPivot(['id', 'price', 'qty', 'unit_id']);
                        },
                        'stage',
                        'getLeadChat',
                        'getCustomerAllPhone',
                        'getLeadCall',
                        'getQuoteAll' => function ($q) {
                            $q->select('id', 'lead_id', 'code', 'date', 'customer_id')
                                ->with([
                                    'customer' => function ($q) {
                                        $q->select('id', 'name', 'email');
                                    }
                                ]);
                        }

                    ])->first();
                $data->append('source_list');
            } else {

                return Utility::return_response(false, "Access Denied.", "", 422);
            }


            Log::info('------ end lead_detail ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "Lead Detail.", $data, 200);
        } catch (\Exception $e) {
            Log::error("Error lead_detail: ",[$e->getMessage()]);
            return Utility::return_response(false, "Something went wrong.", "", 500);
        }
    }

    public function lead_duplicate(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'lead_id' => 'required|exists:tenant.leads,id',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false,$validator->errors()->first(),"",422);
            }

            $user = JWTAuth::parseToken()->authenticate();

            Log::info('------ start lead_duplicate ------');
            Log::info('Request :-',$request->all());

            $lead =  Lead::where('id',$request->lead_id)->first();

            $newLead = DB::transaction(function () use ($lead, $user) {
                $inp['customer_id'] = $lead['customer_id'];
                $inp['name'] = $lead['name'];
                $inp['email'] = $lead['email'];
                $inp['phone'] = $lead['phone'];
                $inp['gst_no'] = $lead['gst_no'];
                $inp['user_id'] = $lead['user_id'];
                $inp['stage_id'] = 1;
                $inp['sources'] = $lead['sources'];
                $inp['products'] = $lead['products'];
                $inp['notes'] = $lead['notes'];
                $inp['labels'] = $lead['labels'];
                $inp['order'] = $lead['order'];
                $inp['created_by'] = $lead['created_by'];
                $inp['is_active'] = $lead['is_active'];
                $inp['is_converted'] = $lead['is_converted'];
                $inp['date'] = date('Y-m-d');
                $inp['is_duplicate'] = 1;
                $inp['from_lead_id'] = $lead['id'];
                $inp['lead_type_id'] = $lead['lead_type_id'];
                $newLead = Lead::create($inp);

                $leadProducts = LeadProducts::where('lead_id', $lead['id'])->get();
                foreach ($leadProducts as $lp) {
                    LeadProducts::create([
                        'lead_id' => $newLead['id'],
                        'product_id' => $lp['product_id'],
                        'price' => $lp['price'],
                        'qty' => $lp['qty'],
                        'unit_id' => $lp['unit_id'],
                        'created_by' => $lp['created_by'],
                    ]);
                }

                $assignedUserIds = UserLead::where('lead_id', $lead['id'])
                    ->pluck('user_id')
                    ->filter()
                    ->unique();

                foreach ($assignedUserIds as $assignedUserId) {
                    UserLead::create([
                        'user_id' => $assignedUserId,
                        'lead_id' => $newLead->id,
                    ]);
                }

                $date = date('Y-m-d H:i:s');
                Utility::add_lead_activity($newLead->id, $user->id, 'add duplicate lead detail', $date, 'duplicate');

                return $newLead;
            });

            //get latest data
            $all_data = Lead::where('id', $newLead->id)->with([
                'customer' => function ($q) {
                    $q->select('id', 'name', 'email');
                },
                'user' => function ($q) {
                    $q->select('id', 'name');
                },
                'stage' => function ($q) {
                    $q->select('id', 'name');
                }

            ])
                ->select(
                    'id',
                    'customer_id',
                    'name',
                    'user_id',
                    'stage_id',
                    'sources',
                    'lead_type_id',
                    'notes',
                    'lead_id',
                    'date'
                )->first();

            $all_data->append('source_list');


            Log::info('------ end lead_duplicate ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true,"duplicate lead has been added successfully.",$all_data,200);

        } catch (JWTException $e) {
            Log::info('lead_duplicate error ',[$e->getMessage()]);
            return Utility::return_response(false,"Token invalid or not provided.","",500);
        }
    }

}
