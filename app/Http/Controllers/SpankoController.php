<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Lead;
use App\Models\LeadCall;
use App\Models\Products;
use App\Models\LeadProducts;

class SpankoController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax())
        {
            try
            {
                $data = Lead::query();

                if ($request->product_filter && $request->product_filter != '0') {
                    $data->whereRaw('FIND_IN_SET(?, products)', [$request->product_filter]);
                }

                if ($request->status_filter && $request->status_filter != '0') {
                    $lead_call = LeadCall::where('status',$request->status_filter)->pluck('lead_id')->unique()->toArray();
                    $data->whereIn('id',$lead_call);
                }

                 if (!empty($request->start_date_filter)) {
                    $data->whereDate('date', '>=',$request->start_date_filter);
                }

                if (!empty($request->end_date_filter)) {
                    $data->whereDate('next_contact_date', '<=',$request->end_date_filter);
                }

                return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return $row->name;
                })
                ->addColumn('products', function ($row) {
                    $product_ids = LeadProducts::where('lead_id', $row->id)->pluck('product_id')->toArray();
                    $product_names = Products::whereIn('id', $product_ids)->pluck('name')->toArray();
                    return implode(', ', $product_names);
                })
                ->addColumn('amount', function ($row) {
                    $prices = LeadProducts::where('lead_id', $row->id)->pluck('price')->toArray();
                    return array_sum($prices);
                })
                ->addColumn('total_connected', function ($row) {
                    return LeadCall::where('lead_id', $row->id)->where('status', 1)->count();
                })
                ->addColumn('total_not_connected', function ($row) {
                    return LeadCall::where('lead_id', $row->id)->where('status', 0)->count();
                })
                ->rawColumns(['name', 'products'])
                ->make(true);
            } catch (\Exception $e) {

                    return response()->json([
                        'error' => 'Server Error: ' . $e->getMessage()
                    ], 500);
            }
        }
        $data['product_list'] = Products::InHouse()->pluck('name','id');

        return view('spanko.index',$data);
    }
}
