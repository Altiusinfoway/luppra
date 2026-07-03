<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Imports\LeadImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\ProcessLeadUpload;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use App\Models\Utility;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use App\Models\UserLead;
use App\Models\LeadSource;
use App\Models\LeadStage;
use App\Models\Products;
use App\Models\LeadProducts;
use App\Models\Quotes;
use App\Models\LeadCall;
use App\Models\LeadChat;
use App\Models\Entity;
use App\Models\CustomerPhone;
use App\Models\ThirdParty;
use App\Models\Employee;
use App\Models\LeadType;
use App\Models\Address;
use App\Models\Country;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use App\Services\ActivityLogger;


class LeadController extends Controller
{
    private function resolveLead(int|string $id): ?Lead
    {
        return Lead::query()->find((int) $id);
    }

    private function userCanAccessLead(Lead $lead): bool
    {
        $creatorId = \Auth::user()->creatorId();
        $isSales = \Auth::user()->type == 'Sales';

        if ($isSales) {
            return ((int) $lead->user_id === (int) \Auth::id())
                || UserLead::where('user_id', \Auth::id())->where('lead_id', $lead->id)->exists();
        }

        return ((int) $lead->created_by === (int) $creatorId);
    }

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

    private function resolveUserNames(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        return User::whereIn('id', $userIds)
            ->pluck('name')
            ->values()
            ->all();
    }

    private function resolveLeadProduct(int|string $id): ?LeadProducts
    {
        return LeadProducts::query()->find((int) $id);
    }

    private function resolveLeadStage(int|string $id): ?LeadStage
    {
        return LeadStage::query()->find((int) $id);
    }

    private function resolveCustomer(int|string $id): ?Entity
    {
        return Entity::query()->find((int) $id);
    }

