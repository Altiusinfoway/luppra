@extends('layouts.app')

@section('page-css')
    <style>
        .workflow-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .workflow-suite .hero-shell,
        .workflow-suite .toolbar-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
        }

        .workflow-suite .hero-shell {
            border-radius: 28px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
        }

        .workflow-suite .toolbar-shell {
            border-radius: 22px;
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

        .workflow-suite .hero-title {
            font-size: clamp(2rem, 3vw, 2.7rem);
            line-height: 1.05;
            letter-spacing: -0.04em;
            font-weight: 800;
            margin: 1rem 0 .45rem;
            color: #0f172a;
        }

        .workflow-suite .hero-subtitle,
        .workflow-suite .toolbar-note {
            color: #64748b;
        }

        .workflow-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.86);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .workflow-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .workflow-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            line-height: 1.1;
            letter-spacing: -0.03em;
            font-weight: 800;
            color: #0f172a;
        }

        .workflow-suite .filter-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 1rem;
            margin-top: 1rem;
        }

        .workflow-suite .filter-label {
            display: block;
            margin-bottom: 0.35rem;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .workflow-suite .toolbar-alert {
            max-width: 680px;
            margin: 1rem 1rem 0;
            border: 1px solid #bfdbfe;
            border-radius: 18px;
            padding: 0.95rem 1rem;
            background: linear-gradient(180deg, #eff6ff 0%, #f8fbff 100%);
            color: #1d4ed8;
            box-shadow: 0 12px 26px rgba(37, 99, 235, 0.08);
        }

        .workflow-suite .toolbar-alert .banner-label {
            display: block;
            margin-bottom: 0.3rem;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            opacity: .82;
        }

        .workflow-suite .toolbar-alert .banner-dismiss {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 28px;
            height: 28px;
            border: 0;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.78);
            color: #1d4ed8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.12);
        }

        .workflow-suite .toolbar-alert .banner-dismiss:hover {
            background: #ffffff;
            color: #1e3a8a;
        }

        .workflow-suite .table-shell {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
        }

        .workflow-suite .table-shell table {
            margin-bottom: 0;
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
                                    <span class="hero-eyebrow">Quotation Workspace</span>
                                    <h1 class="hero-title">Quotes</h1>
                                    <p class="hero-subtitle mb-0">Track draft and final quotations, filter by lead and date, and keep the sales handoff flow consistent with the refreshed dashboard UI.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end flex-wrap gap-2">
                                        @can('create quote')
                                            <a href="{{ route('quotes.create') }}" class="btn btn-primary">
                                                <i class="ri-add-line align-bottom me-1"></i> Add Quote
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Quotes</span>
                            <h3>Sales Drafts</h3>
                            <p class="text-muted mb-0 mt-2">Keep proposal preparation and final quote handoff in the same KPI-driven rhythm as the refreshed workflow pages.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Filters</span>
                            <h3>Lead + Status</h3>
                            <p class="text-muted mb-0 mt-2">Review pipeline-ready quotations faster with lead, status, and date filters grouped above the live table.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card toolbar-shell">

                        @if (request()->has('message_success'))
                            <div class="toolbar-alert position-relative" role="status" id="success_msg">
                                <span class="banner-label">Quote update</span>
                                {{ request()->get('message_success') }}
                                <button type="button" class="banner-dismiss" aria-label="Close" onclick="dismissQuoteBanner()">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>
                        @endif

                        <div class="card-header border-0">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                <div>
                                    <h5 class="card-title mb-1">Quote List</h5>
                                    <p class="toolbar-note mb-0">Refine by lead, status, or date and keep the table focused on active quotation work.</p>
                                </div>
                            </div>
                            <div class="filter-shell">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-6 col-xl-3">
                                        <label class="filter-label" for="lead_filter">Lead</label>
                                        <select name="lead_filter" class="form-control form-control-sm mr-3 " id="lead_filter">
                                            <option value="">Select Lead Name</option>
                                            @foreach ($lead_list as $l_list)
                                                <option value="{{ $l_list['id'] }}"
                                                    {{ request('lead_filter') == $l_list['id'] ? 'selected' : '' }}>
                                                    {{ $l_list['name'] }} </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-xl-2">
                                        <label class="filter-label" for="status_filter">Status</label>
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
                                    <div class="col-md-6 col-xl-2">
                                        <label class="filter-label" for="quote_start_date">Start Date</label>
                                        <input
                                            class="form-control form-control-sm datepicker-range flatpickr-input active start_date_filter"
                                            id="quote_start_date" data-provider="flatpickr" data-range="true"
                                            name="start_date_filter" type="text" readonly="readonly"
                                            placeholder="Select Start Date" value="{{ request('start_date_filter') }}">
                                    </div>
                                    <div class="col-md-6 col-xl-2">
                                        <label class="filter-label" for="quote_end_date">End Date</label>
                                        <input class="form-control form-control-sm datepicker-range flatpickr-input active end_date_filter"
                                            id="quote_end_date" data-provider="flatpickr" data-range="true"
                                            name="end_date_filter" type="text" readonly="readonly"
                                            placeholder="Select End Date" value="{{ request('end_date_filter') }}">
                                    </div>
                                    <div class="col-md-12 col-xl-3 text-end">
                                        @can('create quote')
                                            <a href="{{ route('quotes.create') }}" class="btn btn-sm btn-primary" id="addproduct-btn">
                                                <i class="ri-add-line align-bottom me-1"></i> Add Quote
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-shell">
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
                    </div>
                </div><!--end col-->
            </div>
        </div>
    </div>

    <script>
        function dismissQuoteBanner() {
            const msg = document.getElementById('success_msg');

            if (!msg) {
                return;
            }

            msg.style.display = "none";

            const url = new URL(window.location.href);
            url.searchParams.delete('message_success');
            window.history.replaceState({}, document.title, url.pathname);
        }

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
                    dismissQuoteBanner();
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
