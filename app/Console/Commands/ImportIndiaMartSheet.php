<?php

namespace App\Console\Commands;

use App\Http\Controllers\GoogleSheetImportController;
use App\Models\Tenant;
use Illuminate\Console\Command;
use App\Support\Tenancy\TenancyManager;
use Symfony\Component\HttpFoundation\Response;

class ImportIndiaMartSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'india-mart-sheet:import {tenant? : Tenant ID or slug (optional)}';

    protected $description = 'Import india mart lead.';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenants = $this->resolveTargetTenants();

        if ($tenants->isEmpty()) {
            $this->error('No active tenant found for IndiaMart import.');
            return self::FAILURE;
        }

        $tenancy = app(TenancyManager::class);
        $controller = app(GoogleSheetImportController::class);
        $hasFailure = false;

        foreach ($tenants as $tenant) {
            if (empty($tenant->database)) {
                $this->warn("Skipping tenant #{$tenant->id}: database is empty.");
                $hasFailure = true;
                continue;
            }

            $this->line("Importing IndiaMart leads for tenant #{$tenant->id} ({$tenant->slug})...");

            $tenancy->initialize($tenant);
            app()->instance('currentTenant', $tenant);

            try {
                $result = $controller->india_mart_import();

                if ($this->isFailedResult($result)) {
                    $this->error("IndiaMart import failed for tenant #{$tenant->id}.");
                    $hasFailure = true;
                    continue;
                }

                $this->info("IndiaMart import completed for tenant #{$tenant->id}.");
            } catch (\Throwable $e) {
                report($e);
                $this->error("IndiaMart import failed for tenant #{$tenant->id}: {$e->getMessage()}");
                $hasFailure = true;
            } finally {
                $tenancy->end();
                app()->forgetInstance('currentTenant');
            }
        }

        return $hasFailure ? self::FAILURE : self::SUCCESS;
    }

    private function resolveTargetTenants()
    {
        $tenantRef = (string) ($this->argument('tenant') ?? '');
        $tenantRef = trim($tenantRef);

        $tenantQuery = Tenant::query()->where('is_active', true);

        if ($tenantRef !== '') {
            if (is_numeric($tenantRef)) {
                return $tenantQuery->where('id', (int) $tenantRef)->get();
            }

            return $tenantQuery->where('slug', $tenantRef)->get();
        }

        return $tenantQuery->orderBy('id')->get();
    }

    private function isFailedResult($result): bool
    {
        if ($result === false) {
            return true;
        }

        if ($result instanceof Response) {
            return $result->getStatusCode() >= 400;
        }

        return false;
    }
}
