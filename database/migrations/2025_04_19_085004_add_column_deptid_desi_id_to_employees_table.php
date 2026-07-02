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
        Schema::table('employees', function (Blueprint $table) {
            // Drop columns if they exist
            if (Schema::hasColumn('employees', 'designation_id')) {
                $table->dropColumn('designation_id');
            }

            if (Schema::hasColumn('employees', 'department_id')) {
                $table->dropColumn('department_id');
            }
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('branch_id');
            $table->unsignedBigInteger('designation_id')->nullable()->after('department_id');


            $table->foreign('designation_id')->references('id')->on('designations')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropForeign(['designation_id']);
                $table->dropForeign(['department_id']);
                $table->dropColumn(['designation_id', 'department_id']);
            });
        });
    }
};
