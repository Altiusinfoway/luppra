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
        Schema::connection('landlord')->dropIfExists('invoice_template_sections');

        Schema::connection('landlord')->create('invoice_template_sections', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoice_template_id');
            $table->string('section_key', 100);
            $table->string('section_label');
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('settings_json')->nullable();
            $table->timestamps();

            $table->foreign('invoice_template_id')
                ->references('id')
                ->on('invoice_templates')
                ->onDelete('cascade');
            $table->unique(['invoice_template_id', 'section_key'], 'invoice_template_sections_template_key_unique');
            $table->index(['invoice_template_id', 'sort_order'], 'invoice_template_sections_template_sort_index');
            $table->index('is_visible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('invoice_template_sections');
    }
};
