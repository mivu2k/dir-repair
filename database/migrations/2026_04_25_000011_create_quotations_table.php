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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number')->unique();
            $table->foreignId('repair_job_id')->constrained('repair_jobs');
            $table->foreignId('prepared_by')->constrained('users');
            $table->string('labor_description')->nullable();
            $table->decimal('labor_amount', 10, 2)->default(0);
            $table->decimal('parts_amount', 10, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->text('notes')->nullable();
            $table->enum('customer_approval', ['pending', 'approved', 'rejected'])->default('pending');
            $table->dateTime('customer_approved_at')->nullable();
            $table->enum('manager_approval', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('manager_id')->nullable()->constrained('users');
            $table->dateTime('manager_approved_at')->nullable();
            $table->enum('status', ['draft', 'sent', 'approved', 'rejected', 'expired']);
            $table->date('valid_until')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
