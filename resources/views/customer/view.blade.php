@extends('layouts.app')

@section('page-css')
    <style>
        .detail-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .detail-suite .hero-shell,
        .detail-suite .profile-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
        }

        .detail-suite .hero-shell {
            border-radius: 28px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
        }

        .detail-suite .hero-eyebrow {
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

        .detail-suite .hero-title {
            font-size: clamp(1.9rem, 3vw, 2.5rem);
            line-height: 1.05;
            letter-spacing: -0.04em;
            font-weight: 800;
            margin: 1rem 0 .45rem;
            color: #0f172a;
        }

        .detail-suite .hero-subtitle {
            color: #64748b;
        }

        .detail-suite .profile-shell {
            border-radius: 22px;
        }

        .detail-suite .section-title {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .detail-suite .section-subtitle {
            color: #64748b;
            font-size: .84rem;
        }

        .detail-suite .summary-panel {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 1rem;
        }

        .detail-suite .tab-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 8px;
            gap: 8px;
        }

        .detail-suite .tab-shell .nav-link {
            border: 0;
            border-radius: 14px;
            color: #475569;
            font-weight: 700;
            padding: 10px 16px;
        }

        .detail-suite .tab-shell .nav-link.active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }

        .detail-suite .table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
        }
    </style>
@endsection

