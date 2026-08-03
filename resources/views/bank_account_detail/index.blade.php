@extends('layouts.app')

@section('page-css')
    <style>
        .bank-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .bank-suite .hero-shell,
        .bank-suite .shell-card {
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .bank-suite .hero-shell {
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 30%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .bank-suite .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            border: 1px solid #dbeafe;
            background: rgba(255, 255, 255, 0.86);
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .bank-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .bank-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .bank-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .bank-suite .toolbar-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 14px;
        }

        .bank-suite .filter-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 1rem;
        }

        .bank-suite .filter-label {
            display: block;
            margin-bottom: 0.35rem;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .bank-suite .table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
        }

        .bank-suite .table-wrap table {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    <div class="page-content bank-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-7">
                                    <span class="hero-eyebrow">Banking Setup</span>
                                    <h2 class="mt-3 mb-2">Bank Account Detail</h2>
                                    <p class="text-muted mb-0">Review internal bank accounts, keep payout details organized, and manage finance records from one lighter admin surface.</p>
                                </div>
                                <div class="col-lg-5">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('bank-account-details.index') }}">Bank Account Detail</a></li>
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
                            <span class="label">Banking</span>
                            <h3>{{ number_format($bank_account_detail_list->count() ?? 0) }}</h3>
                            <p class="text-muted mb-0 mt-2">Saved internal bank account records available for finance operations.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Module</span>
                            <h3>Accounts</h3>
                            <p class="text-muted mb-0 mt-2">Keep account holder, branch, and IFSC details grouped in one admin workspace.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Discovery</span>
                            <h3>Searchable</h3>
                            <p class="text-muted mb-0 mt-2">Find payout-ready accounts faster by searching holder names, banks, and account metadata.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Operations</span>
                            <h3>Payout Ready</h3>
                            <p class="text-muted mb-0 mt-2">Keep finance operations aligned with the same clean banking registry used across payroll flows.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card shell-card">
                        <div class="card-header">
                            <div class="toolbar-shell d-flex justify-content-between align-items-start flex-wrap gap-3">
                                <div>
                                    <h5 class="card-title mb-1">Bank Account Detail List</h5>
                                    <p class="text-muted mb-0">Review and manage payout-ready banking records from the same cleaner finance shell.</p>
                                </div>

                                @can('create bank detail')
                                    <a href="{{ route('bank-account-details.create') }}" class="btn btn-sm btn-primary"
                                        id="addproduct-btn">
                                        <i class="ri-add-line align-bottom me-1"></i> Add Bank Account Detail
                                    </a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="filter-shell mb-3">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-6 col-xl-4">
                                        <label class="filter-label" for="bank-account-search">Search</label>
                                        <input type="text" class="form-control" id="bank-account-search"
                                            placeholder="Search holder, account, bank, branch...">
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <button type="button" class="btn btn-light w-100" id="bank-account-reset-filters">
                                            Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive table-wrap">
                            <table id="BankDList"
                                class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th data-ordering="false">Sr No</th>
                                        <th data-ordering="false">Account Holder Name</th>
                                        <th data-ordering="false">Account No</th>
                                        <th>Account Type</th>
                                        <th>Bank Name</th>
                                        <th>Branch Name</th>
                                        <th>IFSC Code</th>
                                        <th style="width: 80px;">Action</th>
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
        $(document).ready(function() {

            var table = $('#BankDList').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('bank-account-details.index') }}",
                    data: function(d) {}
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'account_holder_name',
                        name: 'account_holder_name'
                    },
                    {
                        data: 'account_no',
                        name: 'account_no'
                    },
                    {
                        data: 'account_type_label',
                        name: 'account_type_label'
                    },
                    {
                        data: 'bank_name',
                        name: 'bank_name'
                    },
                    {
                        data: 'branch_name',
                        name: 'branch_name'
                    },
                    {
                        data: 'ifsc_code',
                        name: 'ifsc_code'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#bank-account-search').on('keyup change', function() {
                table.search($(this).val()).draw();
            });

            $('#bank-account-reset-filters').on('click', function() {
                $('#bank-account-search').val('');
                table.search('').draw();
            });
        });
    </script>
@endsection
