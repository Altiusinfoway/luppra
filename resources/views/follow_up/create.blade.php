@extends('layouts.app')

@section('content')
    <style>
        .star {
            display: none;
        }

        .star+label {
            font-size: 24px;
            color: #ccc;
            cursor: pointer;
        }

        .star:checked~label {
            color: #ffc700;
        }

        .star+label:hover,
        .star+label:hover~label {
            color: #deb217;
        }
    </style>
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Follow-Up</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('follow-ups.list') }}">Follow-Up</a>
                                </li>
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
                                <h5 class="card-title  mb-0">Follow-Up Add</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('follow-ups.store',$dynamic_slug) }}" method="POST" id="followUpForm">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="lead_id">Select Lead <span
                                                            class="text-danger">*</span></label>
                                                    <select name="lead_id" id="lead_id" class="form-select" required>
                                                        <option value="">Select Lead</option>
                                                        @foreach ($lead_list as $id=>$name)
                                                            <option value="{{ $id }}" {{ (string) old('lead_id') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @if ($errors->has('lead_id'))
                                                        <div class="error text-danger">{{ $errors->first('lead_id') }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="next_date">Status <span
                                                            class="text-danger">*</span></label>
                                                     <select name="stage_id" class="form-control">
                                                        <option value="">Select Lead Status</option>
                                                        @foreach ($lead_status_list as $id=>$name)
                                                            <option value="{{ $id }}"> {{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @if ($errors->has('stage_id'))
                                                        <div class="error text-danger">{{ $errors->first('stage_id') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">

                                             <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="next_date">Next Follow up Date <span
                                                            class="text-danger">*</span></label>
                                                      {{ Form::date('next_date', null, ['class' => 'form-control datepicker-range', 'id' => 'datepicker-range','data-provider' => 'flatpickr', 'data-range' => 'true']) }}

                                                    @if ($errors->has('next_date'))
                                                        <div class="error text-danger">{{ $errors->first('next_date') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="chat">Follow up Communication<span
                                                            class="text-danger">*</span></label>
                                                   <textarea name="chat" class="form-control" rows="5"></textarea>
                                                    @if ($errors->has('chat'))
                                                        <div class="error text-danger">{{ $errors->first('chat') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>


                                        </div>




                                    </div>
                                </div>


                                <div class="text-center mb-3">
                                    <button type="submit" class="btn btn-success w-sm" id="FollowupAddBtn">Submit</button>
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
document.getElementById('followUpForm').addEventListener('submit', function () {
    const btn = document.getElementById('FollowupAddBtn');
    btn.disabled = true;
    btn.innerText = 'processing...';
});
</script>
@endsection
