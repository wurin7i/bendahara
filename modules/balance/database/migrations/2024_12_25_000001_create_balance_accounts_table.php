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
        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique()->comment('Account code (e.g., 101, 401, 501)');
            $table->string('name')->comment('Account name (e.g., Kas, Iuran, Beban Gaji)');
            $table->enum('category', [
                'Assets',
                'Liabilities',
                'Equity',
                'Income',
                'Expenses',
            ])->comment('Account category');
            $table->enum('account_behavior', [
                'FLEXIBLE',
                'TRANSIT_ONLY',
                'CREDIT_ONLY',
                'NON_LIQUID',
            ])->default('FLEXIBLE')->comment('Account behavior rules');
            $table->timestamps();

            // Indexes
            $table->index('category');
            $table->index('account_behavior');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
