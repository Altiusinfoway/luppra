@extends('layouts.app')

@section('page-css')
    <style>
        .transport-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .transport-suite .hero-shell,
        .transport-suite .shell-card {
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .transport-suite .hero-shell {
            background:
                radial-gradient(circle at top right, rgba(16, 185, 129, 0.14), transparent 30%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .transport-suite .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            border: 1px solid #d1fae5;
            background: rgba(255, 255, 255, 0.86);
            color: #047857;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .transport-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .transport-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .transport-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .transport-suite .toolbar-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 14px 16px;
        }

        .transport-suite .filter-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #ffffff;
            padding: 16px;
        }

        .transport-suite .filter-label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .transport-suite .search-shell {
            position: relative;
            min-width: min(100%, 300px);
        }

        .transport-suite .search-shell .form-control {
            min-height: 44px;
            padding-left: 2.7rem;
            border-radius: 14px;
            border-color: #cbd5e1;
            background: #fff;
        }

        .transport-suite .search-shell .search-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #64748b;
            pointer-events: none;
        }

        .transport-suite .table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
        }

        .transport-suite .table-wrap thead th {
            background: #f8fafc !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-content transport-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-7">
                                    <span class="hero-eyebrow">Logistics Directory</span>
                                    <h2 class="mt-3 mb-2">Transports</h2>
                                    <p class="text-muted mb-0">Manage transport partners, filter by location, and keep contact coverage organized inside a cleaner logistics dashboard.</p>
                                </div>
                                <div class="col-lg-5">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('transports.index') }}">Transports</a></li>
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
                            <span class="label">Logistics</span>
                            <h3>Transporters</h3>
                            <p class="text-muted mb-0 mt-2">Keep carrier contacts and GST coverage in the same clearer dashboard pattern as the refreshed catalog and sales modules.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Coverage</span>
                            <h3>Location Filters</h3>
                            <p class="text-muted mb-0 mt-2">Country, state, and city filters are grouped into one cleaner lookup surface for transport discovery.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card shell-card">
                        <div class="card-header">
                            <div class="toolbar-shell d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                                    <h5 class="card-title mb-1">Transport List</h5>
                                    <p class="text-muted mb-0">Search transport partners and refine by geography from one compact logistics control bar.</p>
                                </div>
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <div class="search-shell">
                                        <i class="ri-search-line search-icon"></i>
                                        <input type="search" id="search-task-options" class="form-control" placeholder="Search transporter">
                                    </div>
                                    @can('create transport')
                                        <div>
                                            <a href="{{ route('transports.create') }}" class="btn btn-sm btn-primary" id="addproduct-btn">
                                                <i class="ri-add-line align-bottom me-1"></i> Add Transport
                                            </a>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                            <div class="filter-shell mt-3">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label for="country_filter" class="filter-label">Country</label>
                                        <select name="country_filter" id="country_filter" class="form-control">
                                            <option value="">All Country</option>
                                            @foreach ($country_list as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="state_filter" class="filter-label">State</label>
                                        <select name="state_filter" id="state_filter" class="form-control">
                                            <option value="">All States</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="city_filter" class="filter-label">City</label>
                                        <select name="city_filter" id="city_filter" class="form-control">
                                            <option value="">All Cities</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-wrap">
                                <table id="transportList"
                                    class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th data-ordering="false">Sr No</th>
                                            <th data-ordering="false">Name</th>
                                            <th>Contact</th>
                                            <th data-ordering="false">GST</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
            var table = $('#transportList').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('transports.index') }}",
                    data: function(d) {
                        d.name = $('#search-task-options').val();
                        d.state_filter = $('#state_filter').val();
                        d.city_filter = $('#city_filter').val();
                        d.country_filter = $('#country_filter').val();
                    }
                },
                columns: [{
                       data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'contact_list',
                        name: 'contact_list'
                    },
                    {
                        data: 'gst_no',
                        name: 'gst_no'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#search-task-options').on('keyup', function() {
                table.draw();
            });

            $('#country_filter, #state_filter, #city_filter').on('change', function() {
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
