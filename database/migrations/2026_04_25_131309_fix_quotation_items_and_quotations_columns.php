<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add item_type and total_price columns to quotation_items
        // (existing: id, quotation_id, description, quantity, unit_price, line_total)
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->string('item_type')->default('misc')->after('quotation_id');
            $table->decimal('total_price', 10, 2)->default(0)->after('unit_price');
        });

        // Add subtotal and tax_amount to quotations
        // (existing table has: tax_percent and total_amount but not subtotal/tax_amount)
        Schema::table('quotations', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(0)->after('discount_amount');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn(['item_type', 'total_price']);
        });
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax_amount']);
        });
    }
};
