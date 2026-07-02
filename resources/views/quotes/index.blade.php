@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Quotes</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('quotes.index') }}">Quotes</a></li>
                                <li class="breadcrumb-item active">List</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Varying Modal Content -->
                <div class="col-lg-12">
                    <div class="card">

                        @if (request()->has('message_success'))
                            <div class="alert bg-primary text-white alert-dismissible fade show col-md-6 m-3" role="alert"
                                id="success_msg">
                                {{ request()->get('message_success') }}
                                <button type="button" class="btn-close text-white" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="card-header">
                            <h5 class="card-title  mb-0">Quote List</h5>

                            <div class="d-flex mt-3 justify-content-between align-items-center w-100">

                                {{-- <form method="get" action="{{ route('quotes.index') }}" id="quote_form" class="d-flex gap-2"> --}}

                                <div class="row w-100">
                                    <div class="col">
                                        <select name="lead_filter" class="form-control form-control-sm mr-3 " id="lead_filter">
                                            <option value="">Select Lead Name</option>
                                            @foreach ($lead_list as $l_list)
                                                <option value="{{ $l_list['id'] }}"
                                                    {{ request('lead_filter') == $l_list['id'] ? 'selected' : '' }}>
                                                    {{ $l_list['name'] }} </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col">
                                        <select name="status_filter" class="form-control form-control-sm" id="status_filter">
                                            <option value="">Select Status</option>
                                            <option value="1" {{ request('status_filter') == 1 ? 'selected' : '' }}>
                                                Pending
                                            </option>
                                            {{-- <option value="2" {{ request('status_filter') == 2 ? 'selected' : '' }}>
                                                Send
                                            </option> --}}
                                            <option value="3" {{ request('status_filter') == 3 ? 'selected' : '' }}>
                                                Final
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <input
                                            class="form-control form-control-sm datepicker-range flatpickr-input active start_date_filter"
                                            id="datepicker-range" data-provider="flatpickr" data-range="true"
                                            name="start_date_filter" type="text" readonly="readonly"
                                            placeholder="Select Start Date" value="{{ request('start_date_filter') }}">
                                    </div>
                                    <div class="col">
                                        <input class="form-control form-control-sm datepicker-range flatpickr-input active end_date_filter"
                                            id="datepicker-range" data-provider="flatpickr" data-range="true"
                                            name="end_date_filter" type="text" readonly="readonly"
                                            placeholder="Select End Date" value="{{ request('end_date_filter') }}">
                                    </div>
                                    <div class="col text-end">
                                        @can('create quote')
                                            <a href="{{ route('quotes.create') }}" class="btn btn-sm btn-success" id="addproduct-btn">
                                                <i class="ri-add-line align-bottom me-1"></i> Add Quote
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                                {{-- </form> --}}

                                {{-- <div>
                                <a  href="javascript:void(0);"
                                    class="btn btn-success"
                                    data-size="xl"
                                    data-url="{{ route('quotes.create',[$lead_id]) }}"
                                    data-ajax-popup="true"
                                    id="create_quote_btn"
                                    data-bs-original-title="{{__('Add Quotes')}}"><i class="ri-add-line align-bottom me-1"

                                    ></i> Add Quote</a>
                            </div> --}}



                            </div>
                        </div>

                        <div class="card-body">
                            <table id="quoteList"
                                class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 150px;">Sr No</th>
                                        <th style="width: 150px;">Quote Id</th>
                                        <th data-ordering="false">Customer Name</th>
                                        <th>Date</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th class="text-center">Quotes Approve</th>
                                        <th>Contact</th>
                                        <th>Action</th>
                                    </tr>

                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div><!--end col-->
            </div>
        </div>
    </div>

    <script>
        function openStatusEditPage(element) {
            const url = element.getAttribute('data-url');
            window.location.href = url;
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const msg = document.getElementById('success_msg');
            if (msg) {
                setTimeout(function() {
                    msg.classList.remove('show');
                    msg.classList.add('fade');
                    msg.style.display = "none";
                    const url = new URL(window.location.href);
                    url.searchParams.delete('message_success');
                    window.history.replaceState({}, document.title, url.pathname);
                }, 10000);
            }
        });
    </script>
    <script>
        @if (isset($lead_id))

            document.addEventListener("DOMContentLoaded", function() {
                document.querySelector("#create_quote_btn").click();

                setTimeout(function() {
                    var leadId = "{{ (string) $lead_id }}"; // value you want to select

                    var leadSelect = document.getElementById('lead_id');
                    const choices1 = new Choices(leadSelect, {
                        shouldSort: false
                    });

                    // Set the value in Choices.js
                    choices1.setChoiceByValue(leadId);

                    // Manually dispatch change event (so any listeners fire)
                    leadSelect.dispatchEvent(new Event('change'));

                    console.log("Lead ID set to:", leadId);
                }, 2000);
            });
        @endif
    </script>
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
        $(document).ready(function() {

            var table = $('#quoteList').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('quotes.index') }}",
                    data: function(d) {
                        d.lead_filter = $('#lead_filter').val();
                        d.status_filter = $('#status_filter').val();
                        d.start_date_filter = $('.start_date_filter').val();
                        d.end_date_filter = $('.end_date_filter').val();
                    }
                },
                columns: [
                    {
                         data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'lead_id',
                        name: 'lead_id'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'grand_total',
                        name: 'grand_total'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'quotation_approve',
                        name: 'quotation_approve'
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
                ]
            });

            $('#lead_filter').on('change', function() {
                table.draw();
            });
            $('#status_filter').on('change', function() {
                table.draw();
            });
            $('.start_date_filter').on('change', function() {
                table.draw();
            });
            $('.end_date_filter').on('change', function() {
                table.draw();
            });
        });
    </script>
@endsection
