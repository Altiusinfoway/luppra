<!doctype html>
<html lang="en">
<head>
    @php
        $website_nm = \App\Models\Utility::getWebsiteName();
        $website_img = asset('public/build/assets/images/engage-logo.png');
        $default_img = \App\Models\Utility::defaultImage();
    @endphp
    <meta charset="utf-8" />
    <title>Verify Email | {{ $website_nm ?? '' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ !empty($website_img) ? $website_img : $default_img }}">
    <script src="{{ asset('public/build/assets/js/layout.js') }}"></script>
    <link href="{{ asset('public/build/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('public/build/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('public/build/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('public/build/assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        :root { --brand-ink:#0f172a; --brand-muted:#64748b; --brand-primary:#2563eb; --brand-primary-dark:#0f766e; --brand-line:#dbe4f0; }
        body { min-height:100vh; background:radial-gradient(820px 420px at -8% 8%, rgba(37,99,235,.14), transparent 60%), radial-gradient(760px 360px at 108% 92%, rgba(15,118,110,.12), transparent 60%), linear-gradient(180deg,#f8fbff 0%,#f2f7fb 100%); }
        .auth-wrap { min-height:100vh; display:flex; align-items:center; padding:24px; }
        .auth-shell { width:100%; max-width:1120px; margin:0 auto; border-radius:26px; overflow:hidden; box-shadow:0 28px 70px rgba(15,23,42,.12); background:rgba(255,255,255,.95); border:1px solid rgba(219,228,240,.92); }
        .auth-left { background:radial-gradient(circle at top right, rgba(255,255,255,.14), transparent 28%), radial-gradient(circle at bottom left, rgba(255,255,255,.1), transparent 24%), linear-gradient(155deg,#0f172a 0%,#0f766e 46%,#2563eb 100%); color:#eff6ff; padding:42px 36px; position:relative; height:100%; }
        .auth-left:before, .auth-left:after { content:""; position:absolute; border-radius:999px; background:rgba(255,255,255,.09); }
        .auth-left:before { width:220px; height:220px; right:-70px; top:-50px; }
        .auth-left:after { width:180px; height:180px; left:-50px; bottom:-60px; }
        .auth-right { padding:36px; background:#fff; }
        .auth-topbar, .auth-footer-links { display:flex; justify-content:space-between; align-items:center; gap:12px; }
        .auth-topbar { margin-bottom:24px; }
        .auth-footer-links { margin-top:24px; flex-wrap:wrap; }
        .auth-back-link { display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:var(--brand-ink); font-weight:700; padding:10px 14px; border-radius:999px; border:1px solid var(--brand-line); background:#fffaf8; }
        .auth-back-link:hover { color:#1d4ed8; border-color:#bfdbfe; background:#eff6ff; }
        .eyebrow { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:linear-gradient(135deg,#eff6ff,#ecfeff); border:1px solid #dbeafe; color:#1d4ed8; font-size:.86rem; font-weight:700; margin-bottom:16px; }
        .auth-title { color:var(--brand-ink); font-weight:700; font-size:2rem; margin-bottom:6px; }
        .auth-subtitle { color:var(--brand-muted); font-size:1rem; margin-bottom:22px; }
        .auth-logo { max-width:260px; width:100%; height:auto; filter:drop-shadow(0 10px 22px rgba(0,0,0,.16)); }
        .auth-point { display:flex; gap:12px; align-items:flex-start; padding:14px 16px; border-radius:16px; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.14); }
        .auth-point + .auth-point { margin-top:12px; }
        .auth-point-icon { width:36px; height:36px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,.12); color:#fff; flex:0 0 auto; }
        .auth-card { border:1px solid var(--brand-line); border-radius:20px; padding:20px; background:linear-gradient(180deg,#ffffff,#f8fbff); margin-bottom:24px; }
        .hint-card { border:1px solid rgba(255,255,255,.25); background:rgba(255,255,255,.1); border-radius:12px; padding:12px 14px; backdrop-filter:blur(2px); font-size:.98rem; }
        .status-banner { border:1px solid var(--brand-line); border-radius:18px; padding:.95rem 1rem; margin-bottom:1rem; background:linear-gradient(180deg,#ffffff,#f8fbff); box-shadow:0 12px 28px rgba(15,23,42,.05); }
        .status-banner .banner-label { display:block; font-size:.76rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; margin-bottom:.3rem; opacity:.82; }
        .status-banner.status-success { border-color:#bbf7d0; background:linear-gradient(180deg,#ecfdf3,#f7fffb); color:#067647; }
        .btn-brand { border-radius:14px; font-weight:600; font-size:1.02rem; padding:.82rem 1rem; background:linear-gradient(135deg,var(--brand-primary-dark),var(--brand-primary)); border:0; color:#fff; box-shadow:0 14px 28px rgba(37,99,235,.22); }
        .btn-brand:hover, .btn-brand:focus { color:#fff; background:linear-gradient(135deg,#0d9488,#1d4ed8); }
        .support-link { color:#1d4ed8; font-weight:600; text-decoration:none; }
        .support-link:hover { color:#0f766e; }
        @media (max-width:991.98px) { .auth-wrap { padding:10px; } .auth-right { padding:24px 18px; } .auth-topbar, .auth-footer-links { flex-direction:column; align-items:flex-start; } .auth-left { min-height:220px; } }
    </style>
</head>
<body>
    <div class="auth-wrap">
        <div class="auth-shell">
            <div class="row g-0">
                <div class="col-lg-5">
                    <div class="auth-left d-flex flex-column justify-content-between">
                        <div>
                            <img src="{{ $website_img }}" alt="Luppra" class="auth-logo mb-4">
                            <p class="mb-4 opacity-75">Verify your email address so your account can continue with the correct business and tenant access.</p>
                            <div class="auth-point">
                                <div class="auth-point-icon"><i class="ri-mail-check-line"></i></div>
                                <div><div class="fw-semibold">Check your inbox</div><div class="small opacity-75">Open the verification email and click the link to confirm ownership of your business account.</div></div>
                            </div>
                            <div class="auth-point">
                                <div class="auth-point-icon"><i class="ri-refresh-line"></i></div>
                                <div><div class="fw-semibold">Need another link?</div><div class="small opacity-75">You can request a fresh verification email from this page if the earlier message has expired.</div></div>
                            </div>
                        </div>
                        <div class="hint-card">
                            <div class="small fw-semibold mb-1">Email verification</div>
                            <div class="small opacity-75">This step helps protect your account and keeps activation and login details tied to the right email identity.</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="auth-right">
                        <div class="auth-topbar">
                            <a href="{{ route('login') }}" class="auth-back-link"><i class="ri-arrow-left-line"></i><span>Back to Login</span></a>
                            <div class="small text-muted">Email verification</div>
                        </div>
                        <div class="eyebrow"><i class="ri-mail-line"></i><span>Account verification</span></div>
                        <h3 class="auth-title">Verify your email address</h3>
                        <p class="auth-subtitle">Before proceeding, please check your email for a verification link and complete the confirmation step.</p>

                        @if (session('resent'))
                            <div class="status-banner status-success">
                                <span class="banner-label">Verification sent</span>
                                A fresh verification link has been sent to your email address.
                            </div>
                        @endif

                        <div class="auth-card">
                            <div class="fw-semibold mb-1">Almost done</div>
                            <div class="small text-muted">If you did not receive the email, you can request another verification link below.</div>
                        </div>

                        <form class="m-0" method="POST" action="{{ route('verification.resend') }}">
                            @csrf
                            <button type="submit" class="btn btn-brand w-100">Send Another Verification Link</button>
                        </form>

                        <div class="auth-footer-links">
                            <div class="small text-muted">Already verified? <a href="{{ route('login') }}" class="support-link">Return to login</a></div>
                            <div class="text-muted small">&copy; {{ date('Y') }} {{ $website_nm ?? '' }}. All rights reserved.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('public/build/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
