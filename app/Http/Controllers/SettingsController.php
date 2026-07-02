<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeadSource;
use App\Models\LeadStage;
use App\Models\LeadType;
use App\Models\OrderStage;
use App\Models\Taxes;
use App\Models\GstSlabMaster;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SettingsController extends Controller
{
    private function writeSettingsActivity(string $action, string $eventKey, string $description, array $properties = []): void
    {
        ActivityLogger::writeFor('settings', $action, 'settings', (int) \Auth::user()->creatorId(), [
            'event_key' => $eventKey,
            'description' => $description,
            'properties' => $properties,
        ]);
    }

    private function leadSettingsActivityTimeline(int $creatorId)
    {
        return ActivityLogger::activityForModule('settings', 10, [
            'event_key' => 'settings.system_updated',
            'subject' => 'settings',
            'subject_id' => $creatorId,
            'property_equals' => [
                'type' => ['lead_source', 'lead_stage', 'lead_type'],
            ],
        ], 'lead_settings_activities_page');
    }

    private function orderSettingsActivityTimeline(int $creatorId)
    {
        return ActivityLogger::activityForModule('settings', 10, [
            'event_key' => 'settings.order_updated',
            'subject' => 'settings',
            'subject_id' => $creatorId,
            'property_equals' => [
                'type' => 'order_stage',
            ],
        ], 'order_settings_activities_page');
    }

    private function taxSettingsActivityTimeline(int $creatorId)
    {
        return ActivityLogger::activityForModule('settings', 10, [
            'event_key' => 'settings.gst_updated',
            'subject' => 'settings',
            'subject_id' => $creatorId,
            'property_equals' => [
                'type' => 'gst_slab',
            ],
        ], 'tax_settings_activities_page');
    }

    public function terms()
    {
        $connection = app()->bound('currentTenant') ? 'tenant' : 'landlord';
        $this->ensureTermsAndConditionsTable($connection);

        if (!Schema::connection($connection)->hasTable('terms_and_conditions')) {
            return redirect()->back()->with('error', __('Terms and conditions table not found.'));
        }

        $terms = DB::connection($connection)
            ->table('terms_and_conditions')
            ->orderBy('id')
            ->first();

        return view('setting.terms-and-conditions', [
            'terms' => [
                // 'general' => $terms->general ?? '',
                'invoice' => $terms->invoice ?? '',
                'quotation' => $terms->quotation ?? '',
            ],
        ]);
    }

    public function terms_save(Request $request)
    {
        $request->validate([
            'general' => 'nullable|string',
            'invoice' => 'nullable|string',
            'quotation' => 'nullable|string',
        ]);

        $connection = app()->bound('currentTenant') ? 'tenant' : 'landlord';
        $this->ensureTermsAndConditionsTable($connection);

        if (!Schema::connection($connection)->hasTable('terms_and_conditions')) {
            return redirect()->back()->with('error', __('Terms and conditions table not found.'));
        }

        $payload = [
            'general' => $request->input('general'),
            'invoice' => $request->input('invoice'),
            'quotation' => $request->input('quotation'),
            'updated_at' => now(),
        ];

        $table = DB::connection($connection)->table('terms_and_conditions');
        $existingId = $table->orderBy('id')->value('id');

        if ($existingId) {
            DB::connection($connection)->table('terms_and_conditions')->where('id', $existingId)->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::connection($connection)->table('terms_and_conditions')->insert($payload);
        }

        return redirect()->route('setting.terms.index')->with('success', __('Terms and conditions successfully updated.'));
    }

    private function ensureTermsAndConditionsTable(string $connection): void
    {
        if ($connection === 'tenant' && !Schema::connection('tenant')->hasTable('terms_and_conditions') && Schema::connection('landlord')->hasTable('terms_and_conditions')) {
            $landlordDatabase = str_replace('`', '``', (string) config('database.connections.landlord.database'));
            DB::connection('tenant')->statement("CREATE TABLE `terms_and_conditions` LIKE `{$landlordDatabase}`.`terms_and_conditions`");
        }

        if (!Schema::connection($connection)->hasTable('terms_and_conditions')) {
            return;
        }

        foreach (['general', 'invoice', 'quotation'] as $column) {
            if (!Schema::connection($connection)->hasColumn('terms_and_conditions', $column)) {
                Schema::connection($connection)->table('terms_and_conditions', function ($table) use ($column) {
                    $table->longText($column)->nullable();
                });
            }
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function lead($type = 'source')
    {

        $user = \Auth::user();

        $sources = LeadSource::orderBy('order')->get(); // where('created_by', '=', $user->creatorId())->
        $statuses = LeadStage::orderBy('order')->get(); // where('created_by', '=', $user->creatorId())->
        $lead_type_all = LeadType::get(); // where('created_by', '=', $user->creatorId())->
        $settingsActivityTimeline = $this->leadSettingsActivityTimeline((int) $user->creatorId());

        return view('setting.lead')->with(['type' => $type, 'sources' => $sources, 'statuses' => $statuses, 'lead_type_all' => $lead_type_all, 'settingsActivityTimeline' => $settingsActivityTimeline]);

    }

    public function lead_save(Request $request){

        $objUser = \Auth::user()->creatorId();

        if($request->has('setting') && $request->setting  == 'source'){

            $validator = \Validator::make(
                $request->all(), [
                    'source' => 'required|max:120',
                ]
            );

        }

        if($request->has('setting') && $request->setting  == 'status'){

            $validator = \Validator::make(
                $request->all(), [
                    'statusName' => 'required|max:120',
                    'color'      => 'required|max:120',
                ]
            );

        }

        if($request->has('setting') && $request->setting  == 'lead_type'){

            $validator = \Validator::make(
                $request->all(), [
                    'lead_type_name' => 'required|max:120',
                ]
            );

        }



        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $request['created_by'] = \Auth::user()->creatorId();

        if($request->has('setting') && $request->setting == 'source'){

            $request['name'] = $request['source'];

            $maxOrder = LeadSource::max('order');
            $latestOrder = $maxOrder ? $maxOrder + 1 : 1;

            $request['order'] = $latestOrder;

            $leadStatus = LeadSource::create([
                'name' => $request['name'],
                'created_by' => $request['created_by'],
                'order' => $request['order'],
                'is_editable' => 1,
            ]);

            if($leadStatus){
                $this->writeSettingsActivity(
                    'create',
                    'settings.system_updated',
                    'Lead source created.',
                    [
                        'type' => 'lead_source',
                        'item_id' => $leadStatus->id,
                        'name' => $leadStatus->name,
                    ]
                );

                return redirect()->route('setting.lead.index',['source'])->with('success', __('Lead source successfully created.'));

            } else {

                return redirect()->route('setting.lead.index',['source'])->with('error', __('Something is wrong.'));

            }


        }

        if($request->has('setting') && $request->setting == 'status'){

            $request['name'] = $request['statusName'];
            $request['color'] = $request['color'];

            $maxOrder = LeadStage::max('order');
            $latestOrder = $maxOrder ? $maxOrder + 1 : 1;

            $request['order'] = $latestOrder;

            $leadSource = LeadStage::create([
                'name' => $request['name'],
                'color' => $request['color'],
                'created_by' => $request['created_by'],
                'order' => $request['order'],
                'is_editable' => 1,
            ]);

            if($leadSource){
                $this->writeSettingsActivity(
                    'create',
                    'settings.system_updated',
                    'Lead stage created.',
                    [
                        'type' => 'lead_stage',
                        'item_id' => $leadSource->id,
                        'name' => $leadSource->name,
                        'color' => $leadSource->color,
                    ]
                );

                return redirect()->route('setting.lead.index',['status'])->with('success', __('Lead status successfully created.'));

            } else {

                return redirect()->route('setting.lead.index',['status'])->with('error', __('Something is wrong.'));

            }

        }

        if($request->has('setting') && $request->setting == 'lead_type')
        {

            $request['name'] = $request['lead_type_name'];

            $leadType = LeadType::create($request->all());

            if($leadType){
                $this->writeSettingsActivity(
                    'create',
                    'settings.system_updated',
                    'Lead type created.',
                    [
                        'type' => 'lead_type',
                        'item_id' => $leadType->id,
                        'name' => $leadType->name,
                    ]
                );

                return redirect()->route('setting.lead.index',['lead_type'])->with('success', __('Lead Type successfully created.'));

            } else {

                return redirect()->route('setting.lead.index',['lead_type'])->with('error', __('Something is wrong.'));

            }


        }

    }

    public function lead_edit(string $type, int $id){

        $user = \Auth::user();

        $sources    = LeadSource::orderBy('order')->get();
        $statuses   = LeadStage::orderBy('order')->get();
        $lead_type_all   = LeadType::get();

        $source = $status = $lead_type_id = [];

        if($type == 'source') {

            $source = LeadSource::find($id);

            if(!$source){

                return redirect()->route('setting.lead.index')->with('error', __('Something is wrong.'));

            }

            if ((int) ($source->is_editable ?? 1) !== 1) {
                return redirect()->route('setting.lead.index', ['source'])->with('error', __('Predefined lead source cannot be edited.'));
            }

        } else if($type == 'status') {

            $status = LeadStage::find($id);

            if(!$status){

                return redirect()->route('setting.lead.index')->with('error', __('Something is wrong.'));

            }

            if ((int) ($status->is_editable ?? 1) !== 1) {
                return redirect()->route('setting.lead.index', ['status'])->with('error', __('Predefined lead status cannot be edited.'));
            }

        } else if($type == 'lead_type') {

            $lead_type_id = LeadType::find($id);
            if(!$lead_type_id){
                return redirect()->route('setting.lead.index')->with('error', __('Something is wrong.'));
            }
        }
        else
        {
            return redirect()->back();
        }

        return view('setting.lead')->with([
            'type' => $type,
            'sources' => $sources,
            'statuses' => $statuses,
            'source' => $source,
            'status' => $status,
            'lead_type_all' => $lead_type_all,
            'lead_type_id' => $lead_type_id,
            'settingsActivityTimeline' => $this->leadSettingsActivityTimeline((int) $user->creatorId()),
        ]);

    }

    public function lead_update(Request $request, string $type, int $id){

        if($request->has('setting') && $request->setting  == 'source'){

            $validator = \Validator::make(
                $request->all(), [
                    'source' => 'required|max:120',
                ]
            );

        }

        if($request->has('setting') && $request->setting  == 'status'){

            $validator = \Validator::make(
                $request->all(), [
                    'statusName' => 'required|max:120',
                    'color'     => 'required|max:120',
                ]
            );

        }

        if($request->has('setting') && $request->setting  == 'lead_type'){

                $validator = \Validator::make(
                    $request->all(), [
                        'lead_type_name' => 'required|max:120',
                    ]
                );

                if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

        }


        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        if($request->has('setting') && $request->setting == 'source'){
            $source = LeadSource::find($id);

            if (!$source) {
                return redirect()->route('setting.lead.index',['source'])->with('error', __('Lead source not found.'));
            }

            if ((int) ($source->is_editable ?? 1) !== 1) {
                return redirect()->route('setting.lead.index',['source'])->with('error', __('Predefined lead source cannot be edited.'));
            }

            $originalSource = [
                'name' => $source->name,
            ];

            $leadStatus = $source->update(['name' => $request->source]);

            if($leadStatus){
                if ($source) {
                    $changes = ActivityLogger::diff(
                        $originalSource,
                        ['name' => $request->source]
                    );
                    if (!empty($changes)) {
                        $this->writeSettingsActivity(
                            'update',
                            'settings.system_updated',
                            'Lead source updated.',
                            [
                                'type' => 'lead_source',
                                'item_id' => $source->id,
                                'changes' => $changes,
                            ]
                        );
                    }
                }

                return redirect()->route('setting.lead.index',['source'])->with('success', __('Lead source successfully updated.'));

            } else {

                return redirect()->route('setting.lead.index',['source'])->with('error', __('Something is wrong.'));

            }


        }

        if($request->has('setting') && $request->setting == 'status'){
            $status = LeadStage::find($id);

            if (!$status) {
                return redirect()->route('setting.lead.index',['status'])->with('error', __('Lead status not found.'));
            }

            if ((int) ($status->is_editable ?? 1) !== 1) {
                return redirect()->route('setting.lead.index',['status'])->with('error', __('Predefined lead status cannot be edited.'));
            }

            $originalStatus = [
                'name' => $status->name,
                'color' => $status->color,
            ];

            $leadSource = $status->update(['name' => $request->statusName, 'color' => $request->color]);

            if($leadSource){
                if ($status) {
                    $changes = ActivityLogger::diff(
                        $originalStatus,
                        [
                            'name' => $request->statusName,
                            'color' => $request->color,
                        ]
                    );
                    if (!empty($changes)) {
                        $this->writeSettingsActivity(
                            'update',
                            'settings.system_updated',
                            'Lead stage updated.',
                            [
                                'type' => 'lead_stage',
                                'item_id' => $status->id,
                                'changes' => $changes,
                            ]
                        );
                    }
                }

                return redirect()->route('setting.lead.index',['status'])->with('success', __('Lead status successfully updated.'));

            } else {

                return redirect()->route('setting.lead.index',['status'])->with('error', __('Something is wrong.'));

            }

        }

        if($request->has('setting') && $request->setting == 'lead_type')
        {
            $leadTypeRecord = LeadType::find($request->id);

            $leadType = LeadType::where('id', $request->id)->update(['name' => $request->lead_type_name]);

            if($leadType){
                if ($leadTypeRecord) {
                    $changes = ActivityLogger::diff(
                        ['name' => $leadTypeRecord->name],
                        ['name' => $request->lead_type_name]
                    );
                    if (!empty($changes)) {
                        $this->writeSettingsActivity(
                            'update',
                            'settings.system_updated',
                            'Lead type updated.',
                            [
                                'type' => 'lead_type',
                                'item_id' => $leadTypeRecord->id,
                                'changes' => $changes,
                            ]
                        );
                    }
                }

                return redirect()->route('setting.lead.index',['lead_type'])->with('success', __('Lead Type successfully updated.'));

            } else {

                return redirect()->route('setting.lead.index',['lead_type'])->with('error', __('Something is wrong.'));

            }


        }

    }

    public function destroy(string $type, string $id)
    {
        if ($type === 'source') {
            $source = LeadSource::find((int) $id);

            if (!$source) {
                return redirect()->route('setting.lead.index', ['source'])->with('error', __('Lead source not found.'));
            }

            if ((int) ($source->is_editable ?? 1) !== 1) {
                return redirect()->route('setting.lead.index', ['source'])->with('error', __('Predefined lead source cannot be deleted.'));
            }

            $sourceName = $source->name;
            $sourceId = $source->id;

            if (!$source->delete()) {
                return redirect()->route('setting.lead.index', ['source'])->with('error', __('Something is wrong.'));
            }

            $this->writeSettingsActivity(
                'delete',
                'settings.system_updated',
                'Lead source deleted.',
                [
                    'type' => 'lead_source',
                    'item_id' => $sourceId,
                    'name' => $sourceName,
                ]
            );

            return redirect()->route('setting.lead.index', ['source'])->with('success', __('Lead source successfully deleted.'));
        }

        if ($type !== 'status') {
            return redirect()->route('setting.lead.index')->with('error', __('Something is wrong.'));
        }

        $status = LeadStage::find((int) $id);

        if (!$status) {
            return redirect()->route('setting.lead.index', ['status'])->with('error', __('Lead status not found.'));
        }

        if ((int) ($status->is_editable ?? 1) !== 1) {
            return redirect()->route('setting.lead.index', ['status'])->with('error', __('Predefined lead status cannot be deleted.'));
        }

        $stageName = $status->name;
        $stageColor = $status->color;
        $stageId = $status->id;

        if (!$status->delete()) {
            return redirect()->route('setting.lead.index', ['status'])->with('error', __('Something is wrong.'));
        }

        $this->writeSettingsActivity(
            'delete',
            'settings.system_updated',
            'Lead stage deleted.',
            [
                'type' => 'lead_stage',
                'item_id' => $stageId,
                'name' => $stageName,
                'color' => $stageColor,
            ]
        );

        return redirect()->route('setting.lead.index', ['status'])->with('success', __('Lead status successfully deleted.'));
    }

    public function order()
    {
        $user = \Auth::user();
        $statuses = OrderStage::where('created_by', '=', $user->creatorId())->get();
        $settingsActivityTimeline = $this->orderSettingsActivityTimeline((int) $user->creatorId());
        return view('setting.order')->with(['statuses' => $statuses, 'settingsActivityTimeline' => $settingsActivityTimeline]);
    }

    public function order_save(Request $request)
    {
        $objUser = \Auth::user()->creatorId();

        if($request->has('setting') && $request->setting  == 'status')
        {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required|max:120',
                    'color'      => 'required|max:120',
                ]
            );
        }

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $request['created_by'] = \Auth::user()->creatorId();

        if($request->has('setting') && $request->setting == 'status')
        {
            $request['name'] = $request['name'];
            $request['color'] = $request['color'];

            $maxOrder = OrderStage::max('order');
            $latestOrder = $maxOrder ? $maxOrder + 1 : 1;

            $request['order'] = $latestOrder;

            $leadSource = OrderStage::create($request->all());

            if($leadSource){
                $this->writeSettingsActivity(
                    'create',
                    'settings.order_updated',
                    'Order status created.',
                    [
                        'type' => 'order_stage',
                        'item_id' => $leadSource->id,
                        'name' => $leadSource->name,
                        'color' => $leadSource->color,
                    ]
                );
                return redirect()->route('setting.order.index')->with('success', __('Order status successfully created.'));
            } else {
                return redirect()->route('setting.order.index')->with('error', __('Something is wrong.'));
            }

        }

    }

    public function order_edit(string $type, int $id)
    {
        $user = \Auth::user();

        $statuses   = OrderStage::where('created_by', '=', $user->creatorId())->get();

        $source = $status = [];

         if($type == 'status')
         {
            $status = OrderStage::find($id);
            if(!$status){
                return redirect()->route('setting.order.index')->with('error', __('Something is wrong.'));
            }
        } else {
            return redirect()->back();
        }

        return view('setting.order')->with([
            'status' => $status,
            'statuses' => $statuses,
            'settingsActivityTimeline' => $this->orderSettingsActivityTimeline((int) $user->creatorId()),
        ]);

    }

    public function order_update(Request $request, string $type, int $id)
    {
        if($request->has('setting') && $request->setting  == 'status'){

            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required|max:120',
                    'color' => 'required|max:120',
                ]
            );
        }

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        if($request->has('setting') && $request->setting == 'status'){
            $orderStatus = OrderStage::find($request->id);

            $leadSource = OrderStage::where('id', $request->id)->update(['name' => $request->name, 'color' => $request->color]);

            if($leadSource){
                if ($orderStatus) {
                    $changes = ActivityLogger::diff(
                        [
                            'name' => $orderStatus->name,
                            'color' => $orderStatus->color,
                        ],
                        [
                            'name' => $request->name,
                            'color' => $request->color,
                        ]
                    );
                    if (!empty($changes)) {
                        $this->writeSettingsActivity(
                            'update',
                            'settings.order_updated',
                            'Order status updated.',
                            [
                                'type' => 'order_stage',
                                'item_id' => $orderStatus->id,
                                'changes' => $changes,
                            ]
                        );
                    }
                }

                return redirect()->route('setting.order.index')->with('success', __('Order status successfully updated.'));

            } else {

                return redirect()->route('setting.order.index')->with('error', __('Something is wrong.'));

            }

        }

    }



    public function order_destroy(string $type, string $id)
    {
        return redirect()->route('setting.order.index')->with('error', __('Under working...'));
    }

    public function taxes()
    {
        $user = \Auth::user();
        $taxes = GstSlabMaster::where('created_by', '=', $user->creatorId())->get();
        $settingsActivityTimeline = $this->taxSettingsActivityTimeline((int) $user->creatorId());
        return view('setting.taxes')->with(['taxes' => $taxes, 'settingsActivityTimeline' => $settingsActivityTimeline]);
    }

    public function tax_save(Request $request)
    {
        $objUser = \Auth::user()->creatorId();

        if($request->has('setting') && $request->setting  == 'taxes')
        {
            $validator = \Validator::make(
                $request->all(), [
                    // 'name' => 'required|max:120',
                    'taxPercentage' => 'required|integer',
                ]
            );
        }

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $request['created_by'] = \Auth::user()->creatorId();

        if($request->has('setting') && $request->setting == 'taxes')
        {
            // $request['name'] = $request['name'];
            $request['rate'] = $request['taxPercentage'];

            $Taxes = GstSlabMaster::create($request->all());

            if($Taxes){
                $this->writeSettingsActivity(
                    'create',
                    'settings.gst_updated',
                    'GST slab created.',
                    [
                        'type' => 'gst_slab',
                        'item_id' => $Taxes->id,
                        'rate' => $Taxes->rate,
                    ]
                );
                return redirect()->route('setting.taxes')->with('success', __('Tax successfully created.'));
            } else {
                return redirect()->route('setting.taxes')->with('error', __('Something is wrong.'));
            }

        }

    }


    public function tax_edit(string $type, int $id)
    {
        $user = \Auth::user();

        $taxes   = GstSlabMaster::where('created_by', '=', $user->creatorId())->get();

        $tax = [];

         if($type == 'taxes')
         {
            $tax = GstSlabMaster::find($id);
            if(!$tax){
                return redirect()->route('setting.taxes')->with('error', __('Something is wrong.'));
            }
        } else {
            return redirect()->back();
        }

        return view('setting.taxes')->with([
            'tax' => $tax,
            'taxes' => $taxes,
            'settingsActivityTimeline' => $this->taxSettingsActivityTimeline((int) $user->creatorId()),
        ]);

    }


    public function tax_update(Request $request, string $type, int $id)
    {
        if($request->has('setting') && $request->setting  == 'taxes'){

            $validator = \Validator::make(
                $request->all(), [
                    // 'name' => 'required|max:120',
                    'taxPercentage' => 'required|integer',
                ]
            );
        }

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        if($request->has('setting') && $request->setting == 'taxes'){
            $taxRecord = GstSlabMaster::find($request->id);

            $Taxes = GstSlabMaster::where('id', $request->id)->update(['rate' => $request->taxPercentage]);

            if($Taxes){
                if ($taxRecord) {
                    $changes = ActivityLogger::diff(
                        ['rate' => $taxRecord->rate],
                        ['rate' => $request->taxPercentage]
                    );
                    if (!empty($changes)) {
                        $this->writeSettingsActivity(
                            'update',
                            'settings.gst_updated',
                            'GST slab updated.',
                            [
                                'type' => 'gst_slab',
                                'item_id' => $taxRecord->id,
                                'changes' => $changes,
                            ]
                        );
                    }
                }

                return redirect()->route('setting.taxes')->with('success', __('Tax has been updated successfully.'));

            } else {

                return redirect()->route('setting.order.index')->with('error', __('Something is wrong.'));

            }

        }

    }

    public function tax_destroy(string $type, string $id)
    {
        return redirect()->route('setting.taxes')->with('error', __('Under working...'));
    }
}
