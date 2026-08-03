<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            if (!Schema::hasColumn('marketplace_listings', 'base_price')) {
                $table->decimal('base_price', 12, 2)->nullable()->after('mrp');
            }

            if (!Schema::hasColumn('marketplace_listings', 'external_orders_count')) {
                $table->unsignedInteger('external_orders_count')->default(0)->after('reserved_stock');
            }

            if (!Schema::hasColumn('marketplace_listings', 'external_sold_qty')) {
                $table->decimal('external_sold_qty', 12, 2)->default(0)->after('external_orders_count');
            }

            if (!Schema::hasColumn('marketplace_listings', 'external_revenue')) {
                $table->decimal('external_revenue', 12, 2)->default(0)->after('external_sold_qty');
            }

            if (!Schema::hasColumn('marketplace_listings', 'external_last_synced_at')) {
                $table->timestamp('external_last_synced_at')->nullable()->after('external_revenue');
            }

            if (!Schema::hasColumn('marketplace_listings', 'external_sync_note')) {
                $table->string('external_sync_note')->nullable()->after('external_last_synced_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            foreach ([
                'external_sync_note',
                'external_last_synced_at',
                'external_revenue',
                'external_sold_qty',
                'external_orders_count',
                'base_price',
            ] as $column) {
                if (Schema::hasColumn('marketplace_listings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
