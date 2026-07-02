@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Target</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                {{-- <li class="breadcrumb-item"><a href="{{ route('sales-employee-targets.index') }}">Target </a></li> --}}
                                <li class="breadcrumb-item active">Create</li>
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
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title  mb-0"> Target Add</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('sales-employee-targets.store') }}" method="POST" id="empTargetSet">
                                @csrf

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
                                                    <option value="{{ $sales_target->id }}">{{ number_format((float)$sales_target->min_target, 2) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted incentive-preview d-block mt-1"></small>
                                        </div>

                                        <div class="col-12 col-sm-6 col-md-3">
                                            <input type="text" class="form-control incentive-input" name="incentive[]"
                                                placeholder="Auto incentive snapshot" readonly>
                                        </div>

                                        <div class="col-12 col-sm-6 col-md-3 d-flex align-items-center">
                                            <button type="button" class="btn btn-primary add-row me-2">+</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mb-3 mt-3">
                                    <button type="submit" class="btn btn-primary w-sm">Submit</button>
                                </div>

                            </form>
                            <!-- end card body -->
                        </div>
                    </div>
                </div><!--end col-->
            </div><!--end row-->

        </div>
        <!-- container-fluid -->
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
                        <option value="{{ $sales_target->id }}">{{ number_format((float)$sales_target->min_target, 2) }}</option>
                    @endforeach
                </select>
                <small class="text-muted incentive-preview d-block mt-1"></small>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <input type="number" min="0" name="incentive[]" class="form-control incentive-input" placeholder="Auto incentive snapshot" readonly>
            </div>

            <div class="col-12 col-sm-6 col-md-3 d-flex align-items-center">
                <button type="button" class="btn btn-danger remove-row">×</button>
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
