@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Customers</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a>
                                </li>
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
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <h5 class="card-title  mb-0">Customer List</h5>

                                @can('create customer')
                                    <div>
                                        <a href="{{ route('customers.create') }}" class="btn btn-sm btn-success"
                                            id="addproduct-btn">
                                            <i class="ri-add-line align-bottom me-1"></i> Add Customer
                                        </a>
                                    </div>
                                @endcan

                            </div>
                        </div>
                        <div class="card-body">

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
