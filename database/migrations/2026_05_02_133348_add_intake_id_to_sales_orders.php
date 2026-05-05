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
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('repair_job_id')->nullable()->change();
            $table->foreignId('intake_id')->nullable()->after('repair_job_id')->constrained('intakes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['intake_id']);
            $table->dropColumn('intake_id');
            $table->foreignId('repair_job_id')->nullable(false)->change();
        });
    }
};
