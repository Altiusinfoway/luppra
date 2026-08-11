<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('marketplace_listings')) {
            return;
        }

        $dropIndexes = [
            'marketplace_listing_platform_sku_unique',
            'marketplace_listing_item_unique',
            'marketplace_listing_platform_account_sku_unique',
            'marketplace_listing_platform_account_item_unique',
        ];

        foreach ($dropIndexes as $indexName) {
            if ($this->indexExists('marketplace_listings', $indexName)) {
                Schema::table('marketplace_listings', function (Blueprint $table) use ($indexName) {
                    $table->dropUnique($indexName);
                });
            }
        }

        if (
            Schema::hasColumn('marketplace_listings', 'marketplace_account_id')
            && !$this->indexExists('marketplace_listings', 'marketplace_listing_account_sku_unique')
        ) {
            Schema::table('marketplace_listings', function (Blueprint $table) {
                $table->unique(
                    ['created_by', 'marketplace_account_id', 'platform_sku'],
                    'marketplace_listing_account_sku_unique'
                );
            });
        }

        if (
            Schema::hasColumn('marketplace_listings', 'marketplace_account_id')
            && !$this->indexExists('marketplace_listings', 'marketplace_listing_account_item_unique')
        ) {
            Schema::table('marketplace_listings', function (Blueprint $table) {
                $table->unique(
                    ['created_by', 'marketplace_account_id', 'marketplace_item_id'],
                    'marketplace_listing_account_item_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('marketplace_listings')) {
            return;
        }

        if ($this->indexExists('marketplace_listings', 'marketplace_listing_account_sku_unique')) {
            Schema::table('marketplace_listings', function (Blueprint $table) {
                $table->dropUnique('marketplace_listing_account_sku_unique');
            });
        }

        if ($this->indexExists('marketplace_listings', 'marketplace_listing_account_item_unique')) {
            Schema::table('marketplace_listings', function (Blueprint $table) {
                $table->dropUnique('marketplace_listing_account_item_unique');
            });
        }

        if (
            Schema::hasColumn('marketplace_listings', 'account_name')
            && !$this->indexExists('marketplace_listings', 'marketplace_listing_platform_account_sku_unique')
        ) {
            Schema::table('marketplace_listings', function (Blueprint $table) {
                $table->unique(
                    ['created_by', 'platform', 'account_name', 'platform_sku'],
                    'marketplace_listing_platform_account_sku_unique'
                );
            });
        }

        if (
            Schema::hasColumn('marketplace_listings', 'account_name')
            && !$this->indexExists('marketplace_listings', 'marketplace_listing_platform_account_item_unique')
        ) {
            Schema::table('marketplace_listings', function (Blueprint $table) {
                $table->unique(
                    ['created_by', 'platform', 'account_name', 'marketplace_item_id'],
                    'marketplace_listing_platform_account_item_unique'
                );
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        $row = DB::selectOne(
            'SELECT COUNT(*) AS aggregate FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $indexName]
        );

        return (int) ($row->aggregate ?? 0) > 0;
    }
};
