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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('transaction_id')->comment('Reference to transaction');
            $table->uuid('account_id')->comment('Reference to account');
            $table->enum('entry_type', ['DEBIT', 'CREDIT'])->comment('Entry type for double-entry');
            $table->decimal('amount', 15, 2)->comment('Entry amount');
            $table->timestamps();

            // Foreign keys
            $table->foreign('transaction_id')
                  ->references('id')
                  ->on('transactions')
                  ->onDelete('cascade');
            
            $table->foreign('account_id')
                  ->references('id')
                  ->on('accounts')
                  ->onDelete('restrict');

            // Indexes
            $table->index('transaction_id');
            $table->index('account_id');
            $table->index(['transaction_id', 'entry_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
