<?php

namespace App\Http\Controllers;

use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Models\Entity;
use App\Models\Address;
use Illuminate\Validation\Rule;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\CustomerPhone;
use App\Models\LeadType;
use App\Models\Order;
use App\Models\Quotes;
use App\Models\Lead;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
            'is_active' => (string) ($customer->is_active ?? ''),
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

    public function index(Request $request)
    {

        $data['country_list'] = Country::isActive()->pluck('name', 'id');

        if ($request->ajax()) {
            try {
                $hasCitiesTable = Schema::hasTable('cities');
                $hasStatesTable = Schema::hasTable('states');
                $cityMap = $hasCitiesTable ? City::pluck('name', 'id') : collect();
                $stateMap = $hasStatesTable ? State::pluck('name', 'id') : collect();

                if(\Auth::user()->type == 'Sales')
                {
                    $query = Entity::where('type', 'customer')->where('user_id',\Auth::user()->id)->with(['getAddress']);
                }
                else
                {
                    $query = Entity::where('type', 'customer')->with(['getAddress']);
                }


                if ($request->name) {
                    $query->where('name', 'like', '%' . $request->name . '%');
                }

                if ($request->country_filter) {
                    $query->whereHas('getAddress', function ($q) use ($request) {
                        $q->where('country', $request->country_filter);
                    });
                }

                if ($request->state_filter) {
                    $query->whereHas('getAddress', function ($q) use ($request) {
                        $q->where('state', $request->state_filter);
                    });
                }

                if ($request->city_filter) {
                    $query->whereHas('getAddress', function ($q) use ($request) {
                        $q->where('city', $request->city_filter);
                    });
                }


                $data = $query->with(['getCustomerPhone'])->orderBy('id', 'desc')->get();

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('customer_detail',function ($row) use ($cityMap, $stateMap)
                    {
                        $billingAddress = $row->getBillingAddress;

                        $city = '';
                        $state = '';
                        if ($billingAddress) {
                            $cityKey = (string) ($billingAddress->city ?? '');
                            $stateKey = (string) ($billingAddress->state ?? '');

                            if ($cityKey !== '' && $cityMap->has((int) $cityKey)) {
                                $city = (string) $cityMap->get((int) $cityKey);
                            } else {
                                $city = $cityKey;
                            }

                            if ($stateKey !== '' && $stateMap->has((int) $stateKey)) {
                                $state = (string) $stateMap->get((int) $stateKey);
                            } else {
                                $state = $stateKey;
                            }
                        }
                        // $customer = $row->getCustomer;
                        $primary = $row->getCustomerPhone?->where('is_primary', 1)->first();
                        $name = $phone = $address ='';

                        if($row->name){

                            $name =  '<div class="col-12">
                                <p class="born timestamp text-muted mb-0">
                                    <i class="ri-user-fill"></i> '
                                    . ucwords(strtolower($row->name ?? '')) .
                                '</p>
                            </div>';

                        }

                        if($primary){

                            $phone = '<div class="col">
                                        <p class="born timestamp text-muted mb-0">
                                            <i class="ri-phone-fill"></i>'. ($primary?->phone ?? '').'
                                        </p>
                                    </div>';
                        }

                        if($city || $state){

                            $address ='<div class="col">
                                <p class="text-capitalize born timestamp text-muted mb-0">
                                    <i class="ri-map-pin-2-fill"></i> ' . ($city . ' ' . $state) . '
                                </p>
                            </div>';
                        }

                        $componyName = '<div class="text-capitalize"><a href="'. route('customers.view',[$row->id]) .'" target="_blank">' . ucwords(strtolower($row->company_name ?? '')) . '</a></div>';

                        return $componyName .
                        '<div class="row">'.
                                $name.
                                $phone.
                                $address.
                        '</div>';
                    })
                    ->addColumn('call_link', function ($row) {
                        $get_phone = CustomerPhone::where('customer_id', $row->id)->where('is_primary', 1)->first();
                        $get_whatsapp = CustomerPhone::where('customer_id', $row->id)->where('is_whatsapp', 1)->first();
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

                    ->addColumn('is_active', function ($row) {
                        if ($row->is_active == 1) {
                            return '<h5><span class="badge bg-success-subtle text-success">' . 'Active' . '</span></h5>';
                        } else {
                            return '<h5><span class="badge bg-success-subtle text-danger" >' . 'In-Active' . '</span></h5>';
                        }
                    })
                    ->addColumn('action', function ($row) {
                        $user = auth()->user();
                        $html = '';

                        // Only show dropdown if user has any permissions
                        if ($user->can('edit customer') || $user->can('view customer')) {

                            $editUrl = route('customers.edit', $row->id);
                            $viewUrl = route('customers.view', $row->id);

                            $html .= '
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-fill align-middle"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">';

                            if ($user->can('edit customer')) {
                                $html .= '
                                <li>
                                    <a href="' . $editUrl . '" class="dropdown-item edit-item-btn">
                                        <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                    </a>
                                </li>';
                            }

                            if ($user->can('view customer')) {
                                $html .= '
                                <li>
                                    <a href="' . $viewUrl . '" class="dropdown-item view-item-btn">
                                        <i class="ri-eye-fill align-bottom me-2 text-muted"></i> View
                                    </a>
                                </li>';
                            }

                            $html .= '
                                </ul>
                            </div>';
                        }


                        return $html;
                    })

                    ->rawColumns(['is_active', 'action', 'cust_cont', 'call_link','customer_detail'])
                    ->make(true);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }

        return view('customer.index', $data);
    }

    public function create()
    {
        $data['country_list'] = Country::isActive()->pluck('name', 'id');
        $data['state_list'] =  Schema::hasTable('states') ? State::isActive()->pluck('name', 'id') : collect();
        $data['city_list'] = Schema::hasTable('cities') ? City::pluck('name', 'id') : collect();
        $data['lead_type_list'] = LeadType::pluck('name', 'id');
        return view('customer.create', $data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            // 'rate' => 'required|numeric',
            // 'description' => 'required',
            'email' => 'nullable|email',
            'is_active' => 'nullable',
            'company_name' => 'required',
            'gst_no' => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[0-9A-Z]{1}Z[0-9A-Z]{1}$/',

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
            'companies.*.shipping_country' => 'nullable',
            'companies.*.shipping_state'   => 'nullable',
            'companies.*.shipping_city'    => 'nullable',
            'companies.*.shipping_zipcode' => 'nullable',
            'companies.*.shipping_address_line_1' => 'nullable|string',
            'companies.*.shipping_address_line_2' => 'nullable|string',

            // 'lead_type_id' => 'required',
        ]);

        $input = $request->all();

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
        //             'error' => 'yes',
        //             'message' => $cust_check->phone . ' Customer phone already exists '.$get_user->name
        //         ], 200);
        //     }
        // }

        //check phones exists
        foreach ($request->phones as $key => $phoneData) {
            if ($phoneData['phone_type'] == 'primary') {
                $phoneData['phone_type'] = 1;
            }

            $cust_check = CustomerPhone::where('phone', $phoneData['phone'])
                ->where('is_primary', 1)
                ->first();

            if ($cust_check) {
                $get_cust = Entity::where('id',$cust_check->customer_id)->first();
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

        //gst validation check
        if(!empty($request->gst_no))
        {
            $validated = $request->validate([

                'gst_no' => [
                    'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i'
                ]]);

                $check_exist_gst = Entity::where('gst_no',$request->gst_no)->where('type','customer')->exists();

                if($check_exist_gst)
                {
                    return response()->json([
                            'error'   => 'yes',
                            'message' => $request->gst_no . ' Customer Company GST No already exists.'
                        ], 200);
                }
        }

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
        $cust = Entity::create($input);

        //customer phones
        foreach ($request->phones as $key => $phoneData)
        {
            $cust_phone_rcd = CustomerPhone::create([
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

            //address
            $billingAddress = Address::create([
                'name'           => $request->name ?? null,
                'email'          => $request->email ?? null,
                'phone'          => $request->phone ?? null,
                'country'        => $companyData['billing_country'],
                'state'          => $companyData['billing_state'],
                'city'           => $companyData['billing_city'],
                'zipcode'        => $companyData['billing_zipcode'],
                'address_line_1' => $companyData['billing_address_line_1'],
                'address_line_2' => $companyData['billing_address_line_2'] ?? null,
            ]);

            //address
            $shippingAddress = Address::create([
                'name'           => $request->name ?? null,
                'email'          => $request->email ?? null,
                'phone'          => $request->phone ?? null,
                'country'        => $companyData['shipping_country'] ?? $companyData['billing_country'],
                'state'          => $companyData['shipping_state'] ?? $companyData['billing_state'],
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

        $this->writeCustomerActivity('create', 'customer.created', $cust, 'Customer created.', [
            'name' => $cust->name,
            'company_name' => $cust->company_name,
            'gst_no' => $cust->gst_no,
            'primary_phone' => $this->customerActivitySnapshot($cust)['primary_phone'],
            'phone_count' => count($request->phones ?? []),
        ]);

        return response()->json([
            'success' => 'yes',
            'message' => 'Customer has been added successfully',
        ], 200);
    }

    public function edit($id)
    {
        $data['customer'] = Entity::with([
            'getBillingAddress.get_state',
            'getBillingAddress.get_city',
            'getBillingAddress.get_country',
            'getShippingAddress.get_state',
            'getShippingAddress.get_city',
            'getShippingAddress.get_country',
        ])->findOrFail($id);

        $data['cust_phone_list'] = CustomerPhone::where('customer_id', $id)->get();
        $data['country_list'] = Country::isActive()->pluck('name', 'id');

        $address_data = [];

        $billing_address_id = $data['customer']['billing_address_id'] ?? null;
        $company_address_id = $data['customer']['shipping_address_id'] ?? null;

        $address_data[] = $billing_address_id
            ? Address::with(['get_country', 'get_state', 'get_city'])->find($billing_address_id)
            : (object)[
                'country' => '',
                'state' => '',
                'city' => '',
                'zipcode' => '',
                'address_line_1' => '',
                'address_line_2' => '',
            ];

        $address_data[] = $company_address_id
            ? Address::with(['get_country', 'get_state', 'get_city'])->find($company_address_id)
            : (object)[
                'country' => '',
                'state' => '',
                'city' => '',
                'zipcode' => '',
                'address_line_1' => '',
                'address_line_2' => '',
            ];

        $data['address_list'] = $address_data;
        $data['lead_type_list'] = LeadType::pluck('name', 'id');

        return view('customer.edit', $data);
    }

    public function update($id, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required'],
            // 'rate' => 'required|numeric',
            // 'description' => 'required',
            'email' => 'nullable|email',
            'is_active' => 'nullable',
            'company_name' => 'required',
            'gst_no' => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[0-9A-Z]{1}Z[0-9A-Z]{1}$/',

            'phones' => 'required|array|min:1',
            // 'phones.*.phone' => ['required','regex:/^\d{10}$/'],
            // 'phones.*.phone_type' => 'required|in:primary,secondary',

            // // Billing
            'companies.*.billing_country' => 'nullable|string',
            'companies.*.billing_state'   => 'nullable|string',
            'companies.*.billing_city'    => 'nullable|string',
            'companies.*.billing_zipcode' => 'nullable|string',
            'companies.*.billing_address_line_1' => 'nullable|string',
            'companies.*.billing_address_line_2' => 'nullable|string',

            // // Shipping
            'companies.*.shipping_country' => 'nullable|string',
            'companies.*.shipping_state'   => 'nullable|string',
            'companies.*.shipping_city'    => 'nullable|string',
            'companies.*.shipping_zipcode' => 'nullable|string',
            'companies.*.shipping_address_line_1' => 'nullable|string',
            'companies.*.shipping_address_line_2' => 'nullable|string',

            // 'lead_type_id' => 'required',
        ]);


        $input = $request->all();

        $cust = Entity::findOrFail($id);
        $customerBefore = $this->customerActivitySnapshot($cust);

        //gst validation
        if($request->gst_no)
        {
            $check_exist_gst = Entity::where('gst_no',$request->gst_no)->where('id','!=',$id)->exists();

            if($check_exist_gst)
            {
                return response()->json([
                        'error'   => true,
                        'message' => $request->gst_no . ' Customer Company GST No already exists.'
                    ], 200);
            }
        }


        $cust->update($input);


        // ------ multiple phone --------

        foreach ($request->phones as $key => $phoneData) {
            if (empty($phoneData['phone'])) continue;
            $phoneType = ($phoneData['phone_type'] ?? '') === 'primary' ? 1 : 0;

            $query = CustomerPhone::where('phone', $phoneData['phone'])
                ->where('is_primary', 1);

            if (!empty($phoneData['id'])) {
                $query->where('id', '!=', $phoneData['id']);
            }

            $cust_check = $query->first();

            if ($cust_check) {

                $get_cust = Entity::where('id',$cust_check->customer_id)->first();
                if(!$get_cust)
                {
                    return response()->json([
                        'success' => false,
                        'message' => 'customer not found'
                    ], 422);
                }

                if(\Auth::user()->type != 'company')
                {
                    $get_user = User::where('id',$get_cust->user_id)->first();
                    if(!$get_user && $get_cust->user_id != null)
                    {
                        return response()->json([
                            'success' => false,
                            'message' => 'user not found'
                        ], 422);
                    }

                    if($get_user)
                    {
                        return response()->json([
                            'error'   => true,
                            'message' => $cust_check->phone . ' Customer phone already exists '.$get_user->name
                        ], 200);
                    }



                }


                return response()->json([
                    'error'   => true,
                    'message' => $cust_check->phone . ' Customer phone already exists '
                ], 200);
            }
        }

        $phones = $request->input('phones', []);
        $existingPhoneIds = CustomerPhone::where('customer_id', $cust->id)->pluck('id')->toArray();
        $submittedPhoneIds = [];

        foreach ($phones as $phoneData) {
            if (empty($phoneData['phone'])) continue;

            if (!empty($phoneData['id']) && CustomerPhone::where('id', $phoneData['id'])->where('customer_id', $cust->id)->exists()) {
                // update
                CustomerPhone::where('id', $phoneData['id'])->update([
                    'phone'        => $phoneData['phone'],
                    'is_primary'   => ($phoneData['phone_type'] ?? '') === 'primary' ? 1 : 0,
                    'is_secondary' => ($phoneData['phone_type'] ?? '') === 'secondary' ? 1 : 0,
                    'is_whatsapp'  => !empty($phoneData['is_whatsapp']) ? 1 : 0,
                ]);
                $submittedPhoneIds[] = $phoneData['id'];
            } else {
                // insert
                $newPhone = CustomerPhone::create([
                    'customer_id'  => $cust->id,
                    'phone'        => $phoneData['phone'],
                    'is_primary'   => ($phoneData['phone_type'] ?? '') === 'primary' ? 1 : 0,
                    'is_secondary' => ($phoneData['phone_type'] ?? '') === 'secondary' ? 1 : 0,
                    'is_whatsapp'  => !empty($phoneData['is_whatsapp']) ? 1 : 0,
                ]);
                $submittedPhoneIds[] = $newPhone->id;
            }
        }

        $toDelete = array_diff($existingPhoneIds, $submittedPhoneIds);
        if (!empty($toDelete)) {
            CustomerPhone::whereIn('id', $toDelete)->delete();
        }

        $primaryPhones = CustomerPhone::where('customer_id', $cust->id)
            ->where('is_primary', 1)
            ->orderBy('id', 'asc')
            ->get();

        if ($primaryPhones->count() > 1) {
            $first = $primaryPhones->first();
            $others = $primaryPhones->skip(1)->pluck('id')->toArray();
            CustomerPhone::whereIn('id', $others)->update(['is_primary' => 0]);
        }

        // ------ End multiple phone --------


        //---------- multiple company ------------
        foreach ($validated['companies'] as $companyData) {
            // Billing
            if ($cust->billing_address_id) {
                $billingAddress = Address::find($cust->billing_address_id);
                $billingAddress->update([
                    'name'           => $request->name ?? null,
                    'email'          => $request->email ?? null,
                    'phone'          => $request->phone ?? null,
                    'country'        => $companyData['billing_country'],
                    'state'          => $companyData['billing_state'],
                    'city'           => $companyData['billing_city'],
                    'zipcode'        => $companyData['billing_zipcode'],
                    'address_line_1' => $companyData['billing_address_line_1'],
                    'address_line_2' => $companyData['billing_address_line_2'] ?? null,
                ]);
            } else {
                $billingAddress = Address::create([
                    'name'           => $request->name ?? null,
                    'email'          => $request->email ?? null,
                    'phone'          => $request->phone ?? null,
                    'country'        => $companyData['billing_country'],
                    'state'          => $companyData['billing_state'],
                    'city'           => $companyData['billing_city'],
                    'zipcode'        => $companyData['billing_zipcode'],
                    'address_line_1' => $companyData['billing_address_line_1'],
                    'address_line_2' => $companyData['billing_address_line_2'] ?? null,
                ]);
            }

            // Shipping
            if ($cust->shipping_address_id) {
                $shippingAddress = Address::find($cust->shipping_address_id);
                $shippingAddress->update([
                    'name'           => $request->name ?? null,
                    'email'          => $request->email ?? null,
                    'phone'          => $request->phone ?? null,
                    'country'        => $companyData['shipping_country'] ?? $companyData['billing_country'],
                    'state'          => $companyData['shipping_state'] ?? $companyData['billing_state'],
                    'city'           => $companyData['shipping_city'] ?? $companyData['billing_city'],
                    'zipcode'        => $companyData['shipping_zipcode'] ?? $companyData['billing_zipcode'],
                    'address_line_1' => $companyData['shipping_address_line_1'] ?? $companyData['billing_address_line_1'],
                    'address_line_2' => $companyData['shipping_address_line_2'] ?? $companyData['billing_address_line_2'],
                ]);
            } else {
                $shippingAddress = Address::create([
                    'name'           => $request->name ?? null,
                    'email'          => $request->email ?? null,
                    'phone'          => $request->phone ?? null,
                    'country'        => $companyData['shipping_country'] ?? $companyData['billing_country'],
                    'state'          => $companyData['shipping_state'] ?? $companyData['billing_state'],
                    'city'           => $companyData['shipping_city'] ?? $companyData['billing_city'],
                    'zipcode'        => $companyData['shipping_zipcode'] ?? $companyData['billing_zipcode'],
                    'address_line_1' => $companyData['shipping_address_line_1'] ?? $companyData['billing_address_line_1'],
                    'address_line_2' => $companyData['shipping_address_line_2'] ?? $companyData['billing_address_line_2'],
                ]);
            }

            // Save IDs in entity
            $cust->update([
                'billing_address_id'  => $billingAddress->id,
                'shipping_address_id' => $shippingAddress->id,
            ]);
        }

        $customerChanges = ActivityLogger::diff($customerBefore, $this->customerActivitySnapshot($cust));
        if (!empty($customerChanges)) {
            $this->writeCustomerActivity('update', 'customer.updated', $cust, 'Customer updated.', [
                'changes' => $customerChanges,
            ]);
        }

        return response()->json([
            'success' => 'Customer has been Updated successfully.'
        ], 200);
    }

    public function view($id)
    {
        $customer = Entity::with([
            'getBillingAddress',
            'leads' => fn($q) => $q->latest(),
            'leads.customer',
        ])->findOrFail($id);

        $totalOrders = Order::where('customer_id', $id)->count();
        $lastOrder   = Order::where('customer_id', $id)->latest()->first();

        $quotations = Quotes::with('quoteProducts')
            ->where('customer_id', $id)
            ->latest()
            ->get();

        $totalAmount = Order::where('customer_id', $id)->sum('grand_total');

        $orders = Order::with('orderProducts')
            ->where('customer_id', $id)
            ->latest()
            ->get();
        $activityTimeline = ActivityLogger::activityForRecord($customer, null, 12, 'customer_activities_page');

        return view('customer.view', compact(
            'customer',
            'totalOrders',
            'lastOrder',
            'quotations',
            'totalAmount',
            'orders',
            'activityTimeline'
        ));
    }


    public function delete($id)
    {
        $cust = Entity::find($id);
        $cust->delete();

        return response()->json([
            'success' => 'Customer has been deleted successfully.'
        ], 200);

        return redirect()->route('customers.index');
    }
}
