<?php

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
        $this->updateLeadSourcesTable();
    }

    public function down(): void
    {
        $this->rollbackLeadSourcesTable();
    }

    private function updateLeadSourcesTable(): void
    {
        if (!Schema::hasTable('lead_sources')) {
            return;
        }

        $addIsEditable = !Schema::hasColumn('lead_sources', 'is_editable');
        $addDeletedAt = !Schema::hasColumn('lead_sources', 'deleted_at');

        if ($addIsEditable || $addDeletedAt) {
            Schema::table('lead_sources', function (Blueprint $table) use ($addIsEditable, $addDeletedAt) {
                if ($addIsEditable) {
                    $table->integer('is_editable')->default(0)->after('order');
                }

                if ($addDeletedAt) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        }

        if ($addIsEditable) {
            DB::table('lead_sources')
                ->whereIn('name', $this->defaultSourceNames)
                ->update(['is_editable' => 0]);

            DB::table('lead_sources')
                ->whereNotIn('name', $this->defaultSourceNames)
                ->update(['is_editable' => 1]);
        }
    }

    private function rollbackLeadSourcesTable(): void
    {
        if (!Schema::hasTable('lead_sources')) {
            return;
        }

        $dropIsEditable = Schema::hasColumn('lead_sources', 'is_editable');
        $dropDeletedAt = Schema::hasColumn('lead_sources', 'deleted_at');

        if (!$dropIsEditable && !$dropDeletedAt) {
            return;
        }

        Schema::table('lead_sources', function (Blueprint $table) use ($dropIsEditable, $dropDeletedAt) {
            if ($dropDeletedAt) {
                $table->dropSoftDeletes();
            }

            if ($dropIsEditable) {
                $table->dropColumn('is_editable');
            }
        });
    }

};
