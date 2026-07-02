<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
           Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'stock_qty')) {
                $table->integer('stock_qty')
                    ->default(0)
                    ->after('product_id'); // Change 'qty' to the appropriate existing column
            }
        });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'stock_qty')) {
                    $table->dropColumn('stock_qty');
                }
            });
        });
    }
};
