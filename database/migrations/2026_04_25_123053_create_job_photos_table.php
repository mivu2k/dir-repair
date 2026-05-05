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
        Schema::create('job_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_job_id')->constrained('repair_jobs');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->enum('type', ['before', 'after', 'damage']);
            $table->string('path');
            $table->string('caption')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_photos');
    }
};
