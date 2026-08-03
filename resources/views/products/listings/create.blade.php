@extends('layouts.app')

@section('page-css')
    <style>
        .listing-form-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .listing-form-suite .hero-shell,
        .listing-form-suite .form-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
        }

        .listing-form-suite .hero-eyebrow {
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
    </style>
@endsection

@section('content')
    <div class="page-content listing-form-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="hero-eyebrow">Marketplace Listing</span>
                                    <h1 class="mb-3">Add Marketplace Listing</h1>
                                    <p class="text-muted mb-0">{{ $product->name }} under master SKU {{ $product->sku_code }}. Add a marketplace-ready Amazon or Flipkart listing and attach it to the right seller account for separate stock control.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
                                            <li class="breadcrumb-item"><a href="{{ route('products.marketplace', $product->id) }}">Marketplace</a></li>
                                            <li class="breadcrumb-item active">Add Listing</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-xl-8">
                    <div class="card form-shell">
                        <div class="card-header">
                            <h5 class="card-title mb-1">Create Listing</h5>
                            <p class="text-muted mb-0">Add a new Amazon or Flipkart listing for this master product and choose the marketplace account it belongs to.</p>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('products.marketplace.listings.store', $product->id) }}" method="POST">
                                @csrf
                                @include('products.listings._form')
                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('products.marketplace', $product->id) }}" class="btn btn-light">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Save Listing</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
