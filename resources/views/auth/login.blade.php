<!doctype html>
<html lang="en">

<head>

    @php
        $website_nm = \App\Models\Utility::getWebsiteName();
        $website_img = \App\Models\Utility::websiteLogo();
        $default_img = \App\Models\Utility::defaultImage();
        // asset('public/build/assets/images/engage-logo.png');
    @endphp

    <meta charset="utf-8" />
    <title>Sign In | {{ $website_nm ?? '' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="{{ $website_nm ?? '' }}" name="description" />
    <meta content="" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ !empty($website_img) ? $website_img : $default_img }}">

    <!-- Layout config Js -->
    <script src="{{ asset('public/build/assets/js/layout.js') }}"></script>
    <!-- Bootstrap Css -->
    <link href="{{ asset('public/build/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('public/build/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('public/build/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="{{ asset('public/build/assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        :root {
            --brand-ink: #0f172a;
            --brand-muted: #64748b;
            --brand-primary: #2563eb;
            --brand-primary-dark: #0f766e;
            --brand-soft: #eff6ff;
            --brand-soft-alt: #ecfeff;
            --brand-line: #dbe4f0;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(820px 420px at -8% 8%, rgba(37, 99, 235, 0.14), transparent 60%),
                radial-gradient(760px 360px at 108% 92%, rgba(15, 118, 110, 0.12), transparent 60%),
                linear-gradient(180deg, #f8fbff 0%, #f2f7fb 100%);
        }

        .auth-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 24px;
        }

        .auth-shell {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
            border-radius: 26px;
            overflow: hidden;
            box-shadow: 0 28px 70px rgba(15, 23, 42, 0.12);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(234, 222, 216, 0.9);
        }

        .auth-left {
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.14), transparent 28%),
                radial-gradient(circle at bottom left, rgba(255, 255, 255, 0.1), transparent 24%),
                linear-gradient(155deg, #0f172a 0%, #0f766e 46%, #2563eb 100%);
            color: #eff6ff;
            padding: 42px 36px;
            height: 100%;
            position: relative;
        }

        .auth-left:before,
        .auth-left:after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.09);
        }

        .auth-left:before {
            width: 220px;
            height: 220px;
            right: -70px;
            top: -50px;
        }

        .auth-left:after {
            width: 180px;
            height: 180px;
            left: -50px;
            bottom: -60px;
        }

        .auth-right {
            padding: 36px;
            background: #ffffff;
        }

        .auth-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .auth-back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--brand-ink);
            font-weight: 700;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid var(--brand-line);
            background: #fffaf8;
            transition: .18s ease;
        }

        .auth-back-link:hover {
            color: var(--brand-primary-dark);
            border-color: #f7c9b2;
            background: #fff4ee;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: linear-gradient(135deg, #eff6ff, #ecfeff);
            border: 1px solid #dbeafe;
            color: #1d4ed8;
            font-size: 0.86rem;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .auth-title {
            color: var(--brand-ink);
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 6px;
        }

        .auth-subtitle {
            color: var(--brand-muted);
            font-size: 1rem;
            margin-bottom: 22px;
        }

        .auth-left h2 {
            font-size: 2rem;
        }

        .auth-logo {
            max-width: 260px;
            width: 100%;
            height: auto;
            filter: drop-shadow(0 10px 22px rgba(0, 0, 0, 0.16));
        }

        .auth-left p {
            font-size: 1.02rem;
        }

        .form-control {
            border-radius: 14px;
            padding-top: 0.7rem;
            padding-bottom: 0.7rem;
            font-size: 1rem;
            border-color: var(--brand-line);
            box-shadow: none;
        }

        .form-control:focus {
            border-color: rgba(37, 99, 235, 0.42);
            box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.12);
        }

        .form-label {
            font-size: 0.98rem;
            font-weight: 600;
        }

        .btn-login {
            border-radius: 14px;
            font-weight: 600;
            letter-spacing: .2px;
            font-size: 1.02rem;
            padding-top: 0.82rem;
            padding-bottom: 0.82rem;
            background: linear-gradient(135deg, var(--brand-primary-dark), var(--brand-primary));
            border: 0;
            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.22);
        }

        .btn-login:hover,
        .btn-login:focus {
            background: linear-gradient(135deg, #0d9488, #1d4ed8);
            box-shadow: 0 18px 34px rgba(37, 99, 235, 0.24);
        }

        .hint-card {
            border: 1px solid rgba(255, 255, 255, 0.25);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 12px 14px;
            backdrop-filter: blur(2px);
            font-size: 0.98rem;
        }

        .auth-points {
            display: grid;
            gap: 12px;
            margin-top: 24px;
        }

        .auth-point {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
        }

        .auth-point-icon {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            flex: 0 0 auto;
        }

        .auth-point-icon i {
            font-size: 1rem;
        }

        .auth-card {
            border: 1px solid var(--brand-line);
            border-radius: 20px;
            padding: 20px;
            background: linear-gradient(180deg, #ffffff, #f8fbff);
            margin-bottom: 24px;
        }

        .status-banner {
            border: 1px solid var(--brand-line);
            border-radius: 18px;
            padding: 0.95rem 1rem;
            margin-bottom: 1rem;
            background: linear-gradient(180deg, #ffffff, #f8fbff);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .status-banner .banner-label {
            display: block;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.3rem;
            opacity: 0.82;
        }

        .status-banner.status-warning {
            border-color: #fed7aa;
            background: linear-gradient(180deg, #fff7ed, #fffdf8);
            color: #9a3412;
        }

        .status-banner.status-danger {
            border-color: #fecaca;
            background: linear-gradient(180deg, #fef2f2, #fffafa);
            color: #b91c1c;
        }

        .support-link {
            color: #1d4ed8;
            font-weight: 600;
            text-decoration: none;
        }

        .support-link:hover {
            color: #0f766e;
        }

        .auth-footer-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 24px;
        }

        @media (max-width: 991.98px) {
            .auth-wrap {
                padding: 10px;
            }
            .auth-left {
                min-height: 220px;
            }
            .auth-right {
                padding: 24px 18px;
            }
            .auth-topbar,
            .auth-footer-links {
                flex-direction: column;
                align-items: flex-start;
            }
            .auth-title {
                font-size: 1.72rem;
            }
            .auth-subtitle {
                font-size: 0.98rem;
            }
        }
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
                            <p class="mb-0 opacity-75">Bring sales, quotation, follow-up, WhatsApp, and business operations into one professional CRM workspace.</p>
                            <div class="auth-points">
                                <div class="auth-point">
                                    <div class="auth-point-icon"><i class="ri-line-chart-line"></i></div>
                                    <div>
                                        <div class="fw-semibold">Clear business visibility</div>
                                        <div class="small opacity-75">Track leads, quotation progress, order movement, and customer follow-up from one login.</div>
                                    </div>
                                </div>
                                <div class="auth-point">
                                    <div class="auth-point-icon"><i class="ri-shield-check-line"></i></div>
                                    <div>
                                        <div class="fw-semibold">Secure tenant access</div>
                                        <div class="small opacity-75">Tenant users can sign in with the right business context and continue work without confusion.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hint-card">
                            <div class="small fw-semibold mb-1">Secure Access</div>
                            <div class="small opacity-75">
                                Use your business credentials to access your tenant dashboard.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="auth-right">
                        <div class="auth-topbar">
                            <div class="small text-muted">Business login portal</div>
                        </div>
                        <div class="eyebrow">
                            <i class="ri-lock-password-line"></i>
                            <span>Secure workspace sign in</span>
                        </div>
                        <h3 class="auth-title">Sign In</h3>
                        <p class="auth-subtitle">Welcome back. Enter your account details to continue.</p>

                        @if (session('status'))
                            <div class="status-banner status-warning">
                                <span class="banner-label">Account notice</span>
                                {{ session('status') }}
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="status-banner status-danger">
                                <span class="banner-label">Sign in error</span>
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="auth-card">
                            <div class="fw-semibold mb-1">Use your registered business account</div>
                            <div class="small text-muted">Enter the email and password linked to your CRM workspace. If your subscription was activated recently, use the same credentials shared during onboarding.</div>
                        </div>

                        <form method="POST" action="{{ route('login', array_filter([
                            'tenant' => request()->query('tenant'),
                            'tenant_id' => request()->query('tenant_id'),
                        ])) }}">
                            @csrf
                            @if (request()->filled('tenant'))
                                <input type="hidden" name="tenant" value="{{ request()->query('tenant') }}">
                            @endif
                            @if (request()->filled('tenant_id'))
                                <input type="hidden" name="tenant_id" value="{{ request()->query('tenant_id') }}">
                            @endif
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input
                                    type="text"
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    placeholder="name@company.com"
                                    value="{{ old('email') }}"
                                    required
                                    autocomplete="email"
                                    autofocus
                                >
                                @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="password">Password</label>
                                <div class="position-relative auth-pass-inputgroup">
                                    <input
                                        type="password"
                                        class="form-control pe-5 password-input @error('password') is-invalid @enderror"
                                        placeholder="Enter password"
                                        id="password"
                                        name="password"
                                        required
                                        autocomplete="current-password"
                                    >
                                    <button
                                        class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none"
                                        type="button"
                                        id="password-addon"
                                    >
                                        <i class="ri-eye-fill align-middle"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                                {{-- @if (Route::has('password.request'))
                                    <div class="text-end mt-2">
                                        <a href="{{ route('password.request') }}" class="support-link">Forgot Password?</a>
                                    </div>
                                @endif --}}
                            </div>

                            <div class="mt-4">
                                <button class="btn btn-login text-white w-100" type="submit">Sign In</button>
                            </div>
                        </form>

                        <div class="auth-footer-links">
                            <div class="small text-muted">Use your assigned workspace credentials to continue.</div>
                            <div class="text-muted small">
                                &copy; {{ date('Y') }} {{ $website_nm ?? '' }}. All rights reserved.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script src="{{ asset('public/build/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('public/build/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('public/build/assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('public/build/assets/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('public/build/assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
    <script src="{{ asset('public/build/assets/js/plugins.js') }}"></script>

    <!-- password-addon init -->
    <script src="{{ asset('public/build/assets/js/pages/password-addon.init.js') }}"></script>
</body>

</html>
