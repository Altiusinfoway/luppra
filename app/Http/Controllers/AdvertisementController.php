<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Advertisement;

class AdvertisementController extends Controller
{
    public function index(Request $request)
    {
        $data['advertisement_list'] =Advertisement::all();
        return view('advertisement.index',$data);
    }

    public function create()
    {
        return view('advertisement.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required|regex:/^[\pL\s]+$/u',
            'amount'=>'required',
        ]);

        $input = $request->all();
        Advertisement::create($input);

        return redirect()->route('advertisements.index')->with(['success'=>'Advertisement has been added successfully']);
    }

    public function edit(Request $request,$id)
    {
        $data['advertisement']  =  Advertisement::find($id);
        return view('advertisement.edit',$data);
    }

    public function update(Request $request,$id)
    {
        $request->validate([
            'name'=>'required|regex:/^[\pL\s]+$/u',
            'amount'=>'required',
        ]);

        $advt  =  Advertisement::find($id);
        $input =$request->all();
        $advt->update($input);

        return redirect()->route('advertisements.index')->with(['success'=>'Advertisement has been updated successfully']);
    }

    public function delete(Request $request,$id)
    {
        $advt  =  Advertisement::find($id);
        $advt->delete();

        return response()->json([
            'success' => 'Advertisement has been deleted successfully.'
        ], 200);

        return redirect()->route('advertisements.index');
    }
}
