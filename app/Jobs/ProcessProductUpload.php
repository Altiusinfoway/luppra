<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Models\UserLead;
use App\Models\LeadStage;
use App\Models\LeadSource;
use App\Models\Utility;
use App\Models\Entity;
use App\Models\User;
use App\Models\CustomerPhone;
use App\Models\Address;
use App\Models\Products;
use App\Models\Units;
use App\Models\UnitTypes;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessProductUpload implements ShouldQueue
{
    use Queueable;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user, $jobKey, $productsData;

    public function __construct($user, $jobKey, $productsData)
    {
        Log::info('------ construct process product upload --------');
        $this->user = $user;
        $this->jobKey = $jobKey;
        $this->productsData = $productsData;


        Log::info("User: ", ['usr' => $user]);
        Log::info("job key : ", ['key' => json_encode($jobKey)]);
        Log::info("product data : ", ['product data' => $productsData]);
    }

    public function handle()
    {
        Log::info("===== Product Upload Started =====");

        $user_data = User::find($this->user);
        $super_admin = User::where('type','company')->first();

        foreach ($this->productsData as $sheetIndex => $row) {

            if ($sheetIndex === 0) {
                continue; // skip header row
            }

            if (!is_array($row)) {
                continue;
            }

            $name      = trim($row['name'] ?? '');
            $price     = trim($row['price'] ?? '');
            $sku       = trim($row['sku_code'] ?? '');
            $unitType  = strtolower(trim($row['unit_type'] ?? ''));
            $unitName  = strtolower(trim($row['unit'] ?? ''));

            // Skip empty rows
            if (empty($name) && empty($price) && empty($unitType) && empty($unitName)) {
                continue;
            }

              try
              {

                DB::beginTransaction();

                if (!empty($unitType)) {

                    $unitTypeRecord = UnitTypes::whereRaw("LOWER(name) = ?", [$unitType])->first();

                    if (!$unitTypeRecord) {
                        $unitTypeRecord = UnitTypes::create([
                            'name' => ucfirst($unitType)
                        ]);
                    }

                    $unitTypeId = $unitTypeRecord->id;

                } else {
                    $defaultType = UnitTypes::where('name', 'Weight')->first();
                    $unitTypeId = $defaultType ? $defaultType->id : null;
                }

                $unitId = null;

                if (!empty($unitName)) {

                    $unitsUnderType = Units::where('type_id', $unitTypeId)->get();

                    $unitRecord = $unitsUnderType->first(function ($u) use ($unitName) {
                        return strtolower($u->name) === strtolower($unitName);
                    });

                    if ($unitRecord) {
                        $unitId = $unitRecord->id;

                    } else {
                        $unitRecord = Units::create([
                            'name'     => ucfirst($unitName),
                            'type_id'  => $unitTypeId,
                        ]);
                        $unitId = $unitRecord->id;
                    }

                } else {
                    $defaultUnit = Units::where('name', 'Gram')->first();
                    $unitId = $defaultUnit ? $defaultUnit->id : null;
                }

                Products::create([
                    'name'       => $name,
                    'sku_code'   => $sku,
                    'price'      => $price,
                    'unit_type'  => $unitTypeId,
                    'unit'       => $unitId,
                    'created_by' =>  \Auth::user()->creatorId(),
                ]);

                DB::commit();
                Log::info("Inserted Product Row: {$name}");

            } catch (\Exception $e) {

                DB::rollBack();

                Log::error(" Product Insert Failed", [
                    'sheetIndex' => $sheetIndex,
                    'error'      => $e->getMessage(),
                    'row'        => $row
                ]);

                continue;
            }
        }

        Cache::put($this->jobKey, 'completed', now()->addMinutes(10));
        $this->delete();

        Log::info("===== Product Upload Completed Successfully =====");



    }
}
