<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendBulkMessageJob;
use App\Models\Device;
use App\Models\Entity;
use App\Models\LeadStage;
use App\Traits\Whatsapp;
use Cache;
use App\Rules\Phone;
use App\Services\WhatsappSessionStatusService;
use App\Support\Tenancy\TenantUsageService;

class ChatController extends Controller
{
    use Whatsapp;

    private function abortUnlessTenantBulkMessageAllowed(): void
    {
        if (Auth::check() && Auth::user()->type === 'super admin') {
            abort(403, 'Bulk message is not available in super admin.');
        }

        if (!Auth::check() || !Auth::user()->can('manage bulk message')) {
            abort(403, 'Permission Denied.');
        }
    }

    public function bulkMessage()
    {
        $this->abortUnlessTenantBulkMessageAllowed();

        $leadStages = LeadStage::query()
            ->orderBy('order')
            ->orderBy('id')
            ->get(['id', 'name']);

        $devices = Device::query()
            ->where('user_id', Auth::id())
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        $customers = Entity::query()
            ->where('type', 'customer')
            ->where('is_active', 1)
            ->whereHas('getCustomerPhone', function ($query) {
                $query->where('is_whatsapp', 1);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'company_name']);

        return view('chat.bulk_message', compact('leadStages', 'devices', 'customers'));
    }

