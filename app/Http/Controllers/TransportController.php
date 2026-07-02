<?php

namespace App\Http\Controllers;

use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Entity;
use App\Models\Country;
use App\Models\EntityAddress;

class TransportController extends Controller
{
    public function index(Request $request)
    {
        $data['country_list'] = Country::isActive()->pluck('name','id');

        if ($request->ajax())
        {
            try
            {

                $query = Entity::GetTransport()
                    //->whereHas('getEntityAddress')
                    ->select('id', 'name', 'gst_no', 'is_active', 'contact_json');

                if ($request->name) {
                    $query->where('name', 'like', '%' . $request->name . '%');
                }

                if ($request->country_filter) {
                    $query->whereHas('getEntityAddress', function ($q) use ($request) {
                        $q->where('country', $request->country_filter);
                    });
                }

                if ($request->state_filter) {
                    $query->whereHas('getEntityAddress', function ($q) use ($request) {
                        $q->where('state', $request->state_filter);
                    });
                }

                if ($request->city_filter) {
                    $query->whereHas('getEntityAddress', function ($q) use ($request) {
                        $q->where('city', $request->city_filter);
                    });
                }


                $data = $query->orderBy('id', 'desc')->get();

                return DataTables::of($data)
                    ->addIndexColumn()
                 ->addColumn('contact_list', function ($row) {
                        $contacts = json_decode($row->contact_json, true);

                       return $contacts ? implode('<br>', $contacts) : '';

                    })

                    ->addColumn('action', function($row){
                        $user = auth()->user();
                        $editUrl = route("transports.edit", $row->id);

                        $html = '<div class="dropdown d-inline-block">
                                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-more-fill align-middle"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">';

                        if ($user->can('edit transport')) {
                            $html .= '<li>
                                        <a href="' . $editUrl . '" class="dropdown-item edit-item-btn">
                                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                        </a>
                                    </li>';
                        }

                        $html .= '</ul></div>';

                        return $html;
                    })

                    ->rawColumns(['contact_list','is_active','action'])
                    ->make(true);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }

        return view('transport.index',$data);

    }

    public function create()
    {
        return view('transport.create');
    }

    public function store(Request $request)
    {
       $request->validate([
        'name' => ['required'],
        'gst_no' => [
            'nullable',
            'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
        ],
        'specification' => 'required',
        'contact' => 'required|array|min:1',
        'contact.*' => 'required|digits:10',
        'company_name' => 'required',
        'email' => 'required|email',
        'country' => 'required|array',
        'country.*' => 'required|exists:tenant.countries,id',
        'state' => 'required|array',
        'state.*' => 'required|exists:tenant.states,id',
        'city' => 'required|array',
        'city.*' => 'required|exists:tenant.cities,id',
        'zipcode' => 'required|array',
        'zipcode.*' => 'required|digits_between:4,10',
        'address_line_1' => 'required|array',
        'address_line_1.*' => 'required|string',
        ]);

        if (count($request->contact) !== count(array_unique($request->contact))) {
            return response()->json([
                'errors' => [
                    'contact' => ['Duplicate contact numbers are not allowed.'],
                ],
            ], 422);

        }

        //gst validation
        if($request->gst_no)
        {
            $check_exist_gst = Entity::where('gst_no',$request->gst_no)->where('type','transport')->exists();

            if($check_exist_gst)
            {
                return response()->json([
                        'error'   => true,
                        'message' => $request->gst_no . ' Transport Company GST No already exists.'
                    ], 200);
            }
        }


        // Create Entity
        $entity = Entity::create([
            'name' => $request->name,
            'gst_no' => $request->gst_no,
            'specification' => $request->specification,
            'company_name' => $request->company_name,
            'email' => $request->email,
            // 'is_active'=> $request->is_active,
            'contact_json'=> json_encode($request->contact),
            'created_by'=> \Auth::user()->creatorId(),
            'type' =>'transport',
        ]);

        foreach ($request->country as $i => $country) {
            $entity->getEntityAddress()->create([
                'entity_id'=>$entity->id,
                'country' => $country,
                'state' => $request->state[$i],
                'city' => $request->city[$i],
                'zipcode' => $request->zipcode[$i],
                'address_line_1' => $request->address_line_1[$i],
                'address_line_2' => $request->address_line_2[$i] ?? null,
            ]);
        }

        return response()->json([
            'success' => 'Transport has been added successfully.',
            'redirect_url' => route('transports.index'),
        ]);

    }

    public function edit($id)
    {
        $data['transport'] = Entity::with(['getEntityAddress'])->find($id);
        $data['address_data'] = $data['transport']->getEntityAddress() ?? [];
        $data['contact_array'] = json_decode($data['transport']->contact_json, true) ?? [''];
        return view('transport.edit',$data);
    }

    public function update($id,Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'gst_no' => [
                'nullable',
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            ],
            'specification' => 'required',
            'contact' => 'required|array|min:1',
            'contact.*' => 'required|digits:10',
            'company_name' => 'required',
            'email' => 'required|email',

            'country.*' => 'required|exists:tenant.countries,id',
            'state.*' => 'required|exists:tenant.states,id',
            'city.*' => 'required|exists:tenant.cities,id',
            'zipcode.*' => 'required|string|max:10',
            'address_line_1.*' => 'required',
            'address_line_2.*' => 'nullable',
        ]);

