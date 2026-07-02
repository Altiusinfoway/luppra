@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Vendor Operation Section</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('vendors.index') }}">Vendor Operation</a></li>
                                <li class="breadcrumb-item active">List</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="row">

                <!-- Varying Modal Content -->
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title  mb-0">Vendor Operation List</h5>
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">

                                <div class="row g-2 align-items-center mt-2 w-75">

                                    <div class="col-md-3">
                                        <input type="search" id="search-task-options" class="form-control"
                                            placeholder="Search...">
                                    </div>

                                    <div class="col-md-2">
                                        <select name="country_filter" id="country_filter" class="form-control">
                                            <option value="">All Country</option>
                                            @foreach ($country_list as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <select name="state_filter" id="state_filter" class="form-control">
                                            <option value="">All States</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <select name="city_filter" id="city_filter" class="form-control">
                                            <option value="">All Cities</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
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

                                @can('create vender')
                                <div>
                                    <a href="{{ route('vendors.create') }}" class="btn btn-success">
                                        <i class="ri-add-line align-bottom me-1"></i> Add Vendor
                                    </a>
                                </div>
                                @endcan
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

                            <table id="vendorList"
                                class="table table-bordered dt-responsive nowrap table-striped align-middle"
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
