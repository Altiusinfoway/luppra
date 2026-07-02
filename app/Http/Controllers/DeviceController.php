<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Http;

use App\Models\Device;
use App\Services\WhatsappSessionStatusService;

class DeviceController extends Controller
{
    private function whatsappServerUrl()
    {
        $url = (string) env('WA_SERVER_URL', '');
        if (trim($url) === '') {
            $url = 'http://127.0.0.1:8002';
        }

        return rtrim($url, '/');
    }

    public function index(Request $request)
    {
        $devices=Device::where('user_id',Auth::id())->get();
        return view('device.index',compact('devices'));
    }

     public function create(Request $request)
    {
        return view('device.create');
    }

    public function store(Request $request)
    {
        //  return redirect('device/111/qr');


        Log::info('device store');

        // if (getUserPlanData('device_limit') == false) {
        //     return response()->json([
        //         'message'=>__('Maximum Device Limit Exceeded')
        //     ],401);
        // }

        $validated = $request->validate([
            'name' => 'required|max:100',
            'phone' => 'required|max:30',
            'webhook_url' => 'nullable|url|max:100',
            'is_lead_mobile_number' => 'nullable|boolean',
        ]);

        $device=new Device;
        $device->user_id=Auth::id();
        $device->name=$request->name;
        $device->hook_url=$request->webhook_url;
        $device->phone=$request->phone;
        $connectionName = $device->getConnectionName() ?: config('database.default');
        if (Schema::connection($connectionName)->hasColumn('devices', 'is_lead_mobile_number')) {
            $device->is_lead_mobile_number = $request->boolean('is_lead_mobile_number') ? 1 : 0;
        }
        $device->save();
         \Log::info('------------ device created ------------');


        // return redirect()->route('device.scan',$device->uuid)->with('success','ddd');
        return response()->json([
            'redirect'=>url('device/'.$device->uuid.'/qr'),
            'message'=>__('Device Created Successfully')
        ],200);
    }

      public function scanQr($id)
    {
       \Log::info('------------ scanQr function controller------------');
        $device=Device::where('user_id',Auth::id())->where('uuid',$id)->first();
        $check_device_active = Device::where('user_id',Auth::id())->where('uuid',$id)->where('status',1)->first();
        abort_if(empty($device),404);
        return view('device.qr',compact('device','check_device_active'));
    }

    public function getQr($id)
    {
        \Log::info('---------- getQr fun controller ----------');
        $device= Device::where('user_id',Auth::id())->where('uuid',$id)->first();
        abort_if(empty($device),404);

         \Log::info('---- getQr fun controller =>env => ----',[env('WA_SERVER_URL')]);

        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;
        $response=Http::post($this->whatsappServerUrl().'/sessions/add',[
                'id'       => $device->whatsappSessionId(),
                'typeAuth' =>'qr',
                'phoneNumber' =>$device->phone,
                'tenant_id' => $tenant?->id,
                'tenant_slug' => $tenant?->slug,
        ]);

        \Log::info('---------- sessions/add ----------');
        \Log::info([$response]);

        if ($response->status() == 200) {
              \Log::info('inside if 200');
             $body=json_decode($response->body());
             if (isset($body->data->qrcode)) {
                $data['qr']=$body->data->qrcode;
                $data['message']=$body->message;
                $device->qr=$body->data->qrcode;
                $device->save();
                 \Log::info('save qr img');
                return response()->json($data);
             }

        }
        elseif($response->status() == 409){
            \Log::info('inside elseif 409');
            $data['qr']      = $device->qr;
            $data['message'] = __('QR code received, please scan the QR code');
            return response()->json($data);
        }
    }

    public function checkSession($id, WhatsappSessionStatusService $sessionStatusService)
    {
        \Log::info('---------- checkSession fun controller -----------');

       $device=Device::where('user_id',Auth::id())->where('uuid',$id)->first();
       abort_if(empty($device),404);

       $session = $sessionStatusService->forDevice($device);

       $device->status = !empty($session['can_open_chat']) ? 1 : 0;
       if (!empty($session['can_open_chat'])) {
            $device->qr = null;
       }
       $device->save();

       return response()->json([
            'status' => $session['status'] ?? 'not_ready',
            'message' => $session['message'] ?? null,
            'connected' => !empty($session['can_open_chat']),
            'can_open_chat' => !empty($session['can_open_chat']),
            'should_redirect_to_qr' => !empty($session['should_redirect_to_qr']),
            'qr_available' => !empty($session['qr_available']),
       ]);

    }

    public function logoutSession($id)
    {
       $device=Device::where('user_id',Auth::id())->where('uuid',$id)->first();
       abort_if(empty($device),404);

       $device->status=0;
       $device->qr=null;
       $device->save();

       $id=$device->id;
       $response=Http::delete($this->whatsappServerUrl().'/sessions/delete/'.$device->whatsappSessionId());

      return response()->json(['message'=>__('Congratulations! Your Device Successfully Logout')]);

    }


}
