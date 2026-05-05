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
        Schema::create('intakes', function (Blueprint $table) {
            $table->id();
            $table->string('intake_number')->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('received_by')->constrained('users');
            $table->dateTime('received_at');
            $table->enum('payment_method', ['cash', 'credit', 'bank_transfer', 'warranty']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intakes');
    }
};
