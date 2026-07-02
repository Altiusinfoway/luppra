<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_targets', 'incentive_mode')) {
                $table->string('incentive_mode', 40)->default('percent_over_target')->after('incentive');
            }
            if (!Schema::hasColumn('sales_targets', 'incentive_value')) {
                $table->decimal('incentive_value', 12, 2)->default(0)->after('incentive_mode');
            }
            if (!Schema::hasColumn('sales_targets', 'incentive_slabs')) {
                $table->longText('incentive_slabs')->nullable()->after('incentive_value');
            }
        });

        DB::table('sales_targets')
            ->whereNull('incentive_mode')
            ->update([
                'incentive_mode' => 'percent_over_target',
            ]);

        DB::table('sales_targets')
            ->where('incentive_value', 0)
            ->update([
                'incentive_value' => DB::raw('COALESCE(incentive,0)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            if (Schema::hasColumn('sales_targets', 'incentive_slabs')) {
                $table->dropColumn('incentive_slabs');
            }
            if (Schema::hasColumn('sales_targets', 'incentive_value')) {
                $table->dropColumn('incentive_value');
            }
            if (Schema::hasColumn('sales_targets', 'incentive_mode')) {
                $table->dropColumn('incentive_mode');
            }
        });
    }
};

