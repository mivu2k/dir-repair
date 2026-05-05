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
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('repair_job_id')->nullable()->change();
            $table->foreignId('intake_id')->nullable()->after('repair_job_id')->constrained('intakes');
            $table->foreignId('customer_id')->nullable()->after('intake_id')->constrained('customers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['intake_id']);
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['intake_id', 'customer_id']);
            $table->foreignId('repair_job_id')->nullable(false)->change();
        });
    }
};
