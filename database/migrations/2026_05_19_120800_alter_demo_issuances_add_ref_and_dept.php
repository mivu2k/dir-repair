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
        Schema::table('demo_issuances', function (Blueprint $table) {
            $table->string('reference_letter')->nullable()->after('notes');
            $table->string('department')->nullable()->after('reference_letter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demo_issuances', function (Blueprint $table) {
            $table->dropColumn(['reference_letter', 'department']);
        });
    }
};
