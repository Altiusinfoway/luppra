@extends('layouts.app')

@section('page-css')
    <style>
        .product-activity-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .product-activity-suite .hero-card,
        .product-activity-suite .shell-card,
        .product-activity-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 24px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.06);
        }

        .product-activity-suite .hero-card {
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .product-activity-suite .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.76);
            border: 1px solid #dbeafe;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .product-activity-suite .summary-label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .product-activity-suite .stock-feed-item {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 1rem 1.1rem;
            background: #fff;
        }
    </style>
@endsection

@section('content')
    <div class="page-content product-activity-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="hero-card mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="eyebrow">Product Activity</span>
                                    <h2 class="mt-3 mb-2">{{ $product->name }}</h2>
                                    <p class="text-muted mb-0">SKU: {{ $product->sku_code }}. Review quantity updates, who changed them, and all major product edits from one clean history screen.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end align-items-center gap-2 flex-wrap">
                                        <a href="{{ route('products.marketplace', $product->id) }}" class="btn btn-primary btn-sm">
                                            <i class="ri-eye-line align-middle me-1"></i>View Product
                                        </a>
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
                                            <li class="breadcrumb-item active">Activity</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="summary-card h-100">
                        <div class="card-body">
                            <span class="summary-label">Current Stock</span>
                            <h3 class="mb-1">{{ number_format((float) ($product->stock_qty ?? 0)) }}</h3>
                            <p class="text-muted mb-0">Latest available quantity on the master product.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card h-100">
                        <div class="card-body">
                            <span class="summary-label">Qty Change Logs</span>
                            <h3 class="mb-1">{{ $stockActivities->total() }}</h3>
                            <p class="text-muted mb-0">Separate stock history with user and timestamp.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card h-100">
                        <div class="card-body">
                            <span class="summary-label">Product Change Logs</span>
                            <h3 class="mb-1">{{ $activityTimeline->total() }}</h3>
                            <p class="text-muted mb-0">Field-level product updates captured in the activity timeline.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-xl-5">
                    <div class="shell-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                <div>
                                    <h5 class="mb-1">Quantity Change History</h5>
                                    <p class="text-muted mb-0">Who changed stock and when it happened.</p>
                                </div>
                            </div>

                            @if ($stockActivities->count())
                                <div class="d-grid gap-3">
                                    @foreach ($stockActivities as $stockActivity)
                                        <div class="stock-feed-item">
                                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                <h6 class="mb-0">{{ optional($stockActivity->created_user)->name ?: 'System' }}</h6>
                                                <span class="badge bg-primary-subtle text-primary">Stock Update</span>
                                                <small class="text-muted ms-auto">{{ optional($stockActivity->date_time)->format('d M Y, h:i A') }}</small>
                                            </div>
                                            <p class="text-muted mb-0">{{ $stockActivity->message }}</p>
                                        </div>
                                    @endforeach
                                </div>

                                @if ($stockActivities->hasPages())
                                    <div class="mt-3">
                                        {{ $stockActivities->onEachSide(1)->links() }}
                                    </div>
                                @endif
                            @else
                                <div class="text-center text-muted py-4">No quantity activity found for this product yet.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-xl-7">
                    <div class="shell-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                <div>
                                    <h5 class="mb-1">Product Update Timeline</h5>
                                    <p class="text-muted mb-0">Track what fields changed on the product record.</p>
                                </div>
                            </div>

                            @include('activity._timeline', [
                                'activities' => $activityTimeline,
                                'emptyMessage' => 'No product update history found yet.',
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
