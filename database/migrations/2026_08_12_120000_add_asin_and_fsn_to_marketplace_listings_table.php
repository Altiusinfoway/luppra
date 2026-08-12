<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('marketplace_listings')) {
            return;
        }

        Schema::table('marketplace_listings', function (Blueprint $table) {
            if (!Schema::hasColumn('marketplace_listings', 'asin')) {
                $table->string('asin')->nullable()->after('marketplace_item_id');
            }

            if (!Schema::hasColumn('marketplace_listings', 'fsn')) {
                $table->string('fsn')->nullable()->after('asin');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('marketplace_listings')) {
            return;
        }

        Schema::table('marketplace_listings', function (Blueprint $table) {
            if (Schema::hasColumn('marketplace_listings', 'fsn')) {
                $table->dropColumn('fsn');
            }

            if (Schema::hasColumn('marketplace_listings', 'asin')) {
                $table->dropColumn('asin');
            }
        });
    }
};
