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
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('transaction_id')->comment('Reference to transaction');
            $table->string('actor_id')->comment('ID of user who performed action (decoupled from User model)');
            $table->enum('action', [
                'SUBMIT',
                'EDIT',
                'APPROVE',
                'REJECT',
                'VOID',
            ])->comment('Action performed');
            $table->text('comment')->nullable()->comment('Optional comment (e.g., rejection reason)');
            $table->timestamp('created_at')->useCurrent()->comment('Timestamp of action');

            // Foreign keys
            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions')
                ->onDelete('cascade');

            // Indexes
            $table->index('transaction_id');
            $table->index('created_at');
            $table->index(['transaction_id', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_logs');
    }
};
