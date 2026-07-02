<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use App\Models\LeadStage;
use App\Models\Utility;
use App\Models\UserLead;
use App\Models\LeadChat;
use App\Models\User;
use App\Models\Lead;

class FollowUpController extends Controller
{
    public function follow_up_list(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'slug' => 'required',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = JWTAuth::parseToken()->authenticate();

            Log::info('------ start follow_up_list ------');
            Log::info('Request :-', $request->all());

            $slug = $request->slug;
            $today = now()->toDateString();
            $status_not_intreseted = LeadStage::where('name', 'Not Interested')->first();

            $login_sales_leads = Lead::where('user_id',$user->id)->pluck('id');

            if ($slug == 'upcomming') {
                $query = LeadChat::whereDate('next_date', '>=', $today)
                    ->whereIn('lead_id', $login_sales_leads)
                     ->whereIn('id', function ($q) {
                        $q->selectRaw('MAX(id)')
                        ->from('lead_chats')
                        ->groupBy('lead_id');
                    })
                    ->select('id','lead_id','chat','next_date','created_by','stage_id');

            } elseif ($slug == 'expired') {
                $query = LeadChat::whereDate('next_date', '<', $today)
                    ->whereIn('lead_id', $login_sales_leads)
                     ->whereIn('id', function ($q) {
                        $q->selectRaw('MAX(id)')
                        ->from('lead_chats')
                        ->groupBy('lead_id');
                    })
                    ->select('id','lead_id','chat','next_date','created_by','stage_id');

            } elseif ($slug == 'notinterested') {
                $query = LeadChat::where('stage_id', $status_not_intreseted->id)
                    ->whereIn('lead_id', $login_sales_leads)
                      ->whereIn('id', function ($q) {
                        $q->selectRaw('MAX(id)')
                        ->from('lead_chats')
                        ->groupBy('lead_id');
                    })
                    ->select('id','lead_id','chat','next_date','created_by','stage_id');

            } else {
                $query = LeadChat::whereIn('lead_id', $login_sales_leads)
                ->whereIn('id', function ($q) {
                    $q->selectRaw('MAX(id)')
                    ->from('lead_chats')
                    ->groupBy('lead_id');
                })
                ->select('id', 'lead_id', 'chat', 'next_date', 'created_by', 'stage_id');
            }


            $data = $query->with([
                'getLeadDetail:id,customer_id,sources',

                'getLeadDetail.customer:id,name,email',

                'getLeadDetail.customer.getCustomerPhone' => function ($q) {
                    $q->where('is_primary', 1)
                    ->select('id', 'customer_id', 'phone', 'is_primary');
                }

            ])->orderBy('id','desc')->get();

            if(count($data) > 0)
            {
                foreach($data as $dt)
                {
                    $usr = User::where('id',$dt['created_by'])->first();
                    $dt['created_user_name'] =isset($usr) ? $usr->name :'';

                    $lead_rcd = Lead::where('id',$dt->lead_id)->first();
                    $dt['stage_id']=$lead_rcd->stage_id;

                    $lead_stage = LeadStage::withTrashed()->where('id',$lead_rcd->stage_id)->select('id','name')->first();
                    $dt['get_lead_status']= $lead_stage ?? null;

                    $dt['get_lead_detail'] = $lead_rcd->source_list;
                     if ($dt->getLeadDetail) {
                        $dt->getLeadDetail->source_list = $lead_rcd->source_list;
                    }

                }
            }

            Log::info('------ end follow_up_list ------');
            Log::info('------------------------------------------------------------------------------');

            return Utility::return_response(true, "follow-up list.", $data, 200);

        } catch (\Exception $e) {
            Log::error("Error follow_up_list: ".$e->getMessage());
            return Utility::return_response(false, "Something went wrong.", "", 500);
        }
    }
}
