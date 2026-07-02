<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\SalesTarget;

class SalesTargetController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax())
        {
            try {
                $query = SalesTarget::select('id', 'min_target', 'max_target', 'incentive', 'incentive_mode', 'incentive_value', 'incentive_slabs');

                $data = $query->orderBy('id', 'desc')->get();

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('target_value', function ($row) {
                        return number_format((float) ($row->min_target ?? 0), 2);
                    })
                    ->addColumn('incentive_rule', function ($row) {
                        return $row->incentiveSummary();
                    })
                    ->addColumn('action', function ($row) {
                        $user = auth()->user();
                        $html = '';

                        // if ($user->can('edit holiday') || $user->can('delete holiday')) {
                            $html .= '<div class="dropdown d-inline-block">
                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-fill align-middle"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">';

                            if ($user->can('edit sales target')) {
                                $html .= '<li>
                                    <a href="' . route('sales-targets.edit', $row->id) . '" class="dropdown-item edit-item-btn">
                                        <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                    </a>
                                </li>';
                            }

                            // if ($user->can('delete holiday')) {
                                // $html .= '<li>
                                //     <a class="dropdown-item remove-item-btn"
                                //         data-delete-popup="true"
                                //         data-bs-original-title="You are about to delete a Holiday ?"
                                //         data-bs-original-description="Deleting your Holiday will remove all of your information from our database."
                                //         data-url="' . route('holidays.delete', $row->id) . '"
                                //         data-method="DELETE"
                                //         data-cb="afterDelete"
                                //         href="javascript:void(0)">
                                //         <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                //     </a>
                                // </li>';
                            // }

                            $html .= '</ul></div>';
                        // }

                        return $html;
                    })

                    ->rawColumns(['action'])
                    ->setRowClass('main-row')
                    ->make(true);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }

        return view('sales_target.index');
    }

    public function create()
    {
        return view('sales_target.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'target' => 'required|numeric|min:1',
            'incentive_mode' => 'required|in:percent_over_target,percent_on_achieved,fixed_on_achieve,slab',
            'incentive_value' => 'nullable|numeric|min:0',
            'slab_from' => 'nullable|array',
            'slab_to' => 'nullable|array',
            'slab_type' => 'nullable|array',
            'slab_value' => 'nullable|array',
        ]);

        $input = [];
        $input['min_target'] = $validated['target'];
        $input['max_target'] = $validated['target'];
        $input['incentive_mode'] = $validated['incentive_mode'];
        $input['incentive_value'] = (float) ($validated['incentive_value'] ?? 0);
        $input['incentive_slabs'] = null;
        $input['incentive'] = $input['incentive_value'];

        if ($input['incentive_mode'] === 'slab') {
            $slabs = $this->buildSlabsFromRequest($request);
            if (empty($slabs)) {
                return back()->withErrors(['slab_value' => 'Please add at least one valid slab rule.'])->withInput();
            }
            $input['incentive_slabs'] = $slabs;
            $input['incentive'] = 0;
            $input['incentive_value'] = 0;
        }

        SalesTarget::create($input);

        return redirect()->route('sales-targets.index')->with(['success'=>'Sales Target has been added successfully']);
    }

    public function edit(Request $request,$id)
    {
        $data['sales_target'] = SalesTarget::find($id);
        return view('sales_target.edit',$data);
    }

    public function update(Request $request,$id)
    {
        $validated = $request->validate([
            'target' => 'required|numeric|min:1',
            'incentive_mode' => 'required|in:percent_over_target,percent_on_achieved,fixed_on_achieve,slab',
            'incentive_value' => 'nullable|numeric|min:0',
            'slab_from' => 'nullable|array',
            'slab_to' => 'nullable|array',
            'slab_type' => 'nullable|array',
            'slab_value' => 'nullable|array',
        ]);

        $sales_target  = SalesTarget::find($id);
        $input = [];
        $input['min_target'] = $validated['target'];
        $input['max_target'] = $validated['target'];
        $input['incentive_mode'] = $validated['incentive_mode'];
        $input['incentive_value'] = (float) ($validated['incentive_value'] ?? 0);
        $input['incentive_slabs'] = null;
        $input['incentive'] = $input['incentive_value'];

        if ($input['incentive_mode'] === 'slab') {
            $slabs = $this->buildSlabsFromRequest($request);
            if (empty($slabs)) {
                return back()->withErrors(['slab_value' => 'Please add at least one valid slab rule.'])->withInput();
            }
            $input['incentive_slabs'] = $slabs;
            $input['incentive'] = 0;
            $input['incentive_value'] = 0;
        }

        $sales_target->update($input);
        return redirect()->route('sales-targets.index')->with(['success'=>'Sales Target has been updated successfully']);
    }

    public function delete(Request $request,$id)
    {
        $sales_target  =  SalesTarget::find($id);
        $sales_target->delete();

        return response()->json([
            'success' => 'Sales Target has been deleted successfully.'
        ], 200);

        return redirect()->route('sales-targets.index');
    }

    private function buildSlabsFromRequest(Request $request): array
    {
        $fromList = $request->input('slab_from', []);
        $toList = $request->input('slab_to', []);
        $typeList = $request->input('slab_type', []);
        $valueList = $request->input('slab_value', []);

        $slabs = [];
        $count = max(count($fromList), count($toList), count($typeList), count($valueList));
        for ($i = 0; $i < $count; $i++) {
            $from = isset($fromList[$i]) ? (float) $fromList[$i] : 0;
            $toRaw = $toList[$i] ?? '';
            $to = ($toRaw === '' || $toRaw === null) ? null : (float) $toRaw;
            $type = (string) ($typeList[$i] ?? 'percent_over_target');
            $value = isset($valueList[$i]) ? (float) $valueList[$i] : 0;

            if ($value <= 0) {
                continue;
            }

            if (!in_array($type, ['percent_over_target', 'percent_on_achieved', 'fixed_on_achieve'], true)) {
                $type = 'percent_over_target';
            }

            $slabs[] = [
                'from_pct' => max(0, $from),
                'to_pct' => $to === null ? null : max($from, $to),
                'type' => $type,
                'value' => $value,
            ];
        }

        usort($slabs, function ($a, $b) {
            return ($a['from_pct'] <=> $b['from_pct']);
        });

        return $slabs;
    }
}
