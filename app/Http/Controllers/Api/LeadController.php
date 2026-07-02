<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessLeadUpload;
use App\Models\Address;
use App\Models\CustomerPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Cache;
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
use App\Models\LeadCall;
use App\Models\User;
use App\Services\ActivityLogger;

class LeadController extends Controller
{
    private function writeLeadActivity(string $action, string $eventKey, Lead $lead, string $description, array $properties = []): void
    {
        ActivityLogger::writeFor('leads', $action, $lead, null, [
            'event_key' => $eventKey,
            'description' => $description,
            'properties' => $properties,
        ]);
    }

    private function resolveLeadStageName(?int $stageId): ?string
    {
        if (!$stageId) {
            return null;
        }

        return LeadStage::withTrashed()->where('id', $stageId)->value('name');
    }

    public function list_lead(Request $request)
    {
        try
        {
            Log::info('------ start list_lead ------');
            Log::info('Request :-', $request->all());


            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

              $validator = Validator::make($request->all(), [
                'list_type' => 'required',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false,$validator->errors()->first(),"",422);
            }

            // $leads = DB::table('leads')
            //     ->join('entities', 'entities.id', '=', 'leads.customer_id')
            //     ->leftJoin('addresses', 'addresses.id', '=', 'entities.billing_address_id')
            //     ->leftJoin('states', 'states.id', '=', 'addresses.state')
            //     ->leftJoin('cities', 'cities.id', '=', 'addresses.city')
            //     ->leftJoin('customer_phones', function ($join) {
            //         $join->on('customer_phones.customer_id', '=', 'entities.id')
            //             ->where('customer_phones.is_primary', 1);
            //     })
            //     ->leftJoin('lead_types', 'lead_types.id', '=', 'leads.lead_type_id')
            //     ->leftJoin('lead_stages', 'lead_stages.id', '=', 'leads.stage_id')
            //     ->where('leads.user_id', $user->id)
            //     ->select([
            //         'leads.id',
            //         'leads.date',
            //         'entities.name as customer_name',
            //         'entities.email',
            //         'states.name as billing_state',
            //         'cities.name as billing_city',
            //         'customer_phones.phone',
            //         'lead_types.id as lead_type_id',
            //         'lead_types.name as lead_type',
            //         'lead_stages.id as stage_id',
            //         'lead_stages.name as stage',
            //         'leads.sources',
            //          'leads.customer_id',
            //     ]);



            // $data = $leads->orderBy('id', 'desc')->get();

            // foreach ($data as $itm)
            // {
            //     $itm->date = $itm->date ? Utility::getDateFormated($itm->date) : '';

            //     $itm->sources_list = $itm->sources ? LeadSource::whereIn('id', explode(',', $itm->sources))->select('id', 'name')->get() : [];


            // }

             $leads = $this->scopedLeadListQuery($user, $request->input('list_type'));

            if($request->filled('source_ids'))
            {
                $sourceIds = $this->normalizeFilterIds($request->source_ids);

                $leads->where(function ($q) use ($sourceIds) {
                    foreach ($sourceIds as $sourceId) {
                        $q->orWhereRaw('FIND_IN_SET(?, sources)', [$sourceId]);
                    }
                });
            }
            if($request->filled('product_ids'))
            {
                 $productIds = $this->normalizeFilterIds($request->product_ids);


                $leads->where(function ($q) use ($productIds) {
                    foreach ($productIds as $productId) {
                        $q->orWhereRaw('FIND_IN_SET(?, products)', [$productId]);
                    }
                });

            }
            if($request->status)
            {
                $leads->where('stage_id',$request->status);
            }
             if($request->lead_type_id)
            {
                $leads->where('lead_type_id',$request->lead_type_id);
            }

            if($request->date)
            {
                $leads->whereDate('date', $request->date);
            }

            $leads = $leads->orderBy('id', 'desc')->get();




            foreach ($leads as $itm) {
                $stageRelation = $itm->getRelation('stage');

                $itm->date_original =  $itm->date;
                $itm->date = $itm->date
                    ? Utility::getDateFormated($itm->date)
                    : '';

                // Source list (using accessor)
                $itm->sources_list = $itm->source_list;

                // Customer
                $itm->customer = $itm->customer ?? null;

                // Billing State & City (safe)
                $itm->billing_state = optional(
                    optional($itm->customer?->getBillingAddress)->get_state
                )->name;

                $itm->billing_city = optional(
                    optional($itm->customer?->getBillingAddress)->get_city
                )->name;

                // Primary Phone
                $itm->phone = optional($itm->customerPhone)->phone;

                $itm->customer_name = $itm->customer ? $itm->customer?->name : "";
                $itm->email = $itm->customer ? $itm->customer?->email : "";
                $itm->name = $itm->customer ? $itm->customer?->name : "";
                $itm->lead_type = $itm->getLeadType?->name ? $itm->getLeadType?->name : "";
                $itm->stage_id = (int) ($itm->stage_id ?? $stageRelation?->id ?? 0);
                $itm->stage = $stageRelation?->name ?? "";

            }

            // return count($leads);

            Log::info('------ end list_lead ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true, "Lead list fetched successfully", $leads, 200);
        } catch (JWTException $e) {
              \Log::info('lead list error ',[$e->getMessage()]);
            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        } catch (\Throwable $e) {
            \Log::info('lead list error ', [$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
        }
    }

    private function scopedLeadListQuery(User $user, ?string $listType = null)
    {
        $query = Lead::with([
            'customer:id,name,email,billing_address_id,created_by,user_id',
            'customer.getBillingAddress:id,state,city',
            'customer.getBillingAddress.get_state:id,name',
            'customer.getBillingAddress.get_city:id,name',
            'customerPhone:id,customer_id,phone,is_primary',
            'getLeadType:id,name',
            'stage:id,name',
        ])->where('created_by', $user->creatorId());

        if ($user->type === 'company') {
            return $query;
        }

        $assignedLeadIds = UserLead::where('user_id', $user->id)->pluck('lead_id');
        $listType = strtolower((string) $listType);

        return $query->where(function ($leadQuery) use ($user, $assignedLeadIds, $listType) {
            $leadQuery->where('user_id', $user->id);

            if ($assignedLeadIds->isNotEmpty()) {
                $leadQuery->orWhereIn('id', $assignedLeadIds);
            }
            if ($listType !== 'my') {
                $leadQuery->orWhereNull('user_id');
            }
        });
    }

    private function normalizeFilterIds($value): array
    {
        $values = is_array($value) ? $value : explode(',', (string) $value);

        return collect($values)
            ->map(fn ($item) => trim((string) $item))
            ->filter(fn ($item) => $item !== '' && ctype_digit($item))
            ->values()
            ->all();
    }

    public function add_lead(Request $request)
    {

        try {

			Log::info('------ start add_lead ------');
            Log::info('Request :-', $request->all());

            $validator = Validator::make($request->all(), [
                'name'         => 'required|string',
                'email'        => 'nullable|email',
                'lead_type_id' => 'nullable|exists:tenant.lead_types,id',
                'lead_source'  => 'required',
                'phones'       => 'required',
                'customer_id'  => 'nullable|integer',
                'stage_id'     => 'nullable|exists:tenant.lead_stages,id',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            if($request->lead_source)
            {
                $ids = $this->normalizeFilterIds($request->lead_source);

                if (empty($ids)) {
                    return Utility::return_response(false, "At least one valid lead source is required", "", 422);
                }

                foreach ($ids as $id)
                {
                    if (!LeadSource::where('id', $id)->exists()) {
                        return Utility::return_response(false, "Lead source ID $id does not exist", "", 422);
                    }
                }

                $request->merge(['lead_source' => implode(',', $ids)]);
            }


            //product validation
            //lead_products checks
            $get_product_list = is_string($request->product_list)
                ? json_decode($request->product_list, true)
                : $request->product_list;

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

                    if (filter_var($prod['qty'], FILTER_VALIDATE_INT) === false || (int) $prod['qty'] <= 0) {
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

            $connection = $this->tenantConnectionName();
            DB::connection($connection)->beginTransaction();

            try {
                if (!empty($request->customer_id) && (int) $request->customer_id !== 0) {
                    $customer = Entity::where('id', $request->customer_id)
                        ->where('type', 'customer')
                        ->where('created_by', $user->creatorId())
                        ->first();

                    if (!$customer) {
                        DB::connection($connection)->rollBack();
                        return Utility::return_response(false, "customer not found", "", 422);
                    }

                    if ($user->type !== 'company') {
                        if ($customer->user_id === null) {
                            $customer->user_id = $user->id;
                            $customer->save();
                        } elseif ((int) $customer->user_id !== (int) $user->id) {
                            $assignedUser = User::where('id', $customer->user_id)->first();
                            DB::connection($connection)->rollBack();
                            return Utility::return_response(false, "customer already assign to " . ($assignedUser->name ?? 'another user'), "", 422);
                        }
                    }
                } else {
                    $customer = Entity::create([
                        'name'       => $request->name,
                        'email'      => $request->email,
                        'type'       => 'customer',
                        'created_by' => $user->creatorId(),
                        'company_name' => $request->company_name ?: $request->name,
                        'user_id'    => $user->type === 'company' ? null : $user->id,
                        'lead_type_id' => $request->lead_type_id,
                    ]);
                }

                $cust_id = $customer->id;

                if ($request->billing_country) {
                    $addressData = [
                        'country'        => $request->billing_country,
                        'state'          => $request->billing_state,
                        'city'           => $request->billing_city,
                        'zipcode'        => $request->billing_zipcode,
                        'address_line_1' => $request->billing_address_line_1,
                        'address_line_2' => $request->billing_address_line_2 ?? null,
                    ];

                    if ($customer->billing_address_id) {
                        $billing = Address::find($customer->billing_address_id);
                        if ($billing) {
                            $billing->update($addressData);
                        }
                    } else {
                        $billing = Address::create($addressData);
                        $customer->billing_address_id = $billing->id;
                    }

                    if ($customer->shipping_address_id) {
                        $shipping = Address::find($customer->shipping_address_id);
                        if ($shipping) {
                            $shipping->update($addressData);
                        }
                    } else {
                        $shipping = Address::create($addressData);
                        $customer->shipping_address_id = $shipping->id;
                    }

                    $customer->save();
                }

                $primaryCount = 0;

                foreach ($request->phones as $index => $phone) {
                    if (empty($phone['phone'])) {
                        DB::connection($connection)->rollBack();
                        return Utility::return_response(false, "Phone is required at index " . ($index + 1), "", 422);
                    }

                    if (!preg_match('/^[0-9]{10}$/', $phone['phone'])) {
                        DB::connection($connection)->rollBack();
                        return Utility::return_response(false, "Invalid phone at index " . ($index + 1), "", 422);
                    }

                    foreach (['is_primary', 'is_secondary', 'is_whatsapp'] as $field) {
                        if (!isset($phone[$field]) || !in_array((int) $phone[$field], [0, 1], true)) {
                            DB::connection($connection)->rollBack();
                            return Utility::return_response(false, "Invalid {$field} at index " . ($index + 1), "", 422);
                        }
                    }

                    if ((int) $phone['is_primary'] === 1 && (int) $phone['is_secondary'] === 1) {
                        DB::connection($connection)->rollBack();
                        return Utility::return_response(false, "Primary and Secondary both cannot be 1 at index " . ($index + 1), "", 422);
                    }

                    if ((int) $phone['is_primary'] === 0 && (int) $phone['is_secondary'] === 0) {
                        DB::connection($connection)->rollBack();
                        return Utility::return_response(false, "Set either primary or secondary at index " . ($index + 1), "", 422);
                    }

                    if ((int) $phone['is_primary'] === 1) {
                        $primaryCount++;
                    }

                    $exists = CustomerPhone::where('phone', $phone['phone'])
                        ->where('is_primary', 1)
                        ->where('customer_id', '!=', $cust_id)
                        ->first();

                    if ($exists) {
                        $duplicateCustomer = Entity::where('id', $exists->customer_id)
                            ->where('type', 'customer')
                            ->first();

                        if (!$duplicateCustomer || (int) $duplicateCustomer->created_by !== (int) $user->creatorId()) {
                            DB::connection($connection)->rollBack();
                            return Utility::return_response(false, $phone['phone'] . " already exists", "", 422);
                        }

                        if ($user->type !== 'company' && $duplicateCustomer->user_id === null) {
                            $duplicateCustomer->user_id = $user->id;
                            $duplicateCustomer->save();
                        }

                        $duplicateUser = $duplicateCustomer->user_id ? User::where('id', $duplicateCustomer->user_id)->first() : null;
                        if ($duplicateUser && (int) $duplicateCustomer->user_id !== (int) $user->id) {
                            DB::connection($connection)->rollBack();
                            return Utility::return_response(false, $phone['phone'] . " already exists & assign to " . $duplicateUser->name, "", 422);
                        }
                    }
                }

                if ($primaryCount > 1) {
                    DB::connection($connection)->rollBack();
                    return Utility::return_response(false, "Only one primary phone allowed", "", 422);
                }

                if ($primaryCount < 1) {
                    DB::connection($connection)->rollBack();
                    return Utility::return_response(false, "one primary phone required", "", 422);
                }

                foreach ($request->phones as $phone) {
                    CustomerPhone::updateOrCreate(
                        [
                            'customer_id' => $cust_id,
                            'phone'       => $phone['phone'],
                        ],
                        [
                            'is_primary'   => (int) ($phone['is_primary'] ?? 0),
                            'is_secondary' => (int) ($phone['is_secondary'] ?? 0),
                            'is_whatsapp'  => (int) ($phone['is_whatsapp'] ?? 0),
                        ]
                    );
                }

                $lead = Lead::create([
                    'name'         => $customer->name,
                    'email'        => $customer->email,
                    'user_id'      => $user->type === 'company' ? null : $user->id,
                    'sources'      => $request->lead_source,
                    'stage_id'     => $request->stage_id ?? $stage->id,
                    'notes'        => $request->description ?? null,
                    'created_by'   => $user->creatorId(),
                    'date'         => now()->format('Y-m-d'),
                    'customer_id'  => $cust_id,
                    'lead_type_id' => $request->lead_type_id,
                ]);

                $existingProducts = [];
                if (!empty($get_product_list)) {
                    foreach ($get_product_list as $prod) {
                        LeadProducts::updateOrCreate(
                            [
                                'lead_id'    => $lead->id,
                                'product_id' => $prod['product_id'],
                            ],
                            [
                                'price'      => $prod['price'],
                                'qty'        => (int) $prod['qty'],
                                'created_by' => $user->creatorId(),
                            ]
                        );

                        $existingProducts[] = $prod['product_id'];
                    }

                    $lead->products = implode(',', array_unique($existingProducts));
                    $lead->save();
                }

                Utility::add_lead_activity(
                    $lead->id,
                    $user->id,
                    'add lead detail',
                    now(),
                    'add'
                );

                $this->writeLeadActivity(
                    'create',
                    'lead.created',
                    $lead,
                    'Lead created.',
                    [
                        'customer_id' => $lead->customer_id,
                        'stage_id' => $lead->stage_id,
                        'stage_name' => $this->resolveLeadStageName((int) $lead->stage_id),
                        'lead_type_id' => $lead->lead_type_id,
                        'assigned_user_id' => $lead->user_id,
                    ]
                );

                DB::connection($connection)->commit();

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
            } catch (\Throwable $th) {
                DB::connection($connection)->rollBack();
                throw $th;
            }

            $all_data->append('source_list');

            Log::info('------ end add_lead ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "Lead successfully created", $all_data, 200);
        } catch (\Throwable $e) {
            \Log::info('add lead error ',[$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
        }
    }

    public function edit_lead(Request $request)
    {
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

            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            Log::info('------ start edit_lead ------');
            Log::info('Request :-', $request->all());

            //lead sources check
            if($request->lead_source)
            {
                $ids = $this->normalizeFilterIds($request->lead_source);

                if (empty($ids)) {
                    return Utility::return_response(false, "At least one valid lead source is required", "", 422);
                }

                foreach ($ids as $id)
                {
                    if (!LeadSource::where('id', $id)->exists()) {
                        return Utility::return_response(false, "Lead source ID $id does not exist", "", 422);
                    }
                }

                $request->merge(['lead_source' => implode(',', $ids)]);
            }

            //lead_products checks
            $get_product_list = is_string($request->product_list)
                ? json_decode($request->product_list, true)
                : $request->product_list;

			// Log::info('Products :' . print_r($get_product_list));

            /* if (empty($get_product_list) || !is_array($get_product_list)) {
                return Utility::return_response(false, "At least one product is required", "", 422);
            } */

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

                    if (filter_var($prod['qty'], FILTER_VALIDATE_INT) === false || (int) $prod['qty'] <= 0) {
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
            $connection = $this->tenantConnectionName();
            DB::connection($connection)->beginTransaction();

            try {
                $lead = $this->scopedLeadMutationQuery($user)
                    ->where('id', $request->lead_id)
                    ->first();

                if (!$lead) {
                    DB::connection($connection)->rollBack();
                    return Utility::return_response(false, "Lead not found.", "", 404);
                }

                $customer = Entity::where('id', $lead->customer_id)
                    ->where('type', 'customer')
                    ->where('created_by', $user->creatorId())
                    ->first();

                if (!$customer) {
                    DB::connection($connection)->rollBack();
                    return Utility::return_response(false, "customer not found", "", 422);
                }

                if ($user->type !== 'company') {
                    if ($customer->user_id === null) {
                        $customer->user_id = $user->id;
                        $customer->save();
                    } elseif ((int) $customer->user_id !== (int) $user->id) {
                        $assignedUser = User::where('id', $customer->user_id)->first();
                        DB::connection($connection)->rollBack();
                        return Utility::return_response(false, "customer already assign to " . ($assignedUser->name ?? 'another user'), "", 422);
                    }
                }

                $cust_id = $customer->id;

                $customer->update([
                    'name'  => $request->name,
                    'email' => $request->email,
                    'lead_type_id' => $request->lead_type_id,
                ]);

                if ($request->billing_country) {
                    $addressData = [
                        'country'        => $request->billing_country,
                        'state'          => $request->billing_state,
                        'city'           => $request->billing_city,
                        'zipcode'        => $request->billing_zipcode,
                        'address_line_1' => $request->billing_address_line_1,
                        'address_line_2' => $request->billing_address_line_2 ?? null,
                    ];

                    if ($customer->billing_address_id) {
                        $billing = Address::find($customer->billing_address_id);
                        if ($billing) {
                            $billing->update($addressData);
                        }
                    } else {
                        $billing = Address::create($addressData);
                        $customer->billing_address_id = $billing->id;
                    }

                    if ($customer->shipping_address_id) {
                        $shipping = Address::find($customer->shipping_address_id);
                        if ($shipping) {
                            $shipping->update($addressData);
                        }
                    } else {
                        $shipping = Address::create($addressData);
                        $customer->shipping_address_id = $shipping->id;
                    }

                    $customer->save();
                }

                $primaryCount = 0;
                foreach ($request->phones as $index => $phone) {
                    if (empty($phone['phone'])) {
                        DB::connection($connection)->rollBack();
                        return Utility::return_response(false, "Phone is required at index " . ($index + 1), "", 422);
                    }

                    if (!preg_match('/^[0-9]{10}$/', $phone['phone'])) {
                        DB::connection($connection)->rollBack();
                        return Utility::return_response(false, "Invalid phone at index " . ($index + 1), "", 422);
                    }

                    foreach (['is_primary', 'is_secondary', 'is_whatsapp'] as $field) {
                        if (!isset($phone[$field]) || !in_array((int) $phone[$field], [0, 1], true)) {
                            DB::connection($connection)->rollBack();
                            return Utility::return_response(false, "Invalid {$field} at index " . ($index + 1), "", 422);
                        }
                    }

                    if ((int) $phone['is_primary'] === 1 && (int) $phone['is_secondary'] === 1) {
                        DB::connection($connection)->rollBack();
                        return Utility::return_response(false, "Primary and Secondary both cannot be 1 at index " . ($index + 1), "", 422);
                    }

                    if ((int) $phone['is_primary'] === 0 && (int) $phone['is_secondary'] === 0) {
                        DB::connection($connection)->rollBack();
                        return Utility::return_response(false, "Set either primary or secondary at index " . ($index + 1), "", 422);
                    }

                    if ((int) $phone['is_primary'] === 1) {
                        $primaryCount++;
                    }

                    $exists = CustomerPhone::where('phone', $phone['phone'])
                        ->where('is_primary', 1)
                        ->where('customer_id', '!=', $cust_id)
                        ->first();

                    if ($exists) {
                        $duplicateCustomer = Entity::where('id', $exists->customer_id)
                            ->where('type', 'customer')
                            ->first();

                        if (!$duplicateCustomer || (int) $duplicateCustomer->created_by !== (int) $user->creatorId()) {
                            DB::connection($connection)->rollBack();
                            return Utility::return_response(false, $phone['phone'] . " already exists", "", 422);
                        }

                        if ($user->type !== 'company' && $duplicateCustomer->user_id === null) {
                            $duplicateCustomer->user_id = $user->id;
                            $duplicateCustomer->save();
                        }

                        $duplicateUser = $duplicateCustomer->user_id ? User::where('id', $duplicateCustomer->user_id)->first() : null;
                        if ($duplicateUser && (int) $duplicateCustomer->user_id !== (int) $user->id) {
                            DB::connection($connection)->rollBack();
                            return Utility::return_response(false, $phone['phone'] . " already exists & assign to " . $duplicateUser->name, "", 422);
                        }
                    }
                }

                if ($primaryCount > 1) {
                    DB::connection($connection)->rollBack();
                    return Utility::return_response(false, "Only one primary phone allowed", "", 422);
                }

                if ($primaryCount < 1) {
                    DB::connection($connection)->rollBack();
                    return Utility::return_response(false, "one primary phone required", "", 422);
                }

                CustomerPhone::where('customer_id', $cust_id)->delete();
                foreach ($request->phones as $phone) {
                    CustomerPhone::updateOrCreate(
                        [
                            'customer_id' => $cust_id,
                            'phone'       => $phone['phone'],
                        ],
                        [
                            'is_primary'   => (int) ($phone['is_primary'] ?? 0),
                            'is_secondary' => (int) ($phone['is_secondary'] ?? 0),
                            'is_whatsapp'  => (int) ($phone['is_whatsapp'] ?? 0),
                        ]
                    );
                }

                $leadBefore = [
                    'name' => $lead->name,
                    'email' => $lead->email,
                    'sources' => $lead->sources,
                    'notes' => $lead->notes,
                    'lead_type_id' => $lead->lead_type_id,
                    'next_contact_date' => $lead->next_contact_date,
                    'products' => $lead->products,
                ];
                $previousStageId = (int) ($lead->stage_id ?? 0);
                $lead->update([
                    'name'         => $customer->name,
                    'email'        => $customer->email,
                    'sources'      => $request->lead_source,
                    'stage_id'     => $request->stage_id ?? $lead->stage_id,
                    'notes'        => $request->description ?? $lead->notes,
                    'lead_type_id' => $request->lead_type_id,
                    'next_contact_date' => $request->filled('next_contact_date')
                        ? date('Y-m-d', strtotime($request->next_contact_date))
                        : $lead->next_contact_date,
                ]);

                if ((string) $lead->stage_id === "4") {
                    $lead->update(['won_date' => date('Y-m-d')]);
                }

                if (!empty($get_product_list)) {
                    $existingProducts = [];
                    foreach ($get_product_list as $prod) {
                        LeadProducts::updateOrCreate(
                            [
                                'lead_id'    => $lead->id,
                                'product_id' => $prod['product_id'],
                            ],
                            [
                                'price'      => $prod['price'],
                                'qty'        => (int) $prod['qty'],
                                'created_by' => $user->creatorId(),
                            ]
                        );

                        $existingProducts[] = $prod['product_id'];
                    }

                    $lead->products = implode(',', array_unique($existingProducts));
                    $lead->save();
                }

                $leadAfter = [
                    'name' => $lead->name,
                    'email' => $lead->email,
                    'sources' => $lead->sources,
                    'notes' => $lead->notes,
                    'lead_type_id' => $lead->lead_type_id,
                    'next_contact_date' => $lead->next_contact_date,
                    'products' => $lead->products,
                ];
                $leadChanges = ActivityLogger::diff($leadBefore, $leadAfter);

                if (!empty($leadChanges)) {
                    $this->writeLeadActivity(
                        'update',
                        'lead.updated',
                        $lead,
                        'Lead details updated.',
                        ['changes' => $leadChanges]
                    );
                }

                if ((int) $previousStageId !== (int) ($request->stage_id ?? $previousStageId)) {
                    Utility::add_lead_activity($lead->id, $user->id, 'update lead stage', date('Y-m-d H:i:s'), 'update');
                    $this->writeLeadActivity(
                        'change_status',
                        'lead.stage_changed',
                        $lead,
                        'Lead stage changed.',
                        [
                            'before' => [
                                'stage_id' => $previousStageId,
                                'stage_name' => $this->resolveLeadStageName($previousStageId),
                            ],
                            'after' => [
                                'stage_id' => (int) $lead->stage_id,
                                'stage_name' => $this->resolveLeadStageName((int) $lead->stage_id),
                            ],
                        ]
                    );
                }

                DB::connection($connection)->commit();
            } catch (\Throwable $th) {
                DB::connection($connection)->rollBack();
                throw $th;
            }




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
        } catch (\Throwable $e) {
             \Log::info('edit lead error ',[$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
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

             $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            Log::info('------ start leadProductList ------');
            Log::info('Request :-', $request->all());


            $lead_detail = $this->scopedLeadDetailQuery($user)->with([
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

                if (!$lead_detail) {
                    return Utility::return_response(false, "Lead not found.", "", 404);
                }



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

            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            Log::info('------ start addLeadProduct ------');
            Log::info('Request :-', $request->all());


            $get_product_list = is_string($request->product_list)
                ? json_decode($request->product_list, true)
                : $request->product_list;

            if (empty($get_product_list) || !is_array($get_product_list)) {
                return Utility::return_response(false, "At least one product is required", "", 422);
            }

            $lead = $this->scopedLeadMutationQuery($user)->where('id', $request->lead_id)->first();
            if (!$lead) {
                return Utility::return_response(false, "Lead not found.", "", 404);
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

                    if (filter_var($prod['qty'], FILTER_VALIDATE_INT) === false || (int) $prod['qty'] <= 0) {
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
                DB::connection($this->tenantConnectionName())->transaction(function () use ($get_product_list, $request, $user, $lead, &$get_latest_lead) {
                    foreach ($get_product_list as $prod) {
                        $existing = LeadProducts::where('lead_id', $lead->id)
                            ->where('product_id', $prod['product_id'])
                            ->first();

                        if ($existing) {
                            $existing->update([
                                'price' => $prod['price'],
                                'qty'   => (int) $prod['qty'],
                            ]);

                            $get_latest_lead[] = $existing->id;
                        } else {
                            $new = LeadProducts::create([
                                'lead_id'    => $lead->id,
                                'product_id' => $prod['product_id'],
                                'price'      => $prod['price'],
                                'qty'        => (int) $prod['qty'],
                                'created_by' => $user->creatorId(),
                            ]);

                            $get_latest_lead[] = $new->id;
                        }

                        $existingProducts = $lead->products
                            ? explode(',', $lead->products)
                            : [];

                        $existingProducts[] = $prod['product_id'];

                        $lead->products = implode(',', array_unique($existingProducts));
                        $lead->save();
                    }
                });
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
        } catch (\Throwable $e) {
             \Log::info('add lead product error ',[$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
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

            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            Log::info('------ start editLeadProduct ------');
            Log::info('Request :-', $request->all());

            $check_lead_product = LeadProducts::with('getLead')
                ->where('id',$request->id)
                ->where('product_id',$request->product_id)
                ->first();
            if(!$check_lead_product)
            {
                return Utility::return_response(false, "lead product not found", "", 404);
            }

            $lead = $check_lead_product->getLead;
            if (!$lead || !$this->scopedLeadMutationQuery($user)->where('id', $lead->id)->exists()) {
                return Utility::return_response(false, "Lead not found.", "", 404);
            }

            $l_product = [];
            if ($request->filled('qty')) {
                if (filter_var($request->qty, FILTER_VALIDATE_INT) === false || (int) $request->qty <= 0) {
                    return Utility::return_response(false, "qty must be positive integer", "", 422);
                }
                $l_product['qty'] = (int) $request->qty;
            }
            if ($request->filled('price')) {
                if (!is_numeric($request->price) || (float) $request->price < 0) {
                    return Utility::return_response(false, "Invalid price", "", 422);
                }
                $l_product['price'] = $request->price;
            }

            if (empty($l_product)) {
                return Utility::return_response(false, "Nothing to update.", "", 422);
            }

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
        } catch (\Throwable $e) {
             \Log::info('edit lead product error ',[$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
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
            $cht['chat'] = $request->chat;
            $cht['next_date'] = $request->next_date;
            $cht['created_by'] = $user->id;
            $new_rcd = LeadChat::create($cht);

            if($request->stage_id == 4)
            {
                $ld['won_date'] = date('Y-m-d');
            }
            $ld['stage_id'] = $request->stage_id;
            $lead_rcd = Lead::where('id',$request->lead_id)->first();
            $lead_rcd->update($ld);

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

            $lead = Lead::where('id', $request->lead_id)->first();
            if ($lead) {
                $this->writeLeadActivity(
                    'update',
                    'lead.followup_added',
                    $lead,
                    'Lead follow-up added.',
                    [
                        'followup_id' => $new_rcd->id,
                        'message' => $new_rcd->chat,
                        'next_date' => $new_rcd->next_date,
                        'stage_id' => $new_rcd->stage_id,
                        'stage_name' => $this->resolveLeadStageName((int) $new_rcd->stage_id),
                    ]
                );
            }

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
            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            Log::info('------ start get_lead_sources ------');
            Log::info('Request :-', $request->all());

            $leadSources = LeadSource::query()
                ->select('id', 'name')
                ->where(function ($query) use ($user) {
                    $query->where('created_by', $user->creatorId());

                    if (config('tenancy.enabled', false) && app()->bound('currentTenant')) {
                        $query->orWhereNull('created_by');
                    }
                })
                ->orderBy('order', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            Log::info('------ end get_lead_sources ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "Lead source list fetched successfully", $leadSources, 200);
        } catch (\Throwable $e) {
            \Log::info('get lead source error ',[$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
        }
    }

    public function get_lead_stages(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            Log::info('------ start get_lead_stages ------');
            Log::info('Request :-', $request->all());

            $leadStages = LeadStage::query()
                ->select('id', 'name', 'color')
                ->where(function ($query) use ($user) {
                    $query->where('created_by', $user->creatorId());

                    if (config('tenancy.enabled', false) && app()->bound('currentTenant')) {
                        $query->orWhereNull('created_by');
                    }
                })
                ->orderBy('order', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            Log::info('------ end get_lead_stages ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "Lead stages list fetched successfully", $leadStages, 200);
        } catch (\Throwable $e) {
             \Log::info('get lead stage error ',[$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
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

            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            Log::info('------ start lead_detail ------');
            Log::info('Request :-', $request->all());

            $data = $this->scopedLeadDetailQuery($user)
                ->where('id', $request->lead_id)
                ->first();

            if (!$data) {
                return Utility::return_response(false, "Lead not found.", "", 404);
            }

            $data->append('source_list');

            $get_lead_products_rcd = LeadProducts::with([
                'getProduct:id,name,image,sku_code,hsn_code,category_id,unit_type,price,unit',
                'getUnit:id,name',
                'getProduct.getUnitType:id,name',
                'getProduct.getUnit:id,name',
            ])->where('lead_id', $data->id)->orderBy('id', 'desc')->get();

            if ($get_lead_products_rcd->count() > 0) {
                foreach ($get_lead_products_rcd as $itm_product) {
                    $customer_price_rcd = CustomerPriceHistory::where('product_id', $itm_product->product_id)
                        ->where('customer_id', $data->customer_id)
                        ->first();
                    $itm_product['discount'] = (int) ($customer_price_rcd?->discount ?? 0);
                    $itm_product['customer_previous_price'] = (int) ($customer_price_rcd?->price ?? 0);
                }
            }

            $data['get_lead_products'] = $get_lead_products_rcd;


            Log::info('------ end lead_detail ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "Lead Detail.", $data, 200);
        } catch (\Exception $e) {
            Log::error("Error lead_detail: ",[$e->getMessage()]);
            return Utility::return_response(false, "Something went wrong.", "", 500);
        }
    }

    private function scopedLeadDetailQuery(User $user)
    {
        return $this->scopedLeadListQuery($user)->select(
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
            'next_contact_date',
            'created_by'
        )->with([
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
                    'specification',
                    'created_by',
                    'user_id'
                )->with([
                    'getBillingAddress' => function ($add) {
                        $add->select(
                            'id',
                            'address_line_1',
                            'address_line_2',
                            'country',
                            'state',
                            'city',
                            'zipcode'
                        )->with([
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
                        )->with([
                            'get_country:id,name',
                            'get_state:id,name',
                            'get_city:id,name'
                        ]);
                    },
                ]);
            },
            'user:id,name',
            'stage:id,name',
            'getLeadChat' => function ($q) {
                $q->orderBy('id', 'desc');
            },
            'getCustomerAllPhone',
            'getLeadCall' => function ($q) {
                $q->orderBy('id', 'desc');
            },
            'getQuoteAll' => function ($q) {
                $q->select('id', 'lead_id', 'code', 'date', 'customer_id')
                    ->orderBy('id', 'desc')
                    ->with([
                        'customer:id,name,email'
                    ]);
            },
            'getLeadActivity' => function ($q) {
                $q->with(['users:id,name'])
                    ->orderBy('id', 'desc');
                $q->selectRaw("*, DATE_FORMAT(date_time, '%a, %d %b %Y - %h:%i %p') as date_time_formate");
            },
        ])->withCount([
            'product as lead_products_count'
        ]);
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

            $lead = $this->scopedLeadMutationQuery($user)->where('id', $request->lead_id)->first();
            if (!$lead) {
                return Utility::return_response(false, "Lead not found.", "", 404);
            }

            $newLead = DB::connection($this->tenantConnectionName())->transaction(function () use ($lead, $user) {
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

        } catch (\Throwable $e) {
            Log::info('lead_duplicate error ',[$e->getMessage()]);
            return Utility::return_response(false,$e->getMessage(),"",500);
        }
    }

    public function add_lead_call(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'lead_id' => 'required|exists:tenant.leads,id',
                'status'=> 'required',
                'call_duration'=> 'required'
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false,$validator->errors()->first(),"",422);
            }

            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            Log::info('------ start add_lead_call ------');
            Log::info('Request :-',$request->all());

            $lead = $this->scopedLeadMutationQuery($user)->where('id', $request->lead_id)->first();
            if (!$lead) {
                return Utility::return_response(false, "Lead not found.", "", 404);
            }

            $cht['lead_id']=$lead->id;
            $cht['status'] = $request->status;
            $cht['call_duration'] = $request->call_duration;
            $cht['date_time'] = now()->format('Y-m-d H:i:s');
            $cht['user_id'] = $user->id;
            $new_rcd = LeadCall::create($cht);

            $get_lead_call = LeadCall::where('id',$new_rcd->id)->select('id','lead_id','call_duration','status','date_time','user_id')->first();


            Log::info('------ end add_lead_call ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true,"Lead call has been added successfully.",$get_lead_call ?? "",200);

        } catch (\Throwable $e) {
             \Log::info('add_lead_call list error ',[$e->getMessage()]);
            return Utility::return_response(false,$e->getMessage(),"",500);
        }
    }

    public function lead_call_list(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'lead_id' => 'nullable|exists:tenant.leads,id',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            Log::info('------ start lead_call_list ------');
            Log::info('Request :-', $request->all());

            $leadScope = $this->scopedLeadMutationQuery($user);

            if ($request->filled('lead_id')) {
                $lead = (clone $leadScope)->where('id', $request->lead_id)->first();
                if (!$lead) {
                    return Utility::return_response(false, "Lead not found.", "", 404);
                }

                $leadIds = collect([$lead->id]);
            } else {
                $leadIds = (clone $leadScope)->pluck('id');
            }

            $leadCalls = LeadCall::with([
                'user:id,name',
            ])
            ->whereIn('lead_id', $leadIds)
            ->select('id', 'lead_id','call_duration', 'status', 'date_time', 'user_id')
            ->orderBy('id', 'desc')
            ->get();

            Log::info('------ end lead_call_list ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "Lead call list.", $leadCalls, 200);
        } catch (\Throwable $e) {
            \Log::info('lead_call_list error ', [$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
        }
    }

    public function lead_description_update(Request $request)
    {
        try
        {
            Log::info('------ start lead_description_update ------');
            Log::info('Request :-',$request->all());

            $validator = Validator::make($request->all(), [
                'lead_id' => 'required|exists:tenant.leads,id',
                'notes'=> 'required',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false,$validator->errors()->first(),"",422);
            }

            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            $lead_rcd = $this->scopedLeadMutationQuery($user)
                ->where('id', $request->lead_id)
                ->select('id','notes')
                ->first();
            if(!$lead_rcd) {
                return Utility::return_response(false, "Lead not found.", "", 404);
            }

            $lead_rcd->update(['notes'=>$request->notes]);

            Log::info('------ end lead_description_update ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true,"Lead description has been updated successfully.",$lead_rcd ?? "",200);

        } catch (\Throwable $e) {
             \Log::info('lead_description_update list error ',[$e->getMessage()]);
            return Utility::return_response(false,$e->getMessage(),"",500);
        }
    }

    public function lead_status_update(Request $request)
    {
        try
        {
            Log::info('------ start lead_status_update ------');
            Log::info('Request :-',$request->all());

            $validator = Validator::make($request->all(), [
                'lead_id' => 'required|exists:tenant.leads,id',
                'status'=> 'required|exists:tenant.lead_stages,id',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false,$validator->errors()->first(),"",422);
            }

            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            $lead_rcd = $this->scopedLeadMutationQuery($user)->with([
                'getLeadStatus:id,name'
                ])->where('id',$request->lead_id)->first();
            if(!$lead_rcd) {
                return Utility::return_response(false, "Lead not found.", "", 404);
            }

            $previousStageId = (int) ($lead_rcd->stage_id ?? 0);
            $ld = ['stage_id' => $request->status];
            if((int) $request->status === 4)
            {
                $ld['won_date'] = date('Y-m-d');
            }
            else {
                $ld['won_date'] = null;
            }
            $lead_rcd->update($ld);

            Utility::add_lead_activity($lead_rcd->id, $user->id, 'update lead stage', date('Y-m-d H:i:s'), 'update');
            $this->writeLeadActivity(
                'change_status',
                'lead.stage_changed',
                $lead_rcd,
                'Lead stage changed.',
                [
                    'before' => [
                        'stage_id' => $previousStageId,
                        'stage_name' => $this->resolveLeadStageName($previousStageId),
                    ],
                    'after' => [
                        'stage_id' => (int) $lead_rcd->stage_id,
                        'stage_name' => $this->resolveLeadStageName((int) $lead_rcd->stage_id),
                    ],
                ]
            );

            $get_lead_rcd = $this->scopedLeadMutationQuery($user)->with([
                'getLeadStatus:id,name'
                ])->where('id',$request->lead_id)->select('id','stage_id','won_date','sources')->first();

            Log::info('------ end lead_status_update ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true,"Lead Status has been updated successfully.",$get_lead_rcd ?? "",200);

        } catch (\Throwable $e) {
             \Log::info('lead_status_update list error ',[$e->getMessage()]);
            return Utility::return_response(false,$e->getMessage(),"",500);
        }
    }

    public function import_leads(Request $request)
    {
        try {
            Log::info('------ start import_leads ------');
            Log::info('Request :-', $request->all());

            $validator = Validator::make($request->all(), [
                'leads_data' => 'required',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            $leads = $request->input('leads_data');
            if (is_string($leads)) {
                $leads = json_decode($leads, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    return Utility::return_response(false, 'Invalid JSON: ' . json_last_error_msg(), "", 422);
                }
            }

            if (!is_array($leads) || empty($leads)) {
                return Utility::return_response(false, 'No lead data received for upload.', "", 422);
            }

            $jobKey = 'lead_upload_' . uniqid();
            Cache::put($jobKey, 'pending', now()->addMinutes(60));

            $tenantId = app()->bound('currentTenant') ? (int) data_get(app('currentTenant'), 'id') : null;

            ProcessLeadUpload::dispatchSync($user->id, $jobKey, $leads, $tenantId);

            Log::info('------ end import_leads ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, 'Lead import processed successfully.', [
                'job_key' => $jobKey,
                'status' => Cache::get($jobKey, 'completed'),
                'total_rows' => count($leads),
            ], 200);
        } catch (\Throwable $e) {
            Log::error('import_leads error ', [$e->getMessage()]);
            return Utility::return_response(false, 'Upload failed: ' . $e->getMessage(), "", 500);
        }
    }

    private function scopedLeadMutationQuery(User $user)
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
        });
    }

    private function tenantConnectionName(): string
    {
        if (config('tenancy.enabled', false) && app()->bound('currentTenant')) {
            return 'tenant';
        }

        return (new Lead())->getConnectionName() ?: config('database.default', 'mysql');
    }

}
