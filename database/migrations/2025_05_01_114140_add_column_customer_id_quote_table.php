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
        Schema::table('quotes', function (Blueprint $table) {
            if (Schema::hasColumn('quotes', 'customer_id')) {
                $foreignKeyName = $this->getForeignKeyName('quotes', 'customer_id');

                if ($foreignKeyName) {
                    $table->dropForeign($foreignKeyName);
                }

                $table->dropColumn('customer_id');
            }

            if (Schema::hasColumn('quotes', 'where_from')) {
                $table->dropColumn('where_from');
            }
        });


        Schema::table('quotes', function (Blueprint $table) {
            if (!Schema::hasColumn('quotes', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('lead_id');
            }

            if (!Schema::hasColumn('quotes', 'where_from')) {
                $table->string('where_from')->nullable()->comment('Lead,Customer')->after('customer_id');
            }

            if (!$this->getForeignKeyName('quotes', 'customer_id')) {
                $table->foreign('customer_id')->references('id')->on('entities')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $foreignKeyName = $this->getForeignKeyName('quotes', 'customer_id');

            if ($foreignKeyName) {
                $table->dropForeign($foreignKeyName);
            }

            if (Schema::hasColumn('quotes', 'customer_id')) {
                $table->dropColumn('customer_id');
            }

            if (Schema::hasColumn('quotes', 'where_from')) {
                $table->dropColumn('where_from');
            }
        });
    }

    private function getForeignKeyName(string $tableName, string $columnName): ?string
    {
        $databaseName = DB::getDatabaseName();

        $row = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->select('CONSTRAINT_NAME')
            ->where('TABLE_SCHEMA', $databaseName)
            ->where('TABLE_NAME', $tableName)
            ->where('COLUMN_NAME', $columnName)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->first();

        return $row?->CONSTRAINT_NAME;
    }
};
