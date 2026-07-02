<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax())
        {
            try {
                $query = Department::select('id', 'name');

                $data = $query->orderBy('id', 'desc')->get();

                return DataTables::of($data)
                ->addIndexColumn()
                    ->addColumn('action', function($row) {
                          $editUrl = route('departments.edit', [$row->id]);

                            $actions = '<div class="dropdown d-inline-block">
                                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-fill align-middle"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">';


                            if (\Auth::user()->can('edit department')) {
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
                    ->rawColumns(['action'])
                    ->setRowClass('main-row')
                    ->make(true);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }
        return view('department.index');
    }

    /* <li>
                                            <a class="dropdown-item remove-item-btn"
                                            data-delete-popup="true"
                                            data-bs-original-title="You are about to delete a Department?"
                                            data-bs-original-description="Deleting your Department will remove all of your information from our database."
                                            data-url="' . $deleteUrl . '"
                                            data-method="DELETE"
                                            data-cb="afterDelete"
                                            href="javascript:void(0)">
                                                <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                            </a>
                                        </li>
                                        */

    public function create()
    {
        return view('department.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required|regex:/^[\pL\s]+$/u',
        ]);

        $input = $request->all();
        Department::create($input);

        return redirect()->route('departments.index')->with(['success'=>'Department has been added successfully']);
    }

    public function edit(Request $request,$id)
    {
        $data['department']  =  Department::find($id);
        return view('department.edit',$data);
    }

    public function update(Request $request,$id)
    {
        $request->validate([
            'name'=>'required|regex:/^[\pL\s]+$/u',
        ]);
        $department  =  Department::find($id);
        $input =$request->all();
        $department->update($input);
        return redirect()->route('departments.index')->with(['success'=>'Department has been updated successfully']);
    }

    public function delete(Request $request,$id)
    {
        $department  =  Department::find($id);
        $department->delete();

        return response()->json([
            'success' => 'Department has been deleted successfully.'
        ], 200);

        return redirect()->route('departments.index');
    }
}
