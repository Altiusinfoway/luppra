<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Utility;
use App\Models\Entity;
use App\Models\CustomerPhone;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Support\Tenancy\TenancyManager;

class CustomerController extends Controller
{
    private function writeCustomerActivity(string $action, string $eventKey, Entity $customer, string $description, array $properties = []): void
    {
        ActivityLogger::writeFor('customers', $action, $customer, null, [
            'event_key' => $eventKey,
            'description' => $description,
            'properties' => $properties,
        ]);
    }

    private function customerActivitySnapshot(Entity $customer): array
    {
        $customer->refresh();

        $phones = CustomerPhone::where('customer_id', $customer->id)->orderBy('id')->get();
        $primaryPhone = optional($phones->firstWhere('is_primary', 1) ?: $phones->first())->phone;
        $whatsappPhone = optional($phones->firstWhere('is_whatsapp', 1))->phone;
        $billingAddress = $customer->billing_address_id ? Address::find($customer->billing_address_id) : null;
        $shippingAddress = $customer->shipping_address_id ? Address::find($customer->shipping_address_id) : null;

        return [
            'name' => (string) ($customer->name ?? ''),
            'email' => (string) ($customer->email ?? ''),
            'company_name' => (string) ($customer->company_name ?? ''),
            'gst_no' => (string) ($customer->gst_no ?? ''),
            'company_adhar_no' => (string) ($customer->company_adhar_no ?? ''),
            'company_udhyam_no' => (string) ($customer->company_udhyam_no ?? ''),
            'rate' => (string) ($customer->rate ?? ''),
            'lead_type_id' => (string) ($customer->lead_type_id ?? ''),
            'description' => (string) ($customer->description ?? ''),
            'primary_phone' => (string) ($primaryPhone ?? ''),
            'whatsapp_phone' => (string) ($whatsappPhone ?? ''),
            'phone_count' => (string) $phones->count(),
            'billing_address' => $this->formatCustomerAddressSummary($billingAddress),
            'shipping_address' => $this->formatCustomerAddressSummary($shippingAddress),
        ];
    }

    private function formatCustomerAddressSummary(?Address $address): string
    {
        if (!$address) {
            return '';
        }

        return implode(', ', array_filter([
            $address->address_line_1,
            $address->address_line_2,
            $address->city,
            $address->state,
            $address->country,
            $address->zipcode,
        ], static fn ($value) => $value !== null && $value !== ''));
    }

