<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_listings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('created_by');
            $table->string('platform', 30);
            $table->string('platform_sku');
            $table->string('marketplace_item_id')->nullable();
            $table->string('listing_title');
            $table->string('pack_size')->nullable();
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('mrp', 12, 2)->default(0);
            $table->string('listing_status', 30)->default('active');
            $table->string('fulfillment_type', 50)->nullable();
            $table->integer('allocated_stock')->nullable();
            $table->integer('reserved_stock')->default(0);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unique(['created_by', 'platform', 'platform_sku'], 'marketplace_listing_platform_sku_unique');
            $table->unique(['created_by', 'platform', 'marketplace_item_id'], 'marketplace_listing_item_unique');
        });

        Schema::table('lead_products', function (Blueprint $table) {
            if (!Schema::hasColumn('lead_products', 'marketplace_listing_id')) {
                $table->unsignedBigInteger('marketplace_listing_id')->nullable()->after('product_id');
                $table->foreign('marketplace_listing_id')->references('id')->on('marketplace_listings')->nullOnDelete();
            }
        });

        Schema::table('quote_products', function (Blueprint $table) {
            if (!Schema::hasColumn('quote_products', 'marketplace_listing_id')) {
                $table->unsignedBigInteger('marketplace_listing_id')->nullable()->after('product_id');
                $table->foreign('marketplace_listing_id')->references('id')->on('marketplace_listings')->nullOnDelete();
            }
        });

        Schema::table('order_products', function (Blueprint $table) {
            if (!Schema::hasColumn('order_products', 'marketplace_listing_id')) {
                $table->unsignedBigInteger('marketplace_listing_id')->nullable()->after('product_id');
                $table->foreign('marketplace_listing_id')->references('id')->on('marketplace_listings')->nullOnDelete();
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_source_type')) {
                $table->string('order_source_type', 30)->default('manual')->after('order_number');
            }
            if (!Schema::hasColumn('orders', 'external_order_id')) {
                $table->string('external_order_id')->nullable()->after('order_source_type');
            }
            if (!Schema::hasColumn('orders', 'external_order_reference')) {
                $table->string('external_order_reference')->nullable()->after('external_order_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['external_order_reference', 'external_order_id', 'order_source_type'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        foreach (['order_products', 'quote_products', 'lead_products'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'marketplace_listing_id')) {
                    $table->dropForeign([$tableName . '_marketplace_listing_id_foreign']);
                    $table->dropColumn('marketplace_listing_id');
                }
            });
        }

        Schema::dropIfExists('marketplace_listings');
    }
};
