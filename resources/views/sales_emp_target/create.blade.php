@extends('layouts.app')

@section('page-css')
<style>
.employee-target-form-suite {
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
}

.employee-target-form-suite .hero-shell,
.employee-target-form-suite .form-shell {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
}

.employee-target-form-suite .hero-shell {
    background:
        radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 30%),
        radial-gradient(circle at left center, rgba(139, 92, 246, 0.14), transparent 30%),
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.employee-target-form-suite .hero-eyebrow {
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

.employee-target-form-suite .summary-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.86);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
}

.employee-target-form-suite .summary-card .label {
    display: block;
    margin-bottom: 8px;
    color: #64748b;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.employee-target-form-suite .summary-card h3 {
    margin: 0;
    font-size: 1.7rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #0f172a;
}

.employee-target-form-suite .section-card {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #f8fafc;
    padding: 16px;
}
</style>
@endsection

@section('content')
    <div class="page-content employee-target-form-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-7">
                                    <span class="hero-eyebrow">Employee Performance</span>
                                    <h2 class="mt-3 mb-2">Create Target Assignment</h2>
                                    <p class="text-muted mb-0">Assign target plans to sales employees with multi-row input inside the same refreshed performance form shell.</p>
                                </div>
                                <div class="col-lg-5">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item active">Create</li>
                                        </ol>
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
                            <span class="label">Employee Performance</span>
                            <h3>New Assignment</h3>
                            <p class="text-muted mb-0 mt-2">Assign sales targets in the same KPI-first setup language used across the refreshed performance workflow.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Structure</span>
                            <h3>Multi-Row Plan</h3>
                            <p class="text-muted mb-0 mt-2">Group employee, target, and incentive snapshots into one cleaner assignment panel with repeatable rows.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card form-shell">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title mb-0">Target Add</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('sales-employee-targets.store') }}" method="POST" id="empTargetSet">
                                @csrf
                                <div class="section-card">
                                    <div class="mb-3">
                                        <h6 class="mb-1">Assignment rows</h6>
                                        <p class="text-muted mb-0">Add one or more employee-target mappings and review the incentive snapshot before saving the assignment set.</p>
                                    </div>
                                    <div id="targetContainer">
                                        <div class="row gy-2 mb-3 target-row">
                                            <div class="col-12 col-sm-6 col-md-3">
                                                <select name="user_id[]" class="form-control user-select">
                                                    <option value="">Select Employee</option>
                                                    @foreach ($user_sales as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-12 col-sm-6 col-md-3">
                                                <select name="sales_target_id[]" class="form-control sales-target-select">
                                                    <option value="">Select Target</option>
                                                    @foreach ($sales_target_list as $sales_target)
                                                        <option value="{{ $sales_target->id }}">{{ number_format((float) $sales_target->min_target, 2) }}</option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted incentive-preview d-block mt-1"></small>
                                            </div>

                                            <div class="col-12 col-sm-6 col-md-3">
                                                <input type="text" class="form-control incentive-input" name="incentive[]" placeholder="Auto incentive snapshot" readonly>
                                            </div>

                                            <div class="col-12 col-sm-6 col-md-3 d-flex align-items-center">
                                                <button type="button" class="btn btn-primary add-row me-2">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mb-3 mt-3">
                                    <button type="submit" class="btn btn-primary w-sm">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
$(document).ready(function() {

    $(document).on('change', '.sales-target-select', function () {
        let sales_target_id = $(this).val();
        let row = $(this).closest('.target-row');
        let incentiveField = row.find('.incentive-input');

        if (!sales_target_id) {
            incentiveField.val('');
            return;
        }

        let url = "{{ route('sales-employee-targets.get_sales_target_incentive', ':id') }}";
        url = url.replace(':id', sales_target_id);

        $.ajax({
            type: 'GET',
            url: url,
            success: function (res) {
                if (res.status === 'yes') {
                    incentiveField.val(res.incentive);
                    row.find('.incentive-preview').text(res.label || '');
                } else {
                    incentiveField.val(0);
                    row.find('.incentive-preview').text('');
                }
            },
            error: function () {
                show_toastr('error', 'Unable to fetch incentive. Please try again.');
            }
        });
    });

    $(document).on('click', '.add-row', function() {
        let newRow = `
        <div class="row gy-2 mb-3 target-row">
            <div class="col-12 col-sm-6 col-md-3">
                <select name="user_id[]" class="form-control user-select" required>
                    <option value="">Select Employee</option>
                    @foreach ($user_sales as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <select name="sales_target_id[]" class="form-control sales-target-select" required>
                    <option value="">Select Target</option>
                    @foreach ($sales_target_list as $sales_target)
                        <option value="{{ $sales_target->id }}">{{ number_format((float) $sales_target->min_target, 2) }}</option>
                    @endforeach
                </select>
                <small class="text-muted incentive-preview d-block mt-1"></small>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <input type="number" min="0" name="incentive[]" class="form-control incentive-input" placeholder="Auto incentive snapshot" readonly>
            </div>

            <div class="col-12 col-sm-6 col-md-3 d-flex align-items-center">
                <button type="button" class="btn btn-danger remove-row">x</button>
            </div>
        </div>`;
        $('#targetContainer').append(newRow);
    });

    $(document).on('click', '.remove-row', function() {
        if ($('.target-row').length > 1) {
            $(this).closest('.target-row').remove();
        } else {
            show_toastr('warning', 'At least one row is required.');
        }
    });

    $('#empTargetSet').on('submit', function(e) {
        e.preventDefault();

        let form = $(this);
        let url = form.attr('action');

        let isValid = true;
        form.find('select[required], input[required]').each(function() {
            if ($(this).val() === '') {
                show_toastr('error', 'All fields are required.');
                $(this).focus();
                isValid = false;
                return false;
            }
        });
        if (!isValid) return;

        let formArray = form.serializeArray();
        let formDataObj = {};
        formArray.forEach(item => {
            if (formDataObj[item.name]) {
                if (!Array.isArray(formDataObj[item.name])) {
                    formDataObj[item.name] = [formDataObj[item.name]];
                }
                formDataObj[item.name].push(item.value);
            } else {
                formDataObj[item.name] = item.value;
            }
        });

        postAjax(url, formDataObj, function(res) {
            if (res.error === 'yes') {
                show_toastr('error', res.message || 'Something went wrong.');
                return;
            }

            show_toastr('success', res.message || 'Target saved successfully!');

            if (res.redirect_route) {
                setTimeout(function() {
                    window.location.href = res.redirect_route;
                }, 1500);
            }
        });
    });
});
    </script>
@endsection
