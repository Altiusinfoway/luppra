<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subscription Activation</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --brand-ink:#171515; --brand-muted:#6b6570; --brand-primary:#fb641b; --brand-primary-dark:#e85a16; --brand-purple:#842f89; --brand-purple-dark:#5a246f; --brand-line:#eaded8; }
        body { font-family:"Manrope",sans-serif; background:radial-gradient(680px 360px at 0% 0%, rgba(251,100,27,.16), transparent 55%), radial-gradient(620px 320px at 100% 0%, rgba(132,47,137,.10), transparent 55%), linear-gradient(180deg,#fffaf8 0%,#fbf6f4 100%); color:var(--brand-ink); }
        .shell, .card-soft { background:rgba(255,255,255,.96); border:1px solid var(--brand-line); border-radius:24px; box-shadow:0 18px 40px rgba(34,27,32,.07); }
        .hero-band { border-radius:24px; padding:1.25rem 1.4rem; background:linear-gradient(130deg,#221b20 0%, #5a246f 42%, #fb641b 100%); color:#fff7f3; box-shadow:0 20px 40px rgba(34,27,32,.14); }
        .status-pill { display:inline-flex; align-items:center; padding:.4rem .8rem; border-radius:999px; background:linear-gradient(135deg,#fff2ea,#f9eff9); color:var(--brand-primary-dark); border:1px solid #f1dde5; font-weight:700; font-size:.85rem; }
        .status-pill.failed { background:#fef2f2; color:#b91c1c; border-color:#fecaca; }
        .status-pill.processing { background:#fff7ed; color:#9a3412; border-color:#fed7aa; }
        .summary-label { color:var(--brand-muted); font-size:.86rem; }
        .summary-value { font-weight:700; }
        .timeline-step { border-radius:16px; padding:.95rem 1rem; border:1px solid #eee2dc; background:linear-gradient(180deg,#fffefd,#fff8f4); }
        .timeline-step + .timeline-step { margin-top:.9rem; }
        .timeline-step.active { border-color:#fdba74; background:#fff7ed; }
        .timeline-step.done { border-color:#f4bf9e; background:#fff3eb; }
        .timeline-step.failed { border-color:#fca5a5; background:#fef2f2; }
        .action-btn { border-radius:14px; font-weight:700; padding:.82rem 1rem; }
        .btn-brand { background:linear-gradient(90deg,var(--brand-primary-dark),var(--brand-primary)); border:0; color:#fff; box-shadow:0 12px 26px rgba(251,100,27,.25); }
        .btn-brand:hover, .btn-brand:focus { color:#fff; background:linear-gradient(90deg,#db5414,#ff742f); }
        .btn-soft { border:1px solid var(--brand-line); background:#fffaf8; color:var(--brand-ink); }
        .btn-soft:hover, .btn-soft:focus { background:#fff1ea; color:var(--brand-primary-dark); border-color:#f7c9b2; }
        .brand-link { color:var(--brand-primary-dark); font-weight:700; text-decoration:none; }
        .brand-link:hover { color:var(--brand-purple); }
    </style>
</head>
<body>
    @php
        $signupRecord = $signup ?? null;
        $meta = (array) optional($signupRecord)->meta;
        $status = (string) optional($signupRecord)->status;
        $isActivated = $status === 'activated' && !empty($meta['tenant_id']) && !empty($meta['user_id']);
        $isFailed = in_array($status, ['failed', 'payment_failed', 'cancelled', 'provisioning_failed'], true);
        $isProcessing = in_array($status, ['verifying', 'provisioning', 'paid'], true);
        $isTrial = (string) data_get($meta, 'checkout_mode') === 'trial';
        $stateLabel = $isActivated ? 'Workspace Ready' : ($isFailed ? 'Needs Attention' : 'Activation In Progress');
    @endphp

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="shell p-4 p-lg-5">
                    <div class="hero-band mb-4">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                            <div>
                                <div class="small text-uppercase fw-bold opacity-75">Subscription onboarding</div>
                                <div class="h3 fw-bold mb-1">Your CRM activation journey is now in progress.</div>
                                <div class="opacity-75">We keep the payment, provisioning, and workspace access steps clear so you know exactly what happens next.</div>
                            </div>
                            <a href="{{ route('website.home') }}" class="btn btn-light action-btn">Back to Home</a>
                        </div>
                    </div>
                    <div class="row g-4 align-items-start">
                        <div class="col-lg-7">
                            <span class="status-pill {{ $isActivated ? '' : ($isFailed ? 'failed' : 'processing') }}">{{ $stateLabel }}</span>
                            <h1 class="display-6 fw-bold mt-3 mb-3">
                                {{ $isActivated ? 'Your CRM workspace is ready to use.' : ($isFailed ? ($isTrial ? 'Your trial started, but activation needs support.' : 'Your payment was received, but activation needs support.') : 'We are finishing your subscription setup.') }}
                            </h1>
                            <p class="text-secondary mb-4">
                                {{ $isActivated
                                    ? 'Your workspace, access setup, and core onboarding steps are complete. You can now sign in and start setting up your team and business process.'
                                        : ($isFailed
                                            ? ($isTrial ? 'Your trial flow started, but the workspace setup needs a quick review from our side.' : 'Your payment flow completed, but the workspace setup needs a quick review from our side. In most cases this can be resolved without asking you to pay again.')
                                            : ($isTrial ? 'Your trial has started and the system is finishing tenant provisioning. Stay on this page and we will keep checking the latest activation status for you.' : 'Your payment has been received and the system is finishing verification and tenant provisioning. Stay on this page and we will keep checking the latest activation status for you.')) }}
                            </p>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="card-soft p-3 h-100">
                                        <div class="summary-label">Signup ID</div>
                                        <div class="summary-value">#{{ optional($signupRecord)->id ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="card-soft p-3 h-100">
                                        <div class="summary-label">Current Status</div>
                                        <div class="summary-value text-uppercase">{{ $status ?: 'unknown' }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="card-soft p-3 h-100">
                                        <div class="summary-label">Plan</div>
                                        <div class="summary-value">{{ data_get($meta, 'plan_name', optional($signupRecord)->plan_id ?: '-') }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="card-soft p-3 h-100">
                                        <div class="summary-label">Email</div>
                                        <div class="summary-value">{{ data_get($meta, 'login_email', optional($signupRecord)->email ?: '-') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-soft p-4 mt-4">
                                <h2 class="h5 fw-bold mb-3">Activation progress</h2>
                                <div class="timeline-step {{ in_array($status, ['verifying', 'provisioning', 'activated'], true) ? 'done' : ($isFailed ? 'failed' : 'active') }}">
                                    <div class="fw-bold">{{ $isTrial ? 'Trial started' : 'Payment captured' }}</div>
                                    <div class="small text-secondary">{{ $isTrial ? 'The payment gateway was bypassed and the trial activation was locked.' : 'Razorpay payment reached the verification and locking stage.' }}</div>
                                </div>
                                <div class="timeline-step {{ in_array($status, ['provisioning', 'activated'], true) ? ($isActivated ? 'done' : 'active') : ($status === 'provisioning_failed' ? 'failed' : '') }}">
                                    <div class="fw-bold">Tenant workspace provisioning</div>
                                    <div class="small text-secondary">Creating the tenant database, provisioning tables, permissions, masters, settings, and user sync.</div>
                                </div>
                                <div class="timeline-step {{ $isActivated ? 'done' : ($isFailed ? 'failed' : '') }}">
                                    <div class="fw-bold">Login access ready</div>
                                    <div class="small text-secondary">Your workspace URL, tenant assignment, and company-user login details are finalized here.</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="card-soft p-4">
                                <h2 class="h5 fw-bold mb-3">Next steps</h2>

                                @if($isActivated)
                                    <div class="alert alert-success">
                                        <div><strong>Login URL:</strong> <a class="brand-link" href="{{ $meta['login_url'] ?? route('login') }}">{{ $meta['login_url'] ?? route('login') }}</a></div>
                                        <div class="mt-2"><strong>Email:</strong> {{ $meta['login_email'] ?? optional($signupRecord)->email }}</div>
                                        <div class="mt-2"><strong>Temporary Password:</strong> {{ $meta['temp_password'] ?? 'Use Forgot Password' }}</div>
                                    </div>
                                    <div class="small text-secondary mb-3">Please change the password immediately after your first login.</div>
                                @elseif($isFailed)
                                    <div class="alert alert-warning">
                                        <strong>Activation note:</strong> {{ $meta['activation_error'] ?? 'Workspace setup could not be completed automatically.' }}
                                    </div>
                                    <div class="small text-secondary">Keep this signup ID available when contacting support so the team can finish activation faster.</div>
                                @else
                                    <div class="alert alert-info">
                                        {{ $isTrial ? 'Your trial is active and we are still checking the latest activation result for your workspace. This page will keep refreshing the status for you automatically.' : 'We are still checking the latest activation result for your workspace. This page will keep refreshing the status for you automatically.' }}
                                    </div>
                                    <div class="small text-secondary">{{ $isTrial ? 'There is no need to open the payment gateway while this status is still processing.' : 'There is no need to attempt another payment while this status is still processing.' }}</div>
                                @endif

                                <div class="d-grid gap-2 mt-4">
                                    <a href="{{ $meta['login_url'] ?? route('login') }}" class="btn btn-brand action-btn {{ $isActivated ? '' : 'disabled' }}">Login</a>
                                    <a href="{{ route('website.home') }}" class="btn btn-soft action-btn">Back to Website</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($signupRecord && !$isActivated && !$isFailed)
        <script>
            const statusUrl = `{{ route('website.checkout.status.poll') }}?id={{ $signupRecord->id }}`;

            const refreshStatus = async () => {
                try {
                    const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                    const payload = await response.json();
                    if (payload.state === 'activated' || payload.state === 'failed') {
                        window.location.reload();
                        return;
                    }
                } catch (error) {
                }

                setTimeout(refreshStatus, 3000);
            };

            setTimeout(refreshStatus, 2500);
        </script>
    @endif
</body>
</html>
