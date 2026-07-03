<?php

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
        $this->updateLeadStagesTable();
    }

    public function down(): void
    {
        $this->rollbackLeadStagesTable();
    }

    private function updateLeadStagesTable(): void
    {
        if (!Schema::hasTable('lead_stages')) {
            return;
        }

        $addIsEditable = !Schema::hasColumn('lead_stages', 'is_editable');
        $addDeletedAt = !Schema::hasColumn('lead_stages', 'deleted_at');

        if ($addIsEditable || $addDeletedAt) {
            Schema::table('lead_stages', function (Blueprint $table) use ($addIsEditable, $addDeletedAt) {
                if ($addIsEditable) {
                    $table->integer('is_editable')->default(0)->after('order');
                }

                if ($addDeletedAt) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        }

        if ($addIsEditable) {
            DB::table('lead_stages')
                ->whereIn('name', $this->defaultStageNames)
                ->update(['is_editable' => 0]);

            DB::table('lead_stages')
                ->whereNotIn('name', $this->defaultStageNames)
                ->update(['is_editable' => 1]);
        }
    }

    private function rollbackLeadStagesTable(): void
    {
        if (!Schema::hasTable('lead_stages')) {
            return;
        }

        $dropIsEditable = Schema::hasColumn('lead_stages', 'is_editable');
        $dropDeletedAt = Schema::hasColumn('lead_stages', 'deleted_at');

        if (!$dropIsEditable && !$dropDeletedAt) {
            return;
        }

        Schema::table('lead_stages', function (Blueprint $table) use ($dropIsEditable, $dropDeletedAt) {
            if ($dropDeletedAt) {
                $table->dropSoftDeletes();
            }

            if ($dropIsEditable) {
                $table->dropColumn('is_editable');
            }
        });
    }

};
