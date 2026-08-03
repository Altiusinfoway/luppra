@extends('layouts.app')

@section('page-css')
    <style>
        .vendors-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .vendors-suite .hero-shell,
        .vendors-suite .table-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
        }

        .vendors-suite .hero-eyebrow {
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

        .vendors-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .vendors-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .vendors-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .vendors-suite .filter-shell {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.92), rgba(255, 255, 255, 0.98));
            padding: 1rem;
        }

        .vendors-suite .filter-label {
            display: block;
            margin-bottom: 0.45rem;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .vendors-suite .toolbar-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 14px;
        }

        .vendors-suite .toolbar-note {
            color: #64748b;
        }

        .vendors-suite .search-shell {
            position: relative;
        }

        .vendors-suite .search-shell .search-icon {
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
        }

        .vendors-suite .table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
        }

        .vendors-suite .table-wrap table {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    <div class="page-content vendors-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="hero-eyebrow">Procurement Directory</span>
                                    <h1 class="mb-3">Vendors</h1>
                                    <p class="text-muted mb-0">Manage supplier records, filters, and product relationships in a cleaner procurement workspace.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('vendors.index') }}">Vendors</a></li>
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
                            <span class="label">Suppliers</span>
                            <h3>{{ number_format($vendors->count() ?? 0) }}</h3>
                            <p class="text-muted mb-0 mt-2">Vendor records available for procurement and replenishment workflows.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Filters</span>
                            <h3>4</h3>
                            <p class="text-muted mb-0 mt-2">Country, state, city, and product filters keep sourcing views focused.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card table-shell">
                        <div class="card-header">
                            <div class="toolbar-shell mb-3">
                                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                                    <div>
                                        <h5 class="card-title mb-1">Vendor Operation List</h5>
                                        <p class="toolbar-note mb-0">Use vendor, location, and product filters to keep procurement and sourcing workflows focused.</p>
                                    </div>

                                    @can('create vender')
                                    <div>
                                        <a href="{{ route('vendors.create') }}" class="btn btn-primary">
                                            <i class="ri-add-line align-bottom me-1"></i> Add Vendor
                                        </a>
                                    </div>
                                    @endcan
                                </div>
                            </div>

                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">

                                <div class="filter-shell mt-2 flex-grow-1">
                                <div class="row g-3 align-items-end">

                                    <div class="col-md-3">
                                        <label class="filter-label">Search</label>
                                        <div class="search-shell">
                                            <input type="search" id="search-task-options" class="form-control"
                                                placeholder="Search vendors...">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="filter-label">Country</label>
                                        <select name="country_filter" id="country_filter" class="form-control">
                                            <option value="">All Country</option>
                                            @foreach ($country_list as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="filter-label">State</label>
                                        <select name="state_filter" id="state_filter" class="form-control">
                                            <option value="">All States</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="filter-label">City</label>
                                        <select name="city_filter" id="city_filter" class="form-control">
                                            <option value="">All Cities</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="filter-label">Product</label>
                                        <select name="product_filter" id="product_filter" class="form-control">
                                            <option value="">All Product</option>
                                            @foreach ($product_list as $product)
                                                <option value="{{ $product->id }}" {{ request('product_filter') == $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Sr No.</th>
                                    <th data-ordering="false">Name</th>
                                    <th data-ordering="false">GST</th>
                                    <th data-ordering="false">Address</th>
                                    <th style="width: 80px;">Status</th>
                                    <th style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vendors as $list)
                                <tr class="main-row">
                                    <td>{{ $list['id'] }}</td>
                                    <td>
                                        <h6>{{ $list['name'] }}</h6>
                                    </td>
                                    <td>{{ $list['gst_no'] }}</td>
                                    <td>{{ $list->getAddress['address_line_1'] ?? '' }} </td>
                                    <td>
                                        <h5>
                                            @if ($list['is_active'] == 1)
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                            <span class="badge bg-success-subtle text-danger">In-Active</span>
                                            @endif
                                        </h5>
                                    </td>
                                    <td>
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-fill align-middle"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a href="{{ route('vendors.edit',$list['id']) }}" class="dropdown-item edit-item-btn"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit</a></li>
                                                <li>
                                                    <a class="dropdown-item remove-item-btn"
                                                    data-delete-popup="true"
                                                    data-bs-original-title="You are about to delete a vendor ?"  data-bs-original-description="Deleting your vendor will remove all of your information from our database."
                                                    data-original-title=""
                                                    data-url="{{ route('vendors.delete',[$list['id']]) }}"
                                                    data-method="DELETE"
                                                    data-cb="afterDelete"
                                                    href="javascript:void(0)">
                                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                                    </a>

                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table> --}}

                            <div class="table-responsive table-wrap">
                            <table id="vendorList"
                                class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0"
                                style="width:100%">
                                <thead>

                                    <tr>
                                        <th data-ordering="false">Sr No</th>
                                        <th data-ordering="false">Name</th>
                                        <th>Contact</th>
                                        <th data-ordering="false">GST</th>
                                        <th>Address</th>
                                        <th>Status</th>
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
        $(document).ready(function()
        {

            var table = $('#vendorList').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('vendors.index') }}",
                    data: function(d) {
                        d.name = $('#search-task-options').val();
                        d.state_filter = $('#state_filter').val();
                        d.city_filter = $('#city_filter').val();
                        d.country_filter = $('#country_filter').val();
                        d.product_filter = $('#product_filter').val();
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'contact',
                        name: 'contact'
                    },
                    {
                        data: 'gst_no',
                        name: 'gst_no'
                    },
                    {
                        data: 'address_id',
                        name: 'address_id'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active'
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

            $('#country_filter, #state_filter, #city_filter').on('change', function () {
                table.draw();
            });

            $('#product_filter').on('change', function() {
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
