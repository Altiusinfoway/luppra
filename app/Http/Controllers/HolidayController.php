<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Holiday;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax())
        {
            try {
                $query = Holiday::select('id', 'name', 'start_date', 'end_date');

                $data = $query->orderBy('id', 'desc')->get();

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function ($row) {
                        $user = auth()->user();
                        $html = '';

                        if ($user->can('edit holiday') || $user->can('delete holiday')) {
                            $html .= '<div class="dropdown d-inline-block">
                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-fill align-middle"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">';

                            if ($user->can('edit holiday')) {
                                $html .= '<li>
                                    <a href="' . route('holidays.edit', $row->id) . '" class="dropdown-item edit-item-btn">
                                        <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                    </a>
                                </li>';
                            }

                            if ($user->can('delete holiday')) {
                                $html .= '<li>
                                    <a class="dropdown-item remove-item-btn"
                                        data-delete-popup="true"
                                        data-bs-original-title="You are about to delete a Holiday ?"
                                        data-bs-original-description="Deleting your Holiday will remove all of your information from our database."
                                        data-url="' . route('holidays.delete', $row->id) . '"
                                        data-method="DELETE"
                                        data-cb="afterDelete"
                                        href="javascript:void(0)">
                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                    </a>
                                </li>';
                            }

                            $html .= '</ul></div>';
                        }

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

        return view('holiday.index');
    }

    public function create()
    {
        return view('holiday.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required|regex:/^[\pL\s]+$/u',
            'start_date'=>'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active'=>'required'
        ]);

        $input = $request->all();
        Holiday::create($input);

        return redirect()->route('holidays.index')->with(['success'=>'Holiday has been added successfully']);
    }

    public function edit(Request $request,$id)
    {
        $data['holiday']  =  Holiday::find($id);
        return view('holiday.edit',$data);
    }

    public function update(Request $request,$id)
    {
        $request->validate([
            'name'=>'required|regex:/^[\pL\s]+$/u',
            'start_date'=>'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active'=>'required'
        ]);

        $holiday  = Holiday::find($id);
        $input =$request->all();
        $holiday->update($input);
        return redirect()->route('holidays.index')->with(['success'=>'Holiday has been updated successfully']);
    }

    public function delete(Request $request,$id)
    {
        $holiday  =  Holiday::find($id);
        $holiday->delete();

        return response()->json([
            'success' => 'Holiday has been deleted successfully.'
        ], 200);

        return redirect()->route('holidays.index');
    }
}
