@extends('layouts.app')

@section('content')
    @include('leads.lead_import_all_section')
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Leads</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('leads.list') }}">Leads</a></li>
                                <li class="breadcrumb-item active">List</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Content -->

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-item-center justify-content-between">
                                <div class="col-auto">
                                    <h5 class="card-title mb-0">Leads List</h5>
                                </div>

                                <div class="col-auto d-flex">
                                    <div class="">
                                        <div class="hstack gap-2">

                                            <button class="btn btn-soft-danger" id="remove-actions"
                                                onClick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>

                                            <a href="javascript:void(0);"
                                                class="btn btn-sm btn-success add-btn"
                                                data-size="lg" data-url="{{ route('leads.create') }}"
                                                data-ajax-popup="true"
                                                data-bs-original-title="{{ __('Add Lead') }}"><i
                                                    class="ri-add-circle-line me-1"></i> Add leads</a>


                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-sm btn-info  dropdown" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ri-file-download-line align-bottom "> Import</i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    {{-- <li>
                                                        <a href="javascript:void(0)" class="dropdown-item edit-item-btn"
                                                            data-bs-toggle="modal" data-bs-target="#indiamartModel">
                                                            <i class="ri-octagon-fill align-bottom me-2 text-muted"></i>
                                                            India Mart
                                                        </a>
                                                    </li> --}}
                                                    {{-- <li>
                                                        <a class="dropdown-item remove-item-btn" href="javascript:void(0)"
                                                            data-bs-toggle="modal" data-bs-target="#facebookModel">
                                                            <i class="ri-octagon-fill align-bottom me-2 text-muted"></i>
                                                            Facebook
                                                        </a>
                                                    </li> --}}

                                                    <li>
                                                        <a class="dropdown-item remove-item-btn"
                                                            href="{{ route('leads.upload_excel_lead') }}">
                                                            <i class="ri-octagon-fill align-bottom me-2 text-muted"></i>
                                                            Upload data
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>



                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="offcanvas"
                                                href="#offcanvasExample">
                                                <i class="ri-filter-3-line align-bottom me-1"></i> Fliters
                                            </button>


                                            <div class="d-flex gap-1 flex-wrap">
                                                <div class="hstack gap-2">
                                                    @if(\Auth::user()->type == 'Sales')
                                                    <div>
                                                        <a href="javascript:void(0);"
                                                            class="btn btn-sm btn-primary btn-icon waves-effect waves-light"
                                                            data-size="md" data-url="{{ route('leads.get_lead_fetch') }}"
                                                            data-ajax-popup="true"
                                                            data-bs-original-title="{{ __('Fetch Leads') }}"><i
                                                                class="ri-download-cloud-2-line me-1"></i> </a>
                                                    </div>
                                                    @endif

                                                    <div class="d-none">
                                                        <input type="radio" class="btn-check" name="leads-filter"
                                                            id="leads-filter" value="all"
                                                            {{ \Auth::user()->type == 'company' ? 'checked' : '' }}>
                                                        <label class="btn btn-outline-success material-shadow"
                                                            for="leads-filter">All Leads</label>

                                                        <input type="radio" class="btn-check" name="leads-filter"
                                                            id="leads-filter-1" value="my"
                                                            {{ \Auth::user()->type != 'company' ? 'checked' : '' }}
                                                            {{ \Auth::user()->type == 'company' ? 'disabled' : '' }}>
                                                        <label class="btn btn-outline-success material-shadow"
                                                            for="leads-filter-1">My Leads</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <span class="dropdown">
                                                <button class="btn btn-sm btn-soft-info btn-icon fs-14" type="button"
                                                    id="dropdownMenuButton1" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="ri-settings-4-line"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('setting.lead.index') }}">Stages</a>
                                                    </li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('setting.lead.index') }}">Sources</a></li>
                                                </ul>
                                            </span>
                                            <div class="">
                                                <a href="{{ route('leads.index') }}"
                                                    class="btn btn-sm btn-danger btn-icon waves-effect waves-light"><i
                                                        class="bx bx-grid-alt fs-3"></i></a>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                            <table id="leadList"
                                class="table table-bordered  nowrap table-striped align-middle"
                                style="width:100%">
                                <thead>

                                    <tr>
                                        <th>
                                            {{-- <div class="form-check">
                                            <input class="form-check-input fs-15" type="checkbox" id="checkAll" value="option">
                                        </div> --}}
                                            Sr. No
                                        </th>

                                        <th data-ordering="false">Name</th>
                                        <th>Create Date</th>
                                        <th>Source</th>
                                        <th>Status</th>
                                        <th data-ordering="false">Contact</th>
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
            <!-- end Content -->

            <!-- filter model -->
            <div class="offcanvas offcanvas-end fade" tabindex="-1" id="offcanvasExample"
                aria-labelledby="offcanvasExampleLabel" aria-modal="true" role="dialog">
                <div class="offcanvas-header bg-light">
                    <h5 class="offcanvas-title" id="offcanvasExampleLabel">Leads Fliters</h5>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                        aria-label="Close"></button>
                </div>

                {{ Form::open(['route' => 'leads.list', 'method' => 'get', 'id' => 'lead-filter-form', 'class' => 'form-inline d-flex flex-column justify-content-end h-100']) }}
                <div class="offcanvas-body">
                    @if (\Auth::user()->type == 'company')
                        <div class="mb-2">
                            <label for="customer-select"
                                class="form-label text-muted text-uppercase fw-semibold">Customer</label>
                            <div class="">
                                {{ Form::select('customer_list', ['' => 'Select Customer'] + $cust_list->toArray(), null, ['class' => 'form-control form-control-sm  ', 'data-choices', 'data-choices-removeItem']) }}
                            </div>
                        </div>
                    @endif

                    <div class="mb-2">
                        <label for="customer-select" class="form-label text-muted text-uppercase fw-semibold">Sales
                            Employee</label>
                        <div class="">
                            {{ Form::select('sales_list', ['' => 'Select Sales Employee'] + $sales_user_list->toArray(), null, ['class' => 'form-control   form-control-sm', 'data-choices', 'data-choices-removeItem']) }}
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="country-select"
                            class="form-label text-muted text-uppercase fw-semibold">Source</label>
                        <div class="">
                            {{ Form::select('sources[]', $sources, null, ['class' => 'form-control  choices-select', 'multiple' => true, 'data-choices', 'data-choices-removeItem']) }}
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="country-select"
                            class="form-label text-muted text-uppercase fw-semibold">Product</label>
                        <div class="">
                            {{ Form::select('products[]', $products, null, ['class' => 'form-control  choices-select', 'multiple' => true, 'data-choices', 'data-choices-removeItem']) }}
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="country-select"
                            class="form-label text-muted text-uppercase fw-semibold">Stage</label>
                        <div class="">
                            {{ Form::select('stage', ['' => 'Select Stage'] + $stages->toArray(), null, ['class' => 'form-select form-select-sm mb-3', 'id' => 'stage_id', 'aria-label' => 'Select stage Source']) }}
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="country-select"
                            class="form-label text-muted text-uppercase fw-semibold">Date</label>
                        <div class="">
                            {{ Form::date('date', null, ['class' => 'form-control form-control-sm  datepicker-range', 'id' => 'datepicker-range', 'data-provider' => 'flatpickr', 'data-range' => 'true']) }}
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="lead_type-select" class="form-label text-muted text-uppercase fw-semibold">Lead
                            Type</label>
                        <div class="">
                            {{ Form::select('lead_type_filter', ['' => 'Select Lead Type'] + $lead_type_list->toArray(), null, ['class' => 'form-control form-control-sm ', 'data-choices', 'data-choices-removeItem']) }}
                        </div>
                    </div>
                </div>

                <div class="offcanvas-footer border-top p-3 text-center hstack gap-2">
                    <button class="btn btn-light w-100">Clear Filter</button>
                    <button type="submit" class="btn btn-success w-100">Filters</button>
                </div>
                {{ Form::close() }}




            </div>
            <!-- End filter model -->




        </div>
    </div>

    {{-- @include('leads/create') --}}
