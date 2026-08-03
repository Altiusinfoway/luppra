<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">

@include('layouts.inc.head')

<body>
    <div class="error-page-shell py-5 d-flex justify-content-center align-items-center min-vh-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-9">
                    <div class="card border-0 shadow-lg overflow-hidden">
                        <div class="card-body p-0">
                            <div class="row g-0 align-items-stretch">
                                <div class="col-lg-6">
                                    <div class="h-100 p-4 p-lg-5 d-flex flex-column justify-content-center"
                                        style="background: radial-gradient(circle at top left, rgba(37, 99, 235, 0.12), transparent 38%), radial-gradient(circle at bottom right, rgba(15, 118, 110, 0.12), transparent 32%), linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
                                        <span class="badge bg-primary-subtle text-primary mb-3 align-self-start">Error 404</span>
                                        <h1 class="mb-3" style="font-size: clamp(2.2rem, 5vw, 3.5rem);">Page not found</h1>
                                        <p class="text-muted mb-4">
                                            The page you tried to open is not available anymore, or the link is incomplete.
                                        </p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('home') }}" class="btn btn-primary">
                                                <i class="mdi mdi-home me-1"></i>Back to home
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="h-100 d-flex align-items-center justify-content-center p-4 p-lg-5 bg-light-subtle">
                                        <img src="{{ asset('public/build/assets/images/error400-cover.png') }}"
                                            alt="404 illustration" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
