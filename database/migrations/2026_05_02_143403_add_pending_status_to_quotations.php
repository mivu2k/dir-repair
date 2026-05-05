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
        // No-op for SQLite as it handles string statuses anyway, 
        // but we'll document the change here.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
