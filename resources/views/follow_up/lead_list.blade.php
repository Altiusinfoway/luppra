@extends('layouts.app')

@section('page-css')
    <style>
        .workflow-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .workflow-suite .hero-shell,
        .workflow-suite .toolbar-shell,
        .workflow-suite .table-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
        }

        .workflow-suite .hero-eyebrow {
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
    <div class="page-content workflow-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <span class="hero-eyebrow">Follow-Up Workspace</span>
                                    <h1 class="mb-3">
                                        @if ($dynamic_slug == 'upcomming')
                                            Up-Coming Follow-Up
                                        @elseif($dynamic_slug == 'expired')
                                            Expired Follow-Up
                                        @elseif($dynamic_slug == 'notinterested')
                                            Not Interested Follow-Up
                                        @else
                                            Follow-Up
                                        @endif
                                    </h1>
                                    <p class="text-muted mb-0">Filter and review the focused follow-up queue from the same updated workflow shell used across the CRM module.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item">
                                                <a href="{{ route('follow-ups.follow_up_lead', $dynamic_slug) }}">
                                                    @if ($dynamic_slug == 'upcomming')
                                                        Up-Coming Follow-Up
                                                    @elseif($dynamic_slug == 'expired')
                                                        Expired Follow-Up
                                                    @elseif($dynamic_slug == 'notinterested')
                                                        Not Interested Follow-Up
                                                    @else
                                                        Follow-Up List
                                                    @endif
                                                </a>
                                            </li>
                                            <li class="breadcrumb-item active">List</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card toolbar-shell mb-4">
                <div class="card-body">

                    <form id="lead-filter-form" class="d-flex flex-wrap align-items-center gap-2">
                        <div class="col">
                            {{ Form::date('start_date', null, [
                                'class' => 'form-control form-control-md',
                                'id' => 'start_date',
                                'placeholder' => 'Start Date',
                            ]) }}
                        </div>

                        <div class="col">
                            {{ Form::date('end_date', null, [
                                'class' => 'form-control form-control-md',
                                'id' => 'end_date',
                                'placeholder' => 'End Date',
                            ]) }}
                        </div>

                        @if (\Auth::user()->type == 'company')
                            <div class="col">
                                <select name="sales_user" class="form-control form-control-md">
                                    <option value="">Select Sales User</option>
                                    @foreach ($get_sales_user as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col">
                            <select name="lead_status" class="form-control form-control-md">
                                <option value="">Select Lead Status</option>
                                @foreach ($status_list as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary ">Search</button>
                    </form>

                    @if (\Auth::user()->type == 'Sales')
                        <div class="text-end mt-2">
                            <a href="{{ route('follow-ups.create', $dynamic_slug) }}" class="btn btn-success "
                                id="addFollow-btn">
                                <i class="ri-add-line align-bottom me-1"></i> Add Follow-Up
                            </a>
                        </div>
                    @endif

                </div>
            </div>
            <!--end card-->

            <div class="row">
                <div class="col-lg-12">
                    <div class="card table-shell">

                        <div class="card-body">
                            <table id="followUpcomList"
                                class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                style="width:100%">
                                <thead>

                                    <tr>
                                        <th style="width: 50px;">Id</th>
                                        <th data-ordering="false">Leads Details</th>
                                        <th data-ordering="false">Follow-Up</th>
                                        <th data-ordering="false">Lead Source</th>
                                        <th>Lead Status</th>
                                        <th>Contact Us</th>
                                        <th>Action</th>
                                    </tr>

                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div><!--end col-->
            </div><!--end row-->
            <!-- end Content -->




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
        $(document).ready(function() {

            var table = $('#followUpcomList').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('follow-ups.follow_up_lead', $dynamic_slug) }}",
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.sales_user = $('select[name="sales_user"]').val();
                        d.lead_status = $('select[name="lead_status"]').val();

                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },

                    {
                        data: 'lead_all_detail',
                        name: 'lead_all_detail'
                    },
                    {
                        data: 'follow_up_date',
                        name: 'follow_up_date'
                    },
                    {
                        data: 'sources',
                        name: 'sources'
                    },

                    {
                        data: 'lead_status',
                        name: 'lead_status'
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

            $('#lead-filter-form').on('submit', function(e) {
                e.preventDefault();
                table.draw();
            });
        });
    </script>
@endsection
