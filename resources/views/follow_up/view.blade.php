@extends('layouts.app')

@section('page-css')
    <style>
        .workflow-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .workflow-suite .hero-shell,
        .workflow-suite .detail-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
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
                                <span class="hero-eyebrow">Follow-Up Workspace</span>
                                <h1 class="mb-3">Follow-Up Details</h1>
                                <p class="text-muted mb-0">Review the selected follow-up status, next action date, and communication history in the same refined workflow shell.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('follow-ups.follow_up_lead','all') }}">Follow-Up List</a></li>
                                        <li class="breadcrumb-item active">View</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card detail-shell">
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
