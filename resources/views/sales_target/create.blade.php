@extends('layouts.app')

@section('page-css')
<style>
.target-form-suite {
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
}

.target-form-suite .hero-shell,
.target-form-suite .form-shell {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
}

.target-form-suite .hero-shell {
    background:
        radial-gradient(circle at top right, rgba(139, 92, 246, 0.16), transparent 30%),
        radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.target-form-suite .hero-eyebrow {
    display: inline-flex;
    align-items: center;
    padding: 7px 12px;
    border-radius: 999px;
    border: 1px solid #ddd6fe;
    background: rgba(255, 255, 255, 0.86);
    color: #6d28d9;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.target-form-suite .summary-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.86);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
}

.target-form-suite .summary-card .label {
    display: block;
    margin-bottom: 8px;
    color: #64748b;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.target-form-suite .summary-card h3 {
    margin: 0;
    font-size: 1.7rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #0f172a;
}

.target-form-suite .section-card {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #f8fafc;
    padding: 16px;
}
</style>
@endsection

@section('content')
<div class="page-content target-form-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Performance Rules</span>
                                <h2 class="mt-3 mb-2">Create Sales Target</h2>
                                <p class="text-muted mb-0">Create new target and incentive logic inside the same lighter compensation-rule form shell.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('sales-targets.index') }}">Sales Target</a></li>
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
                        <span class="label">Performance Rules</span>
                        <h3>New Target</h3>
                        <p class="text-muted mb-0 mt-2">Create sales target logic in the same KPI-first setup language used across the refreshed performance dashboard.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Logic Scope</span>
                        <h3>Single + Slab</h3>
                        <p class="text-muted mb-0 mt-2">Keep target amount, incentive mode, and optional slab rules grouped into one focused configuration section.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="card form-shell">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title  mb-0">Sales Target Add</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('sales-targets.store') }}" method="POST" id="salesTargetForm">
                            @csrf
                            <div class="section-card">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="target">Target <span class="text-danger">*</span></label>
                                        <input type="number" min="1" step="0.01" name="target" class="form-control" placeholder="Enter Target Amount" value="{{ old('target') }}">
                                        @if($errors->has('target'))
                                            <div class="error text-danger">{{ $errors->first('target') }}</div>
                                        @endif
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="incentive_mode">Incentive Mode <span class="text-danger">*</span></label>
                                        <select name="incentive_mode" id="incentive_mode" class="form-control">
                                            <option value="percent_over_target" {{ old('incentive_mode') === 'percent_over_target' ? 'selected' : '' }}>% on Amount Above Target</option>
                                            <option value="percent_on_achieved" {{ old('incentive_mode') === 'percent_on_achieved' ? 'selected' : '' }}>% on Achieved Amount</option>
                                            <option value="fixed_on_achieve" {{ old('incentive_mode') === 'fixed_on_achieve' ? 'selected' : '' }}>Fixed on Target Achieved</option>
                                            <option value="slab" {{ old('incentive_mode') === 'slab' ? 'selected' : '' }}>Slab Based</option>
                                        </select>
                                        @if($errors->has('incentive_mode'))
                                            <div class="error text-danger">{{ $errors->first('incentive_mode') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="row" id="single_incentive_wrap">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="incentive_value">Incentive Value <span class="text-danger">*</span></label>
                                        <input type="number" min="0" step="0.01" name="incentive_value" id="incentive_value" class="form-control" placeholder="Enter incentive value" value="{{ old('incentive_value', old('incentive')) }}">
                                        <small class="text-muted" id="incentive_value_hint">% or Rs value based on mode.</small>
                                        @if($errors->has('incentive_value'))
                                            <div class="error text-danger">{{ $errors->first('incentive_value') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div id="slab_wrap" class="border rounded p-3 mb-3" style="display:none;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label mb-0">Slab Rules</label>
                                        <button type="button" class="btn btn-sm btn-primary" id="add_slab_rule">Add Slab</button>
                                    </div>
                                    <div id="slab_rules"></div>
                                    <small class="text-muted">Use achieved % range. Keep To % empty for open ended slab.</small>
                                    @if($errors->has('slab_value'))
                                        <div class="error text-danger">{{ $errors->first('slab_value') }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="text-center mb-3">
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
$(function () {
    function slabRow(from = '', to = '', type = 'percent_over_target', value = '') {
        return `
            <div class="row g-2 align-items-end slab-row mb-2">
                <div class="col-md-2"><input type="number" min="0" step="0.01" class="form-control" name="slab_from[]" value="${from}" placeholder="From %"></div>
                <div class="col-md-2"><input type="number" min="0" step="0.01" class="form-control" name="slab_to[]" value="${to}" placeholder="To %"></div>
                <div class="col-md-4">
                    <select class="form-control" name="slab_type[]">
                        <option value="percent_over_target" ${type === 'percent_over_target' ? 'selected' : ''}>% on Above Target</option>
                        <option value="percent_on_achieved" ${type === 'percent_on_achieved' ? 'selected' : ''}>% on Achieved</option>
                        <option value="fixed_on_achieve" ${type === 'fixed_on_achieve' ? 'selected' : ''}>Fixed Amount</option>
                    </select>
                </div>
                <div class="col-md-3"><input type="number" min="0" step="0.01" class="form-control" name="slab_value[]" value="${value}" placeholder="Value"></div>
                <div class="col-md-1"><button type="button" class="btn btn-sm btn-danger remove-slab">X</button></div>
            </div>`;
    }

    function syncModeUI() {
        const mode = $('#incentive_mode').val();
        const singleWrap = $('#single_incentive_wrap');
        const slabWrap = $('#slab_wrap');
        const hint = $('#incentive_value_hint');

        if (mode === 'slab') {
            singleWrap.hide();
            slabWrap.show();
            if ($('#slab_rules .slab-row').length === 0) {
                $('#slab_rules').append(slabRow());
            }
            return;
        }

        slabWrap.hide();
        singleWrap.show();
        if (mode === 'fixed_on_achieve') {
            hint.text('Enter fixed Rs amount.');
        } else {
            hint.text('Enter percentage value.');
        }
    }

    $('#incentive_mode').on('change', syncModeUI);
    $('#add_slab_rule').on('click', function () {
        $('#slab_rules').append(slabRow());
    });
    $(document).on('click', '.remove-slab', function () {
        $(this).closest('.slab-row').remove();
    });

    syncModeUI();
});
</script>
@endsection
