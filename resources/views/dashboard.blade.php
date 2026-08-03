@extends('layouts.app')

@section('page-css')
    <style>
        .dashboard-home {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .dashboard-home .hero-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 28px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
        }

        .dashboard-home .hero-eyebrow {
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

        .dashboard-home .hero-title {
            font-size: clamp(2rem, 3vw, 2.85rem);
            font-weight: 800;
            letter-spacing: -0.04em;
            margin: 1rem 0 .45rem;
            color: #0f172a;
        }

        .dashboard-home .card {
            border-radius: 22px !important;
        }

        .dashboard-home .card-header {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .dashboard-home .dashboard-card {
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(255, 255, 255, 0.78);
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
        }

        .dashboard-home .section-title {
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #0f172a;
        }

        .dashboard-home .section-subtitle {
            color: #64748b;
            font-size: 0.85rem;
        }

        .dashboard-home .soft-panel {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 0.85rem 1rem;
        }

        .dashboard-home .control-cluster {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: end;
        }

        .dashboard-home .control-group label {
            display: block;
            margin-bottom: 0.35rem;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .dashboard-home .chart-filter {
            min-width: 160px;
            border-radius: 14px;
            border: 1px solid #dbe4f0;
            background: rgba(255, 255, 255, 0.92);
            color: #0f172a;
            padding: 0.65rem 0.8rem;
        }

        .dashboard-home .report-trigger {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.55rem 0.8rem;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8 !important;
            font-weight: 700;
            text-decoration: none;
        }

        .dashboard-home .table-summary {
            color: #64748b;
            font-size: 0.84rem;
        }
    </style>
@endsection

@section('content')

    <div class="page-content dashboard-home">
        <div class="container-fluid mb-4">
            <div class="hero-shell">
                <div class="card-body p-4 p-lg-5">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-8">
                            <span class="hero-eyebrow">Operations Overview</span>
                            <h1 class="hero-title">Dashboard</h1>
                            <p class="text-muted mb-0">A cleaner command center for charts, sales activity, lead movement, and day-to-day business visibility.</p>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex justify-content-lg-end">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Overview</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(\Auth::user()->type != 'Employee')

        <div class="row">
            <div class="col-md-9">
                <div class="row">
                    <!-- end col -->
                    <div class="col-xl-12">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <h4 class="card-title mb-1 section-title">Credit & Debit Trend</h4>
                                        <div class="section-subtitle">Combined cash movement and transaction direction.</div>
                                    </div>
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body">
                                <div
                                    id="multi_chart"
                                    class="apex-charts"
                                    data-colors='["--vz-primary", "--vz-info", "--vz-success"]'
                                    dir="ltr"></div>
                            </div><!-- end card-body -->
                        </div><!-- end card -->
                    </div>
                    <div class="col-xl-6">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                    <div>
                                        <h4 class="card-title mb-1 section-title">Call Attempt vs Connected</h4>
                                        <div class="section-subtitle">Track outreach activity by month and staff.</div>
                                    </div>
                                    <div class="soft-panel">
                                        <div class="control-cluster">
                                            <div class="control-group">
                                                <label for="monthFilter">Month</label>
                                                <select id="monthFilter" class="chart-filter" onchange="updateCharts()">
                                            <option value="January">January</option>
                                            <option value="February">February</option>
                                            <option value="March">March</option>
                                            <option value="April">April</option>
                                            <option value="May">May</option>
                                            <option value="June">June</option>
                                            <option value="July">July</option>
                                            <option value="August">August</option>
                                            <option value="September">September</option>
                                            <option value="October">October</option>
                                            <option value="November">November</option>
                                            <option value="December">December</option>
                                                </select>
                                            </div>
                                            <div class="control-group">
                                                <label for="staffFilter">Staff</label>
                                                <select id="staffFilter" class="chart-filter" onchange="updateCharts()">
                                            <option value="All">All</option>
                                            <option value="Staff 1">Staff 1</option>
                                            <option value="Staff 2">Staff 2</option>
                                            <option value="Staff 3">Staff 3</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body">
                                <!-- Chart Containers -->
                                <div id="attemptVsConnectedChart"></div>

                            </div><!-- end card-body -->
                        </div><!-- end card -->
                    </div>
                    <div class="col-xl-6">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <div>
                                    <h4 class="card-title mb-1 section-title">Conversation Ratio</h4>
                                    <div class="section-subtitle">Measure how much outreach turns into real conversations.</div>
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body">
                                <div id="conversationRatioChart"></div>
                            </div><!-- end card-body -->
                        </div><!-- end card -->
                    </div>
                    <!-- end col -->
                    <div class="col-xl-4">
                        <div class="card dashboard-card card-height-100">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Lead Status</h4>
                                <div class="flex-shrink-0">
                                    <div class="dropdown card-header-dropdown">
                                        <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="report-trigger">Report<i class="mdi mdi-chevron-down"></i></span>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="#">Download Report</a>
                                            <a class="dropdown-item" href="#">Export</a>
                                            <a class="dropdown-item" href="#">Import</a>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body">
                                <div
                                    id="store-visits-source"
                                    data-colors='["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]'
                                    data-colors-minimal='["--vz-primary", "--vz-primary-rgb, 0.85", "--vz-primary-rgb, 0.70", "--vz-primary-rgb, 0.60", "--vz-primary-rgb, 0.45"]' data-colors-interactive='["--vz-primary", "--vz-primary-rgb, 0.85", "--vz-primary-rgb, 0.70", "--vz-primary-rgb, 0.60", "--vz-primary-rgb, 0.45"]' data-colors-galaxy='["--vz-primary", "--vz-primary-rgb, 0.85", "--vz-primary-rgb, 0.70", "--vz-primary-rgb, 0.60", "--vz-primary-rgb, 0.45"]' class="apex-charts" dir="ltr"></div>
                            </div>
                        </div> <!-- .card-->
                    </div> <!-- .col-->
                    <!-- end col -->
                    <div class="col-xl-4">
                        <div class="card dashboard-card card-height-100">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Lead Source</h4>
                                <div class="flex-shrink-0">
                                    <div class="dropdown card-header-dropdown">
                                        <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="report-trigger">Report<i class="mdi mdi-chevron-down"></i></span>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="#">Download Report</a>
                                            <a class="dropdown-item" href="#">Export</a>
                                            <a class="dropdown-item" href="#">Import</a>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body">
                                <div
                                    id="lead-source-chart"
                                    data-colors='["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]'
                                    data-colors-minimal='["--vz-primary", "--vz-primary-rgb, 0.85", "--vz-primary-rgb, 0.70", "--vz-primary-rgb, 0.60", "--vz-primary-rgb, 0.45"]' data-colors-interactive='["--vz-primary", "--vz-primary-rgb, 0.85", "--vz-primary-rgb, 0.70", "--vz-primary-rgb, 0.60", "--vz-primary-rgb, 0.45"]' data-colors-galaxy='["--vz-primary", "--vz-primary-rgb, 0.85", "--vz-primary-rgb, 0.70", "--vz-primary-rgb, 0.60", "--vz-primary-rgb, 0.45"]' class="apex-charts" dir="ltr"></div>
                            </div>
                        </div> <!-- .card-->
                    </div> <!-- .col-->
                    <!-- end col -->
                    <div class="col-xl-4">
                        <div class="card dashboard-card card-height-100">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Order status</h4>
                                <div class="flex-shrink-0">
                                    <div class="dropdown card-header-dropdown">
                                        <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="report-trigger">Report<i class="mdi mdi-chevron-down"></i></span>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="#">Download Report</a>
                                            <a class="dropdown-item" href="#">Export</a>
                                            <a class="dropdown-item" href="#">Import</a>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body">
                                <div
                                    id="order-status-chart"
                                    data-colors='["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]'
                                    data-colors-minimal='["--vz-primary", "--vz-primary-rgb, 0.85", "--vz-primary-rgb, 0.70", "--vz-primary-rgb, 0.60", "--vz-primary-rgb, 0.45"]' data-colors-interactive='["--vz-primary", "--vz-primary-rgb, 0.85", "--vz-primary-rgb, 0.70", "--vz-primary-rgb, 0.60", "--vz-primary-rgb, 0.45"]' data-colors-galaxy='["--vz-primary", "--vz-primary-rgb, 0.85", "--vz-primary-rgb, 0.70", "--vz-primary-rgb, 0.60", "--vz-primary-rgb, 0.45"]' class="apex-charts" dir="ltr"></div>
                            </div>
                        </div> <!-- .card-->
                    </div> <!-- .col-->

                    <div class="col-xl-12">
                        <div class="card dashboard-card card-height-100">
                            <div class="card-header align-items-center d-flex">
                                <div class="flex-grow-1">
                                    <h4 class="card-title mb-1 section-title">Sales Report</h4>
                                    <div class="section-subtitle">Recent sales activity by salesperson.</div>
                                </div>
                                <div class="flex-shrink-0">
                                    <select class="form-select form-select-sm p-none chart-filter" aria-label=".form-select-sm example" disabled>
                                        <option selected="" desable>Filter</option>
                                        <option value="1">Active Deals</option>
                                        <option value="2">Paused Deals</option>
                                        <option value="3">Canceled Deals</option>
                                    </select>
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-nowrap align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col" style="width: 30%;">Salesperson</th>
                                                <th scope="col">New Leads</th>
                                                <th scope="col"> In Progress</th>
                                                <th scope="col"> Lost Leads</th>
                                                <th scope="col"> Won Leads </th>
                                                <th scope="col"> Total Value (Won) </th>
                                                <th scope="col"> Date</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr>
                                                <td><img src="assets/images/users/avatar-1.jpg" alt="" class="avatar-xs rounded-circle me-2 material-shadow">
                                                    <a href="#javascript: void(0);" class="text-body fw-medium">John Doe</a>
                                                </td>
                                                <td>5</td>
                                                <td>10</td>
                                                <td>3</td>
                                                <td>7</td>
                                                <td>15,000</td>
                                                <td>20 jan 2025</td>
                                            </tr>
                                            <tr>
                                                <td><img src="assets/images/users/avatar-2.jpg" alt="" class="avatar-xs rounded-circle me-2 material-shadow">
                                                    <a href="#javascript: void(0);" class="text-body fw-medium">Jansh Brown</a>
                                                </td>
                                                <td>5</td>
                                                <td>10</td>
                                                <td>3</td>
                                                <td>7</td>
                                                <td>15,000</td>
                                                <td>20 jan 2025</td>
                                            </tr>
                                            <tr>
                                                <td><img src="assets/images/users/avatar-7.jpg" alt="" class="avatar-xs rounded-circle me-2 material-shadow">
                                                    <a href="#javascript: void(0);" class="text-body fw-medium">Ayaan Hudda</a>
                                                </td>
                                                <td>5</td>
                                                <td>10</td>
                                                <td>3</td>
                                                <td>7</td>
                                                <td>15,000</td>
                                                <td>20 jan 2025</td>
                                            </tr>
                                            <tr>
                                                <td><img src="assets/images/users/avatar-4.jpg" alt="" class="avatar-xs rounded-circle me-2 material-shadow">
                                                    <a href="#javascript: void(0);" class="text-body fw-medium">Julia William</a>
                                                </td>
                                                <td>5</td>
                                                <td>10</td>
                                                <td>3</td>
                                                <td>7</td>
                                                <td>15,000</td>
                                                <td>20 jan 2025</td>
                                            </tr>
                                        </tbody><!-- end tbody -->
                                    </table><!-- end table -->
                                </div><!-- end table responsive -->
                            </div><!-- end card body -->

                            <!-- View More Button -->
                            <div class="text-center mb-3 table-summary">
                                <button id="viewMoreBtn" class="btn btn-primary">View More</button>
                            </div>
                        </div><!-- end card -->
                    </div><!-- end col -->

                    <div class="col-xl-12">
                        <div class="card dashboard-card card-height-100">
                            <div class="card-header align-items-center d-flex">
                                <div class="flex-grow-1">
                                    <h4 class="card-title mb-1 section-title">Call Report</h4>
                                    <div class="section-subtitle">Call volume and outreach summary by staff.</div>
                                </div>
                                <div class="flex-shrink-0">
                                    <select class="form-select form-select-sm p-none chart-filter" aria-label=".form-select-sm example" disabled>
                                        <option selected="" desable>Filter</option>
                                        <option value="1">Active Deals</option>
                                        <option value="2">Paused Deals</option>
                                        <option value="3">Canceled Deals</option>
                                    </select>
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-nowrap align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col" style="width: 30%;">Staff Name</th>
                                                <th scope="col">Total Calls</th>
                                                <th scope="col"> Answered </th>
                                                <th scope="col"> Missed</th>
                                                <th scope="col"> Follow-ups </th>
                                                <th scope="col"> Call Duration </th>
                                                <th scope="col"> Date</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr>
                                                <td><img src="assets/images/users/avatar-1.jpg" alt="" class="avatar-xs rounded-circle me-2 material-shadow">
                                                    <a href="#javascript: void(0);" class="text-body fw-medium">John Doe</a>
                                                </td>
                                                <td>50</td>
                                                <td>30</td>
                                                <td>3</td>
                                                <td>7</td>
                                                <td>3h 20m</td>
                                                <td>20 jan 2025</td>
                                            </tr>
                                            <tr>
                                                <td><img src="assets/images/users/avatar-2.jpg" alt="" class="avatar-xs rounded-circle me-2 material-shadow">
                                                    <a href="#javascript: void(0);" class="text-body fw-medium">Jansh Brown</a>
                                                </td>
                                                <td>50</td>
                                                <td>30</td>
                                                <td>3</td>
                                                <td>7</td>
                                                <td>3h 20m</td>
                                                <td>20 jan 2025</td>
                                            </tr>
                                            <tr>
                                                <td><img src="assets/images/users/avatar-7.jpg" alt="" class="avatar-xs rounded-circle me-2 material-shadow">
                                                    <a href="#javascript: void(0);" class="text-body fw-medium">Ayaan Hudda</a>
                                                </td>
                                                <td>50</td>
                                                <td>30</td>
                                                <td>3</td>
                                                <td>7</td>
                                                <td>3h 20m</td>
                                                <td>20 jan 2025</td>
                                            </tr>
                                        </tbody><!-- end tbody -->
                                    </table><!-- end table -->
                                </div><!-- end table responsive -->
                            </div><!-- end card body -->

                            <!-- View More Button -->
                            <div class="text-center mb-3">
                                <button id="viewMoreBtn" class="btn btn-primary">View More</button>
                            </div>
                        </div><!-- end card -->
                    </div><!-- end col -->




                </div> <!-- end row-->
            </div>
            <div class="col-md-3">
                <div class="card card-height-100">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Sticky Notes</h4>
                        <a href="javascript:;" class="btn btn-primary waves-effect waves-light mx-4" id="add_new">Add New Note</a>
                    </div>

                    <div id="board">

                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12">
                <div class="card card-height-100">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Order Report</h4>
                        <div class="flex-shrink-0">
                            <select class="form-select form-select-sm p-none" aria-label=".form-select-sm example" disabled>
                                <option selected="" desable>Filter</option>
                                <option value="1">Active Deals</option>
                                <option value="2">Paused Deals</option>
                                <option value="3">Canceled Deals</option>
                            </select>
                        </div>
                    </div><!-- end card header -->

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class=" table table-bordered table-nowrap align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">Order ID</th>
                                        <th scope="col">Customer Name</th>
                                        <th scope="col">Product (Qty + Price) </th>
                                        <th scope="col"> Raw Materia (Name + Used Qty) </th>
                                        <th scope="col"> Finished Produc (Name + Qty)</th>
                                        <th scope="col"> Packaging (Name + Qty + Bill/Bilty) </th>
                                        <th scope="col"> Inventory Cost </th>
                                        <th scope="col"> Date</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td>ORD1002</td>
                                        <td>
                                            Jane Smith
                                        </td>
                                        <td>Widget B (5 x $80)</td>
                                        <td>Copper (3 Kg), Rubber (1 Kg)</td>
                                        <td>Assembled Widget B (5)</td>
                                        <td>Box (5), Label (5), Bilty#456</td>
                                        <td>4000</td>
                                        <td>20 jan 2025</td>
                                    </tr>
                                    <tr>
                                        <td>ORD1003</td>
                                        <td>
                                            Mike Brown
                                        </td>
                                        <td>Widget C (8 x $60)</td>
                                        <td>Aluminum (4 Kg), Nylon (2 Kg)</td>
                                        <td>Assembled Widget C (8)</td>
                                        <td>Carton (8), Wrap (8), Bill#789</td>
                                        <td>4800</td>
                                        <td>20 jan 2025</td>
                                    </tr>
                                    <tr>
                                        <td>ORD1001</td>
                                        <td>
                                            John Doe
                                        </td>
                                        <td>Widget A (10 x $50)</td>
                                        <td>Steel (5 Kg), Plastic (2 Kg)</td>
                                        <td>Assembled Widget A (10)</td>
                                        <td>Box (10), Tape (2 Rolls), Bill#123</td>
                                        <td>5000</td>
                                        <td>20 jan 2025</td>
                                    </tr>

                                </tbody><!-- end tbody -->
                            </table><!-- end table -->
                        </div><!-- end table responsive -->
                    </div>
                    <!-- end card body -->
                </div><!-- end card -->
            </div><!-- end col -->

            <div class="col-xl-12">
                <div class="card card-height-100">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">On Process Inventory Report</h4>
                        <div class="flex-shrink-0">
                            <select class="form-select form-select-sm p-none" aria-label=".form-select-sm example" disabled>
                                <option selected="" desable>Filter</option>
                                <option value="1">Active Deals</option>
                                <option value="2">Paused Deals</option>
                                <option value="3">Canceled Deals</option>
                            </select>
                        </div>
                    </div><!-- end card header -->

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-nowrap align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">Inventory Type</th>
                                        <th scope="col">Item Name</th>
                                        <th scope="col">Inventory Quantity (Kg)</th>
                                        <th scope="col">Total Costing ($)</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Raw Material</td>
                                        <td>Steel</td>
                                        <td>500 Kg</td>
                                        <td>10,000</td>
                                        <td>Locked</td>
                                        <td>20 Jan 2025</td>
                                    </tr>
                                    <tr>
                                        <td>Raw Material</td>
                                        <td>Copper</td>
                                        <td>300 Kg</td>
                                        <td>7,500</td>
                                        <td>Locked</td>
                                        <td>20 Jan 2025</td>
                                    </tr>
                                    <tr>
                                        <td>Finished Product</td>
                                        <td>Widget A</td>
                                        <td>100 Units</td>
                                        <td>15,000</td>
                                        <td>Locked</td>
                                        <td>20 Jan 2025</td>
                                    </tr>
                                    <tr>
                                        <td>Finished Product</td>
                                        <td>Widget B</td>
                                        <td>50 Units</td>
                                        <td>8,000</td>
                                        <td>Locked</td>
                                        <td>20 Jan 2025</td>
                                    </tr>
                                    <tr>
                                        <td>Packaging Material</td>
                                        <td>Carton Box</td>
                                        <td>200 Units</td>
                                        <td>2,500</td>
                                        <td>Locked</td>
                                        <td>20 Jan 2025</td>
                                    </tr>
                                    <tr>
                                        <td>Packaging Material</td>
                                        <td>Plastic Wrap</td>
                                        <td>150 Rolls</td>
                                        <td>1,800</td>
                                        <td>Locked</td>
                                        <td>20 Jan 2025</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>


                </div><!-- end card -->
            </div><!-- end col -->

            <div class="col-xl-12">
                <div class="card card-height-100">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Available Inventory Report</h4>
                        <div class="flex-shrink-0">
                            <select class="form-select form-select-sm p-none" aria-label=".form-select-sm example" disabled>
                                <option selected="" desable>Filter</option>
                                <option value="1">Active Deals</option>
                                <option value="2">Paused Deals</option>
                                <option value="3">Canceled Deals</option>
                            </select>
                        </div>
                    </div><!-- end card header -->

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-nowrap align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">Inventory Type</th>
                                        <th scope="col">Item Name</th>
                                        <th scope="col">Inventory Quantity (Kg)</th>
                                        <th scope="col">Total Costing ($)</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Raw Material</td>
                                        <td>Steel</td>
                                        <td>800 Kg</td>
                                        <td>16,000</td>
                                        <td>Available</td>
                                        <td>20 Jan 2025</td>
                                    </tr>
                                    <tr>
                                        <td>Raw Material</td>
                                        <td>Copper</td>
                                        <td>500 Kg</td>
                                        <td>12,500</td>
                                        <td>Available</td>
                                        <td>20 Jan 2025</td>
                                    </tr>
                                    <tr>
                                        <td>Finished Product</td>
                                        <td>Widget A</td>
                                        <td>150 Units</td>
                                        <td>22,500</td>
                                        <td>Available</td>
                                        <td>20 Jan 2025</td>
                                    </tr>
                                    <tr>
                                        <td>Finished Product</td>
                                        <td>Widget B</td>
                                        <td>80 Units</td>
                                        <td>12,800</td>
                                        <td>Available</td>
                                        <td>20 Jan 2025</td>
                                    </tr>
                                    <tr>
                                        <td>Packaging Material</td>
                                        <td>Carton Box</td>
                                        <td>300 Units</td>
                                        <td>3,750</td>
                                        <td>Available</td>
                                        <td>20 Jan 2025</td>
                                    </tr>
                                    <tr>
                                        <td>Packaging Material</td>
                                        <td>Plastic Wrap</td>
                                        <td>250 Rolls</td>
                                        <td>3,000</td>
                                        <td>Available</td>
                                        <td>20 Jan 2025</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>



                </div><!-- end card -->
            </div><!-- end col -->
        </div>

        @else

            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 mb-4" style="border-radius:24px;background:radial-gradient(circle at top right, rgba(14,165,233,.14), transparent 30%), radial-gradient(circle at left center, rgba(16,185,129,.14), transparent 30%), linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); box-shadow:0 18px 40px rgba(15,23,42,.06);">
                            <div class="card-body p-4 p-lg-5">
                                <div class="row align-items-center g-4">
                                    <div class="col-lg-7">
                                        <span style="display:inline-flex;align-items:center;padding:7px 12px;border-radius:999px;border:1px solid #dbeafe;background:rgba(255,255,255,.86);color:#1d4ed8;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">Workday Actions</span>
                                        <h4 class="mt-3 mb-2">Dashboard</h4>
                                        <p class="text-muted mb-0">Use quick attendance actions from the same lighter dashboard surface as the rest of the refreshed project.</p>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="d-flex justify-content-lg-end">
                                            <ol class="breadcrumb m-0">
                                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard </a></li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card  h-100">
                            <div class="card-body">
                                <div class="d-flex align-item-center">

                                    @if(!$attendance_id || $attendance_id && $attendance_id['check_out'] != null)
                                    <div>
                                        <form method="post" action="{{ route('attendances.store') }}">
                                            @csrf
                                            <button type="submit" name="action" value="check_in" class="btn btn-success">
                                                <i class="ri-login-circle-line align-bottom me-1"></i> Check In
                                            </button>
                                        </form>
                                    </div>
                                    @endif

                                    @if($attendance_id && $attendance_id['check_out'] == null)
                                    <div class="ms-2">
                                        <form method="post" action="{{ route('attendances.attendance-update',$attendance_id['id']) }}">
                                            @csrf
                                            <button type="submit" name="action" value="check_out" class="btn btn-danger">
                                                <i class="ri-logout-circle-line align-bottom me-1"></i> Check Out
                                            </button>
                                        </form>
                                    </div>
                                    @endif

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @endif
    </div>
@endsection

@section('scripts')
    <!-- apexcharts -->
   <script src="{{ asset('public/build/assets/libs/apexcharts/apexcharts.min.js') }}"></script>

   <!-- Dashboard init -->
   <script src="{{ asset('public/build/assets/js/pages/dashboard-ecommerce.init.js') }}"></script>

   <!-- apexcharts -->
   <script src="{{ asset('public/build/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
   <script src="//cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.0/dayjs.min.js') }}"></script>
   <script src="//cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.0/plugin/quarterOfYear.min.js') }}"></script>

   <!-- mixed charts init -->
   <script src="{{ asset('public/build/assets/js/pages/apexcharts-mixed.init.js') }}"></script>

   <!-- apexcharts init -->
   <script src="{{ asset('public/build/assets/js/pages/apexcharts-column.init.js') }}"></script>
@endsection
