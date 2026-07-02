<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleSheetService;
use Illuminate\Support\Facades\Http;
use App\Models\Entity;
use App\Models\Address;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Models\CustomerPhone;
use App\Models\LeadStage;
use App\Models\LeadSource;
use App\Models\Lead;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use RuntimeException;


class GoogleSheetImportController extends Controller
{
    private function getRequiredSetting(string $name, string $label): string
    {
        $value = trim((string) Utility::getSetting($name));

        if ($value === '') {
            throw new RuntimeException($label . ' is not configured for the current tenant.');
        }

        return $value;
    }

    private function resolveCompanyCreatorId(): int
    {
        $companyUserId = (int) (User::query()
            ->where('type', 'company')
            ->orderBy('id')
            ->value('id') ?? 0);

        if ($companyUserId <= 0) {
            throw new RuntimeException('Company user not found for the current database connection.');
        }

        return $companyUserId;
    }

    public function import(GoogleSheetService $sheet)
    {
        \Log::info('------------- import fb call --------------');

        DB::beginTransaction();

        try {
            $companyCreatorId = $this->resolveCompanyCreatorId();
            $spreadsheetId = $this->getRequiredSetting('facebook_spreadsheet_id', 'Facebook Spreadsheet ID');
            $range = 'Sheet1!A1:Z';

            $rows = $sheet->getSheetData($spreadsheetId, $range);

            if (empty($rows)) {
                return;
            }

            $header = array_shift($rows);

            $rows = array_filter($rows, function ($row) {
                return count(array_filter($row)) > 0;
            });

            $rows = array_map(function ($row) use ($header) {
                $row = array_pad($row, count($header), null);
                return array_combine($header, $row);
            }, $rows);


            foreach ($rows as $k => $row) {
                $facebook_lead_id = $row['id'] ?? '';
                $name    = $row['full_name'] ?? 'name';
                $email   = $row['email'] ?? '';
                $phone   = $row['phone_number'] ?? '';
                $address = $row['city'] ?? '';
                $platform = $row['platform'] ?? '';

                \Log::info('Imported row', [
                    'name'  => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'platform' => $platform,
                ]);

                // Clean phone
                if ($phone) {
                    $phone = preg_replace('/\D/', '', $phone);
                    if (strlen($phone) > 10) {
                        $phone = substr($phone, -10);
                    }
                }

                // Skip invalid phone
                if (strlen($phone) !== 10) {
                    continue;
                }

                // \Log::info('phone ', [$phone]);

                $l_stage_id  = LeadStage::where('name', 'new')->first();
                if ($platform) {
                    if ($platform == 'fb') {
                        $l_source_id = LeadSource::where('name', 'facebook')->first();
                    }

                    if ($platform == 'ig') {
                        $l_source_id = LeadSource::where('name', 'instagram')->first();
                    }
                }

                $existingLead = Lead::where('lead_id', $facebook_lead_id)->first();
                // \Log::info('existing lead ',[$existingLead]);
                if ($existingLead) {
                    //  \Log::info('existing lead if  ');
                    $sources = array_filter(explode(',', $existingLead->sources));

                    if (!in_array($l_source_id->id, $sources)) {
                        $sources[] = $l_source_id->id;

                        $existingLead->update([
                            'sources' => implode(',', $sources),
                        ]);
                    }
                    continue;
                }


                $check_phone_avl = CustomerPhone::where('phone', $phone)->first();
                if ($check_phone_avl) {
                    $check_customer = Entity::where('id', $check_phone_avl->customer_id)
                        ->where('type', 'customer')
                        ->first();
                }

                if (!$check_phone_avl) {
                    $cust_data['name'] = $name ?? '';
                    $cust_data['email'] = $email ?? '';
                    $cust_data['type'] = 'customer';
                    $cust_data['created_by'] = $companyCreatorId;
                    $cust_data['company_name'] = $name ? $name . ' ' : '';
                    $cust_data['user_id'] =null;

                    $check_customer = Entity::create($cust_data);

                    $cust_phone['customer_id'] = $check_customer->id;
                    $cust_phone['phone'] = $phone;
                    $cust_phone['is_primary'] = 1;
                    CustomerPhone::create($cust_phone);

                    $billingAddress = Address::create([
                        'name'           => $name,
                        'email'          => $email ?? null,
                        'phone'          => $phone ?? null,
                        'country'        => null,
                        'state'          => null,
                        'city'           => null,
                        'zipcode'        => null,
                        'address_line_1' => $address ?? null,
                        'address_line_2' => null,
                    ]);

                    $shippingAddress = Address::create([
                        'name'           => $name,
                        'email'          => $email ?? null,
                        'phone'          => $phone ?? null,
                        'country'        => null,
                        'state'          => null,
                        'city'           => null,
                        'zipcode'        => null,
                        'address_line_1' => null,
                        'address_line_2' => null,
                    ]);

                    $check_customer->update([
                        'billing_address_id'  => $billingAddress->id,
                        'shipping_address_id' => $shippingAddress->id,
                    ]);
                }

                // $check_exist_leadid = Lead::where('lead_id', $facebook_lead_id)->first();
                // if ($check_exist_leadid) {
                //     continue;
                // }

                $lead_data['name'] = $check_customer->name ?? $name;
                $lead_data['email'] = $check_customer->email ?? $email;
                $lead_data['phone'] = $check_customer->contact ?? $phone;
                $lead_data['user_id'] =  $check_customer ?  $check_customer->user_id : null;
                $lead_data['stage_id'] = $l_stage_id->id ?? null;
                $lead_data['sources'] = $l_source_id->id ?? null;
                $lead_data['created_by'] = $companyCreatorId;
                $lead_data['date'] = date('Y-m-d');
                $lead_data['customer_id'] = $check_customer->id;
                $lead_data['lead_id'] = $facebook_lead_id ?? null;

                $new_lead_id = Lead::create($lead_data);

                // UserLead::create([
                //     'user_id' => 2,//\Auth::user()->id
                //     'lead_id' => $new_lead_id->id,
                // ]);

                // // Lead Activity
                // $date = date('Y-m-d H:i:s');//\Auth::user()->id
                // Utility::add_lead_activity(
                //     $new_lead_id->id,
                //     2,
                //     'add lead detail from facebook',
                //     $date,
                //     'add'
                // );

                \Log::info('new lead id', [$new_lead_id]);
            }

            DB::commit();

            return redirect()->back()->with(['success' => "Facebook leads  has been uploaded successfully"]);
        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('Google Sheet import failed', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Import failed. Please check logs.'
            ], 500);
        }
    }

    public function india_mart_import()
    {
        Log::info('------------- IndiaMart import started --------------');

        DB::beginTransaction();

        try {
            $companyCreatorId = $this->resolveCompanyCreatorId();
            $indiaMartKey = $this->getRequiredSetting('india_mart_key', 'IndiaMart key');

            // $time_slot = 15;
            $time_slot = 3000;

            $now = Carbon::now('Asia/Kolkata');
            $startTime = $now->copy()->subMinutes($time_slot)->format('d-m-Y H:i:s');
            $endTime   = $now->format('d-m-Y H:i:s');

            // \Log::info('now ' ,[$now]);
            // \Log::info('startTime ' ,[$startTime]);
            // \Log::info('endTime ' ,[$endTime]);

            $response = Http::timeout(20)->get(
                'https://mapi.indiamart.com/wservce/crm/crmListing/v2/',
                [
                    'glusr_crm_key' => $indiaMartKey,
                    'start_time' => $startTime,
                    'end_time'   => $endTime,
                    //  'start_time'   => '03-01-2026 00:00:00',
                    // 'end_time'     => '03-01-2026 23:59:59',
                ]
            );

            Log::info('IndiaMart API Response Status', [
                'status' => $response->status(),
            ]);

            if (!$response->successful()) {
                Log::error('IndiaMart API failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                DB::rollBack();
                // return redirect()->back()->with(['error ' => "IndiaMart API failed."]);
                return false;
            }

            $data = $response->json();

            // \Log::info('code response ',[$data['CODE']]);
            if (isset($data['CODE']) && $data['CODE'] != 200) {
                // DB::commit();
                // Log::warning('IndiaMart API rate limit exceeded', $data);
                return redirect()->back()->with(
                    'error',
                    $data['MESSAGE'] ?? 'IndiaMart API failed',
                );
            }

            if (
                !isset($data['CODE']) ||
                $data['CODE'] != 200 ||
                !isset($data['STATUS']) ||
                $data['STATUS'] !== 'SUCCESS'
            ) {
                // DB::rollBack();

                // Log::warning('IndiaMart API failed', $data);

                return redirect()->back()->with(
                    'error',
                    'IndiaMart import failed. Please try again later.'
                );
            }

            if (!isset($data['RESPONSE']) || !is_array($data['RESPONSE'])) {
                // Log::info('IndiaMart empty response');
                // DB::commit();
                return redirect()->back()->with(['success' => "India Mart leads  has been uploaded successfully"]);
                return true;
            }

            foreach ($data['RESPONSE'] as $inquiry)
            {

				// \Log::info('now ' ,$inquiry);

                $name  = $inquiry['SENDER_NAME'] ?? null;
                $phone = $inquiry['SENDER_MOBILE'] ?? null;
                $email = $inquiry['SENDER_EMAIL'] ?? null;
                $address  = $inquiry['SENDER_ADDRESS'] ?? null;
                $leadId = $inquiry['UNIQUE_QUERY_ID'] ?? null;
                $company_name = $inquiry['SENDER_COMPANY'] ?? null;
                $platform='india mart';
                $subject = $inquiry['SUBJECT'] ?? null;
                $query_product_nm = $inquiry['QUERY_PRODUCT_NAME'] ?? null;
                $query_msg = trim($inquiry['QUERY_MESSAGE'] ?? '');
                $product_nm = $inquiry['QUERY_MCAT_NAME'] ?? null;
                $lead_description = trim(
                    "Subject: {$subject}\n" .
                    "Product Name: {$query_product_nm}\n" .
                    "Category: {$product_nm}\n\n" .
                    "Message:\n{$query_msg}"
                );

                if (!$phone || !$leadId) {
                    continue;
                }

                // Normalize phone
                $phone = preg_replace('/\D/', '', $phone);
                if (strlen($phone) > 10) {
                    $phone = substr($phone, -10);
                }

                if (strlen($phone) !== 10) {
                    continue;
                }

                // \Log::info('phone ', [$phone]);

				$l_stage_id  = LeadStage::where('name', 'new')->first();
                $l_source_id = LeadSource::where('name', 'india mart')->first();

				$l_stage_id  = LeadStage::where('name', 'new')->first();
                $l_source_id = LeadSource::where('name', 'india mart')->first();

                $existingLead = Lead::where('lead_id', $leadId)->first();
                // \Log::info('existing lead ',[$existingLead]);
                if ($existingLead) {
                    //  \Log::info('existing lead if  ');
                    $sources = array_filter(explode(',', $existingLead->sources));

                    if (!in_array($l_source_id->id, $sources)) {
                        $sources[] = $l_source_id->id;

                        $existingLead->update([
                            'sources' => implode(',', $sources),
                        ]);
                    }
                    continue;
                }
                // \Log::info('out');

                $check_phone_avl = CustomerPhone::where('phone', $phone)->first();
                if ($check_phone_avl) {
                    $check_customer = Entity::where('id', $check_phone_avl->customer_id)
                        ->where('type', 'customer')
                        ->first();
                }

                if (!$check_phone_avl) {
                    $cust_data['name'] = $name ?? '';
                    $cust_data['email'] = $email ?? '';
                    $cust_data['type'] = 'customer';
                    $cust_data['created_by'] = $companyCreatorId;
                    $cust_data['company_name'] = $company_name ? $company_name  : $name;
                    $cust_data['user_id'] = null;

                    $check_customer = Entity::create($cust_data);

                    $cust_phone['customer_id'] = $check_customer->id;
                    $cust_phone['phone'] = $phone;
                    $cust_phone['is_primary'] = 1;
                    CustomerPhone::create($cust_phone);

                    $billingAddress = Address::create([
                        'name'           => $name,
                        'email'          => $email ?? null,
                        'phone'          => $phone ?? null,
                        'country'        => null,
                        'state'          => null,
                        'city'           => null,
                        'zipcode'        => null,
                        'address_line_1' => $address ?? null,
                        'address_line_2' => null,
                    ]);

                    $shippingAddress = Address::create([
                        'name'           => $name,
                        'email'          => $email ?? null,
                        'phone'          => $phone ?? null,
                        'country'        => null,
                        'state'          => null,
                        'city'           => null,
                        'zipcode'        => null,
                        'address_line_1' => null,
                        'address_line_2' => null,
                    ]);

                    $check_customer->update([
                        'billing_address_id'  => $billingAddress->id,
                        'shipping_address_id' => $shippingAddress->id,
                    ]);
                }

                // $check_exist_leadid = Lead::where('lead_id', $leadId)->where('sources',$l_source_id->id)->first();
                // if ($check_exist_leadid) {
                //     continue;
                // }

                $lead_data['name'] = $check_customer->name ?? $name;
                $lead_data['email'] = $check_customer->email ?? $email;
                $lead_data['phone'] = $check_customer->contact ?? $phone;
                $lead_data['user_id'] = $check_customer ? $check_customer->user_id : null;
                $lead_data['stage_id'] = $l_stage_id->id ?? null;
                $lead_data['sources'] = $l_source_id->id ?? null;
                $lead_data['created_by'] = $companyCreatorId;
                $lead_data['date'] = date('Y-m-d');
                $lead_data['customer_id'] = $check_customer->id;
                $lead_data['lead_id'] = $leadId ?? null;
                $lead_data['notes']=$lead_description?? null;

                $new_lead_id = Lead::create($lead_data);

                Log::info('new lead id', [$new_lead_id]);
                Log::info('IndiaMart import completed');
                Log::info('------------------------------------------------------------');
            }


            DB::commit();
            return redirect()->back()->with(['success' => "India Mart leads  has been uploaded successfully"]);

            return true;

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('IndiaMart import failed catch', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);

            return redirect()->back()->with(['error' => "IndiaMart import failed"]);
            return false;
        }
    }
}
