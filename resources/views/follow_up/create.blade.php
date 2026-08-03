@extends('layouts.app')

@section('page-css')
    <style>
        .workflow-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .workflow-suite .hero-shell,
        .workflow-suite .form-shell {
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

        .workflow-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.86);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .workflow-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .workflow-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .workflow-suite .section-card {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 16px;
        }

        .star {
            display: none;
        }

        .star + label {
            font-size: 24px;
            color: #ccc;
            cursor: pointer;
        }

        .star:checked ~ label {
            color: #ffc700;
        }

        .star + label:hover,
        .star + label:hover ~ label {
            color: #deb217;
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
                                    <h1 class="mb-3">Create Follow-Up</h1>
                                    <p class="text-muted mb-0">Schedule the next follow-up, update lead status, and log communication from a cleaner CRM form experience.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('follow-ups.list') }}">Follow-Up</a></li>
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
                            <span class="label">CRM Follow-Up</span>
                            <h3>New Touchpoint</h3>
                            <p class="text-muted mb-0 mt-2">Create the next lead interaction in the same dashboard-driven workflow language used across the refreshed CRM screens.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Fields</span>
                            <h3>Lead + Date</h3>
                            <p class="text-muted mb-0 mt-2">Keep lead, stage, date, and communication notes grouped into one focused follow-up configuration section.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card form-shell">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title mb-0">Follow-Up Add</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('follow-ups.store', $dynamic_slug) }}" method="POST" id="followUpForm">
                                @csrf
                                <div class="section-card">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="lead_id">Select Lead <span class="text-danger">*</span></label>
                                                        <select name="lead_id" id="lead_id" class="form-select" required>
                                                            <option value="">Select Lead</option>
                                                            @foreach ($lead_list as $id => $name)
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
                                                        <label class="form-label" for="stage_id">Status <span class="text-danger">*</span></label>
                                                        <select name="stage_id" id="stage_id" class="form-control">
                                                            <option value="">Select Lead Status</option>
                                                            @foreach ($lead_status_list as $id => $name)
                                                                <option value="{{ $id }}">{{ $name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @if ($errors->has('stage_id'))
                                                            <div class="error text-danger">{{ $errors->first('stage_id') }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="datepicker-range">Next Follow up Date <span class="text-danger">*</span></label>
                                                        {{ Form::date('next_date', null, ['class' => 'form-control datepicker-range', 'id' => 'datepicker-range', 'data-provider' => 'flatpickr', 'data-range' => 'true']) }}
                                                        @if ($errors->has('next_date'))
                                                            <div class="error text-danger">{{ $errors->first('next_date') }}</div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="chat">Follow up Communication<span class="text-danger">*</span></label>
                                                        <textarea name="chat" id="chat" class="form-control" rows="5"></textarea>
                                                        @if ($errors->has('chat'))
                                                            <div class="error text-danger">{{ $errors->first('chat') }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mb-3">
                                    <button type="submit" class="btn btn-primary w-sm" id="FollowupAddBtn">Submit</button>
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
document.getElementById('followUpForm').addEventListener('submit', function () {
    const btn = document.getElementById('FollowupAddBtn');
    btn.disabled = true;
    btn.innerText = 'processing...';
});
</script>
@endsection
