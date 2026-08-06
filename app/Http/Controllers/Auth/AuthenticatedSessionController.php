<?php

namespace App\Http\Controllers\Auth;

use App\Events\VerifyReCaptchaToken;
use App\Models\Customer;
use App\Models\LoginDetail;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\Vender;
use App\Support\Tenancy\TenantAuditLogger;
use  App\Models\Utility;
use  App\Models\User;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use DateTime;
use App\Models\Permission;
use App\Models\Role;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */


    public function __construct()
    {
        
    }



    public function store(LoginRequest $request)
    {

        $user = User::where('email',$request->email)->first();
        $resolvedTenantId = $this->resolveTenantIdForLogin($request);
        if ($user != null && $user->type != 'super admin') {
            if ($resolvedTenantId !== null && (int) ($user->tenant_id ?? 0) !== (int) $resolvedTenantId) {
                return redirect()->back()->with('status', __('This login URL does not belong to your tenant.'));
            }
        }

        if($user != null)
        {
            $companyUser = User::where('id' , $user->created_by)->first();
        }

        if ($user != null && $user->type != 'super admin') {
            $hasIsDisableColumn = Schema::hasColumn('users', 'is_disable');
            if (
                $user->type != 'company'
                && $hasIsDisableColumn
                && (int) ($user->is_disable ?? 1) === 0
            ) {
                return redirect()->back()->with('status', __('Your Account is disable,please contact your Administrator.'));
            }

            $hasEnableLoginColumn = Schema::hasColumn('users', 'is_enable_login');
            $isUserLoginDisabled = $hasEnableLoginColumn && (int) ($user->is_enable_login ?? 1) === 0;
            $isCompanyLoginDisabled = $hasEnableLoginColumn && isset($companyUser) && $companyUser != null && (int) ($companyUser->is_enable_login ?? 1) === 0;

            if ($isUserLoginDisabled || $isCompanyLoginDisabled) {
                return redirect()->back()->with('status', __('Your Account is disable from company.'));
            }
        }

        $settings = Utility::settings();
        //ReCpatcha
        $validation = [];

        if(isset($settings['recaptcha_module']) && $settings['recaptcha_module'] == 'on')
        {
            if($settings['google_recaptcha_version'] == 'v2-checkbox'){
                $validation['g-recaptcha-response'] = 'required|captcha';
            }
            elseif($settings['google_recaptcha_version'] == 'v3'){
                $result = event(new VerifyReCaptchaToken($request));

                if (!isset($result[0]['status']) || $result[0]['status'] != true) {
                    $key = 'g-recaptcha-response';
                    $request->merge([$key => null]); // Set the key to null

                    $validation['g-recaptcha-response'] = 'required';
                }
            }else{
                $validation = [];
            }
        }
        else{
            $validation = [];
        }
        $this->validate($request, $validation);
        User::defaultEmail();
        if($user != null) {
            $user->userDefaultDataRegister($user->id);
        }

        $request->authenticate();
        $request->session()->regenerate();
        $user = Auth::user();

        if ($user->type != 'super admin') {
            $userTenantId = (int) ($user->tenant_id ?? 0);
            if ($userTenantId <= 0 && (int) ($user->created_by ?? 0) > 0) {
                $creatorTenantId = (int) (User::query()->where('id', (int) $user->created_by)->value('tenant_id') ?? 0);
                if ($creatorTenantId > 0) {
                    $user->tenant_id = $creatorTenantId;
                    $user->save();
                    $userTenantId = $creatorTenantId;
                }
            }
            if ($userTenantId <= 0) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', __('Tenant is not assigned to this user.'));
            }

            $request->session()->put('tenant_id', $userTenantId);
            $request->session()->put('login_tenant_id', $userTenantId);
            TenantAuditLogger::log(
                event: 'tenant_login_success',
                tenantId: $userTenantId,
                userId: (int) $user->id,
                message: 'Tenant user logged in.',
                meta: ['host' => (string) $request->getHost()]
            );

            if ($this->isCentralHost((string) $request->getHost()) && $resolvedTenantId === null) {
                $tenantSlug = Tenant::query()->where('id', $userTenantId)->value('slug');
                if (!empty($tenantSlug)) {
                    return redirect()->to(route('dashboard', ['tenant' => $tenantSlug]));
                }
            }
        } else {
            $request->session()->forget('tenant_id');
            $request->session()->forget('login_tenant_id');
        }

        $companyUser = User::find($user->created_by);
        $status = $companyUser ? $companyUser->delete_status : 1;

        if($user->delete_status == 0 || $status == 0)
        {
            auth()->logout();
            return redirect()->back()->with('status', __('Your Account is deleted by admin,please contact your Administrator.'));
        }

        if($user->is_active == 0)
        {
            auth()->logout();
            return redirect()->back()->with('status', __('Your Account is deactive by admin,please contact your Administrator.'));
        }

        $user = \Auth::user();
        if(isset($user->type) && $user->type == 'company')
        {
            $plan = Plan::find($user->plan);
            if($plan)
            {
                if($plan->duration != 'lifetime')
                {
                    $datetime1 = new \DateTime($user->plan_expire_date);
                    $datetime2 = new \DateTime(date('Y-m-d'));
                    //                    $interval  = $datetime1->diff($datetime2);
                    $interval = $datetime2->diff($datetime1);
                    $days     = $interval->format('%r%a');
                    if($days <= 0)
                    {
                        $user->assignPlan(1);

                        return redirect()->intended(route('dashboard'))->with('error', __('Your Plan is expired.'));
                    }
                }

                if($user->trial_expire_date != null)
                {
                    if(\Auth::user()->trial_expire_date < date('Y-m-d'))
                    {
                        $user->assignPlan(1);

                        return redirect()->intended(route('dashboard'))->with('error', __('Your Trial plan Expired.'));
                    }
                }
            }
        }

        $setting = Utility::settingsById($user->creatorId());

        $timezone = $setting['timezone'] ? $setting['timezone'] : 'Asia/Kolkata';
        date_default_timezone_set($timezone);

        // Update Last Login Time
        $user->update(
            [
                'last_login_at' => Carbon::now()->toDateTimeString(),
            ]
        );

        //start for user log
        if($user->type != 'company' && $user->type != 'super admin')
        {
        //            $ip = '49.36.83.154'; // This is static ip address
            $ip = $_SERVER['REMOTE_ADDR']; // your ip address here
            $query = @unserialize(file_get_contents('http://ip-api.com/php/' . $ip));

            $whichbrowser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
            if ($whichbrowser->device->type == 'bot') {
                return;
            }
            $referrer = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']) : null;

            /* Detect extra details about the user */
            $query['browser_name'] = $whichbrowser->browser->name ?? null;
            $query['os_name'] = $whichbrowser->os->name ?? null;
            $query['browser_language'] = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
            $query['device_type'] = get_device_type($_SERVER['HTTP_USER_AGENT']);
            $query['referrer_host'] = !empty($referrer['host']);
            $query['referrer_path'] = !empty($referrer['path']);

            isset($query['timezone'])?date_default_timezone_set($query['timezone']):'';

            $json = json_encode($query);

            $login_detail = new LoginDetail();
            $login_detail->user_id = Auth::user()->id;
            $login_detail->ip = $ip;
            $login_detail->date = date('Y-m-d H:i:s');
            $login_detail->Details = $json;
            $login_detail->created_by = \Auth::user()->creatorId();
            $login_detail->save();

        }
        //end for user log

        if($user->type =='company' || $user->type =='super admin' || $user->type =='client')
        {
            return redirect()->intended(route('dashboard'));

        }
        else
        {
            return redirect()->intended(route('dashboard'));
        }

    }

    /**
     * Destroy an authenticated session.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function showLoginForm($lang = '')
    {
        if($lang == '')
        {
            $lang = Utility::getValByName('default_language');
        }

        $langList = Utility::languages()->toArray();
        $lang = array_key_exists($lang, $langList) ? $lang : 'en';

        \App::setLocale($lang);

        $settings = Utility::settings();
        $tenantId = $this->resolveTenantIdForLogin(request());
        if ($tenantId !== null) {
            request()->session()->put('login_tenant_id', $tenantId);
        }

        return view('auth.login', compact('lang','settings'));
    }

    private function resolveTenantIdForLogin(Request $request): ?int
    {
        $tenantIdHeader = (string) config('tenancy.header_tenant_id', 'X-Tenant-Id');
        $tenantSlugHeader = (string) config('tenancy.header_tenant_slug', 'X-Tenant-Slug');

        $tenantId = $request->header($tenantIdHeader) ?? $request->query('tenant_id');
        if (!empty($tenantId)) {
            return (int) $tenantId;
        }

        $tenantSlug = $request->header($tenantSlugHeader) ?? $request->query('tenant');
        if (!empty($tenantSlug)) {
            return (int) (Tenant::query()->where('slug', $tenantSlug)->value('id') ?? 0) ?: null;
        }

        $sessionTenantId = (int) $request->session()->get('login_tenant_id', 0);
        if ($sessionTenantId > 0) {
            return $sessionTenantId;
        }

        $host = strtolower((string) $request->getHost());
        if (!empty($host) && !$this->isCentralHost($host)) {
            return (int) (TenantDomain::query()->where('domain', $host)->value('tenant_id') ?? 0) ?: null;
        }

        return null;
    }

    private function isCentralHost(string $host): bool
    {
        $host = strtolower(trim($host));
        $centralHosts = (array) config('tenancy.central_hosts', ['localhost', '127.0.0.1']);

        return in_array($host, $centralHosts, true);
    }


}

//for user log
if (!function_exists('get_device_type')) {
    function get_device_type($user_agent)
    {
        $mobile_regex = '/(?:phone|windows\s+phone|ipod|blackberry|(?:android|bb\d+|meego|silk|googlebot) .+? mobile|palm|windows\s+ce|opera mini|avantgo|mobilesafari|docomo)/i';
        $tablet_regex = '/(?:ipad|playbook|(?:android|bb\d+|meego|silk)(?! .+? mobile))/i';
        if (preg_match_all($mobile_regex, $user_agent)) {
            return 'mobile';
        } else {
            if (preg_match_all($tablet_regex, $user_agent)) {
                return 'tablet';
            } else {
                return 'desktop';
            }
        }
    }
}
