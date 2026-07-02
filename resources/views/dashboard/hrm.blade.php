@extends('layouts.app')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-3px);
        }

        .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e6f4f1;
            color: #0ca678;
            font-size: 20px;
        }

        .progress-bar-custom {
            background-color: #0ca678;
        }

        body {
            font-size: 15px;
        }

        .data {
            padding: 3%;
        }
    </style>

    <div class="page-content">

        <div class="data py-2">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card dashboard-card p-3">
                        <div class="d-flex align-items-center justify-content-between">

                            <div class="d-flex align-items-center">
                                <div class="icon-circle me-2">
                                    <i class="fa fa-users"></i>
                                </div>
                                <span>Total Employees</span>
                            </div>

                            <div class="text-end">
                                <h4 class="fw-bold mb-0">{{ $total_emp_count }} </h4>
                                <small class="text-muted">Total Count</small>
                            </div>
                        </div>
                        <hr>

                        <div class="row">
                            <div class="col-12 d-flex justify-content-center">
                                <a href="{{ route('employees.index') }}" class="btn btn-success mt-1">
                                    View All →
                                </a>
                            </div>
                        </div>

                    </div>
                </div>


                <div class="col-md-3">
                    <div class="card dashboard-card p-3">
                        <div class="d-flex align-items-center justify-content-between">

                            <div class="d-flex align-items-center">
                                <div class="icon-circle me-2">
                                    <i class="fa-solid fa-house-circle-check"></i>
                                </div>
                                <span>Leave Requests Today</span>
                            </div>

                            <div class="text-end">
                                <h4 class="fw-bold mb-0">{{ $leave_pending_count }} </h4>
                                <small class="text-muted">Pending Leave</small>
                            </div>
                        </div>
                        <hr>

                        <div class="row">
                            <div class="col-12 d-flex justify-content-center">
                                <a href="{{ route('leaves.index') }}" class="btn btn-success mt-1">
                                    View All →
                                </a>
                            </div>
                        </div>

                    </div>
                </div>


                <div class="col-md-3">
                    <div class="card dashboard-card p-3">
                        <div class="d-flex align-items-center justify-content-between">

                            <!-- Left side (icon + title) -->
                            <div class="d-flex align-items-center">
                                <div class="icon-circle me-2">
                                    <i class="fa-solid fa-person-chalkboard"></i>
                                </div>
                                <span>Attendance Summary Today</span>
                            </div>

                            <!-- Right side (Present + Absent spaced) -->
                            <div class="d-flex">
                                <div class="text-end me-4"> <!-- add margin-end -->
                                    <h4 class="fw-bold mb-0">{{ $present_emp_count }} </h4>
                                    <small class="text-muted">Present</small>
                                </div>
                                <div class="text-end">
                                    <h4 class="fw-bold mb-0">{{ $absent_emp_count }} </h4>
                                    <small class="text-muted">Absent</small>
                                </div>
                            </div>
                        </div>
                        <hr>

                        <div class="row">
                            <div class="col-12 d-flex justify-content-center">
                                <a href="{{ route('attendances.report') }}" class="btn btn-success mt-1">
                                    View All →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card dashboard-card p-3">
                        <div class="d-flex align-items-center justify-content-between">

                            <div class="d-flex align-items-center">
                                <div class="icon-circle me-2">
                                    <i class="fa-solid fa-user-clock"></i>
                                </div>
                                <span>Late Comers</span>
                            </div>

                            <div class="text-end">
                                <h4 class="fw-bold mb-0">{{ count($late_emp_list) }} </h4>
                                <small class="text-muted">Late Comers</small>
                            </div>
                        </div>
                        <hr>

                        <div class="row">
                            <div class="col-12 d-flex justify-content-center">
                                <a href="{{ route('attendances.report') }}" class="btn btn-success mt-1">
                                    View All →
                                </a>
                            </div>
                        </div>

                    </div>
                </div>




            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card dashboard-card p-3">
                        <!-- Card Header -->
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle me-2">
                                    <i class="fa-solid fa-building"></i>
                                </div>
                                <span class="fw-bold">Department-wise Count</span>
                            </div>
                        </div>
                        <hr class="my-2">

                        <!-- Department List -->
                        <div class="mt-2">
                            @foreach ($department_acc_emp_list as $deptName => $empCount)
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold">{{ $deptName }}</span>
                                    <span class="badge bg-success rounded-pill">{{ $empCount }}</span>
                                </div>
                            @endforeach
                        </div>

                        <hr class="my-2">

                        <!-- Footer Button -->
                        <div class="row">
                            <div class="col-12 d-flex justify-content-center">
                                <a href="{{ route('employees.index') }}" class="btn btn-success mt-1">
                                    View All →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @section('scripts')
        <!-- apexcharts -->
        <script src="{{ asset('public/build/assets/libs/apexcharts/apexcharts.min.js') }}"></script>

        <!-- Dashboard init -->
        <script src="{{ asset('public/build/assets/js/pages/dashboard-ecommerce.init.js') }}"></script>

        <!-- apexcharts -->
        <script src="{{ asset('public/build/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.0/dayjs.min.js') }}"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.0/plugin/quarterOfYear.min.js') }}"></script>

        <!-- mixed charts init -->
        <script src="{{ asset('public/build/assets/js/pages/apexcharts-mixed.init.js') }}"></script>

        <!-- apexcharts init -->
        <script src="{{ asset('public/build/assets/js/pages/apexcharts-column.init.js') }}"></script>
    @endsection
