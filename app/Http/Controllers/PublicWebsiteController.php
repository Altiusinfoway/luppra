<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WebsiteSignup;
use App\Services\TenantOnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PublicWebsiteController extends Controller
{
    private const ACTIVE_CHECKOUT_WINDOW_MINUTES = 20;

    public function __construct(private TenantOnboardingService $tenantOnboardingService)
    {
    }

    private function denyIfNotSuperAdmin()
    {
        if (!auth()->check() || auth()->user()->type !== 'super admin') {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        return null;
    }

    public function home()
    {
        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $checkoutUser = auth()->user();
        $checkoutTenant = $this->resolveRenewalTenant();

        return view('public.home', compact('plans', 'checkoutUser', 'checkoutTenant'));
    }

    public function features()
    {
        return view('public.detail', $this->websiteDetailPageData('features'));
    }

    public function workflow()
    {
        return view('public.detail', $this->websiteDetailPageData('workflow'));
    }

    public function integrations()
    {
        return view('public.detail', $this->websiteDetailPageData('integrations'));
    }

    public function pricing()
    {
        $data = $this->websiteDetailPageData('pricing');
        $data['plans'] = Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('public.detail', $data);
    }

    public function saveDraft(Request $request)
    {
        $data = $request->validate([
            'draft_id' => 'nullable|integer|exists:website_signups,id',
            'plan_id' => 'nullable|integer|exists:plans,id',
            'name' => 'nullable|string|max:120',
            'email' => 'nullable|email|max:190',
            'phone' => 'nullable|string|max:30',
            'company_name' => 'nullable|string|max:190',
        ]);

        if (empty($data['email']) && empty($data['phone']) && empty($data['name']) && empty($data['company_name'])) {
            return response()->json(['message' => 'No draft data provided.'], 422);
        }

        $plan = !empty($data['plan_id'])
            ? Plan::query()->where('id', (int) $data['plan_id'])->where('is_active', true)->first()
            : null;

        $signup = !empty($data['draft_id'])
            ? WebsiteSignup::query()->where('id', (int) $data['draft_id'])->first()
            : null;

        if (!$signup) {
            $signup = WebsiteSignup::query()->create([
                'plan_id' => $plan?->id,
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? ('draft-' . Str::uuid() . '@draft.local'),
                'phone' => $data['phone'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'amount' => (float) ($plan?->price ?? 0),
                'status' => 'draft',
                'meta' => [
                    'draft_started_at' => now()->toDateTimeString(),
                    'plan_name' => $plan?->name,
                    'billing_cycle' => $plan?->billing_cycle,
                ],
            ]);
        } else {
            $signup->update([
                'plan_id' => $plan?->id ?? $signup->plan_id,
                'name' => $data['name'] ?? $signup->name,
                'email' => $data['email'] ?? $signup->email,
                'phone' => $data['phone'] ?? $signup->phone,
                'company_name' => $data['company_name'] ?? $signup->company_name,
                'amount' => (float) ($plan?->price ?? $signup->amount),
                'status' => in_array((string) $signup->status, ['paid', 'activated', 'verifying', 'provisioning'], true) ? $signup->status : 'draft',
                'meta' => array_merge((array) $signup->meta, [
                    'draft_updated_at' => now()->toDateTimeString(),
                    'plan_name' => $plan?->name ?? data_get($signup->meta, 'plan_name'),
                    'billing_cycle' => $plan?->billing_cycle ?? data_get($signup->meta, 'billing_cycle'),
                ]),
            ]);
        }

        return response()->json([
            'draft_id' => $signup->id,
            'message' => 'Draft saved.',
        ]);
    }

    public function createOrder(Request $request)
    {
        $data = $request->validate([
            'draft_id' => 'nullable|integer|exists:website_signups,id',
            'plan_id' => 'required|integer|exists:plans,id',
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190',
            'phone' => 'nullable|string|max:30',
            'company_name' => 'required|string|max:190',
        ]);

        $plan = Plan::query()->where('id', $data['plan_id'])->where('is_active', true)->first();
        if (!$plan) {
            return response()->json(['message' => 'Selected plan is not active.'], 422);
        }

        $renewalTenant = $this->resolveRenewalTenant();
        $renewalUser = $renewalTenant ? auth()->user() : null;
        $isRenewal = $renewalTenant && $renewalUser;

        if (!$isRenewal && $this->emailAlreadyRegistered($data['email'])) {
            return response()->json([
                'message' => 'This email is already registered. Please sign in with your existing account or use Forgot Password.',
            ], 422);
        }

        $amount = (float) $plan->price;
        $startsAsTrial = $this->tenantOnboardingService->planStartsAsTrial($plan);

        if ($startsAsTrial && $isRenewal && $this->tenantHasUsedFreeTrial($renewalTenant)) {
            return response()->json([
                'message' => 'Your free trial has already been used. Please choose a paid plan to continue.',
                'code' => 'trial_already_used',
            ], 422);
        }

        $signup = !empty($data['draft_id'])
            ? WebsiteSignup::query()->where('id', (int) $data['draft_id'])->first()
            : null;

        if ($signup) {
            $signup->update([
                'plan_id' => $plan->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'company_name' => $data['company_name'],
                'amount' => $startsAsTrial ? 0 : $amount,
                'status' => in_array((string) $signup->status, ['verifying', 'provisioning'], true) ? $signup->status : 'pending',
                'meta' => array_merge((array) $signup->meta, [
                    'plan_name' => $plan->name,
                    'billing_cycle' => $plan->billing_cycle,
                    'trial_days' => (int) $plan->trial_days,
                    'checkout_mode' => $startsAsTrial ? 'trial' : 'payment',
                    'subscription_flow' => $isRenewal ? 'renewal' : 'new_signup',
                    'tenant_id' => $isRenewal ? $renewalTenant->id : data_get($signup->meta, 'tenant_id'),
                    'tenant_slug' => $isRenewal ? $renewalTenant->slug : data_get($signup->meta, 'tenant_slug'),
                    'user_id' => $isRenewal ? $renewalUser->id : data_get($signup->meta, 'user_id'),
                    'submitted_at' => now()->toDateTimeString(),
                ]),
            ]);
        } else {
            $signup = WebsiteSignup::query()->create([
                'plan_id' => $plan->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'company_name' => $data['company_name'],
                'amount' => $startsAsTrial ? 0 : $amount,
                'status' => 'pending',
                'meta' => [
                    'plan_name' => $plan->name,
                    'billing_cycle' => $plan->billing_cycle,
                    'trial_days' => (int) $plan->trial_days,
                    'checkout_mode' => $startsAsTrial ? 'trial' : 'payment',
                    'subscription_flow' => $isRenewal ? 'renewal' : 'new_signup',
                    'tenant_id' => $isRenewal ? $renewalTenant->id : null,
                    'tenant_slug' => $isRenewal ? $renewalTenant->slug : null,
                    'user_id' => $isRenewal ? $renewalUser->id : null,
                    'submitted_at' => now()->toDateTimeString(),
                ],
            ]);
        }

        $status = (string) $signup->status;
        if (in_array($status, ['verifying', 'provisioning'], true)) {
            return response()->json([
                'message' => 'Your payment is already being verified and the workspace is being prepared.',
                'status_payload' => $this->buildStatusPayload($signup->fresh()),
            ], 409);
        }

        if ($status === 'activated') {
            return response()->json([
                'message' => 'This signup is already activated.',
                'status_payload' => $this->buildStatusPayload($signup->fresh()),
                'redirect_url' => route('website.thankyou', ['id' => $signup->id]),
            ], 409);
        }

        if ($startsAsTrial) {
            $signup->update([
                'status' => 'provisioning',
                'razorpay_order_id' => null,
                'razorpay_payment_id' => null,
                'razorpay_signature' => null,
                'meta' => array_merge((array) $signup->meta, [
                    'trial_started_at' => now()->toDateTimeString(),
                    'processing_stage' => 'provisioning_workspace',
                    'processing_message' => 'Your free trial is active. Creating your workspace now.',
                ]),
            ]);

            try {
                if ($isRenewal) {
                    $this->activateExistingTenantSubscription($signup->fresh(), $plan, $renewalTenant, $renewalUser);
                } else {
                    $this->tenantOnboardingService->activateWebsiteSignup($signup->fresh(), $plan);
                }
            } catch (\Throwable $e) {
                $signup->refresh();
                $signup->update([
                    'status' => 'provisioning_failed',
                    'meta' => array_merge((array) $signup->meta, [
                        'activation_error' => $e->getMessage(),
                        'activation_failed_at' => now()->toDateTimeString(),
                        'processing_stage' => 'failed',
                        'processing_message' => 'Trial started, but workspace setup needs attention. Our team can complete it quickly.',
                    ]),
                ]);

                return response()->json([
                    'message' => 'Trial started, but workspace activation needs attention.',
                    'trial_activation' => true,
                    'redirect_url' => route('website.thankyou', ['id' => $signup->id]),
                    'status_payload' => $this->buildStatusPayload($signup->fresh()),
                ], 500);
            }

            $signup->refresh();

            return response()->json([
                'message' => 'Free trial activated.',
                'trial_activation' => true,
                'redirect_url' => route('website.thankyou', ['id' => $signup->id]),
                'status_payload' => $this->buildStatusPayload($signup),
            ]);
        }

        if ($amount <= 0) {
            return response()->json(['message' => 'Invalid plan amount.'], 422);
        }

        $gateway = $this->getRazorpayGatewayConfig();
        $razorpayKey = (string) ($gateway['key_id'] ?? '');
        $razorpaySecret = (string) ($gateway['key_secret'] ?? '');
        $enabled = (bool) ($gateway['enabled'] ?? false);

        if (!$enabled) {
            return response()->json(['message' => 'Online payment is currently disabled by admin.'], 422);
        }

        if ($razorpayKey === '' || $razorpaySecret === '') {
            return response()->json(['message' => 'Payment gateway is not configured.'], 500);
        }

        if (
            $status === 'order_created'
            && !empty($signup->razorpay_order_id)
            && $signup->updated_at
            && $signup->updated_at->gt(now()->subMinutes(self::ACTIVE_CHECKOUT_WINDOW_MINUTES))
        ) {
            return response()->json([
                'key' => $razorpayKey,
                'order_id' => $signup->razorpay_order_id,
                'amount' => (int) round($amount * 100),
                'currency' => 'INR',
                'name' => config('app.name', 'CRM'),
                'description' => $plan->name . ' Plan',
                'signup_id' => $signup->id,
                'prefill' => [
                    'name' => $signup->name,
                    'email' => $signup->email,
                    'contact' => $signup->phone,
                ],
            ]);
        }

        $receipt = 'crm_' . $signup->id . '_' . Str::upper(Str::random(6));
        $orderResponse = Http::withBasicAuth($razorpayKey, $razorpaySecret)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => (int) round($amount * 100),
                'currency' => 'INR',
                'receipt' => $receipt,
                'notes' => [
                    'website_signup_id' => (string) $signup->id,
                    'company_name' => $signup->company_name,
                    'email' => $signup->email,
                    'subscription_flow' => $isRenewal ? 'renewal' : 'new_signup',
                    'tenant_id' => $isRenewal ? (string) $renewalTenant->id : '',
                ],
            ]);

        if (!$orderResponse->successful()) {
            $signup->update([
                'status' => 'failed',
                'meta' => array_merge((array) $signup->meta, [
                    'order_error' => $orderResponse->json(),
                ]),
            ]);

            return response()->json(['message' => 'Failed to create payment order.'], 502);
        }

        $order = $orderResponse->json();
        $signup->update([
            'status' => 'order_created',
            'razorpay_order_id' => $order['id'] ?? null,
            'meta' => array_merge((array) $signup->meta, [
                'receipt' => $receipt,
                'checkout_locked_at' => now()->toDateTimeString(),
            ]),
        ]);

        return response()->json([
            'key' => $razorpayKey,
            'order_id' => $order['id'] ?? null,
            'amount' => (int) round($amount * 100),
            'currency' => 'INR',
            'name' => config('app.name', 'CRM'),
            'description' => $plan->name . ' Plan',
            'signup_id' => $signup->id,
            'prefill' => [
                'name' => $signup->name,
                'email' => $signup->email,
                'contact' => $signup->phone,
            ],
        ]);
    }

    public function updateCheckoutStatus(Request $request)
    {
        $data = $request->validate([
            'signup_id' => 'required|integer|exists:website_signups,id',
            'status' => 'required|string|in:cancelled,payment_failed',
            'reason' => 'nullable|string|max:500',
            'error_code' => 'nullable|string|max:120',
            'error_description' => 'nullable|string|max:1000',
            'razorpay_order_id' => 'nullable|string|max:120',
            'razorpay_payment_id' => 'nullable|string|max:120',
        ]);

        $signup = WebsiteSignup::query()->findOrFail((int) $data['signup_id']);
        $currentStatus = (string) $signup->status;

        if (in_array($currentStatus, ['paid', 'activated', 'verifying', 'provisioning'], true)) {
            return response()->json(['message' => 'Checkout is already being processed, status not changed.']);
        }

        $event = [
            'at' => now()->toDateTimeString(),
            'status' => $data['status'],
            'reason' => $data['reason'] ?? null,
            'error_code' => $data['error_code'] ?? null,
            'error_description' => $data['error_description'] ?? null,
        ];

        $meta = (array) $signup->meta;
        $events = (array) ($meta['checkout_events'] ?? []);
        $events[] = $event;

        $signup->update([
            'status' => $data['status'],
            'razorpay_order_id' => $data['razorpay_order_id'] ?? $signup->razorpay_order_id,
            'razorpay_payment_id' => $data['razorpay_payment_id'] ?? $signup->razorpay_payment_id,
            'meta' => array_merge($meta, [
                'last_event' => $event,
                'checkout_events' => $events,
            ]),
        ]);

        return response()->json(['message' => 'Checkout status updated.']);
    }

    public function verifyPayment(Request $request)
    {
        $data = $request->validate([
            'signup_id' => 'required|integer|exists:website_signups,id',
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $signup = WebsiteSignup::query()->findOrFail($data['signup_id']);
        $plan = Plan::query()->where('id', $signup->plan_id)->first();
        if (!$plan) {
            return response()->json(['message' => 'Plan not found for signup.'], 422);
        }

        $meta = (array) $signup->meta;
        if (
            in_array((string) $signup->status, ['activated'], true)
            && !empty($meta['tenant_id'])
            && !empty($meta['user_id'])
            && !empty($meta['login_url'])
        ) {
            return response()->json([
                'message' => 'Payment already verified.',
                'redirect_url' => route('website.thankyou', ['id' => $signup->id]),
                'status_payload' => $this->buildStatusPayload($signup),
            ]);
        }

        if (in_array((string) $signup->status, ['verifying', 'provisioning'], true)) {
            return response()->json([
                'message' => 'Payment verification is already in progress.',
                'redirect_url' => route('website.thankyou', ['id' => $signup->id]),
                'status_payload' => $this->buildStatusPayload($signup),
            ]);
        }

        $gateway = $this->getRazorpayGatewayConfig();
        $secret = (string) ($gateway['key_secret'] ?? '');
        if ($secret === '') {
            return response()->json(['message' => 'Payment gateway secret missing.'], 500);
        }

        $payload = $data['razorpay_order_id'] . '|' . $data['razorpay_payment_id'];
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        if (!hash_equals($expectedSignature, $data['razorpay_signature'])) {
            $signup->update([
                'status' => 'failed',
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_order_id' => $data['razorpay_order_id'],
                'razorpay_signature' => $data['razorpay_signature'],
                'meta' => array_merge((array) $signup->meta, [
                    'verify' => 'signature_mismatch',
                    'processing_message' => 'Payment verification failed. Please contact support if money was debited.',
                ]),
            ]);

            return response()->json(['message' => 'Payment verification failed.'], 422);
        }

        $signup->update([
            'status' => 'verifying',
            'razorpay_payment_id' => $data['razorpay_payment_id'],
            'razorpay_order_id' => $data['razorpay_order_id'],
            'razorpay_signature' => $data['razorpay_signature'],
            'meta' => array_merge((array) $signup->meta, [
                'paid_at' => now()->toDateTimeString(),
                'processing_stage' => 'verifying_payment',
                'processing_message' => 'Payment verified. Creating your workspace now.',
            ]),
        ]);

        try {
            $signup->refresh();
            $signup->update([
                'status' => 'provisioning',
                'meta' => array_merge((array) $signup->meta, [
                    'processing_stage' => 'provisioning_workspace',
                    'processing_message' => $this->isRenewalSignup($signup)
                        ? 'Updating your subscription plan.'
                        : 'Setting up your tenant, permissions, and defaults.',
                ]),
            ]);

            if ($this->isRenewalSignup($signup)) {
                $tenant = Tenant::query()->find((int) data_get($signup->meta, 'tenant_id'));
                $user = User::query()->find((int) data_get($signup->meta, 'user_id')) ?: auth()->user();

                if (!$tenant || !$user) {
                    throw new \RuntimeException('Existing tenant or user could not be resolved for subscription renewal.');
                }

                $this->activateExistingTenantSubscription($signup, $plan, $tenant, $user);
            } else {
                $this->tenantOnboardingService->activateWebsiteSignup($signup, $plan);
            }
        } catch (\Throwable $e) {
            $signup->refresh();
            $signup->update([
                'status' => 'provisioning_failed',
                'meta' => array_merge((array) $signup->meta, [
                    'activation_error' => $e->getMessage(),
                    'activation_failed_at' => now()->toDateTimeString(),
                    'processing_stage' => 'failed',
                    'processing_message' => 'Payment succeeded but workspace setup needs attention. Our team can complete it quickly.',
                ]),
            ]);

            return response()->json([
                'message' => 'Payment received, but workspace activation needs attention.',
                'redirect_url' => route('website.thankyou', ['id' => $signup->id]),
                'status_payload' => $this->buildStatusPayload($signup->fresh()),
            ]);
        }

        $signup->refresh();

        return response()->json([
            'message' => 'Payment successful.',
            'redirect_url' => route('website.thankyou', ['id' => $signup->id]),
            'status_payload' => $this->buildStatusPayload($signup),
        ]);
    }

    public function checkoutStatus(Request $request)
    {
        $signupId = (int) $request->query('id', 0);
        abort_if($signupId <= 0, 404);

        $signup = WebsiteSignup::query()->findOrFail($signupId);

        return response()->json($this->buildStatusPayload($signup));
    }

    public function thankYou(Request $request)
    {
        $signup = WebsiteSignup::query()->find($request->query('id'));
        return view('public.thankyou', compact('signup'));
    }

    public function transactions(Request $request)
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        $status = (string) $request->query('status', '');
        $query = WebsiteSignup::query()->orderByDesc('id');

        if ($status !== '') {
            $query->where('status', $status);
        }

        $rows = $query->paginate(25)->withQueryString();

        $summary = [
            'total' => WebsiteSignup::query()->count(),
            'draft' => WebsiteSignup::query()->where('status', 'draft')->count(),
            'order_created' => WebsiteSignup::query()->where('status', 'order_created')->count(),
            'paid' => WebsiteSignup::query()->where('status', 'paid')->count(),
            'verifying' => WebsiteSignup::query()->where('status', 'verifying')->count(),
            'provisioning' => WebsiteSignup::query()->where('status', 'provisioning')->count(),
            'activated' => WebsiteSignup::query()->where('status', 'activated')->count(),
            'failed' => WebsiteSignup::query()->where('status', 'failed')->count(),
            'cancelled' => WebsiteSignup::query()->where('status', 'cancelled')->count(),
            'payment_failed' => WebsiteSignup::query()->where('status', 'payment_failed')->count(),
            'provisioning_failed' => WebsiteSignup::query()->where('status', 'provisioning_failed')->count(),
            'stale_submitted' => WebsiteSignup::query()
                ->where('status', 'order_created')
                ->where('updated_at', '<', now()->subHours(1))
                ->count(),
            'stale_draft' => WebsiteSignup::query()
                ->where('status', 'draft')
                ->where('updated_at', '<', now()->subHours(1))
                ->count(),
        ];

        return view('setting.razorpay_transactions', compact('rows', 'summary', 'status'));
    }

    private function websiteDetailPageData(string $page): array
    {
        $pages = [
            'features' => [
                'eyebrow' => 'EngageNet features',
                'title' => 'CRM modules that cover sales, accounts, communication, and teams.',
                'intro' => 'Explore how EngageNet connects daily customer work into one tenant-ready CRM workspace.',
                'image' => 'public/build/assets/images/feature-main.png',
                'summary' => [
                    'Lead capture and stage tracking',
                    'Quotation, order, invoice, and payment flow',
                    'WhatsApp chats, devices, and bulk messages',
                    'Attendance, leave, payroll, targets, and reports',
                ],
                'sections' => [
                    [
                        'title' => 'Sales CRM',
                        'copy' => 'Manage leads, sources, stages, owners, product requirements, customer follow-ups, calls, chat history, and activity logs from one place.',
                        'image' => 'public/build/assets/images/sales-crm.png',
                        'items' => ['Lead import', 'Follow-up calendar', 'Customer product history', 'Sales targets'],
                    ],
                    [
                        'title' => 'Quote to cash',
                        'copy' => 'Prepare quotations, convert them into orders, generate invoices, collect payments, and keep customer ledgers ready for account review.',
                        'image' => 'public/build/assets/images/quote-to-crash2.png',
                        'items' => ['Quotation builder', 'Order conversion', 'Invoice templates', 'Payment history'],
                    ],
                    [
                        'title' => 'Team operations',
                        'copy' => 'Give managers a practical view of employee attendance, leave, payroll, working hours, route activity, and target performance.',
                        'image' => 'public/build/assets/images/team operations.png',
                        'items' => ['Attendance', 'Payroll', 'Leave rules', 'Location activity'],
                    ],
                ],
            ],
            'workflow' => [
                'eyebrow' => 'EngageNet workflow',
                'title' => 'A clear operating flow from inquiry to collection.',
                'intro' => 'The workflow page shows how a team can move through customer work without losing context between departments.',
                'image' => 'public/build/assets/images/workflow-main.png',
                'summary' => [
                    'Capture inquiries and assign owners',
                    'Discuss needs through follow-ups and WhatsApp',
                    'Create quotes and convert confirmed orders',
                    'Track invoices, payments, and team output',
                ],
                'sections' => [
                    [
                        'title' => '1. Capture and qualify',
                        'copy' => 'Create leads manually or import them, attach products, assign users, and maintain a clear activity timeline.',
                        'image' => 'public/build/assets/images/capture.png',
                        'items' => ['Lead source', 'Stage owner', 'Product interest', 'Next follow-up'],
                    ],
                    [
                        'title' => '2. Quote and confirm',
                        'copy' => 'Use customer history and product pricing to prepare quotes, revise details, and move confirmed deals into orders.',
                        'image' => 'public/build/assets/images/quote-confirm.png',
                        'items' => ['Quote PDF', 'Discount control', 'GST details', 'Order records'],
                    ],
                    [
                        'title' => '3. Fulfill and measure',
                        'copy' => 'Generate invoices, track payments, review outstanding amounts, and measure employee and sales performance.',
                        'image' => 'public/build/assets/images/fulfill-measure.png',
                        'items' => ['Invoice output', 'Ledger view', 'Outstanding report', 'Sales analytics'],
                    ],
                ],
            ],
            'integrations' => [
                'eyebrow' => 'EngageNet integrations',
                'title' => 'Connected essentials for communication, payment, files, and reports.',
                'intro' => 'EngageNet focuses on practical integrations that reduce manual work in sales and customer operations.',
                'image' => 'public/build/assets/images/integration-main.png',
                'summary' => [
                    'WhatsApp device and chat support',
                    'Razorpay checkout for subscriptions',
                    'PDF invoices, quotes, and payment files',
                    'Excel import and business reporting',
                ],
                'sections' => [
                    [
                        'title' => 'WhatsApp communication',
                        'copy' => 'Connect devices, sync customer chats, send custom messages, and run bulk messaging from the CRM flow.',
                        'image' => 'public/build/assets/images/WhatsApp-communication.png',
                        'items' => ['Device QR', 'Chat history', 'Bulk message', 'Lead conversation'],
                    ],
                    [
                        'title' => 'Payments and activation',
                        'copy' => 'Use Razorpay for subscription checkout and automatically move successful customers into tenant workspace provisioning.',
                        'image' => 'public/build/assets/images/payments-activation.png',
                        'items' => ['Secure checkout', 'Payment verification', 'Tenant setup', 'Activation status'],
                    ],
                    [
                        'title' => 'Documents and imports',
                        'copy' => 'Import leads or products, export professional PDFs, and keep invoice templates consistent for every company workspace.',
                        'image' => 'public/build/assets/images/documents-imports.png',
                        'items' => ['Excel import', 'Quote PDFs', 'Invoice PDFs', 'Template preview'],
                    ],
                ],
            ],
            'pricing' => [
                'eyebrow' => 'EngageNet pricing',
                'title' => 'Choose the plan that matches your team size and CRM usage.',
                'intro' => 'Review active plans here, then continue to the homepage checkout to activate your workspace securely.',
                'image' => 'public/build/assets/images/pricing-main1.png',
                'summary' => [
                    'Plan-based user limits',
                    'WhatsApp message limits',
                    'Trial days where configured',
                    'Automatic tenant activation after checkout',
                ],
                'sections' => [
                    [
                        'title' => 'What every plan is designed for',
                        'copy' => 'Plans can control CRM modules, user limits, WhatsApp usage, billing cycle, and trial behavior from the admin settings.',
                        'image' => 'public/build/assets/images/plan-designed1.png',
                        'items' => ['Users', 'Modules', 'Messages', 'Billing cycle'],
                    ],
                    [
                        'title' => 'How activation works',
                        'copy' => 'After checkout, EngageNet verifies payment, creates or updates the tenant workspace, applies defaults, and prepares login access.',
                        'image' => 'public/build/assets/images/activation-works.png',
                        'items' => ['Select plan', 'Pay securely', 'Provision workspace', 'Start using CRM'],
                    ],
                ],
            ],
        ];

        return [
            'pageKey' => $page,
            'page' => $pages[$page] ?? $pages['features'],
            'plans' => collect(),
        ];
    }

    private function resolveRenewalTenant(): ?Tenant
    {
        if (!auth()->check()) {
            return null;
        }

        $user = auth()->user();
        if (!$user || $user->type === 'super admin') {
            return null;
        }

        if (app()->bound('currentTenant')) {
            $tenant = app('currentTenant');
            if ($tenant instanceof Tenant) {
                return $tenant;
            }
        }

        $tenantId = (int) ($user->tenant_id ?? 0);
        if ($tenantId <= 0) {
            return null;
        }

        return Tenant::query()->where('id', $tenantId)->where('is_active', true)->first();
    }

    private function isRenewalSignup(WebsiteSignup $signup): bool
    {
        return (string) data_get($signup->meta, 'subscription_flow') === 'renewal'
            && (int) data_get($signup->meta, 'tenant_id') > 0;
    }

    private function tenantHasUsedFreeTrial(?Tenant $tenant): bool
    {
        if (!$tenant) {
            return false;
        }

        return $tenant->subscriptions()
            ->where('status', 'trialing')
            ->where('amount', 0)
            ->exists();
    }

    private function activateExistingTenantSubscription(WebsiteSignup $signup, Plan $plan, Tenant $tenant, User $user): void
    {
        if ($this->tenantOnboardingService->planStartsAsTrial($plan)) {
            $this->tenantOnboardingService->upsertTrialSubscription(
                tenant: $tenant,
                plan: $plan,
                user: $user,
                notes: 'Subscription plan updated from expired trial',
            );
        } else {
            $this->tenantOnboardingService->upsertActiveSubscription(
                tenant: $tenant,
                plan: $plan,
                user: $user,
                amount: (float) $signup->amount,
                paymentRef: (string) ($signup->razorpay_payment_id ?: $signup->razorpay_order_id),
                notes: 'Subscription plan updated after payment',
            );
        }

        $user->forceFill([
            'plan' => $plan->id,
            'plan_expire_date' => $this->tenantOnboardingService->planStartsAsTrial($plan)
                ? now()->addDays((int) $plan->trial_days)->toDateString()
                : $this->tenantOnboardingService->computePlanEndDate($plan),
        ])->save();

        $signup->update([
            'status' => 'activated',
            'meta' => array_merge((array) $signup->meta, [
                'tenant_id' => $tenant->id,
                'tenant_slug' => $tenant->slug,
                'user_id' => $user->id,
                'login_email' => $user->email,
                'login_url' => route('login', ['tenant' => $tenant->slug]),
                'activated_at' => now()->toDateTimeString(),
                'processing_stage' => 'completed',
                'processing_message' => 'Your subscription plan has been updated.',
            ]),
        ]);
    }

    private function buildStatusPayload(WebsiteSignup $signup): array
    {
        $meta = (array) $signup->meta;
        $status = (string) $signup->status;
        $hasActivation = !empty($meta['tenant_id']) && !empty($meta['user_id']) && !empty($meta['login_url']);

        $state = match ($status) {
            'activated' => 'activated',
            'verifying' => 'verifying',
            'provisioning' => 'provisioning',
            'order_created' => 'awaiting_payment',
            'cancelled', 'payment_failed', 'failed', 'provisioning_failed' => 'failed',
            default => $hasActivation ? 'activated' : 'pending',
        };

        $message = $meta['processing_message'] ?? match ($state) {
            'activated' => 'Your workspace is ready. You can login now.',
            'verifying' => 'We are verifying your payment.',
            'provisioning' => 'Your tenant workspace is being prepared.',
            'awaiting_payment' => 'Waiting for payment completion.',
            'failed' => 'This checkout needs attention before it can continue.',
            default => 'Continue with plan selection and payment.',
        };

        return [
            'signup_id' => $signup->id,
            'status' => $status,
            'state' => $state,
            'message' => $message,
            'plan_name' => data_get($meta, 'plan_name'),
            'processing_stage' => data_get($meta, 'processing_stage'),
            'login_url' => data_get($meta, 'login_url'),
            'login_email' => data_get($meta, 'login_email', $signup->email),
            'temp_password' => data_get($meta, 'temp_password'),
            'tenant_slug' => data_get($meta, 'tenant_slug'),
            'activation_error' => data_get($meta, 'activation_error'),
            'thank_you_url' => route('website.thankyou', ['id' => $signup->id]),
        ];
    }

    private function getRazorpayGatewayConfig(): array
    {
        $config = [
            'enabled' => false,
            'key_id' => (string) env('RAZORPAY_KEY_ID', ''),
            'key_secret' => (string) env('RAZORPAY_KEY_SECRET', ''),
        ];

        $settingNames = ['razorpay_enabled', 'razorpay_key_id', 'razorpay_key_secret'];
        $preferredCreatorIds = User::on('landlord')
            ->where('type', 'super admin')
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        if (empty($preferredCreatorIds)) {
            $preferredCreatorIds = User::on('landlord')
                ->where('type', 'company')
                ->orderBy('id')
                ->limit(1)
                ->pluck('id')
                ->map(static fn ($id) => (int) $id)
                ->all();
        }

        $settingRows = DB::connection('landlord')
            ->table('settings')
            ->whereIn('name', $settingNames)
            ->orderByDesc('updated_at')
            ->get(['name', 'value', 'created_by', 'updated_at']);

        if ($settingRows->isNotEmpty()) {
            $grouped = $settingRows
                ->groupBy(static fn ($row) => (int) ($row->created_by ?? 0))
                ->map(function ($rows, $creatorId) use ($preferredCreatorIds) {
                    $latestByName = [];
                    $latestUpdatedAt = null;

                    foreach ($rows as $row) {
                        if (!array_key_exists($row->name, $latestByName)) {
                            $latestByName[$row->name] = (string) $row->value;
                        }
                        $updatedAt = (string) ($row->updated_at ?? '');
                        if ($latestUpdatedAt === null || $updatedAt > $latestUpdatedAt) {
                            $latestUpdatedAt = $updatedAt;
                        }
                    }

                    return [
                        'creator_id' => (int) $creatorId,
                        'is_preferred' => in_array((int) $creatorId, $preferredCreatorIds, true),
                        'updated_at' => $latestUpdatedAt,
                        'enabled' => (($latestByName['razorpay_enabled'] ?? '0') === '1'),
                        'key_id' => (string) ($latestByName['razorpay_key_id'] ?? ''),
                        'key_secret' => (string) ($latestByName['razorpay_key_secret'] ?? ''),
                    ];
                })
                ->values()
                ->sort(function (array $a, array $b) {
                    $aComplete = $a['enabled'] && $a['key_id'] !== '' && $a['key_secret'] !== '';
                    $bComplete = $b['enabled'] && $b['key_id'] !== '' && $b['key_secret'] !== '';

                    if ($aComplete !== $bComplete) {
                        return $aComplete ? -1 : 1;
                    }

                    if ($a['is_preferred'] !== $b['is_preferred']) {
                        return $a['is_preferred'] ? -1 : 1;
                    }

                    return strcmp((string) $b['updated_at'], (string) $a['updated_at']);
                })
                ->values();

            $selected = $grouped->first();
            if ($selected) {
                $config['enabled'] = (bool) $selected['enabled'];
                $config['key_id'] = (string) $selected['key_id'];
                $config['key_secret'] = (string) $selected['key_secret'];
            }
        }

        $config['enabled'] = $config['enabled'] && $config['key_id'] !== '' && $config['key_secret'] !== '';

        return $config;
    }

    private function emailAlreadyRegistered(string $email): bool
    {
        $email = trim($email);
        if ($email === '') {
            return false;
        }

        return User::query()
            ->where('email', $email)
            ->exists();
    }
  
  	
}
