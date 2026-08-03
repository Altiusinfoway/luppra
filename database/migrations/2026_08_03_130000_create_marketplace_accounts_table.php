<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('marketplace_accounts')) {
            Schema::create('marketplace_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('created_by');
                $table->string('platform', 30);
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['created_by', 'platform', 'name'], 'marketplace_accounts_creator_platform_name_unique');
            });
        }

        if (!Schema::hasTable('marketplace_listings')) {
            return;
        }

        if (!Schema::hasColumn('marketplace_listings', 'marketplace_account_id')) {
            Schema::table('marketplace_listings', function (Blueprint $table) {
                $table->unsignedBigInteger('marketplace_account_id')->nullable()->after('account_name');
            });
        }

        $listingRows = DB::table('marketplace_listings')
            ->select('id', 'created_by', 'platform', 'account_name')
            ->get();

        foreach ($listingRows as $listingRow) {
            $platform = strtolower(trim((string) ($listingRow->platform ?? '')));
            $accountName = trim((string) ($listingRow->account_name ?? '')) ?: 'Primary Account';

            $accountId = DB::table('marketplace_accounts')
                ->where('created_by', (int) $listingRow->created_by)
                ->where('platform', $platform)
                ->where('name', $accountName)
                ->value('id');

            if (!$accountId) {
                $accountId = DB::table('marketplace_accounts')->insertGetId([
                    'created_by' => (int) $listingRow->created_by,
                    'platform' => $platform,
                    'name' => $accountName,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('marketplace_listings')
                ->where('id', (int) $listingRow->id)
                ->update(['marketplace_account_id' => $accountId]);
        }

        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->foreign('marketplace_account_id')
                ->references('id')
                ->on('marketplace_accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('marketplace_listings') && Schema::hasColumn('marketplace_listings', 'marketplace_account_id')) {
            Schema::table('marketplace_listings', function (Blueprint $table) {
                $table->dropForeign(['marketplace_account_id']);
                $table->dropColumn('marketplace_account_id');
            });
        }

        Schema::dropIfExists('marketplace_accounts');
    }
};
