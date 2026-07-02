<?php

namespace App\Http\Controllers;

use App\Models\CustomerPhone;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\UserLead;
use App\Models\Lead;
use App\Models\Utility;
use App\Models\LeadSource;
use App\Models\Products;
use App\Models\LeadStage;
use App\Models\LeadChat;
use App\Models\User;
use App\Models\Entity;
use App\Services\ActivityLogger;

class LeadFollowUpController extends Controller
{

    public function create(Request $request, $slug)
    {
        $data['dynamic_slug'] = $slug;
        $leadQuery = Lead::query()
            ->with(['customer:id,name,company_name'])
            ->select('id', 'name', 'phone', 'customer_id', 'user_id')
            ->orderByDesc('id');

        if (\Auth::user()->type === 'Sales') {
            $login_sales_leads = UserLead::where('user_id', \Auth::user()->id)->pluck('lead_id')->toArray();
            $directLeadIds = Lead::where('user_id', \Auth::user()->id)->pluck('id')->toArray();
            $assignedLeadIds = array_values(array_unique(array_merge($login_sales_leads, $directLeadIds)));

            if (empty($assignedLeadIds)) {
                $data['lead_list'] = [];
                $data['lead_status_list'] = LeadStage::pluck('name', 'id');
                return view('follow_up.create', $data);
            }

            $leadQuery->whereIn('id', $assignedLeadIds);
        }

        $data['lead_list'] = $leadQuery->get()->mapWithKeys(function ($lead) {
            $name = trim((string) ($lead->name ?? ''));
            $customerName = trim((string) optional($lead->customer)->name);
            $companyName = trim((string) optional($lead->customer)->company_name);
            $phone = trim((string) ($lead->phone ?? ''));

            $parts = array_values(array_filter([$name, $companyName, $customerName], static function ($value) {
                return trim((string) $value) !== '';
            }));

            $label = implode(' - ', array_unique($parts));

            if ($label === '' && $phone !== '') {
                $label = $phone;
            }

            if ($label === '') {
                $label = 'Lead #' . $lead->id;
            }

            return [$lead->id => $label];
        })->toArray();

        $data['lead_status_list'] = LeadStage::pluck('name', 'id');
        return view('follow_up.create', $data);
    }

    public function store(Request $request, $slug)
    {

        $request->validate([
            'lead_id' => [
                'required'
            ],
            'next_date' => [
                'required',
            ],
            'stage_id' => 'required',
            'chat' => 'required',
        ]);

        $input = $request->all();

        $input['created_by'] = \Auth::user()->id;

        $followUp = LeadChat::create($input);
        $lead = Lead::where('id', $request->lead_id)->first();
        if ($lead) {
            ActivityLogger::writeFor('leads', 'update', $lead, null, [
                'event_key' => 'lead.followup_added',
                'description' => 'Lead follow-up added.',
                        'properties' => [
                            'followup_id' => $followUp->id,
                            'message' => $followUp->chat,
                            'next_date' => $followUp->next_date,
                            'stage_id' => $followUp->stage_id,
                            'stage_name' => LeadStage::withTrashed()->where('id', $followUp->stage_id)->value('name'),
                        ],
                    ]);
        }

        return redirect()->route('follow-ups.list', $slug)->with(['success' => "Follow-Up has been added successfully"]);
    }

