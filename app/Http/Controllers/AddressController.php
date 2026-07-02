<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\State;
use App\Models\City;
use App\Models\Address;
use App\Models\Country;
use App\Models\Entity;

class AddressController extends Controller
{
    public function getStates(Request $request)
    {
        $states = State::where('country_id', $request->country_id)
            ->isActive()
            ->pluck('name', 'id');

        return response()->json(['states' => $states]);
    }

    public function getCities(Request $request)
    {
        $cities = City::where('state_id', $request->state_id)
            ->isActive()
            ->pluck('name', 'id');

        return response()->json(['cities' => $cities]);
    }

     public function loadAddressBlock(Request $request)
    {
        $index = $request->get('index', 0);
        return view('address.multiple_address_list', ['index' => $index]);
    }


     public function create(Request $request, $type, $company_id, $id = null)
    {
        //company_id = customer_id in this project
        if ($company_id === null || $company_id === '' || $company_id == 0) {

            return response()->json([
                'error' => 'Please select customer first',
                'message' => 'Please select customer first',
            ], 422);
        }
        $countries = Country::isActive()->pluck('name', 'id');
        $states = State::isActive()->pluck('name', 'id');

        $address_rcd = null;

        if ($id) {
            $address_rcd = Address::find($id);
        }

        return view('address.create', compact('countries','states', 'company_id', 'type', 'address_rcd','id'));
    }

    //this is common function which used in invoice & quotes
    public function store(Request $request, $type, $company_id, $id = null)
    {
        $validated = $request->validate([
            'country' => 'required',
            'state' => 'required',
            // 'city' => 'required',
            // 'zipcode' => 'required',
            // 'address_line_1' => 'required',
        ]);

        $company_detail = Entity::where('id', $company_id)->first();
        $adr_data['name'] = $company_detail->company_name ?? null;
        $adr_data['email'] = $company_detail->email ?? null;
        $adr_data['country'] = $request->country;
        $adr_data['state'] = $request->state;
        $adr_data['city'] = $request->city;
        $adr_data['zipcode'] = $request->zipcode;
        $adr_data['address_line_1'] = $request->address_line_1 ?? null;
        $adr_data['address_line_2'] = $request->address_line_2 ?? null;

        if (is_null($id))
        {
            //new address create
            if ($type == 'shipping')
            {
                $adr_rcd = Address::create($adr_data);

                //customer-address add
                $company_detail->update(['shipping_address_id'=>$adr_rcd->id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Shipping Address been added successfully',
                    'company_id' => $company_id
                ], 200);
            }
            else
            {
                $adr_rcd = Address::create($adr_data);

                //customer-address add
               $company_detail->update(['billing_address_id'=>$adr_rcd->id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Billing Address been added successfully',
                    'company_id' => $company_id
                ], 200);
            }
        }
        else
        {
            //existing address update
            $exit_adr = Address::where('id',$id)->first();
            if($exit_adr)
            {
                $exit_adr->update($adr_data);
            }

            if ($type == 'shipping')
            {
                 return response()->json([
                    'success' => true,
                    'message' => 'Shipping Address been updated successfully',
                    'company_id' => $company_id
                ], 200);
            }
            else
            {
                return response()->json([
                    'success' => true,
                    'message' => 'Billing Address been updated successfully',
                    'company_id' => $company_id
                ], 200);
            }
        }
    }

    public function fetchAddresses($entity_id, $billing_id = null, $shipping_id = null)
    {
        $entity = Entity::find($entity_id);

        if ($billing_id === 'undefined' || $billing_id === 'null') {
            $billing_id = null;
        }

        if ($shipping_id === 'undefined' || $shipping_id === 'null') {
            $shipping_id = null;
        }

        if (!$entity) {
            return view('address.address_selection', ['company' => null, 'billing_address_id' => null, 'shipping_address_id' => null ]);
        }

        // Fetch Address For Company.
        return view('address.address_selection', ['company' => $entity, 'billing_address_id' => $billing_id, 'shipping_address_id' => $shipping_id ]);
    }


    public function getCustAddress($entity_id)
    {
        $entity  = Entity::findOrFail($entity_id);
        $fallbackAddressId = $entity->address_id ?? null;

        return response()->json([
            'billing_address_id'  => $entity->billing_address_id ?? $fallbackAddressId,
            'shipping_address_id' => $entity->shipping_address_id ?? $fallbackAddressId,
        ]);
    }

}
