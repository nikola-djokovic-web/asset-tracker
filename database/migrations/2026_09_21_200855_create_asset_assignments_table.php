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
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('asset_id')->constrained()->cascadeOnDelete();
            
            // Kome je dodeljen aset (korisnik unutar sistema)
            $table->foreignUlid('assigned_to_user_id')->constrained('users')->cascadeOnDelete();
            
            // Ko iz IT/Admin tima je izvršio zaduženje
            $table->foreignUlid('assigned_by_user_id')->constrained('users')->cascadeOnDelete();

            $table->timestamp('assigned_at');
            $table->timestamp('returned_at')->nullable();

            $table->text('notes')->nullable();
            $table->string('condition_on_checkout')->default('good'); // new, good, damaged
            $table->string('condition_on_checkin')->nullable();

            $table->timestamps();

            // Indeksi za brže pretraživanje istorije
            $table->index(['tenant_id', 'asset_id']);
            $table->index(['tenant_id', 'assigned_to_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
    }
};
