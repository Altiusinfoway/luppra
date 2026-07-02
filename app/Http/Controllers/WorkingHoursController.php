<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\WorkingHours;

class WorkingHoursController extends Controller
{
    public function index(Request $request)
    {
         if ($request->ajax())
        {
            try {
                $query = WorkingHours::select('id', 'start_time', 'end_time', 'day');

                $data = $query->orderBy('id', 'desc')->get();

                    $dayList = [
                        1 => 'Monday',
                        2 => 'Tuesday',
                        3 => 'Wednesday',
                        4 => 'Thursday',
                        5 => 'Friday',
                        6 => 'Saturday',
                    ];

                return DataTables::of($data)
                ->addIndexColumn()
                  ->addColumn('day_name', function ($row) use ($dayList) {
                        return $dayList[$row->day] ?? '-';
                    })

                    ->addColumn('action', function ($row) {
                      $html = '';

                        $html .= '<div class="dropdown d-inline-block">
                                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-more-fill align-middle"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">';


                        if (auth()->user()->can('edit working hours')) {
                            $html .= '<li>
                                        <a href="' . route('working-hours.edit', $row->id) . '" class="dropdown-item edit-item-btn">
                                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                        </a>
                                    </li>';
                        }

                        $html .= '</ul></div>';

                        return $html;
                    })

                    ->rawColumns(['day_name','action'])
                    ->setRowClass('main-row')
                    ->make(true);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }
        return view('working_hours.index');
    }

    public function create(Request $request)
    {
        $data['day_list'] = [
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
            ];
        return view('working_hours.create',$data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'day'=>'required',
           'start_time' => 'required|date_format:H:i',
           'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $exists = WorkingHours::where('day', $request->day)->exists();

        if ($exists) {
            return back()->withErrors(['day' => 'This day is already assigned in another record.'])->withInput();
        }

        $input = $request->all();
        WorkingHours::create($input);

        return redirect()->route('working-hours.index')->with(['success'=>'Working Hours has been added successfully']);
    }

    public function edit(Request $request,$id)
    {
         $data['day_list'] = [
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
            ];
        $data['working_id']  =  WorkingHours::find($id);
        return view('working_hours.edit',$data);
    }

    public function update(Request $request,$id)
    {
        $request->validate([
            'day'=>'required',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',

        ]);

     $exists = WorkingHours::where('day', $request->day)
        ->where('id', '!=', $id)
        ->exists();

    if ($exists) {
        return back()->withErrors(['day' => 'This day is already assigned in another record.'])->withInput();
    }

    // Update the record
    $working = WorkingHours::findOrFail($id);
    $working->day = $request->day; // assuming it's saved as array or json
    $working->start_time = $request->start_time;
    $working->end_time = $request->end_time;
    $working->save();

         return redirect()->route('working-hours.index')->with(['success'=>'Working Hours has been updated successfully']);
    }

}
