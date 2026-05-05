<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diagnoses', function (Blueprint $table) {
            // Drop existing NOT NULL columns that no longer match the controller
            $table->dropColumn(['diagnosis_notes', 'complexity']);
        });

        Schema::table('diagnoses', function (Blueprint $table) {
            // Drop FK, drop unique, restore FK to allow multiple diagnoses per job in MySQL
            $table->dropForeign(['repair_job_id']);
            $table->dropUnique(['repair_job_id']);
            $table->foreign('repair_job_id')->references('id')->on('repair_jobs');
        });

        Schema::table('diagnoses', function (Blueprint $table) {
            // Add the new columns that match the controller
            $table->text('findings')->after('technician_id');
            $table->text('required_parts')->nullable()->after('findings');
            $table->text('required_labor')->nullable()->after('required_parts');
            $table->integer('estimated_repair_time_days')->nullable()->after('required_labor');
        });
    }

    public function down(): void
    {
        Schema::table('diagnoses', function (Blueprint $table) {
            $table->dropColumn(['findings', 'required_parts', 'required_labor', 'estimated_repair_time_days']);
        });

        Schema::table('diagnoses', function (Blueprint $table) {
            $table->text('diagnosis_notes');
            $table->enum('complexity', ['simple', 'moderate', 'complex']);
            $table->unique('repair_job_id');
        });
    }
};
