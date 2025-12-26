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
        Schema::create('division_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('division_id')->comment('Reference to division');
            $table->uuid('account_id')->comment('Reference to account from Balance module');
            $table->string('alias_name')->comment('Custom name for this account in division context (e.g., "Kas Kecil Acara")');
            $table->boolean('is_active')->default(true)->comment('Active status for this division-account mapping');
            $table->timestamps();

            // Foreign keys
            $table->foreign('division_id')
                ->references('id')
                ->on('divisions')
                ->onDelete('cascade');

            $table->foreign('account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('restrict');

            // Unique constraint: one account can only be mapped once per division
            $table->unique(['division_id', 'account_id']);

            // Indexes
            $table->index('division_id');
            $table->index('account_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('division_accounts');
    }
};
