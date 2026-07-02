<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utility;

class getDataController extends Controller
{
    public function getUnits($type){

        $response = Utility::getUnits($type);
        if (request()->ajax()) {
            return response()->json($response, 200);

        }

        return $response;

    }

}
