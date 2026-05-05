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
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_job_id')->unique()->constrained('repair_jobs');
            $table->foreignId('technician_id')->constrained('users');
            $table->text('diagnosis_notes');
            $table->text('work_performed')->nullable();
            $table->text('parts_required')->nullable();
            $table->decimal('estimated_hours', 5, 2)->nullable();
            $table->enum('complexity', ['simple', 'moderate', 'complex']);
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnoses');
    }
};
