<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FacebookController extends Controller
{
    public function create(Request $request)
    {
        return view('facebook.create');
    }

    public function login()
    {

        $scopes = implode(',', [
            'pages_show_list',
            'pages_read_engagement'
        ]);

        $url =
        'https://www.facebook.com/v18.0/dialog/oauth?' .
        'client_id=' . env('FACEBOOK_APP_ID') .
        '&redirect_uri=' .
        urlencode(env('FACEBOOK_REDIRECT_URI')) .
        '&scope=' . $scopes;

        return redirect($url);

        // $url =
        // 'https://www.facebook.com/v18.0/dialog/oauth?' .
        // 'client_id=' . env('FACEBOOK_APP_ID') .
        // '&redirect_uri=' .
        // urlencode(env('FACEBOOK_REDIRECT_URI')) .
        // '&scope=' .
        // 'pages_show_list,' .
        // 'pages_read_engagement,' .
        // 'pages_manage_metadata,'.
        // 'pages_manage_ads,' .
        // 'leads_retrieval';

        // return redirect($url);
    }

    // Step 2: Facebook returns here
    public function callback(Request $request)
    {
        \Log::info('========== callback ==============');
         if(!$request->code){
            return redirect()
            ->back()
            ->with('error','Facebook login failed');
        }

        $response = Http::get(
            'https://graph.facebook.com/v18.0/oauth/access_token',
            [
                'client_id'=>env('FACEBOOK_APP_ID'),
                'client_secret'=>env('FACEBOOK_APP_SECRET'),
                'redirect_uri'=>env('FACEBOOK_REDIRECT_URI'),
                'code'=>$request->code
            ]
        );

        $tokenData = $response->json();




        \Log::info('token data ',[ $tokenData]);

         return redirect()->route(
            'facebooks.get_data',
            ['token' =>$tokenData['access_token']]
        );

        // return redirect()->route(
        //     'facebooks.get_data',
        //     ['token' => 'AQL8Fo7vMUsDUYmnQSBDV6yXYKrdalHv0chRZ0wATPysoKWiBRHMRSUiVNevcgQ_vJhCo5htocdmqN5KhFcipQYsrOEFoMeKwPY2lcMfrjxUIAkNwEIzdQ80Eviso_gszfpRG5LWpgrPKgf2rXTawGCFrO1pbVL9_ofWRwdDkkM1nZ-Qg8JZfJ2qVfMfLWI_dnRgigxqJIvdIli4ehqBzx_PDhQQnWa1ppkNk0Pb_FaZMty93XstVl_B6NjmBTmUdeFCj6Zm_MwQfiRHvoJT02VZ12MqAKu2ZRNlODbcSoXDtN744h4fT1UcQxfnDT1TOG_r3ahmnUuwQR1Af0v5AC6_ZQ0ilkTee1egwx6PwvGuNOa55oEiycGovxVRBJgUZHtJt-RWf4f3Y5RhpnOtgF2YXPXb8yvHTxVJ9ScMM5TGKNYqlQkyzk0O4nVGd2A0VZGvT_CFVkvKlBEMLFaf1aEe']
        // );
    }


     public function getData(Request $request)
    {
        \Log::info('========== getData ==============');
        $userToken = $request->token;

        \Log::info('userToken'.$userToken);

        if (!$userToken) {
            return back()->with(
                'error',
                'Access token not found'
            );
        }

        // Get pages
        $pageResponse = Http::withToken($userToken)
            ->get(
                'https://graph.facebook.com/v18.0/me/accounts'
            );

        $pages = $pageResponse->json();
        \Log::info('response ACCOUNT ',[$pages]);

        \Log::info('FB RESPONSE', [
            'status' => $pageResponse->status(),
            'body' => $pageResponse->body(),
        ]);

        $firstPage = $pages['data'][0] ?? [];
        $settingsConnection = config('database.default', 'mysql');

        \Log::info('tenant db ',[$settingsConnection]);

        $creatorId = (int) auth()->id();
        $upsertSetting = function (string $name, ?string $value) use ($settingsConnection, $creatorId): void {
            DB::connection($settingsConnection)->table('settings')->updateOrInsert(
                ['name' => $name, 'created_by' => $creatorId],
                ['value' => $value]
            );
        };

        $upsertSetting('facebook_login_token', $userToken);
        $upsertSetting('facebook_page_id', $firstPage['id'] ?? null);
        $upsertSetting('facebook_page_name', $firstPage['name'] ?? null);
        $upsertSetting('facebook_page_token', $firstPage['access_token'] ?? null);

        $expiryDate = Carbon::now()->addDays(55)->toDateString();
        $upsertSetting('facebook_token_expiry_date', $expiryDate);
        $upsertSetting('facebook_token_is_error', '0');

        return redirect()
            ->route('facebooks.create')
            ->with(
                'success',
                'Facebook connected successfully'
            );
    }

    public function checkTokenExpiry()
    {
        \Log::info('========== checkTokenExpiry Scheduled Run ==============');

        $settingsConnection = config('database.default', 'mysql');
        $today = Carbon::now()->toDateString(); // Current System Date

        // 1. Get all settings blocks where token is not marked as error yet
        $expirySettings = DB::connection($settingsConnection)->table('settings')
            ->where('name', 'facebook_token_expiry_date')
            ->get();

        foreach ($expirySettings as $setting) {
            $expiryDate = $setting->value;
            $creatorId = $setting->created_by;

            // 2. Check if current system date matches or has passed the 55-day expiry threshold
            if ($expiryDate && $today >= $expiryDate) {

                \Log::warning("Token expired for user ID: {$creatorId}. Expiry Date was: {$expiryDate}");

                // Set facebook_token_is_error flag to 1
                DB::connection($settingsConnection)->table('settings')->updateOrInsert(
                    ['name' => 'facebook_token_is_error', 'created_by' => $creatorId],
                    ['value' => '1']
                );
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Token check complete.']);
    }

}
