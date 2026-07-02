<?php

use App\Models\Tenant;
use App\Support\Tenancy\TenancyManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $defaultSourceNames = [
        'facebook',
        'others',
        'instagram',
        'india mart',
    ];

    public function up(): void
    {
        $this->updateLeadSourcesTable('landlord');
        $this->forEachTenant(function (): void {
            $this->updateLeadSourcesTable('tenant');
        });
    }

    public function down(): void
    {
        $this->rollbackLeadSourcesTable('landlord');
        $this->forEachTenant(function (): void {
            $this->rollbackLeadSourcesTable('tenant');
        });
    }

    private function updateLeadSourcesTable(string $connection): void
    {
        if (!Schema::connection($connection)->hasTable('lead_sources')) {
            return;
        }

        $addIsEditable = !Schema::connection($connection)->hasColumn('lead_sources', 'is_editable');
        $addDeletedAt = !Schema::connection($connection)->hasColumn('lead_sources', 'deleted_at');

        if ($addIsEditable || $addDeletedAt) {
            Schema::connection($connection)->table('lead_sources', function (Blueprint $table) use ($addIsEditable, $addDeletedAt) {
                if ($addIsEditable) {
                    $table->integer('is_editable')->default(0)->after('order');
                }

                if ($addDeletedAt) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        }

        if ($addIsEditable) {
            DB::connection($connection)
                ->table('lead_sources')
                ->whereIn('name', $this->defaultSourceNames)
                ->update(['is_editable' => 0]);

            DB::connection($connection)
                ->table('lead_sources')
                ->whereNotIn('name', $this->defaultSourceNames)
                ->update(['is_editable' => 1]);
        }
    }

    private function rollbackLeadSourcesTable(string $connection): void
    {
        if (!Schema::connection($connection)->hasTable('lead_sources')) {
            return;
        }

        $dropIsEditable = Schema::connection($connection)->hasColumn('lead_sources', 'is_editable');
        $dropDeletedAt = Schema::connection($connection)->hasColumn('lead_sources', 'deleted_at');

        if (!$dropIsEditable && !$dropDeletedAt) {
            return;
        }

        Schema::connection($connection)->table('lead_sources', function (Blueprint $table) use ($dropIsEditable, $dropDeletedAt) {
            if ($dropDeletedAt) {
                $table->dropSoftDeletes();
            }

            if ($dropIsEditable) {
                $table->dropColumn('is_editable');
            }
        });
    }

    private function forEachTenant(callable $callback): void
    {
        $tenancy = app(TenancyManager::class);

        Tenant::query()
            ->whereNotNull('database')
            ->where('database', '!=', '')
            ->orderBy('id')
            ->get()
            ->each(function (Tenant $tenant) use ($callback, $tenancy): void {
                $tenancy->initialize($tenant);
                app()->instance('currentTenant', $tenant);

                try {
                    $callback();
                } finally {
                    $tenancy->end();
                    app()->forgetInstance('currentTenant');
                }
            });
    }
};
