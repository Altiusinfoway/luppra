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
        :root { --brand-ink:#171515; --brand-muted:#6b6570; --brand-primary:#fb641b; --brand-primary-dark:#e85a16; --brand-purple:#842f89; --brand-line:#eaded8; }
        body { min-height:100vh; background:radial-gradient(800px 400px at -10% 10%, rgba(251,100,27,.16), transparent 60%), radial-gradient(700px 360px at 110% 90%, rgba(132,47,137,.12), transparent 60%), linear-gradient(180deg,#fffaf8 0%,#fbf6f4 100%); }
        .auth-wrap { min-height:100vh; display:flex; align-items:center; padding:24px; }
        .auth-shell { width:100%; max-width:1120px; margin:0 auto; border-radius:26px; overflow:hidden; box-shadow:0 24px 60px rgba(34,27,32,.14); background:#fff; border:1px solid rgba(234,222,216,.9); }
        .auth-left { background:linear-gradient(145deg,#221b20 0%,#5a246f 50%,#fb641b 100%); color:#fff7f3; padding:42px 36px; position:relative; height:100%; }
        .auth-left:before, .auth-left:after { content:""; position:absolute; border-radius:999px; background:rgba(255,255,255,.09); }
        .auth-left:before { width:220px; height:220px; right:-70px; top:-50px; }
        .auth-left:after { width:180px; height:180px; left:-50px; bottom:-60px; }
        .auth-right { padding:36px; background:#fff; }
        .auth-topbar, .auth-footer-links { display:flex; justify-content:space-between; align-items:center; gap:12px; }
        .auth-topbar { margin-bottom:24px; }
        .auth-footer-links { margin-top:24px; flex-wrap:wrap; }
        .auth-back-link { display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:var(--brand-ink); font-weight:700; padding:10px 14px; border-radius:999px; border:1px solid var(--brand-line); background:#fffaf8; }
        .auth-back-link:hover { color:var(--brand-primary-dark); border-color:#f7c9b2; background:#fff4ee; }
        .eyebrow { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:linear-gradient(135deg,#fff2ea,#f9eff9); border:1px solid #f1dde5; color:var(--brand-primary-dark); font-size:.86rem; font-weight:700; margin-bottom:16px; }
        .auth-title { color:var(--brand-ink); font-weight:700; font-size:2rem; margin-bottom:6px; }
        .auth-subtitle { color:var(--brand-muted); font-size:1rem; margin-bottom:22px; }
        .auth-logo { max-width:260px; width:100%; height:auto; filter:drop-shadow(0 10px 22px rgba(0,0,0,.16)); }
        .auth-point { display:flex; gap:12px; align-items:flex-start; padding:14px 16px; border-radius:16px; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.14); }
        .auth-point + .auth-point { margin-top:12px; }
        .auth-point-icon { width:36px; height:36px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,.12); color:#fff; flex:0 0 auto; }
        .auth-card { border:1px solid var(--brand-line); border-radius:20px; padding:20px; background:linear-gradient(180deg,#fffefd,#fff8f4); margin-bottom:24px; }
        .hint-card { border:1px solid rgba(255,255,255,.25); background:rgba(255,255,255,.1); border-radius:12px; padding:12px 14px; backdrop-filter:blur(2px); font-size:.98rem; }
        .btn-brand { border-radius:14px; font-weight:600; font-size:1.02rem; padding:.82rem 1rem; background:linear-gradient(90deg,var(--brand-primary-dark),var(--brand-primary)); border:0; color:#fff; box-shadow:0 12px 26px rgba(251,100,27,.28); }
        .btn-brand:hover, .btn-brand:focus { color:#fff; background:linear-gradient(90deg,#db5414,#ff742f); }
        .support-link { color:var(--brand-primary-dark); font-weight:600; text-decoration:none; }
        .support-link:hover { color:var(--brand-purple); }
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
                            <img src="{{ $website_img }}" alt="Engage Net" class="auth-logo mb-4">
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
                            <a href="{{ url('/') }}" class="auth-back-link"><i class="ri-arrow-left-line"></i><span>Back to Home</span></a>
                            <div class="small text-muted">Email verification</div>
                        </div>
                        <div class="eyebrow"><i class="ri-mail-line"></i><span>Account verification</span></div>
                        <h3 class="auth-title">Verify your email address</h3>
                        <p class="auth-subtitle">Before proceeding, please check your email for a verification link and complete the confirmation step.</p>

                        @if (session('resent'))
                            <div class="alert alert-success py-2">A fresh verification link has been sent to your email address.</div>
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
