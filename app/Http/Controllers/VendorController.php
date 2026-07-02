<?php

namespace App\Http\Controllers;

use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Products;
use App\Models\Entity;
use App\Models\VendorProduct;
use App\Models\Address;
use App\Models\Country;
use App\Models\Utility;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $data['country_list'] = Country::isActive()->pluck('name','id');

        $data['product_list'] = Products::get(['name','id']);

        if ($request->ajax())
        {
            try
            {
                $query = Entity::GetVendor()->with(['getAddress'])->select('id', 'name', 'gst_no', 'address_id', 'is_active','contact');

                if ($request->name) {
                    $query->where('name', 'like', '%' . $request->name . '%');
                }

                  if ($request->country_filter) {
                    $query->whereHas('getAddress', function ($q) use ($request) {
                        $q->where('country', $request->country_filter);
                    });
                }

                if ($request->state_filter) {
                    $query->whereHas('getAddress', function ($q) use ($request) {
                        $q->where('state', $request->state_filter);
                    });
                }

                if ($request->city_filter) {
                    $query->whereHas('getAddress', function ($q) use ($request) {
                        $q->where('city', $request->city_filter);
                    });
                }

                 if (!empty($request->product_filter)) {
                    $vendor_pro_all = VendorProduct::where('product_id',$request->product_filter)->pluck('vendor_id');
                    $query->whereIn("id",$vendor_pro_all);
                }

                $data = $query->get();

                return DataTables::of($data)
                    ->addColumn('address_id', function ($row) {
                        if (!empty($row->getAddress['address_line_1'])) {
                            return $row->getAddress['address_line_1'];
                        } else {
                            return '';
                        }
                    })
                    ->addColumn('is_active', function ($row) {
                        if ($row->is_active == 1) {
                            return '<h5><span class="badge bg-success-subtle text-success">' . 'Active' . '</span></h5>';
                        } else {
                            return '<h5><span class="badge bg-success-subtle text-danger" >' . 'In-Active' . '</span></h5>';
                        }
                    })
                    ->addColumn('action', function ($row) {
                         $user = auth()->user();
                        $html = '';

                        if ($user->can('edit vender')) {

                            $editUrl = route("vendors.edit", $row->id);

                            $html .= '
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-fill align-middle"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a href="' . $editUrl . '" class="dropdown-item edit-item-btn">
                                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                        </a>
                                    </li>
                                </ul>
                            </div>';
                        }

                        return $html;
                    })

                    ->rawColumns(['address_id', 'is_active', 'action'])
                    ->make(true);
            } catch (\Exception $e) {

                return response()->json([
                    'error' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        }

        return view('vendor.index',$data);

    }

    public function create()
    {
        $data['product_list'] = Products::get(['name','id']);
        return view('vendor.create',$data);
    }

    public function store(Request $request)
    {
        $request->validate([
           'name' => [
                'required',
            ],
            // 'gst_no' => [
            //     'required',
            //     'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            //     'unique:entities,gst_no'
            // ],
            'rate' => 'required|numeric',
            'description' => 'required',
            'contact' => [
                'required',
                'digits:10',
                Rule::unique('tenant.entities')->where(function ($query) {
                    return $query->where('type','vendor');
                }),
            ],
            'company_name'=>'required',
            'email'=>'required|email',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'address_line_1' => 'required',
        ]);

        $input = $request->all();

        $vendor_contact_exist = Entity::GetVendor()->where('contact',$request['contact'])->first();
        if($vendor_contact_exist)
        {
            return back()->with(['error'=>'contact number already exists.']);
        }

        // address
        $addr['name'] = $request->name;
        $addr['email'] = $request->email;
        $addr['phone'] = $request->contact;
        $addr['state'] = $request->state;
        $addr['city'] = $request->city;
        $addr['country'] = $request->country;
        $addr['zipcode'] = $request->zipcode;
        $addr['address_line_1'] = $request->address_line_1;
        $addr['address_line_2'] = $request->address_line_2;
        $address_id = Address::create($addr);

        //entity
        $input['type']='vendor';
        $input['address_id']= $address_id['id'];
        $input['created_by'] =\Auth::user()->creatorId();
        $vendor = Entity::create($input);

        if($request->has('productIds') && !empty($request->productIds) && $request->productIds != null){

            foreach($request->productIds as $key => $productId){

                VendorProduct::create([
                    'vendor_id'=>$vendor->id,
                    'product_id'=> $productId,
                    'price'=>$request->prices[$key],
                    'unit_id'=>$request->units[$key]
                ]);

            }

        }

        return response()->json([
            'success' => 'yes',
            'message' => 'Vendor has been added successfully',
            'redirect_url' => route('vendors.index'),
        ]);
        // return redirect()->route('vendors.index')->with(['success'=>"Vendor has been added successfully"]);
    }

    public function edit($id)
    {
        $data['units'] = Utility::getUnits();

        $data['vendor'] = Entity::with('getAddress')->find($id);

        $data['products'] =Products::where('created_by', '=', \Auth::user()->creatorId())->get(['name', 'id']);

        $selectedProductIds = VendorProduct::where('vendor_id', $id)->pluck('product_id')->toArray();
        $data['selectedProducts'] = Products::whereIn('id',$selectedProductIds)->get();

        $vendorProduct = VendorProduct::where('vendor_id',$id)->select('product_id','price','unit_id')->get()->toArray();
        $data['productDetail'] = array_column($vendorProduct, null, 'product_id');

        $data['address_data'] = $data['vendor']->getAddress ?? null;

        return view('vendor.edit',$data);
    }

    public function update($id,Request $request)
    {
        $request->validate([
           'name' => [
                'required'
            ],
        //    'gst_no' => [
        //         'required',
        //         'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
        //         Rule::unique('entities', 'gst_no')->ignore($id),
        //     ],
             'contact' => [
                'required',
                'digits:10',
                Rule::unique('tenant.entities')
                    ->ignore($id, 'id')
                    ->where(function ($query) {
                        return $query->where('type', 'vendor');
                    }),
            ],
            'rate' => 'required|numeric',
            'description' => 'required',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'address_line_1' => 'required',

        ]);

        $input =$request->all();
        $vend = Entity::find($id);
        $vend->update($input);

        VendorProduct::where(['vendor_id' => $id])->delete();
        if($request->has('productIds') && !empty($request->productIds) && $request->productIds != null){

            foreach($request->productIds as $key => $productId){

                VendorProduct::create([
                    'vendor_id'=> $id,
                    'product_id'=> $productId,
                    'price'=>$request->prices[$key],
                    'unit_id'=>$request->units[$key]
                ]);

            }
        }

        //address
        $addr['name'] = $request->name;
        $addr['email'] = $request->email;
        $addr['phone'] = $request->contact;
        $addr['state'] = $request->state;
        $addr['city'] = $request->city;
        $addr['country'] = $request->country;
        $addr['zipcode'] = $request->zipcode;
        $addr['address_line_1'] = $request->address_line_1;
        $addr['address_line_2'] = $request->address_line_2;


        if (!empty($vend->address_id))
        {
            $address_record = Address::find($vend->address_id);
            if ($address_record) {
                $address_record->update($addr);
            }
        }

         return response()->json([
            'success' => 'yes',
            'message' => 'Vendor has been updated successfully',
            'redirect_url' => route('vendors.index'),
        ]);
        // return redirect()->route('vendors.index')->with(['success'=>"Vendor has been Updated successfully"]);
    }

    public function delete($id)
    {
        $vend = Entity::find($id);
        $vendor_all_prods = VendorProduct::where('vendor_id',$id)->get();
        if($vendor_all_prods->isNotEmpty())
        {
            foreach($vendor_all_prods as $v_pro)
            {
                $v_pro->delete();
            }
        }
        $vend->delete();
        return response()->json([
            'success' => 'Vendor has been deleted successfully.'
        ], 200);
        return redirect()->route('vendors.index');
    }

    public function addMoreProducts(Request $request){

        $products = Products::where('id', $request->productIds)->get();

        return view('vendor.add-more-products',compact('products'));
    }

}
