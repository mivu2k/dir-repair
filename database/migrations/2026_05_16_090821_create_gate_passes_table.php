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
        Schema::create('gate_passes', function (Blueprint $table) {
            $table->id();
            $table->string('pass_number')->unique(); // IN-0001, OUT-0001
            $table->string('type'); // inward, outward
            $table->nullableMorphs('reference'); // Optionally link to Intake, RepairJob, etc
            $table->string('person_name'); // Who brought it in / took it out
            $table->string('company_name')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->text('items_description'); // What was moved
            $table->string('status')->default('issued'); // issued, cancelled
            $table->foreignId('authorized_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gate_passes');
    }
};
