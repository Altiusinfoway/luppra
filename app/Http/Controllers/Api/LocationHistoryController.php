<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LocationHistory;
use App\Models\Utility;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;

class LocationHistoryController extends Controller
{
    public function add_location(Request $request)
    {
        try {

		    Log::info('------ start add_location ------');
            Log::info('Request :-', $request->all());


            $validator = Validator::make($request->all(), [
                'location_json'=>'required',
            ]);

            if ($validator->fails()) {
                return Utility::return_response(false, $validator->errors()->first(), "", 422);
            }

            $user = JWTAuth::parseToken()->authenticate();

            $location_list = $this->normalizeLocationPayload($request->input('location_json'));

            if (empty($location_list) || !is_array($location_list)) {
                return Utility::return_response(false, "Location list is required", "", 422);
            }

            $new_ids = [];

            foreach ($location_list as $index => $prod) {

                foreach (['latitude', 'longitude', 'date_time'] as $field) {
                    if (empty($prod[$field])) {
                        return Utility::return_response(
                            false,
                            "{$field} is required at index " . ($index + 1),
                            "",
                            422
                        );
                    }
                }

                $location = LocationHistory::create([
                    'user_id'   => $user->id,
                    'latitude'  => $prod['latitude'],
                    'longitude' => $prod['longitude'],
                    'date_time' => $prod['date_time'],
                ]);

                $new_ids[] = $location->id;
            }

            $data = LocationHistory::whereIn('id', $new_ids)->get();

            Log::info('------ end add_location ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true, "location has been added successfully.",$data ?? "", 200);
        } catch (JWTException $e) {
            Log::info('add_location  error ', [$e->getMessage()]);
            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        } catch (\Throwable $e) {
            Log::info('add_location error ', [$e->getMessage()]);
            return Utility::return_response(false, $e->getMessage(), "", 500);
        }
    }

    public function location_list(Request $request)
    {
        try
        {
		    Log::info('------ start location_list ------');
            Log::info('Request :-', $request->all());

            $user = JWTAuth::parseToken()->authenticate();

            $input = $request->all();

            $data =  LocationHistory::where('user_id',$user->id)->get();

            Log::info('------ end location_list ------');
            Log::info('------------------------------------------------------------------------------');
            return Utility::return_response(true, "location list.",$data, 200);
        } catch (JWTException $e) {
            Log::info('location_list  error ', [$e->getMessage()]);
            return Utility::return_response(false, "Token invalid or not provided.", "", 500);
        }
    }



    private function normalizeLocationPayload($locationPayload): array
    {
        if (is_array($locationPayload)) {
            return $locationPayload;
        }

        if (is_string($locationPayload)) {
            $decoded = json_decode($locationPayload, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
