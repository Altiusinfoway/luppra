<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Support\Tenancy\TenancyManager;

class RegionsController extends Controller
{
    private function ensureTenantContext(Request $request): void
    {
        if (!config('tenancy.enabled', false) || app()->bound('currentTenant')) {
            return;
        }

        $user = $request->user();
        if (!$user || $user->type === 'super admin') {
            return;
        }

        $tenantId = (int) $request->session()->get('tenant_id', 0);
        if ($tenantId <= 0) {
            $tenantId = (int) $request->session()->get('login_tenant_id', 0);
        }

        if ($tenantId <= 0) {
            abort(403, 'Tenant context is missing for this session.');
        }

        $tenant = Tenant::query()->where('id', $tenantId)->where('is_active', true)->first();
        if (!$tenant) {
            abort(403, 'Tenant is inactive or unavailable.');
        }

        app(TenancyManager::class)->initialize($tenant);
        app()->instance('currentTenant', $tenant);
    }

    private function resolveCountry(int|string $id): ?Country
    {
        return Country::query()->find((int) $id);
    }

    private function resolveState(int|string $id): ?State
    {
        return State::query()->find((int) $id);
    }

    private function resolveCity(int|string $id): ?City
    {
        return City::query()->find((int) $id);
    }

    public function countries(Request $req){
        $this->ensureTenantContext($req);

        if ($req->ajax()) {

            $data = Country::select(['id', 'name', 'code', 'created_at','is_active']);
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status_nm', function($row){
                    if($row->is_active == 1)
                    {
                        $status='Active';
                    }
                    else
                    {
                        $status='In-active';
                    }
                    return '<h5><span class="badge bg-success me-1">' . $status . '</span></h5>';
                })
                ->addColumn('action', function($row){
                    return '
                        <button data-id="'.$row->id.'" class="btn btn-sm btn-primary editBtn" data-size="md"
                                        data-url="'.route("regions.countries.edit",[$row->id]).'"
                                        data-ajax-popup="true"
                                        data-bs-original-title="Edit Country">Edit</button>';
                })
                ->rawColumns(['status_nm','action'])
                ->make(true);

        }

        return view('regions.countries');
    }

    public function createCountry(Request $req){
        $this->ensureTenantContext($req);

        return view('regions.createCountry');
    }

    public function storeCountry(Request $request) {
        $this->ensureTenantContext($request);

        $validated = $request->validate([
            'name' => ['required', Rule::unique('tenant.countries', 'name')]
        ]);

        Country::create($request->only('name','code','is_active'));

        return response()->json([
                'success' => 'true',
                'message' => 'Country added',
        ]);
    }

    public function editCountry(Request $req, $country){
        $this->ensureTenantContext($req);
        $country = $this->resolveCountry($country);
        abort_if(!$country, 404, 'Country not found for this tenant.');

        return view('regions.createCountry', compact('country'));
    }


    public function updateCountry(Request $request, $id) {
        $this->ensureTenantContext($request);

        $country = $this->resolveCountry($id);
        abort_if(!$country, 404, 'Country not found for this tenant.');

        $validated = $request->validate([
            'name' => ['required', Rule::unique('tenant.countries', 'name')->ignore($country->id)]
        ]);

        $country->update($request->only('name','code','is_active'));

        return response()->json([
                'success' => 'true',
                'message' => 'Country updated',
        ]);
    }


    public function states(Request $req){
        $this->ensureTenantContext($req);

        if ($req->ajax()) {

            $data = State::select(['id', 'name','country_id','is_active']);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('country_name', function($row){

                    return optional(optional($row)->getCountry)->name;

                })
                 ->addColumn('status_nm', function($row){
                    if($row->is_active == 1)
                    {
                        $status='Active';
                    }
                    else
                    {
                        $status='In-active';
                    }
                    return '<h5><span class="badge bg-success me-1">' . $status . '</span></h5>';
                })
                ->addColumn('action', function($row){
                    return '
                        <button data-id="'.$row->id.'" class="btn btn-sm btn-primary editBtn" data-size="md"
                                        data-url="'.route("regions.states.edit",[$row->id]).'"
                                        data-ajax-popup="true"
                                        data-bs-original-title="Edit State">Edit</button>';
                })
                ->rawColumns(['status_nm','action'])
                ->make(true);

        }

        return view('regions.states');
    }

    public function createState(Request $req){
        $this->ensureTenantContext($req);

        $country = Country::IsActive()->select(['id', 'name'])->get();
        return view('regions.createState', compact('country'));
    }

    public function storeState(Request $request) {
        $this->ensureTenantContext($request);

        $validated = $request->validate([
            'country_id' => 'required',
            'name' => ['required', Rule::unique('tenant.states', 'name')]
        ]);

        State::create($request->only('name','country_id','is_active'));

        return response()->json([
                'success' => 'true',
                'message' => 'State added',
        ]);
    }

    public function editState(Request $req, $state){
        $this->ensureTenantContext($req);
        $state = $this->resolveState($state);
        abort_if(!$state, 404, 'State not found for this tenant.');

        $country = Country::IsActive()->select(['id', 'name'])->get();
        return view('regions.createState', compact('country','state'));
    }

    public function updateState(Request $request, $id) {
        $this->ensureTenantContext($request);

        $state = $this->resolveState($id);
        abort_if(!$state, 404, 'State not found for this tenant.');

        $validated = $request->validate([
            'name' => ['required', Rule::unique('tenant.states', 'name')->ignore($state->id)]
        ]);

        $state->update($request->only('name','country_id','is_active'));

        return response()->json([
                'success' => 'true',
                'message' => 'State updated',
        ]);
    }


    public function fetchState($id){
        $this->ensureTenantContext(request());

        $state = State::IsActive()->select(['id', 'name'])->where('country_id',$id)->get();

        return response()->json([
                'success' => 'true',
                'message' => 'State List',
                'state' => $state
        ]);

    }

    public function cities(Request $req){
        $this->ensureTenantContext($req);

        if ($req->ajax()) {

            $data = City::select(['id', 'name','state_id','is_active']);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('state_name', function($row){

                    return optional(optional($row)->getState)->name;

                })
                 ->addColumn('status_nm', function($row){
                    if($row->is_active == 1)
                    {
                        $status='Active';
                    }
                    else
                    {
                        $status='In-active';
                    }
                    return '<h5><span class="badge bg-success me-1">' . $status . '</span></h5>';
                })
                ->addColumn('action', function($row){
                    return '
                        <button data-id="'.$row->id.'" class="btn btn-sm btn-primary editBtn" data-size="md"
                                        data-url="'.route("regions.cities.edit",[$row->id]).'"
                                        data-ajax-popup="true"
                                        data-bs-original-title="Edit City">Edit</button>';
                })
                ->rawColumns(['status_nm','action'])
                ->make(true);

        }

        return view('regions.cities');
    }

    public function createCity(Request $req){
        $this->ensureTenantContext($req);

        $country = Country::IsActive()->select(['id', 'name'])->get();
        return view('regions.createCity', compact('country'));
    }

    public function storeCity(Request $request) {
        $this->ensureTenantContext($request);

        $validated = $request->validate([
            'state_id' => 'required',
            'name' => ['required', Rule::unique('tenant.cities', 'name')]
        ]);

        City::create($request->only('name','state_id','is_active'));

        return response()->json([
                'success' => 'true',
                'message' => 'City added',
        ]);
    }

    public function editCity(Request $req, $city){
        $this->ensureTenantContext($req);
        $city = $this->resolveCity($city);
        abort_if(!$city, 404, 'City not found for this tenant.');

        $country = Country::select(['id', 'name'])->get();
        $state = State::select(['id', 'name'])->get();

        $selectedCoutry = State::where('id',$city->state_id)->value('country_id');

        return view('regions.createCity', compact('city','country','state','selectedCoutry'));
    }


    public function updateCity(Request $request, $id) {
        $this->ensureTenantContext($request);

        $city = $this->resolveCity($id);
        abort_if(!$city, 404, 'City not found for this tenant.');

        $validated = $request->validate([
            'name' => ['required', Rule::unique('tenant.cities', 'name')->ignore($city->id)]
        ]);

        $city->update($request->only('name','state_id','is_active'));

        return response()->json([
                'success' => 'true',
                'message' => 'City updated',
        ]);
    }

}
