<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\LeaveType;

class LeaveTypeController extends Controller
{
     public function index(Request $request)
    {
        if ($request->ajax())
        {
            try {
                $query = LeaveType::select('id', 'name');

                $data = $query->orderBy('id', 'desc')->get();

                return DataTables::of($data)
                ->addIndexColumn()
                    ->addColumn('action', function($row) {
                       $editUrl = route('leave-types.edit', [$row->id]);

                        $html = '<div class="dropdown d-inline-block">
                                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-more-fill align-middle"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">';

                        if (auth()->user()->can('edit leave type')) {
                            $html .= '<li>
                                        <a href="' . $editUrl . '" class="dropdown-item edit-item-btn">
                                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                        </a>
                                    </li>';
                        }

                        $html .= '</ul></div>';

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
        return view('leave_type.index');
    }

    public function create()
    {
        return view('leave_type.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required|regex:/^[\pL\s]+$/u',
        ]);

        $input = $request->all();
        LeaveType::create($input);

        return redirect()->route('leave-types.index')->with(['success'=>'Leave Type has been added successfully']);
    }

    public function edit(Request $request,$id)
    {
        $data['department']  =  LeaveType::find($id);
        return view('leave_type.edit',$data);
    }

    public function update(Request $request,$id)
    {
        $request->validate([
            'name'=>'required|regex:/^[\pL\s]+$/u',
        ]);
        $department  =  LeaveType::find($id);
        $input =$request->all();
        $department->update($input);
        return redirect()->route('leave-types.index')->with(['success'=>'Leave Type has been updated successfully']);
    }
}
