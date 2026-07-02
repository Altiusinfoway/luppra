<?php

use App\Models\Tenant;
use App\Support\Tenancy\TenancyManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $defaultStageNames = [
        'New',
        'Proposal',
        'Negotiation',
        'Won',
        'Close',
        'Not Interested',
    ];

    public function up(): void
    {
        $this->updateLeadStagesTable('landlord');
        $this->forEachTenant(function (): void {
            $this->updateLeadStagesTable('tenant');
        });
    }

    public function down(): void
    {
        $this->rollbackLeadStagesTable('landlord');
        $this->forEachTenant(function (): void {
            $this->rollbackLeadStagesTable('tenant');
        });
    }

    private function updateLeadStagesTable(string $connection): void
    {
        if (!Schema::connection($connection)->hasTable('lead_stages')) {
            return;
        }

        $addIsEditable = !Schema::connection($connection)->hasColumn('lead_stages', 'is_editable');
        $addDeletedAt = !Schema::connection($connection)->hasColumn('lead_stages', 'deleted_at');

        if ($addIsEditable || $addDeletedAt) {
            Schema::connection($connection)->table('lead_stages', function (Blueprint $table) use ($addIsEditable, $addDeletedAt) {
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
                ->table('lead_stages')
                ->whereIn('name', $this->defaultStageNames)
                ->update(['is_editable' => 0]);

            DB::connection($connection)
                ->table('lead_stages')
                ->whereNotIn('name', $this->defaultStageNames)
                ->update(['is_editable' => 1]);
        }
    }

    private function rollbackLeadStagesTable(string $connection): void
    {
        if (!Schema::connection($connection)->hasTable('lead_stages')) {
            return;
        }

        $dropIsEditable = Schema::connection($connection)->hasColumn('lead_stages', 'is_editable');
        $dropDeletedAt = Schema::connection($connection)->hasColumn('lead_stages', 'deleted_at');

        if (!$dropIsEditable && !$dropDeletedAt) {
            return;
        }

        Schema::connection($connection)->table('lead_stages', function (Blueprint $table) use ($dropIsEditable, $dropDeletedAt) {
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
