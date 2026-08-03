@extends('layouts.app')

@section('page-css')
    <style>
        .customers-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .customers-suite .hero-shell,
        .customers-suite .table-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
        }

        .customers-suite .hero-eyebrow {
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

        .customers-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .customers-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .customers-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .customers-suite .toolbar-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 14px;
        }

        .customers-suite .filter-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 1rem;
        }

        .customers-suite .filter-label {
            display: block;
            margin-bottom: 0.35rem;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .customers-suite .table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
        }

        .customers-suite .table-wrap table {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    <div class="page-content customers-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="hero-eyebrow">CRM Directory</span>
                                    <h1 class="mb-3">Customers</h1>
                                    <p class="text-muted mb-0">Browse and manage customer records from the same light admin table layout as the refreshed sales and finance modules.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
                                            <li class="breadcrumb-item active">List</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Directory</span>
                            <h3>Customers</h3>
                            <p class="text-muted mb-0 mt-2">Keep the customer book in one lighter CRM directory surface.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Workflow</span>
                            <h3>Searchable</h3>
                            <p class="text-muted mb-0 mt-2">Use quick search and geography filters to narrow customer records faster.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card table-shell">
                        <div class="card-header">
                            <div class="toolbar-shell">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                    <div>
                                        <h5 class="card-title mb-1">Customer List</h5>
                                        <p class="text-muted mb-0">Search and filter the CRM directory from the same polished list shell used across the refreshed dashboards.</p>
                                    </div>

                                    @can('create customer')
                                        <div>
                                            <a href="{{ route('customers.create') }}" class="btn btn-sm btn-primary"
                                                id="addproduct-btn">
                                                <i class="ri-add-line align-bottom me-1"></i> Add Customer
                                            </a>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="filter-shell mb-3">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-6 col-xl-3">
                                        <label class="filter-label" for="search-task-options">Search</label>
                                        <input type="text" class="form-control" placeholder="Search customer name..." autocomplete="off" id="search-task-options">
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <label class="filter-label" for="country_filter">Country</label>
                                        <select id="country_filter" class="form-control">
                                            <option value="">All Countries</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <label class="filter-label" for="state_filter">State</label>
                                        <select id="state_filter" class="form-control">
                                            <option value="">All States</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <label class="filter-label" for="city_filter">City</label>
                                        <select id="city_filter" class="form-control">
                                            <option value="">All Cities</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive table-wrap">
                            <table id="leadList"
                                class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                style="width:100%">
                                <thead>

                                    <tr>
                                        <th data-ordering="false">Sr No</th>
                                        <th data-ordering="false">Name</th>
                                        <th data-ordering="false">Link</th>
                                        <th>Action</th>
                                    </tr>

                                </thead>
                                <tbody></tbody>
                            </table>
                            </div>

                        </div>
                    </div>
                </div><!--end col-->
            </div><!--end row-->

        </div>
        <!-- container-fluid -->
    </div>
@endsection
@section('scripts')
    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
@endsection

@section('page-script')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            @if(isset($countries) && count($countries))
                @foreach($countries as $country)
                    $('#country_filter').append(`<option value="{{ $country['id'] }}">{{ $country['name'] }}</option>`);
                @endforeach
            @endif

            var table = $('#leadList').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('customers.index') }}",
                    data: function(d) {
                        d.name = $('#search-task-options').val();
                        d.country_filter = $('#country_filter').val();
                        d.state_filter = $('#state_filter').val();
                        d.city_filter = $('#city_filter').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'customer_detail',
                        name: 'customer_detail'
                    },
                    {
                        data: 'call_link',
                        name: 'call_link'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#search-task-options, #country_filter, #state_filter, #city_filter').on('change keyup', function() {
                table.draw();
            });

            $('#country_filter').on('change', function() {
                let countryId = $(this).val();
                $('#state_filter').html('<option value="">All States</option>');
                $('#city_filter').html('<option value="">All Cities</option>');

                if (countryId) {
                    $.post("{{ route('get.states') }}", {
                        country_id: countryId
                    }, function(res) {
                        $.each(res.states, function(id, name) {
                            $('#state_filter').append(
                                `<option value="${id}">${name}</option>`);
                        });
                    });
                }
            });

            $('#state_filter').on('change', function() {
                let stateId = $(this).val();
                $('#city_filter').html('<option value="">All Cities</option>');

                if (stateId) {
                    $.post("{{ route('get.cities') }}", {
                        state_id: stateId
                    }, function(res) {
                        $.each(res.cities, function(id, name) {
                            $('#city_filter').append(
                                `<option value="${id}">${name}</option>`);
                        });
                    });
                }
            });
        });
    </script>
@endsection
