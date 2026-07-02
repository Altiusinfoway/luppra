@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Sales Target</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('sales-targets.index') }}">Sales Target</a></li>
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
                                <h5 class="card-title  mb-0">Sales Target Edit</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('sales-targets.update', $sales_target['id']) }}" method="POST"
                                id="sales_targetsForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="target">Target <span
                                                class="text-danger">*</span></label>
                                        <input type="number" min="1" step="0.01" name="target" class="form-control"
                                            placeholder="Enter Target" value="{{ old('target', $sales_target->min_target ?? '') }}">
                                        @if ($errors->has('target'))
                                            <div class="error text-danger">{{ $errors->first('target') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="incentive_mode">Incentive Mode <span
                                                class="text-danger">*</span></label>
                                        <select name="incentive_mode" id="incentive_mode" class="form-control">
                                            @php($mode = old('incentive_mode', $sales_target->incentive_mode ?? 'percent_over_target'))
                                            <option value="percent_over_target" {{ $mode === 'percent_over_target' ? 'selected' : '' }}>% on Amount Above Target</option>
                                            <option value="percent_on_achieved" {{ $mode === 'percent_on_achieved' ? 'selected' : '' }}>% on Achieved Amount</option>
                                            <option value="fixed_on_achieve" {{ $mode === 'fixed_on_achieve' ? 'selected' : '' }}>Fixed on Target Achieved</option>
                                            <option value="slab" {{ $mode === 'slab' ? 'selected' : '' }}>Slab Based</option>
                                        </select>
                                        @if ($errors->has('incentive_mode'))
                                            <div class="error text-danger">{{ $errors->first('incentive_mode') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="row" id="single_incentive_wrap">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="incentive_value">Incentive Value <span class="text-danger">*</span></label>
                                        <input type="number" min="0" step="0.01" name="incentive_value" id="incentive_value" class="form-control"
                                            placeholder="Enter incentive value"
                                            value="{{ old('incentive_value', $sales_target->incentive_value ?? $sales_target->incentive ?? 0) }}">
                                        <small class="text-muted" id="incentive_value_hint">% or Rs value based on mode.</small>
                                        @if ($errors->has('incentive_value'))
                                            <div class="error text-danger">{{ $errors->first('incentive_value') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div id="slab_wrap" class="border rounded p-3 mb-3" style="display:none;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label mb-0">Slab Rules</label>
                                        <button type="button" class="btn btn-sm btn-primary" id="add_slab_rule">Add Slab</button>
                                    </div>
                                    <div id="slab_rules">
                                        @php($slabs = old('slab_from') ? [] : ($sales_target->incentive_slabs ?? []))
                                        @if(old('slab_from'))
                                            @for($i = 0; $i < count(old('slab_from', [])); $i++)
                                                <div class="row g-2 align-items-end slab-row mb-2">
                                                    <div class="col-md-2"><input type="number" min="0" step="0.01" class="form-control" name="slab_from[]" value="{{ old('slab_from.'.$i) }}" placeholder="From %"></div>
                                                    <div class="col-md-2"><input type="number" min="0" step="0.01" class="form-control" name="slab_to[]" value="{{ old('slab_to.'.$i) }}" placeholder="To %"></div>
                                                    <div class="col-md-4">
                                                        <select class="form-control" name="slab_type[]">
                                                            @php($stype = old('slab_type.'.$i, 'percent_over_target'))
                                                            <option value="percent_over_target" {{ $stype === 'percent_over_target' ? 'selected' : '' }}>% on Above Target</option>
                                                            <option value="percent_on_achieved" {{ $stype === 'percent_on_achieved' ? 'selected' : '' }}>% on Achieved</option>
                                                            <option value="fixed_on_achieve" {{ $stype === 'fixed_on_achieve' ? 'selected' : '' }}>Fixed Amount</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3"><input type="number" min="0" step="0.01" class="form-control" name="slab_value[]" value="{{ old('slab_value.'.$i) }}" placeholder="Value"></div>
                                                    <div class="col-md-1"><button type="button" class="btn btn-sm btn-danger remove-slab">X</button></div>
                                                </div>
                                            @endfor
                                        @elseif(!empty($slabs))
                                            @foreach($slabs as $slab)
                                                <div class="row g-2 align-items-end slab-row mb-2">
                                                    <div class="col-md-2"><input type="number" min="0" step="0.01" class="form-control" name="slab_from[]" value="{{ $slab['from_pct'] ?? 0 }}" placeholder="From %"></div>
                                                    <div class="col-md-2"><input type="number" min="0" step="0.01" class="form-control" name="slab_to[]" value="{{ $slab['to_pct'] ?? '' }}" placeholder="To %"></div>
                                                    <div class="col-md-4">
                                                        <select class="form-control" name="slab_type[]">
                                                            <option value="percent_over_target" {{ ($slab['type'] ?? '') === 'percent_over_target' ? 'selected' : '' }}>% on Above Target</option>
                                                            <option value="percent_on_achieved" {{ ($slab['type'] ?? '') === 'percent_on_achieved' ? 'selected' : '' }}>% on Achieved</option>
                                                            <option value="fixed_on_achieve" {{ ($slab['type'] ?? '') === 'fixed_on_achieve' ? 'selected' : '' }}>Fixed Amount</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3"><input type="number" min="0" step="0.01" class="form-control" name="slab_value[]" value="{{ $slab['value'] ?? 0 }}" placeholder="Value"></div>
                                                    <div class="col-md-1"><button type="button" class="btn btn-sm btn-danger remove-slab">X</button></div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <small class="text-muted">Use achieved % range. Keep To % empty for open ended slab.</small>
                                    @if($errors->has('slab_value'))
                                        <div class="error text-danger">{{ $errors->first('slab_value') }}</div>
                                    @endif
                                </div>
                                <div class="text-center mt-3">
                                    <button type="submit" class="btn btn-success w-sm">Submit</button>
                                </div>
                            </form>
                        </div>
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
