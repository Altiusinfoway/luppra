<style>
    .payroll-history-suite .summary-row {
        margin-bottom: 1.5rem;
    }

    .payroll-history-suite .metric-card {
        border: 1px solid rgba(255, 255, 255, 0.82);
        border-radius: 22px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.06);
    }

    .payroll-history-suite .metric-label {
        display: block;
        margin-bottom: 8px;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .payroll-history-suite .metric-value {
        margin: 0;
        font-size: 1.9rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
    }

    .payroll-history-suite .filter-shell,
    .payroll-history-suite .table-shell,
    .payroll-history-suite .transaction-details {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
    }

    .payroll-history-suite .filter-shell {
        padding: 18px;
        background: #f8fafc;
    }

    .payroll-history-suite .filter-label {
        display: block;
        margin-bottom: 0.35rem;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .payroll-history-suite .table-shell {
        overflow: hidden;
    }

    .payroll-history-suite .section-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
    }

    .payroll-history-suite .section-copy {
        color: #64748b;
        margin: 0;
    }

    .payroll-history-suite .toolbar-shell {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .payroll-history-suite .search-shell {
        position: relative;
    }

    .payroll-history-suite .search-shell .form-control {
        min-height: 44px;
        padding-left: 2.7rem;
        border-radius: 14px;
        border-color: #cbd5e1;
        background: #fff;
    }

    .payroll-history-suite .search-shell .search-icon {
        position: absolute;
        top: 50%;
        left: 14px;
        transform: translateY(-50%);
        color: #64748b;
        pointer-events: none;
    }

    .payroll-history-suite .action-btn {
        border-radius: 999px;
        font-weight: 700;
    }

    .payroll-history-suite .transaction-details {
        padding: 18px;
    }

    .payroll-history-suite .transaction-details h6 {
        margin-bottom: 14px;
        font-weight: 800;
        color: #0f172a;
    }

    .payroll-history-suite .modal-content {
        border: 1px solid rgba(255, 255, 255, 0.82);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.12);
    }

    .payroll-history-suite .modal-header,
    .payroll-history-suite .modal-footer {
        border-color: #e2e8f0;
        background: #f8fafc;
    }
</style>

<div class="payroll-history-suite">
<!-- Summary Cards -->
<div class="row summary-row">
    <div class="col-md-4">
        <div class="card metric-card h-100">
            <div class="card-body">
                <span class="metric-label">Employees Paid</span>
                <h2 class="metric-value">{{ $all_paid_sal_sum ?? 0 }}</h2>
                <p class="mb-0 text-muted mt-2">Completed payroll records across the tracked history.</p>
            </div><!-- end card body -->
        </div> <!-- end card-->
    </div> <!-- end col-4-->

    <div class="col-md-4">
        <div class="card metric-card h-100">
            <div class="card-body">
                <span class="metric-label">This Month</span>
                <h2 class="metric-value">{{ $cur_month_sal ?? 0 }}</h2>
                <p class="mb-0 text-muted mt-2">Total salary value already processed in the current month.</p>
            </div><!-- end card body -->
        </div> <!-- end card-->
    </div> <!-- end col-4-->

    <div class="col-md-4">

        <div class="card metric-card h-100">
            <div class="card-body">
                <span class="metric-label">Pending</span>
                <h2 class="metric-value">{{ $pending_all_sal ?? 0 }}</h2>
                <p class="mb-0 text-muted mt-2">Remaining payroll payments still waiting to be settled.</p>
            </div><!-- end card body -->
        </div> <!-- end card-->
    </div>

</div>

<!-- Filter Section -->
<div class="filter-shell mt-4">
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="dateRange" class="filter-label">Date Range</label>
            <select class="form-select" id="dateRange">
                <option selected>All Time</option>
                <option>This Month</option>
                <option>Last Month</option>
                <option>Last 3 Months</option>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label for="statusFilter" class="filter-label">Status</label>
            <select class="form-select" id="statusFilter">
                <option selected>All Statuses</option>
                <option>Paid</option>
                <option>UnPaid</option>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label for="searchInput" class="filter-label">Search</label>
            <div class="search-shell">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="form-control" id="searchInput" placeholder="Search amount,payment method...">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 text-end">
            <button class="btn btn-outline-secondary me-2" id="applyPayrollFilters">
                <i class="fas fa-filter me-1"></i> Apply Filters
            </button>
            <button class="btn btn-outline-danger" id="resetPayrollFilters">
                <i class="fas fa-redo me-1"></i> Reset
            </button>
        </div>
    </div>
</div>

<!-- Export Section -->
<div class="toolbar-shell">
    <div>
        <h4 class="section-title">Payment History</h4>
        <p class="section-copy">Review processed payouts, inspect receipt details, and drill into transaction data from one cleaner payroll history panel.</p>
    </div>
</div>

<!-- Payroll Table -->
<div class="card table-shell border-0">

    <div class="card-body p-0">
        <div class="table-responsive" style="max-height:350px; overflow-y:auto;">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="paymentTableBody">
                    @foreach ($payment_history as $history)
                        <tr>
                            <td>{{ $history->payment_date ?? '' }}</td>
                            <td>{{ $history->amount ?? 0 }}</td>
                            <td><span class="payment-method"><i class="fas fa-building me-1"></i>
                                    {{ $history->payment_method ?? '' }}</span></td>
                            <td><span class="badge rounded-pill bg-success">{{ $history->payment_status ?? '' }}</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary action-btn"
                                    onclick="showPaymentDetails({{ $history->id }})">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                @if (!empty($history->attachment))
                                    <a href="{{ route('payrolls.download_payroll_attachment', $history->id) }}"
                                        class="btn btn-sm btn-outline-secondary action-btn">
                                        <i class="fas fa-receipt"></i> Receipt
                                        </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
</div>



<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="transaction-details">
                            <h6 class="mb-1"><i class="fas fa-info-circle me-2 "></i>Payment Information</h6>
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Transaction ID:</div>
                                <div class="col-7" id="transaction_id"></div>

                            </div>
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Date & Time:</div>
                                <div class="col-7" id="payment_date"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Payment Method:</div>
                                <div class="col-7" id="payment_method"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Status:</div>
                                <div class="col-7 "><span class="status-badge bg-success" id="status"></span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="transaction-details">
                            <h6 class="mb-1"><i class="fas fa-user me-2"></i>Employee Information</h6>
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Name:</div>
                                <div class="col-7" id="employee_name"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Employee ID:</div>
                                <div class="col-7" id="employee_id"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Department:</div>
                                <div class="col-7" id="employee_dept_name"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Bank Account:</div>
                                <div class="col-7" id="employee_bank_numb"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="transaction-details mt-3">
                    <h6><i class="fas fa-receipt me-2"></i>Payment Breakdown</h6>
                    <div class="row mb-2">
                        <div class="col-5 fw-bold">Basic Salary:</div>
                        <div class="col-7" id="employee_salary"></div>
                    </div>
                    {{-- <div class="row mb-2">
                                <div class="col-5 fw-bold">Overtime:</div>
                                <div class="col-7">$300.00</div>
                            </div> --}}
                    <div class="row mb-2">
                        <div class="col-5 fw-bold">Bonus:</div>
                        <div class="col-7" id="employee_bonus"></div>
                    </div>
                    {{-- <div class="row mb-2">
                                <div class="col-5 fw-bold">Deductions:</div>
                                <div class="col-7">-$200.00</div>
                            </div> --}}
                    {{-- <div class="row mb-2">
                                <div class="col-5 fw-bold">Tax:</div>
                                <div class="col-7">-$350.00</div>
                            </div> --}}
                    <hr>
                    <div class="row mb-2">
                        <div class="col-5 fw-bold">Net Amount:</div>
                        <div class="col-7 fw-bold" id="final_salary"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                <a id="print_receipt_btn" href="#" class="btn btn-primary">
                    <i class="fas fa-print me-1"></i> Print Receipt
                </a>
            </div>
        </div>
    </div>
</div>

</div>

<script>
    function showPaymentDetails(id) {

        $.ajax({
            url: "{{ route('payrolls.view_payment_history', '') }}/" + id,
            type: "GET",
            success: function(data) {
                $('#transaction_id').text(data.transaction_id ?? 'N/A');
                $('#payment_date').text(data.payment_date ?? 'N/A');
                $('#payment_method').text(data.payment_method ?? 'N/A');
                $('#status').text(data.payment_status ?? 'N/A');
                $('#employee_name').text(data.employee_name ?? 'N/A');
                $('#employee_id').text(data.employee_id ?? 'N/A');
                $('#employee_dept_name').text(data.employee_dept_name ?? 'N/A');
                $('#employee_bank_numb').text(data.employee_bank_numb ?? 'N/A');
                $('#employee_salary').text(data.employee_salary ?? 'N/A');
                $('#employee_bonus').text(data.employee_bonus ?? 'N/A');
                $('#final_salary').text(data.final_salary ?? 'N/A');

                let printUrl = "{{ route('payrolls.download_payment_history', ':id') }}";
                printUrl = printUrl.replace(':id', id);
                $("#print_receipt_btn").attr("href", printUrl);


                $('#transactionModal').modal('show');
            },
            error: function() {
                alert("Error loading payment details");
            }
        });
    }

    function loadPaymentHistory() {

        let dateRange = $("#dateRange").val();
        let status = $("#statusFilter").val();
        let search = $("#searchInput").val();

        $.ajax({
            url: "{{ route('payrolls.logs.filter', $emp_id) }}",
            type: "GET",
            data: {
                dateRange: dateRange,
                status: status,
                search: search
            },
            success: function(response) {

                let rows = "";

                if (response.length === 0) {
                    rows = `<tr><td colspan="5" class="text-center">No records found</td></tr>`;
                } else {

                    response.forEach(function(item) {
                        rows += `
                    <tr>
                        <td>${item.payment_date ?? ""}</td>
                        <td>${item.amount ?? 0}</td>
                        <td>${item.payment_method ?? ""}</td>
                        <td><span class="badge bg-success">${item.payment_status}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="showPaymentDetails(${item.id})">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    </tr>`;
                    });
                }

                $("#paymentTableBody").html(rows);
            }
        });
    }

    // 🔹 Apply Filters Button
    $("#applyPayrollFilters").click(function() {
        loadPaymentHistory();
    });
    $("#resetPayrollFilters").click(function() {
        $("#dateRange").val("All Time");
        $("#statusFilter").val("All Statuses");
        $("#searchInput").val("");
        loadPaymentHistory();
    });
    $("#searchInput").keyup(function() {
        loadPaymentHistory();
    });
    $("#dateRange, #statusFilter").change(function() {
        loadPaymentHistory();
    });
</script>
