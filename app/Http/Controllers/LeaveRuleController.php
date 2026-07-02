<?php

namespace App\Http\Controllers;

use App\Models\LeaveRule;
use Illuminate\Http\Request;

class LeaveRuleController extends Controller
{
    public function edit(Request $request,$id)
    {
        $data['leave_rule'] =LeaveRule::find($id);
        return view('leave_rule.edit',$data);
    }
    public function update(Request $request,$id)
    {
        $request->validate([
            'total_leave'=>'required',
        ]);

        $leave_rule =LeaveRule::find($id);
        if(empty($leave_rule))
        {
            LeaveRule::create($request->all());
        }
        else
        {
            $leave_rule->update($request->all());
        }

        return redirect()->route('leave-rules.edit',$id)->with(['success'=>'Leave Rule has been added successfully']);

    }
}