@endsection

@section('scripts')
    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="{{ asset('public/build/assets/js/pages/user/whatsapp-chat-entry.js') }}"></script>
@endsection

@section('page-script')
    <script>
        var atable;
        $(document).on("change", "input[type=radio][name=leads-filter]", function() {

            atable.ajax.reload();

        });

        $(document).ready(function() {

            atable = $('#leadList').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('leads.list',$slug) }}",
                    data: function(d) {
                        d.sources = $('select[name="sources[]"]').val();
                        d.products = $('select[name="products[]"]').val();
                        d.date = $('input[name="date"]').val();
                        d.stage = $('select[name="stage"]').val();
                        d.name = $('#search-task-options').val();
                        d.customer_list = $('select[name="customer_list"]').val();
                        d.listType = $("input[name='leads-filter']:checked").val();
                        d.lead_type_filter = $('select[name="lead_type_filter"]').val();
                        d.sales_list = $('select[name="sales_list"]').val()
                    }
                },
                columns: [
                    /* { data: 'checkboxes', name: 'checkboxes', orderable: false}, */
                    {
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
                        data: 'createdAt',
                        name: 'createdAt'
                    },
                    {
                        data: 'sources',
                        name: 'sources'
                    },
                    {
                        data: 'stages',
                        name: 'stages'
                    },
                    {
                        data: 'cust_phone',
                        name: 'cust_phone'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                initComplete: function(settings, json) {

                    enableConfirmationOn('change', "need-confirmation", "You want to change status?",
                        function(url, data) {

                            console.log(url);

                            getAjax(url, function(response) {
                                if (response.success == 'true') {
                                    show_toastr('success', response.message);
                                } else {
                                    show_toastr('error', response.message);
                                }
                                atable.ajax.reload();
                            });
                        });

                    /* enableConfirmationOn('click',"custom-toggle","You want to PICKUP / GIVEUP?", function(url, data){
                        console.log(url);
                        alert("sd");
                    }); */
                },
            });

            $('#search-task-options').on('keyup', function() {
                atable.draw();
            });

            $('#lead-filter-form').on('submit', function(e) {
                e.preventDefault();
                atable.draw();

                var offcanvasElement = document.getElementById('offcanvasExample');
                var offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasElement);
                if (offcanvasInstance) {
                    offcanvasInstance.hide();
                }
            });

            $('.btn-light').on('click', function() {
                $('#lead-filter-form')[0].reset();
                $('#search-task-options').val('');
                atable.draw();

                var offcanvasElement = document.getElementById('offcanvasExample');
                var offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasElement);
                if (offcanvasInstance) {
                    offcanvasInstance.hide();
                }
            });


            // enableConfirmationOnClick("need-confirmation-on","You want to fetch leads ?", function(url, data){
            //     console.log("dfd");

            //     window.location.href=url;
            // });


            $(document).on('click', '.custom-toggle', function() {


                var button = $(this);
                var isActive = button.hasClass('active'); // true if now active

                // Determine action
                var action = isActive ? 'giveup' : 'pickup'; // Check previous state before toggle

                // Actually Bootstrap toggles after click event, so reverse logic:
                //action = isActive ? 'pickup' : 'giveup'; // after toggle, state changes

                doConfirmation(button, "You want to  GIVEUP & Also giveup Customer ?", function(status, $this) {

                    if (status) {

                        // Assign to leads.
                        var url = $this.data("url");
                        console.log(url);
                        getAjax(url, function(e) {

                            e.success ? show_toastr("success", e.message) : show_toastr(
                                "error", e.message);

                            atable.ajax.reload(null, false);

                        });

                    } else {

                        // Set previus value.
                        if (isActive) {
                            button.removeClass('active');
                        } else {
                            button.addClass('active');
                        }

                    }

                });

            });

        });
    </script>
@endsection
