@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- PAGE TITLE -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4>Facebook Settings</h4>
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Facebook</a></li>
                        <li class="breadcrumb-item active">Connect</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="#" method="POST" id="facebookForm">
                    @csrf

                    <!-- ================= FACEBOOK CONNECTION & PAGE ================= -->
                    <div class="row">
                        <!-- Left Column: Connection Status & Button -->
                        <div class="col-lg-5">
                            <div class="card border shadow-none mb-3">
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

                        <!-- Right Column: Page Selection & Settings -->
                        <div class="col-lg-7">
                            <div class="card border shadow-none">
                                <div class="card-header bg-transparent">
                                    <h5 class="mb-0"><i class="ri-page-line me-2"></i>Facebook Page Settings</h5>
                                </div>
                                <div class="card-body">
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

        <!-- Help Card -->
        <div class="row mt-2">
            <div class="col-12">
                <div class="card bg-light border-0">
                    <div class="card-body py-3">
                        <h6 class="mb-1"><i class="ri-question-line me-1"></i> How it works:</h6>
                        <ol class="small text-muted mb-0 ps-3">
                            <li>Click <strong>"Connect with Facebook"</strong> and authorize the app.</li>
                            <li>After connection, select your desired Facebook Page from the dropdown.</li>
                            <li>Save settings – you can now post to that page.</li>
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
