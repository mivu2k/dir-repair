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
            $table->string('subject')->nullable()->after('quotation_number');
            $table->string('reference')->nullable()->after('subject');
            $table->date('date')->nullable()->after('reference');
            $table->string('currency')->nullable()->after('date');
            $table->string('project')->nullable()->after('currency');
        });
        
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->decimal('discount', 10, 2)->default(0)->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['subject', 'reference', 'date', 'currency', 'project']);
        });
        
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
};
