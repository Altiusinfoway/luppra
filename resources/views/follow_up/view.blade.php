@extends('layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Follow-Up</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('follow-ups.follow_up_lead','all') }}">Follow-Up List</a></li>
                            <li class="breadcrumb-item active">View</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Follow-Up Details</h5>
                    </div>

                    <div class="card-body">
                        @php
                            $status = null;
                            if (!empty($leadChat->stage_id)) {
                                $status = \App\Models\LeadStage::withTrashed()->find($leadChat->stage_id);
                            }
                        @endphp

                        <div class="mb-3">
                            <strong>Status:</strong> {{ $status->name ?? '' }}
                        </div>
                        <div class="mb-3">
                            <strong>Next Follow-up Date:</strong> {{ $leadChat->next_date ?? '' }}
                        </div>
                        <div class="mb-3">
                            <strong>Follow-up Communication:</strong> {{ $leadChat->chat }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end row-->

    </div><!-- container-fluid -->
</div>
@endsection
