<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Designation;
use App\Models\Department;

class DesignationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax())
        {
            try {
                $query = Designation::with('departments')->select('id', 'name', 'department_id');
                $data = $query->orderBy('id', 'desc')->get();

                return DataTables::of($data)
                ->addIndexColumn()
                    ->addColumn('department_id', function ($row) {
                        return $row->departments->name ?? '';
                    })
                    ->addColumn('action', function($row){
                           $editUrl = route('designations.edit', [$row->id]);

                            $actions = '<div class="dropdown d-inline-block">
                                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-fill align-middle"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">';

                            // ---- EDIT PERMISSION CHECK ----
                            if (\Auth::user()->can('edit designation')) {
                                $actions .= '
                                    <li>
                                        <a href="' . $editUrl . '" class="dropdown-item edit-item-btn">
                                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                        </a>
                                    </li>';
                            }

                            $actions .= '</ul></div>';

                            return $actions;
                    })

                    ->rawColumns(['department_id','action'])
                    ->setRowClass('main-row')
                    ->make(true);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }
        return view('designation.index');
    }

    public function create(Request $request)
    {
        $data['department_list']=Department::all();
        return view('designation.create',$data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id'=>'required|not_in:0',
            'name'=>'required|regex:/^[\pL\s]+$/u',
        ]);

        $input=$request->all();
        Designation::create($input);

        return redirect()->route('designations.index')->with(['success'=>'Designation has been inserted successfully']);
    }

    public function edit(Request $request,$id)
    {
        $data['designation']=Designation::find($id);
        $data['department_list']=Department::all();
        return view('designation.edit',$data);
    }

    public function update(Request $request,$id)
    {
        $request->validate([
            'department_id'=>'required|not_in:0',
            'name'=>'required|regex:/^[\pL\s]+$/u',
        ]);

        $input=$request->all();
        $des = Designation::find($id);
        $des->update($input);

        return redirect()->route('designations.index')->with(['success'=>'Designation has been updated successfully']);
    }

    public function delete(Request $request,$id)
    {
        $input=$request->all();
        $des = Designation::find($id);
        $des->delete();

        return response()->json([
            'success' => 'Designation has been deleted successfully.'
        ], 200);

        return redirect()->route('designations.index');
    }

}