    public function add_customer(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'nullable|email',
                'company_name' => 'required',
                // 'product_rate' => 'required|integer|between:1,5',
                'lead_type_id' => 'nullable|exists:tenant.lead_types,id',
                // 'description' => 'required',
                'phone_list' => 'required',
                // 'billing_country' => 'required',
                // 'billing_state' => 'required',
                // 'billing_city' => 'required',
                // 'billing_zipcode' => 'required',
                // 'billing_address_line_1' => 'required',
                'company_gst_no' => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[0-9A-Z]{1}Z[0-9A-Z]{1}$/',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            Log::info('------ start add_customer ------');
            Log::info('Request :-', $request->all());

            if (!$request->is_same_above || $request->is_same_above == 0) {
                // \Log::info('is same above not required shipping');
                $validator = Validator::make($request->all(), [
                    // 'shipping_country' => 'required',
                    // 'shipping_state' => 'required',
                    // 'shipping_city' => 'required',
                    // 'shipping_zipcode' => 'required',
                    // 'shipping_address_line_1' => 'required'
                ]);

                if ($validator->fails()) {
                    return Utility::return_response(false, $validator->errors()->first(), "", 422);
                }
            }

            //check phone validation
            $phone_list = json_decode($request->phone_list, true);

            if (empty($phone_list) || !is_array($phone_list)) {
                return Utility::return_response(false, "At least one phone number is required", "", 422);
            }


            if ($phone_list) {
                $primaryCount = 0;

                foreach ($phone_list as  $index => $phone) {

                    if (empty($phone['phone'])) {
                        return Utility::return_response(false, "Phone number is required at index " . ($index + 1), "", 422);
                    }

                    if (!preg_match('/^[0-9]{10}$/', $phone['phone'])) {
                        return Utility::return_response(false, "Phone number must be exactly 10 digits at index " . ($index + 1), "", 422);
                    }

                    foreach (['is_primary', 'is_secondary', 'is_whatsapp'] as $field) {
                        if (!isset($phone[$field]) || !in_array($phone[$field], [0, 1])) {
                            return Utility::return_response(false, "Invalid value for {$field} at index " . ($index + 1), "", 422);
                        }
                    }
                    if ($phone['is_primary'] == 1 && $phone['is_secondary'] == 1) {
                        return Utility::return_response(false, "Primary and secondary cannot both be 1 at index " . ($index + 1), "", 422);
                    }
                    if ($phone['is_primary'] == 0 && $phone['is_secondary'] == 0) {
                        return Utility::return_response(false, "Please set either primary or secondary at index " . ($index + 1), "", 422);
                    }
                    if ($phone['is_primary'] == 1) {
                        $primaryCount++;
                    }

                    $cust_check = CustomerPhone::where('phone', $phone['phone'])->where('is_primary', 1)->first();

                    if ($cust_check) {
                        $get_cust = Entity::where('id', $cust_check->customer_id)
                            ->where('type', 'customer')
                            ->first();
                        if (!$get_cust) {
                            return Utility::return_response(false, "customer not found ", "", 422);
                        }

                        if ($user->type !== 'company' && (int) $get_cust->created_by === (int) $user->creatorId() && $get_cust->user_id === null) {
                            $get_cust->user_id = $user->id;
                            $get_cust->save();
                        }

                        $get_user = $get_cust->user_id ? User::where('id', $get_cust->user_id)->first() : null;
                        if ($get_user && (int) $get_cust->user_id !== (int) $user->id) {
                            return Utility::return_response(false, $cust_check->phone . "  Customer phone already exists " . $get_user->name, "", 422);
                        }

                        return Utility::return_response(false, $cust_check->phone . "  Customer phone already exists ", "", 422);
                    }
                }

                if ($primaryCount > 1) {
                    return Utility::return_response(false, "Only one phone number can be marked as primary", "", 422);
                }
            }

            //gst check
            if ($request->company_gst_no) {

                 $validator = Validator::make($request->all(), [
                   'company_gst_no' => [
                        'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
                    ]
                ]);

                if ($validator->fails()) {
                    return Utility::return_response(false, $validator->errors()->first(), "", 422);
                }
                $check_exist_gst = Entity::where('gst_no', $request->company_gst_no)->where('type', 'customer')->exists();

                if ($check_exist_gst) {
                    return Utility::return_response(false, "This GST number already exists for another customer", "", 422);
                }
            }

            $connection = $this->tenantConnectionName();
            DB::connection($connection)->beginTransaction();

            try {

                //customer create
                $cust['name'] = $request->name;
                $cust['email'] = $request->email;
                $cust['company_name'] = $request->company_name;
                $cust['gst_no'] = $request->company_gst_no ?? null;
                $cust['company_adhar_no'] = $request->company_adhar_no ?? null;
                $cust['company_udhyam_no'] = $request->company_udhyam_no ?? null;
                $cust['rate'] = $request->product_rate ?? 1;
                $cust['lead_type_id'] = $request->lead_type_id;
                $cust['description'] = $request->description;
                $cust['created_by'] = $user->creatorId();
                $cust['type'] = 'customer';

                if(\Auth::user()->type == 'company')
                {
                    $cust['user_id'] = null;
                }
                else
                {
                    $cust['user_id'] = \Auth::user()->id;
                }

                $customer_rcd = Entity::create($cust);

                //customer multiple phone
                if ($phone_list) {
                    foreach ($phone_list as  $index => $ph) {
                        $cust_phone['customer_id'] = $customer_rcd->id;
                        $cust_phone['phone'] = $ph['phone'];
                        $cust_phone['is_primary'] = $ph['is_primary'];
                        $cust_phone['is_secondary'] = $ph['is_secondary'];
                        $cust_phone['is_whatsapp'] = $ph['is_whatsapp'];

                        CustomerPhone::create($cust_phone);
                    }
                }

                //address
                if (!$request->is_same_above || $request->is_same_above == 0) {
                    //both address diff
                    $billing['name'] = $customer_rcd->name;
                    $billing['email'] =  $customer_rcd->email;
                    $billing['country'] = $request->billing_country;
                    $billing['state'] = $request->billing_state;
                    $billing['city'] = $request->billing_city;
                    $billing['zipcode'] = $request->billing_zipcode;
                    $billing['address_line_1'] = $request->billing_address_line_1;
                    $billing['address_line_2'] = $request->billing_address_line_2;

                    $billing_adress = Address::create($billing);

                    $shipping['name'] = $customer_rcd->name;
                    $shipping['email'] =  $customer_rcd->email;
                    $shipping['country'] = $request->shipping_country;
                    $shipping['state'] = $request->shipping_state;
                    $shipping['city'] = $request->shipping_city;
                    $shipping['zipcode'] = $request->shipping_zipcode;
                    $shipping['address_line_1'] = $request->shipping_address_line_1;
                    $shipping['address_line_2'] = $request->shipping_address_line_2;

                    $shipping_adress = Address::create($shipping);

                    $customer_rcd->update(['billing_address_id' => $billing_adress->id, 'shipping_address_id' => $shipping_adress->id]);
                } else {
                    //both address same
                    $billing['name'] = $customer_rcd->name;
                    $billing['email'] =  $customer_rcd->email;
                    $billing['country'] = $request->billing_country;
                    $billing['state'] = $request->billing_state;
                    $billing['city'] = $request->billing_city;
                    $billing['zipcode'] = $request->billing_zipcode;
                    $billing['address_line_1'] = $request->billing_address_line_1;
                    $billing['address_line_2'] = $request->billing_address_line_2;

                    $billing_adress = Address::create($billing);
                    $shipping_adress = Address::create($billing);

                    $customer_rcd->update(['billing_address_id' => $billing_adress->id, 'shipping_address_id' => $shipping_adress->id]);
                }



                //fetch data

                $customer_data = Entity::with([

                    // Billing Address
                    'getBillingAddress' => function ($q) {
                        $q->select(
                            'id',
                            'address_line_1',
                            'address_line_2',
                            'city',
                            'state',
                            'country'
                        );
                    },
                    'getBillingAddress.get_country' => function ($q) {
                        $q->select('id', 'name');
                    },
                    'getBillingAddress.get_state' => function ($q) {
                        $q->select('id', 'name');
                    },
                    'getBillingAddress.get_city' => function ($q) {
                        $q->select('id', 'name');
                    },

                    // Shipping Address
                    'getShippingAddress' => function ($q) {
                        $q->select(
                            'id',
                            'address_line_1',
                            'address_line_2',
                            'city',
                            'state',
                            'country'
                        );
                    },
                    'getShippingAddress.get_country' => function ($q) {
                        $q->select('id', 'name');
                    },
                    'getShippingAddress.get_state' => function ($q) {
                        $q->select('id', 'name');
                    },
                    'getShippingAddress.get_city' => function ($q) {
                        $q->select('id', 'name');
                    },
                    'getCustomerPhone'

                ])
                    ->where('id', $customer_rcd->id)->select(
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
                        'user_id'
                    )
                    ->first();


                $customerSnapshot = $this->customerActivitySnapshot($customer_rcd);
                $this->writeCustomerActivity('create', 'customer.created', $customer_rcd, 'Customer created.', [
                    'name' => $customer_rcd->name,
                    'company_name' => $customer_rcd->company_name,
                    'gst_no' => $customer_rcd->gst_no,
                    'primary_phone' => $customerSnapshot['primary_phone'] ?? '',
                    'phone_count' => $customerSnapshot['phone_count'] ?? '0',
                ]);

                DB::connection($connection)->commit();
            } catch (\Throwable $th) {
                DB::connection($connection)->rollBack();
                throw $th;
            }


            Log::info('------ end add_customer ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true, "customer has been added successfully.", $customer_data, 200);
        } catch (\Throwable $e) {
            \Log::info('add customer error ', [$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
        }
    }

    public function edit_customer(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            Log::info('------ start edit_customer ------');
            Log::info('Request :-', $request->all());

            $customer_data = $this->managedCustomerQuery($user)
                ->where('id', $request->id)
                ->first();
            if (!$customer_data) {
                return Utility::return_response(false, "customer not found", "", 422);
            }

            if ($customer_data->user_id === null) {
                $customer_data->user_id = $user->id;
                $customer_data->save();
            }

            $customerBefore = $this->customerActivitySnapshot($customer_data);

            //check phone validation

            if ($request->phone_list) {
                $phone_list = json_decode($request->phone_list, true);

                if (empty($phone_list) || !is_array($phone_list)) {
                    return Utility::return_response(false, "At least one phone number is required", "", 422);
                }

                if ($phone_list) {
                    $primaryCount = 0;

                    foreach ($phone_list as  $index => $phone) {

                        if (empty($phone['phone'])) {
                            return Utility::return_response(false, "Phone number is required at index " . ($index + 1), "", 422);
                        }

                        if (!preg_match('/^[0-9]{10}$/', $phone['phone'])) {
                            return Utility::return_response(false, "Phone number must be exactly 10 digits at index " . ($index + 1), "", 422);
                        }

                        foreach (['is_primary', 'is_secondary', 'is_whatsapp'] as $field) {
                            if (!isset($phone[$field]) || !in_array($phone[$field], [0, 1])) {
                                return Utility::return_response(false, "Invalid value for {$field} at index " . ($index + 1), "", 422);
                            }
                        }
                        if ($phone['is_primary'] == 1 && $phone['is_secondary'] == 1) {
                            return Utility::return_response(false, "Primary and secondary cannot both be 1 at index " . ($index + 1), "", 422);
                        }
                        if ($phone['is_primary'] == 0 && $phone['is_secondary'] == 0) {
                            return Utility::return_response(false, "Please set either primary or secondary at index " . ($index + 1), "", 422);
                        }
                        if ($phone['is_primary'] == 1) {
                            $primaryCount++;
                        }

                        $cust_check = CustomerPhone::where('phone', $phone['phone'])
                            ->where('is_primary', 1)
                            ->where('customer_id', '!=', $customer_data->id)
                            ->first();

                        if ($cust_check) {
                            $get_cust = Entity::where('id', $cust_check->customer_id)
                                ->where('type', 'customer')
                                ->first();
                            if (!$get_cust) {
                                return Utility::return_response(false,"  customer not found.", "", 422);
                            }

                            if ($user->type !== 'company' && (int) $get_cust->created_by === (int) $user->creatorId() && $get_cust->user_id === null) {
                                $get_cust->user_id = $user->id;
                                $get_cust->save();
                            }

                            $get_user = $get_cust->user_id ? User::where('id', $get_cust->user_id)->first() : null;
                            if ($get_user && (int) $get_cust->user_id !== (int) $user->id) {
                                return Utility::return_response(false, $cust_check->phone . "  Customer phone already exists " . $get_user->name, "", 422);
                            }

                            return Utility::return_response(false, $cust_check->phone . "  Customer phone already exists ", "", 422);
                        }
                    }

                    if ($primaryCount > 1) {
                        return Utility::return_response(false, "Only one phone number can be marked as primary", "", 422);
                    }
                }
            }


            //gst check
            if ($request->company_gst_no) {

                $validator = Validator::make($request->all(), [
                'company_gst_no' => [
                    'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
                ]
                ]);

                if ($validator->fails()) {
                    return Utility::return_response(false, $validator->errors()->first(), "", 422);
                }

                $check_exist_gst = Entity::where('gst_no',$request->company_gst_no)->where('id', '!=', $request->id)->where('type', 'customer')->exists();

                if ($check_exist_gst) {
                    return Utility::return_response(false, "This GST number already exists for another customer", "", 422);
                }
            }

            $connection = $this->tenantConnectionName();
            DB::connection($connection)->beginTransaction();

            try {



                //customer create
                $cust['name'] = $request->name ? $request->name :  $customer_data->name;
                $cust['email'] = $request->email ? $request->email : $customer_data->email;
                $cust['company_name'] = $request->company_name ? $request->company_name : $customer_data->company_name;
                $cust['gst_no'] = $request->company_gst_no ? $request->company_gst_no : $customer_data->gst_no;
                $cust['company_adhar_no'] = $request->company_adhar_no ? $request->company_adhar_no : $customer_data->company_adhar_no;
                $cust['company_udhyam_no'] = $request->company_udhyam_no ? $request->company_udhyam_no : $customer_data->company_udhyam_no;
                $cust['rate'] = $request->product_rate ? $request->product_rate : $customer_data->rate;
                $cust['lead_type_id'] = $request->lead_type_id ? $request->lead_type_id : $customer_data->lead_type_id;
                $cust['description'] = $request->description ? $request->description : $customer_data->description;

                $customer_data->update($cust);

                //customer multiple phone
                if ($request->phone_list) {
                    $requestPhones = collect($phone_list)->pluck('phone')->toArray();

                    CustomerPhone::where('customer_id', $customer_data->id)
                        ->whereNotIn('phone', $requestPhones)
                        ->delete();

                    foreach ($phone_list as $index => $ph) {

                        $duplicate = CustomerPhone::where('phone', $ph['phone'])
                            ->where('customer_id', '!=', $customer_data->id)
                            ->where('is_primary', 1)
                            ->first();

                        if ($duplicate) {
                            $duplicateCustomer = Entity::where('id', $duplicate->customer_id)
                                ->where('type', 'customer')
                                ->first();

                            if ($duplicateCustomer && (int) $duplicateCustomer->created_by === (int) $user->creatorId() && $duplicateCustomer->user_id === null) {
                                $duplicateCustomer->user_id = $user->id;
                                $duplicateCustomer->save();
                            }

                            if ($duplicateCustomer && (int) ($duplicateCustomer->user_id ?? 0) !== (int) $user->id) {
                                $duplicateUser = $duplicateCustomer->user_id ? User::where('id', $duplicateCustomer->user_id)->first() : null;
                                $duplicateOwner = $duplicateUser ? (' and assign to ' . $duplicateUser->name) : '';
                                DB::connection($connection)->rollBack();
                                return Utility::return_response(false, $ph['phone'] . " already exists for another customer" . $duplicateOwner . ".", "", 422);
                            }

                            DB::connection($connection)->rollBack();
                            return Utility::return_response(false, $ph['phone'] . " already exists for another customer.", "", 422);
                        }

                        // Check if phone exists for THIS customer
                        $existingPhone = CustomerPhone::where('customer_id',$customer_data->id)
                            ->where('phone', $ph['phone'])
                            ->first();

                        if ($existingPhone) {
                            // Update
                            $existingPhone->update([
                                'is_primary'  => $ph['is_primary'],
                                'is_secondary' => $ph['is_secondary'],
                                'is_whatsapp' => $ph['is_whatsapp'],
                            ]);
                        } else {
                            // Insert
                            CustomerPhone::create([
                                'customer_id' => $customer_data->id,
                                'phone'       => $ph['phone'],
                                'is_primary'  => $ph['is_primary'],
                                'is_secondary' => $ph['is_secondary'],
                                'is_whatsapp' => $ph['is_whatsapp'],
                            ]);
                        }
                    }
                }

                //address is empty then newly insert
                if (empty($customer_data->billing_address_id) || empty($customer_data->shipping_address_id)) {
                    $validator = Validator::make($request->all(), [
                        // 'billing_country' => 'required',
                        // 'billing_state' => 'required',
                        // 'billing_city' => 'required',
                        // 'billing_zipcode' => 'required',
                        // 'billing_address_line_1' => 'required'
                    ]);

                    if ($validator->fails()) {
                        DB::connection($connection)->rollBack();
                        return Utility::return_response(false, $validator->errors()->first(), "", 422);
                    }

                    if (!$request->is_same_above || $request->is_same_above == 0) {
                        // \Log::info('is same above not required shipping');
                        $validator = Validator::make($request->all(), [
                            // 'shipping_country' => 'required',
                            // 'shipping_state' => 'required',
                            // 'shipping_city' => 'required',
                            // 'shipping_zipcode' => 'required',
                            // 'shipping_address_line_1' => 'required'
                        ]);

                        if ($validator->fails()) {
                            DB::connection($connection)->rollBack();
                            return Utility::return_response(false, $validator->errors()->first(), "", 422);
                        }
                    }

                    if (!$request->is_same_above || $request->is_same_above == 0) {
                        //both address diff
                        $billing['name'] = $customer_data->name;
                        $billing['email'] =  $customer_data->email;
                        $billing['country'] = $request->billing_country;
                        $billing['state'] = $request->billing_state;
                        $billing['city'] = $request->billing_city;
                        $billing['zipcode'] = $request->billing_zipcode;
                        $billing['address_line_1'] = $request->billing_address_line_1;
                        $billing['address_line_2'] = $request->billing_address_line_2;

                        $billing_adress = Address::create($billing);

                        $shipping['name'] = $customer_data->name;
                        $shipping['email'] =  $customer_data->email;
                        $shipping['country'] = $request->shipping_country;
                        $shipping['state'] = $request->shipping_state;
                        $shipping['city'] = $request->shipping_city;
                        $shipping['zipcode'] = $request->shipping_zipcode;
                        $shipping['address_line_1'] = $request->shipping_address_line_1;
                        $shipping['address_line_2'] = $request->shipping_address_line_2;

                        $shipping_adress = Address::create($shipping);

                        $customer_data->update(['billing_address_id' => $billing_adress->id, 'shipping_address_id' => $shipping_adress->id]);
                    } else {
                        //both address same
                        $billing['name'] = $customer_data->name;
                        $billing['email'] =  $customer_data->email;
                        $billing['country'] = $request->billing_country;
                        $billing['state'] = $request->billing_state;
                        $billing['city'] = $request->billing_city;
                        $billing['zipcode'] = $request->billing_zipcode;
                        $billing['address_line_1'] = $request->billing_address_line_1;
                        $billing['address_line_2'] = $request->billing_address_line_2;

                        $billing_adress = Address::create($billing);
                        $shipping_adress = Address::create($billing);

                        $customer_data->update(['billing_address_id' => $billing_adress->id, 'shipping_address_id' => $shipping_adress->id]);
                    }
                } else {
                    //existing address update

                    //billing adr update
                    if ($customer_data->billing_address_id) {
                        $bill_adr = Address::where('id', $customer_data->billing_address_id)->first();
                        if (!$bill_adr) {
                            DB::connection($connection)->rollBack();
                            return Utility::return_response(false, "billing address not found", "", 404);
                        }

                        $billing['name'] = $customer_data->name;
                        $billing['email'] =  $customer_data->email;
                        $billing['country'] = $request->billing_country ?? $bill_adr->country;
                        $billing['state'] = $request->billing_state ?? $bill_adr->state;
                        $billing['city'] = $request->billing_city ?? $bill_adr->city;
                        $billing['zipcode'] = $request->billing_zipcode ?? $bill_adr->zipcode;
                        if (isset($request->billing_address_line_1)) {
                            $billing['address_line_1'] = $request->billing_address_line_1;
                        }

                        if (isset($request->billing_address_line_2)) {
                            $billing['address_line_2'] = $request->billing_address_line_2;
                        }
                        $bill_adr->update($billing);
                    }


                    //shipping adr update
                    if ($customer_data->shipping_address_id) {
                        $ship_adr = Address::where('id', $customer_data->shipping_address_id)->first();
                        if (!$ship_adr) {
                            DB::connection($connection)->rollBack();
                            return Utility::return_response(false, "shipping address not found", "", 404);
                        }

                        $shipping['name'] = $customer_data->name;
                        $shipping['email'] =  $customer_data->email;
                        $shipping['country'] = $request->shipping_country ?? $ship_adr->country;
                        $shipping['state'] = $request->shipping_state ?? $ship_adr->state;
                        $shipping['city'] = $request->shipping_city ?? $ship_adr->city;
                        $shipping['zipcode'] = $request->shipping_zipcode ?? $ship_adr->zipcode;
                        if (isset($request->shipping_address_line_1)) {
                            $shipping['address_line_1'] = $request->shipping_address_line_1;
                        }

                        if (isset($request->shipping_address_line_2)) {
                            $shipping['address_line_2'] = $request->shipping_address_line_2;
                        }
                        $ship_adr->update($shipping);
                    }
                }

                $customer_up = Entity::with([

                    // Billing Address
                    'getBillingAddress' => function ($q) {
                        $q->select(
                            'id',
                            'address_line_1',
                            'address_line_2',
                            'city',
                            'state',
                            'country'
                        );
                    },
                    'getBillingAddress.get_country' => function ($q) {
                        $q->select('id', 'name');
                    },
                    'getBillingAddress.get_state' => function ($q) {
                        $q->select('id', 'name');
                    },
                    'getBillingAddress.get_city' => function ($q) {
                        $q->select('id', 'name');
                    },

                    // Shipping Address
                    'getShippingAddress' => function ($q) {
                        $q->select(
                            'id',
                            'address_line_1',
                            'address_line_2',
                            'city',
                            'state',
                            'country'
                        );
                    },
                    'getShippingAddress.get_country' => function ($q) {
                        $q->select('id', 'name');
                    },
                    'getShippingAddress.get_state' => function ($q) {
                        $q->select('id', 'name');
                    },
                    'getShippingAddress.get_city' => function ($q) {
                        $q->select('id', 'name');
                    },
                    'getCustomerPhone'

                ])
                    ->where('id', $customer_data->id)->select(
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
                         'user_id'
                    )
                    ->first();

                $customerChanges = ActivityLogger::diff($customerBefore, $this->customerActivitySnapshot($customer_data));
                if (!empty($customerChanges)) {
                    $this->writeCustomerActivity('update', 'customer.updated', $customer_data, 'Customer updated.', [
                        'changes' => $customerChanges,
                    ]);
                }

                DB::connection($connection)->commit();
            } catch (\Throwable $th) {
                DB::connection($connection)->rollBack();
                throw $th;
            }

            Log::info('------ end edit_customer ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true, "customer has been updated successfully.", $customer_up, 200);
        } catch (\Throwable $e) {
            \Log::info('edit customer error ', [$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
        }
    }

    //old
    //  public function edit_customer(Request $request)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'id' => 'required|exists:entities,id',
    //             'name' => 'required',
    //             'email' => 'required|email',
    //             'company_name' => 'required',
    //             'product_rate' => 'required|integer|between:1,5',
    //             'lead_type_id' => 'required|exists:tenant.lead_types,id',
    //             'description' => 'required',
    //             'phone_list' => 'required',
    //             'billing_country' => 'required',
    //             'billing_state' => 'required',
    //             'billing_city' => 'required',
    //             'billing_zipcode' => 'required',
    //             'billing_address_line_1' => 'required'
    //         ]);

    //         if ($validator->fails()) {
    //             return Utility::return_response(false, $validator->errors()->first(), "", 422);
    //         }

    //         $user = JWTAuth::parseToken()->authenticate();

    //         Log::info('------ start edit_customer ------');

    //         $customer = Entity::where('id', $request->id)
    //             ->where('type', 'customer')
    //             ->first();

    //         if (!$customer) {
    //             return Utility::return_response(false, "Customer not found.", "", 404);
    //         }

    //         // Shipping validation
    //         if (!$request->is_same_above || $request->is_same_above == 0) {
    //             $validator = Validator::make($request->all(), [
    //                 'shipping_country' => 'required',
    //                 'shipping_state' => 'required',
    //                 'shipping_city' => 'required',
    //                 'shipping_zipcode' => 'required',
    //                 'shipping_address_line_1' => 'required'
    //             ]);

    //             if ($validator->fails()) {
    //                 return Utility::return_response(false, $validator->errors()->first(), "", 422);
    //             }
    //         }

    //         /* ------------------ PHONE VALIDATION ------------------ */
    //         $phone_list = json_decode($request->phone_list, true);

    //         if (empty($phone_list) || !is_array($phone_list)) {
    //             return Utility::return_response(false, "At least one phone number is required", "", 422);
    //         }

    //         $existingPhones = CustomerPhone::where('customer_id', $customer->id)
    //             ->pluck('phone')
    //             ->toArray();

    //         $primaryCount = 0;

    //         foreach ($phone_list as $index => $phone) {

    //             if (empty($phone['phone']) || !preg_match('/^[0-9]{10}$/', $phone['phone'])) {
    //                 return Utility::return_response(false, "Invalid phone at index " . ($index + 1), "", 422);
    //             }

    //             foreach (['is_primary', 'is_secondary', 'is_whatsapp'] as $field) {
    //                 if (!isset($phone[$field]) || !in_array($phone[$field], [0, 1])) {
    //                     return Utility::return_response(false, "Invalid {$field} at index " . ($index + 1), "", 422);
    //                 }
    //             }

    //             if ($phone['is_primary'] == 1 && $phone['is_secondary'] == 1) {
    //                 return Utility::return_response(false, "Primary and secondary cannot both be 1 at index " . ($index + 1), "", 422);
    //             }

    //             if ($phone['is_primary'] == 0 && $phone['is_secondary'] == 0) {
    //                 return Utility::return_response(false, "Select primary or secondary at index " . ($index + 1), "", 422);
    //             }

    //             if ($phone['is_primary'] == 1) {
    //                 $primaryCount++;
    //             }

    //             // Check duplicate ONLY if phone is new
    //             if (!in_array($phone['phone'], $existingPhones)) {

    //                 $phoneExists = CustomerPhone::where('phone', $phone['phone'])->first();

    //                 if ($phoneExists) {
    //                     return Utility::return_response(false, $phone['phone'] . " already exists.", "", 422);
    //                 }
    //             }
    //         }

    //         if ($primaryCount > 1) {
    //             return Utility::return_response(false, "Only one phone number can be primary", "", 422);
    //         }

    //         /* ------------------ UPDATE CUSTOMER ------------------ */
    //         $customer->update([
    //             'name' => $request->name,
    //             'email' => $request->email,
    //             'company_name' => $request->company_name,
    //             'gst_no' => $request->company_gst_no,
    //             'company_adhar_no' => $request->company_adhar_no,
    //             'company_udhyam_no' => $request->company_udhyam_no,
    //             'rate' => $request->product_rate,
    //             'lead_type_id' => $request->lead_type_id,
    //             'description' => $request->description
    //         ]);

    //         /* ------------------ UPDATE PHONES ------------------ */
    //         CustomerPhone::where('customer_id', $customer->id)->delete();

    //         foreach ($phone_list as $ph) {
    //             CustomerPhone::create([
    //                 'customer_id' => $customer->id,
    //                 'phone' => $ph['phone'],
    //                 'is_primary' => $ph['is_primary'],
    //                 'is_secondary' => $ph['is_secondary'],
    //                 'is_whatsapp' => $ph['is_whatsapp']
    //             ]);
    //         }

    //         /* ------------------ UPDATE ADDRESSES ------------------ */
    //         $billingData = [
    //             'name' => $customer->name,
    //             'email' => $customer->email,
    //             'country' => $request->billing_country,
    //             'state' => $request->billing_state,
    //             'city' => $request->billing_city,
    //             'zipcode' => $request->billing_zipcode,
    //             'address_line_1' => $request->billing_address_line_1,
    //             'address_line_2' => $request->billing_address_line_2
    //         ];

    //         Address::where('id', $customer->billing_address_id)->update($billingData);

    //         if (!$request->is_same_above || $request->is_same_above == 0) {
    //             $shippingData = [
    //                 'name' => $customer->name,
    //                 'email' => $customer->email,
    //                 'country' => $request->shipping_country,
    //                 'state' => $request->shipping_state,
    //                 'city' => $request->shipping_city,
    //                 'zipcode' => $request->shipping_zipcode,
    //                 'address_line_1' => $request->shipping_address_line_1,
    //                 'address_line_2' => $request->shipping_address_line_2
    //             ];
    //         } else {
    //             $shippingData = $billingData;
    //         }

    //         Address::where('id', $customer->shipping_address_id)->update($shippingData);

    //         $customer_data = Entity::with([
    //             'getBillingAddress',
    //             'getBillingAddress.get_country',
    //             'getBillingAddress.get_state',
    //             'getBillingAddress.get_city',
    //             'getShippingAddress',
    //             'getShippingAddress.get_country',
    //             'getShippingAddress.get_state',
    //             'getShippingAddress.get_city',
    //             'getCustomerPhone'
    //         ])->find($customer->id);

    //         DB::commit();

    //         Log::info('------ end edit_customer ------');

    //         return Utility::return_response(true, "Customer updated successfully.", $customer_data, 200);
    //     } catch (\Throwable $e) {
    //         DB::rollback();
    //         return Utility::return_response(false, $e->getMessage(), "", 500);
    //     }
    // }

    public function get_customers(Request $request)
    {

        try {
            $user = Auth::user();

            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            // $this->initializeApiTenantContext($request, $user);

            Log::info('------ start get_customers ------');
            Log::info('Request :-', $request->all());


            $customers = Entity::with([
                // Billing Address
                'getBillingAddress' => function ($q) {
                    $q->select(
                        'id',
                        'address_line_1',
                        'address_line_2',
                        'city',
                        'state',
                        'country'
                    );
                },
                'getBillingAddress.get_country' => function ($q) {
                    $q->select('id', 'name');
                },
                'getBillingAddress.get_state' => function ($q) {
                    $q->select('id', 'name');
                },
                'getBillingAddress.get_city' => function ($q) {
                    $q->select('id', 'name');
                },

                // Shipping Address
                'getShippingAddress' => function ($q) {
                    $q->select(
                        'id',
                        'address_line_1',
                        'address_line_2',
                        'city',
                        'state',
                        'country'
                    );
                },
                'getShippingAddress.get_country' => function ($q) {
                    $q->select('id', 'name');
                },
                'getShippingAddress.get_state' => function ($q) {
                    $q->select('id', 'name');
                },
                'getShippingAddress.get_city' => function ($q) {
                    $q->select('id', 'name');
                },
                'getCustomerPhone',
            ])
            ->where('type', 'customer')
            ->where('created_by', $user->creatorId())
            ->where('user_id', $user->id)
            ->select(
                'id',
                'name',
                'email',
                'company_name',
                'gst_no',
                'company_adhar_no',
                'company_udhyam_no',
                'billing_address_id',
                'shipping_address_id',
                'lead_type_id',
                'rate',
                'description',
                'specification',
                'user_id'
            )->orderBy('id', 'desc')->get();

            Log::info('------ end get_customers ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true, "customer list.", $customers, 200);
        } catch (\Throwable $e) {
            \Log::info('get customer error ', [$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
        }
    }


    public function check_customer_phone(Request $request)
    {
        try {
            Log::info('------ start check_customer_phone ------');
            Log::info('Request :-', $request->all());

            $validator = Validator::make($request->all(), [
                'phone' => 'required|digits:10',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = Auth::user();
            if (!$user) {
                return Utility::return_response(false, "User not authenticated.", "", 401);
            }

            $customer = "";
            $custome_phone = CustomerPhone::where('phone', $request->phone)->where('is_primary', 1)->first();
            if ($custome_phone) {
                $customer = Entity::with([
                    'getBillingAddress:id,country,state,city,zipcode,address_line_1,address_line_2',
                    'getBillingAddress.get_country:id,name',
                    'getBillingAddress.get_state:id,name',
                    'getBillingAddress.get_city:id,name',
                    'getShippingAddress:id,country,state,city,zipcode,address_line_1,address_line_2',
                    'getShippingAddress.get_country:id,name',
                    'getShippingAddress.get_state:id,name',
                    'getShippingAddress.get_city:id,name',
                    'getLeadType:id,name',
                    'getCustomerPhone' => function ($q) {
                        $q->where('is_primary', 1)
                            ->select('id', 'customer_id', 'phone', 'is_primary', 'is_secondary', 'is_whatsapp');
                    },
                ])->where('id', $custome_phone->customer_id)->select(
                    'id',
                    'name',
                    'email',
                    'company_name',
                    'gst_no',
                    'company_adhar_no',
                    'company_udhyam_no',
                    'due_amount',
                    'paid_amount',
                    'billing_address_id',
                    'shipping_address_id',
                    'lead_type_id',
                    'created_by',
                    'user_id'
                )->first();

                if ($customer) {
                    if ((int) $customer->created_by !== (int) $user->creatorId()) {
                        return Utility::return_response(false, "customer not found", "", 404);
                    }

                    if ($customer->user_id === null) {
                        $customer->user_id = $user->id;
                        $customer->save();
                        $customer->refresh();
                    }

                    if ((int) $customer->user_id !== (int) $user->id) {
                        $get_user = User::where('id', $customer->user_id)->first();
                        if (!$get_user) {
                            return Utility::return_response(false, " user not found ", "", 422);
                        }
                        return Utility::return_response(false, " this phone number already exists and that customer assign to " . $get_user->name, "", 422);
                    }
                }
            }

            Log::info('------ end check_customer_phone ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true, "customer.", $customer ?? "", 200);
        } catch (\Throwable $e) {
            return Utility::return_response(false, $e->getMessage(), "", 500);
        }
    }

    private function managedCustomerQuery(User $user)
    {
        return Entity::where('type', 'customer')
            ->where('created_by', $user->creatorId())
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereNull('user_id');
            });
    }

    private function tenantConnectionName(): string
    {
        if (config('tenancy.enabled', false) && app()->bound('currentTenant')) {
            return 'tenant';
        }

        return (new Entity())->getConnectionName() ?: config('database.default', 'mysql');
    }

    // private function initializeApiTenantContext(Request $request, ?User $user = null): void
    // {
    //     if (!config('tenancy.enabled', false) || app()->bound('currentTenant')) {
    //         return;
    //     }

    //     $tenantId = 0;

    //     try {
    //         $tenantId = (int) (JWTAuth::parseToken()->getPayload()->get('tenant_id') ?? 0);
    //     } catch (\Throwable $e) {
    //         $tenantId = 0;
    //     }

    //     if ($tenantId <= 0) {
    //         $tenantId = (int) ($user->tenant_id ?? 0);
    //     }

    //     if ($tenantId <= 0) {
    //         $tenantId = (int) ($request->header(config('tenancy.header_tenant_id', 'X-Tenant-Id')) ?? $request->query('tenant_id') ?? 0);
    //     }

    //     if ($tenantId <= 0) {
    //         return;
    //     }

    //     $tenant = Tenant::query()
    //         ->where('id', $tenantId)
    //         ->where('is_active', true)
    //         ->first();

    //     if (!$tenant) {
    //         return;
    //     }

    //     app(TenancyManager::class)->initialize($tenant);
    //     app()->instance('currentTenant', $tenant);
    // }
}