@section('content')
    <div class="page-content detail-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="hero-eyebrow">Customer Profile</span>
                                    <h1 class="hero-title">{{ $customer->name }}</h1>
                                    <p class="hero-subtitle mb-0">Follow customer sales history, open balances, leads, quotations, invoices, and activities from one cleaner profile screen.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
                                            <li class="breadcrumb-item active">Details</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- LEFT: CUSTOMER CARD -->
                <div class="col-xl-3">
                    <div class="card profile-shell">
                        <div class="card-body text-center pb-3">
                            <div class="position-relative d-inline-block">
                                @php
                                    $name = trim($customer->name);
                                    $words = explode(' ', $name);

                                    if (count($words) >= 2) {
                                        $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                                    } else {
                                        $initials = strtoupper(substr($words[0], 0, 1));
                                    }
                                @endphp

                                <div
                                    class="avatar-xl rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center">
                                    <span class="fs-1 text-primary">{{ $initials }}</span>
                                </div>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                                    Active
                                </span>
                            </div>
                            <h4 class="mt-3 mb-1">{{ $customer->name }}</h4>
                            <p class="text-muted mb-2">
                                {{ $customer->type === 'customer' ? $customer?->getBillingAddress?->address_line_1 : '-' }}

                            </p>
                            {{-- <p class="text-muted mb-0">Customer Code: <span class="fw-semibold">CUST-001</span></p> --}}
                        </div>

                        <div class="card-body border-top">
                            <h6 class="text-muted text-uppercase fs-12 mb-3">Contact Details</h6>
                            <div class="d-flex mb-2">
                                <div class="flex-shrink-0 me-2">
                                    <i class="ri-mail-line text-primary align-middle"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-0">{{ $customer->email }}</p>
                                </div>
                            </div>
                            <div class="d-flex mb-2">
                                <div class="flex-shrink-0 me-2">
                                    <i class="ri-phone-line text-primary align-middle"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-0">
                                        {{ optional($customer->getCustomerPhone->where('is_primary', 1)->first())->phone }}
                                    </p>
                                </div>
                            </div>
                            <div class="d-flex mb-2">
                                <div class="flex-shrink-0 me-2">
                                    <i class="ri-user-line text-primary align-middle"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-0">Contact Person: <span
                                            class="fw-semibold">{{ $customer->name }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-2">
                                    <i class="ri-map-pin-line text-primary align-middle"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-0">{{ $customer?->getBillingAddress?->address_line_1 }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="card-body border-top">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="text-muted text-uppercase fs-12 mb-0">Account Summary</h6>
                                <span class="badge bg-primary-subtle text-primary">
                                    <i class="ri-pie-chart-2-line align-middle me-1"></i> Overview
                                </span>
                            </div>

                            <div class="summary-panel">
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            <span
                                                class="avatar-xs bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center me-2">
                                                <i class="ri-bar-chart-line"></i>
                                            </span>
                                            <span class="text-muted">Total Sales</span>
                                        </div>
                                        <span class="fw-semibold fs-6">₹ {{ number_format($totalAmount, 2) }}</span>
                                    </li>

                                    <li class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            <span
                                                class="avatar-xs bg-danger-subtle text-danger rounded-circle d-inline-flex align-items-center justify-content-center me-2">
                                                <i class="ri-alert-line"></i>
                                            </span>
                                            <span class="text-muted">Outstanding</span>
                                        </div>
                                        <span class="fw-semibold fs-6 text-danger">₹ {{ $customer->due_amount }}</span>
                                    </li>

                                    <li class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            <span
                                                class="avatar-xs bg-success-subtle text-success rounded-circle d-inline-flex align-items-center justify-content-center me-2">
                                                <i class="ri-shopping-bag-3-line"></i>
                                            </span>
                                            <span class="text-muted">Total Orders</span>
                                        </div>
                                        <span class="fw-semibold fs-6">{{ $totalOrders }}</span>
                                    </li>

                                    <li class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <span
                                                class="avatar-xs bg-info-subtle text-info rounded-circle d-inline-flex align-items-center justify-content-center me-2">
                                                <i class="ri-time-line"></i>
                                            </span>
                                            <span class="text-muted">Last Order</span>
                                        </div>
                                        <span
                                            class="fw-semibold fs-6">{{ $lastOrder ? $lastOrder->created_at->format('d-M-Y') : 'N/A' }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {{-- <div class="card-body border-top">
                            <h6 class="text-muted text-uppercase fs-12 mb-3">Tags</h6>
                            <span class="badge bg-info-subtle text-info me-1">Key Account</span>
                            <span class="badge bg-warning-subtle text-warning me-1">Credit 30 Days</span>
                            <span class="badge bg-success-subtle text-success">Seasonal Bulk</span>
                        </div> --}}
                    </div>
                </div>
                <!-- RIGHT: TABS -->
                <div class="col-xl-9">
                    <div class="card profile-shell">
                        <div class="card-header d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1 section-title">Customer Activity</h5>
                                <div class="section-subtitle">Leads, quotations, invoices, orders, and recent actions linked to this customer.</div>
                            </div>
                            <div class="flex-shrink-0">
                                <a class="btn btn-sm btn-primary me-1"
                                    href="{{ route('quotes.create', [$customer->id]) }}">
                                    <i class="ri-file-list-3-line me-1"></i>New Quotation
                                </a>
                                <a class="btn btn-sm btn-success" href="{{ route('invoices.create', [$customer->id]) }}">
                                    <i class="ri-shopping-bag-3-line me-1"></i>New Invoice
                                </a>
                            </div>
                        </div>

                        <div class="card-header">
	                            <ul class="nav nav-tabs card-header-tabs tab-shell" role="tablist">
	                                <li class="nav-item">
	                                    <a class="nav-link {{ request()->has('customer_activities_page') ? '' : 'active' }}" data-bs-toggle="tab" href="#leads-tab" role="tab">
	                                        <i class="ri-lightbulb-line me-1"></i>Leads
	                                    </a>
	                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#quotation-tab" role="tab">
                                        <i class="ri-file-list-3-line me-1"></i>Quotations
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#invoices-tab" role="tab">
                                        <i class="ri-bill-line me-1"></i>Invoices
                                    </a>
                                </li>
	                                <li class="nav-item">
	                                    <a class="nav-link" data-bs-toggle="tab" href="#orders-tab" role="tab">
	                                        <i class="ri-shopping-bag-3-line me-1"></i>Orders
	                                    </a>
	                                </li>
                                    <li class="nav-item">
	                                    <a class="nav-link {{ request()->has('customer_activities_page') ? 'active' : '' }}" data-bs-toggle="tab" href="#activities-tab" role="tab">
	                                        <i class="ri-history-line me-1"></i>Activities
	                                    </a>
	                                </li>
	                            </ul>
	                        </div>

	                        <div class="card-body">
	                            <div class="tab-content">

	                                <!-- LEADS -->
	                                <div class="tab-pane {{ request()->has('customer_activities_page') ? '' : 'active' }}" id="leads-tab" role="tabpanel">
	                                    <div class="table-responsive table-wrap">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Lead #</th>
                                                    <th>Title</th>
                                                    <th>Stage</th>
                                                    <th>Created</th>
                                                    <th>Owner</th>
                                                </tr>

                                            </thead>
                                            <tbody>
                                                @foreach ($customer->leads as $lead)
                                                    <tr>
                                                        <td><a href="#"
                                                                class="fw-semibold">Lead-{{ $loop->iteration }}</a></td>
                                                        <td>{{ $lead->name }}</td>
                                                        <td>
                                                            <span class="badge"
                                                                style="
                                                                    background-color: {{ $lead->stage->color }}20;
                                                                    color: {{ $lead->stage->color }};
                                                                    border: 1px solid {{ $lead->stage->color }};
                                                                ">
                                                                {{ $lead->stage->name }}
                                                            </span>
                                                        </td>
                                                        <td>{{ optional($lead->created_at)->format('d-M-Y') ?? '-' }}</td>
                                                        <td>{{ $lead->customer->name ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- QUOTATIONS -->
                                <div class="tab-pane" id="quotation-tab" role="tabpanel">
                                    <div class="table-responsive table-wrap">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Quote #</th>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Valid Till</th>
                                                    <th>Status</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($quotations as $quotation)
                                                    <tr>
                                                        <td><a href="#"
                                                                class="fw-semibold">{{ $quotation->code }}</a></td>
                                                        <td>{{ optional($quotation->created_at)->format('d-M-Y') ?? '-' }}
                                                        </td>
                                                        <td>₹ {{ $quotation->grand_total ?? '-' }}</td>
                                                        <td>{{ optional($quotation->created_at)?->addDays(30)->format('d-M-Y') ?? '-' }}
                                                        </td>
                                                        <td>
                                                            @php
                                                                $statuses = [
                                                                    1 => [
                                                                        'label' => 'Pending',
                                                                        'class' => 'bg-warning-subtle text-warning',
                                                                    ],
                                                                    2 => [
                                                                        'label' => 'Sent',
                                                                        'class' => 'bg-info-subtle text-info',
                                                                    ],
                                                                    3 => [
                                                                        'label' => 'Final',
                                                                        'class' => 'bg-success-subtle text-success',
                                                                    ],
                                                                ];

                                                                $status = $statuses[$quotation->status] ?? [
                                                                    'label' => 'Unknown',
                                                                    'class' => 'bg-secondary-subtle text-secondary',
                                                                ];
                                                            @endphp

                                                            <span class="badge {{ $status['class'] }}">
                                                                {{ $status['label'] }}
                                                            </span>
                                                        </td>

                                                        <td>
                                                            @if($quotation->status !=3)
                                                                <a href="{{ route('quotes.edit_status',[$quotation->id]) }}" class="btn btn-sm btn-soft-primary">View</a>
                                                            @endif
                                                        </td>

                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- INVOICES -->
                                <div class="tab-pane" id="invoices-tab" role="tabpanel">
                                    <div class="table-responsive table-wrap">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Invoice #</th>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Paid</th>
                                                    <th>Balance</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($orders as $order)
                                                    <tr>
                                                        <td>
                                                            <a href="#" class="fw-semibold">
                                                                {{ 'INV-' . substr($order->order_number, 6) }}
                                                            </a>
                                                        </td>
                                                        <td>{{ optional($order->created_at)->format('d-M-Y') ?? '-' }}</td>
                                                        <td>₹{{ $order->grand_total }}</td>
                                                        <td>₹{{ $order->grand_total - $order->remaining_payment }}</td>
                                                        <td>₹{{ $order->remaining_payment }}</td>
                                                        <td>
                                                            <span
                                                                class="badge {{ strtolower($order->payment_status) === 'unpaid' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                                                                {{ ucfirst($order->payment_status) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

	                                <!-- ORDERS -->
	                                <div class="tab-pane" id="orders-tab" role="tabpanel">
	                                    <div class="table-responsive table-wrap">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Order #</th>
                                                    <th>Date</th>
                                                    <th>Items</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Payment</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($orders as $order)
                                                    <tr>
                                                        <td><a href="#"
                                                                class="fw-semibold">{{ $order->order_number }}</a></td>
                                                        <td>{{ optional($order->created_at)->format('d-M-Y') ?? '-' }}
                                                        </td>
                                                        <td>{{ $order->orderProducts->count() }}</td>
                                                        <td>₹{{ $order->grand_total }}</td>
                                                        <td>
                                                            <span class="badge"
                                                                style="background-color: {{ $order->Orderstatus->color }}; color: #fff;">
                                                                {{ $order->Orderstatus->name }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="badge {{ strtolower($order->payment_status) === 'unpaid' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                                                                {{ ucfirst($order->payment_status) }}
                                                            </span>
                                                        </td>

                                                    </tr>
                                                @endforeach
                                            </tbody>
	                                        </table>
	                                    </div>
	                                </div>
                                    <div class="tab-pane {{ request()->has('customer_activities_page') ? 'active' : '' }}" id="activities-tab" role="tabpanel">
                                        @include('activity._timeline', [
                                            'activities' => $activityTimeline,
                                            'emptyMessage' => 'No activity found for this customer.',
                                        ])
                                    </div>
	                            </div><!-- tab-content -->
	                        </div><!-- card-body -->
                    </div><!-- card -->
                </div>
            </div>


        </div>
    </div>
@endsection
