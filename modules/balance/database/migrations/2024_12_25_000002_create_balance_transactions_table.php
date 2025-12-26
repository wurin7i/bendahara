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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date')->comment('Transaction date');
            $table->text('description')->comment('Transaction description');
            $table->decimal('total_amount', 15, 2)->comment('Total transaction amount');
            $table->enum('status', [
                'DRAFT',
                'PENDING',
                'APPROVED',
                'REJECTED',
                'VOID'
            ])->default('DRAFT')->comment('Transaction status');
            $table->string('voucher_no', 50)->nullable()->unique()->comment('Voucher number (auto-generated after approval)');
            $table->string('attachment_url')->nullable()->comment('URL to receipt/proof attachment');
            $table->timestamps();

            // Indexes
            $table->index('date');
            $table->index('status');
            $table->index('voucher_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
