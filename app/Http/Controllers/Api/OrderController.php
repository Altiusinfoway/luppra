<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Utility;

class OrderController extends Controller
{
    public function order_list(Request $request)
    {
        try
        {
            $user = JWTAuth::parseToken()->authenticate();

            Log::info('------ start order_list ------');
            Log::info('Request :-', $request->all());

            $order_all = Order::with([
                'getCustomer'=>function ($q)
                {
                    $q->select('id','name');
                },
                'Orderstatus'=> function ($q)
                {
                    $q->select('id','name');
                }
            ])->
            where('created_by', '=', $user->creatorId())->where('user_id',$user->id)->select('id','order_number','customer_id','date','grand_total','payment_status','status','user_id')->get();

            if($order_all->isNotEmpty())
            {
                foreach($order_all as $ord)
                {
                    $ord['date']=Utility::getDateFormated($ord->date);
                }
            }
            Log::info('------ end order_list ------');
            Log::info('------------------------------------------------------------------------------');


            return Utility::return_response(true, "Order list.", $order_all, 200);
        } catch (JWTException $e) {
             \Log::info('order-list error ',[$e->getMessage()]);
            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        }
    }
}
