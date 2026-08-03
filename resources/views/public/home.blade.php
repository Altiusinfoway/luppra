<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} CRM</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --ink:#101828; --muted:#667085; --brand:#fb641b; --brand2:#e85a16; --brand-soft:#fff0e7; --brand-soft-2:#fff7f2; --line:#e7e9f0; --bg:#f7f9fc; --ok:#067647; --warn:#9a3412; --danger:#b91c1c; --navy:#121826; --teal:#08a88a; --blue:#2f6df6; --violet:#6f42c1; }
        * { font-family:"Manrope",sans-serif; }
        body { color:var(--ink); background:linear-gradient(180deg,#fff3e9 0%,#fffaf4 28%,#f2ecfb 58%,#fff7ef 100%); overflow-x:hidden; }
        body.processing-active { overflow:hidden; }
        .top-strip { background:linear-gradient(90deg,#fb641b 0%,#842f89 55%,#4b1d57 100%); color:#fff; font-size:.85rem; overflow:hidden; box-shadow:0 10px 30px rgba(251,100,27,.18); }
        .top-marquee { display:flex; width:max-content; gap:2rem; white-space:nowrap; animation:marquee 24s linear infinite; }
        .top-marquee span { display:inline-flex; align-items:center; gap:.55rem; font-weight:800; }
        .top-marquee span:before { content:""; width:.45rem; height:.45rem; border-radius:50%; background:#fb641b; display:inline-block; }
        .site-nav { background:rgba(255,255,255,.94); backdrop-filter:blur(14px); border-bottom:1px solid var(--line); box-shadow:0 10px 30px rgba(16,24,40,.04); }
        .nav-shell { min-height:70px; }
        .site-link { color:#344054; font-size:.94rem; font-weight:700; text-decoration:none; padding:.55rem .7rem; border-radius:999px; transition:.18s ease; }
        .site-link:hover { color:var(--brand2); }
        .hero-shell { position:relative; padding:4.4rem 0 3.8rem; overflow:hidden; background:radial-gradient(720px 380px at 15% 2%,rgba(251,100,27,.24),transparent 60%),radial-gradient(720px 420px at 86% 6%,rgba(132,47,137,.20),transparent 58%),linear-gradient(180deg,#fff3e7 0%,#fffaf4 100%); border-bottom:1px solid #f1d8cc; }
        .hero-shell:before { content:""; position:absolute; inset:0; background:linear-gradient(135deg,rgba(251,100,27,.08),rgba(132,47,137,.07)); pointer-events:none; }
        .hero-shell > .container { position:relative; }
        .hero-banner-card { background:#fff; border:1px solid rgba(251,100,27,.18); border-radius:8px; overflow:hidden; box-shadow:0 28px 62px rgba(16,24,40,.14); position:relative; max-width:1120px; margin:0 auto; }
        .hero-banner-card:after { content:""; position:absolute; inset:auto 8% 0 8%; height:5px; background:linear-gradient(90deg,#fb641b,#842f89); border-radius:999px 999px 0 0; }
        .hero-banner-img { width:100%; aspect-ratio:16/9; object-fit:cover; object-position:center; display:block; }
        .hero-title { font-size:clamp(2.55rem,5.4vw,4.75rem); line-height:.98; max-width:760px; margin-left:auto; margin-right:auto; }
        .hero-sub { color:var(--muted); font-size:1.08rem; max-width:760px; margin-left:auto; margin-right:auto; }
        .hero-actions { align-items:center; }
        .hero-checks { display:flex; flex-wrap:wrap; gap:.65rem; margin-top:1.25rem; }
        .hero-check { display:inline-flex; align-items:center; gap:.45rem; padding:.48rem .72rem; border:1px solid #ffe0cf; border-radius:999px; background:rgba(255,255,255,.78); color:#344054; font-size:.84rem; font-weight:800; box-shadow:0 10px 24px rgba(251,100,27,.07); }
        .hero-check:before { content:""; width:.48rem; height:.48rem; border-radius:50%; background:#08a88a; display:inline-block; }
        .badge-soft { background:#fff7f2; color:var(--brand2); border:1px solid #ffd2bf; box-shadow:0 10px 24px rgba(251,100,27,.08); }
        .btn-brand { background:linear-gradient(135deg,var(--brand2),var(--brand)); border:0; color:#fff; font-weight:800; box-shadow:0 14px 36px rgba(251,100,27,.24); }
        .btn-brand:hover, .btn-brand:focus { color:#fff; background:linear-gradient(135deg,#de5514,#ff742f); box-shadow:0 18px 40px rgba(251,100,27,.28); }
        .btn-outline-dark { border-color:#222938; color:#222938; font-weight:800; }
        .btn-outline-dark:hover, .btn-outline-dark:focus { background:#222938; border-color:#222938; color:#fff; }
        .btn-dark { background:#101828; border-color:#101828; font-weight:800; }
        .btn-dark:hover, .btn-dark:focus { background:#050b18; border-color:#050b18; }
        .panel, .feature-card, .pricing-card, .checkout-panel, .trust-card, .proof-card, .workflow-card, .integration-card { background:linear-gradient(180deg,rgba(255,248,242,.98),rgba(255,255,255,.96)); border:1px solid rgba(16,24,40,.18); border-radius:8px; box-shadow:0 18px 42px rgba(251,100,27,.10); }
        .mini-kpi { border:1px solid rgba(16,24,40,.18); border-radius:8px; background:linear-gradient(180deg,#fff7f1,#fff); padding:1rem; box-shadow:0 12px 28px rgba(251,100,27,.08), inset 0 1px 0 rgba(255,255,255,.72); }
        .mini-kpi strong, .hero-metric-value { letter-spacing:0; }
        .hero-kpi-row { margin-top:1.6rem; }
        .hero-kpi { border:1px solid rgba(251,100,27,.16); background:linear-gradient(180deg,rgba(255,255,255,.95),rgba(255,247,242,.92)); }
        .section-title { font-size:clamp(1.7rem,3vw,2.45rem); }
        .section-eyebrow { display:inline-flex; align-items:center; gap:.45rem; color:var(--brand2); font-weight:800; font-size:.82rem; text-transform:uppercase; margin-bottom:.55rem; }
        .section-eyebrow:before { content:""; width:1.2rem; height:2px; background:var(--brand); display:inline-block; }
        .muted { color:var(--muted); }
        .feature-card, .workflow-card, .integration-card { height:100%; padding:1.35rem; transition:.18s ease; }
        .feature-card { text-align:center; }
        .feature-card:hover, .workflow-card:hover, .integration-card:hover, .proof-card:hover { transform:translateY(-3px); box-shadow:0 22px 46px rgba(16,24,40,.10); }
        .feature-icon { width:4.6rem; height:4.6rem; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#fb641b,#ff9a4d); color:#fff; box-shadow:0 14px 30px rgba(251,100,27,.24); margin-left:auto; margin-right:auto; }
        .feature-icon.teal { background:linear-gradient(135deg,#08a88a,#33d3b8); color:#fff; box-shadow:0 14px 30px rgba(8,168,138,.22); }
        .feature-icon.blue { background:linear-gradient(135deg,#2f6df6,#65a5ff); color:#fff; box-shadow:0 14px 30px rgba(47,109,246,.22); }
        .feature-icon.violet { background:linear-gradient(135deg,#842f89,#b45ac0); color:#fff; box-shadow:0 14px 30px rgba(132,47,137,.22); }
        .feature-icon svg { width:2.25rem; height:2.25rem; stroke:currentColor; fill:none; stroke-width:2.35; stroke-linecap:round; stroke-linejoin:round; }
        .workflow-card { text-align:center; }
        .workflow-icon { width:4.2rem; height:4.2rem; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; margin:0 auto 1rem; background:linear-gradient(135deg,#fb641b,#842f89); color:#fff; box-shadow:0 14px 30px rgba(132,47,137,.22); }
        .workflow-icon svg { width:2.1rem; height:2.1rem; stroke:currentColor; fill:none; stroke-width:2.35; stroke-linecap:round; stroke-linejoin:round; }
        .feature-copy { min-height:72px; }
        .pricing-card { height:100%; padding:1.4rem; transition:.18s ease; cursor:pointer; }
        .pricing-card:hover { transform:translateY(-4px); box-shadow:0 22px 40px rgba(16,24,40,.10); border-color:rgba(16,24,40,.30); }
        .pricing-card.active { border-color:rgba(251,100,27,.62); box-shadow:0 22px 48px rgba(251,100,27,.18); background:linear-gradient(180deg,rgba(255,235,222,.98),#fff8f2); }
        .pill { display:inline-flex; align-items:center; padding:.38rem .7rem; border-radius:999px; background:linear-gradient(135deg,#fff0e7,#f4efff); color:#842f89; font-size:.8rem; font-weight:800; border:1px solid rgba(16,24,40,.18); box-shadow:0 8px 18px rgba(251,100,27,.10); }
        .price-tag { font-size:clamp(1.9rem,4vw,2.5rem); line-height:1; letter-spacing:0; font-weight:800; }
        .pricing-list { padding-left:1.1rem; margin-bottom:0; color:var(--muted); }
        .pricing-list li + li { margin-top:.55rem; }
        .checkout-wrap { position:sticky; top:92px; }
        .checkout-panel { padding:1.5rem; }
        .summary-card { border:1px solid #ffd9c7; border-radius:8px; background:linear-gradient(180deg,#fff4ee,#fff); padding:1rem; box-shadow:inset 0 1px 0 rgba(255,255,255,.75); }
        .summary-row { display:flex; justify-content:space-between; gap:1rem; }
        .summary-row + .summary-row { margin-top:.8rem; }
        .summary-amount { font-size:1.6rem; font-weight:800; letter-spacing:0; }
        .step { display:flex; gap:.8rem; align-items:flex-start; }
        .step + .step { margin-top:.9rem; }
        .step-badge { width:2rem; height:2rem; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:#ffe5d8; color:var(--brand2); font-weight:800; flex:0 0 auto; }
        .trust-card { padding:1rem; }
        .trust-line + .trust-line { margin-top:.75rem; }
        .status-banner { border-radius:8px; padding:1rem 1.1rem; border:1px solid #ffd9c7; background:#fff4ee; color:#9a3f18; box-shadow:0 12px 24px rgba(251,100,27,.08); }
        .status-banner.is-warning { border-color:#fed7aa; background:#fff7ed; color:var(--warn); }
        .status-banner.is-danger { border-color:#fecaca; background:#fef2f2; color:var(--danger); }
        .status-banner .banner-label { display:block; font-size:.76rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; margin-bottom:.3rem; opacity:.82; }
        .hero-band { border-radius:8px; background:linear-gradient(130deg,#101828 0%, #193a5e 48%, #fb641b 100%); color:#fff7f3; box-shadow:0 30px 60px rgba(16,24,40,.16); }
        .proof-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1rem; }
        .proof-card { padding:1.1rem; transition:.18s ease; }
        .proof-card .small { color:var(--muted); }
        .brand-logo { height:42px; width:auto; display:block; }
        .logo-cloud { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.85rem; justify-content:center; }
        .logo-tile { border:1px solid rgba(16,24,40,.18); border-radius:8px; background:linear-gradient(180deg,#fff8f2,#fff); min-height:104px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.55rem; color:#667085; font-weight:800; font-size:.85rem; text-align:center; padding:.8rem; box-shadow:0 14px 30px rgba(251,100,27,.08); }
        .logo-mark { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#08a88a,#33d3b8); color:#fff; box-shadow:0 12px 24px rgba(8,168,138,.20); }
        .logo-tile:nth-child(2) .logo-mark { background:linear-gradient(135deg,#dc2626,#fb7185); box-shadow:0 12px 24px rgba(220,38,38,.18); }
        .logo-tile:nth-child(3) .logo-mark { background:linear-gradient(135deg,#16a34a,#6ee7b7); box-shadow:0 12px 24px rgba(22,163,74,.18); }
        .logo-tile:nth-child(4) .logo-mark { background:linear-gradient(135deg,#2f6df6,#65a5ff); box-shadow:0 12px 24px rgba(47,109,246,.18); }
        .logo-mark svg { width:28px; height:28px; stroke:currentColor; fill:none; stroke-width:2.35; stroke-linecap:round; stroke-linejoin:round; }
        .section-warm { background:linear-gradient(180deg,#fff6ed 0%,#fffaf5 100%); border-block:1px solid #f3dfd1; }
        .section-purple { background:linear-gradient(180deg,#f5efff 0%,#f8f4ff 100%); border-block:1px solid #e7dafa; }
        .section-cream { background:linear-gradient(180deg,#fffaf0 0%,#fff6ec 100%); border-block:1px solid #f2dec9; }
        .band-muted { background:linear-gradient(180deg,#eee7fb 0%,#f4edff 100%); border-block:1px solid #ded0f5; }
        .module-list { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.75rem; }
        .module-item { border:1px solid rgba(16,24,40,.18); border-radius:8px; padding:.85rem; background:linear-gradient(180deg,#fff8f2,#fff); font-weight:800; color:#344054; display:flex; align-items:center; gap:.7rem; box-shadow:0 12px 26px rgba(251,100,27,.08); }
        .module-mark { width:38px; height:38px; border-radius:8px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#fb641b,#ff9a4d); color:#fff; flex:0 0 auto; box-shadow:0 10px 20px rgba(251,100,27,.20); }
        .module-mark svg { width:22px; height:22px; stroke:currentColor; fill:none; stroke-width:2.35; stroke-linecap:round; stroke-linejoin:round; }
        .detail-image-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1rem; }
        .detail-image-card { border:1px solid rgba(16,24,40,.18); border-radius:8px; background:linear-gradient(180deg,#fff8f2,#fff); padding:1.25rem; box-shadow:0 18px 42px rgba(251,100,27,.10); text-align:center; transition:.18s ease; }
        .detail-image-card:hover { transform:translateY(-3px); box-shadow:0 22px 46px rgba(16,24,40,.10); }
        .detail-icon-box { width:90px; height:90px; margin:0 auto 1rem; border-radius:18px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#fb641b,#ff9a4d); box-shadow:0 16px 34px rgba(251,100,27,.22); }
        .detail-image-card:nth-child(2) .detail-icon-box { background:linear-gradient(135deg,#08a88a,#33d3b8); box-shadow:0 16px 34px rgba(8,168,138,.20); }
        .detail-image-card:nth-child(3) .detail-icon-box { background:linear-gradient(135deg,#2f6df6,#65a5ff); box-shadow:0 16px 34px rgba(47,109,246,.20); }
        .detail-image-card:nth-child(4) .detail-icon-box { background:linear-gradient(135deg,#842f89,#b45ac0); box-shadow:0 16px 34px rgba(132,47,137,.22); }
        .detail-icon-box svg { width:48px; height:48px; stroke:#fff; fill:none; stroke-width:2.5; stroke-linecap:round; stroke-linejoin:round; }
        .processing-overlay { position:fixed; inset:0; background:rgba(5,15,29,.74); backdrop-filter:blur(8px); display:none; align-items:center; justify-content:center; padding:1.25rem; z-index:1200; }
        .processing-overlay.active { display:flex; }
        .processing-panel { width:min(680px,100%); background:linear-gradient(180deg,rgba(16,24,40,.98),rgba(25,58,94,.97)); border:1px solid rgba(255,255,255,.14); border-radius:8px; color:#f8fafc; padding:2rem; box-shadow:0 28px 60px rgba(2,6,23,.42); }
        .loader { width:72px; height:72px; border-radius:50%; border:4px solid rgba(255,255,255,.16); border-top-color:#ffb089; animation:spin 1s linear infinite; box-shadow:0 0 0 10px rgba(251,100,27,.08); }
        .processing-step { border-radius:8px; padding:.9rem 1rem; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.08); }
        .processing-step + .processing-step { margin-top:.9rem; }
        .processing-step.active { border-color:rgba(251,100,27,.45); background:rgba(251,100,27,.14); }
        .processing-step.done { border-color:rgba(8,168,138,.44); background:rgba(8,168,138,.14); }
        .page-lock { pointer-events:none; opacity:.72; }
        .checkout-toast { position:fixed; top:92px; right:20px; width:min(380px,calc(100vw - 40px)); padding:1rem 1.1rem; border-radius:8px; background:#101828; color:#fff; box-shadow:0 18px 44px rgba(16,24,40,.24); z-index:1400; transform:translateY(-12px); opacity:0; pointer-events:none; transition:.18s ease; }
        .checkout-toast.show { transform:translateY(0); opacity:1; }
        .checkout-toast.warning { background:#9a3412; }
        .checkout-toast.error { background:#991b1b; }
        .form-control, .form-select { border-color:#e6d6cf; }
        .form-control:focus, .form-select:focus { border-color:rgba(251,100,27,.46); box-shadow:0 0 0 .25rem rgba(251,100,27,.14); }
        @keyframes spin { to { transform:rotate(360deg);} }
        @keyframes marquee { from { transform:translateX(0); } to { transform:translateX(-50%); } }
        @media (max-width:991.98px) { .checkout-wrap { position:static; } .proof-grid, .detail-image-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .logo-cloud { grid-template-columns:repeat(3,minmax(0,1fr)); } .hero-shell { padding-top:3.5rem; } .nav-shell { min-height:64px; } }
        @media (max-width:575.98px) { .proof-grid, .module-list, .detail-image-grid { grid-template-columns:1fr; } .logo-cloud { grid-template-columns:repeat(2,minmax(0,1fr)); } .brand-logo { height:36px; } }
    </style>
</head>
<body>
    @php
        $brandLogo = asset('public/build/assets/images/engage-logo.png');
    @endphp
    <div class="top-strip py-2">
        <div class="top-marquee" aria-label="EngageNet CRM highlights">
            <span>All-in-one CRM for sales, WhatsApp, quotations, orders, accounts, and HRM</span>
            <span>Start with a plan and activate your workspace online</span>
            <span>Track leads, quotes, invoices, payments, attendance, payroll, and reports</span>
            <span>Built for Indian teams and growing businesses</span>
            <span>All-in-one CRM for sales, WhatsApp, quotations, orders, accounts, and HRM</span>
            <span>Start with a plan and activate your workspace online</span>
            <span>Track leads, quotes, invoices, payments, attendance, payroll, and reports</span>
            <span>Built for Indian teams and growing businesses</span>
        </div>
    </div>
    <nav class="navbar navbar-expand-lg site-nav sticky-top">
        <div class="container nav-shell d-flex align-items-center justify-content-between gap-3">
            <a class="navbar-brand fw-bold fs-5 d-flex align-items-center gap-2" href="#">
                <img src="{{ $brandLogo }}" alt="Engage Net" class="brand-logo">
            </a>
            <div class="d-none d-lg-flex align-items-center gap-4">
                <a href="{{ route('website.home') }}" class="site-link">Home</a>
                <a href="{{ route('website.features') }}" class="site-link">Features</a>
                <a href="{{ route('website.workflow') }}" class="site-link">Workflow</a>
                <a href="{{ route('website.integrations') }}" class="site-link">Integrations</a>
                <a href="{{ route('website.pricing') }}" class="site-link">Pricing</a>
            </div>
            <div class="d-flex gap-2">
                <a href="#pricing" class="btn btn-outline-dark btn-sm">Book Plan</a>
                <a href="{{ route('login') }}" class="btn btn-dark btn-sm">Login</a>
            </div>
        </div>
    </nav>

    <section class="hero-shell">
        <div class="container py-lg-4">
            <div class="text-center">
                <span class="badge badge-soft rounded-pill px-3 py-2 mb-3">Built for Indian teams. Ready for growing businesses.</span>
                <h1 class="hero-title fw-bold mb-3">Run every customer flow from one CRM.</h1>
                <p class="hero-sub mb-4">EngageNet brings leads, follow-ups, WhatsApp chats, quotations, orders, invoices, payments, attendance, payroll, and reports into one tenant-ready workspace. Choose a plan and the system prepares your CRM automatically after checkout.</p>
                <div class="d-flex flex-wrap gap-2 hero-actions justify-content-center">
                    <a href="#pricing" class="btn btn-brand btn-lg px-4">Start Now</a>
                    <a href="{{ route('login') }}" class="btn btn-outline-dark btn-lg px-4">Client Login</a>
                </div>
                <div class="hero-checks justify-content-center">
                    <span class="hero-check">Tenant-ready setup</span>
                    <span class="hero-check">WhatsApp connected</span>
                    <span class="hero-check">Quote to cash flow</span>
                </div>
                <div class="row row-cols-1 row-cols-md-2 g-3 hero-kpi-row mx-auto" style="max-width:760px;">
                    <div class="col"><div class="mini-kpi hero-kpi"><div class="small muted">Workspace Setup</div><div class="fs-4 fw-bold hero-metric-value">Automatic</div><div class="small muted mt-1">Tenant, roles, settings, and login access</div></div></div>
                    <div class="col"><div class="mini-kpi hero-kpi"><div class="small muted">Business Coverage</div><div class="fs-4 fw-bold hero-metric-value">CRM + HRM</div><div class="small muted mt-1">Sales, accounts, team activity, and reporting</div></div></div>
                </div>
            </div>
            <div class="hero-banner-card mt-5">
                <img src="{{ asset('public/website_logo/banner.jpg') }}" alt="EngageNet streamlines leads, quotes, orders, team collaboration, and WhatsApp integration" class="hero-banner-img">
            </div>
        </div>
    </section>

    <section class="py-5 band-muted">
        <div class="container">
            <div class="proof-grid">
                <div class="proof-card">
                    <div class="small">Inquiry to deal</div>
                    <div class="fw-bold fs-5">Lead capture, assignment, follow-up, quotation, and order conversion</div>
                </div>
                <div class="proof-card">
                    <div class="small">Customer communication</div>
                    <div class="fw-bold fs-5">WhatsApp chats, bulk messages, and customer conversation history</div>
                </div>
                <div class="proof-card">
                    <div class="small">Business operations</div>
                    <div class="fw-bold fs-5">Products, vendors, transport, invoices, payments, and accounts</div>
                </div>
                <div class="proof-card">
                    <div class="small">Team control</div>
                    <div class="fw-bold fs-5">Attendance, leave, payroll, targets, location activity, and reports</div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="py-5 section-warm">
        <div class="container">
            <div class="text-center mb-4">
                    <div class="section-eyebrow justify-content-center">Core modules</div>
                    <h2 class="section-title fw-bold mb-2">Best-fit CRM modules for daily business work</h2>
                    <p class="muted mb-0 mx-auto" style="max-width:760px;">Inspired by all-in-one CRM platforms, shaped around what this project already does: sales, communication, accounts, HRM, tenant setup, and reporting.</p>
                    <div class="mt-3">
                        <a href="{{ route('website.features') }}" class="site-link">View all feature details</a>
                    </div>
                </div>
            <div class="module-list mx-auto mb-4" style="max-width:620px;">
                <div class="module-item"><span class="module-mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19h16"></path><path d="M7 16V9"></path><path d="M12 16V5"></path><path d="M17 16v-4"></path><path d="m6 9 4-4 4 5 4-3"></path></svg></span><span>CRM</span></div>
                <div class="module-item"><span class="module-mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4.5 19.5 6 15.8A8 8 0 1 1 9 18.4z"></path><path d="M9.5 8.8c.2-.4.4-.5.7-.5h.6c.2 0 .4.1.5.4l.5 1.2c.1.3 0 .6-.2.8l-.4.4c.6 1.1 1.5 2 2.7 2.6l.4-.4c.2-.2.5-.3.8-.2l1.2.5c.3.1.4.3.4.6v.6c0 .3-.1.5-.4.7-.5.3-1.1.4-1.7.3-3.1-.5-5.6-3-6.1-6.1-.1-.6 0-1.2.3-1.7z"></path></svg></span><span>WhatsApp</span></div>
                <div class="module-item"><span class="module-mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 21h18"></path><path d="M5 21V7l7-4 7 4v14"></path><path d="M9 21v-7h6v7"></path><path d="M8 9h1"></path><path d="M12 9h1"></path><path d="M16 9h1"></path></svg></span><span>Tenant</span></div>
                <div class="module-item"><span class="module-mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></span><span>HRM</span></div>
                </div>
            <div class="row g-3">
                <div class="col-md-6 col-xl-3"><div class="feature-card"><div class="feature-icon mb-3" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19h16"></path><path d="M6 16v-5"></path><path d="M12 16V8"></path><path d="M18 16V5"></path><path d="M4 7c1.5-2 3.5-3 6-3 2.3 0 4.2.8 5.7 2.4L20 10"></path></svg></div><h5 class="fw-bold">Lead management</h5><p class="muted feature-copy mb-0">Track stages, owners, follow-ups, chats, imported leads, and activity history end to end.</p></div></div>
                <div class="col-md-6 col-xl-3"><div class="feature-card"><div class="feature-icon teal mb-3" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 4h8"></path><path d="M9 2v4"></path><path d="M15 2v4"></path><rect x="4" y="6" width="16" height="14" rx="2"></rect><path d="M8 11h8"></path><path d="M8 15h5"></path></svg></div><h5 class="fw-bold">Quotation to invoice</h5><p class="muted feature-copy mb-0">Build quotes, convert orders, generate invoice templates, and keep payment records ready.</p></div></div>
                <div class="col-md-6 col-xl-3"><div class="feature-card"><div class="feature-icon blue mb-3" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 7h10a3 3 0 0 1 3 3v5a3 3 0 0 1-3 3h-5l-4 3v-3H7a3 3 0 0 1-3-3v-5a3 3 0 0 1 3-3Z"></path><path d="M9 12h6"></path><path d="M9 15h4"></path></svg></div><h5 class="fw-bold">WhatsApp CRM</h5><p class="muted feature-copy mb-0">Connect devices, manage chats, send bulk messages, and keep customer replies visible.</p></div></div>
                <div class="col-md-6 col-xl-3"><div class="feature-card"><div class="feature-icon violet mb-3" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3l7 4v5c0 4.2-2.7 7.7-7 9-4.3-1.3-7-4.8-7-9V7l7-4Z"></path><path d="M9.5 12.5l1.7 1.7 3.3-3.7"></path></svg></div><h5 class="fw-bold">Tenant-ready setup</h5><p class="muted feature-copy mb-0">Provision isolated workspaces with roles, plans, settings, seeded masters, and user access.</p></div></div>
            </div>
        </div>
    </section>

    <section id="workflow" class="py-5 section-purple">
        <div class="container">
            <div class="text-center mb-4">
                <div class="section-eyebrow justify-content-center">Operating flow</div>
                <h2 class="section-title fw-bold mb-2">One connected operating flow</h2>
                <p class="muted mb-0">Teams can move from inquiry to collection without jumping between disconnected tools.</p>
                <a href="{{ route('website.workflow') }}" class="site-link d-inline-block mt-3">View workflow details</a>
            </div>
            <div class="row g-3">
                <div class="col-md-6 col-xl-3"><div class="workflow-card"><div class="workflow-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M21 21l-5.2-5.2"></path><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="M10.5 7.5v6"></path><path d="M7.5 10.5h6"></path></svg></div><h5 class="fw-bold">Capture inquiry</h5><p class="muted mb-0">Create or import leads, assign team members, add products, and schedule follow-ups.</p></div></div>
                <div class="col-md-6 col-xl-3"><div class="workflow-card"><div class="workflow-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 7h10a3 3 0 0 1 3 3v5a3 3 0 0 1-3 3h-5l-4 3v-3H7a3 3 0 0 1-3-3v-5a3 3 0 0 1 3-3Z"></path><path d="M9 12h6"></path><path d="M9 15h4"></path></svg></div><h5 class="fw-bold">Discuss and quote</h5><p class="muted mb-0">Use WhatsApp chat context, prepare quotations, and share clear pricing with customers.</p></div></div>
                <div class="col-md-6 col-xl-3"><div class="workflow-card"><div class="workflow-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 6h15l-1.5 8h-13z"></path><path d="M6 6 5 3H2"></path><circle cx="9" cy="19" r="1.5"></circle><circle cx="18" cy="19" r="1.5"></circle><path d="M10 10h5"></path></svg></div><h5 class="fw-bold">Convert order</h5><p class="muted mb-0">Turn winning quotes into orders, invoices, transport records, and payment follow-ups.</p></div></div>
                <div class="col-md-6 col-xl-3"><div class="workflow-card"><div class="workflow-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19h16"></path><path d="M7 16V9"></path><path d="M12 16V5"></path><path d="M17 16v-4"></path><path d="m6 9 4-4 4 5 4-3"></path></svg></div><h5 class="fw-bold">Measure team output</h5><p class="muted mb-0">Review sales reports, attendance, targets, payroll, and customer account activity.</p></div></div>
            </div>
        </div>
    </section>

    <section id="integrations" class="py-5 section-cream">
        <div class="container">
            <div class="text-center mb-4">
                    <span class="badge badge-soft rounded-pill px-3 py-2 mb-3">Connected workspace</span>
                    <div class="section-eyebrow justify-content-center">Integrations</div>
                    <h2 class="section-title fw-bold mb-2">Integrations and essentials your team already expects</h2>
                    <p class="muted mb-0 mx-auto" style="max-width:760px;">The homepage keeps the reference site's all-in-one idea, but uses EngageNet's own checkout, tenant, WhatsApp, and reporting strengths.</p>
                    <a href="{{ route('website.integrations') }}" class="site-link d-inline-block mt-3">View integration details</a>
                </div>
                <div class="mx-auto" style="max-width:900px;">
                    <div class="logo-cloud">
                        <div class="logo-tile"><span class="logo-mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4.5 19.5 6 15.8A8 8 0 1 1 9 18.4z"></path><path d="M9.5 8.8c.2-.4.4-.5.7-.5h.6c.2 0 .4.1.5.4l.5 1.2c.1.3 0 .6-.2.8l-.4.4c.6 1.1 1.5 2 2.7 2.6l.4-.4c.2-.2.5-.3.8-.2l1.2.5c.3.1.4.3.4.6v.6c0 .3-.1.5-.4.7-.5.3-1.1.4-1.7.3-3.1-.5-5.6-3-6.1-6.1-.1-.6 0-1.2.3-1.7z"></path></svg></span><span>WhatsApp</span></div>
                        <div class="logo-tile"><span class="logo-mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 3h7l4 4v14H7z"></path><path d="M14 3v5h5"></path><path d="M9 14h6"></path><path d="M9 17h4"></path></svg></span><span>PDF Invoice</span></div>
                        <div class="logo-tile"><span class="logo-mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4z"></path><path d="M8 5v14"></path><path d="M4 10h16"></path><path d="M4 15h16"></path><path d="m13 9 4 6"></path><path d="m17 9-4 6"></path></svg></span><span>Excel Import</span></div>
                        <div class="logo-tile"><span class="logo-mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19h16"></path><path d="M7 16V9"></path><path d="M12 16V5"></path><path d="M17 16v-4"></path><path d="m6 9 4-4 4 5 4-3"></path></svg></span><span>Reports</span></div>
                    </div>
                </div>
        </div>
    </section>

    <section class="py-5 band-muted">
        <div class="container">
            <div class="text-center mb-4">
                <div class="section-eyebrow justify-content-center">Explore more</div>
                <h2 class="section-title fw-bold mb-2">Explore with visuals</h2>
                <p class="muted mb-0">Open each detail page to see how the CRM modules, workflows, integrations, and plans fit together.</p>
            </div>
            <div class="detail-image-grid">
                <a href="{{ route('website.features') }}" class="detail-image-card text-decoration-none text-dark">
                    <div class="detail-icon-box" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 19h16"></path><path d="M7 16V9"></path><path d="M12 16V5"></path><path d="M17 16v-4"></path><path d="M5 9l4-4 4 4 5-5"></path></svg>
                    </div>
                    <div class="fw-bold mt-3">Feature Details</div>
                    <div class="small muted">Sales, accounts, WhatsApp, HRM, and reporting modules.</div>
                </a>
                <a href="{{ route('website.workflow') }}" class="detail-image-card text-decoration-none text-dark">
                    <div class="detail-icon-box" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="5" height="5" rx="1"></rect><rect x="16" y="4" width="5" height="5" rx="1"></rect><rect x="9.5" y="15" width="5" height="5" rx="1"></rect><path d="M8 6.5h8"></path><path d="M12 9v6"></path><path d="M5.5 9v2a4 4 0 0 0 4 4"></path><path d="M18.5 9v2a4 4 0 0 1-4 4"></path></svg>
                    </div>
                    <div class="fw-bold mt-3">Workflow Details</div>
                    <div class="small muted">Inquiry, quotation, order, invoice, and collection flow.</div>
                </a>
                <a href="{{ route('website.integrations') }}" class="detail-image-card text-decoration-none text-dark">
                    <div class="detail-icon-box" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M8 7H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h3"></path><path d="M16 7h3a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-3"></path><path d="M9 12h6"></path><path d="M12 9v6"></path><circle cx="12" cy="12" r="3"></circle><path d="M12 3v3"></path><path d="M12 18v3"></path></svg>
                    </div>
                    <div class="fw-bold mt-3">Integration Details</div>
                    <div class="small muted">WhatsApp, Razorpay, PDF, Excel, and reports.</div>
                </a>
                <a href="{{ route('website.pricing') }}" class="detail-image-card text-decoration-none text-dark">
                    <div class="detail-icon-box" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 10h18"></path><path d="M7 15h4"></path><path d="M15 15h2"></path></svg>
                    </div>
                    <div class="fw-bold mt-3">Pricing Details</div>
                    <div class="small muted">Plan limits, trials, modules, and activation steps.</div>
                </a>
            </div>
        </div>
    </section>

    <section id="pricing" class="py-5 section-warm">
        <div class="container">
            <div class="row align-items-end g-3 mb-4">
                <div class="col-12 text-center">
                    <div class="section-eyebrow justify-content-center">Plans and checkout</div>
                    <h2 class="section-title fw-bold mb-2">Choose your plan and activate your CRM professionally</h2>
                    <p class="muted mb-0 mx-auto" style="max-width:760px;">The checkout panel updates instantly with plan details, payment guidance, and onboarding steps so the customer understands the full journey before paying.</p>
                    <a href="{{ route('website.pricing') }}" class="site-link d-inline-block mt-3">View pricing details</a>
                    @if (session('error'))
                        <div class="status-banner is-danger mt-3 mb-0 mx-auto text-start" style="max-width:760px;">
                            <span class="banner-label">Checkout issue</span>
                            {{ session('error') }}
                        </div>
                    @endif
                </div>
                <div class="col-12">
                    <div id="checkout-status-banner" class="status-banner d-none">
                        <div class="fw-bold" id="checkout-status-title">Checkout status</div>
                        <div class="small mt-1" id="checkout-status-copy"></div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="row g-3" id="pricing-grid">
                        @if($plans->isEmpty())
                            <div class="col-12">
                                <div class="status-banner is-warning h-100">
                                    <span class="banner-label">Plans unavailable</span>
                                    No active plans configured yet.
                                </div>
                            </div>
                        @else
                            @foreach($plans as $index => $plan)
                                @php
                                    $modules = is_array($plan->modules) && count($plan->modules)
                                        ? (in_array('*', $plan->modules, true) ? ['All modules enabled'] : array_values($plan->modules))
                                        : ['All modules enabled'];
                                @endphp
                                <div class="col-md-6">
                                    <div class="pricing-card {{ $index === 0 ? 'active' : '' }}" data-plan-id="{{ $plan->id }}">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                            <div>
                                                <div class="pill mb-2">{{ ucfirst(str_replace('_', ' ', (string) $plan->billing_cycle)) }}</div>
                                                <h3 class="h4 fw-bold mb-0">{{ $plan->name }}</h3>
                                            </div>
                                            @if($index === 0)
                                                <span class="badge text-bg-dark rounded-pill">Popular</span>
                                            @endif
                                        </div>
                                        <div class="price-tag">Rs. {{ number_format((float) $plan->price, 2) }}</div>
                                        <div class="muted small mt-2">Trial: {{ (int) $plan->trial_days }} days</div>
                                        <ul class="pricing-list mt-4">
                                            <li>Users: {{ $plan->user_limit ?: 'Unlimited' }}</li>
                                            <li>WhatsApp messages: {{ $plan->whatsapp_limit ?: 'Unlimited' }}</li>
                                            <li>Modules: {{ implode(', ', $modules) }}</li>
                                        </ul>
                                        <button type="button" class="btn btn-outline-dark w-100 mt-4 choose-plan-btn">Choose {{ $plan->name }}</button>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="checkout-wrap" id="checkout-container">
                        <div class="checkout-panel">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="small text-uppercase fw-bold muted">Guided checkout</div>
                                    <h3 class="h4 fw-bold mb-1" id="checkout-heading">Review the plan, then pay securely</h3>
                                    <p class="muted mb-0" id="checkout-copy">After payment, the workspace is prepared using the same tenant creation flow used from tenancy admin.</p>
                                </div>
                                <span class="pill" id="checkout-mode-pill">Gateway On</span>
                            </div>
                            <div class="summary-card mt-4">
                                <div class="summary-row">
                                    <div><div class="small muted">Selected plan</div><div class="fw-bold fs-5" id="summary-plan-name">{{ $plans->first()->name ?? 'Select a plan' }}</div></div>
                                    <div class="text-end"><div class="small muted">Billing</div><div class="fw-bold" id="summary-plan-cycle">{{ $plans->first() ? ucfirst(str_replace('_', ' ', (string) $plans->first()->billing_cycle)) : '-' }}</div></div>
                                </div>
                                <div class="summary-row align-items-end">
                                    <div><div class="small muted">Payable today</div><div class="summary-amount" id="summary-plan-amount">Rs. {{ $plans->first() ? number_format((float) $plans->first()->price, 2) : '0.00' }}</div></div>
                                    <div class="pill" id="summary-payment-pill">Secure online payment</div>
                                </div>
                                <div class="summary-row small muted">
                                    <div>Trial days: <span class="fw-bold text-dark" id="summary-plan-trial">{{ $plans->first()->trial_days ?? 0 }}</span></div>
                                    <div>Users: <span class="fw-bold text-dark" id="summary-plan-users">{{ $plans->first()->user_limit ?: 'Unlimited' }}</span></div>
                                </div>
                                <div class="mt-3 small muted">Modules included: <span class="fw-semibold text-dark" id="summary-plan-modules">{{ $plans->first() && is_array($plans->first()->modules) && count($plans->first()->modules) ? (in_array('*', $plans->first()->modules, true) ? 'All modules enabled' : implode(', ', $plans->first()->modules)) : 'All modules enabled' }}</span></div>
                            </div>
                            <div class="mt-4">
                                <div class="step"><span class="step-badge">1</span><div><div class="fw-bold">Choose the right plan</div><div class="small muted">Your plan summary remains visible throughout checkout.</div></div></div>
                                <div class="step"><span class="step-badge">2</span><div><div class="fw-bold">Enter company details once</div><div class="small muted">Drafts auto-save before payment so accidental refreshes are less risky.</div></div></div>
                                <div class="step"><span class="step-badge">3</span><div><div class="fw-bold" id="checkout-step-title">Pay and let setup finish</div><div class="small muted" id="checkout-step-copy">After Razorpay success, the page locks into a guided processing state while your workspace is created.</div></div></div>
                            </div>
                            <form id="checkout-form" class="mt-4">
                                <input type="hidden" id="plan_id" name="plan_id" value="{{ $plans->first()->id ?? '' }}">
                                <input type="hidden" id="draft_id" name="draft_id">
                                <div class="row g-3">
                                    <div class="col-12"><label class="form-label fw-semibold">Full Name</label><input type="text" class="form-control form-control-lg" name="name" placeholder="Your full name" value="{{ $checkoutUser->name ?? '' }}" required></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Email</label><input type="email" class="form-control form-control-lg" name="email" placeholder="name@company.com" value="{{ $checkoutUser->email ?? '' }}" required></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Phone</label><input type="text" class="form-control form-control-lg" name="phone" placeholder="Optional" value="{{ $checkoutUser->phone ?? '' }}"></div>
                                    <div class="col-12"><label class="form-label fw-semibold">Company Name</label><input type="text" class="form-control form-control-lg" name="company_name" placeholder="Your business name" value="{{ $checkoutTenant->name ?? '' }}" required></div>
                                </div>
                                <div class="trust-card mt-4">
                                    <div class="fw-bold mb-2" id="trust-title">What happens right after payment</div>
                                    <div class="trust-line small muted" id="trust-copy">Once your payment is successful, the system starts preparing your CRM workspace automatically. Your business account, basic setup, and access details are arranged step by step in the background.</div>
                                    <div class="trust-line small muted">You will see a clear processing screen during setup, and the next page will keep you updated until your workspace is ready to use.</div>
                                </div>
                                <div class="d-grid mt-4"><button type="submit" class="btn btn-brand btn-lg" id="pay-btn">Proceed To Secure Payment</button></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="hero-band p-4 p-lg-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <h2 class="h2 fw-bold mb-2">Professional onboarding, not just a payment page.</h2>
                        <p class="mb-0 opacity-75">Choose a plan, complete payment securely, and let the system prepare your workspace step by step. The experience is clearer for customers and more reliable for business onboarding.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end"><a href="#pricing" class="btn btn-light btn-lg">Start Now</a></div>
                </div>
            </div>
        </div>
    </section>

    <div class="processing-overlay" id="processing-overlay" aria-hidden="true">
        <div class="processing-panel">
            <div class="d-flex flex-column flex-lg-row gap-4 align-items-start align-items-lg-center">
                <div class="loader"></div>
                <div class="flex-grow-1">
                    <div class="small text-uppercase fw-bold">Please wait</div>
                    <h3 class="h2 fw-bold mb-2" id="processing-title">We are activating your workspace</h3>
                    <p class="mb-0 text-white-50" id="processing-copy">Payment is being verified and your tenant setup is starting.</p>
                </div>
            </div>
            <div class="mt-4">
                <div class="processing-step" data-stage="verifying_payment"><div class="fw-bold">Verifying payment</div><div class="small text-white-50">Confirming the Razorpay payment and locking the checkout.</div></div>
                <div class="processing-step" data-stage="provisioning_workspace"><div class="fw-bold">Creating workspace</div><div class="small text-white-50">Running tenant provisioning, permissions, settings, and master data setup.</div></div>
                <div class="processing-step" data-stage="completed"><div class="fw-bold">Preparing login access</div><div class="small text-white-50">Finalizing user sync and generating ready-to-use login details.</div></div>
            </div>
        </div>
    </div>

    <div class="checkout-toast warning" id="checkout-toast" role="status" aria-live="polite"></div>

    <footer class="border-top py-4 mt-3 bg-white">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="small text-secondary">&copy; {{ date('Y') }} {{ config('app.name') }} CRM</div>
            <div class="small"><a class="text-decoration-none text-secondary" href="{{ route('login') }}">Client Login</a></div>
        </div>
    </footer>

    @php
        $planPayload = $plans->map(function ($plan) {
            $modules = is_array($plan->modules) && count($plan->modules)
                ? (in_array('*', $plan->modules, true) ? ['All modules enabled'] : array_values($plan->modules))
                : ['All modules enabled'];

            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'amount' => number_format((float) $plan->price, 2, '.', ''),
                'billing_cycle' => ucfirst(str_replace('_', ' ', (string) $plan->billing_cycle)),
                'trial_days' => (int) $plan->trial_days,
                'user_limit' => $plan->user_limit ?: 'Unlimited',
                'modules' => implode(', ', $modules),
            ];
        })->values();
    @endphp

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        const plans = @json($planPayload);

        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const checkoutForm = document.getElementById('checkout-form');
        const checkoutContainer = document.getElementById('checkout-container');
        const draftInput = document.getElementById('draft_id');
        const planInput = document.getElementById('plan_id');
        const payBtn = document.getElementById('pay-btn');
        const statusBanner = document.getElementById('checkout-status-banner');
        const statusTitle = document.getElementById('checkout-status-title');
        const statusCopy = document.getElementById('checkout-status-copy');
        const processingOverlay = document.getElementById('processing-overlay');
        const processingTitle = document.getElementById('processing-title');
        const processingCopy = document.getElementById('processing-copy');
        const checkoutToast = document.getElementById('checkout-toast');
        const processingKey = 'website_checkout_processing';
        const checkoutHeading = document.getElementById('checkout-heading');
        const checkoutCopy = document.getElementById('checkout-copy');
        const checkoutModePill = document.getElementById('checkout-mode-pill');
        const summaryPaymentPill = document.getElementById('summary-payment-pill');
        const checkoutStepTitle = document.getElementById('checkout-step-title');
        const checkoutStepCopy = document.getElementById('checkout-step-copy');
        const trustTitle = document.getElementById('trust-title');
        const trustCopy = document.getElementById('trust-copy');
        const summaryPlanName = document.getElementById('summary-plan-name');
        const summaryPlanCycle = document.getElementById('summary-plan-cycle');
        const summaryPlanAmount = document.getElementById('summary-plan-amount');
        const summaryPlanTrial = document.getElementById('summary-plan-trial');
        const summaryPlanUsers = document.getElementById('summary-plan-users');
        const summaryPlanModules = document.getElementById('summary-plan-modules');

        let selectedPlan = plans[0] || null;
        let draftTimer = null;
        let pollTimer = null;
        let toastTimer = null;

        const showToast = (message, type = 'warning') => {
            if (!checkoutToast) return;

            clearTimeout(toastTimer);
            checkoutToast.textContent = message;
            checkoutToast.className = `checkout-toast ${type} show`;
            toastTimer = setTimeout(() => {
                checkoutToast.classList.remove('show');
            }, 4200);
        };

        const setSelectedPlan = (planId, shouldScroll = false) => {
            const nextPlan = plans.find((plan) => Number(plan.id) === Number(planId));
            if (!nextPlan) return;

            selectedPlan = nextPlan;
            planInput.value = nextPlan.id;
            summaryPlanName.textContent = nextPlan.name;
            summaryPlanCycle.textContent = nextPlan.billing_cycle;
            summaryPlanAmount.textContent = `Rs. ${Number(nextPlan.amount).toFixed(2)}`;
            summaryPlanTrial.textContent = nextPlan.trial_days;
            summaryPlanUsers.textContent = nextPlan.user_limit;
            summaryPlanModules.textContent = nextPlan.modules;

            const isTrialPlan = Number(nextPlan.amount) <= 0 && Number(nextPlan.trial_days) > 0;
            checkoutHeading.textContent = isTrialPlan ? 'Review the plan, then start your trial' : 'Review the plan, then pay securely';
            checkoutCopy.textContent = isTrialPlan
                ? 'No payment gateway is opened today. Your trial workspace is prepared using the same tenant creation flow.'
                : 'After payment, the workspace is prepared using the same tenant creation flow used from tenancy admin.';
            checkoutModePill.textContent = isTrialPlan ? 'Trial Mode' : 'Gateway On';
            summaryPaymentPill.textContent = isTrialPlan ? `${Number(nextPlan.trial_days)} day free trial` : 'Secure online payment';
            checkoutStepTitle.textContent = isTrialPlan ? 'Start trial and let setup finish' : 'Pay and let setup finish';
            checkoutStepCopy.textContent = isTrialPlan
                ? 'After trial activation, the page locks into a guided processing state while your workspace is created.'
                : 'After Razorpay success, the page locks into a guided processing state while your workspace is created.';
            trustTitle.textContent = isTrialPlan ? 'What happens right after trial start' : 'What happens right after payment';
            trustCopy.textContent = isTrialPlan
                ? 'Once your trial starts, the system prepares your CRM workspace automatically. Your business account, basic setup, and access details are arranged step by step in the background.'
                : 'Once your payment is successful, the system starts preparing your CRM workspace automatically. Your business account, basic setup, and access details are arranged step by step in the background.';
            payBtn.textContent = isTrialPlan ? 'Start Free Trial' : 'Proceed To Secure Payment';

            document.querySelectorAll('.pricing-card').forEach((card) => {
                card.classList.toggle('active', Number(card.dataset.planId) === Number(nextPlan.id));
            });

            if (shouldScroll) {
                checkoutContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        };

        const setInputsDisabled = (disabled) => {
            checkoutContainer.classList.toggle('page-lock', disabled);
            Array.from(checkoutForm.elements).forEach((element) => {
                if (element.name === 'draft_id' || element.name === 'plan_id') return;
                element.disabled = disabled;
            });
            document.querySelectorAll('.choose-plan-btn').forEach((button) => {
                button.disabled = disabled;
            });
        };

        const setStatusBanner = (payload) => {
            if (!payload) {
                statusBanner.className = 'status-banner d-none';
                statusTitle.textContent = 'Checkout status';
                statusCopy.textContent = '';
                return;
            }

            statusBanner.className = 'status-banner';
            if (payload.state === 'failed') statusBanner.classList.add('is-danger');
            if (payload.state === 'awaiting_payment') statusBanner.classList.add('is-warning');

            statusTitle.textContent = payload.state === 'activated'
                ? 'Workspace ready'
                : payload.state === 'failed'
                    ? 'Checkout needs attention'
                    : 'Checkout in progress';
            statusCopy.textContent = payload.message || '';
        };

        const setProcessingStage = (stage) => {
            document.querySelectorAll('.processing-step').forEach((step) => {
                step.classList.remove('active', 'done');
                const current = step.dataset.stage;
                if (stage === 'completed') {
                    step.classList.add('done');
                } else if (stage === 'provisioning_workspace') {
                    if (current === 'verifying_payment') step.classList.add('done');
                    if (current === 'provisioning_workspace') step.classList.add('active');
                } else if (current === 'verifying_payment') {
                    step.classList.add('active');
                }
            });
        };

        const showProcessingOverlay = (payload = {}) => {
            processingOverlay.classList.add('active');
            document.body.classList.add('processing-active');
            setInputsDisabled(true);
            setStatusBanner(payload);
            processingTitle.textContent = payload.state === 'activated' ? 'Your workspace is ready' : 'We are activating your workspace';
            processingCopy.textContent = payload.message || 'Payment is being verified and tenant setup is in progress.';
            setProcessingStage(payload.processing_stage || (payload.state === 'provisioning' ? 'provisioning_workspace' : 'verifying_payment'));
        };

        const hideProcessingOverlay = () => {
            processingOverlay.classList.remove('active');
            document.body.classList.remove('processing-active');
            setInputsDisabled(false);
        };

        const saveProcessingState = (payload) => {
            localStorage.setItem(processingKey, JSON.stringify({
                signup_id: payload.signup_id,
                status: payload.status || 'verifying'
            }));
        };

        const clearProcessingState = () => {
            localStorage.removeItem(processingKey);
            if (pollTimer) clearTimeout(pollTimer);
            pollTimer = null;
        };

        const getProcessingState = () => {
            try {
                return JSON.parse(localStorage.getItem(processingKey) || 'null');
            } catch (error) {
                return null;
            }
        };

        const saveDraft = async () => {
            if (payBtn.disabled) return;

            const data = Object.fromEntries(new FormData(checkoutForm).entries());
            if (!data.name && !data.email && !data.phone && !data.company_name) return;

            try {
                const response = await fetch("{{ route('website.checkout.draft') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify(data)
                });
                const json = await response.json();
                if (response.ok && json.draft_id) draftInput.value = json.draft_id;
            } catch (error) {
            }
        };

        const pollCheckoutStatus = async (signupId, redirectOnReady = false) => {
            try {
                const response = await fetch(`{{ route('website.checkout.status.poll') }}?id=${encodeURIComponent(signupId)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const payload = await response.json();
                setStatusBanner(payload);

                if (payload.state === 'activated') {
                    clearProcessingState();
                    showProcessingOverlay({ ...payload, processing_stage: 'completed' });
                    setTimeout(() => { window.location.href = payload.thank_you_url; }, redirectOnReady ? 300 : 900);
                    return;
                }

                if (payload.state === 'failed') {
                    clearProcessingState();
                    hideProcessingOverlay();
                    setStatusBanner(payload);
                    alert(payload.message || 'Payment was received, but setup needs attention.');
                    if (payload.thank_you_url) window.location.href = payload.thank_you_url;
                    return;
                }

                showProcessingOverlay(payload);
                saveProcessingState(payload);
                pollTimer = setTimeout(() => pollCheckoutStatus(signupId, redirectOnReady), 3000);
            } catch (error) {
                pollTimer = setTimeout(() => pollCheckoutStatus(signupId, redirectOnReady), 4000);
            }
        };

        document.querySelectorAll('.pricing-card').forEach((card) => {
            card.addEventListener('click', (event) => {
                if (event.target.closest('button') || event.target.closest('a')) return;
                setSelectedPlan(card.dataset.planId, true);
                saveDraft();
            });
        });

        document.querySelectorAll('.choose-plan-btn').forEach((button) => {
            button.addEventListener('click', (event) => {
                const card = event.currentTarget.closest('.pricing-card');
                if (!card) return;
                setSelectedPlan(card.dataset.planId, true);
                saveDraft();
            });
        });

        checkoutForm.querySelectorAll('input').forEach((input) => {
            input.addEventListener('input', () => {
                clearTimeout(draftTimer);
                draftTimer = setTimeout(saveDraft, 450);
            });
        });

        checkoutForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            payBtn.disabled = true;
            const isTrialPlan = selectedPlan && Number(selectedPlan.amount) <= 0 && Number(selectedPlan.trial_days) > 0;
            payBtn.textContent = isTrialPlan ? 'Starting Trial...' : 'Creating Order...';

            const payload = Object.fromEntries(new FormData(checkoutForm).entries());

            try {
                const orderResponse = await fetch("{{ route('website.checkout.order') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const order = await orderResponse.json();

                if (!orderResponse.ok) {
                    if (order.code === 'trial_already_used') {
                        showToast(order.message || 'Your free trial has already been used. Please choose a paid plan to continue.', 'warning');
                        return;
                    }
                    if (order.redirect_url) {
                        window.location.href = order.redirect_url;
                        return;
                    }
                    if (order.status_payload) {
                        saveProcessingState(order.status_payload);
                        showProcessingOverlay(order.status_payload);
                        pollCheckoutStatus(order.status_payload.signup_id, true);
                        return;
                    }
                    throw new Error(order.message || 'Order creation failed');
                }

                if (order.signup_id) draftInput.value = order.signup_id;

                if (order.trial_activation && order.status_payload) {
                    saveProcessingState(order.status_payload);
                    showProcessingOverlay(order.status_payload);
                    pollCheckoutStatus(order.status_payload.signup_id, true);
                    return;
                }

                const options = {
                    key: order.key,
                    amount: order.amount,
                    currency: order.currency,
                    name: order.name,
                    description: order.description,
                    order_id: order.order_id,
                    prefill: order.prefill || {},
                    modal: {
                        ondismiss: async () => {
                            try {
                                await fetch("{{ route('website.checkout.status') }}", {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                                    body: JSON.stringify({
                                        signup_id: order.signup_id,
                                        status: 'cancelled',
                                        reason: 'User closed Razorpay checkout',
                                        razorpay_order_id: order.order_id
                                    })
                                });
                            } catch (error) {
                            }
                        }
                    },
                    handler: async (response) => {
                        const optimisticStatus = {
                            signup_id: order.signup_id,
                            status: 'verifying',
                            state: 'verifying',
                            processing_stage: 'verifying_payment',
                            message: 'Payment captured. Verifying details and starting workspace setup.'
                        };
                        saveProcessingState(optimisticStatus);
                        showProcessingOverlay(optimisticStatus);

                        try {
                            const verifyResponse = await fetch("{{ route('website.checkout.verify') }}", {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                                body: JSON.stringify({
                                    signup_id: order.signup_id,
                                    razorpay_payment_id: response.razorpay_payment_id,
                                    razorpay_order_id: response.razorpay_order_id,
                                    razorpay_signature: response.razorpay_signature
                                })
                            });
                            const verify = await verifyResponse.json();
                            if (!verifyResponse.ok) {
                                clearProcessingState();
                                hideProcessingOverlay();
                                throw new Error(verify.message || 'Payment verification failed');
                            }
                            if (verify.status_payload) saveProcessingState(verify.status_payload);
                            pollCheckoutStatus(order.signup_id, true);
                        } catch (error) {
                            clearProcessingState();
                            hideProcessingOverlay();
                            alert(error.message || 'Payment verification failed');
                        }
                    }
                };

                const razorpay = new Razorpay(options);
                razorpay.on('payment.failed', async (response) => {
                    const error = response?.error || {};
                    try {
                        await fetch("{{ route('website.checkout.status') }}", {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                            body: JSON.stringify({
                                signup_id: order.signup_id,
                                status: 'payment_failed',
                                reason: error.reason || 'Payment failed',
                                error_code: error.code || '',
                                error_description: error.description || '',
                                razorpay_order_id: error.metadata?.order_id || order.order_id,
                                razorpay_payment_id: error.metadata?.payment_id || ''
                            })
                        });
                    } catch (ignored) {
                    }
                });

                razorpay.open();
            } catch (error) {
                showToast(error.message || 'Payment failed', 'error');
            } finally {
                payBtn.disabled = false;
                payBtn.textContent = isTrialPlan ? 'Start Free Trial' : 'Proceed To Secure Payment';
            }
        });

        const restoredState = getProcessingState();
        if (restoredState?.signup_id) {
            showProcessingOverlay({
                signup_id: restoredState.signup_id,
                status: restoredState.status,
                state: restoredState.status === 'activated' ? 'activated' : 'verifying',
                processing_stage: 'verifying_payment',
                message: 'Restoring your payment progress and checking workspace activation.'
            });
            pollCheckoutStatus(restoredState.signup_id, false);
        }

        if (selectedPlan) setSelectedPlan(selectedPlan.id);
    </script>
</body>
</html>
