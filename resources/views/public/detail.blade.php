<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page['eyebrow'] }} | {{ config('app.name') }} CRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --ink:#101828; --muted:#667085; --brand:#fb641b; --brand2:#e85a16; --line:#e7e9f0; --soft:#f4f7fb; --blue:#2f6df6; --teal:#08a88a; --violet:#6f42c1; }
        * { font-family:"Manrope",sans-serif; }
        body { color:var(--ink); background:linear-gradient(180deg,#fff3e9 0%,#fffaf4 30%,#f2ecfb 64%,#fff7ef 100%); }
        .top-strip { background:linear-gradient(90deg,#fb641b 0%,#842f89 55%,#4b1d57 100%); color:#fff; font-size:.85rem; overflow:hidden; box-shadow:0 10px 30px rgba(251,100,27,.18); }
        .top-marquee { display:flex; width:max-content; gap:2rem; white-space:nowrap; animation:marquee 24s linear infinite; }
        .top-marquee span { display:inline-flex; align-items:center; gap:.55rem; font-weight:800; }
        .top-marquee span:before { content:""; width:.45rem; height:.45rem; border-radius:50%; background:#fb641b; display:inline-block; }
        .site-nav { background:rgba(255,255,255,.94); backdrop-filter:blur(14px); border-bottom:1px solid var(--line); box-shadow:0 10px 30px rgba(16,24,40,.04); }
        .brand-logo { height:42px; width:auto; display:block; }
        .site-link { color:#344054; font-size:.94rem; font-weight:700; text-decoration:none; }
        .site-link:hover, .site-link.active { color:var(--brand2); }
        .btn-brand { background:linear-gradient(135deg,var(--brand2),var(--brand)); border:0; color:#fff; font-weight:800; box-shadow:0 14px 36px rgba(251,100,27,.24); }
        .btn-brand:hover, .btn-brand:focus { color:#fff; background:linear-gradient(135deg,#de5514,#ff742f); }
        .btn-outline-dark { border-color:#222938; color:#222938; font-weight:800; }
        .btn-outline-dark:hover, .btn-outline-dark:focus { background:#222938; border-color:#222938; color:#fff; }
        .btn-dark { background:#101828; border-color:#101828; font-weight:800; }
        .detail-hero { position:relative; overflow:hidden; padding:5rem 0 3rem; background:radial-gradient(720px 380px at 15% 2%,rgba(251,100,27,.24),transparent 60%),radial-gradient(720px 420px at 86% 6%,rgba(132,47,137,.20),transparent 58%),linear-gradient(180deg,#fff3e7 0%,#fffaf4 100%); border-bottom:1px solid #f1d8cc; }
        .detail-hero:before { content:""; position:absolute; inset:0; background:linear-gradient(135deg,rgba(251,100,27,.08),rgba(132,47,137,.07)); pointer-events:none; }
        .detail-hero > .container { position:relative; }
        .hero-title { font-size:clamp(2.35rem,5vw,4.2rem); line-height:1; max-width:820px; }
        .hero-sub { color:var(--muted); font-size:1.08rem; max-width:760px; }
        .hero-visual-wrap { background:#101828; border:1px solid rgba(16,24,40,.08); border-radius:8px; padding:1rem; box-shadow:0 24px 52px rgba(16,24,40,.18); }
        .hero-visual { width:100%; min-height:280px; max-height:420px; object-fit:contain; border-radius:8px; background:#fff; }
        .badge-soft { background:#fff7f2; color:var(--brand2); border:1px solid #ffd2bf; box-shadow:0 10px 24px rgba(251,100,27,.08); }
        .summary-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1rem; }
        .card-soft, .section-card, .pricing-card { background:linear-gradient(180deg,rgba(255,248,242,.98),rgba(255,255,255,.96)); border:1px solid rgba(16,24,40,.18); border-radius:8px; box-shadow:0 18px 42px rgba(251,100,27,.10); }
        .card-soft { padding:1rem; }
        .section-card, .pricing-card { height:100%; padding:1.35rem; }
        .section-image { width:100%; height:190px; object-fit:cover; object-position:center; border:1px solid rgba(16,24,40,.18); border-radius:8px; background:#fff8f2; margin-bottom:1rem; display:block; }
        .section-band { background:linear-gradient(180deg,#eee7fb 0%,#f4edff 100%); border-block:1px solid #ded0f5; }
        .detail-content-band { background:linear-gradient(180deg,#fff6ed 0%,#fffaf5 100%); border-block:1px solid #f3dfd1; }
        .pricing-band { background:linear-gradient(180deg,#f5efff 0%,#f8f4ff 100%); border-block:1px solid #e7dafa; }
        .pill { display:inline-flex; align-items:center; padding:.38rem .7rem; border-radius:999px; background:linear-gradient(135deg,#fff0e7,#f4efff); color:#842f89; font-size:.8rem; font-weight:800; border:1px solid rgba(16,24,40,.18); box-shadow:0 8px 18px rgba(251,100,27,.10); }
        .item-list { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.7rem; margin-top:1rem; }
        .item-chip { border:1px solid rgba(16,24,40,.18); border-radius:8px; padding:.75rem; font-weight:800; color:#344054; background:linear-gradient(180deg,#fff8f2,#fff); box-shadow:0 10px 22px rgba(251,100,27,.07); }
        .price-tag { font-size:2rem; line-height:1; letter-spacing:0; font-weight:800; }
        .pricing-list { padding-left:1.1rem; margin-bottom:0; color:var(--muted); }
        .pricing-list li + li { margin-top:.55rem; }
        .cta-band { border-radius:8px; background:linear-gradient(130deg,#101828 0%, #193a5e 48%, #fb641b 100%); color:#fff7f3; box-shadow:0 30px 60px rgba(16,24,40,.16); }
        .muted { color:var(--muted); }
        @keyframes marquee { from { transform:translateX(0); } to { transform:translateX(-50%); } }
        @media (max-width:991.98px) { .summary-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .detail-hero { padding-top:3.5rem; } .hero-visual { min-height:220px; } }
        @media (max-width:575.98px) { .summary-grid, .item-list { grid-template-columns:1fr; } .brand-logo { height:36px; } }
    </style>
</head>
<body>
    @php
        $brandLogo = asset('public/build/assets/images/engage-logo.png');
    @endphp
    <div class="top-strip py-2">
        <div class="top-marquee" aria-label="EngageNet CRM highlights">
            <span>All-in-one CRM for sales, WhatsApp, quotations, orders, tenants, and HRM</span>
            <span>Start with a plan and activate your workspace online</span>
            <span>Track leads, quotes, invoices, payments, attendance, payroll, and reports</span>
            <span>Built for Indian teams and growing businesses</span>
            <span>All-in-one CRM for sales, WhatsApp, quotations, orders, tenants, and HRM</span>
            <span>Start with a plan and activate your workspace online</span>
            <span>Track leads, quotes, invoices, payments, attendance, payroll, and reports</span>
            <span>Built for Indian teams and growing businesses</span>
        </div>
    </div>
    <nav class="navbar navbar-expand-lg site-nav sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold fs-5 d-flex align-items-center gap-2" href="{{ route('website.home') }}">
                <img src="{{ $brandLogo }}" alt="Engage Net" class="brand-logo">
            </a>
            <div class="d-none d-lg-flex align-items-center gap-4">
                <a href="{{ route('website.home') }}" class="site-link {{ $pageKey === 'home' ? 'active' : '' }}">Home</a>
                <a href="{{ route('website.features') }}" class="site-link {{ $pageKey === 'features' ? 'active' : '' }}">Features</a>
                <a href="{{ route('website.workflow') }}" class="site-link {{ $pageKey === 'workflow' ? 'active' : '' }}">Workflow</a>
                <a href="{{ route('website.integrations') }}" class="site-link {{ $pageKey === 'integrations' ? 'active' : '' }}">Integrations</a>
                <a href="{{ route('website.pricing') }}" class="site-link {{ $pageKey === 'pricing' ? 'active' : '' }}">Pricing</a>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('website.home') }}#pricing" class="btn btn-outline-dark btn-sm">Book Plan</a>
                <a href="{{ route('login') }}" class="btn btn-dark btn-sm">Login</a>
            </div>
        </div>
    </nav>

    <section class="detail-hero">
        <div class="container">
            <div class="row align-items-center g-4 g-lg-5">
                <div class="col-lg-7">
                    <span class="badge badge-soft rounded-pill px-3 py-2 mb-3">{{ $page['eyebrow'] }}</span>
                    <h1 class="hero-title fw-bold mb-3">{{ $page['title'] }}</h1>
                    <p class="hero-sub mb-4">{{ $page['intro'] }}</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('website.home') }}#pricing" class="btn btn-brand btn-lg px-4">Start Now</a>
                        <a href="{{ route('website.home') }}" class="btn btn-outline-dark btn-lg px-4">Back Home</a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="hero-visual-wrap">
                        <img src="{{ asset($page['image']) }}" alt="{{ $page['eyebrow'] }} visual" class="hero-visual">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 section-band">
        <div class="container">
            <div class="summary-grid">
                @foreach($page['summary'] as $summary)
                    <div class="card-soft">
                        <span class="pill mb-3">Included</span>
                        <div class="fw-bold fs-5">{{ $summary }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-5 detail-content-band">
        <div class="container">
            <div class="row g-3">
                @foreach($page['sections'] as $section)
                    <div class="col-lg-4">
                        <div class="section-card">
                            @if(!empty($section['image']))
                                <img src="{{ asset($section['image']) }}" alt="{{ $section['title'] }} visual" class="section-image">
                            @endif
                            <h2 class="h4 fw-bold mb-3">{{ $section['title'] }}</h2>
                            <p class="muted mb-0">{{ $section['copy'] }}</p>
                            <div class="item-list">
                                @foreach($section['items'] as $item)
                                    <div class="item-chip">{{ $item }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if($pageKey === 'pricing')
        <section class="py-5 pricing-band">
            <div class="container">
                <div class="row align-items-end g-3 mb-4">
                    <div class="col-lg-8">
                        <h2 class="fw-bold mb-2">Active Plans</h2>
                        <p class="muted mb-0">These plans come from the admin plan settings. Select checkout on the homepage to activate one.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="{{ route('website.home') }}#pricing" class="btn btn-brand">Open Checkout</a>
                    </div>
                </div>
                <div class="row g-3">
                    @forelse($plans as $plan)
                        @php
                            $modules = is_array($plan->modules) && count($plan->modules)
                                ? (in_array('*', $plan->modules, true) ? ['All modules enabled'] : array_values($plan->modules))
                                : ['All modules enabled'];
                        @endphp
                        <div class="col-md-6 col-xl-4">
                            <div class="pricing-card">
                                <div class="pill mb-3">{{ ucfirst(str_replace('_', ' ', (string) $plan->billing_cycle)) }}</div>
                                <h3 class="h4 fw-bold">{{ $plan->name }}</h3>
                                <div class="price-tag mb-2">Rs. {{ number_format((float) $plan->price, 2) }}</div>
                                <div class="muted small mb-3">Trial: {{ (int) $plan->trial_days }} days</div>
                                <ul class="pricing-list">
                                    <li>Users: {{ $plan->user_limit ?: 'Unlimited' }}</li>
                                    <li>WhatsApp messages: {{ $plan->whatsapp_limit ?: 'Unlimited' }}</li>
                                    <li>Modules: {{ implode(', ', $modules) }}</li>
                                </ul>
                                <a href="{{ route('website.home') }}#pricing" class="btn btn-outline-dark w-100 mt-4">Choose {{ $plan->name }}</a>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-warning mb-0">No active plans configured yet.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    @endif

    <section class="py-5">
        <div class="container">
            <div class="cta-band p-4 p-lg-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <h2 class="h2 fw-bold mb-2">Ready to activate your CRM workspace?</h2>
                        <p class="mb-0 opacity-75">Choose a plan on the homepage, complete checkout, and let EngageNet prepare the tenant workspace.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="{{ route('website.home') }}#pricing" class="btn btn-light btn-lg">Go To Pricing</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="border-top py-4 bg-white">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="small text-secondary">&copy; {{ date('Y') }} {{ config('app.name') }} CRM</div>
            <div class="small"><a class="text-decoration-none text-secondary" href="{{ route('website.home') }}">Home</a></div>
        </div>
    </footer>
</body>
</html>