    public function list(Request $request,$slug='upcomming')
    {
        $list_data['dynamic_slug'] = $slug;
        if (\Auth::user()->can('manage lead')) {

            if ($request->ajax()) {

                try {
                    $today = now()->toDateString();
                    $status_not_intreseted = LeadStage::where('name', 'Not Interested')->first();

                    if (\Auth::user()->type == 'Sales') {

                        $login_sales_leads = Lead::where('user_id', \Auth::user()->id)->pluck('id');


                        if ($slug == 'upcomming') {
                            $query = LeadChat::whereDate('next_date', '>=', $today)
                                ->whereIn('lead_id', $login_sales_leads)
                                ->select('lead_id', 'id', 'chat', 'next_date', 'created_by', 'stage_id');
                        } elseif ($slug == 'expired') {
                            $query = LeadChat::whereDate('next_date', '<', $today)
                                ->whereIn('lead_id', $login_sales_leads)
                                ->select('lead_id', 'id', 'chat', 'next_date', 'created_by', 'stage_id');
                        } elseif ($slug == 'notinterested') {
                            $query = LeadChat::where('stage_id', $status_not_intreseted->id)
                                ->whereIn('lead_id', $login_sales_leads)
                                ->select('lead_id', 'id', 'chat', 'next_date', 'created_by', 'stage_id');
                        } else {
                            $query = LeadChat::whereIn('lead_id', $login_sales_leads)
                                ->select('lead_id', 'id', 'chat', 'next_date', 'created_by', 'stage_id');
                        }
                    } else {

                        if ($slug == 'upcomming') {
                            $query = LeadChat::whereDate('next_date', '>=', $today)
                                ->select('lead_id', 'id', 'chat', 'next_date', 'created_by', 'stage_id');
                        } elseif ($slug == 'expired') {
                            $query = LeadChat::whereDate('next_date', '<', $today)
                                ->select('lead_id', 'id', 'chat', 'next_date', 'created_by', 'stage_id');
                        } elseif ($slug == 'notinterested') {
                            $query = LeadChat::where('stage_id', $status_not_intreseted->id)
                                ->select('lead_id', 'id', 'chat', 'next_date', 'created_by', 'stage_id');
                        } else {
                            $query = LeadChat::select('lead_id', 'id', 'chat', 'next_date', 'created_by', 'stage_id');
                        }
                    }


                    if ($request->start_date && $request->end_date) {
                        $query->whereBetween('next_date', [$request->start_date, $request->end_date]);
                    } elseif ($request->start_date) {
                        $query->whereDate('next_date', '>=', $request->start_date);
                    } elseif ($request->end_date) {
                        $query->whereDate('next_date', '<=', $request->end_date);
                    }

                    if ($request->lead_status) {
                        $query->where('stage_id', $request->lead_status);
                    }

                    if ($request->sales_user) {
                        $login_sales_leads = UserLead::where('user_id', $request->sales_user)->pluck('lead_id');
                        $query->whereIn('lead_id', $login_sales_leads);
                    }

                    // $data = $query->whereIn('id', function ($q) {
                    // $q->selectRaw('MAX(id)')
                    // ->from('lead_chats')
                    // ->groupBy('lead_id');
                    // })->orderBy('id', 'desc')->get();

                    $data = $query->whereIn('id', function ($q) {
                        $q->selectRaw('MAX(id)')
                        ->from('lead_chats')
                        ->groupBy('lead_id');
                    })->orderBy('id', 'desc')->get();


                    return DataTables::of($data)

                        ->addIndexColumn()

                        ->addColumn('follow_up_date', function ($row) {
                            $assign_user = User::find($row->created_by);

                            $assign_user_name = $assign_user ? $assign_user->name : '';
                            $assign_date = Utility::getDateFormated($row->next_date);

                            return '<div class="d-flex justify-content-between">
                                        <div>' . ($assign_date ?? '') . '</div>
                                        <div>
                                        <h5>
                                            <span class="badge bg-primary me-1">' . ($assign_user_name ?? '') . '</span>
                                        </h5>
                                        </div>
                                    </div>';
                        })

                        ->addColumn('lead_all_detail', function ($row) {
                            $lead_detail = '';

                            $lead = Lead::find($row->lead_id); // get lead
                            if ($lead) {
                                $customer = Entity::with(['getAddress'])
                                    ->where('type', 'customer')
                                    ->where('id', $lead->customer_id)
                                    ->first();

                                if ($customer) {
                                    $lead_detail  = $customer->name ?? '';

                                    if ($customer->getAddress) {
                                        $lead_detail .= $customer->getAddress->city ?? '';
                                        $lead_detail .= ', ' . ($customer->getAddress->state ?? '');
                                    }

                                    $lead_detail .= $customer->contact ?? '';
                                    $lead_detail .= $customer->email ?? '';
                                }
                            }

                            // return $lead_detail;
                            $city  = optional(optional(optional($row->getLeadDetail->customer)->getBillingAddress)->get_city)->name;
                            $state = optional(optional(optional($row->getLeadDetail->customer)->getBillingAddress)->get_state)->name;
                            $name = $customer->name ?? '';
                            $phone = $row->getLeadDetail->customerPhone->where('is_primary', 1)->first();
                            $email = $customer->email ?? '';

                            $address = '';

                            if ($name) {

                                $name = ucwords(strtolower($name ?? ''));
                            }
                            if ($phone) {

                                $phone = '<div>
                                            <p class="born timestamp text-muted mb-0">
                                                <i class="ri-phone-fill"></i>' . ($phone?->phone ?? '') . '
                                            </p>
                                        </div>';
                            }
                            if ($email) {

                                $email = '<div>
                                            <p class="born timestamp text-muted mb-0">
                                                <i class="fa-solid fa-envelope"></i> ' . ($email ?? '') . '
                                            </p>
                                            </div>';
                            }
                            if ($city || $state) {

                                $address = '<div>
                                    <p class="text-capitalize born timestamp text-muted mb-0">
                                        <i class="ri-map-pin-2-fill"></i> ' . ($city . ' ' . $state) . '
                                    </p>
                                </div>';
                            }

                            return
                                '<div class="d-flex justify-content-between">' .
                                '<div>' .
                                ($name ?? '') .
                                '</div>' .
                                '</div>' .
                                '<div class="d-flex justify-content-between">' .
                                $phone .
                                $email .
                                '</div>
                                <div class="d-flex justify-content-between">' .
                                $address .
                                '</div>';
                        })
                        ->addColumn('sources', function ($row) {

                            $lead_source_names = '';

                            $lead = Lead::find($row->lead_id);
                            if ($lead && $lead->sources) {
                                $get_sources = explode(',', $lead->sources);

                                $lead_source_names = LeadSource::withTrashed()->whereIn('id', $get_sources)
                                    ->pluck('name')
                                    ->implode(', ');
                            }

                            return
                                '<h5> <span class="badge bg-info me-1">
                                ' . $lead_source_names . '
                                </span> </h5>';
                        })
                        ->addColumn('lead_status', function($row){
                             $lead = Lead::find($row->lead_id);
                            /*
							$lead_status= '';

                            $lead = LeadStage::find($row->stage_id);
                           if ($lead) {
                                $lead_status = '<h4> <span class="badge" style="background-color:' . $lead->color . '; color: #fff;">'
                                            . $lead->name . '</span></h4>';
                            }

                            return $lead_status; */



							$options = '<option value=""> Select Stage</option>';
                            foreach (LeadStage::all() as $stage) {
                                $selected = $lead->stage_id == $stage->id ? 'selected' : '';
                                $options .= '<option value="' . $stage->id . '" ' . $selected . '>' . $stage->name . '</option>';
                            }


                            // $toggleBtn = '<div class="col-md-4"><span>' . optional($row->getUser)->name . '</span></div>';


                            return '<div class="row"><div class="col-md-8"><select class="form-control stage-dropdown form-select need-confirmation" data-data="{id:' . $row->lead_id . '}" data-url="' . route('leads.stage.update', [$row->lead_id, '#sticky']) . '" aria-label=".form-select-sm example">' . $options . '</select></div></div>';

                        })

                        ->addColumn('action', function ($row) {
                            $lead = Lead::find($row->lead_id);
                            $customer = Entity::where('type', 'customer')->where('id', $lead->customer_id)->first();
                            $primary_number = CustomerPhone::where('customer_id', $customer->id)->where('is_primary', 1)->first();

                            $whatsappUrl = $primary_number && $primary_number->phone ? "https://wa.me/{$primary_number->phone}" : '#';

                            return '<div class="dropdown d-inline-block">
                                        <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-more-fill align-middle"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                               <a href="' . route('leads.view', [$row->lead_id]) . '#activity" class="dropdown-item">
                                                    <i class="fa-solid fa-clock-rotate-left align-bottom me-2 text-muted"></i> History
                                                </a>
                                            </li>
                                            <li>
                                                <a href="' . $whatsappUrl . '" target="_blank" class="dropdown-item">
                                                    <i class="fa-brands fa-whatsapp align-bottom me-2 text-muted "></i> WhatsApp
                                                </a>
                                            </li>
                                            <li>
                                                <a href="' . route('customers.edit', [$customer->id]) . '" target="_blank" class="dropdown-item">
                                                    <i class="fa-solid fa-phone align-bottom me-2 text-muted"></i> Edit Contact
                                                </a>
                                            </li>

                                            <li>
                                                <a href="' . route('quotes.create', [$customer->id,$row->lead_id]) . '" class="dropdown-item">
                                                    <i class="fa-solid fa-list align-bottom me-2 text-muted"></i> Create Quotation
                                                </a>
                                            </li>

                                             <li><a class="dropdown-item edit-item-btn" href="javascript:void(0);"
                                                    data-size="lg"
                                                    data-url="' . route("leads.edit", [$row->lead_id]) . '"
                                                    data-ajax-popup="true"
                                                    data-bs-original-title="Edit Lead">
                                                    <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit Lead
                                            </a></li>

                                            <li><a href="' . route('leads.duplicate', [$row->lead_id]) . '" class="dropdown-item">
                                                <i class="ri-file-copy-fill align-bottom me-2 text-muted"></i> Lead Duplicate
                                            </a></li>


                                        </ul>
                                    </div>';
                        })

                        ->addColumn('cust_phone', function ($row) {
                            $get_phone = $row->getLeadDetail->customerPhone->where('is_primary', 1)->first();
                            $get_whatsapp = $row->getLeadDetail->customerPhone->where('is_whatsapp', 1)->first();

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

                        ->rawColumns(['follow_up_date', 'lead_all_detail', 'sources', 'lead_status', 'cust_phone', 'action'])



                        ->make(true);
                } catch (\Exception $e) {

                    return response()->json([
                        'error' => 'Server Error: ' . $e->getMessage()
                    ], 500);
                }
            }
            $get_sales_user = User::where('type', 'Sales')->pluck('name', 'id');
            $list_data['status_list'] = LeadStage::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $list_data['get_sales_user'] = $get_sales_user;
            return view('follow_up.list', $list_data);
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }




    }

}
