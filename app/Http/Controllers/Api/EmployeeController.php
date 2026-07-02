<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Lead;
use App\Models\Utility;
use App\Models\LeadCall;

class EmployeeController extends Controller
{

}
