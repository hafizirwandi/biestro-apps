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
            $table->id();
            $table->string('transaction_code')->unique();
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->decimal('total_amount', 12, 2);
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_type')->nullable();
            $table->decimal('amount_given', 12, 2)->nullable();
            $table->string('noncash_method')->nullable();
            $table->string('bank')->nullable();
            $table->timestamps();
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
