@extends('layouts.app')

@section('content')

<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Designation Section</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('designations.index') }}">Designation</a></li>
                            <li class="breadcrumb-item active">Edit</li>
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
                            <h5 class="card-title  mb-0">Designation Edit</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('designations.update',$designation['id']) }}" method="POST" id="designationEditForm">
                        @csrf
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="name">Department <span class="text-danger">*</span></label>
                                            <select name="department_id" class="form-control">
                                                <option value="0">Select Department</option>
                                                @foreach ($department_list as $list)
                                                    <option value="{{ $list['id'] }}" {{ $list['id'] == $designation['department_id'] ? 'selected' : '' }}>{{ $list['name'] }}</option>
                                                @endforeach
                                            </select>
                                            @if($errors->has('department_id'))
                                                <div class="error text-danger">{{ $errors->first('department_id') }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="name">name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" id="name" placeholder="Enter name" value="{{ $designation['name'] }}">
                                        @if($errors->has('name'))
                                            <div class="error text-danger">{{ $errors->first('name') }}</div>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-success w-sm" id="designationEditBtn">Submit</button>
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
document.getElementById('designationEditForm').addEventListener('submit', function () {
    const btn = document.getElementById('designationEditBtn');
    btn.disabled = true;
    btn.innerText = 'processing...';
});
</script>
@endsection
