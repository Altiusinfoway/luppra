@extends('layouts.app')

@section('page-css')
<style>
.facebook-suite{background:linear-gradient(180deg,rgba(248,250,252,.72) 0%,rgba(245,247,251,0) 100%)}
.facebook-suite .hero-shell,.facebook-suite .shell-card{border:1px solid rgba(255,255,255,.8);border-radius:24px;background:rgba(255,255,255,.9);box-shadow:0 18px 40px rgba(15,23,42,.06)}
.facebook-suite .hero-shell{background:radial-gradient(circle at top right, rgba(59,130,246,.16), transparent 30%),radial-gradient(circle at left center, rgba(14,165,233,.12), transparent 30%),linear-gradient(135deg,#ffffff 0%,#f8fafc 100%)}
.facebook-suite .hero-eyebrow{display:inline-flex;align-items:center;padding:7px 12px;border-radius:999px;border:1px solid #bfdbfe;background:rgba(255,255,255,.86);color:#1d4ed8;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.facebook-suite .summary-card{border:1px solid rgba(255,255,255,.8);border-radius:20px;background:rgba(255,255,255,.86);box-shadow:0 12px 28px rgba(15,23,42,.05)}
.facebook-suite .summary-card .label{display:block;margin-bottom:8px;color:#64748b;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.facebook-suite .summary-card h3{margin:0;font-size:1.7rem;font-weight:800;letter-spacing:-.03em;color:#0f172a}
.facebook-suite .section-card{border:1px solid #e2e8f0;border-radius:20px;background:#fff;box-shadow:0 10px 24px rgba(15,23,42,.04)}
.facebook-suite .section-intro{border:1px solid #dbeafe;border-radius:18px;background:#f8fbff;padding:16px 18px;margin-bottom:20px}
.facebook-suite .guide-card{border:1px dashed #bfdbfe;border-radius:20px;background:linear-gradient(135deg,rgba(239,246,255,.95),rgba(248,250,252,.95));box-shadow:none}
</style>
@endsection

@section('content')
<div class="page-content facebook-suite">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Social Integration</span>
                                <h2 class="mt-3 mb-2">Facebook Settings</h2>
                                <p class="text-muted mb-0">Connect a Facebook account, select publishing pages, and manage posting defaults from a cleaner integration screen.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="#">Facebook</a></li>
                                        <li class="breadcrumb-item active">Connect</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Channel</span>
                        <h3>Facebook</h3>
                        <p class="text-muted mb-0 mt-2">Manage connection status and page-level publishing controls from one dashboard-style workspace.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Status</span>
                        <h3>{{ isset($connected) && $connected ? 'Live' : 'Pending' }}</h3>
                        <p class="text-muted mb-0 mt-2">See whether the account is already authorized before configuring destination pages.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Pages</span>
                        <h3>{{ isset($pages) ? count($pages) : 0 }}</h3>
                        <p class="text-muted mb-0 mt-2">Available pages can be selected once the Facebook connection handshake is complete.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Workflow</span>
                        <h3>Connect + Save</h3>
                        <p class="text-muted mb-0 mt-2">Authorize first, choose the target page second, then store the posting defaults.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shell-card">
            <div class="card-body">
                <form action="#" method="POST" id="facebookForm">
                    @csrf

                    <div class="section-intro">
                        <h6 class="mb-1">Integration overview</h6>
                        <p class="text-muted mb-0">This setup keeps account authorization and page publishing settings together so social posting can be configured without leaving the main workflow.</p>
                    </div>

                    <div class="row">
                        <div class="col-lg-5">
                            <div class="card section-card mb-3">
                                <div class="card-header bg-transparent">
                                    <h5 class="mb-0"><i class="ri-facebook-circle-fill text-primary me-2"></i>Facebook Connection</h5>
                                </div>
                                <div class="card-body text-center py-4">
                                    @if(isset($connected) && $connected)
                                        <div class="mb-3">
                                            <div class="avatar-lg mx-auto mb-3">
                                                <div class="avatar-title bg-success-subtle text-success rounded-circle fs-1">
                                                    <i class="ri-check-line"></i>
                                                </div>
                                            </div>
                                            <h5 class="text-success">Connected</h5>
                                            <p class="text-muted mb-2">
                                                <strong>{{ $facebookUser['name'] ?? 'Facebook User' }}</strong>
                                            </p>
                                            <p class="text-muted small">
                                                <i class="ri-mail-line me-1"></i> {{ $facebookUser['email'] ?? 'email@example.com' }}
                                            </p>
                                        </div>
                                        <a href="#" class="btn btn-outline-danger btn-sm" onclick="return confirm('Disconnect Facebook account?')">
                                            <i class="ri-link-unlink-m me-1"></i>Disconnect
                                        </a>
                                    @else
                                        <div class="mb-3">
                                            <div class="avatar-lg mx-auto mb-3">
                                                <div class="avatar-title bg-light text-muted rounded-circle fs-1">
                                                    <i class="ri-facebook-circle-line"></i>
                                                </div>
                                            </div>
                                            <h5>Not Connected</h5>
                                            <p class="text-muted">Connect your Facebook account to select a page.</p>
                                            <a href="{{ route('facebooks.login') }}" class="btn btn-primary">
                                                <i class="ri-facebook-fill me-1"></i>Connect with Facebook
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="card section-card">
                                <div class="card-header bg-transparent">
                                    <h5 class="mb-0"><i class="ri-page-line me-2"></i>Facebook Page Settings</h5>
                                </div>
                                <div class="card-body">
                                    <div class="section-intro">
                                        <h6 class="mb-1">Publishing destination</h6>
                                        <p class="text-muted mb-0">Choose the page that should receive posts and lock in the default publishing behavior for your team.</p>
                                    </div>
                                    @if(isset($connected) && $connected)
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Select Facebook Page <span class="text-danger">*</span></label>
                                            <select name="page_id" id="pageSelect" class="form-select @error('page_id') is-invalid @enderror" required>
                                                <option value="">-- Choose a Page --</option>
                                                @if(isset($pages) && count($pages) > 0)
                                                    @foreach($pages as $page)
                                                        <option value="{{ $page['id'] }}"
                                                            data-access-token="{{ $page['access_token'] ?? '' }}"
                                                            {{ old('page_id', $selectedPageId ?? '') == $page['id'] ? 'selected' : '' }}>
                                                            {{ $page['name'] }} ({{ $page['category'] ?? 'Page' }})
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @error('page_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                <i class="ri-information-line me-1"></i> Posts will be published to this page.
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Default Post Template</label>
                                            <select name="template" class="form-select">
                                                <option value="default" {{ old('template') == 'default' ? 'selected' : '' }}>Text + Image</option>
                                                <option value="link" {{ old('template') == 'link' ? 'selected' : '' }}>Link Preview</option>
                                                <option value="video" {{ old('template') == 'video' ? 'selected' : '' }}>Video Post</option>
                                            </select>
                                        </div>

                                        <div class="mb-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="auto_publish" id="autoPublish" value="1"
                                                    {{ old('auto_publish') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="autoPublish">
                                                    Auto-publish scheduled posts
                                                </label>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-success">
                                                <i class="ri-save-line me-1"></i> Save Settings
                                            </button>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="ri-lock-line fs-1 text-muted"></i>
                                            <p class="text-muted mt-2 mb-0">Connect your Facebook account first to configure pages.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-12">
                <div class="card guide-card border-0">
                    <div class="card-body py-3">
                        <h6 class="mb-1"><i class="ri-question-line me-1"></i> How it works:</h6>
                        <ol class="small text-muted mb-0 ps-3">
                            <li>Click <strong>"Connect with Facebook"</strong> and authorize the app.</li>
                            <li>After connection, select your desired Facebook Page from the dropdown.</li>
                            <li>Save settings and you can now post to that page.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Optional: Show selected page info or token handling
        const pageSelect = document.getElementById('pageSelect');
        if (pageSelect) {
            pageSelect.addEventListener('change', function() {
                let selectedOption = this.options[this.selectedIndex];
                let pageName = selectedOption.textContent;
                console.log('Selected page: ' + pageName);
            });
        }

        // Auto-dismiss alerts (if any)
        setTimeout(function() {
            let alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                let bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
</script>
@endsection
