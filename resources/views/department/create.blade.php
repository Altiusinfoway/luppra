@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Department Section</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Department </a></li>
                                <li class="breadcrumb-item active">Create</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="row">
                <!-- Varying Modal Content -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title  mb-0">Department Add</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('departments.store') }}" method="POST" id="departmentsForm">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label" for="name">name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" name="name" class="form-control" id="name"
                                                        placeholder="Enter name">
                                                    @if ($errors->has('name'))
                                                        <div class="error text-danger">{{ $errors->first('name') }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="text-center mb-3">
                                        <button type="submit" class="btn btn-success w-sm" id="departmentAddBtn">Submit</button>
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
document.getElementById('departmentsForm').addEventListener('submit', function () {
    const btn = document.getElementById('departmentAddBtn');
    btn.disabled = true;
    btn.innerText = 'Submitting...';
});
</script>
@endsection