    public function index(Request $request)
    {
        if (\Auth::user()->can('manage lead'))
        {
            $leadStagies = LeadStage::orderBy('order')->get(); //where('created_by', '=', \Auth::user()->creatorId())->
            $sources = LeadSource::get()->pluck('name', 'id'); //where('created_by', '=', \Auth::user()->creatorId())->
            $products = Products::get()->pluck('name', 'id'); //where('created_by', '=', \Auth::user()->creatorId())->
            $stages = LeadStage::get()->pluck('name', 'id'); //where('created_by', '=', \Auth::user()->creatorId())->
            // $coutries = Coutries::isActive()->get();


            $lead_data = collect();
            $creatorId = \Auth::user()->creatorId();
            $assignedLeadIds = [];

            if (\Auth::user()->type == 'Sales') {
                $directLeadIds = Lead::where('user_id', \Auth::id())->pluck('id')->toArray();
                $mappedLeadIds = UserLead::where('user_id', \Auth::id())->pluck('lead_id')->toArray();
                $assignedLeadIds = array_values(array_unique(array_merge($directLeadIds, $mappedLeadIds)));
            }

            $hasFilter = $request->filled('date') ||
                $request->filled('sources') || $request->filled('products') ||
                $request->filled('stage') ||  request()->filled('lead_type_filter');

            $lead_data = collect();

            if ($hasFilter) {

                if(\Auth::user()->type == 'company')
                {
                    $query = Lead::where('created_by', $creatorId);
                }
                else
                {
                    $query = Lead::where(function ($q) use ($assignedLeadIds) {
                        $q->where('user_id', \Auth::id());
                        if (!empty($assignedLeadIds)) {
                            $q->orWhereIn('id', $assignedLeadIds);
                        }
                    });
                }

                if ($request->filled('customer_id')) {
                    $query->where('customer_id', $request->customer_id);
                }

                // Sources filter
                if ($request->has('sources') && is_array($request->sources)) {
                    $query->where(function ($q) use ($request) {
                        foreach ($request->sources as $src) {
                            $q->orWhereRaw('FIND_IN_SET(?, sources)', [$src]);
                        }
                    });
                }

                // Products filter
                if ($request->has('products') && is_array($request->products)) {
                    $query->where(function ($q) use ($request) {
                        foreach ($request->products as $prod) {
                            $q->orWhereRaw('FIND_IN_SET(?, products)', [$prod]);
                        }
                    });
                }

                // Stage filter
                if ($request->filled('stage')) {
                    $query->where('stage_id', $request->stage);
                }

                // Date filter
                if ($request->filled('date')) {
                    $query->whereDate('date', '=', $request->date);
                }

                //lead-type filter
                if ($request->filled('lead_type_filter')) {
                    $query->where('lead_type_id', $request->lead_type_filter);
                }

                $query->where(['is_converted' => 0]);
                if (\Auth::user()->type != 'company') {
                    $query->where(function ($q) use ($assignedLeadIds) {
                        $q->where('user_id', \Auth::id());
                        if (!empty($assignedLeadIds)) {
                            $q->orWhereIn('id', $assignedLeadIds);
                        }
                    });
                }

                $lead_data = $query->orderBy('id', 'desc')->get();
            }
            if ($lead_data->isEmpty()) {
                $lead_data = collect();
            }

            $lead_type_list = LeadType::pluck('name', 'id');
            $device = \App\Models\Device::where('user_id', \Auth::id())
                ->first();

            return view('leads.index', compact('leadStagies', 'sources', 'products', 'stages', 'lead_data', 'lead_type_list', 'device', 'assignedLeadIds'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function list(Request $request,$slug = 'all_leads')
    {

        if (\Auth::user()->can('manage lead')) {

            if ($request->ajax()) {

                try {

                    $productFilter = $request->input('products');
                    $sourceFilter = $request->input('sources');
                    $stageFilter = $request->input('stage');
                    $dateFilter = $request->input('date');
                    $listType = $request->input('listType');
                    $custFilter = $request->input('customer_list');
                    $leadTypeFilter = $request->input('lead_type_filter');
                    $saleFilter = $request->input('sales_list');


                    if (\Auth::user()->type == 'company') {
                        $query = Lead::with('getLeadType')->select('id', 'name', 'email', 'phone', 'user_id', 'stage_id', 'sources', 'products', 'created_by', 'date', 'customer_id', 'lead_type_id');
                    } else {
                        $query = Lead::with('getLeadType')->where('user_id', \Auth::user()->id)->select('id', 'name', 'email', 'phone', 'user_id', 'stage_id', 'sources', 'products', 'created_by', 'date', 'customer_id', 'lead_type_id');
                    }


                    if (\Auth::user()->type != 'company') {

                        /* stage_id is not null and user_id == 1
                        or
                        stage_id is not null and user_id is null */

                        if ($listType == 'my') {

                            $query->where('user_id', \Auth::user()->id);
                        } else {

                            $query->where(function ($q) {

                                $q->where(function ($sq) {

                                    $sq->whereNotNull('stage_id')->where('user_id', \Auth::user()->id);
                                })->orWhere(function ($sq) {

                                    $sq->whereNotNull('stage_id')->whereNull('user_id');
                                });
                            });
                        }
                    }

                    if ($request->name) {
                        $query->where('name', 'like', '%' . $request->name . '%');
                    }

                    if ($productFilter && is_array($productFilter) && !empty($productFilter)) {
                        $query->where(function ($query) use ($productFilter) {
                            foreach ($productFilter as $product) {
                                $query->orWhereRaw('FIND_IN_SET(?, products)', [$product]);
                            }
                        });
                    }

                    if ($sourceFilter && is_array($sourceFilter) && !empty($sourceFilter)) {
                        $query->where(function ($query) use ($sourceFilter) {
                            foreach ($sourceFilter as $source) {
                                $query->orWhereRaw('FIND_IN_SET(?, sources)', [$source]);
                            }
                        });
                    }

                    if ($stageFilter) {
                        $query->where('stage_id', $stageFilter);
                    }

                    if ($dateFilter) {
                        $query->whereDate('date', $dateFilter);
                    }

                    if ($custFilter) {
                        $query->where('customer_id', $custFilter);
                    }

                    if ($leadTypeFilter) {
                        $query->where('lead_type_id', $leadTypeFilter);
                    }
                    if ($saleFilter) {
                        $query->where('user_id', $saleFilter);
                    }

                    $lead_new_stage = LeadStage::where('name', 'new')->first();

                     if($slug == 'all_leads')
                    {
                        $data = $query->orderBy('id', 'desc')->get();
                    }
                    elseif($slug == 'new_leads')
                    {
                        $data =  $query->where('stage_id', $lead_new_stage->id)->orderBy('id', 'desc')->get();
                    }
                    else
                    {
                        $data =  $query->whereNotNull('user_id')->orderBy('id', 'desc')->get();
                    }


                    $device = \App\Models\Device::where('user_id', \Auth::user()->id)
                        ->first();
                    $deviceUuid = $device?->uuid;
                    $deviceScanUrl = $deviceUuid ? route('device.scan', $deviceUuid) : null;
                    $formatWhatsappPhone = function ($phone) {
                        $digits = preg_replace('/\D+/', '', (string) $phone);
                        if ($digits === '') {
                            return '';
                        }

                        if (str_starts_with($digits, '91') && strlen($digits) === 12) {
                            return $digits;
                        }

                        if (strlen($digits) === 10) {
                            return '91' . $digits;
                        }

                        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
                            return '91' . substr($digits, 1);
                        }

                        return $digits;
                    };

                    return DataTables::of($data)
                        ->addIndexColumn()
                        ->addColumn('checkboxes', function ($row) {
                            return '<div class="form-check"><input class="form-check-input fs-15" type="checkbox" id="checkAll" value="' . $row->id . '"></div>';
                        })

                        ->addColumn('cust_phone', function ($row) use ($device, $deviceUuid, $deviceScanUrl, $formatWhatsappPhone) {
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

                            $whatsappPhoneRaw = $get_whatsapp?->phone ?? $get_phone?->phone ?? $row->phone ?? '';
                            $whatsappPhone = $formatWhatsappPhone($whatsappPhoneRaw);

                            if (!empty($whatsappPhone)) {
                                $chatUrl = $device
                                    ? url('device/chats/' . $device->uuid) . '?phone=' . $whatsappPhone
                                    : 'https://wa.me/' . $whatsappPhone;
                                $whatsappAttrs = $deviceUuid && $deviceScanUrl
                                    ? ' class="avatar-title bg-success-subtle text-success fs-16 rounded js-wa-chat-entry" title="WhatsApp Chat"
                                                data-chat-url="' . e($chatUrl) . '"
                                                data-qr-url="' . e($deviceScanUrl) . '"
                                                data-device-uuid="' . e($deviceUuid) . '"'
                                    : ' class="avatar-title bg-success-subtle text-success fs-16 rounded" title="WhatsApp Chat" target="_blank" rel="noopener"';
                                $div_wp = '<li class="list-inline-item avatar-xs">
                                            <a href="' . $chatUrl . '"' . $whatsappAttrs . '>
                                                <i class="ri-whatsapp-line"></i>
                                            </a>
                                        </li>';
                            }

                            return '<ul class="list-inline mb-0 text-center">' . $div_phone . $div_wp . '</ul>';
                        })

                        ->addColumn('name', function ($row) {
                            $name = '';
                            $phone = '';
                            $address = '';
                            $email = '';
                            $getLeadType = '';
                            $company = $row->customer;

                            if ($company->name) {

                                    $name = ucwords(strtolower($company->name ?? ''));
                            }
                            $primary = $row->customerPhone()->where('is_primary', 1)->first();

                            $address = '';
                            if ($primary) {

                                    $phone = '<div>
                                            <p class="born timestamp text-muted mb-0">
                                                <i class="ri-phone-fill"></i>' . ($primary?->phone ?? '') . '
                                            </p>
                                        </div>';
                                }
                                if ($company->email) {

                                    $email = '<div>
                                            <p class="born timestamp text-muted mb-0">
                                                <i class="fa-solid fa-envelope"></i> ' . ($company->email ?? '') . '
                                            </p>
                                            </div>';
                                }

                                $getLeadType = '<div>
                                            <p class="born timestamp text-muted mb-0">
                                                <i class="fa-solid fa-lines-leaning"></i> ' . ($row->getLeadType->name ?? '-') . '
                                            </p>
                                            </div>';


                            if ($company && $company->getBillingAddress)
                            {
                                $billingAddress = $company->getBillingAddress;

                                $city  = optional(optional(optional($company)->getBillingAddress)->get_city)->name;
                                $state = optional(optional(optional($company)->getBillingAddress)->get_state)->name;

                                // $name = $phone = $address = $email = '';


                                if ($city || $state) {

                                    $address = '<div>
                                    <p class="text-capitalize born timestamp text-muted mb-0">
                                        <i class="ri-map-pin-2-fill"></i> ' . ($city . ' ' . $state) . '
                                    </p>
                                </div>';
                                }
                            }

                            return
                                '<div class="d-flex justify-content-between">' .
                                '<div>' .
                                ($name ?? '') .
                                '</div>' .
                                '</div>' .
                                '<div class="d-flex justify-content-between">' .
                                $phone .
                                $address .
                                '</div>
                                <div class="d-flex justify-content-between">' .
                                $getLeadType .
                                $email .
                                '</div>';
                        })

                        ->addColumn('createdAt', function ($row) {

                            return Utility::getDateFormated($row->date);
                        })
                        ->addColumn('lead_type_name', function ($row) {

                            return $row->getLeadType->name ?? '-';
                        })
                        ->addColumn('stages', function ($row) {

                            // return '<span class="badge" style="background-color:'.$row->stage->color.';">'.$row->stage->name.'</span>';

                            $options = '<option value=""> Select Stage</option>';
                            foreach (LeadStage::all() as $stage) {
                                $selected = $row->stage_id == $stage->id ? 'selected' : '';
                                $options .= '<option value="' . $stage->id . '" ' . $selected . '>' . $stage->name . '</option>';
                            }


                            $toggleBtn = '';

                            if (\Auth::user()->type != 'company') {
                                $lead_avil_in_quote = Quotes::where('lead_id',$row->id)->count();
                                if($lead_avil_in_quote == 0)
                                {
                                    $toggleBtn = '<div class="col"><button type="button" class="btn btn-sm btn-outline-secondary custom-toggle ' . ((\Auth::user()->id == $row->user_id) ? 'active' : '') . '" data-bs-toggle="button" data-data="{id:' . $row->id . '}" data-url="' . route('leads.assign.user', [$row->id, \Auth::user()->id, ((\Auth::user()->id != $row->user_id) ? 1 : 0)]) . '">
                                    <span class="icon-on"><i class="ri-add-line align-bottom me-1"></i> Pickup</span>
                                    <span class="icon-off"><i class="ri-user-unfollow-line align-bottom me-1"></i> Give Up </span>
                                </button></div>';
                                }

                            } else {

                                $toggleBtn = '<div class="col">
                                                        <h5>
                                                        <span class="badge bg-primary me-1">' . optional($row->user)->name . '</span>
                                                        </h5>
                                            </div>';
                            }

                            return '<div class="row"><div class="col"><select class="form-control stage-dropdown form-select form-select-sm need-confirmation" data-data="{id:' . $row->id . '}" data-url="' . route('leads.stage.update', [$row->id, '#sticky']) . '" aria-label=".form-select-sm example">' . $options . '</select></div>' . $toggleBtn . '</div>';
                        })
                        ->addColumn('sources', function ($row) {

                            $res = '';
                            if (!is_null($row->sources) && $row->sources != "") {

                                foreach ($row->sources() as $key => $val) {

                                    $res .= '<h5><span class="badge bg-info me-1">' . $val->name . '</span></h5>';
                                }
                            }

                            return $res;
                        })
                        ->addColumn('action', function ($row) use ($device, $deviceUuid, $deviceScanUrl, $formatWhatsappPhone) {

                            $whatsapp_msg = CustomerPhone::where('customer_id', $row->customer_id)
                                ->where('is_whatsapp', 1)
                                ->first();


                            $html  = '<div class="dropdown d-inline-block">
                                        <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-more-fill align-middle"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">';

                            // View
                            $html .= '<li><a href="' . route('leads.view', [$row->id]) . '" class="dropdown-item">
                                        <i class="ri-eye-fill align-bottom me-2 text-muted"></i> View
                                    </a></li>';

                            // Edit
                            $html .= '<li><a class="dropdown-item edit-item-btn" href="javascript:void(0);"
                                            data-size="lg"
                                            data-url="' . route("leads.edit", [$row->id]) . '"
                                            data-ajax-popup="true"
                                            data-bs-original-title="Edit Lead">
                                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                    </a></li>';

                            // Duplicate
                            $html .= '<li><a href="' . route('leads.duplicate', [$row->id]) . '" class="dropdown-item">
                                        <i class="ri-file-copy-fill align-bottom me-2 text-muted"></i> Duplicate
                                    </a></li>';

                            $whatsappPhoneRaw = $whatsapp_msg?->phone
                                ?? optional(CustomerPhone::where('customer_id', $row->customer_id)->where('is_primary', 1)->first())->phone
                                ?? $row->phone
                                ?? '';
                            $wpPhone = $formatWhatsappPhone($whatsappPhoneRaw);
                            if (!empty($wpPhone)) {
                                    $chatUrl = $device
                                        ? url('device/chats/' . $device->uuid) . '?phone=' . $wpPhone
                                        : 'https://wa.me/' . $wpPhone;
                                    $whatsappAttrs = $deviceUuid && $deviceScanUrl
                                        ? ' class="dropdown-item js-wa-chat-entry"
                                                data-chat-url="' . e($chatUrl) . '"
                                                data-qr-url="' . e($deviceScanUrl) . '"
                                                data-device-uuid="' . e($deviceUuid) . '"'
                                        : ' class="dropdown-item" target="_blank" rel="noopener"';
                                    $html .= '<li><a href="' . $chatUrl . '"' . $whatsappAttrs . '>
                                                <i class="ri-whatsapp-line align-bottom me-2 text-success"></i> WhatsApp Chat
                                            </a></li>';
                            }

                            $html .= '</ul></div>';

                            return $html;
                        })

                        ->rawColumns(['cust_phone', 'checkboxes', 'created_by', 'createdAt', 'stages', 'sources', 'action', 'name', 'lead_type_name'])

                        ->make(true);
                } catch (\Exception $e) {

                    return response()->json([
                        'error' => 'Server Error: ' . $e->getMessage()
                    ], 500);
                }
            }

            $list_data['sources'] = LeadSource::get()->pluck('name', 'id'); //where('created_by', '=', \Auth::user()->creatorId())->
            $list_data['products'] = Products::get()->pluck('name', 'id'); //where('created_by', '=', \Auth::user()->creatorId())->
            $list_data['stages'] = LeadStage::get()->pluck('name', 'id'); //where('created_by', '=', \Auth::user()->creatorId())->
            $list_data['cust_list'] = Entity::GetCustomer()->pluck('name', 'id');
            $list_data['lead_type_list'] = LeadType::pluck('name', 'id');

            if(\Auth::user()->type == 'Sales')
            {
                $list_data['sales_user_list'] = User::Isdeleted()->where('type', 'Sales')->where('id',\Auth::user()->id)->pluck('name', 'id');
            }
            else
            {
                 $list_data['sales_user_list'] = User::Isdeleted()->where('type', 'Sales')->pluck('name', 'id');
            }

            $list_data['slug'] = $slug;

            return view('leads.list', $list_data);
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function create(Request $request){
        if (\Auth::user()->can('create lead')) {
            if (!$request->ajax()) {
                return redirect()->route('leads.index');
            }

            $country_list = Country::isActive()->pluck('name','id');
            return view('leads.create', compact('country_list'));
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // 'company'             => 'required',
            'phones.phone'        => 'required|array|min:1',
            'phones.phone.*'      => 'required|numeric|digits:10',
            'phones.phone_type'   => 'required|array|min:1',
            'phones.phone_type.*' => 'required|in:primary,secondary',
        ]);

        $usr = \Auth::user();
        $stage = LeadStage::select('id')->orderBy('order')->first();
        if (empty($stage)) {
            return response()->json([
                'success' => 'error',
                'message' => 'Please Create Stage for Leads.',
            ]);
        }

        //customer alredy exist
        if ($request->customer_id != 0) {
            $cust_id = $request->customer_id;
        } else {
            //new customer create

            if(\Auth::user()->type == 'Sales')
            {
                $usr_id =\Auth::user()->id;
            }
            else
            {
                $usr_id = null;
            }
            $customer = Entity::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'type' => 'customer',
                'created_by' => \Auth::user()->creatorId(),
                'company_name'=>$request['name'],
                'user_id'=>$usr_id,
            ]);

            $address_new = [
                'country'        => $request->billing_country ?? null,
                'state'          => $request->billing_state ?? null,
                'city'           => $request->billing_city ?? null,
                'zipcode'        => $request->billing_zipcode ?? null,
                'address_line_1' => $request->billing_address_line_1 ?? null,
                'address_line_2' => $request->billing_address_line_2 ?? null,
            ];

            $billing_adr = Address::create($address_new);
            $shipping_adr = Address::create($address_new);

            $customer->update(['billing_address_id' => $billing_adr->id, 'shipping_address_id' => $shipping_adr->id]);

            $cust_id = $customer->id;
        }

        //customer inside user_id val assign
        if(\Auth::user()->type != 'company')
        {
            $check_customer_rcd = Entity::where('id',$cust_id)->first();
            if($check_customer_rcd && $check_customer_rcd->user_id == null)
            {
                $check_customer_rcd->update(['user_id'=>\Auth::user()->id]);
            }
        }


        foreach ($request->phones['phone'] as $index => $phone) {
            if (empty($phone)) {
                continue;
            }

            $phoneType  = $request->phones['phone_type'][$index] ?? null;
            $isWhatsapp = $request->phones['is_whatsapp'][$index] ?? 0;

            $cust_phone_exist = CustomerPhone::where('customer_id', $cust_id)
                ->where('phone', $phone)
                ->first();
            if ($cust_phone_exist) {
                continue;
            }
            CustomerPhone::create([
                'customer_id'  => $cust_id,
                'phone'        => $phone,
                'is_primary'   => $phoneType === 'primary' ? 1 : 0,
                'is_secondary' => $phoneType === 'secondary' ? 1 : 0,
                'is_whatsapp'  => $isWhatsapp ? 1 : 0,
            ]);
        }

        $stage = LeadStage::select('id')->orderBy('order')->first();

        if (!empty($stage)) {
            $cust_detail = Entity::where('id', $cust_id)->first();

            //customer address update
            if ($request->billing_country && $request->billing_state && $request->billing_city && $request->billing_zipcode && $request->billing_address_line_1) {
                $address_up = [
                    'country'        => $request->billing_country ?? null,
                    'state'          => $request->billing_state ?? null,
                    'city'           => $request->billing_city ?? null,
                    'zipcode'        => $request->billing_zipcode ?? null,
                    'address_line_1' => $request->billing_address_line_1 ?? null,
                    'address_line_2' => $request->billing_address_line_2 ?? null,
                ];

                if ($request->billing_address_id) {
                    $cust_address_id = Address::where('id', $request->billing_address_id)->first();
                    if ($cust_address_id) {
                        $cust_address_id->update($address_up);
                    }
                } else {
                    $bill_address = Address::create($address_up);
                    $ship_address = Address::create($address_up);

                    $cust_detail->update(['billing_address_id' => $bill_address->id, 'shipping_address_id' => $ship_address->id]);
                }
            }

            $lead              = new Lead();
            $lead->name        = isset($cust_detail) ? $cust_detail->name : null;
            $lead->email       = isset($cust_detail) ? $cust_detail->email : $request->email;
            // $lead->phone       = $request->phone;
            $lead->user_id     = \Auth::user()->type !== 'company' ? \Auth::user()->id : null;
            $lead->sources     = $request->lead_source;
            $lead->stage_id    = $request->stage_id ?? null;
            $lead->notes       = $request->description;
            $lead->created_by  = $usr->creatorId();
            $lead->date        = date('Y-m-d');
            $lead->customer_id = isset($cust_detail) ? $cust_detail->id : null;
            $lead->lead_type_id  = $request->lead_type_id;
            $lead->save();


            // $usrLeads = [
            //     \Auth::user()->id,
            // ];

            // foreach ($usrLeads as $usrLead) {
            //     UserLead::create(
            //         [
            //             'user_id' => $usrLead,
            //             'lead_id' => $lead->id,
            //         ]
            //     );
            // }


            // Send Email
            // $usrEmail = User::find($request->user_id);
            // $setings = Utility::settings();
            // if($setings['lead_assigned'] == 1)
            // {
            //     $usrEmail = User::find($request->user_id);
            //     $leadAssignArr = [
            //         'lead_name' => $lead->name,
            //         'lead_email' => $lead->email,
            //         'lead_subject' => $lead->subject,
            //         'lead_pipeline' => $pipeline->name,
            //         'lead_stage' => $stage->name,
            //     ];
            //     $resp = Utility::sendEmailTemplate('lead_assigned', [$usrEmail->id => $usrEmail->email], $leadAssignArr);
            // }

            //Lead Activity
            $date = date('Y-m-d H:i:s');
            Utility::add_lead_activity($lead->id, \Auth::user()->id, 'add lead detail', $date, 'add');

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
        }

        return response()->json([
            'success' => 'success',
            'message' => 'Lead successfully created',
        ]);
    }

    public function edit($lead)
    {
        $lead = $this->resolveLead($lead);
        if (!$lead) {
            return response()->json(['error' => __('Lead not found for this tenant.')], 404);
        }

        if (\Auth::user()->can('edit lead'))
        {


            $sources        = LeadSource::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            $products       = Products::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            $users          = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', '!=', 'client')->where('type', '!=', 'company')->where('id', '!=', \Auth::user()->id)->get()->pluck('name', 'id');

            $lead_type        = LeadType::get()->pluck('name', 'id');

            $lead->sources  = explode(',', $lead->sources);
            $lead->products = explode(',', $lead->products);

            $entity = $lead->customer;

            $customer_phone_list = CustomerPhone::where('customer_id', $lead->customer_id)->get();
            if (count($customer_phone_list) < 0) {
                $customer_phone_list = [];
            }

            $country_list = Country::isActive()->pluck('name', 'id');
            $customer_address = null;

            if (!empty($lead->customer?->billing_address_id)) {
                $customer_address = Address::find($lead->customer?->billing_address_id);
            }
            $customer_name  = $entity?->name;
            $customer_email = $entity?->email;


            // $customer_comp_list = Company::where('customer_id',$lead->customer_id)->pluck('company_name','id');

            return view('leads.edit', compact('lead', 'sources', 'users', 'products', 'entity', 'customer_phone_list',
            'lead_type','country_list','customer_address','customer_name','customer_email'));
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function update(Request $request, $lead)
    {
        $lead = $this->resolveLead($lead);
        if (!$lead) {
            return response()->json([
                'success' => 'error',
                'message' => 'Lead not found for this tenant.',
            ], 404);
        }

        if (\Auth::user()->can('edit lead')) {


            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    // 'email' => 'required|email',
                    //'user_id' => 'required',  //Assign to
                    //'stage_id' => 'required',
                    'stage_id' => [
                        Rule::requiredIf(\Auth::user()->type !== 'company'),
                    ],
                    // 'sources' => 'required',
                    // 'products' => 'required',
                    // 'notes' => 'required|max:200',
                    // 'next_contact_date' => 'required',


                    'phones.phone'        => 'required|array|min:1',
                    'phones.phone.*'      => 'required|numeric|digits:10',
                    'phones.phone_type'   => 'required|array|min:1',
                    'phones.phone_type.*' => 'required|in:primary,secondary',

                    // 'products'     => 'required|array|min:1',
                    // 'products.*'   => 'required|exists:products,id',

                    'sources'     => 'required|array|min:1',
                    'sources.*'   => 'required|exists:lead_sources,id',
                    //  'billing_country'=>'required',
                    // 'billing_state'=>'required',
                    // 'billing_city'=>'required',
                    // 'billing_zipcode'=>'required',
                    // 'billing_address_line_1'=>'required',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'success' => 'error',
                    'errors'  => $validator->errors()
                ], 422);
            }

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            $cust_id = $lead->customer_id;

            //check customer exist
            foreach ($request->phones['phone'] as $index => $phone) {
                if (empty($phone)) {
                    continue;
                }

                $cust_phone_exist = CustomerPhone::where('is_primary', 1)->where('customer_id', '!=', $cust_id)
                    ->where('phone', $phone)
                    ->first();

                if ($cust_phone_exist)
                {
                    $get_cust = Entity::where('type','customer')->where('id',$cust_phone_exist->customer_id)->first();
                    if($get_cust)
                    {
                        $get_user = User::where('id',$get_cust->user_id)->first();
                        if($get_user)
                        {
                                return response()->json([
                                    'success'   => 'error',
                                    'message' => $phone . ' phone already exists. & this customer is assign to '.$get_user->name,
                                ], 200);
                        }
                    }
                }

            }



            $submittedIds = $request->phone_id ?? [];

            CustomerPhone::where('customer_id', $cust_id)
                ->whereNotIn('id', $submittedIds)
                ->delete();

            foreach ($request->phones['phone'] as $index => $phone) {
                if (empty($phone)) {
                    continue;
                }

                $phoneType  = $request->phones['phone_type'][$index] ?? null;
                $isWhatsapp = $request->phones['is_whatsapp'][$index] ?? 0;

                $phoneId = $request->phone_id[$index] ?? null;

                if ($phoneId) {
                    // update existing
                    CustomerPhone::where('id', $phoneId)
                        ->where('customer_id', $cust_id)
                        ->update([
                            'phone'        => $phone,
                            'is_primary'   => $phoneType === 'primary' ? 1 : 0,
                            'is_secondary' => $phoneType === 'secondary' ? 1 : 0,
                            'is_whatsapp'  => $isWhatsapp ? 1 : 0,
                        ]);
                } else {
                    // insert new

                    CustomerPhone::create([
                        'customer_id'  => $cust_id,
                        'phone'        => $phone,
                        'is_primary'   => $phoneType === 'primary' ? 1 : 0,
                        'is_secondary' => $phoneType === 'secondary' ? 1 : 0,
                        'is_whatsapp'  => $isWhatsapp ? 1 : 0,
                    ]);
                }
            }


            $leadBefore = [
                'name' => $lead->name,
                'email' => $lead->email,
                'sources' => $lead->sources,
                'products' => $lead->products,
                'notes' => $lead->notes,
                'next_contact_date' => $lead->next_contact_date,
                'lead_type_id' => $lead->lead_type_id,
            ];
            $previousStageId = (int) ($lead->stage_id ?? 0);

            //customer update
            $cust = $lead->customer;
            $cust->update([
                'name' => $request->name,
                'email' => $request->email,
                'lead_type_id' => $request->lead_type_id,
                // 'contact' => $request->phone,
            ]);

            if ($lead['stage_id'] != $request->stage_id) {
                //lead Activity
                $date = date('Y-m-d H:i:s');
                Utility::add_lead_activity($lead->id, \Auth::user()->id, 'update lead stage', $date, 'update');
            }



            $lead->name        = $request->name;
            $lead->email       = $request->email;
            // $lead->phone       = $request->phone;
            //$lead->subject     = $request->subject;
            //$lead->user_id     = $request->user_id;
            $lead->stage_id    = $request->stage_id;
            $lead->sources     = implode(",", array_filter($request->sources));
            $lead->products    = !empty($request->products) ? implode(",", array_filter($request->products)) : null;
            $lead->notes       = $request->notes;
            $lead->next_contact_date  = $request->next_contact_date;
            $lead->lead_type_id  = $request->lead_type_id;

            if ($lead->stage_id == "4") {
                $lead->won_date = date('Y-m-d');
            }
            $lead->save();



            if ($request->has('products') && is_array($request->products) && count($request->products) > 0) {
                foreach ($request->products as $key => $val) {

                    $product = Products::find($val, ['unit']);

                    LeadProducts::updateOrCreate(
                        [
                            'lead_id' => $lead->id,
                            'product_id' => $val,
                        ],
                        [
                            'qty' => isset($request->qty[$key]) ? $request->qty[$key] : 0,
                            'price' => isset($request->price[$key]) ? $request->price[$key] : 0,
                            'unit_id' => $product->unit,
                            'created_by' => \Auth::user()->creatorId()
                        ]
                    );
                }

                //existing remove product
                $incomingProductIds = $request->products;
                $existingProductIds = LeadProducts::where('lead_id', $lead->id)->pluck('product_id')->toArray();
                $toDelete = array_diff($existingProductIds, $incomingProductIds);

                if (!empty($toDelete)) {
                    LeadProducts::where('lead_id', $lead->id)
                        ->whereIn('product_id', $toDelete)
                        ->delete();
                }
            }

             if($request->bill_id != 0)
            {
                $address = Address::where('id',$request->bill_id)->first();
                $up_adr=[

                    'country'=> $request->billing_country,
                    'state'=> $request->billing_state,
                    'city'=> $request->billing_city,
                    'zipcode'=> $request->billing_zipcode,
                    'address_line_1'=> $request->billing_address_line_1,
                    'address_line_2'=> $request->billing_address_line_2,
                ];
                $address->update($up_adr);
            }
            else
            {
                 $up_adr=[
                    'country'=> $request->billing_country,
                    'state'=> $request->billing_state,
                    'city'=> $request->billing_city,
                    'zipcode'=> $request->billing_zipcode,
                    'address_line_1'=> $request->billing_address_line_1,
                    'address_line_2'=> $request->billing_address_line_2,
                ];
                $biiling_adr = Address::create($up_adr);
                $shipping_adr = Address::create($up_adr);

                $cust->update([
                    'billing_address_id'=>$biiling_adr->id,
                    'shipping_address_id'=>$shipping_adr->id,
                ]);
            }

            $leadAfter = [
                'name' => $lead->name,
                'email' => $lead->email,
                'sources' => $lead->sources,
                'products' => $lead->products,
                'notes' => $lead->notes,
                'next_contact_date' => $lead->next_contact_date,
                'lead_type_id' => $lead->lead_type_id,
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

            if ($previousStageId !== (int) ($lead->stage_id ?? 0)) {
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

            return response()->json([
                'success' => 'success',
                'message' => 'Lead successfully updated!',
            ]);
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function view(Request $request, $id)
    {
        $lead = Lead::where('id', (int) $id)->first();
        if (!$lead) {
            return redirect()->route('leads.index')->with('error', __('Lead not found for this tenant.'));
        }

        $creatorId = \Auth::user()->creatorId();

        if (!$this->userCanAccessLead($lead)) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $stageCnt      = LeadStage::where('created_by', '=', $creatorId)->get();
        $i             = 0;
        foreach ($stageCnt as $stage) {
            $i++;
            if ($stage->id == $lead->stage_id) {
                break;
            }
        }
        $precentage = number_format(($i * 100) / (count($stageCnt) == 0 ? 1 :  count($stageCnt)));
        $leadCallList = LeadCall::where('lead_id', $lead['id'])->orderBy('id', 'desc')->get();
        $leadActivity = LeadActivity::where('lead_id', $lead['id'])->orderBy('id', 'desc')->get();
        $leadChat = LeadChat::where('lead_id', $lead['id'])->orderBy('id', 'desc')->get();
        $lead_attachments = $this->getLeadAttachments($lead, $leadCallList);
        $activityTimeline = ActivityLogger::activityForRecord($lead, null, 12, 'lead_activities_page');

            $quote_list = Quotes::with('customer')
                ->where('lead_id', $lead->id)
                ->where('created_by', $creatorId)
            ->orderBy('id', 'desc')
            ->get();
        //get all sales emp
        $all_sales_emp_list = User::where('type', 'Sales')
            ->where('created_by', $creatorId)
            ->where('id', '!=', \Auth::user()->id)
            ->get();
        return view('leads.view', compact('lead', 'precentage', 'leadCallList', 'leadActivity', 'leadChat', 'all_sales_emp_list', 'quote_list', 'lead_attachments', 'activityTimeline'));
    }

    public function listAttachments(Request $request, $id)
    {
        $lead = $this->resolveLead($id);
        if (!$lead) {
            return response()->json(['message' => __('Lead not found.')], 404);
        }

        if (!$this->userCanAccessLead($lead)) {
            return response()->json(['message' => __('Permission Denied.')], 403);
        }

        $attachments = $this->getLeadAttachments($lead);

        return response()->json([
            'attachments' => $attachments,
            'count' => $attachments->count(),
        ]);
    }

    public function downloadAttachment(Request $request, $id, $callId)
    {
        $lead = $this->resolveLead($id);
        if (!$lead) {
            abort(404, 'Lead not found.');
        }

        if (!$this->userCanAccessLead($lead)) {
            abort(403, 'Permission Denied.');
        }

        $call = LeadCall::where('id', (int) $callId)
            ->where('lead_id', $lead->id)
            ->first();

        if (!$call || empty($call->getRawOriginal('audio'))) {
            abort(404, 'Attachment not found.');
        }

        $fileName = (string) $call->getRawOriginal('audio');
        $candidatePaths = [
            public_path('storage/uploads/lead_call/' . $fileName),
            storage_path('uploads/lead_call/' . $fileName),
        ];

        $filePath = collect($candidatePaths)->first(fn ($path) => File::exists($path));
        if (!$filePath) {
            abort(404, 'Attachment file not found.');
        }

        return response()->download($filePath, $fileName);
    }

    public function uploadAttachment(Request $request, $id)
    {
        $lead = Lead::where('id', (int) $id)->first();
        if (!$lead) {
            return redirect()->route('leads.index')->with('error', __('Lead not found for this tenant.'));
        }

        if (!$this->userCanAccessLead($lead)) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $request->validate([
            'attachment' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt,zip,rar,mp3,wav,ogg,m4a',
        ]);

        $file = $request->file('attachment');
        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeBase = Str::slug($baseName);
        $ext = strtolower($file->getClientOriginalExtension());
        $fileName = ($safeBase ?: 'attachment') . '_' . time() . '.' . $ext;

        $destination = public_path('storage/uploads/lead_call');
        if (!File::isDirectory($destination)) {
            File::makeDirectory($destination, 0775, true, true);
        }
        $file->move($destination, $fileName);

        $callData = [
            'lead_id' => $lead->id,
            'audio' => $fileName,
            'user_id' => \Auth::id(),
            'date_time' => now(),
        ];

        try {
            if (Schema::hasColumn('lead_calls', 'status')) {
                $callData['status'] = 0;
            }
            if (Schema::hasColumn('lead_calls', 'created_by')) {
                $callData['created_by'] = \Auth::id();
            }
            if (Schema::hasColumn('lead_calls', 'call_duration')) {
                $callData['call_duration'] = '00:00:00';
            }
        } catch (\Throwable $e) {
            // Keep backward compatibility for tenants with older lead_calls schema.
        }

        LeadCall::create($callData);

        $attachments = $this->getLeadAttachments($lead);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Attachment uploaded successfully.'),
                'attachments' => $attachments,
                'count' => $attachments->count(),
            ]);
        }

        return redirect()->route('leads.view', $lead->id)->with('success', __('Attachment uploaded successfully.'));
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return number_format($bytes / (1024 ** $power), 2) . ' ' . $units[$power];
    }

    protected function getLeadAttachments(Lead $lead, ?Collection $callList = null)
    {
        $calls = $callList instanceof Collection
            ? $callList
            : LeadCall::where('lead_id', $lead->id)->orderBy('id', 'desc')->get();

        return $calls
            ->filter(function ($call) {
                return !empty($call->getRawOriginal('audio'));
            })
            ->map(function ($call) use ($lead) {
                $fileName = (string) $call->getRawOriginal('audio');
                $publicPath = public_path('storage/uploads/lead_call/' . $fileName);
                $fileSize = File::exists($publicPath) ? File::size($publicPath) : 0;
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                return [
                    'id' => (int) $call->id,
                    'name' => $fileName,
                    'url' => route('leads.attachments.download', [
                        'id' => $lead->id,
                        'callId' => $call->id,
                    ]),
                    'size' => $fileSize,
                    'size_formatted' => $this->formatBytes($fileSize),
                    'ext' => $ext,
                ];
            })
            ->values();
    }

    public function products(Request $request, $lead)
    {
        $lead = $this->resolveLead($lead);
        if (!$lead) {
            return response()->json(['error' => __('Lead not found for this tenant.')], 404);
        }

        if (\Auth::user()->can('edit lead')) {

            if ($lead->created_by == \Auth::user()->creatorId()) {
                $products = Products::InHouse()->where('created_by', '=', \Auth::user()->creatorId())->whereNotIn('id', explode(',', $lead->products))->get()->pluck('name', 'id');

                return view('leads.products', compact('lead', 'products'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }


    public function productAdd(Request $request, $lead)
    {
        $lead = $this->resolveLead($lead);
        if (!$lead) {
            return redirect()->back()->with('error', __('Lead not found for this tenant.'));
        }

        if (\Auth::user()->can('edit lead')) {

            if ($lead->created_by == \Auth::user()->creatorId()) {
                if ($request->has('products') && !is_null($request->products)) {

                    foreach ($request->products as $key => $val) {

                        $leadProduct                =  new LeadProducts;
                        $leadProduct->lead_id       = $lead->id;
                        $leadProduct->product_id    = $val;
                        $leadProduct->price         = isset($request->price[$key]) ? $request->price[$key] : 0;
                        $leadProduct->qty           = isset($request->qty[$key]) ? $request->qty[$key] : 0;
                        $leadProduct->created_by    = \Auth::user()->creatorId();
                        $leadProduct->save();
                    }

                    $lead->products =  implode(',', array_unique(array_merge($request->products, explode(',', $lead->products))));

                    $lead->save();
                }

                return redirect()->back()->with('success', __('Successfully added product.'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function productUpdate(Request $request, $product)
    {
        $product = $this->resolveLeadProduct($product);
        if (!$product) {
            return response()->json(['error' => __('Lead product not found for this tenant.')], 404);
        }

        if (\Auth::user()->can('edit lead')) {

            if ($product->created_by == \Auth::user()->creatorId()) {
                $product->price = $request->price;
                $product->qty = $request->qty;
                $product->save();

                return response()->json(['success' => __('Successfully updated product.')], 200);
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function productDelete($product)
    {
        $product = $this->resolveLeadProduct($product);
        if (!$product) {
            return response()->json(['error' => __('Lead product not found for this tenant.')], 404);
        }

        if (\Auth::user()->can('edit lead')) {

            try {

                $lead = Lead::find($product->lead_id);
                if ($lead && !is_null($lead->product)) {

                    $arr = array_filter(explode(',', $lead->products), function ($value) use ($product) {
                        return $value !== $product->product_id;
                    });

                    $lead->products = implode(',', array_values($arr));

                    $lead->save();
                }

                $product->delete();

                return response()->json([
                    'success' => 'Lead product has been successfully deleted.'
                ], 200);

                return redirect()->route('roles.index')->with('success', __('Role successfully deleted.'));
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function getMoreProduct()
    {

        $row = 1;
        return view('leads.add-more-product', compact('row'));
    }

    public function users(Request $request, $lead)
    {
        $lead = $this->resolveLead($lead);
        if (!$lead) {
            return response()->json(['error' => __('Lead not found for this tenant.')], 404);
        }

        if (\Auth::user()->can('edit lead')) {

            if ($lead->created_by == \Auth::user()->creatorId()) {
                // $users = User::Employees()->isdeleted()->get()->pluck('name', 'id');

                $sales_user = User::where('type', 'Sales')->pluck('id');
                if (count($sales_user) > 0) {
                    $users = User::whereIn('id', $sales_user)->get()->pluck('name', 'id');
                }
                // dd($lead->users()->pluck('users.id'));
                // $selectedUsers = $lead->users->pluck('id');

                return view('leads.users', compact('lead', 'users'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function usersUpdate(Request $request, $lead)
    {
        $lead = $this->resolveLead($lead);
        if (!$lead) {
            return redirect()->back()->with('error', __('Lead not found for this tenant.'));
        }

        if (\Auth::user()->can('edit lead')) {

            if ($lead->created_by == \Auth::user()->creatorId()) {

                if ($request->has('users') && is_array($request->users)) {
                    $existingUserIds = UserLead::where('lead_id', $lead->id)
                        ->pluck('user_id')
                        ->map(fn ($userId) => (int) $userId)
                        ->all();

                    UserLead::where('lead_id', $lead->id)->where('user_id', '!=', $lead->user_id)->delete();

                    foreach ($request->users as $val) {

                        $user = new UserLead;
                        $user->lead_id = $lead->id;
                        $user->user_id = $val;
                        $user->save();
                    }

                    $updatedUserIds = UserLead::where('lead_id', $lead->id)
                        ->pluck('user_id')
                        ->map(fn ($userId) => (int) $userId)
                        ->all();

                    sort($existingUserIds);
                    sort($updatedUserIds);

                    if ($existingUserIds !== $updatedUserIds) {
                        $this->writeLeadActivity(
                            'assign',
                            'lead.assigned',
                            $lead,
                            'Lead assignment updated.',
                            [
                                'before' => [
                                    'user_ids' => $existingUserIds,
                                    'users' => $this->resolveUserNames($existingUserIds),
                                ],
                                'after' => [
                                    'user_ids' => $updatedUserIds,
                                    'users' => $this->resolveUserNames($updatedUserIds),
                                ],
                            ]
                        );
                    }
                }

                return redirect()->back()->with('success', __('Successfully added user.'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function usersDelete($lead, $user)
    {

        if (\Auth::user()->can('edit lead')) {

            try {

                $user = UserLead::where('user_id', $user)->where('lead_id', $lead)->first();
                $user->delete();

                return response()->json([
                    'success' => 'Lead user has been successfully deleted.'
                ], 200);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }


    public function sources(Request $request, $lead)
    {
        $lead = $this->resolveLead($lead);
        if (!$lead) {
            return response()->json(['error' => __('Lead not found for this tenant.')], 404);
        }

        if (\Auth::user()->can('edit lead')) {

            if ($lead->created_by == \Auth::user()->creatorId()) {
                $source = LeadSource::get()->pluck('name', 'id');

                return view('leads.source', compact('lead', 'source'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function sourcesUpdate(Request $request, $lead)
    {
        $lead = $this->resolveLead($lead);
        if (!$lead) {
            return redirect()->back()->with('error', __('Lead not found for this tenant.'));
        }

        if (\Auth::user()->can('edit lead')) {

            if ($lead->created_by == \Auth::user()->creatorId()) {

                if ($request->has('sources') && is_array($request->sources)) {


                    $lead->sources =  implode(',', $request->sources);

                    $lead->save();
                }

                return redirect()->back()->with('success', __('Successfully updated source.'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function sourcesDelete($lead, $source)
    {
        $lead = $this->resolveLead($lead);
        if (!$lead) {
            return response()->json(['error' => __('Lead not found for this tenant.')], 404);
        }

        if (\Auth::user()->can('edit lead')) {

            try {

                if ($lead && !is_null($lead->sources)) {

                    $arr = array_filter(explode(',', $lead->sources), function ($value) use ($source) {
                        return $value !== $source;
                    });

                    $lead->sources = implode(',', array_values($arr));

                    $lead->save();
                }

                return response()->json([
                    'success' => 'Lead source has been successfully deleted.'
                ], 200);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function getProducts($lead)
    {
        $lead = $this->resolveLead($lead);
        if (!$lead) {
            abort(404, 'Lead not found for this tenant.');
        }

        return view('products.product-list-table', compact('lead'));
    }

    //new
    public function get_customer_lead_product($customer)
    {
        $customer = $this->resolveCustomer($customer);
        if (!$customer) {
            abort(404, 'Customer not found for this tenant.');
        }

        $lead = Lead::where('customer_id', $customer->id)->orderBy('id', 'desc')->first();
        return view('products.customer-product-list-table', compact('lead'));
    }

    public function edit_getProducts($lead, $quote_id)
    {
        $lead = $this->resolveLead($lead);
        if (!$lead) {
            abort(404, 'Lead not found for this tenant.');
        }

        $qt_id = Quotes::find($quote_id);
        return view('products.edit-product-list-table', compact('lead', 'qt_id'));
    }

    public function edit_customer_getProducts($customer, $quote_id)
    {
        $customer = $this->resolveCustomer($customer);
        if (!$customer) {
            abort(404, 'Customer not found for this tenant.');
        }

        $qt_id = Quotes::find($quote_id);
        $lead = Lead::where('customer_id', $customer->id)->orderBy('id', 'desc')->first();
        return view('products.edit-customer-product-list-table', compact('lead', 'qt_id'));
    }


    public function stageUpdate(Request $request, $lead, $stage)
    {
        $lead = $this->resolveLead($lead);
        $stage = $this->resolveLeadStage($stage);

        if (!$lead || !$stage) {
            return response()->json(['error' => __('Lead or stage not found for this tenant.')], 404);
        }

        if (\Auth::user()->can('edit lead')) {

            // if ($lead->created_by == \Auth::user()->creatorId()) {

            $previousStageId = (int) ($lead->stage_id ?? 0);
            $lead->stage_id = $stage->id;
            if ($lead->stage_id == "4") {
                $lead->won_date = date('Y-m-d');
            }
            $lead->save();

            //Lead Activity
            $date = date('Y-m-d H:i:s');
            Utility::add_lead_activity($lead->id, \Auth::user()->id, 'update lead stage', $date, 'update');

            if ($previousStageId !== (int) $lead->stage_id) {
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

            return response()->json(['success' => 'true', 'message' => 'Lead successfully updated!'], 200);

            /* } else {
                return response()->json(['error' =>  __('Permission Denied.')], 401);
            } */
        } else {
            return response()->json(['error' =>  __('Permission Denied.')], 401);
        }
    }

    public function update_description(Request $request, $id)
    {
        $leadId = Lead::find($id);
        $leadId->update(['notes' => $request->notes]);
        return redirect()->route('leads.index')->with(['success' => 'Lead Description has been updated successfully.']);
    }

    public function lead_chat(Request $request, $lead)
    {
        $lead = $this->resolveLead($lead);
        if (!$lead) {
            abort(404, 'Lead not found for this tenant.');
        }

        $lead_stage_list = LeadStage::pluck('name', 'id');
        return view('leads.chat', compact('lead', 'lead_stage_list'));
    }

    public function lead_chat_save(Request $request, $lead)
    {
        $lead = $this->resolveLead($lead);
        if (!$lead) {
            return redirect()->back()->with('error', __('Lead not found for this tenant.'));
        }

        $input = $request->all();
        $input['lead_id'] = $lead['id'];
        $input['stage_id'] = $request->stage_id;
        $input['next_date'] = $request->next_date;
        $input['created_by'] = \Auth::user()->id;
        $followUp = LeadChat::create($input);

        $lead_rcd = Lead::where('id', $lead->id)->first();
        if (!empty($input['next_date'])) {

            if ($lead_rcd) {
                $lead_rcd->update(['next_contact_date' => $input['next_date']]);
            }
        }
        if($request->stage_id == 4)
        {
            $ld['won_date'] = date('Y-m-d');
        }
        $ld['stage_id'] = $request->stage_id;
        $lead_rcd->update($ld);

        $this->writeLeadActivity(
            'update',
            'lead.followup_added',
            $lead_rcd,
            'Lead follow-up added.',
            [
                'followup_id' => $followUp->id,
                'message' => $followUp->chat,
                'next_date' => $followUp->next_date,
                'stage_id' => $followUp->stage_id,
                'stage_name' => $this->resolveLeadStageName((int) $followUp->stage_id),
            ]
        );

        return redirect()->back()->with(['success' => 'Chat has been added successfully.']);
    }

    public function lead_duplicate(Request $request, $lead)
    {
        $lead = $this->resolveLead($lead);
        if (!$lead) {
            return redirect()->back()->with('error', __('Lead not found for this tenant.'));
        }

        \DB::transaction(function () use ($lead) {
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
            Utility::add_lead_activity($newLead->id, \Auth::id(), 'add duplicate lead detail', $date, 'duplicate');
        });

        return redirect()->back()->with(['success' => 'Duplicate Lead has been added successfully.']);
    }

    //gst no check in quote create
    public function getGst($id) //ID=CUSTOMER-ID
    {
        // $lead = Lead::find($id);
        // $customer = Entity::isCustomer()->where('id', $lead->customer_id)->first();

        // if ($lead) {
        //     return response()->json([
        //         'gst_no' => $customer->gst_no ?? '',
        //     ]);
        // }

        // return response()->json([
        //     'gst_no' => '',
        // ], 404);

        // $lead = Lead::find($id);
        $customer = Entity::isCustomer()->where('id', $id)->first();

        if ($customer) {

            return response()->json([
                'gst_no' => $customer->gst_no ?? '',
                'adhar_nub' => $customer->company_adhar_no ?? '',
                'udhyam_nub' => $customer->company_udhyam_no ?? '',
                'company_name' => $customer->company_name ?? ''
            ]);
        }

        // if ($lead) {
        //     return response()->json([
        //         'gst_no' => $customer->gst_no ?? '',
        //     ]);
        // }

        return response()->json([
            'gst_no' => '',
            'adhar_nub' => '',
            'udhyam_nub' =>  '',
            'company_name' => '',
        ], 404);
    }

    public function upload_data(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            $import = new LeadImport();
            Excel::import($import, $request->file('excel_file'));
            $data = Excel::toCollection($import, $request->file('excel_file'));


            $sheetData = $data[0];

            return response()->json([
                'message' => 'Data extracted successfully',
                'data' => $sheetData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }

        return response()->json(['message' => 'File uploaded successfully!']);
    }

    public function checkEntityPhone(Request $request)
    {
        $phone =  $request->input('phone');
        $comp_list = "";
        $get_customer = CustomerPhone::where('phone', $phone)->first();
        if (!empty($get_customer) && !empty($get_customer->customer_id)) {

            if(\Auth::user()->type == 'Sales')
            {
                $entity = Entity::where('type', 'customer')->where('id', $get_customer->customer_id)->first();
                if($entity)
                {
                    $check_cust_whose = Entity::where('id', $entity->id)->where('user_id',\Auth::user()->id)->first();
                    if(!$check_cust_whose)
                    {
                        $create_customer_user = User::where('id',$entity->user_id)->first();
                        if($create_customer_user)
                        {
                            return response()->json([
                                    'status' => 'error',
                                    'message'=> 'You can not add lead because this customer already assign to '.$create_customer_user->name,
                            ]);
                        }
                    }

                }
            }
            else
            {
                $entity = Entity::where('type', 'customer')->where('id', $get_customer->customer_id)->first();
            }

            if ($entity) {

                $comp_records  = Address::where('id', $entity->billing_address_id)->first();
                if ($comp_records) {
                    $comp_list = $comp_records;
                }
                return response()->json([
                    'status' => 'found',
                    'name' => $entity->name,
                    'email' => $entity->email,
                    'company_all' => [],
                    'customer_phone' => $get_customer,
                    'customer_id' => $entity->id,
                    'lead_type_id' => $entity->lead_type_id,
                    'billing_address_data' => $comp_list,
                ]);
            }
        }

        return response()->json(['status' => 'not_found']);
    }


    public function upload_facebook_leads(Request $request)
    {
        Log::info('-----------------Facebook lead upload start ---------------');
        Log::info('FacebookFetchLeads command run ' . now());

        $response = Http::get('https://graph.facebook.com/v18.0/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => '755048917028843', //$setting->app_id,
            'client_secret' => 'e7f820254abfb307216fd90f6727593a',  //$setting->app_secret,
            'fb_exchange_token' => 'EAAKutoZA8zZBsBPZB0QnvyLAwT4pkDBX1GPgDreKSlubP3qmdfiXndKXAaiWA5lNsL3cy9Xe4qfU4xYX6gU3uaL9x7cy0GhaZAcqeZCNlPxEPMr7Ku8bVDxQT3naGhz0EISfu9y3sVaYSzhaCY1ea4Q6AlMrOD6ZAS3ZALjiKDm59DZCiRZAK0nykq3TAvKhOUZAAG',
        ])->json();

        //if logout then used this access token add into db(access_token) & fb_exchange_token also add into
        // dd($response);


        try {
            $pageId = ThirdParty::where('name', 'page_id')->value('value');
            $userToken = ThirdParty::where('name', 'user_token')->value('value'); //user_id
            $accessToken = ThirdParty::where('name', 'access_token')->value('value'); //api_key

            // Check if user token is valid
            $check = Http::withToken($userToken)->get('https://graph.facebook.com/v18.0/me/permissions')->json();

            if (isset($check['error'])) {
                $response = Http::get('https://graph.facebook.com/v18.0/oauth/access_token', [
                    'grant_type' => 'fb_exchange_token',
                    'client_id' => '755048917028843', //$setting->app_id,
                    'client_secret' => 'e7f820254abfb307216fd90f6727593a',  //$setting->app_secret,
                    'fb_exchange_token' => $userToken,
                ])->json();

                if (isset($response['access_token'])) {
                    $userToken = $response['access_token'];
                    // $entry->user_id = $userToken;
                    // $entry->save();

                    ThirdParty::where('name', 'user_token')
                        ->update(['value' => $userToken ?? null]);

                    Log::info("Updated user token");
                } else {
                    Log::error("Unable to refresh user token ", $response);
                    return redirect()->back()->with(['error' => "Please generate new user token."]);
                }
            }

            // Try getting forms using page token
            $forms = Http::withToken($accessToken)
                ->get("https://graph.facebook.com/v18.0/{$pageId}/leadgen_forms")
                ->json();

            if (isset($forms['error']) && $forms['error']['code'] == 190) {
                // Refresh page token using user token
                Log::warning("Page token expired for Page ID: {$pageId}. Refreshing page token...");

                $pages = Http::withToken($userToken)
                    ->get("https://graph.facebook.com/v18.0/me/accounts")
                    ->json();

                $newPageToken = collect($pages['data'] ?? [])
                    ->firstWhere('id', $pageId)['access_token'] ?? null;

                if ($newPageToken) {
                    $accessToken = $newPageToken;
                    // $entry->api_key = $accessToken;
                    // $entry->save();

                    ThirdParty::where('name', 'access_token')
                        ->update(['value' => $accessToken ?? null]);

                    Log::info("access token updated");

                    $forms = Http::withToken($accessToken)
                        ->get("https://graph.facebook.com/v18.0/{$pageId}/leadgen_forms")
                        ->json();
                } else {
                    Log::error("Unable to refresh page access token for Page ID: {$pageId}");
                }
            }

            Log::info("Forms fetched for Page ID: {$pageId}", $forms);

            $leadCount = 0;
            foreach ($forms['data'] ?? [] as $form) {

                $leads = Http::get("https://graph.facebook.com/v18.0/{$form['id']}/leads?access_token={$accessToken}")->json();
                Log::info("fetched lead: {$pageId}", $leads);


                foreach ($leads['data'] ?? [] as $lead) {
                    $leadId = $lead['id'] ?? null;
                    if (!$leadId) continue;

                    $fieldData = collect($lead['field_data'] ?? []);
                    Log::info("Facebook Lead field_data for Lead ID: {$leadId}", [
                        'form_id' => $form['id'],
                        'lead_id' => $leadId,
                        'field_data' => $fieldData,
                    ]);

                    $full_name      = optional($fieldData->firstWhere('name', 'full_name'))['values'][0] ?? null;
                    $email          = optional($fieldData->firstWhere('name', 'email'))['values'][0] ?? null;
                    $phone_numb     = optional($fieldData->firstWhere('name', 'phone_number'))['values'][0] ?? null;

                    //remove country code
                    if ($phone_numb) {
                        $phone_numb = preg_replace('/\D/', '', $phone_numb);
                        if (strlen($phone_numb) > 10) {
                            $phone_numb = substr($phone_numb, -10);
                        }
                    }

                    $l_stage_id = LeadStage::where('name', 'new')->first();
                    $l_source_id = LeadSource::where('name', 'facebook')->first();

                    $check_phone_avl = CustomerPhone::where('phone', $phone_numb)->first();
                    if ($check_phone_avl) {
                        $check_customer = Entity::where('id', $check_phone_avl->customer_id)->where('type', 'customer')->first();
                    }
                    // else
                    // {
                    //     $check_customer = Entity::where('contact', $phone_numb)->where('type', 'customer')->first();
                    // }

                    if (!$check_phone_avl) {
                        $cust_data['name'] = $full_name;
                        $cust_data['email'] = $email;
                        $cust_data['contact'] = $phone_numb;
                        $cust_data['type'] = 'customer';
                        $cust_data['created_by'] = \Auth::user()->creatorId();
                        $cust_data['company_name'] = $full_name ?? null;
                        $check_customer = Entity::create($cust_data);

                        $cust_phone['customer_id'] = $check_customer->id;
                        $cust_phone['phone'] = $phone_numb;
                        $cust_phone['is_primary'] = 1;
                        CustomerPhone::create($cust_phone);
                    }

                    $check_exist_leadid = Lead::where('lead_id', $leadId)->first();
                    if ($check_exist_leadid) {
                        continue;
                    }

                    $lead_data['name'] = $check_customer->name ?? $full_name;
                    $lead_data['email'] = $check_customer->email ?? $email;
                    $lead_data['phone'] = $check_customer->contact ?? $phone_numb;
                    $lead_data['user_id'] = null; //\Auth::user()->id;
                    $lead_data['stage_id'] = null; //$l_stage_id->id;
                    $lead_data['sources'] = $l_source_id->id;
                    $lead_data['created_by'] = \Auth::user()->creatorId();
                    $lead_data['date'] = date('Y-m-d');
                    $lead_data['customer_id'] = $check_customer->id;
                    $lead_data['lead_id'] = $leadId ?? null;

                    $new_lead_id = Lead::create($lead_data);

                    UserLead::create(
                        [
                            'user_id' => \Auth::user()->id,
                            'lead_id' => $new_lead_id->id,
                        ]
                    );

                    //Lead Activity
                    $date = date('Y-m-d H:i:s');
                    Utility::add_lead_activity($new_lead_id->id, \Auth::user()->id, 'add lead detail', $date, 'add');
                }
            }
            Log::info('-----------------Facebook lead upload End ---------------');

            return redirect()->back()->with(['success' => "Facebook leads  has been uploaded successfully"]);
        } catch (\Exception $e) {
            Log::error('Facebook Fetch Leads command failed: ' . $e->getMessage());
        }
    }

    public function get_header(Request $request)
    {
        Log::info('---------------------- START UPLOAD --------------');
        Log::info('------------get Headers step-1---------------');

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            // Convert excel to collection
            $allSheets = Excel::toCollection(null, $request->file('excel_file'));

            if ($allSheets->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file is empty.'
                ]);
            }

            $firstSheet = $allSheets->first();

            if ($firstSheet->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'The first sheet is empty.'
                ]);
            }

            $headers = array_values($firstSheet->shift()->toArray());

            Log::info('headers list: ', [$headers]);

            return response()->json([
                'status' => true,
                'headers_list' => $headers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function excel_preview(Request $request)
    {

        Log::info('-------- excel_preview step-2------');

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {

            $preDefined_Col = ['name', 'email', 'phone', 'lead_source', 'description', 'comp_name', 'gst_numb', 'adhar_numb', 'udhyam_numb', 'state', 'city', 'address', 'zipcode'];
            $mapping = $request->input('mapping', []);
            if (!is_array($mapping)) {
                $mapping = [];
            }
            $mapping = collect($mapping)
                ->mapWithKeys(function ($value, $key) {
                    return [(int) $key => $value];
                })
                ->toArray();

            $import = new LeadImport($mapping);
            Excel::import($import, $request->file('excel_file'));
            $allSheets = Excel::toCollection(null, $request->file('excel_file'));

            $data = $allSheets->map(function ($sheet) {
                return $sheet->toArray();
            });


            Log::info('Predefined columns:', $preDefined_Col);
            Log::info('Mapping received:', $mapping);
            $allData = $allSheets->first()->toArray();
            $preDefined_Col = ['name', 'email', 'phone', 'lead_source', 'description', 'comp_name', 'gst_numb', 'adhar_numb', 'udhyam_numb', 'state', 'city', 'address', 'zipcode'];

            foreach ($preDefined_Col as $i => $colName) {
                if (!isset($mapping[$i])) {
                    Log::warning("No mapping for {$colName}");
                    continue;
                }

                $excelIndexName  = isset($mapping[0]) && $mapping[0] !== '' ? (int) $mapping[0] : null;
                $excelIndexEmail = isset($mapping[1]) && $mapping[1] !== '' ? (int) $mapping[1] : null;
                $excelIndexPhone = isset($mapping[2]) && $mapping[2] !== '' ? (int) $mapping[2] : null;
                $excelIndexGst = isset($mapping[6]) && $mapping[6] !== '' ? (int) $mapping[6] : null;

                if ($excelIndexName === null || $excelIndexPhone === null) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Mapping error',
                        'data' => [],
                        'messages' => 'Name and Phone mappings are required.',
                    ]);
                }

                foreach (array_slice($allData, 1) as $rowIndex => $dataRow) {

                    // --- Name Validation ---
                    $cellValueName = $dataRow[$excelIndexName] ?? null;
                    if ($cellValueName) {

                        if (preg_match('/^\d+$/', $cellValueName) || str_contains($cellValueName, '@')) {
                            Log::info('excel name ', [$cellValueName]);
                            return response()->json([
                                'status' => false,
                                'message' => 'Name error',
                                'data' => [],
                                'messages' => "Invalid name in row " . ($rowIndex + 2) . ": '{$cellValueName}'",
                            ]);
                        }
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'Name is empty',
                            'data' => [],
                            'messages' => "Name is empty in row " . ($rowIndex + 2),
                        ]);
                    }

                    // --- Email Validation ---
                    // $cellValueEmail = $dataRow[$excelIndexEmail] ?? null;
                    // if ($cellValueEmail) {

                    //     if (!filter_var($cellValueEmail, FILTER_VALIDATE_EMAIL)) {
                    //         Log::info('excel email ',[$cellValueEmail]);
                    //         return response()->json([
                    //             'status' => false,
                    //             'message' => 'Email error',
                    //             'data' => [],
                    //             'messages' => "Invalid email in row " . ($rowIndex + 2) . ": '{$cellValueEmail}'",
                    //         ]);
                    //     }
                    // } else {
                    //     return response()->json([
                    //         'status' => false,
                    //         'message' => 'Email is empty',
                    //         'data' => [],
                    //         'messages' => "Email is empty in row " . ($rowIndex + 2),
                    //     ]);
                    // }


                    if (isset($mapping[1]) && $mapping[1] !== null && $mapping[1] !== '') {
                        $excelIndexEmail = (int) $mapping[1];
                        $cellValueEmail = $dataRow[$excelIndexEmail] ?? null;

                        if ($cellValueEmail) {
                            if (!filter_var($cellValueEmail, FILTER_VALIDATE_EMAIL)) {
                                Log::info('excel email ', [$cellValueEmail]);
                                return response()->json([
                                    'status' => false,
                                    'message' => 'Email error',
                                    'data' => [],
                                    'messages' => "Invalid email in row " . ($rowIndex + 2) . ": '{$cellValueEmail}'",
                                ]);
                            }
                        }
                    }

                    // --- Phone Validation ---
                    $cellValuePhone = $dataRow[$excelIndexPhone] ?? null;
                    if ($cellValuePhone) {

                        $cleanPhone = preg_replace('/\D/', '', $cellValuePhone);
                        if (!preg_match('/^\d{10}$/', $cleanPhone)) {
                            Log::info('excel phone ', [$cellValuePhone]);
                            return response()->json([
                                'status' => false,
                                'message' => 'Phone error',
                                'data' => [],
                                'messages' => "Invalid phone in row " . ($rowIndex + 2) . ": '{$cellValuePhone}' (must be 10 digits only)",
                            ]);
                        }
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'Phone is empty',
                            'data' => [],
                            'messages' => "Phone is empty in row " . ($rowIndex + 2),
                        ]);
                    }

                    //gst validation
                    if (isset($mapping[6]) && $mapping[6] !== null && $mapping[6] !== '')
                    {
                        $gstRegex = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/';

                        $excelIndexGst = (int) $mapping[6];
                        $cellValueGst  = $dataRow[$excelIndexGst] ?? null;

                        // Run only if GST value exists
                        if (!empty($cellValueGst)) {

                            $gst = strtoupper(trim($cellValueGst));

                            // 1️⃣ Format validation
                            if (!preg_match($gstRegex, $gst)) {
                                return response()->json([
                                    'status' => false,
                                    'message' => 'GST format error',
                                    'data' => [],
                                    'messages' => "Invalid GST number format in row " . ($rowIndex + 2) . ": '{$gst}'",
                                ]);
                            }

                            $customerPhone = CustomerPhone::where('phone', $cleanPhone)
                                    ->where('is_primary', 1)
                                    ->first();

                                $gstCompany = Entity::where('gst_no', $gst)->first();

                                if ($gstCompany && $gstCompany->id !== $customerPhone?->customer_id) {

                                    return response()->json([
                                        'status' => false,
                                        'message' => 'GST already assigned',
                                        'data' => [],
                                        'messages' =>
                                            "GST '{$gst}' is already assigned to another customer. Row: " . ($rowIndex + 2),
                                    ]);
                                }
                        }
                    }
                }
            }

            // Log::info('all data ',[$allData]);

            $filteredData = [];

            $validMappings = [];
            foreach ($mapping as $i => $mapIndex) {
                if ($mapIndex !== null && $mapIndex !== '') {
                    $validMappings[$i] = (int) $mapIndex;
                }
            }

            foreach ($allData as $rowIndex => $row) {
                $filteredRow = [];
                foreach ($validMappings as $i => $colIndex) {
                    $filteredRow[$preDefined_Col[$i]] = $row[$colIndex] ?? null;
                }
                $filteredData[] = $filteredRow;
            }

            Log::info('Filtered Excel Data:', $filteredData);

            return response()->json([
                'status' => true,
                'message' => 'Filtered data extracted successfully',
                'data' => $filteredData,
                'messages' => $import->rowErrors,
            ]);
        } catch (ValidationException $ve) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $ve->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function excelPreviewView(Request $request)
    {
        Log::info('------ excelPreviewView -----------');
        $sheets = json_decode($request->get('data'), true);
        $messages = json_decode($request->get('messages'), true) ?? [];

        Log::info('sheets: ', ['sheets', $sheets]);

        Log::info('messages: ', ['messages', $messages]);

        return view('leads.excel_preview_view', compact('sheets', 'messages'));
    }

    public function uploadLeads(Request $request)
    {
        try {
            Log::info('------ upload leads -----------');
            $leads = json_decode($request->leads_data, true);
            if (!is_array($leads) || empty($leads)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No lead data received for upload.',
                    'data' => []
                ], 422);
            }
            $jobKey = 'lead_upload_' . uniqid();

            Log::info('leads : ', ['leads' => $leads]);
            Log::info('job key : ', ['job' => $jobKey]);


            Cache::put($jobKey, 'pending', now()->addMinutes(60));

            $user = auth()->user()->id;
            $tenantId = app()->bound('currentTenant') ? (int) data_get(app('currentTenant'), 'id') : null;
            Log::info('user: ', ['user' => $user]);

            ProcessLeadUpload::dispatchSync($user, $jobKey, $leads, $tenantId);

            return response()->json(['status' => true, 'job_key' => $jobKey]);
        } catch (\Exception $e) {
            \Log::error('Upload failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function upload_excel_lead()
    {
        return view('leads.up_excel_lead');
    }

    public function fetchLeads(Request $request)
    {
        // When clicked, it will fetch random leads (default 10 leads, configurable by admin).

        // The number of leads that can be fetched per request will be set dynamically by admin.


        if (\Auth::user()->type != 'Sales') {

            return redirect()->back()->with('error', 'Permission denied. only Sales person used.');
        }

        $lead_count = $request->total_lead;
        $lead_source_id = $request->source_id;

        $user_id = \Auth::user()->id;

        $emp = Employee::select('lead_fetch_limit')->where('user_id', $user_id)->first();
        $fetchedLeads = lead::where('user_id', $user_id)->count();

        // dd($emp->lead_fetch_limit, $fetchedLeads);

        // if ($emp->lead_fetch_limit <= $fetchedLeads) {

        //     return redirect()->back()->with('error', 'Your leads fetch quota has been reached.');
        // }


        $leads = Lead::whereNull('user_id')
            ->whereRaw("FIND_IN_SET(?, sources)", [$lead_source_id])->orderBy('id','desc')
            ->limit($lead_count)
            ->get();



        $fetchedLeadsCnt = 0;

        if ($leads) {

            foreach ($leads as $lead) {

                $fetchedLeadsCnt++;

                $leadData = lead::find($lead->id);

                $leadData->user_id = $user_id;
                $leadData->save();

                $get_cust = Entity::where('type','customer')->where('id',$leadData->customer_id)->first();
                if($get_cust)
                {
                    $get_cust->update(['user_id'=>$user_id]);
                }

                $date = date('Y-m-d H:i:s');
                Utility::add_lead_activity($lead->id, \Auth::user()->id, 'Lead assigned', $date, 'update');
                $this->writeLeadActivity(
                    'assign',
                    'lead.assigned',
                    $leadData,
                    'Lead assigned.',
                    [
                        'before' => [
                            'user_id' => null,
                            'user_name' => null,
                        ],
                        'after' => [
                            'user_id' => $user_id,
                            'user_name' => User::where('id', $user_id)->value('name'),
                        ],
                    ]
                );
            }
        }

        return redirect()->back()->with(['success' => ' ( ' . $fetchedLeadsCnt . ' ) Leads has been fetched successfully']);
    }

    public function get_lead_fetch()
    {
        return view('leads.get_lead_fetch_model');
    }

    public function assignTo(Request $request, $lead, $user_id, $slug)
    {
        $lead = $this->resolveLead($lead);
        if (!$lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found for this tenant.',
            ], 404);
        }

        try {

            $slug = (bool) $slug;

            if ($slug) {
                $previousUserId = $lead->user_id ? (int) $lead->user_id : null;

                if ($lead->user_id != null) {

                    return response()->json([
                        'success' => false,
                        'message' => 'Lead already pickupded by other!'
                    ]);
                }

                // UserLead::where('lead_id', $lead->id)->where('user_id', '!=', $user_id)->delete();

                // $user = new UserLead;
                // $user->lead_id = $lead->id;
                // $user->user_id = $user_id;
                // $user->save();

                $lead->user_id = $user_id;
                $lead->save();

                $this->writeLeadActivity(
                    'assign',
                    'lead.assigned',
                    $lead,
                    $previousUserId ? 'Lead reassigned.' : 'Lead assigned.',
                    [
                        'before' => [
                            'user_id' => $previousUserId,
                            'user_name' => $previousUserId ? User::where('id', $previousUserId)->value('name') : null,
                        ],
                        'after' => [
                            'user_id' => (int) $user_id,
                            'user_name' => User::where('id', $user_id)->value('name'),
                        ],
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Lead has been assigned to you successfully!'
                ]);
            } else {

                UserLead::where('lead_id', $lead->id)->where('user_id', $user_id)->delete();
                $lead->user_id = null;
                $lead->save();

                $get_cust = Entity::GetCustomer()->where('id',$lead->customer_id)->first();
                if($get_cust)
                {
                    $get_cust->update(['user_id'=>null]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Lead has been successfully unassigned to you!'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    // Fetch Recent Lead By Company.
    public function fetchRecent($company_id){

        $recentLeadId = Lead::where('customer_id', $company_id)
                    ->latest('id')
                    ->value('id') ?? '';


        return response()->json([
            'success' => (bool) $recentLeadId,
            'id' => $recentLeadId,
            'message' => $recentLeadId ? 'Recent lead found.' : 'No lead found.',
        ], 200);
    }

}
