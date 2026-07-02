<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Models\UserLead;
use App\Models\LeadStage;
use App\Models\LeadSource;
use App\Models\Utility;
use App\Models\Entity;
use App\Models\User;
use App\Models\CustomerPhone;
use App\Models\Address;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Tenant;
use App\Support\Tenancy\TenancyManager;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessLeadUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user, $jobKey, $leadsData, $tenantId;

    public function __construct($user, $jobKey, $leadsData, $tenantId = null)
    {
        Log::info('------ construct process lead upload --------');
        $this->user = $user;
        $this->jobKey = $jobKey;
        $this->leadsData = $leadsData;
        $this->tenantId = $tenantId ? (int) $tenantId : null;


        Log::info("User: ", ['usr' => $user]);
        Log::info("job key : ", ['key' => json_encode($jobKey)]);
        Log::info("lead data : ", ['lead data' => $leadsData]);
    }

    public function handle()
    {
        Log::info('------ Handle --------');

        $tenant = null;
        if (!empty($this->tenantId)) {
            $tenant = Tenant::query()->find($this->tenantId);
        }

        if ($tenant) {
            app(TenancyManager::class)->initialize($tenant);
            app()->instance('currentTenant', $tenant);
        }

        try {
            $user_data = User::find($this->user);
            $stage     = LeadStage::orderBy('order')->first();

            $validSources = LeadSource::pluck('id', 'name')->mapWithKeys(function ($id, $name) {
                return [strtolower(trim($name)) => $id];
            });

            foreach ($this->leadsData as $sheetIndex => $row) {

            if ($sheetIndex === 0) {
                continue;
            }

            if (!is_array($row)) {
                continue;
            }

            $pickValue = static function (array $rowData, array $keys, $default = null) {
                foreach ($keys as $key) {
                    if (array_key_exists($key, $rowData) && $rowData[$key] !== null && $rowData[$key] !== '') {
                        return $rowData[$key];
                    }
                }
                return $default;
            };

            $name        = trim((string) $pickValue($row, ['name', 'full_name', 'full name'], ''));
            $email       = trim((string) $pickValue($row, ['email', 'email_id', 'mail'], ''));
            $phoneRaw    = trim((string) $pickValue($row, ['phone', 'mobile', 'mobile_no', 'phone_no', 'contact'], ''));
            $phoneClean  = preg_replace('/\D/', '', $phoneRaw);
            $leadSource  = strtolower(trim((string) $pickValue($row, ['lead_source', 'lead source', 'source'], '')));
            $description = $pickValue($row, ['description', 'desc', 'remarks', 'notes']);
            $company_name = $pickValue($row, ['comp_name', 'company_name', 'company name']);
            $gst_number = $pickValue($row, ['gst_numb', 'gst_no', 'gst no', 'gst number', 'gstin', 'gst no.', 'gstin no', 'gstin number', 'gstno', 'gstinno']);
            $adhar_number = $pickValue($row, ['adhar_numb', 'adhar_no', 'aadhar_no', 'aadhar no', 'aadhaar no']);
            $udhyam_number = $pickValue($row, ['udhyam_numb', 'udhyam_no', 'udhyam no', 'udyam_no', 'udyam no']);
            $state = $row['state'] ?? null;
            $city = $row['city'] ?? null;
            $address = $row['address'] ?? null;
            $zipcode = $row['zipcode'] ?? null;

            if (empty($name) && empty($phoneClean)) {
                continue;
            }

            // ---- Email Validation ----
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Log::warning("Invalid email skipped", ['rowIndex' => $sheetIndex, 'email' => $email]);
                continue;
            }

            // ---- Phone Validation (10 digits only) ----
            if (!empty($phoneClean) && !preg_match('/^\d{10}$/', $phoneClean)) {
                Log::warning("Invalid phone skipped", ['rowIndex' => $sheetIndex, 'phone' => $phoneRaw]);
                continue;
            }

            // ---- Lead Source handling ----
            $sourceId = null;
            if (!empty($leadSource)) {
                if (isset($validSources[$leadSource])) {
                    $sourceId = $validSources[$leadSource];
                } else {
                    $newSource = LeadSource::create([
                        'name'       => ucfirst($leadSource),
                        'created_by' => $user_data->creatorId(),
                        'is_editable' => 1,
                    ]);
                    $sourceId = $newSource->id;
                    $validSources[$leadSource] = $newSource->id;
                }
            } else {
                $exist_source = LeadSource::where('name', 'other')->first();
                if ($exist_source) {
                    $sourceId = $exist_source->id;
                }
            }

            Log::info('sourceId', [$sourceId]);

            $country_id = null;
            $state_id = null;
            $city_id = null;

            $country_record = Country::where('name', 'india')->first();
            if ($country_record) {
                $country_id = $country_record->id;
            }

            $state_record = null;
            if (!empty($state)) {
                $state_record = State::whereRaw('LOWER(name) = ?', [strtolower(trim((string) $state))])->first();
            }
            if ($state_record) {
                $state_id = $state_record->id;
            }

            $city_record = null;
            if (!empty($city)) {
                $city_record = City::whereRaw('LOWER(name) = ?', [strtolower(trim((string) $city))])->first();
            }
            if ($city_record) {
                $city_id = $city_record->id;
            }

            $hasAddressData = !empty($country_id) || !empty($state_id) || !empty($city_id) || !empty($address) || !empty($zipcode);
            $addressPayload = [
                'address_line_1' => $address,
                'city' => $city_id,
                'state' => $state_id,
                'country' => $country_id,
                'zipcode' => $zipcode,
            ];

            try
            {
                // ---- Entity (Customer) handling ----

                DB::beginTransaction();

                $shipping_adr = null;
                $billing_adr = null;
                $company_id = null;


                //check cust phone exist
                $customer_phone = CustomerPhone::where('phone', $phoneClean)->where('is_primary', 1)->first();
                if (!empty($customer_phone)) {
                    $customer_exist = Entity::where('id', $customer_phone->customer_id)->first();
                    if ($customer_exist) {
                        // $comp_detail = Company::where('customer_id', $customer_exist->id)->first();
                        // if ($comp_detail) {
                        //     $company_id = $comp_detail->id;
                        // }
                    }
                }

                if (!$customer_phone) {
                    //create customer

                    if($user_data->type == 'Sales')
                    {
                        $usr_id =$user_data->id;
                    }
                    else
                    {
                        $usr_id = null;
                    }
                    $customer_exist = Entity::create([
                        'type'       => 'customer',
                        'name'       => $name,
                        'email'      => $email,
                        // 'contact'    => $phoneRaw,
                        'created_by' => $user_data->creatorId(),
                        'company_name'=>$company_name ?? $name,
                        'gst_no' => $gst_number ?? null,
                        'company_adhar_no'=>$adhar_number ?? null,
                        'company_udhyam_no'=>$udhyam_number ?? null,
                        'user_id'=>$usr_id,
                    ]);


                    //cust phone create
                    $cust_ph['customer_id'] = $customer_exist->id;
                    $cust_ph['phone'] = $phoneClean;
                    $cust_ph['is_primary'] = 1;
                    CustomerPhone::create($cust_ph);
                }

                if ($hasAddressData) {
                    if (!empty($customer_exist->billing_address_id)) {
                        $billingAddress = Address::find($customer_exist->billing_address_id);
                        if ($billingAddress) {
                            $billingAddress->update($addressPayload);
                            $billing_adr = $billingAddress->id;
                        }
                    }

                    if (empty($billing_adr)) {
                        $billing_adr = Address::create($addressPayload)->id;
                    }

                    if (!empty($customer_exist->shipping_address_id)) {
                        $shippingAddress = Address::find($customer_exist->shipping_address_id);
                        if ($shippingAddress) {
                            $shippingAddress->update($addressPayload);
                            $shipping_adr = $shippingAddress->id;
                        }
                    }

                    if (empty($shipping_adr)) {
                        $shipping_adr = Address::create($addressPayload)->id;
                    }

                    $customer_exist->update([
                        'billing_address_id' => $billing_adr,
                        'shipping_address_id' => $shipping_adr,
                    ]);
                }

                // ---- Lead Insert ----
                if($user_data->type == 'Sales')
                {
                    $lead_usr_id =$customer_exist->user_id;
                }
                else
                {
                    $lead_usr_id = null;
                }

                $lead = Lead::create([
                    'name'        => $customer_exist->name,
                    'email'       => $customer_exist->email,
                    // 'phone'       => $phoneRaw,
                    'sources'     => $sourceId,
                    'user_id'     => $lead_usr_id,
                    'stage_id'    => $stage ? $stage->id : null,
                    'notes'       => $description,
                    'created_by'  => $user_data->creatorId(),
                    'date'        => date('Y-m-d'),
                    'customer_id' => $customer_exist->id,
                    // 'company_id'  => $company_id,
                ]);

                // ---- Assign lead to user ----
                // UserLead::create([
                //     'user_id' => $user_data->id,
                //     'lead_id' => $lead->id,
                // ]);

                //Lead Activity
                $date = date('Y-m-d H:i:s');
                Utility::add_lead_activity($lead->id, $user_data->id, 'add lead detail', $date, 'add');

                // Log::info("Lead inserted", [
                //     'sheetIndex' => $sheetIndex,
                //     'lead_id'    => $lead->id,
                // ]);

                 DB::commit();

            } catch (\Exception $e) {

                DB::rollback();

                Log::error('Lead insert failed', [
                    'sheetIndex' => $sheetIndex,
                    'error'      => $e->getMessage(),
                    'data'       => $row,
                ]);
            }
        }

        Cache::put($this->jobKey, 'completed', now()->addMinutes(10));
        $this->delete();
        } finally {
            if ($tenant) {
                app()->forgetInstance('currentTenant');
                app(TenancyManager::class)->end();
            }
        }
    }
}
