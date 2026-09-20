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
        //hardware details
        Schema::create('hardware_details', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('serial_number')->nullable();
            $table->jsonb('specs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hardware_details');
    }
};
