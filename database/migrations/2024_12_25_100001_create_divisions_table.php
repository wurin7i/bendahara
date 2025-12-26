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
        Schema::create('divisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->comment('Division name (e.g., Pusat, Divisi Acara, Humas)');
            $table->string('code', 10)->unique()->comment('Division code (e.g., PST, ACR, HMS)');
            $table->text('description')->nullable()->comment('Division description');
            $table->boolean('is_active')->default(true)->comment('Active status');
            $table->timestamps();

            // Indexes
            $table->index('code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('divisions');
    }
};
