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
        Schema::create('repair_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_number')->unique();
            $table->foreignId('intake_id')->constrained('intakes');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('assigned_technician_id')->nullable()->constrained('users');
            $table->string('device_name');
            $table->string('brand');
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable()->index();
            $table->enum('condition_on_arrival', ['good', 'fair', 'damaged', 'broken']);
            $table->text('issue_description');
            $table->enum('priority', ['normal', 'urgent'])->default('normal');
            $table->date('expected_delivery_date')->nullable();
            $table->enum('status', ['received', 'diagnosing', 'waiting_approval', 'in_progress', 'completed', 'delivered', 'cancelled']);
            $table->dateTime('status_updated_at');
            $table->dateTime('delivered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_jobs');
    }
};