    public function sendBulkMessage(Request $request, WhatsappSessionStatusService $sessionStatusService)
    {
        $this->abortUnlessTenantBulkMessageAllowed();

        $validated = $request->validate([
            'send_mode' => ['required', 'in:lead_status,direct_customers'],
            'stage_id' => ['required_if:send_mode,lead_status', 'nullable', 'integer'],
            'customer_ids' => ['required_if:send_mode,direct_customers', 'array'],
            'customer_ids.*' => ['integer'],
            'device' => ['required', 'integer'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $stage = null;
        if ($validated['send_mode'] === 'lead_status') {
            $stage = LeadStage::query()->findOrFail((int) $validated['stage_id']);
        } else {
            $customerCount = Entity::query()
                ->where('type', 'customer')
                ->whereHas('getCustomerPhone', function ($query) {
                    $query->where('is_whatsapp', 1);
                })
                ->whereIn('id', $validated['customer_ids'] ?? [])
                ->count();

            if ($customerCount !== count(array_unique($validated['customer_ids'] ?? []))) {
                return redirect()
                    ->route('bulk-message.index')
                    ->with('error', 'Selected customers are invalid.');
            }
        }

        $device = Device::query()
            ->where('user_id', Auth::id())
            ->where('status', 1)
            ->findOrFail((int) $validated['device']);

        $session = $sessionStatusService->forDevice($device);
        if (($session['status'] ?? 'not_ready') !== 'connected') {
            return redirect()
                ->route('device.scan', $device->uuid)
                ->with('error', $session['message'] ?? 'Please reconnect this device.');
        }

        SendBulkMessageJob::dispatch(
            0,
            (int) Auth::id(),
            (string) $validated['send_mode'],
            $stage ? (int) $stage->id : null,
            array_values(array_unique($validated['customer_ids'] ?? [])),
            (int) $device->id,
            (string) $validated['message']
        );

        $targetLabel = $stage ? "for '{$stage->name}'" : 'for selected customers';

        return redirect()
            ->route('bulk-message.index')
            ->with('success', "Bulk message {$targetLabel} has been sent.");
    }

    private function whatsappJsonResponse(string $message, string $code, int $status, array $extra = []): \Illuminate\Http\JsonResponse
    {
        return response()->json(array_merge([
            'message' => $message,
            'code' => $code,
        ], $extra), $status);
    }

    private function blockedSendResponse(Device $device, array $session): \Illuminate\Http\JsonResponse
    {
        $status = (string) ($session['status'] ?? 'not_ready');
        $message = (string) ($session['message'] ?? 'WhatsApp session is not ready. Please reconnect this device.');

        $payload = [
            'whatsapp_status' => $status,
            'can_open_chat' => (bool) ($session['can_open_chat'] ?? false),
            'should_redirect_to_qr' => (bool) ($session['should_redirect_to_qr'] ?? false),
            'qr_available' => (bool) ($session['qr_available'] ?? false),
        ];

        if ($status === 'qr_required') {
            $payload['redirect'] = route('device.scan', $device->uuid);
        }

        return $this->whatsappJsonResponse($message, 'whatsapp_send_blocked', 409, $payload);
    }

    public function chats($id, WhatsappSessionStatusService $sessionStatusService)
    {
        $device = Device::where("user_id", Auth::id())
            ->where("uuid", $id)
            ->first();
        abort_if(empty($device), 404);

        $session = $sessionStatusService->forDevice($device);
        if (($session['status'] ?? 'not_ready') !== 'connected') {
            return redirect()
                ->route('device.scan', $device->uuid)
                ->with('error', $session['message'] ?? 'Please reconnect this device.');
        }

        // $templates = Template::where("user_id", Auth::id())
        //     ->where("status", 1)
        //     ->latest()
        //     ->get();
        $templates=[];
        return view("chat.list", compact("device","templates"));
    }

    public function chatHistory($id)
    {
        $device = Device::where("user_id", Auth::id())
            ->where("uuid", $id)
            ->first();
        abort_if(empty($device), 404);

        $response = $this->getChats($device->id);
        if ($response["status"] == 200) {
            $data["chats"] = $response["data"];
            $data["device_name"] = $device->name;
            $data["phone"] = $device->phone;
            return response()->json($data);
        }

        $data["message"] = $response["message"];
        $data["status"] = $response["status"];

        return response()->json($data, 401);
    }

    public function chatMessages(Request $request, $id)
    {
        $device = Device::where("user_id", Auth::id())
            ->where("uuid", $id)
            ->first();
        abort_if(empty($device), 404);

        $validated = $request->validate([
            "number" => "nullable|string|max:60|required_without:jid",
            "jid" => "nullable|string|max:120|required_without:number",
            "limit" => "nullable|integer|min:1|max:200",
            "is_group" => "nullable|boolean",
        ]);

        $limit = (int) ($validated["limit"] ?? 60);
        $isGroup = (bool) ($validated["is_group"] ?? false);

        $jidOrNumber = !empty($validated["jid"]) ? $validated["jid"] : $validated["number"];
        $response = $this->getChatMessages($device->id, $jidOrNumber, $limit, $isGroup, $validated["number"] ?? "");

        if (($response["status"] ?? 500) === 200) {
            return response()->json([
                "messages" => $response["data"] ?? [],
            ]);
        }

        return response()->json([
            "message" => $response["message"] ?? "Unable to load messages",
            "status" => $response["status"] ?? 500,
        ], 400);
    }

    public function downloadMedia(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|max:120',
            'remoteJid' => 'required|string|max:190',
            'messageId' => 'required|string|max:190',
        ]);

        $device = Device::where('user_id', Auth::id())
            ->where('uuid', $validated['id'])
            ->first();
        abort_if(empty($device), 404);

        $whatsServer = rtrim((string) env('WA_SERVER_URL', 'http://127.0.0.1:8002'), '/');

        try {
            $response = Http::post($whatsServer . '/chats/download-media?id=' . urlencode($device->whatsappSessionId()), [
                'remoteJid' => $validated['remoteJid'],
                'messageId' => $validated['messageId'],
            ]);

            return response()->json($response->json() ?? [], $response->status());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to download media right now.',
            ], 500);
        }
    }

     public function sendMessage(Request $request, $id, WhatsappSessionStatusService $sessionStatusService)
    {
        $device = Device::where("user_id", Auth::id())
            ->where("uuid", $id)
            ->first();
        abort_if(empty($device), 404);

        $validated = $request->validate([
            "reciver" => "required|string|max:30",
            "message" => "nullable|string|max:1000|required_without:file",
            "file" => "nullable|file|mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,csv,txt,ppt,pptx,mp4,mov,avi,mkv,mp3,wav,ogg,aac,webm|max:20480",
        ]);

        $type = "plain-text";
        $payload = [];
        $filter = true;

        if ($request->hasFile("file")) {
            $attachment = $this->saveFile($request, "file");
            if (empty($attachment)) {
                return $this->whatsappJsonResponse('File upload failed', 'whatsapp_send_failed', 422);
            }

            $type = "text-with-media";
            $payload = [
                "message" => $this->formatText($request->message ?? ""),
                "attachment" => $attachment,
            ];
            $filter = false;
        } else {
            $payload = [
                "text" => $this->formatText($request->message ?? ""),
            ];
        }

        try {
            // $usage = app(TenantUsageService::class);
            // if (!$usage->canSendWhatsapp()) {
            //     return $this->whatsappJsonResponse(
            //         'WhatsApp message limit reached for your current plan.',
            //         'whatsapp_send_blocked',
            //         403
            //     );
            // }

            $session = $sessionStatusService->forDevice($device);
            if (($session['status'] ?? 'not_ready') !== 'connected') {
                return $this->blockedSendResponse($device, $session);
            }

            $response = $this->messageSend(
                $payload,
                $device->id,
                $request->reciver,
                $type,
                $filter
            );

            if (($response["status"] ?? 500) == 200) {
                $logs["user_id"] = Auth::id();
                $logs["device_id"] = $device->id;
                $logs["from"] = $device->phone ?? null;
                $logs["to"] = $request->reciver;
                $logs["template_id"] = null;
                $logs["type"] = "single-send";
                $this->saveLog($logs);
                $usage->recordWhatsappSent(1);

                return response()->json(["message" => __("Message sent successfully..!!")], 200);
            }

            $errorMessage = $response["message"] ?? "Request Failed";
            return $this->whatsappJsonResponse($errorMessage, 'whatsapp_send_failed', 401);
        } catch (\Throwable $e) {
            \Log::error("sendMessage failed", ["error" => $e->getMessage()]);
            return $this->whatsappJsonResponse('Request Failed', 'whatsapp_send_failed', 401);
        }
    }

     //return custom text message view page
    public function index()
    {

        $phoneCodes=file_exists('uploads/phonecode.json') ? json_decode(file_get_contents('uploads/phonecode.json')) : [];
        $devices=Device::where('user_id',Auth::id())->where('status',1)->get();
        return view('chat.singlesend_create',compact('phoneCodes','devices'));
    }

      //sent custom text msg request to api
    public function sentCustomText(Request $request,$type)
    {
        $validated = $request->validate([
            'phone'   => ['required', new Phone],
            'device'=>['required','numeric'],
        ]);

        // $usage = app(TenantUsageService::class);
        // if (!$usage->canSendWhatsapp()) {
        //     return response()->json([
        //         'message' => __('WhatsApp message limit reached for your current plan.'),
        //     ], 403);
        // }

        // if (getUserPlanData('messages_limit') == false) {
        //     return response()->json([
        //         'message'=>__('Maximum Monthly Messages Limit Exceeded')
        //     ],401);
        // }

        // if ($request->templatestatus) {
        //     if (getUserPlanData('template_limit') == false) {
        //         return response()->json([
        //             'message'=>__('Maximum Template Limit Exceeded')
        //         ],401);
        //     }
        // }

        \Log::info("type ",[$type]);
        \Log::info("request device ",[$request->device]);

        $device=Device::where('user_id',Auth::id())->where('status',1)->find($request->device);
         \Log::info("device ",[$device]);

        \Log::info("----------dddd-----");
        if(!$device)
        {
            $device_1 = Device::where('user_id',Auth::id())->where('status',0)->find($request->device);
            if($device_1)
            {
                \Log::info('------- status 0 -------');
                return response()->json([
                        'message' => __('please login again !!'),
                        'redirect'=>route('device.scan',$device_1->uuid),
                    ], 400);

                return redirect()->route('device.scan',$device_1->uuid)->with('error','dd');
            }
            \Log::info('------- status not 1 -------');
             return response()->json([
                    'message' => __('Please Add device !!'),
                    'redirect'=>route('device'),
                ], 400);
             return redirect()->route('device')->with('error','Please Add device');
        }

        $phone=str_replace('+', '', $request->phone);

        if ($type == 'text-with-media')
        {
            \Log::info('if text-with-media');
            // $validated = $request->validate([
            //     'file' => 'required|mimes:jpg,jpeg,png,webp,gif,pdf,docx,xlsx,csv,txt|max:1000',
            //     'message' => 'required|max:1000',
            // ]);

            \Log::info('first req ',[$request->all()]);
            if ($request->hasFile('file'))
            {

                $file=$this->saveFile($request,'file');
                if (empty($file)) {
                    return response()->json([
                        'message' => __('Invalid attachment upload.'),
                    ], 422);
                }
                $request['attachment']=$file;
                \Log::info('if section file newly upload',[$request->all()]);
            }
            else
            {
                \Log::info('else section exitsting full file path',[$request->all()]);
                $request['file'] = $request->input('file');
                $request['message'] = $request->input('message');
                $file=$this->saveFile($request,'file');
                if (empty($file)) {
                    return response()->json([
                        'message' => __('Invalid attachment reference.'),
                    ], 422);
                }
                $request['attachment']=$file;
            }
        }
        elseif ($type == 'text-with-vcard')
        {
            $validated = $request->validate([
                'display_name' => 'required|max:100',
                'full_name' => 'required|max:100',
                'org_name' => 'required|max:100',
                'contact_number' => ['required', new Phone,'max:20'],
                'wa_number' => ['required', new Phone,'max:20'],

            ]);
        }
        elseif ($type == 'text-with-button')
        {
            $validated = $request->validate([
                'footer_text' => 'required|max:100',
                'buttons.*' => 'required|max:50',
                'message' => 'required|max:1000',
            ]);

             if (count($request->buttons) > 3) {
                return response()->json([
                    'message' => __('Maximum Button Limit Is 3'),
                ], 403);
             }
        }
        elseif ($type == 'text-with-template')
        {
            $validated = $request->validate([
                'footer_text' => 'required|max:100',
                'buttons.*' => 'required|max:50',
                'message' => 'required|max:1000',
            ]);

            if (count($request->buttons) > 3) {
                return response()->json([
                    'message' => __('Maximum Button Limit Is 3'),
                ], 403);
            }

            $is_valid=true;
            $error_message= __('Please Follow the site rules');
            $types=['urlButton','callButton','quickReplyButton'];
            $properties=['displaytext','action','type'];

            foreach ($request->buttons as $key => $button) {
                if (count($button) < 3) {
                   $is_valid = false;
                   break;
                }
                else{


                     foreach ($button as $buttonKey => $buttonValue) {
                        if ($buttonKey == 'type') {
                            if (!in_array($buttonValue, $types)) {
                              $is_valid = false;
                              break;
                            }
                        }

                        if (!in_array($buttonKey, $properties)) {
                            $is_valid = false;
                            break;
                        }

                        else{


                            if($buttonKey == 'action'){

                                if (!empty($buttonValue)) {
                                    if (strlen($buttonValue) > 50) {
                                        $error_message=__('Maximum Button Value Limit 50');
                                        $is_valid = false;
                                    }
                                }
                                if ($button['type'] != 'quickReplyButton') {
                                     if (empty($buttonValue)) {

                                        $error_message=__('fill up all the fields');
                                        $is_valid = false;
                                     }
                                }


                            }
                            else{


                                if (empty($buttonValue) || $buttonValue == null) {

                                    $error_message= __('fill up all the fields');
                                    $is_valid = false;
                                    break;
                                }
                                else{
                                    if (strlen($buttonValue) > 50) {
                                        $error_message=__('Maximum Button Value Limit 50');
                                        $is_valid = false;
                                    }
                                }

                            }

                        }

                    }
                }

            }

            if ($is_valid == false) {
                return response()->json([
                    'message' => $error_message,
                ], 403);
            }
        }
        elseif ($type == 'text-with-location')
        {
            $validated = $request->validate([
                'degreesLatitude' => 'required|max:100',
                'degreesLongitude' => 'required|max:100',
            ]);
        }
        elseif ($type == 'text-with-list')
        {
            $validated = $request->validate([
                'header_title' => 'required|max:30',
                'message' => 'required|max:300',
                'footer_text' => 'required|max:30',
                'button_text' => 'required|max:30',
                'section.*' => 'required|max:1000',

            ]);

            $is_valid= count($request->section ?? []) > 20 ? false : true;
            $error_message=__('Maximum Section Limit Is 20');

            if ($is_valid == false) {
                return response()->json([
                    'message' => $error_message,
                ], 403);
            }



            foreach ($request->section as $key => $section) {

               if (count($section['value'] ?? []) == 0) {
                   $is_valid=false;
                   $error_message=__('Fill up the list option value');

                   break;
               }
               elseif ($section['title'] == null || !$section['title']) {
                   $is_valid=false;
                   $error_message=__('Fill up all the title field');

                   break;
               }
               elseif (strlen($section['title']) > 50) {
                   $is_valid=false;
                   $error_message=__('Maximum title limit is 50');

                   break;
               }
               else{
                foreach ($section['value'] as $value_key => $value) {
                    if (empty($value['title'])) {
                     $is_valid=false;
                     $error_message=__('Option title is required');

                     break;
                    }
                    elseif (strlen($value['title']) > 50) {
                     $is_valid=false;
                     $error_message=__('List value name maximum word limit is 50');

                     break;
                    }
                    elseif (strlen($value['description']) > 50) {
                        $is_valid=false;
                        $error_message=__('List value description maximum word limit is 50');

                        break;
                    }
                }
               }
            }

            if ($is_valid == false) {
                return response()->json([
                    'message' => $error_message,
                ], 403);
            }
        }

        if ($request->templatestatus)
        {
            $validated = $request->validate([
                'template_name' => 'required|max:100',
            ]);

           $template=$this->saveTemplate($request->all(), $request->message,$type,Auth::id());
           if ($template == false) {
               return response()->json([
                    'message' => __('Maximum Template Limit Exceeded'),
                ], 403);
           }
        }

        $whatsapp= $this->messageSend($request->all(),$device->id,$phone,$type);

        \Log::info('whatsapp messageSend response ',[$whatsapp]);
        if ($whatsapp['status'] != 200)
        {
            // return redirect()->route('device.scan',)->with('error','Login Again');
            \Log::info("status not 200");
            return response()->json([
                    'message' => $whatsapp['message']." please login again",
                    'redirect'=>route('device'),
                ], $whatsapp['status']);
        }
        else
        {
           $logs['user_id']=Auth::id();
           $logs['device_id']=$device->id;
           $logs['from']=$device->phone;
           $logs['to']=$phone;
           $logs['type']='single-send';

           $this->saveLog($logs);
        //    $usage->recordWhatsappSent(1);

           return response()->json([
                    'message' => __('Message sent successfully..!!'),
                ], 200);
        }

    }

}