        if (count($request->contact) !== count(array_unique($request->contact))) {
            return response()->json([
                'errors' => [
                    'contact' => ['Duplicate contact numbers are not allowed.'],
                ],
            ], 422);
        }

        if($request->gst_no)
        {
            $check_exist_gst = Entity::where('gst_no',$request->gst_no)->where('id','!=',$id)->where('type','transport')->exists();

            if($check_exist_gst)
            {
                return response()->json([
                        'error'   => true,
                        'message' => $request->gst_no . ' Transport Company GST No already exists.'
                    ], 200);
            }
        }

        // Update entity
        $entity = Entity::findOrFail($id);
        $entity->update([
            'name' => $request->name,
            'gst_no' => $request->gst_no,
            'specification' => $request->specification,
            'company_name' => $request->company_name,
            'email' => $request->email,
            // 'is_active'=> $request->is_active,
            'contact_json'=> json_encode($request->contact),
            'type' =>'transport',
        ]);

        $existingAddressIds = $entity->getEntityAddress->pluck('id')->toArray();
        $submittedAddressIds = $request->address_id ?? [];

        if($request->has('country')){
            foreach ($request->country as $i => $country) {
                $addressData = [
                    'entity_id' => $entity->id,
                    'country' => $country,
                    'state' => $request->state[$i],
                    'city' => $request->city[$i],
                    'zipcode' => $request->zipcode[$i],
                    'address_line_1' => $request->address_line_1[$i],
                    'address_line_2' => $request->address_line_2[$i] ?? null,
                ];

                if (!empty($submittedAddressIds[$i])) {
                    EntityAddress::where('id', $submittedAddressIds[$i])->update($addressData);
                } else {
                    EntityAddress::create($addressData);
                }
            }
        } else {

            if (!$request->has('country')) {
                return response()->json([
                    'errors' => [
                        'country' => ['Please add trasport an address.'],
                    ],
                ], 422);
            }

        }


        $deletedIds = array_diff($existingAddressIds, array_filter($submittedAddressIds));
        EntityAddress::whereIn('id', $deletedIds)->delete();

        return response()->json([
            'success' => 'Transport has been updated successfully.',
            'redirect_url' => route('transports.index'),
        ]);


    }

    public function delete($id)
    {
        $cust = Entity::find($id);
        $cust->delete();
        return response()->json([
            'success' => 'Transport has been deleted successfully.'
        ], 200);
        return redirect()->route('transports.index');
    }

     public function quick_create()
    {
        return view('transport.quick_create');
    }

    public function quick_store(Request $request)
    {
        $request->validate([
        'name' => ['required', 'regex:/^[A-Za-z\s]+$/'],
        'contact' => 'required|array|min:1',
        'contact.*' => 'required|digits:10',
        ]);

        if (count($request->contact) !== count(array_unique($request->contact))) {
            return response()->json([
                'errors' => [
                    'contact' => ['Duplicate contact numbers are not allowed.'],
                ],
            ], 422);
        }

        // Create Entity
        $entity = Entity::create([
            'name' => $request->name,
            'is_active'=> 1,
            'contact_json'=> json_encode($request->contact),
            'created_by'=> \Auth::user()->creatorId(),
            'type' =>'transport',
        ]);

        return response()->json([
            'success' => 'Transport has been added successfully.',
            'redirect_url' => route('transports.index'),
        ]);

    }

}
