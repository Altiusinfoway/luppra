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
        :root { --ink:#171515; --muted:#6b6570; --brand:#fb641b; --brand2:#e85a16; --brand-soft:#fff0e7; --brand-soft-2:#fff7f2; --line:#eaded8; --bg:#fbf7f5; --ok:#166534; --warn:#9a3412; --danger:#b91c1c; --navy:#221b20; --accent-red:#f41927; --accent-purple:#842f89; --accent-violet:#5a246f; }
        * { font-family:"Manrope",sans-serif; }
        body { color:var(--ink); background:radial-gradient(680px 360px at 0% 0%, rgba(251,100,27,.16), transparent 55%), radial-gradient(620px 320px at 100% 0%, rgba(132,47,137,.10), transparent 55%), linear-gradient(180deg, #fffaf8 0%, #fbf6f4 100%); overflow-x:hidden; }
        body.processing-active { overflow:hidden; }
        .site-nav { background:rgba(255,255,255,.92); backdrop-filter:blur(12px); border-bottom:1px solid var(--line); box-shadow:0 10px 30px rgba(34,27,32,.04); }
        .hero-title { font-size:clamp(2.4rem,5vw,4.1rem); line-height:1.01; letter-spacing:-.05em; max-width:11ch; }
        .hero-sub { color:var(--muted); font-size:1.08rem; max-width:620px; }
        .badge-soft { background:linear-gradient(135deg,#fff2ea,#fff8f4); color:var(--brand2); border:1px solid #ffd2bf; box-shadow:0 10px 24px rgba(251,100,27,.08); }
        .btn-brand { background:linear-gradient(135deg,var(--brand2),var(--brand)); border:0; color:#fff; font-weight:800; box-shadow:0 14px 36px rgba(251,100,27,.24); }
        .btn-brand:hover, .btn-brand:focus { color:#fff; background:linear-gradient(135deg,#de5514,#ff742f); box-shadow:0 18px 40px rgba(251,100,27,.28); }
        .btn-outline-dark { border-color:#2a2222; color:#2a2222; }
        .btn-outline-dark:hover, .btn-outline-dark:focus { background:#2a2222; border-color:#2a2222; color:#fff; }
        .btn-dark { background:#2a2222; border-color:#2a2222; }
        .btn-dark:hover, .btn-dark:focus { background:#181313; border-color:#181313; }
        .panel, .feature-card, .pricing-card, .checkout-panel, .trust-card, .proof-card { background:rgba(255,255,255,.96); border:1px solid var(--line); border-radius:22px; box-shadow:0 18px 42px rgba(34,27,32,.07); }
        .hero-board { padding:1.75rem; background:linear-gradient(180deg,rgba(255,255,255,.98),rgba(255,245,239,.94)); }
        .mini-kpi { border:1px solid #eee2dc; border-radius:18px; background:#fff; padding:1rem; box-shadow:inset 0 1px 0 rgba(255,255,255,.65); }
        .mini-kpi strong, .hero-metric-value { letter-spacing:-.04em; }
        .section-title { font-size:clamp(1.7rem,3vw,2.35rem); letter-spacing:-.03em; }
        .muted { color:var(--muted); }
        .feature-card { height:100%; padding:1.35rem; }
        .feature-icon { width:2.9rem; height:2.9rem; border-radius:14px; display:inline-flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#ffe1d2,#f7d2ea); color:var(--brand2); box-shadow:0 12px 24px rgba(132,47,137,.10); }
        .feature-icon svg { width:1.35rem; height:1.35rem; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        .feature-copy { min-height:72px; }
        .pricing-card { height:100%; padding:1.4rem; transition:.18s ease; cursor:pointer; }
        .pricing-card:hover { transform:translateY(-4px); box-shadow:0 22px 40px rgba(34,27,32,.10); border-color:#f3c3ac; }
        .pricing-card.active { border-color:rgba(251,100,27,.56); box-shadow:0 22px 48px rgba(251,100,27,.16); background:linear-gradient(180deg,rgba(255,243,236,.96),#fff); }
        .pill { display:inline-flex; align-items:center; padding:.38rem .7rem; border-radius:999px; background:linear-gradient(135deg,#fff2ea,#f8edf8); color:#4b3d46; font-size:.8rem; font-weight:700; border:1px solid #f1dde5; }
        .price-tag { font-size:clamp(1.9rem,4vw,2.5rem); line-height:1; letter-spacing:-.04em; font-weight:800; }
        .pricing-list { padding-left:1.1rem; margin-bottom:0; color:var(--muted); }
        .pricing-list li + li { margin-top:.55rem; }
        .checkout-wrap { position:sticky; top:92px; }
        .checkout-panel { padding:1.5rem; }
        .summary-card { border:1px solid #ffd9c7; border-radius:18px; background:linear-gradient(180deg,#fff4ee,#fff); padding:1rem; box-shadow:inset 0 1px 0 rgba(255,255,255,.75); }
        .summary-row { display:flex; justify-content:space-between; gap:1rem; }
        .summary-row + .summary-row { margin-top:.8rem; }
        .summary-amount { font-size:1.6rem; font-weight:800; letter-spacing:-.03em; }
        .step { display:flex; gap:.8rem; align-items:flex-start; }
        .step + .step { margin-top:.9rem; }
        .step-badge { width:2rem; height:2rem; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:#ffe5d8; color:var(--brand2); font-weight:800; flex:0 0 auto; }
        .trust-card { padding:1rem; }
        .trust-line + .trust-line { margin-top:.75rem; }
        .status-banner { border-radius:16px; padding:.9rem 1rem; border:1px solid #ffd9c7; background:#fff4ee; color:#9a3f18; box-shadow:0 12px 24px rgba(251,100,27,.08); }
        .status-banner.is-warning { border-color:#fed7aa; background:#fff7ed; color:var(--warn); }
        .status-banner.is-danger { border-color:#fecaca; background:#fef2f2; color:var(--danger); }
        .hero-band { border-radius:26px; background:linear-gradient(130deg,#221b20 0%, #5a246f 42%, #fb641b 100%); color:#fff7f3; box-shadow:0 30px 60px rgba(34,27,32,.16); }
        .proof-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1rem; }
        .proof-card { padding:1rem; background:linear-gradient(180deg,rgba(255,255,255,.98),rgba(255,247,242,.95)); }
        .proof-card .small { color:var(--muted); }
        .brand-logo { height:42px; width:auto; display:block; }
        .processing-overlay { position:fixed; inset:0; background:rgba(5,15,29,.74); backdrop-filter:blur(8px); display:none; align-items:center; justify-content:center; padding:1.25rem; z-index:1200; }
        .processing-overlay.active { display:flex; }
        .processing-panel { width:min(680px,100%); background:linear-gradient(180deg,rgba(34,27,32,.97),rgba(90,36,111,.96)); border:1px solid rgba(255,255,255,.14); border-radius:28px; color:#f8fafc; padding:2rem; box-shadow:0 28px 60px rgba(2,6,23,.42); }
        .loader { width:72px; height:72px; border-radius:50%; border:4px solid rgba(255,255,255,.16); border-top-color:#ffb089; animation:spin 1s linear infinite; box-shadow:0 0 0 10px rgba(251,100,27,.08); }
        .processing-step { border-radius:16px; padding:.9rem 1rem; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.08); }
        .processing-step + .processing-step { margin-top:.9rem; }
        .processing-step.active { border-color:rgba(251,100,27,.45); background:rgba(251,100,27,.14); }
        .processing-step.done { border-color:rgba(132,47,137,.42); background:rgba(132,47,137,.16); }
        .page-lock { pointer-events:none; opacity:.72; }
        .form-control, .form-select { border-color:#e6d6cf; }
        .form-control:focus, .form-select:focus { border-color:rgba(251,100,27,.46); box-shadow:0 0 0 .25rem rgba(251,100,27,.14); }
        @keyframes spin { to { transform:rotate(360deg);} }
        @media (max-width:991.98px) { .checkout-wrap { position:static; } .proof-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (max-width:575.98px) { .proof-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
    @php
        $brandLogo = asset('public/build/assets/images/engage-logo.png');
    @endphp
    <nav class="navbar navbar-expand-lg site-nav sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold fs-5 d-flex align-items-center gap-2" href="#">
                <img src="{{ $brandLogo }}" alt="Luppra" class="brand-logo">
            </a>
        </div>
    </nav>

    <section class="py-5 py-lg-6">
        <div class="container py-lg-4">
            <div class="row align-items-center g-4 g-lg-5">
                <div class="col-lg-12">
                    
						<h1 class="mb-4">Privacy Policy</h1>

						<p>
							This Privacy Policy explains how Luppra collects, uses, stores, and protects
							information when users access our website and services.
						</p>

						<h3 class="mt-4">1. Information We Collect</h3>
						<p>
							We may collect personal information including name, email address, phone number,
							business information, and social media account information when users interact with our platform.
						</p>

						<h3 class="mt-4">2. How We Use Information</h3>
						<p>
							The information collected is used to:
						</p>

						<ul>
							<li>Provide and improve our services</li>
							<li>Manage user accounts and authentication</li>
							<li>Schedule and publish social media content</li>
							<li>Provide customer support</li>
							<li>Communicate important service updates</li>
						</ul>

						<h3 class="mt-4">3. Third-Party Services</h3>
						<p>
							Our platform may integrate with third-party platforms such as Facebook,
							Instagram, and WhatsApp to provide requested services.
							We only access information necessary to provide these services on behalf of users.
						</p>

						<h3 class="mt-4">4. Data Security</h3>
						<p>
							We implement reasonable technical and organizational measures to protect
							user information from unauthorized access, misuse, or disclosure.
						</p>

						<h3 class="mt-4">5. Data Sharing</h3>
						<p>
							We do not sell, rent, or trade user personal information to third parties.
							Information may only be shared when required by law or to provide requested services.
						</p>

						<h3 class="mt-4">6. User Rights</h3>
						<p>
							Users may request access, correction, or deletion of their personal information
							by contacting us through the contact information provided on this website.
						</p>

						<h3 class="mt-4">7. Changes to This Policy</h3>
						<p>
							We may update this Privacy Policy from time to time.
							Any changes will be posted on this page.
						</p>

						<h3 class="mt-4">8. Contact Us</h3>
						<p>
							If you have any questions regarding this Privacy Policy,
							please contact us through our Contact Us page.
						</p>
					
                </div>
                
            </div>
        </div>
    </section>

    

    

    

    

    <footer class="border-top py-4 mt-3 bg-white">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="small text-secondary">&copy; {{ date('Y') }} {{ config('app.name') }} CRM</div>
            <div class="small"><a class="text-decoration-none text-secondary" href="{{ route('login') }}">Client Login</a></div>
        </div>
    </footer>

    

    
</body>
</html>
