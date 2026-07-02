<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\BankDetail;
use App\Models\SalesTarget;

class BankAccountDetailController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = BankDetail::select('*');

                $data = $query->orderBy('id', 'desc')->get();

                return DataTables::of($data)
                    ->addIndexColumn()

                    ->addColumn('account_type_label', function ($row) {
                        $types = BankDetail::getAccountTypes();
                        return $types[$row->account_type] ?? $row->account_type;
                    })

                    ->addColumn('action', function ($row) {
                        $user = auth()->user();
                        $html = '';

                        if ($user->can('edit bank detail')) {
                            $html .= '<div class="dropdown d-inline-block">
                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-fill align-middle"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">';

                            if ($user->can('edit bank detail')) {
                                $html .= '<li>
                                    <a href="' . route('bank-account-details.edit', $row->id) . '" class="dropdown-item edit-item-btn">
                                        <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                    </a>
                                </li>';
                            }

                            $html .= '</ul></div>';
                        }

                        return $html;
                    })

                    ->rawColumns(['action', 'account_type_label'])
                    ->setRowClass('main-row')
                    ->make(true);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }

        return view('bank_account_detail.index');
    }

    public function create()
    {
        return view('bank_account_detail.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_holder_name' => 'required',
            'account_no' => 'required',
            'account_type' => 'required',
            'bank_name' => 'required',
            'branch_name' => 'required',
            'ifsc_code' => 'required',
        ]);

        $input = $request->all();
        BankDetail::create($input);

        return redirect()->route('bank-account-details.index')->with(['success' => 'Bank Account Detail has been added successfully']);
    }

    public function edit(Request $request, $id)
    {
        $data['bank_account_detail'] = BankDetail::find($id);
        return view('bank_account_detail.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'account_holder_name' => 'required',
            'account_no' => 'required',
            'account_type' => 'required',
            'bank_name' => 'required',
            'branch_name' => 'required',
            'ifsc_code' => 'required',
        ]);

        $bank_account_detail  = BankDetail::find($id);
        $input = $request->all();
        $bank_account_detail->update($input);
        return redirect()->route('bank-account-details.index')->with(['success' => 'Bank Account Detail has been updated successfully']);
    }
}
