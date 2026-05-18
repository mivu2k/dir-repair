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
            $table->json('items')->nullable()->after('customer_id');
            $table->dropColumn(['item_name', 'serial_number', 'accessories_included']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demo_issuances', function (Blueprint $table) {
            $table->string('item_name')->nullable();
            $table->string('serial_number')->nullable();
            $table->text('accessories_included')->nullable();
            $table->dropColumn('items');
        });
    }
};
